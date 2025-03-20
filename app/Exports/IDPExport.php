<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;

class IDPExport extends DefaultValueBinder implements WithEvents, WithCustomValueBinder
{
    protected $employee_id;

    public function __construct($employee_id)
    {
        $this->employee_id = $employee_id;
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\BeforeSheet::class => function ($event) {
                $filePath = storage_path('app/templates/idp_template.xlsx');

                if (!file_exists($filePath)) {
                    throw new \Exception('Template file tidak ditemukan.');
                }

                // Load template Excel
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                // Ambil data employee dari database
                $employee = Employee::find($this->employee_id);
                if (!$employee) {
                    throw new \Exception('Employee tidak ditemukan.');
                }

                // Isi data ke dalam template
                $sheet->setCellValue('H3', $employee->name);
                $sheet->setCellValue('B3', $employee->position);
                $sheet->setCellValue('B4', $employee->department);

                // Simpan perubahan ke dalam event
                $event->sheet->getDelegate()->getParent()->setActiveSheetIndex(0);
            },
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        // Gunakan format default, misalnya untuk tanggal atau angka
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
