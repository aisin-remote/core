<?php

namespace App\Helpers;

use App\Models\Employee;
use Illuminate\Support\Str;

class ApprovalHelper
{
    /**
     * Normalisasi string posisi mentah:
     * - lower, trim, hilangkan titik
     * - buang prefix "act " (Act/ACT/act)
     * - rapikan spasi ganda
     */
    public static function norm(string $s): string
    {
        $x = Str::of($s)->lower()->trim();
        $x = Str::of(preg_replace('/^act\s+/i', '', (string) $x)) // buang prefix "act "
            ->replace('.', '')
            ->squish()
            ->toString();

        return $x;
    }

    /**
     * Mapping ke role KANONIK untuk dipakai di tabel steps:
     *   president, vpd, director, gm, manager, supervisor, leader, jp, operator
     *
     * Catatan:
     * - Semua "Act ..." otomatis direduksi ke jabatan utamanya oleh norm().
     * - Penyetaraan: "section head" → supervisor, "coordinator" → manager,
     *   "direktur" → director, "general manager" → gm, dst.
     */
    public static function canonical(string $pos): string
    {
        $p = self::norm($pos);

        $alias = [
            // Top
            'president' => 'president',
            'vpd'       => 'vpd',

            // Direktur
            'direktur' => 'director',

            // GM
            'gm' => 'gm',

            // Manajerial
            'manager'      => 'manager',
            'coordinator'  => 'manager',
            'section head' => 'supervisor',
            'supervisor'   => 'supervisor',

            // Shopfloor
            'leader'   => 'leader',
            'jp'       => 'jp',
            'operator' => 'operator',

            'act direktur'     => 'director',
            'act gm'           => 'gm',
            'act jp'           => 'jp',
            'act section head' => 'supervisor',
        ];

        return $alias[$p] ?? $p;
    }

    public static function roleKeyFor(Employee $e): string
    {
        return self::canonical($e->position ?? '');
    }

    /**
     * Alias untuk pencarian (opsional): berguna saat whereIn('role', ...)
     * supaya legacy label masih ikut keambil.
     */
    public static function synonymsForSearch(string $canonicalRole): array
    {
        $map = [
            'president'  => ['president', 'presiden', 'president director', 'presdir'],
            'vpd'        => ['vpd', 'vice president director'],
            'director'   => ['director', 'direktur', 'direksi', 'dir'],
            'gm'         => ['gm', 'general manager'],
            'manager'    => ['manager', 'coordinator'],                                   // legacy: coordinator
            'supervisor' => ['supervisor', 'section head'],                               // legacy: section head
            'leader'     => ['leader', 'staff'],                                          // jika di data ada "Staff"
            'jp'         => ['jp'],
            'operator'   => ['operator'],
        ];

        // default ke dirinya sendiri kalau nggak ada di peta
        return $map[$canonicalRole] ?? [$canonicalRole];
    }

    /** Chain sesuai kesepakatan terakhir. */
    public static function expectedChainForEmployee(Employee $e): array
    {
        $role = self::roleKeyFor($e);
        if ($role === 'manager') {
            return [
                ['type' => 'check',   'role' => 'director',  'label' => 'Checking by Director'],
                ['type' => 'check',   'role' => 'vpd',       'label' => 'Checking by VPD'],
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        if ($role === 'operator') {
            return [
                ['type' => 'check',   'role' => 'leader',     'label' => 'Checking by Leader'],
                ['type' => 'approve', 'role' => 'supervisor', 'label' => 'Approve by Supervisor'],
            ];
        }

        if ($role === 'jp') {
            return [
                ['type' => 'check',   'role' => 'supervisor', 'label' => 'Checking by Supervisor'],
                ['type' => 'approve', 'role' => 'gm',         'label' => 'Approve by GM'],
            ];
        }

        // Section Head sudah disetarakan → supervisor
        if ($role === 'supervisor') {
            return [
                ['type' => 'check',   'role' => 'gm',        'label' => 'Checking by GM'],
                ['type' => 'approve', 'role' => 'director',  'label' => 'Approve by Director'],
            ];
        }

        if ($role === 'gm') {
            return [
                ['type' => 'check',   'role' => 'vpd',  'label' => 'Checking by VPD'],
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        if ($role === 'director') {
            return [
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        return [];
    }

    /**
     * Chain khusus IPP
     */
    public static function expectedIppChainForEmployee(Employee $e)
    {
        $role = self::roleKeyFor($e);

        if ($role === 'operator') {
            return [
                ['type' => 'check', 'role' => 'jp', 'label' => 'Checking by JP'],
                ['type' => 'approve', 'role' => 'leader', 'label' => 'Approve by Leader']
            ];
        }

        if ($role === 'jp') {
            return [
                ['type' => 'check', 'role' => 'leader', 'label' => 'Checking by Leader'],
                ['type' => 'approve', 'role' => 'supervisor', 'label' => 'Approve by Supervisor']
            ];
        }

        if ($role === 'leader') {
            return [
                ['type' => 'check', 'role' => 'supervisor', 'label' => 'Checking by Supervisor'],
                ['type' => 'approve', 'role' => 'manager', 'label' => 'Approve by Manager']
            ];
        }


        if ($role === 'supervisor') {
            return [
                ['type' => 'check', 'role' => 'manager', 'label' => 'Checking by Manager'],
                ['type' => 'approve', 'role' => 'gm', 'label' => 'Approve by GM']
            ];
        }

        if ($role === 'manager') {
            return [
                ['type' => 'check', 'role' => 'gm', 'label' => 'Checking by GM'],
                ['type' => 'approve', 'role' => 'director', 'label' => 'Approve by Direktur']
            ];
        }

        if ($role === 'gm') {
            return [
                ['type' => 'check', 'role' => 'director', 'label' => 'Checking by Direktur'],
                ['type' => 'approve', 'role' => 'vpd', 'label' => 'Approve by VPD']
            ];
        }

        if ($role === 'director') {
            return [
                ['type' => 'check', 'role' => 'vpd', 'label' => 'Checking by VPD'],
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President']
            ];
        }

        // vpd, president atau posisi lain yang didefinisikan
        return [];
    }
}
