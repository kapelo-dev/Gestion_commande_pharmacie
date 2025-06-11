<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Exception;

class CommandeController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $credentials = config('firebase.credentials');
        $this->firestore = new FirestoreClient([
            'keyFilePath' => $credentials,
        ]);
    }

    public function showCommandes()
    {
        // Récupérer l'ID de la pharmacie depuis la session
        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('dashboard')->withErrors('Pharmacie non sélectionnée.');
        }

        // Accéder à la collection des commandes dans Firestore
        $commandesCollection = $this->firestore->collection('pharmacies')->document($pharmacieId)->collection('commandes');

        // Récupérer toutes les commandes sans filtrage
        $commandesSnapshot = $commandesCollection->documents();

        // Tableau pour stocker les commandes
        $commandes = [];
        foreach ($commandesSnapshot as $commande) {
            $commandeData = $commande->data();
            $commandeData['id'] = $commande->id(); // Utilisez la méthode id() pour obtenir l'ID
            $commandes[] = $commandeData;
        }

        // Séparer les commandes par statut
        $commandesValidees = array_filter($commandes, function($commande) {
            return $commande['status_commande'] === 'validée';
        });

        $commandesEnCours = array_filter($commandes, function($commande) {
            return $commande['status_commande'] === 'en_cours';
        });

        // Retourner la vue avec les commandes validées et en cours
        return view('commandes.index', [
            'commandesValidees' => $commandesValidees,
            'commandesEnCours' => $commandesEnCours
        ]);
    }

    // Mettre à jour le statut d'une commande (validée ou récupérée)
    public function updateStatus(Request $request, $commandeId, $status)
    {
        // Récupérer l'ID de la pharmacie depuis la session
        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('dashboard')->withErrors('Pharmacie non sélectionnée.');
        }

        // Accéder à la sous-collection de la commande spécifique dans Firestore
        $commandeRef = $this->firestore->collection('pharmacies')->document($pharmacieId)->collection('commandes')->document($commandeId);

        // Récupérer les données de la commande
        $commandeSnapshot = $commandeRef->snapshot();
        $commandeData = $commandeSnapshot->data();

        // Préparer les champs à mettre à jour
        $updateFields = [
            ['path' => 'status_commande', 'value' => $status]
        ];

        // Si le statut est "rejetée", ajouter la raison du rejet
        if ($status === 'rejetée') {
            if (!$request->has('raison_rejet')) {
                return redirect()->back()->withErrors('La raison du rejet est requise.');
            }
            $updateFields[] = ['path' => 'raison_rejet', 'value' => $request->raison_rejet];
            $updateFields[] = ['path' => 'date_rejet', 'value' => date('Y-m-d H:i:s')];
        }

        // Mettre à jour la commande
        $commandeRef->update($updateFields);

        // Si le statut est mis à jour à "récupérée", créer un document de vente
        if ($status === 'récupérée') {
            // Passer les montants perçus et rendus uniquement à la méthode de création de vente
            $this->createVenteFromCommande($commandeData, $request->montant_percu, $request->montant_rendu);
            return redirect()->route('ventes.index')->with('success', 'Commande mise à jour avec succès.');
        }

        // Message de succès personnalisé selon le statut
        $message = $status === 'rejetée' ? 'Commande rejetée avec succès.' : 'Commande mise à jour avec succès.';

        // Rediriger vers la page des commandes après la mise à jour
        return redirect()->route('commandes.index')->with('success', $message);
    }

    protected function createVenteFromCommande($commandeData, $montantPercu, $montantRendu)
    {
        try {
            $pharmacieId = session('pharmacie_id');
            $pharmacieRef = $this->firestore->collection('pharmacies')->document($pharmacieId);
    
            $montantTotal = 0;
            $coutTotal = 0;
            $produitsVendus = [];
            $alertesExpiration = [];
    
            // Récupérer les produits de la pharmacie
            $produitsSnapshot = $pharmacieRef->collection('produits')->documents();
            $produits = [];
            foreach ($produitsSnapshot as $produit) {
                $produitData = $produit->data();
                $produits[$produitData['nom']] = [
                    'id' => $produit->id(),
                    'prix_unitaire' => floatval($produitData['prix_unitaire'] ?? 0),
                    'quantite_en_stock' => intval($produitData['quantite_en_stock'] ?? 0),
                    'sur_ordonnance' => $produitData['sur_ordonnance'] ?? false
                ];
            }
    
            // Traitement de chaque produit de la commande
            foreach ($commandeData['produits'] as $produit) {
                $nomProduit = $produit['nom'];
                if (isset($produits[$nomProduit])) {
                    $produitData = $produits[$nomProduit];
                    $prixTotal = $produit['quantite'] * $produitData['prix_unitaire'];
    
                    // Référence au produit
                    $produitRef = $pharmacieRef->collection('produits')->document($produitData['id']);
    
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
                                    'produit' => $nomProduit,
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
                        throw new Exception("Stock insuffisant pour {$nomProduit}");
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
                    $nouveauStockSnapshot = $produitRef->collection('stock')->documents();
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
                        'id_produit' => $produitData['id'],
                        'nom_produit' => $nomProduit,
                        'quantite' => intval($produit['quantite']),
                        'prix_unitaire' => floatval($produitData['prix_unitaire']),
                        'prix_total' => floatval($prixTotal),
                        'cout_total' => $coutTotalProduit,
                        'details_stock' => $detailsStockUtilise
                    ];
    
                    $montantTotal += $prixTotal;
                    $coutTotal += $coutTotalProduit;
                } else {
                    Log::warning("Produit non trouvé dans la pharmacie : {$nomProduit}");
                }
            }
    
            // Récupérer le prénom du pharmacien depuis la session
            $pharmacienPrenom = session('pharmacien_prenom');
    
            // Enregistrer la vente avec les informations de coût
            $dateVente = (new \DateTime())->format('Y-m-d\TH:i:s\Z');
            $vente = [
                'date_vente' => $dateVente,
                'montant_total' => floatval($montantTotal),
                'cout_total' => floatval($coutTotal),
                'benefice_brut' => floatval($montantTotal - $coutTotal),
                'produits_vendus' => array_values($produitsVendus),
                'vendeur' => $pharmacienPrenom,
                'montant_percu' => floatval($montantPercu),
                'montant_rendu' => floatval($montantRendu),
                'created_at' => $dateVente,
                'updated_at' => $dateVente,
                'source' => 'commande', // Indiquer que la vente provient d'une commande
                'alertes_expiration' => $alertesExpiration
            ];
    
            $venteRef = $pharmacieRef->collection('ventes')->add($vente);
            
            // Log des alertes d'expiration si présentes
            if (!empty($alertesExpiration)) {
                Log::warning('Alertes d\'expiration lors de la vente de commande:', $alertesExpiration);
            }
    
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la vente à partir de la commande : ' . $e->getMessage());
            throw $e; // Propager l'erreur pour la gérer dans updateStatus
        }
    }
    
}
