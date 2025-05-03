<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class ProduitController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $credentials = config('firebase.credentials');

        $this->firestore = new FirestoreClient([
            'keyFilePath' => $credentials,
        ]);
    }

    // Afficher la liste des produits
    public function index()
    {
        $pharmacieId = session('pharmacie_id');

        if (empty($pharmacieId)) {
            return redirect()->route('dashboard')->withErrors('Pharmacie non sélectionnée.');
        }

        try {
            // Récupérer les produits
            $produitsSnapshot = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('produits')
                ->documents();

            // Convertir les produits en tableau
            $produits = [];
            foreach ($produitsSnapshot as $produit) {
                $data = $produit->data();

                $produits[] = [
                    'id' => $produit->id(),
                    'nom' => $data['nom'] ?? '',
                    'description' => $data['description'] ?? '',
                    'quantite_en_stock' => $data['quantite_en_stock'] ?? 0,
                    'sur_ordonnance' => $data['sur_ordonnance'] ?? false,
                    'prix_unitaire' => $data['prix_unitaire'] ?? 0
                ];
            }

            return view('produits.index', [
                'produits' => $produits,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur Firestore : ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->withErrors('Erreur lors de la récupération des données : ' . $e->getMessage());
        }
    }

    // Ajouter un produit
    public function create()
    {
        return view('produits.create');
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'quantite_en_stock' => 'required|integer', // Assurez-vous que la valeur est un entier
            'sur_ordonnance' => 'required|in:true,false', // Assurez-vous que la valeur est soit 'true' soit 'false'
            'prix_unitaire' => 'required|numeric|min:0',
            'id' => 'nullable|string', // L'ID est optionnel et doit être une chaîne de caractères si fourni
        ]);

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->back()->with('error', 'ID de la pharmacie introuvable.');
        }

        // Vérifier si un produit avec le même nom existe déjà (insensible à la casse)
        $produitsRef = $this->firestore
            ->collection('pharmacies')
            ->document($pharmacieId)
            ->collection('produits');

        $produits = $produitsRef->documents();
        foreach ($produits as $produit) {
            if (strtolower($produit->data()['nom']) === strtolower($request->nom)) {
                return redirect()->back()->with('error', 'Un produit avec ce nom existe déjà.');
            }
        }

        // Convertir la valeur de 'sur_ordonnance' en booléen
        $surOrdonnance = filter_var($request->sur_ordonnance, FILTER_VALIDATE_BOOLEAN);

        // Ajouter le produit
        if ($request->has('id') && !empty($request->id)) {
            $produitsRef->document($request->id)->set([
                'nom' => $request->nom,
                'description' => $request->description,
                'quantite_en_stock' => intval($request->quantite_en_stock), // Assurez-vous que la valeur est un entier
                'sur_ordonnance' => $surOrdonnance, // Utiliser la valeur booléenne
                'prix_unitaire' => floatval($request->prix_unitaire),
            ]);
        } else {
            $produitsRef->add([
                'nom' => $request->nom,
                'description' => $request->description,
                'quantite_en_stock' => intval($request->quantite_en_stock), // Assurez-vous que la valeur est un entier
                'sur_ordonnance' => $surOrdonnance, // Utiliser la valeur booléenne
                'prix_unitaire' => floatval($request->prix_unitaire),
            ]);
        }

        return redirect()->route('produits.index')->with('success', 'Produit ajouté avec succès!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erreur lors de l\'ajout du produit: ' . $e->getMessage());
    }
}

    
    
    
public function update(Request $request, $produitId)
{
    try {
        $request->validate([
            'nom' => 'required|string',
            'description' => 'required|string',
            'quantite_en_stock' => 'required|integer', // Assurez-vous que la valeur est un entier
            'sur_ordonnance' => 'required|in:true,false', // Assurez-vous que la valeur est soit 'true' soit 'false'
            'prix_unitaire' => 'required|numeric|min:0',
        ]);

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('configure-pharmacie')->with('error', 'ID de la pharmacie introuvable. Veuillez le configurer.');
        }

        // Vérifier si un autre produit avec le même nom existe déjà (insensible à la casse)
        $produitsRef = $this->firestore
            ->collection('pharmacies')
            ->document($pharmacieId)
            ->collection('produits');

        $produits = $produitsRef->documents();
        foreach ($produits as $produit) {
            if ($produit->id() !== $produitId && // Exclure le produit en cours de modification
                strtolower($produit->data()['nom']) === strtolower($request->nom)) {
                return redirect()->back()->with('error', 'Un produit avec ce nom existe déjà.');
            }
        }

        // Convertir la valeur de 'sur_ordonnance' en booléen
        $surOrdonnance = filter_var($request->sur_ordonnance, FILTER_VALIDATE_BOOLEAN);

        // Mettre à jour le produit
        $produitsRef->document($produitId)->update([
            ['path' => 'nom', 'value' => $request->nom],
            ['path' => 'description', 'value' => $request->description],
            ['path' => 'quantite_en_stock', 'value' => intval($request->quantite_en_stock)], // Assurez-vous que la valeur est un entier
            ['path' => 'prix_unitaire', 'value' => floatval($request->prix_unitaire)],
            ['path' => 'sur_ordonnance', 'value' => $surOrdonnance], // Utiliser la valeur booléenne
        ]);

        return redirect()->route('produits.index')->with('success', 'Produit mis à jour avec succès!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
    }
}

    

    // Modifier un produit
    public function edit($produitId)
    {
        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('configure-pharmacie')->with('error', 'ID de la pharmacie introuvable. Veuillez le configurer.');
        }

        // Récupérer le produit depuis Firestore
        $produitRef = $this->firestore->collection('pharmacies')->document($pharmacieId)->collection('produits')->document($produitId);
        $produit = $produitRef->snapshot();

        return view('produits.edit', compact('produit'));
    }



    // Supprimer un produit
    public function destroy($produitId)
    {
        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('configure-pharmacie')->with('error', 'ID de la pharmacie introuvable. Veuillez le configurer.');
        }

        // Supprimer le produit de Firestore
        $produitsRef = $this->firestore->collection('pharmacies')->document($pharmacieId)->collection('produits')->document($produitId);
        $produitsRef->delete();

        return redirect()->route('produits.index')->with('success', 'Produit supprimé avec succès!');
    }
}
