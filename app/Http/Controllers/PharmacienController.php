<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;

class PharmacienController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    protected function checkGerantRole()
    {
        if (session('pharmacien_role') !== 'gérant') {
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé. Seuls les gérants peuvent accéder à cette section.');
        }
        return null;
    }

    public function index()
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('login')->withErrors('Pharmacie non sélectionnée.');
        }

        $pharmaciensSnapshot = $this->firebaseService->getSubCollection('pharmacies', $pharmacieId, 'pharmaciens');

        $pharmaciens = [];
        foreach ($pharmaciensSnapshot as $pharmacien) {
            $data = $pharmacien->data();
            $pharmaciens[] = [
                'id' => $pharmacien->id(),
                'nom' => $data['nom'] ?? 'Nom inconnu',
                'prenom' => $data['prenom'] ?? 'Prénom inconnu',
                'identifiant' => $data['identifiant'] ?? 'Identifiant inconnu',
                'telephone' => $data['telephone'] ?? 'Téléphone inconnu',
                'role' => $data['role'] ?? 'Rôle inconnu',
            ];
        }

        return view('pharmaciens.index', compact('pharmaciens'));
    }

    public function create()
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }
        return view('pharmaciens.create');
    }

    public function store(Request $request)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'identifiant' => 'required|string',
            'telephone' => 'required|string',
            'mot_de_passe' => 'required|string|min:8',
            'role' => 'required|string',
        ]);

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('login')->withErrors('Pharmacie non sélectionnée.');
        }

        // Vérification de l'unicité de l'identifiant
        $existingPharmacien = $this->firebaseService->getDocumentByField('pharmacies', $pharmacieId, 'pharmaciens', 'identifiant', $request->identifiant);
        if ($existingPharmacien) {
            return redirect()->back()->withErrors('Identifiant déjà utilisé.');
        }

        $data = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'identifiant' => $request->identifiant,
            'telephone' => $request->telephone,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
            'role' => $request->role,
        ];

        $this->firebaseService->addDocument('pharmacies', $pharmacieId, 'pharmaciens', $data);

        return redirect()->route('pharmaciens.index')->with('success', 'Pharmacien ajouté avec succès.');
    }

    public function update(Request $request, $pharmacienId)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'identifiant' => 'required|string',
            'telephone' => 'required|string',
            'role' => 'required|string',
        ]);

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('login')->withErrors('Pharmacie non sélectionnée.');
        }

        // Vérification de l'unicité de l'identifiant
        $existingPharmacien = $this->firebaseService->getDocumentByField('pharmacies', $pharmacieId, 'pharmaciens', 'identifiant', $request->identifiant);
        if ($existingPharmacien && $existingPharmacien->id() != $pharmacienId) {
            return redirect()->back()->withErrors('Identifiant déjà utilisé.');
        }

        // Formater les données pour la mise à jour Firestore
        $updates = [
            ['path' => 'nom', 'value' => $request->nom],
            ['path' => 'prenom', 'value' => $request->prenom],
            ['path' => 'identifiant', 'value' => $request->identifiant],
            ['path' => 'telephone', 'value' => $request->telephone],
            ['path' => 'role', 'value' => $request->role],
        ];

        $this->firebaseService->updateDocument('pharmacies', $pharmacieId, 'pharmaciens', $pharmacienId, $updates);

        return redirect()->route('pharmaciens.index')->with('success', 'Pharmacien mis à jour avec succès.');
    }

    public function edit($pharmacienId)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('login')->withErrors('Pharmacie non sélectionnée.');
        }

        $pharmacienSnapshot = $this->firebaseService->getDocument('pharmacies', $pharmacieId, 'pharmaciens', $pharmacienId);

        if (!$pharmacienSnapshot->exists()) {
            return redirect()->route('pharmaciens.index')->withErrors('Pharmacien non trouvé.');
        }

        $data = $pharmacienSnapshot->data();

        return view('pharmaciens.edit', compact('pharmacienId', 'data'));
    }

    public function destroy($pharmacienId)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $pharmacieId = session('pharmacie_id');

        if (!$pharmacieId) {
            return redirect()->route('login')->withErrors('Pharmacie non sélectionnée.');
        }

        $this->firebaseService->deleteDocument('pharmacies', $pharmacieId, 'pharmaciens', $pharmacienId);

        return redirect()->route('pharmaciens.index')->with('success', 'Pharmacien supprimé avec succès.');
    }
}
