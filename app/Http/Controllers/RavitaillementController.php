<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use App\Models\ActionLog;

class RavitaillementController extends Controller
{
    protected $firestore;

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
        $pharmacieId = session('pharmacie_id');
    
        if (empty($pharmacieId)) {
            return redirect()->route('dashboard')->withErrors('Pharmacie non sélectionnée.');
        }
    
        // Vérifiez que l'ID du document n'est pas vide ou invalide
        if (empty($pharmacieId) || strpos($pharmacieId, '/') !== false) {
            return redirect()->route('dashboard')->with('error', 'ID de la pharmacie invalide.');
        }
    
        try {
            // Récupérer les ravitaillements
            $ravitaillementsSnapshot = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->orderBy('date_ravitaillement', 'DESC')
                ->documents();
    
            $ravitaillements = [];
            foreach ($ravitaillementsSnapshot as $ravitaillement) {
                $data = $ravitaillement->data();
    
                // Récupérer le nom du produit directement
                $produitNom = $data['produit_nom'] ?? 'Produit non trouvé';
    
                // Formater la date
                $date = null;
                if (isset($data['date_ravitaillement'])) {
                    $timestamp = $data['date_ravitaillement']->get();
                    $date = date('d/m/Y', $timestamp->getTimestamp());
                }
    
                $ravitaillements[] = [
                    'id' => $ravitaillement->id(),
                    'date' => $date,
                    'fournisseur' => $data['fournisseur'] ?? '',
                    'lot_numero' => $data['lot_numero'] ?? '',
                    'produit' => $produitNom,
                    'quantite_ravitailler' => $data['quantite_ravitailler'] ?? '',
                ];
            }
    
            // Récupérer la liste des produits pour le formulaire d'ajout
            $produitsSnapshot = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('produits')
                ->documents();
    
            $produits = [];
            foreach ($produitsSnapshot as $produit) {
                $produits[] = [
                    'id' => $produit->id(),
                    'nom' => $produit->data()['nom']
                ];
            }
    
            // Ajoutez ce log pour vérifier les produits récupérés
            Log::info('Produits récupérés:', $produits);
    
            return view('ravitaillements.index', compact('ravitaillements', 'produits'));
    
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de la récupération des ravitaillements.');
        }
    }
    
    

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur' => 'required|string',
            'lot_numero' => 'required|string',
            'produit_ravitaille' => 'required|string',
            'date_expiration' => 'required|date',
            'quantite_disponible' => 'required|integer|min:1',
        ]);
    
        $pharmacieId = session('pharmacie_id');
    
        if (!$pharmacieId) {
            return redirect()->back()
                ->with('error', 'ID de la pharmacie introuvable. Veuillez le configurer.');
        }
    
        // Vérifiez que l'ID du document n'est pas vide ou invalide
        if (empty($pharmacieId) || strpos($pharmacieId, '/') !== false) {
            return redirect()->back()->with('error', 'ID de la pharmacie invalide.');
        }
    
        // Ajoutez ce log pour vérifier les données envoyées avec le formulaire
        Log::info('Données du formulaire:', $request->all());
    
        try {
            // Utiliser la date actuelle pour le ravitaillement
            $dateRavitaillement = now();
    
            // Convertir la date d'expiration au format ISO 8601
            $dateExpiration = \DateTime::createFromFormat('Y-m-d', $request->date_expiration);
            if ($dateExpiration === false) {
                return back()->with('error', 'Format de date d\'expiration invalide.');
            }
            $dateExpirationISO = $dateExpiration->format('Y-m-d\TH:i:s\Z'); // Format ISO 8601
    
            // Référence au produit
            $produitRef = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('produits')
                ->document($request->produit_id);
    
            // Obtenir le document du produit
            $produitSnapshot = $produitRef->snapshot();
            if (!$produitSnapshot->exists()) {
                return back()->with('error', 'Produit introuvable.');
            }
    
            // Récupérer le nom du produit
            $produitNom = $produitSnapshot->data()['nom'] ?? 'Produit inconnu';
    
            // Créer le document stock dans la sous-collection du produit
            $stockRef = $produitRef->collection('stock')->add([
                'date_expiration' => $dateExpirationISO, // Enregistrer au format ISO 8601
                'lot_numero' => $request->lot_numero,
                'quantite_disponible' => intval($request->quantite_disponible)
            ]);
    
            // Créer le document ravitaillement
            $ravitaillementRef = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->add([
                    'date_ravitaillement' => new \Google\Cloud\Core\Timestamp($dateRavitaillement),
                    'fournisseur' => $request->fournisseur,
                    'lot_numero' => $request->lot_numero,
                    'produit_nom' => $produitNom, // Stocker le nom du produit directement
                    'quantite_ravitailler' => intval($request->quantite_disponible)
                ]);
    
            // Mettre à jour la quantité totale du produit
            $stocksSnapshot = $produitRef->collection('stock')->documents();
            $quantiteTotale = 0;
    
            foreach ($stocksSnapshot as $stock) {
                $quantiteTotale += $stock->data()['quantite_disponible'];
            }
    
            $produitRef->update([
                ['path' => 'quantite_en_stock', 'value' => $quantiteTotale]
            ]);
    
            return redirect()->route('ravitaillements.index')
                ->with('success', 'Ravitaillement enregistré avec succès!');
    
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement du ravitaillement.')
                ->withInput();
        }
    }
    
    
    
    public function destroy($id)
    {
        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->back()->with('error', 'ID de la pharmacie introuvable.');
        }

        // Vérifiez que l'ID du document n'est pas vide ou invalide
        if (empty($pharmacieId) || strpos($pharmacieId, '/') !== false) {
            return redirect()->back()->with('error', 'ID de la pharmacie invalide.');
        }

        try {
            // Supprimer le document ravitaillement
            $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->document($id)
                ->delete();

            return redirect()->route('ravitaillements.index')
                ->with('success', 'Ravitaillement supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du ravitaillement.');
        }
    }
}
