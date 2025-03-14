<?php
namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeImport implements ToModel
{
    public function model(array $row)
    {
        return new Employee([
            'npk'         => $row[0],
            'name'        => $row[1],
            'company_name' => $row[2],
            'position'    => $row[3],
            'function'    => $row[4],
            'grade'       => $row[5],
            'birthday_date' => Date::excelToDateTimeObject($row[6])
        ]);
    }
}
