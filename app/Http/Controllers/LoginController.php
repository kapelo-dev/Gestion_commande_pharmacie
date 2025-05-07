<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class LoginController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required',
            'mot_de_passe' => 'required'
        ]);

        try {
            // Log au début de la tentative de connexion
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Tentative de connexion pour : " . $request->identifiant . "\n", 
                FILE_APPEND);
            
            // Récupérer toutes les pharmacies
            $pharmaciesSnapshot = $this->firebaseService->getDocuments('pharmacies');
            
            // Log du nombre de pharmacies trouvées
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Nombre de pharmacies trouvées : " . iterator_count($pharmaciesSnapshot) . "\n", 
                FILE_APPEND);
            
            foreach ($pharmaciesSnapshot as $pharmacie) {
                $pharmacieId = $pharmacie->id();
                
                // Log de la pharmacie en cours de vérification
                file_put_contents(storage_path('logs/auth.log'), 
                    date('Y-m-d H:i:s') . " - Vérification de la pharmacie : " . $pharmacieId . "\n", 
                    FILE_APPEND);

                // Récupérer les pharmaciens de la pharmacie
                $pharmaciensSnapshot = $this->firebaseService->getSubCollection('pharmacies', $pharmacieId, 'pharmaciens');
                
                foreach ($pharmaciensSnapshot as $pharmacien) {
                    $data = $pharmacien->data();
                    
                    // Log des données du pharmacien
                    file_put_contents(storage_path('logs/auth.log'), 
                        date('Y-m-d H:i:s') . " - Vérification du pharmacien : " . ($data['identifiant'] ?? 'inconnu') . "\n", 
                        FILE_APPEND);

                    if (isset($data['identifiant']) && 
                        isset($data['mot_de_passe']) && 
                        $data['identifiant'] === $request->identifiant) {
                        
                        // Log de la correspondance d'identifiant
                        file_put_contents(storage_path('logs/auth.log'), 
                            date('Y-m-d H:i:s') . " - Identifiant trouvé, vérification du mot de passe\n", 
                            FILE_APPEND);
                        
                        if (Hash::check($request->mot_de_passe, $data['mot_de_passe'])) {
                            // Log du succès de l'authentification
                            file_put_contents(storage_path('logs/auth.log'), 
                                date('Y-m-d H:i:s') . " - Authentification réussie\n", 
                                FILE_APPEND);

                            // Créer un utilisateur temporaire pour l'authentification
                            $user = new User([
                                'id' => $pharmacien->id(),
                                'name' => $data['nom'] ?? '',
                                'email' => $data['identifiant'],
                                'password' => $data['mot_de_passe'],
                                'telephone' => $data['telephone'] ?? null,
                                'role' => $data['role'] ?? 'pharmacien'
                            ]);

                            // Log des données de l'utilisateur
                            file_put_contents(storage_path('logs/auth.log'), 
                                date('Y-m-d H:i:s') . " - Création de l'utilisateur : " . json_encode([
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'role' => $user->role
                                ]) . "\n", 
                                FILE_APPEND);

                            // Stocker les informations dans la session
                            session([
                                'pharmacie_id' => $pharmacieId,
                                'pharmacien_id' => $pharmacien->id(),
                                'pharmacien_prenom' => $data['prenom'] ?? '',
                                'pharmacien_role' => $data['role'] ?? 'pharmacien'
                            ]);

                            // Log des données de session
                            file_put_contents(storage_path('logs/auth.log'), 
                                date('Y-m-d H:i:s') . " - Données de session enregistrées : " . json_encode(session()->all()) . "\n", 
                                FILE_APPEND);

                            // Authentifier l'utilisateur
                            Auth::login($user);

                            // Log de la redirection
                            file_put_contents(storage_path('logs/auth.log'), 
                                date('Y-m-d H:i:s') . " - Tentative de redirection vers le dashboard avec les données : " . json_encode([
                                    'auth_check' => Auth::check(),
                                    'session_data' => session()->all(),
                                    'intended_url' => redirect()->intended()->getTargetUrl()
                                ]) . "\n", 
                                FILE_APPEND);

                            return redirect()->route('dashboard')
                                ->with('success', 'Connexion réussie');
                        } else {
                            // Log de l'échec de mot de passe
                            file_put_contents(storage_path('logs/auth.log'), 
                                date('Y-m-d H:i:s') . " - Mot de passe incorrect pour : " . $request->identifiant . "\n", 
                                FILE_APPEND);
                        }
                    }
                }
            }

            // Log de l'échec de connexion
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Échec de connexion : aucun utilisateur trouvé pour " . $request->identifiant . "\n", 
                FILE_APPEND);
            
            return redirect()->route('login')
                ->withErrors(['identifiant' => 'Identifiant ou mot de passe incorrect'])
                ->withInput($request->except('mot_de_passe'));

        } catch (\Exception $e) {
            // Log de l'erreur
            file_put_contents(storage_path('logs/auth.log'), 
                date('Y-m-d H:i:s') . " - Erreur : " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", 
                FILE_APPEND);
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Une erreur est survenue lors de la connexion'])
                ->withInput($request->except('mot_de_passe'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Déconnexion réussie');
    }
}
