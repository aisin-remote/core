<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RtcCandidateFinderService;

class RtcCandidateController extends Controller
{
    public function index(Request $request, RtcCandidateFinderService $finder)
    {
        // ambil kode dari query (?kode=AM/SS/…)
        $kode = strtoupper(trim((string) $request->query('kode', '')));

        if ($kode === '') {
            return response()->json([
                'ok'    => false,
                'error' => 'Parameter "kode" wajib diisi (AS/S/SS/AM/M/SM/AGM/GM/SGM).',
                'data'  => [],
            ], 422);
        }

        $user     = auth()->user();
        $employee = optional($user)->employee;

        // role logic singkat: HRD/Top2 bisa lintas company
        $posRaw      = strtolower(trim((string) ($employee->position ?? '')));
        $normalized  = method_exists($employee, 'getNormalizedPosition')
            ? strtolower((string) $employee->getNormalizedPosition())
            : $posRaw;

        $isHRD  = ($user && $user->role === 'HRD');
        $isTop2 = in_array($posRaw, ['president', 'presdir', 'president director', 'vpd', 'vice president director', 'wakil presdir'], true)
            || in_array($normalized, ['president', 'presdir', 'vpd'], true);

        // scope company: null = semua; string = filter company
        $companyScope = ($isHRD || $isTop2) ? null : (string) ($employee->company_name ?? '');

        // panggil service
        $data = $finder->find($kode, $companyScope);

        return response()->json([
            'ok'      => true,
            'filters' => [
                'kode'    => $kode,
                'company' => $companyScope ?: null,
            ],
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }
}
