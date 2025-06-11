<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use App\Models\ActionLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;

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
    
        if (empty($pharmacieId)) {
            return redirect()->route('dashboard')->withErrors('Pharmacie non sélectionnée.');
        }
    
        // Vérifiez que l'ID du document n'est pas vide ou invalide
        if (empty($pharmacieId) || strpos($pharmacieId, '/') !== false) {
            return redirect()->route('dashboard')->with('error', 'ID de la pharmacie invalide.');
        }
    
        try {
            // Récupérer les ravitaillements depuis Firestore
            $ravitaillements = [];
            $ravitaillementsRef = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->orderBy('date_ravitaillement', 'DESC')
                ->documents();
    
            foreach ($ravitaillementsRef as $doc) {
                $data = $doc->data();
                
                // Récupérer la date du ravitaillement
                $date = null;
                if (isset($data['date_ravitaillement'])) {
                    if ($data['date_ravitaillement'] instanceof \Google\Cloud\Core\Timestamp) {
                        $date = $data['date_ravitaillement']->get()->format('d/m/Y');
                    } else {
                        $date = date('d/m/Y', strtotime($data['date_ravitaillement']));
                    }
                } else {
                    $date = date('d/m/Y');
                }

                $ravitaillements[] = [
                    'id' => $doc->id(),
                    'date' => $date,
                    'fichier' => isset($data['fichier_excel']) ? $data['fichier_excel'] : null,
                    'fournisseur' => isset($data['fournisseur']) ? $data['fournisseur'] : 'N/A',
                    'montant_total' => isset($data['montant_total']) ? number_format($data['montant_total'], 2) . ' F' : '0.00 F'
                ];
            }
    
            return view('ravitaillements.index', compact('ravitaillements'));
    
        } catch (\Exception $e) {
            Log::error('Erreur dans RavitaillementController@index: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de la récupération des ravitaillements.');
        }
    }
    
    

    public function store(Request $request)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        $request->validate([
            'fournisseur' => 'required|string',
            'lot_numero' => 'required|string',
            'produit_ravitaille' => 'required|string',
            'date_expiration' => 'required|date',
            'quantite_disponible' => 'required|integer|min:1',
            'prix_achat' => 'required|numeric|min:0',
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
                'date_expiration' => $dateExpirationISO,
                'lot_numero' => $request->lot_numero,
                'quantite_disponible' => intval($request->quantite_disponible),
                'prix_achat' => floatval($request->prix_achat)
            ]);
    
            // Créer le document ravitaillement avec la nouvelle structure
            $ravitaillementRef = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->add([
                    'date_ravitaillement' => new \Google\Cloud\Core\Timestamp($dateRavitaillement),
                    'fournisseur' => $request->fournisseur,
                    'produits' => [
                        [
                            'id_produit' => $request->produit_id,
                            'nom_produit' => $produitNom,
                            'lot_numero' => $request->lot_numero,
                            'quantite' => intval($request->quantite_disponible),
                            'prix_achat' => floatval($request->prix_achat),
                            'prix_total' => floatval($request->prix_achat) * intval($request->quantite_disponible),
                            'date_expiration' => $dateExpirationISO
                        ]
                    ],
                    'montant_total' => floatval($request->prix_achat) * intval($request->quantite_disponible)
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
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

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

    public function preview($id)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        try {
            $pharmacieId = session('pharmacie_id');
            
            if (!$pharmacieId) {
                return response()->json(['error' => 'ID de la pharmacie non disponible'], 403);
            }

            // Récupérer le document ravitaillement
            $ravitaillementRef = $this->firestore
                ->collection('pharmacies')
                ->document($pharmacieId)
                ->collection('ravitaillements')
                ->document($id);

            $ravitaillement = $ravitaillementRef->snapshot();

            if (!$ravitaillement->exists()) {
                return response()->json(['error' => 'Ravitaillement non trouvé'], 404);
            }

            $data = $ravitaillement->data();
            
            if (isset($data['fichier_excel'])) {
                // C'est un ravitaillement importé via Excel
                return view('ravitaillements.partials.preview-details', [
                    'preview_data' => $data['donnees_excel'] ?? [],
                    'date_ravitaillement' => isset($data['date_creation']) 
                        ? date('d/m/Y', strtotime($data['date_creation'])) 
                        : date('d/m/Y')
                ]);
            } else {
                // C'est un ravitaillement manuel
                return view('ravitaillements.partials.preview-details', [
                    'ravitaillement' => [
                        'date' => isset($data['date_creation']) 
                            ? date('d/m/Y', strtotime($data['date_creation'])) 
                            : date('d/m/Y'),
                        'fichier' => $data['fichier_excel'] ?? null,
                        'fournisseur' => $data['fournisseur'] ?? '',
                        'lot_numero' => $data['lot_numero'] ?? '',
                        'produit' => $data['produit_nom'] ?? '',
                        'quantite' => $data['quantite_ravitailler'] ?? 0
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans preview: ' . $e->getMessage());
            return response()->json([
                'error' => 'Une erreur est survenue lors de la récupération des détails'
            ], 500);
        }
    }

    public function previewImport(Request $request)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        try {
            Log::info('Début de previewImport');
            Log::info('Files reçus:', $request->allFiles());
            
            if (!$request->hasFile('fichier_excel')) {
                Log::error('Aucun fichier dans la requête avec la clé "fichier_excel"');
                return response()->json([
                    'error' => 'Aucun fichier fourni',
                    'debug' => [
                        'has_files' => $request->hasFile('fichier_excel'),
                        'all_files' => $request->allFiles()
                    ]
                ], 422);
            }

            $file = $request->file('fichier_excel');
            Log::info('Fichier reçu:', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);
            
            // Vérifier que le fichier est bien un fichier Excel
            $allowedMimes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                'application/vnd.ms-excel', // xls
                'application/octet-stream' // Certains systèmes envoient ce type pour les fichiers Excel
            ];
            
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                Log::error('Type de fichier invalide:', ['mime' => $file->getMimeType()]);
                return response()->json([
                    'error' => 'Le fichier doit être au format Excel (.xlsx ou .xls)',
                    'mime_type' => $file->getMimeType()
                ], 422);
            }

            // Créer un dossier temporaire s'il n'existe pas
            $tempPath = public_path('temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0777, true);
            }

            // Générer un nom unique pour le fichier
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $filePath = $tempPath . '/' . $fileName;
            
            // Déplacer le fichier vers le dossier temporaire
            $file->move($tempPath, $fileName);

            // Lire le fichier Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Vérifier les en-têtes
            $headers = array_map('strtolower', $rows[0]);
            $required = ['id_produit', 'lot_numero', 'date_expiration', 'quantite_disponible', 'prix_achat', 'prix_unitaire'];
            
            if (count(array_intersect($required, $headers)) !== count($required)) {
                unlink($filePath);
                return response()->json(['error' => 'Format de fichier invalide. Veuillez utiliser le modèle fourni.'], 422);
            }

            // Préparer les données pour la prévisualisation
            $preview = [];
            $errors = [];
            $importData = [];
            
            // Ignorer la ligne d'en-tête
            for ($i = 1; $i < count($rows); $i++) {
                $row = array_combine($headers, $rows[$i]);
                
                try {
                    // Préparer les données de base
                    $previewRow = [
                        'id_produit' => $row['id_produit'],
                        'lot_numero' => $row['lot_numero'],
                        'date_expiration' => $row['date_expiration'],
                        'quantite_disponible' => intval($row['quantite_disponible']),
                        'prix_achat' => floatval($row['prix_achat']),
                        'prix_unitaire' => floatval($row['prix_unitaire']),
                        'produit_trouve' => false,
                        'nom_produit' => '-',
                        'variation_prix' => '-'
                    ];

                    // Vérifier si le produit existe
                    $produitRef = $this->firestore
                        ->collection('pharmacies')
                        ->document($this->pharmacieId)
                        ->collection('produits')
                        ->document($row['id_produit']);

                    $produit = $produitRef->snapshot();
                    
                    if ($produit->exists()) {
                        $produitData = $produit->data();
                        $prixActuel = $produitData['prix_unitaire'] ?? 0;
                        
                        // Calculer la variation de prix
                        $nouveauPrix = floatval($row['prix_unitaire']);
                        $variationPrix = $prixActuel > 0 ? (($nouveauPrix - $prixActuel) / $prixActuel) * 100 : 100;

                        // Mettre à jour les données du produit trouvé
                        $previewRow['produit_trouve'] = true;
                        $previewRow['nom_produit'] = $produitData['nom'];
                        $previewRow['variation_prix'] = round($variationPrix, 2);
                        $previewRow['prix_actuel'] = $prixActuel;
                    } else {
                        $errors["ligne_" . ($i + 1)][] = "Produit non trouvé";
                    }

                    $preview[] = $previewRow;
                    $importData[] = $previewRow;

                } catch (\Exception $e) {
                    $errors["ligne_" . ($i + 1)][] = $e->getMessage();
                }
            }

            // Stocker les données d'importation en session
            session(['import_data' => $importData]);
            session(['temp_file_path' => $filePath]);

            return response()->json([
                'preview' => $preview,
                'errors' => $errors,
                'total_rows' => count($rows) - 1,
                'valid_rows' => count(array_filter($preview, function($row) { return $row['produit_trouve']; }))
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur preview import: ' . $e->getMessage());
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processImport(Request $request)
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        try {
            $importData = session('import_data');
            $filePath = session('temp_file_path');

            if (!$importData) {
                throw new \Exception('Aucune donnée à importer');
            }

            // Préparer les données pour le ravitaillement
            $produits = [];
            $montantTotal = 0;

            foreach ($importData as $row) {
                // Mettre à jour le produit
                $produitRef = $this->firestore
                    ->collection('pharmacies')
                    ->document($this->pharmacieId)
                    ->collection('produits')
                    ->document($row['id_produit']);

                // Ajouter le stock
                $produitRef->collection('stock')->add([
                    'lot_numero' => $row['lot_numero'],
                    'date_expiration' => $row['date_expiration'],
                    'quantite_disponible' => $row['quantite_disponible'],
                    'prix_achat' => $row['prix_achat'],
                    'date_creation' => date('Y-m-d H:i:s')
                ]);

                // Mettre à jour le prix unitaire du produit
                $produitRef->update([
                    ['path' => 'prix_unitaire', 'value' => $row['prix_unitaire']]
                ]);

                // Mettre à jour la quantité totale
                $this->updateProduitQuantite($this->pharmacieId, $row['id_produit']);

                // Calculer le prix total pour ce produit
                $prixTotal = floatval($row['prix_achat']) * intval($row['quantite_disponible']);
                $montantTotal += $prixTotal;

                // Ajouter aux produits du ravitaillement
                $produits[] = [
                    'id_produit' => $row['id_produit'],
                    'nom_produit' => $row['nom_produit'],
                    'lot_numero' => $row['lot_numero'],
                    'quantite' => intval($row['quantite_disponible']),
                    'prix_achat' => floatval($row['prix_achat']),
                    'prix_total' => $prixTotal,
                    'date_expiration' => $row['date_expiration']
                ];
            }

            // Créer l'enregistrement du ravitaillement avec la nouvelle structure
            $this->firestore
                ->collection('pharmacies')
                ->document($this->pharmacieId)
                ->collection('ravitaillements')
                ->add([
                    'date_ravitaillement' => date('Y-m-d\TH:i:s\Z'),
                    'fichier_excel' => basename($filePath),
                    'donnees_excel' => $importData,
                    'produits' => $produits,
                    'montant_total' => $montantTotal
                ]);

            // Nettoyer la session
            session()->forget(['import_data', 'temp_file_path']);

            return response()->json([
                'success' => true,
                'message' => 'Importation réussie'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur process import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'importation: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateProduitQuantite($pharmacieId, $produitId)
    {
        $stocksSnapshot = $this->firestore
            ->collection('pharmacies')
            ->document($pharmacieId)
            ->collection('produits')
            ->document($produitId)
            ->collection('stock')
            ->documents();

        $quantiteTotale = 0;
        foreach ($stocksSnapshot as $stock) {
            $quantiteTotale += $stock->data()['quantite_disponible'];
        }

        $this->firestore
            ->collection('pharmacies')
            ->document($pharmacieId)
            ->collection('produits')
            ->document($produitId)
            ->update([
                ['path' => 'quantite_en_stock', 'value' => $quantiteTotale]
            ]);
    }

    public function showImport()
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        return view('ravitaillements.import');
    }

    public function downloadTemplate()
    {
        if ($response = $this->checkGerantRole()) {
            return $response;
        }

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Définir les en-têtes
            $headers = ['id_produit', 'lot_numero', 'date_expiration', 'quantite_disponible', 'prix_achat', 'prix_unitaire'];
            $sheet->fromArray([$headers], NULL, 'A1');

            // Mettre en forme les en-têtes
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Ajuster la largeur des colonnes
            foreach(range('A','F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Exemple de données
            $exampleData = [
                ['AZITHROMYCINE', 'LOT2024001', '2025-12-31', '100', '1000', '9000'],
                ['AMLODIPINE', 'LOT2024001', '2025-12-12', '150', '1000', '1500']
            ];
            $sheet->fromArray($exampleData, NULL, 'A2');

            // Appliquer un style pour les exemples
            $exampleStyle = [
                'font' => ['italic' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2']
                ]
            ];
            $sheet->getStyle('A2:F3')->applyFromArray($exampleStyle);

            // Créer le fichier Excel
            $writer = new Xlsx($spreadsheet);
            $filename = 'modele_ravitaillement.xlsx';
            $path = public_path('temp/' . $filename);
            
            if (!file_exists(public_path('temp'))) {
                mkdir(public_path('temp'), 0777, true);
            }
            
            $writer->save($path);

            return response()->download($path, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Erreur génération template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du modèle: ' . $e->getMessage()
            ], 500);
        }
    }
}
