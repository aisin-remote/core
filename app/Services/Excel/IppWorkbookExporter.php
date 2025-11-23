<?php

namespace App\Services\Excel;

use App\Models\Ipp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IppWorkbookExporter
{
    public function __construct(
        protected RenderIppSheet $ippRenderer,
        protected RenderActivityPlanSheet $apRenderer,
    ) {}

    public function export(int $ippId): BinaryFileResponse
    {
        $tmp = null;

        try {
            // Load template IPP
            $tplIpp = public_path('assets/file/Template IPP.xlsx');
            abort_unless(is_file($tplIpp), 500, 'Template IPP tidak ditemukan.');
            $wb = IOFactory::load($tplIpp);

            // Render sheet pertama (IPP)
            $this->ippRenderer->render($wb, $ippId);

            // Load template Activity Plan
            $tplAp = public_path('assets/file/Template Activity Plan.xlsx');
            abort_unless(is_file($tplAp), 500, 'Template Activity Plan tidak ditemukan.');

            $apWb = IOFactory::load($tplAp);
            $apTemplateSheet = $apWb->getSheetByName('Activity Plan') ?? $apWb->getSheet(0);

            $sheetIndex = $wb->getSheetCount();
            $wb->addExternalSheet($apTemplateSheet, $sheetIndex);

            $apSheet = $wb->getSheet($sheetIndex);
            $apSheet->setTitle('Activity Plan');

            $this->apRenderer->render($apSheet, $ippId);

            logger('Sheet details before rendering Activity Plan:', [
                'sheet_title' => $apSheet->getTitle(),
                'sheet_index' => $wb->getIndex($apSheet),
                'total_sheets' => $wb->getSheetCount(),
                'highest_row' => $apSheet->getHighestRow(),
                'highest_column' => $apSheet->getHighestColumn(),
            ]);

            // Set active sheet ke IPP form
            $wb->setActiveSheetIndex(0);

            // Cleanup
            $apWb->disconnectWorksheets();
            unset($apWb, $apTemplateSheet, $newSheet);

            // Save file
            $tmp = tempnam(sys_get_temp_dir(), 'ipp_');
            if ($tmp === false) {
                abort(500, 'Gagal membuat file sementara.');
            }

            $writer = new Xlsx($wb);
            $writer->save($tmp);

            $ipp = Ipp::with('employee')->findOrFail($ippId);
            $owner = (string) ($ipp->nama ?? $ipp->employee?->name ?? 'user');
            $fileName = 'IPP_' . $ipp->on_year . '_' . Str::slug($owner) . '.xlsx';

            return response()->download(
                $tmp,
                $fileName,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Gagal mengekspor IPP', [
                'ipp_id'    => $ippId,
                'message'   => $e->getMessage(),
                'exception' => $e,
            ]);

            if ($tmp && is_file($tmp)) {
                @unlink($tmp);
            }

            abort(500, 'Terjadi kesalahan saat mengekspor IPP.');
        }
    }

    /**
     * Copy semua konten dari sheet sumber ke sheet target
     */
    private function copySheetContent(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $source, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $target): void
    {
        // Copy cell values dan styles
        foreach ($source->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();

            foreach ($row->getCellIterator() as $cell) {
                $cellAddress = $cell->getCoordinate();

                // Copy value
                $cellValue = $source->getCell($cellAddress)->getValue();
                $target->setCellValue($cellAddress, $cellValue);

                // Copy style
                try {
                    $style = $source->getStyle($cellAddress);
                    $target->getStyle($cellAddress)->applyFromArray($style->exportArray());
                } catch (\Exception $e) {
                    // Skip jika ada error pada style
                    continue;
                }
            }
        }

        // Copy row dimensions
        foreach ($source->getRowDimensions() as $rowDimension) {
            $rowIndex = $rowDimension->getRowIndex();
            $target->getRowDimension($rowIndex)->setRowHeight($rowDimension->getRowHeight());
            $target->getRowDimension($rowIndex)->setVisible($rowDimension->getVisible());
            $target->getRowDimension($rowIndex)->setOutlineLevel($rowDimension->getOutlineLevel());
            $target->getRowDimension($rowIndex)->setCollapsed($rowDimension->getCollapsed());
        }

        // Copy column dimensions
        foreach ($source->getColumnDimensions() as $columnDimension) {
            $columnIndex = $columnDimension->getColumnIndex();
            $target->getColumnDimension($columnIndex)->setWidth($columnDimension->getWidth());
            $target->getColumnDimension($columnIndex)->setVisible($columnDimension->getVisible());
            $target->getColumnDimension($columnIndex)->setAutoSize($columnDimension->getAutoSize());
        }

        // Copy merge cells
        foreach ($source->getMergeCells() as $mergeCell) {
            $target->mergeCells($mergeCell);
        }

        // Copy page setup
        $target->getPageSetup()->setOrientation($source->getPageSetup()->getOrientation());
        $target->getPageSetup()->setPaperSize($source->getPageSetup()->getPaperSize());
        $target->getPageSetup()->setFitToPage($source->getPageSetup()->getFitToPage());
        $target->getPageSetup()->setFitToWidth($source->getPageSetup()->getFitToWidth());
        $target->getPageSetup()->setFitToHeight($source->getPageSetup()->getFitToHeight());

        // Copy page margins
        $target->getPageMargins()->setTop($source->getPageMargins()->getTop());
        $target->getPageMargins()->setBottom($source->getPageMargins()->getBottom());
        $target->getPageMargins()->setLeft($source->getPageMargins()->getLeft());
        $target->getPageMargins()->setRight($source->getPageMargins()->getRight());
        $target->getPageMargins()->setHeader($source->getPageMargins()->getHeader());
        $target->getPageMargins()->setFooter($source->getPageMargins()->getFooter());
    }
}
