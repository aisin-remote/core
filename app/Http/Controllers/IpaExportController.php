<?php

namespace App\Http\Controllers;

use App\Models\IpaHeader;
use App\Services\Excel\IpaWorkbookExporter;

class IpaExportController extends Controller
{
    public function exportWorkbook(IpaHeader $ipa, IpaWorkbookExporter $exporter)
    {
        return $exporter->export($ipa->id);
    }
}
