<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DestockageController extends Controller
{
    protected $firestore;
    protected $pharmacieId;

    public function __construct()
    {
        $credentials = config('firebase.credentials');

        $this->firestore = new FirestoreClient([
            'keyFilePath' => $credentials,
        ]);
        $this->pharmacieId = session('pharmacie_id');
    }

    private function normaliserDate($date)
    {
        try {
            // Si la date est déjà au format YYYY-MM-DD, la retourner telle quelle
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $date;
            }

            // Essayer de parser la date avec Carbon
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Erreur de normalisation de la date: " . $date);
            return null;
        }
    }

    public function index()
    {
        try {
            $pharmacieRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId);

            // Récupérer tous les produits
            $produitsSnapshot = $pharmacieRef->collection('produits')->documents();
            
            $produitsExpires = [];
            $produitsAExpirer = [];
            
            // Dates de référence au format YYYY-MM-DD
            $dateActuelle = date('Y-m-d');
            $dateLimite = date('Y-m-d', strtotime('+3 months'));

            Log::info("Date actuelle: " . $dateActuelle);
            Log::info("Date limite: " . $dateLimite);

            foreach ($produitsSnapshot as $produit) {
                $produitData = $produit->data();
                $stockCollection = $produit->reference()->collection('stock');
                $stockSnapshot = $stockCollection->documents();

                foreach ($stockSnapshot as $lot) {
                    $lotData = $lot->data();
                    if (!isset($lotData['date_expiration']) || !isset($lotData['quantite_disponible'])) {
                        continue;
                    }

                    if ($lotData['quantite_disponible'] <= 0) {
                        continue;
                    }

                    // Normaliser la date d'expiration
                    $dateExpiration = $this->normaliserDate($lotData['date_expiration']);
                    if (!$dateExpiration) {
                        Log::warning("Date d'expiration invalide pour le produit {$produitData['nom']}: {$lotData['date_expiration']}");
                        continue;
                    }

                    Log::info("Produit: " . ($produitData['nom'] ?? 'Nom inconnu'));
                    Log::info("Date expiration: " . $dateExpiration);
                    
                    $produitInfo = [
                        'id' => $produit->id(),
                        'nom' => $produitData['nom'] ?? 'Nom inconnu',
                        'lot_id' => $lot->id(),
                        'lot_numero' => $lotData['lot_numero'] ?? 'Numéro inconnu',
                        'date_expiration' => $dateExpiration,
                        'date_expiration_affichage' => Carbon::createFromFormat('Y-m-d', $dateExpiration)->format('d/m/Y'),
                        'quantite_disponible' => $lotData['quantite_disponible'],
                        'prix_achat' => $lotData['prix_achat'] ?? 0
                    ];

                    // Comparaison directe des chaînes de date
                    if ($dateExpiration < $dateActuelle) {
                        $produitsExpires[] = $produitInfo;
                        Log::info("Produit expiré");
                    }
                    // Produits qui expirent dans les 3 prochains mois
                    elseif ($dateExpiration <= $dateLimite && $dateExpiration > $dateActuelle) {
                        $produitsAExpirer[] = $produitInfo;
                        Log::info("Produit à expirer");
                    }
                }
            }

            // Trier les produits par date d'expiration
            usort($produitsExpires, function($a, $b) {
                return strcmp($a['date_expiration'], $b['date_expiration']);
            });
            
            usort($produitsAExpirer, function($a, $b) {
                return strcmp($a['date_expiration'], $b['date_expiration']);
            });

            Log::info("Nombre de produits expirés: " . count($produitsExpires));
            Log::info("Nombre de produits à expirer: " . count($produitsAExpirer));

            return view('destockage.index', compact('produitsExpires', 'produitsAExpirer'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DestockageController@index: ' . $e->getMessage());
            return view('destockage.index', [
                'produitsExpires' => [],
                'produitsAExpirer' => []
            ])->with('error', 'Une erreur est survenue lors de la récupération des produits à déstocker.');
        }
    }

    public function destocker(Request $request)
    {
        try {
            $request->validate([
                'lots' => 'required|array',
                'lots.*' => 'required|string',
                'raison' => 'required|string|in:expiration,deterioration,autre',
                'commentaire' => 'nullable|string'
            ]);

            $pharmacieRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId);

            $lotsDestockes = [];
            $perteTotal = 0;

            foreach ($request->lots as $lotInfo) {
                list($produitId, $lotId) = explode('|', $lotInfo);
                
                // Référence au lot
                $lotRef = $pharmacieRef
                    ->collection('produits')
                    ->document($produitId)
                    ->collection('stock')
                    ->document($lotId);

                $lot = $lotRef->snapshot();
                if (!$lot->exists()) {
                    continue;
                }

                $lotData = $lot->data();
                $quantiteDestockee = $lotData['quantite_disponible'];
                $perteTotal += $quantiteDestockee * ($lotData['prix_achat'] ?? 0);

                // Créer un enregistrement de déstockage
                $destockageRef = $pharmacieRef->collection('destockages')->add([
                    'date_destockage' => date('Y-m-d'),
                    'produit_id' => $produitId,
                    'lot_id' => $lotId,
                    'lot_numero' => $lotData['lot_numero'],
                    'quantite_destockee' => $quantiteDestockee,
                    'prix_achat' => $lotData['prix_achat'] ?? 0,
                    'perte_financiere' => $quantiteDestockee * ($lotData['prix_achat'] ?? 0),
                    'raison' => $request->raison,
                    'commentaire' => $request->commentaire,
                    'date_expiration' => $lotData['date_expiration']
                ]);

                // Mettre à jour le stock (quantité = 0)
                $lotRef->update([
                    ['path' => 'quantite_disponible', 'value' => 0]
                ]);

                // Mettre à jour la quantité totale du produit
                $this->updateQuantiteTotaleProduit($produitId);

                $lotsDestockes[] = [
                    'lot_numero' => $lotData['lot_numero'],
                    'quantite' => $quantiteDestockee
                ];
            }

            return redirect()->route('destockage.index')
                ->with('success', 'Déstockage effectué avec succès. ' . count($lotsDestockes) . ' lots ont été déstockés pour une perte totale de ' . number_format($perteTotal, 2) . ' F');

        } catch (\Exception $e) {
            Log::error('Erreur dans DestockageController@destocker: ' . $e->getMessage());
            return redirect()->route('destockage.index')
                ->with('error', 'Une erreur est survenue lors du déstockage.');
        }
    }

    private function updateQuantiteTotaleProduit($produitId)
    {
        $produitRef = $this->firestore
            ->collection('pharmacies')
            ->document($this->pharmacieId)
            ->collection('produits')
            ->document($produitId);

        $stocksSnapshot = $produitRef->collection('stock')->documents();
        $quantiteTotale = 0;

        foreach ($stocksSnapshot as $stock) {
            $quantiteTotale += $stock->data()['quantite_disponible'];
        }

        $produitRef->update([
            ['path' => 'quantite_en_stock', 'value' => $quantiteTotale]
        ]);
    }
} 