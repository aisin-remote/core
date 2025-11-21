<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RtcService
{
    /**
     * NOT SET = tidak ada baris pada tabel `rtc` untuk entitas tsb (apa pun status/term).
     *
     * @param  string $level  'division'|'department'|'section'|'sub_section'|'direksi'|'company'
     * @param  array  $filters
     *   - 'division_ids' => int[]  (membatasi cakupan division dan turunannya)
     *   - 'plant_ids'    => int[]  (membatasi cakupan plant; dipakai direksi/division aggregasi by plant)
     *   - 'manager_id'   => int    (khusus utk scope Manager; hanya department/section/sub_section di dept yg dia pimpin)
     * @return int
     */
    public static function countNotSet(string $level, array $filters = []): int
    {
        $level = strtolower(trim($level));

        switch ($level) {
            case 'company':
                // Tab Company di UI tak menampilkan KPI → biarkan 0 (atau implementasi lain sesuai kebutuhanmu).
                return 0;

            case 'direksi':
                // Angka "Direksi" = jumlah DIVISION yang TIDAK punya data rtc (apa pun status/term)
                // di bawah plant yang dicakup (jika diberi).
                $divisions = DB::table('divisions as d')->select('d.id');
                if (!empty($filters['plant_ids'])) {
                    $divisions->whereIn('d.plant_id', (array) $filters['plant_ids']);
                }

                return (int) DB::table(DB::raw("({$divisions->toSql()}) as dd"))
                    ->mergeBindings($divisions)
                    ->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('rtc')
                            ->whereIn('rtc.area', self::areaAliases('division'))
                            ->whereColumn('rtc.area_id', 'dd.id');
                    })
                    ->count();

            case 'division':
                $q = DB::table('divisions as d')->select('d.id');

                if (!empty($filters['plant_ids'])) {
                    $q->whereIn('d.plant_id', (array) $filters['plant_ids']);
                }
                if (!empty($filters['division_ids'])) {
                    $q->whereIn('d.id', (array) $filters['division_ids']);
                }

                return (int) DB::table(DB::raw("({$q->toSql()}) as dd"))
                    ->mergeBindings($q)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('rtc')
                            ->whereIn('rtc.area', self::areaAliases('division'))
                            ->whereColumn('rtc.area_id', 'dd.id');
                    })
                    ->count();

            case 'department':
                $q = DB::table('departments as dep')->select('dep.id');

                if (!empty($filters['division_ids'])) {
                    $q->whereIn('dep.division_id', (array) $filters['division_ids']);
                }
                if (!empty($filters['plant_ids'])) {
                    $q->join('divisions as d', 'd.id', '=', 'dep.division_id')
                        ->whereIn('d.plant_id', (array) $filters['plant_ids']);
                }
                // Hanya department yang manager_id = manager ini (jika diberikan)
                if (!empty($filters['manager_id'])) {
                    $q->where('dep.manager_id', (int) $filters['manager_id']);
                }

                return (int) DB::table(DB::raw("({$q->toSql()}) as dd"))
                    ->mergeBindings($q)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('rtc')
                            ->whereIn('rtc.area', self::areaAliases('department'))
                            ->whereColumn('rtc.area_id', 'dd.id');
                    })
                    ->count();

            case 'section':
                $q = DB::table('sections as s')
                    ->select('s.id')
                    ->join('departments as dep', 'dep.id', '=', 's.department_id');

                if (!empty($filters['division_ids'])) {
                    $q->whereIn('dep.division_id', (array) $filters['division_ids']);
                }
                if (!empty($filters['plant_ids'])) {
                    $q->join('divisions as d', 'd.id', '=', 'dep.division_id')
                        ->whereIn('d.plant_id', (array) $filters['plant_ids']);
                }
                // Hanya section di department yang manager_id = manager ini (jika diberikan)
                if (!empty($filters['manager_id'])) {
                    $q->where('dep.manager_id', (int) $filters['manager_id']);
                }

                return (int) DB::table(DB::raw("({$q->toSql()}) as ss"))
                    ->mergeBindings($q)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('rtc')
                            ->whereIn('rtc.area', self::areaAliases('section'))
                            ->whereColumn('rtc.area_id', 'ss.id');
                    })
                    ->count();

            case 'sub_section':
                $q = DB::table('sub_sections as sss')
                    ->select('sss.id')
                    ->join('sections as s', 's.id', '=', 'sss.section_id')
                    ->join('departments as dep', 'dep.id', '=', 's.department_id');

                if (!empty($filters['division_ids'])) {
                    $q->whereIn('dep.division_id', (array) $filters['division_ids']);
                }
                if (!empty($filters['plant_ids'])) {
                    $q->join('divisions as d', 'd.id', '=', 'dep.division_id')
                        ->whereIn('d.plant_id', (array) $filters['plant_ids']);
                }
                // Hanya sub_section di bawah department yang manager_id = manager ini (jika diberikan)
                if (!empty($filters['manager_id'])) {
                    $q->where('dep.manager_id', (int) $filters['manager_id']);
                }

                return (int) DB::table(DB::raw("({$q->toSql()}) as ssss"))
                    ->mergeBindings($q)
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('rtc')
                            ->whereIn('rtc.area', self::areaAliases('sub_section'))
                            ->whereColumn('rtc.area_id', 'ssss.id');
                    })
                    ->count();

            default:
                return 0;
        }
    }

    /**
     * Alias nilai 'area' (mengakomodasi variasi penulisan).
     */
    private static function areaAliases(string $area): array
    {
        $a = strtolower(trim($area));
        return match ($a) {
            'division'    => ['division', 'Division'],
            'department'  => ['department', 'Department'],
            'section'     => ['section', 'Section'],
            'sub_section' => ['sub_section', 'Sub_section', 'SubSection', 'Sub Section', 'subsection', 'Subsection'],
            'direksi'     => ['direksi', 'Direksi', 'plant', 'Plant'], // bila pernah menyimpan "Plant" di rtc.area
            'company'     => ['company', 'Company'],
            default       => [$area, ucfirst($area)],
        };
    }
}
