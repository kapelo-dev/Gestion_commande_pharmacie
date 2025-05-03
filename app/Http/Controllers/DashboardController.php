<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;

class DashboardController extends Controller
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
        if (!$this->pharmacieId) {
            return redirect()->route('login')->withErrors(['error' => 'Vous devez être connecté pour accéder à cette page.']);
        }

        $pharmacySnapshot = $this->firestore->collection('pharmacies')->document($this->pharmacieId)->snapshot();
        $pharmacyName = $pharmacySnapshot->get('nom'); // Assurez-vous que 'nom' est le bon champ

        $commandesCollection = $this->firestore->collection('pharmacies')->document($this->pharmacieId)->collection('commandes');

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

        $commandesEnAttenteValidation = count($commandesEnCours);
        $commandesEnAttenteRecuperation = count($commandesValidees);

        // Récupérer les produits
        $produitsCollection = $this->firestore->collection('pharmacies')->document($this->pharmacieId)->collection('produits');
        $produitsSnapshot = $produitsCollection->documents();

        // Récupérer les produits et les filtrer
        $produits = [];
        foreach ($produitsSnapshot as $produit) {
            $produitData = $produit->data();
            if (isset($produitData['quantite_en_stock'])) {
                $produits[] = $produitData; // Ajouter tous les produits à la liste
            }
        }

        // Filtrer les produits en rupture de stock et ceux proches de la rupture
        $medicamentsEnRupture = array_filter($produits, function($produit) {
            return $produit['quantite_en_stock'] === 0; // Produits en rupture de stock
        });

        $produitsSeuil = array_filter($produits, function($produit) {
            return $produit['quantite_en_stock'] > 0 && $produit['quantite_en_stock'] <= 20; // Produits proches de la rupture
        });

        // Initialiser les tableaux pour les produits expirés et ceux qui expirent dans 3 mois
        
        // Initialiser les tableaux pour les produits expirés et ceux qui expirent dans 3 mois
        $produitsExpirés = [];
        $produitsExpirantDansTroisMois = [];

        foreach ($produitsSnapshot as $produit) {
            $stockCollection = $produit->reference()->collection('stock');
            $stockSnapshot = $stockCollection->documents();

            foreach ($stockSnapshot as $lot) {
                $lotData = $lot->data();
                if (isset($lotData['date_expiration'], $lotData['quantite_disponible'], $lotData['lot_numero'])) {
                    // Analyser la date d'expiration avec le format ISO 8601
                    $expirationDate = \DateTime::createFromFormat(\DateTime::ISO8601, $lotData['date_expiration']);

                    // Vérifiez si l'analyse a réussi
                    if ($expirationDate === false) {
                        continue; // Passer à l'itération suivante si l'analyse échoue
                    }

                    // Vérifiez si la date d'expiration est déjà passée
                    if ($expirationDate < new \DateTime()) {
                        $produitsExpirés[] = [
                            'nom' => $produit->get('nom'),
                            'date_expiration' => $lotData['date_expiration'],
                            'quantite_disponible' => $lotData['quantite_disponible'],
                            'lot_numero' => $lotData['lot_numero'],
                        ];
                    }

                    // Vérifiez si la date d'expiration est dans les trois mois
                    if ($expirationDate > new \DateTime() && $expirationDate <= (new \DateTime())->modify('+3 months')) {
                        $produitsExpirantDansTroisMois[] = [
                            'nom' => $produit->get('nom'),
                            'date_expiration' => $lotData['date_expiration'],
                            'quantite_disponible' => $lotData['quantite_disponible'],
                            'lot_numero' => $lotData['lot_numero'],
                        ];
                    }
                }
            }
        }
        

        // Récupérer les ventes d'aujourd'hui
        $ventesCollection = $this->firestore->collection('pharmacies')->document($this->pharmacieId)->collection('ventes');
        $ventesSnapshot = $ventesCollection->documents();

        $montantTotalAujourdHui = 0;
        $aujourdhui = new \DateTime(); // Date actuelle
        $aujourdhuiFormat = $aujourdhui->format('Y-m-d'); // Format pour comparer avec 'date_vente'

        foreach ($ventesSnapshot as $vente) {
            $venteData = $vente->data();
            $dateVente = \DateTime::createFromFormat(\DateTime::ISO8601, $venteData['date_vente']);

            // Vérifiez si l'analyse a réussi
            if ($dateVente === false) {
                continue; // Passer à l'itération suivante si l'analyse échoue
            }

            // Comparer la date de vente avec aujourd'hui
            if ($dateVente->format('Y-m-d') === $aujourdhuiFormat) {
                $montantTotalAujourdHui += $venteData['montant_total'];
            }
        }

        // Compter les produits
        $countMedicamentsEnRupture = count($medicamentsEnRupture);
        $countProduitsSeuil = count($produitsSeuil);
        $countProduitsExpirés = count($produitsExpirés);
        $countProduitsExpirantDansTroisMois = count($produitsExpirantDansTroisMois);
       

        // Retourner la vue avec toutes les données nécessaires
        return view('dashboard', compact(
            'pharmacyName',
            'countMedicamentsEnRupture',
            'countProduitsSeuil',
            'commandesEnAttenteValidation',
            'commandesEnAttenteRecuperation',
            'produitsSeuil',
            'medicamentsEnRupture',
            'montantTotalAujourdHui',
            'produitsExpirés',
            'produitsExpirantDansTroisMois',
            'countProduitsExpirés',
            'countProduitsExpirantDansTroisMois'
           
        ));
    }

    
}
