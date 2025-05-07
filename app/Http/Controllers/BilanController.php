<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Exception;

class BilanController extends Controller
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
    }

    public function index()
    {
        return view('bilans.index');
    }

    public function genererBilan(Request $request)
    {
        try {
            // Log la requête pour le débogage
            Log::info('Requête bilan reçue', $request->all());

            // Validation basique
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut'
            ]);

            // Convertir les dates en format ISO 8601 pour Firestore
            $dateDebut = (new \DateTime($request->date_debut))->setTime(0, 0, 0)->format('Y-m-d\TH:i:s\Z');
            $dateFin = (new \DateTime($request->date_fin))->setTime(23, 59, 59)->format('Y-m-d\TH:i:s\Z');

            // Récupérer les ventes de la période
            $ventesRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId)
                ->collection('ventes')
                ->where('date_vente', '>=', $dateDebut)
                ->where('date_vente', '<=', $dateFin)
                ->documents();

            $totalVentes = 0;
            $nombreVentes = 0;
            $produitsVendus = [];

            // Parcourir les ventes
            foreach ($ventesRef as $vente) {
                $venteData = $vente->data();
                $totalVentes += floatval($venteData['montant_total'] ?? 0);
                $nombreVentes++;

                // Agréger les produits vendus
                if (isset($venteData['produits_vendus']) && is_array($venteData['produits_vendus'])) {
                    foreach ($venteData['produits_vendus'] as $produit) {
                        $nomProduit = $produit['nom_produit'];
                        if (!isset($produitsVendus[$nomProduit])) {
                            $produitsVendus[$nomProduit] = [
                                'nom' => $nomProduit,
                                'quantite' => 0,
                                'montant_total' => 0
                            ];
                        }
                        $produitsVendus[$nomProduit]['quantite'] += intval($produit['quantite']);
                        $produitsVendus[$nomProduit]['montant_total'] += floatval($produit['prix_total']);
                    }
                }
            }

            // Trier les produits par montant total et prendre les 10 premiers
            uasort($produitsVendus, function($a, $b) {
                return $b['montant_total'] <=> $a['montant_total'];
            });
            $produitsPopulaires = array_slice(array_values($produitsVendus), 0, 10);

            // Récupérer les achats (ravitaillements) de la période
            $ravitaillementsRef = $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId)
                ->collection('ravitaillements')
                ->where('date_ravitaillement', '>=', $dateDebut)
                ->where('date_ravitaillement', '<=', $dateFin)
                ->documents();

            $totalAchats = 0;
            $nombreAchats = 0;

            foreach ($ravitaillementsRef as $ravitaillement) {
                $ravitaillementData = $ravitaillement->data();
                if (isset($ravitaillementData['produits']) && is_array($ravitaillementData['produits'])) {
                    foreach ($ravitaillementData['produits'] as $produit) {
                        $totalAchats += floatval($produit['prix_achat'] ?? 0) * intval($produit['quantite'] ?? 0);
                    }
                }
                $nombreAchats++;
            }

            // Calculer le bénéfice net
            $beneficeNet = $totalVentes - $totalAchats;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_ventes' => $totalVentes,
                    'nombre_ventes' => $nombreVentes,
                    'total_achats' => $totalAchats,
                    'nombre_achats' => $nombreAchats,
                    'benefice_net' => $beneficeNet,
                    'produits_populaires' => $produitsPopulaires,
                    'periode' => [
                        'debut' => $request->date_debut,
                        'fin' => $request->date_fin
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erreur dans BilanController@genererBilan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération du bilan: ' . $e->getMessage()
            ], 500);
        }
    }
} 