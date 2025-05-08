<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class PreparationAchatsController extends Controller
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

        if (!$this->pharmacieId) {
            abort(403, 'ID de la pharmacie non disponible.');
        }
    }

    public function index()
    {
        try {
            $produitsRef = $this->firestore->collection('pharmacies')
                ->document($this->pharmacieId)
                ->collection('produits');
            
            $produitsSnapshot = $produitsRef->documents();
            
            $produits = [];
            foreach ($produitsSnapshot as $document) {
                $produits[] = [
                    'id' => $document->id(),
                    'nom' => $document->data()['nom'] ?? 'Sans nom'
                ];
            }

            return view('preparation_achats.index', compact('produits'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la récupération des produits: ' . $e->getMessage());
        }
    }
} 