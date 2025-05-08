<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

    public function generateExcel(Request $request)
    {
        try {
            $products = $request->input('products');

            // Créer un nouveau fichier Excel
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

            // Ajouter les données
            $row = 2;
            foreach ($products as $product) {
                $sheet->setCellValue('A' . $row, $product['id']);
                $sheet->setCellValue('D' . $row, $product['quantite']);
                $row++;
            }

            // Ajuster la largeur des colonnes
            foreach(range('A','F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Créer le writer
            $writer = new Xlsx($spreadsheet);
            $fileName = 'preparation_achats_' . date('Y-m-d_His') . '.xlsx';
            
            ob_start();
            $writer->save('php://output');
            $content = ob_get_contents();
            ob_end_clean();

            return response($content)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Length', strlen($content))
                ->header('Cache-Control', 'max-age=0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du fichier Excel: ' . $e->getMessage()
            ], 500);
        }
    }
} 