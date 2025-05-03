<?php
namespace App\Http\Controllers;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Assurez-vous d'avoir un modèle User

class LoginController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $identifiant = $request->input('identifiant');
        $mot_de_passe = $request->input('mot_de_passe');

        // Récupérer toutes les pharmacies
        $pharmaciesSnapshot = $this->firebaseService->getDocuments('pharmacies');

        foreach ($pharmaciesSnapshot as $pharmacie) {
            $pharmacieData = $pharmacie->data();
            $pharmacieId = $pharmacie->id();

            // Récupérer les pharmaciens de la pharmacie
            $pharmaciensSnapshot = $this->firebaseService->getSubCollection('pharmacies', $pharmacieId, 'pharmaciens');

            foreach ($pharmaciensSnapshot as $pharmacien) {
                $data = $pharmacien->data();
                if (isset($data['identifiant']) && isset($data['mot_de_passe']) && $data['identifiant'] === $identifiant && Hash::check($mot_de_passe, $data['mot_de_passe'])) {
                    // Authentification réussie
                    // Stocker l'ID de la pharmacie et le prénom du pharmacien dans la session
                    session(['pharmacie_id' => $pharmacieId, 'pharmacien_prenom' => $data['prenom']]);

                    // Créer un utilisateur temporaire pour l'authentification
                    $user = new User();
                    $user->id = $pharmacien->id();
                    $user->name = $data['nom'];
                    $user->password = $data['mot_de_passe'];
                    $user->telephone = $data['telephone'];
                    $user->role = $data['role'];
                    

                    // Authentifier l'utilisateur
                    Auth::login($user);

                    return redirect()->route('dashboard')->with('success', 'Connexion réussie');
                }
            }
        }

        // Authentification échouée
        return redirect()->route('login')->with('error', 'Identifiant ou mot de passe incorrect');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Déconnexion réussie');
    }
}
