<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Exception;

class VenteController extends Controller
{
    protected $firestore;
    protected $pharmacieId;

    public function __construct()
    {
        $credentials = config('firebase.credentials');
        $this->firestore = new FirestoreClient([
            'keyFilePath' => $credentials,
        ]);

        // Récupérer l'ID de la pharmacie depuis la session
        $this->pharmacieId = session('pharmacie_id');

        if (!$this->pharmacieId) {
            // Rediriger ou gérer le cas où l'ID de la pharmacie n'est pas disponible
            abort(403, 'ID de la pharmacie non disponible.');
        }
    }

    public function index()
    {
        try {
            $pharmacieRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId);
            $pharmacySnapshot = $this->firestore->collection('pharmacies')->document($this->pharmacieId)->snapshot();
            $pharmacyName = $pharmacySnapshot->get('nom'); // Assurez-vous que 'nom' est le bon champ
            $pharmacyAdresse = $pharmacySnapshot->get('emplacement');
            $pharmacyTel1 = $pharmacySnapshot->get('telephone1');
            $pharmacyTel2 = $pharmacySnapshot->get('telephone2');
            $pharmacyTel = $pharmacyTel1 . ' / ' . $pharmacyTel2;

            // Récupération des ventes
            $ventesSnapshot = $pharmacieRef
                ->collection('ventes')
                ->orderBy('date_vente', 'DESC')
                ->limit(50)
                ->documents();

            $ventes = [];
            foreach ($ventesSnapshot as $vente) {
                $venteData = $vente->data();
                $produitsVendus = array_map(function ($produit) {
                    return [
                        'nom_produit' => $produit['nom_produit'] ?? 'Produit inconnu',
                        'quantite' => $produit['quantite'] ?? 0,
                        'prix_unitaire' => $produit['prix_unitaire'] ?? 0,
                        'prix_total' => $produit['prix_total'] ?? 0,
                        'sur_ordonnance' => $produit['sur_ordonnance'] ?? 'Non'
                    ];
                }, $venteData['produits_vendus'] ?? []);

                $ventes[] = [
                    'id' => $vente->id(),
                    'date' => !empty($venteData['date_vente']) ? (new \DateTime($venteData['date_vente']))->format('d/m/Y H:i') : 'Date inconnue',
                    'montant_total' => $venteData['montant_total'] ?? 0,
                    'produits_vendus' => $produitsVendus,
                    'vendeur' => $venteData['vendeur'] ?? 'Vendeur inconnu',
                    'montant_percu' => $venteData['montant_percu'] ?? 0,
                    'montant_rendu' => $venteData['montant_rendu'] ?? 0
                ];
            }

            // Récupération des produits avec leurs prix
            $produitsSnapshot = $pharmacieRef
                ->collection('produits')
                ->documents();

            $produits = [];
            foreach ($produitsSnapshot as $produit) {
                $produitData = $produit->data();
                $produits[] = [
                    'id' => $produit->id(),
                    'nom' => $produitData['nom'] ?? 'Nom inconnu',
                    'prix_unitaire' => floatval($produitData['prix_unitaire'] ?? 0),
                    'quantite_en_stock' => intval($produitData['quantite_en_stock'] ?? 0),
                    'sur_ordonnance' => $produitData['sur_ordonnance'] ?? false
                ];
            }

            Log::info('Produits récupérés avec succès.', ['produits' => $produits]);

            return view('ventes.index', compact('ventes', 'produits', 'pharmacyName', 'pharmacyAdresse', 'pharmacyTel'));

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des ventes : ' . $e->getMessage());
            Log::error('Erreur Firestore : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des données.');
        }
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'produits' => 'required|array',
            'produits.*.id' => 'required|string',
            'produits.*.nom' => 'required|string',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_unitaire' => 'required|numeric|min:0',
            'montant_percu' => 'required|numeric|min:0',
            'montant_rendu' => 'required|numeric|min:0',
        ]);

        try {
            $pharmacieRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId);

            $montantTotal = 0;
            $coutTotal = 0;
            $produitsVendus = [];
            $alertesExpiration = [];

            // Traitement de chaque produit
            foreach ($request->produits as $produit) {
                $prixTotal = $produit['quantite'] * $produit['prix_unitaire'];

                // Référence au produit
                $produitRef = $pharmacieRef
                    ->collection('produits')
                    ->document($produit['id']);

                // Récupérer tous les stocks (sans filtre)
                $stocksSnapshot = $produitRef->collection('stock')->documents();

                // Préparation des stocks disponibles
                $stocks = [];
                $quantiteDisponible = 0;
                $dateActuelle = new \DateTime();
                
                foreach ($stocksSnapshot as $stock) {
                    $stockData = $stock->data();
                    // On ne garde que les stocks avec quantité > 0
                    if ($stockData['quantite_disponible'] > 0) {
                        $dateExpiration = new \DateTime($stockData['date_expiration']);
                        
                        // Vérifier si le produit expire dans moins de 3 mois
                        $interval = $dateActuelle->diff($dateExpiration);
                        if ($interval->days <= 90) {
                            $alertesExpiration[] = [
                                'produit' => $produit['nom'],
                                'date_expiration' => $dateExpiration->format('d/m/Y'),
                                'quantite' => $stockData['quantite_disponible']
                            ];
                        }
                        
                        $quantiteDisponible += $stockData['quantite_disponible'];
                        $stocks[] = [
                            'reference' => $stock->reference(),
                            'quantite_disponible' => $stockData['quantite_disponible'],
                            'date_expiration' => $stockData['date_expiration'],
                            'prix_achat' => $stockData['prix_achat'] ?? 0
                        ];
                    }
                }

                // Vérifier si le stock est suffisant
                if ($quantiteDisponible < $produit['quantite']) {
                    throw new Exception("Stock insuffisant pour {$produit['nom']}");
                }

                // Trier les stocks par date d'expiration (FEFO)
                usort($stocks, function($a, $b) {
                    return strtotime($a['date_expiration']) - strtotime($b['date_expiration']);
                });

                // Mise à jour du stock en commençant par les dates d'expiration les plus proches
                $quantiteRestante = $produit['quantite'];
                $coutTotalProduit = 0;
                $detailsStockUtilise = [];

                foreach ($stocks as $stock) {
                    if ($quantiteRestante <= 0) break;

                    $quantiteStock = $stock['quantite_disponible'];
                    $quantiteARetirer = min($quantiteRestante, $quantiteStock);

                    // Calculer le coût pour ce lot
                    $coutLot = $quantiteARetirer * floatval($stock['prix_achat']);
                    $coutTotalProduit += $coutLot;

                    // Enregistrer les détails du stock utilisé
                    $detailsStockUtilise[] = [
                        'quantite' => $quantiteARetirer,
                        'prix_achat' => floatval($stock['prix_achat']),
                        'date_expiration' => $stock['date_expiration'],
                        'cout_total' => $coutLot
                    ];
                    
                    // Mise à jour du stock
                    $stock['reference']->update([
                        ['path' => 'quantite_disponible', 'value' => $quantiteStock - $quantiteARetirer]
                    ]);

                    $quantiteRestante -= $quantiteARetirer;
                }

                // Calculer et mettre à jour la quantité totale du produit
                $nouveauStockSnapshot = $produitRef
                    ->collection('stock')
                    ->documents();

                $nouvelleQuantiteTotale = 0;
                foreach ($nouveauStockSnapshot as $stock) {
                    $nouvelleQuantiteTotale += $stock->data()['quantite_disponible'];
                }

                // Mise à jour de la quantité totale du produit
                $produitRef->update([
                    ['path' => 'quantite_en_stock', 'value' => $nouvelleQuantiteTotale]
                ]);

                // Ajouter aux produits vendus avec les détails du stock utilisé
                $produitsVendus[] = [
                    'id_produit' => $produit['id'],
                    'nom_produit' => $produit['nom'],
                    'quantite' => intval($produit['quantite']),
                    'prix_unitaire' => floatval($produit['prix_unitaire']),
                    'prix_total' => floatval($prixTotal),
                    'cout_total' => $coutTotalProduit,
                    'details_stock' => $detailsStockUtilise
                ];

                $montantTotal += $prixTotal;
                $coutTotal += $coutTotalProduit;
            }

            // Enregistrer la vente avec les informations de coût
            $dateVente = (new \DateTime())->format('Y-m-d\TH:i:s\Z');
            $vente = [
                'date_vente' => $dateVente,
                'montant_total' => floatval($montantTotal),
                'cout_total' => floatval($coutTotal),
                'benefice_brut' => floatval($montantTotal - $coutTotal),
                'produits_vendus' => $produitsVendus,
                'vendeur' => session('user_name', 'Inconnu'),
                'montant_percu' => floatval($request->montant_percu),
                'montant_rendu' => floatval($request->montant_rendu),
                'created_at' => $dateVente,
                'updated_at' => $dateVente
            ];

            // Ajouter la vente à Firestore
            $ventesRef = $pharmacieRef->collection('ventes')->add($vente);

            // Retourner la réponse
            return response()->json([
                'success' => true,
                'message' => 'Vente enregistrée avec succès',
                'data' => [
                    'vente_id' => $ventesRef->id(),
                    'alertes_expiration' => $alertesExpiration
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la vente : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la vente : ' . $e->getMessage()
            ], 500);
        }
    }
}
