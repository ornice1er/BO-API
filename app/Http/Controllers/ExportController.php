<?php

namespace App\Http\Controllers;

use App\Http\Requests\Export\ExportRequest;
use App\Utilities\Common;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    public $letters = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    /**
     * @OA\POST(
     *      path="/generate-pdf",
     *      operationId="Generate PDF",
     *      tags={"Export"},
     *      security={{"JWT":{}}},
     *      summary="Générer un document PDF",
     *      description="Ce service permet de générer un fichier PDF à partir des données fournies.",
     *
     *      @OA\RequestBody(
     *          description="Données pour générer le PDF",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ExportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="PDF généré avec succès",
     *
     *          @OA\JsonContent(type="string", example="document.pdf")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Requête invalide"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function generatePDF(ExportRequest $request)
    {
        try {

            $pdf = Pdf::loadView('export.pdf', $request->validated());
            $pdfContent = $pdf->output(); // Génère le contenu du PDF
            $base64 = 'data:application/pdf;base64,'.base64_encode($pdfContent);

            return $base64;
        } catch (\Throwable $th) {
            return Common::error('Erreur lors de la génération du PDF', ['error' => $th->getMessage()]);
        }
    }

    /**
     * @OA\POST(
     *      path="/generate-excel",
     *      operationId="Generate Excel",
     *      tags={"Export"},
     *      security={{"JWT":{}}},
     *      summary="Générer un document Excel",
     *      description="Ce service permet de générer un fichier PDF à partir des données fournies.",
     *
     *      @OA\RequestBody(
     *          description="Données pour générer le PDF",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/ExportCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="PDF généré avec succès",
     *
     *          @OA\JsonContent(type="string", example="document.pdf")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Requête invalide"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function generateExcel(ExportRequest $request)
    {
        // try {

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($request->title, 0, 31));
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->setCellValue('A1', $request->title);
        $sheet->setCellValue('A2', $request->subtitle);
        $sheet->setCellValue('A3', $request->description);

        foreach ($request->table_header as $key => $th) {
            $sheet->getColumnDimension($this->letters[$key])->setWidth(30);
            $sheet->setCellValue($this->letters[$key].'4', $th);
        }

        $i = 5;
        foreach ($request->table_body as $tb) {
            foreach ($tb as $key => $value) {
                $sheet->setCellValue($this->letters[$key]."$i", $value);
            }
            $i++;
        }
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->setCellValue('A'.$i, $request->description);

        $type = 'xlsx';
        $filename = date('d_m_Y_h_i_s_').$request->title.'.'.$type;
        if ($type == 'xlsx') {
            $writer = new Xlsx($spreadsheet);
        } elseif ($type == 'xls') {
            $writer = new Xls($spreadsheet);
        }

        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        // Convertir en base64
        $base64 = base64_encode($excelContent);

        $base64 = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.$base64;

        return $base64;
        // } catch (\Throwable $th) {
        //     return Common::error('Erreur lors de la génération du PDF', ['error' => $th->getMessage()]);
        // }
    }
}
