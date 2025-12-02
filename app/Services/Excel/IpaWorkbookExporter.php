<?php

namespace App\Services\Excel;

use App\Models\IpaHeader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IpaWorkbookExporter
{
    public function __construct(
        protected RenderIpaSheet $ipaRenderer,
        protected RenderPerformanceReview $renderPerformanceReview,
    ) {}

    /**
     * Export 1 file Excel berisi:
     *  - Sheet 1: IPA (Template IPA.xlsx)
     *  - Sheet 2: Performance Review (Template_Performance_Review.xlsx)
     */
    public function export(int $ipaId): BinaryFileResponse
    {
        $tmp = null;

        try {
            // =========================
            // 1) Load template IPA
            // =========================
            $tplIpa = public_path('assets/file/Template IPA.xlsx');
            abort_unless(is_file($tplIpa), 500, 'Template IPA tidak ditemukan.');

            // Workbook utama
            $wb = IOFactory::load($tplIpa);

            // Render sheet IPA di workbook utama
            $this->ipaRenderer->render($wb, $ipaId);

            // =========================
            // 2) Load template Performance Review
            // =========================
            $tplPr = public_path('assets/file/Template_Performance_Review.xlsx');
            abort_unless(is_file($tplPr), 500, 'Template Performance Review tidak ditemukan.');

            $prWb = IOFactory::load($tplPr);

            // Ambil sheet bernama "Performance Review", kalau tidak ada ambil sheet pertama
            $prTemplateSheet = $prWb->getSheetByName('Performance Review') ?? $prWb->getSheet(0);

            // Tambahkan sheet Performance Review ke workbook IPA
            $sheetIndex = $wb->getSheetCount();
            $wb->addExternalSheet($prTemplateSheet, $sheetIndex);

            // Ambil sheet yang baru ditambahkan dan set judul
            $prSheet = $wb->getSheet($sheetIndex);
            $prSheet->setTitle('Performance Review');

            // Render isi Performance Review berdasarkan IPA
            $this->renderPerformanceReview->render($prSheet, $ipaId);

            // =========================
            // 3) Set sheet aktif & cleanup PR workbook
            // =========================
            // Saat file dibuka, user langsung melihat sheet IPA (sheet index 0)
            $wb->setActiveSheetIndex(0);

            // Lepaskan resource workbook Performance Review template
            $prWb->disconnectWorksheets();
            unset($prWb, $prTemplateSheet);

            // =========================
            // 4) Simpan ke file sementara
            // =========================
            $tmp = tempnam(sys_get_temp_dir(), 'ipa_');
            if ($tmp === false) {
                abort(500, 'Gagal membuat file sementara.');
            }

            $writer = new Xlsx($wb);
            $writer->save($tmp);

            // =========================
            // 5) Siapkan nama file download
            // =========================
            $ipa = IpaHeader::with('employee')->findOrFail($ipaId);

            $owner = (string) ($ipa->employee_name ?? $ipa->employee?->name ?? 'user');
            $fileName = 'IPA-' . $ipa->id . '-' . now()->format('Ymd_His') . '-' . Str::slug($owner) . '.xlsx';

            // =========================
            // 6) Return sebagai file download
            // =========================
            return response()->download(
                $tmp,
                $fileName,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Gagal mengekspor IPA workbook', [
                'ipa_id'    => $ipaId,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            if ($tmp && is_file($tmp)) {
                @unlink($tmp);
            }

            abort(500, 'Terjadi kesalahan saat mengekspor IPA.');
        }
    }
}
