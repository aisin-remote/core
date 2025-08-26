<?php

namespace App\Helpers;

use App\Models\Employee;
use Illuminate\Support\Str;

class ApprovalHelper
{
    // lower, trim, buang "Act "
    public static function norm(string $s): string
    {
        $x = Str::of($s)->lower()->trim();
        // hilangkan "act " di depan, titik, dan spasi ganda
        $x = Str::of(preg_replace('/^act\s+/i', '', (string)$x))
            ->replace('.', '')
            ->squish()
            ->toString();
        return $x;
    }

    // ubah ke role kanonik yang dipakai di tabel steps
    public static function canonical(string $pos): string
    {
        $p = self::norm($pos);

        $map = [
            // Top
            'presiden' => 'president',
            'president' => 'president',
            'presdir' => 'president',
            'president director' => 'president',

            'vpd' => 'vpd',
            'vice president director' => 'vpd',

            // Direktur
            'direktur' => 'director',
            'director' => 'director',
            'dir'      => 'director',
            'direksi'  => 'director',

            // Manajerial
            'general manager' => 'gm',
            'gm' => 'gm',
            'manager' => 'manager',
            'coordinator' => 'coordinator',
            'section head' => 'section head',
            'supervisor' => 'supervisor',

            // Shopfloor
            'leader' => 'leader',
            'jp' => 'jp',
            'operator' => 'operator',
        ];

        return $map[$p] ?? $p;
    }

    public static function roleKeyFor(Employee $e): string
    {
        return self::canonical($e->position ?? '');
    }

    /** Chain sesuai kesepakatan terakhir */
    public static function expectedChainForEmployee(Employee $e): array
    {
        $role = self::canonical($e->position ?? '');

        // Khusus MANAGER
        if ($role === 'manager') {
            return [
                ['type' => 'check',   'role' => 'director',  'label' => 'Checking by Director'],
                ['type' => 'check',   'role' => 'vpd',       'label' => 'Checking by VPD'],
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        // Operator → Leader (check) → Supervisor (approve)
        if ($role === 'operator') {
            return [
                ['type' => 'check',   'role' => 'leader',     'label' => 'Checking by Leader'],
                ['type' => 'approve', 'role' => 'supervisor', 'label' => 'Approve by Supervisor'],
            ];
        }

        // JP → (check) Supervisor → (approve) GM
        if ($role === 'jp') {
            return [
                ['type' => 'check',   'role' => 'supervisor', 'label' => 'Checking by Supervisor'],
                ['type' => 'approve', 'role' => 'gm',         'label' => 'Approve by GM'],
            ];
        }

        // Supervisor / Section Head → (check) GM → (approve) Director
        if (in_array($role, ['supervisor', 'section head'], true)) {
            return [
                ['type' => 'check',   'role' => 'gm',        'label' => 'Checking by GM'],
                ['type' => 'approve', 'role' => 'director',  'label' => 'Approve by Director'],
            ];
        }

        // GM → (check) Director → (approve) President
        if ($role === 'gm') {
            return [
                ['type' => 'check',   'role' => 'director',  'label' => 'Checking by Director'],
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        // Director → (approve) President
        if ($role === 'director') {
            return [
                ['type' => 'approve', 'role' => 'president', 'label' => 'Approve by President'],
            ];
        }

        // Default: tidak perlu chain
        return [];
    }
}
