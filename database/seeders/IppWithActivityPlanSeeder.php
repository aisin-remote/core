<?php

namespace Database\Seeders;

use App\Models\Ipp;
use App\Models\IppPoint;
use App\Models\Employee;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IppWithActivityPlanSeeder extends Seeder
{
    private int $onYear = 2026; // FY 2026: Apr-2026 s/d Mar-2027
    private array $APR_TO_MAR = ['APR', 'MAY', 'JUN', 'JUL', 'AGT', 'SEPT', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', 'MAR'];

    public function run(): void
    {
        DB::transaction(function () {
            // Pastikan PIC ada (pakai EmployeeSeeder sebelumnya)
            $owner = Employee::where('npk', 'RAF')->first();
            if (!$owner) {
                $this->call(EmployeeSeeder::class);
                $owner = Employee::where('npk', 'RAF')->firstOrFail();
            }

            // IPP FY 2026
            $ipp = Ipp::updateOrCreate(
                ['employee_id' => $owner->id, 'on_year' => $this->onYear],
                [
                    'nama'       => $owner->name,
                    'division'   => 'MS IT GROUP MANAGER',
                    'department' => 'IT DEVELOPMENT',
                    'section'    => 'POLICY MANAGEMENT',
                    'status'     => 'draft',
                    'no_form'    => 'AUTO-SEED-' . $this->onYear,
                ]
            );

            // Activity Plan FY 2026
            $plan = ActivityPlan::updateOrCreate(
                ['ipp_id' => $ipp->id],
                [
                    'employee_id'   => $owner->id,
                    'fy_start_year' => $this->onYear,
                    'division'      => $ipp->division,
                    'department'    => $ipp->department,
                    'section'       => $ipp->section,
                    'form_no'       => $ipp->no_form,
                    'status'        => 'draft',
                ]
            );

            // ===== IPP Points (semua tanggal geser +1 tahun) =====
            $points = [
                // I. Activity Management (70%)
                ['activity_management', 'IT Collaboration AII & AIIA: Develop AISIN GO (Combine AIS + AISINBISA into 1 platform)', 10, '2027-03-01', '2027-03-31', '1 Apps Go live'],

                ['activity_management', 'Existing System : Feature Improvement', 10, '2026-04-01', '2027-03-31', '14 Features Live → 28 Features Live (Yearly)'],

                ['activity_management', 'EDCL', 5, '2026-06-01', '2026-06-30', 'Go Live - Reduce Working Hour 1 H/D'],
                ['activity_management', 'Monitoring Machine Casting (Ewon) & Advanced Traceability (machine info)', 5, '2026-07-01', '2026-07-31', 'Go Live - Reduce 3 Hours of Tracing data'],
                ['activity_management', 'Traceability Body Injection - Assembly - PPIC', 10, '2026-08-01', '2027-01-31', 'Go Live - Reduce mis part & tracing problem'],
                ['activity_management', 'Real Time Stock', 10, '2026-12-01', '2026-12-31', 'Go Live - Cut manual stocktaking 8.5H / CR 200 Mio/Y'],
                ['activity_management', 'Auto Planning Production (AS/MA/DC + Auto Order Component) bundle', 20, '2026-09-01', '2027-03-31', 'All stages until Go Live'],

                // C. Safety & Environment (10%)
                ['activity_management', 'Identify Security Awareness and Follow Rule', 10, '2026-04-01', '2027-03-31', 'ZERO Accident (Yearly)'],

                // II. People Development (10%)
                ['people_development', 'SS (Join SS)', 10, '2026-04-01', '2027-03-31', '> 6 SS (Yearly)'],

                // III. CRP (10%)
                ['crp', 'Budget Controlling', 5, '2026-04-01', '2027-03-31', '0 ; -10% (Yearly)'],
                ['crp', 'Reduce CAPEX & OPEX & CR Activity', 5, '2026-04-01', '2027-03-31', 'CR IDR 200 Million (Yearly)'],

                // IV. Special Assignment & Improvement (10%)
                ['special_assignment', 'TASK FORCE - HR Talent Value System AIIA', 2, '2026-06-01', '2026-06-30', '100% Go Live for AIIA'],
                ['special_assignment', 'TASK FORCE - HR Talent Value System AII', 2, '2026-09-01', '2026-09-30', '100% Go Live for AII'],
                ['special_assignment', 'Develop integrated Budget Application (i-Beep)', 3, '2026-12-01', '2026-12-31', 'Go Live (AII & AIIA)'],
                ['special_assignment', 'Sistem Monitor Absensi Real-Time', 3, '2027-03-01', '2027-03-31', 'Go Live (AII & AIIA)'],
            ];

            $pointMap = [];
            foreach ($points as [$cat, $title, $weight, $start, $due, $targetOne]) {
                $point = IppPoint::updateOrCreate(
                    ['ipp_id' => $ipp->id, 'activity' => $title],
                    [
                        'category'    => $cat,
                        'target_one'  => $targetOne,
                        'target_mid'  => null,
                        'start_date'  => Carbon::parse($start),
                        'due_date'    => Carbon::parse($due),
                        'weight'      => $weight,
                        'status'      => 'draft',
                    ]
                );
                $pointMap[$title] = $point;
            }

            // ===== Detail Activity Plan (geser +1 tahun) =====
            $raf = Employee::where('npk', 'RAF')->first();
            $fab = Employee::where('npk', 'FAB')->first();
            $addItem = function (string $pointTitle, string $kind, string $picNpk, string $target, string $startNote, ?string $dueNote = null)
            use ($plan, $pointMap, $raf, $fab) {
                $point = $pointMap[$pointTitle] ?? null;
                if (!$point) return;

                $pic = strtoupper($picNpk) === 'FAB' ? $fab : $raf;
                [$s, $e] = $this->parseHumanRange($startNote, $dueNote, $this->onYear);
                $mask   = $this->monthsToMask($this->monthsCovered($s, $e, $this->onYear));

                ActivityPlanItem::updateOrCreate(
                    [
                        'activity_plan_id' => $plan->id,
                        'ipp_point_id'     => $point->id,
                        'kind_of_activity' => $kind,
                        'target'           => $target,
                        'pic_employee_id'  => $pic?->id,
                        'cached_start_date' => $s->toDateString(),
                        'cached_due_date'  => $e->toDateString(),
                    ],
                    [
                        'schedule_mask'    => $mask,
                        'cached_category'  => $point->category,
                        'cached_activity'  => $point->activity,
                    ]
                );
            };

            // -- EDCL
            $t = 'EDCL';
            $addItem($t, 'Fisibility Study', 'RAF', 'Able to identify TMMIN requirement', 'Apr-26', 'Apr-26');
            $addItem($t, 'Development', 'FAB', 'API Ready to use', 'Apr-26', 'Apr-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'May-26', 'May-26');
            $addItem($t, 'HVPT', 'RAF', 'System Ready to go live', 'May-26', 'May-26');
            $addItem($t, 'Go Live', 'RAF', 'Go Live (Reduce 1 H/D workload MP)', 'Jun-26', 'Jun-26');

            // -- Monitoring Machine Casting + Advanced Traceability
            $t = 'Monitoring Machine Casting (Ewon) & Advanced Traceability (machine info)';
            $addItem($t, 'Fisibility Study', 'RAF', 'Able to identify user requirement.', 'May-26', 'May-26');
            $addItem($t, 'Order Tools & Installation', 'RAF', 'Tools and Program device already installed', 'Jun-26', 'Jun-26');
            $addItem($t, 'Desain DB & Mockup', 'RAF', 'DB & Mockup aligned with user needs', 'Jun-26', 'Jun-26');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Jul-26', 'Jul-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Jul-26', 'Jul-26');
            $addItem($t, 'HVPT', 'RAF', 'System ready to go live', 'Jul-26', 'Jul-26');
            $addItem($t, 'Go Live', 'RAF', 'Go live (Reduce 3 Hours/Case)', 'Jul-26', 'Jul-26');

            // -- Traceability BI-Asm-PPIC
            $t = 'Traceability Body Injection - Assembly - PPIC';
            $addItem($t, 'Fisibility Study', 'RAF', 'Able to identify user requirement.', 'May-26', 'May-26');
            $addItem($t, 'Order Tools & Installation', 'RAF', 'Tools and Program device installed', 'Sep-26', 'Sep-26');
            $addItem($t, 'Desain DB & Mockup', 'RAF', 'DB & Mockup aligned with user needs', 'Jun-26', 'Jun-26');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Sep-26', 'Sep-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Sep-26', 'Sep-26');
            $addItem($t, 'HVPT', 'RAF', 'System ready to go live', 'Oct-26', 'Oct-26');
            $addItem($t, 'Go Live', 'RAF', 'Reduce mis part & tracing problem 3 Hours/Case', 'Jan-27', 'Jan-27');

            // -- Real Time Stock
            $t = 'Real Time Stock';
            $addItem($t, 'Fisibility Study', 'RAF', 'Identify system requirement.', 'Sep-26', 'Sep-26');
            $addItem($t, 'Desain DB & Mockup', 'RAF', 'DB & Mockup aligned', 'Oct-26', 'Oct-26');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Oct-26', 'Oct-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Dec-26', 'Dec-26');
            $addItem($t, 'HVPT', 'RAF', 'Ready to go live', 'Dec-26', 'Dec-26');
            $addItem($t, 'Go Live', 'RAF', 'No daily manual stock taking; CR 200 Mio/Y', 'Dec-26', 'Dec-26');

            // -- Auto Planning Production (bundle)
            $t = 'Auto Planning Production (AS/MA/DC + Auto Order Component) bundle';
            // AS
            $addItem($t, 'Fisibility Study (AS)', 'RAF', 'Identify requirement', 'Jul-26', 'Jul-26');
            $addItem($t, 'Desain DB & Mockup (AS)', 'RAF', 'DB & Mockup aligned', 'Agt-26', 'Agt-26');
            $addItem($t, 'Development (AS)', 'FAB', 'Finish Program', 'Sep-26', 'Sep-26');
            $addItem($t, 'Testing (AS)', 'RAF', 'Zero Bug', 'Sep-26', 'Sep-26');
            $addItem($t, 'HVPT (AS)', 'RAF', 'Ready to go live', 'Sep-26', 'Sep-26');
            $addItem($t, 'Go Live (AS)', 'RAF', 'Go live & monitoring (CR 106 Mio/Year)', 'Sep-26', 'Sep-26');
            // MA
            $addItem($t, 'Fisibility Study (MA)', 'RAF', 'Identify requirement', 'Nov-26', 'Nov-26');
            $addItem($t, 'Desain DB & Mockup (MA)', 'RAF', 'DB & Mockup aligned', 'Dec-26', 'Dec-26');
            $addItem($t, 'Development (MA)', 'FAB', 'Finish Program', 'Jan-27', 'Jan-27');
            $addItem($t, 'Testing (MA)', 'RAF', 'Zero Bug', 'Feb-27', 'Feb-27');
            $addItem($t, 'HVPT (MA)', 'RAF', 'Ready to go live', 'Feb-27', 'Feb-27');
            $addItem($t, 'Go Live (MA)', 'RAF', 'Go live & monitoring', 'Mar-27', 'Mar-27');
            // DC
            $addItem($t, 'Fisibility Study (DC)', 'RAF', 'Identify requirement', 'Nov-26', 'Nov-26');
            $addItem($t, 'Desain DB & Mockup (DC)', 'RAF', 'DB & Mockup aligned', 'Dec-26', 'Dec-26');
            $addItem($t, 'Development (DC)', 'FAB', 'Finish Program', 'Jan-27', 'Jan-27');
            $addItem($t, 'Testing (DC)', 'RAF', 'Zero Bug', 'Feb-27', 'Feb-27');
            $addItem($t, 'HVPT (DC)', 'RAF', 'Ready to go live', 'Feb-27', 'Feb-27');
            $addItem($t, 'Go Live (DC)', 'RAF', 'Go live & monitoring', 'Mar-27', 'Mar-27');
            // Auto Order Component
            $addItem($t, 'Fisibility Study (Auto Order)', 'RAF', 'Identify requirement', 'Nov-26', 'Nov-26');
            $addItem($t, 'Desain DB & Mockup (Auto Order)', 'RAF', 'DB & Mockup aligned', 'Dec-26', 'Dec-26');
            $addItem($t, 'Development (Auto Order)', 'FAB', 'Finish Program', 'Jan-27', 'Jan-27');
            $addItem($t, 'Testing (Auto Order)', 'RAF', 'Zero Bug', 'Feb-27', 'Feb-27');
            $addItem($t, 'HVPT (Auto Order)', 'RAF', 'Ready to go live', 'Feb-27', 'Feb-27');
            $addItem($t, 'Go Live (Auto Order)', 'RAF', 'Go live & monitoring', 'Mar-27', 'Mar-27');

            // Safety & Env
            $t = 'Identify Security Awareness and Follow Rule';
            $addItem($t, 'ZERO Accident', 'RAF', 'ZERO Accident', 'Yearly');

            // People Dev
            $t = 'SS (Join SS)';
            $addItem($t, 'Join SS', 'RAF', '> 6 SS', 'Yearly');

            // CRP
            $t = 'Budget Controlling';
            $addItem($t, 'Budget Controlling', 'RAF', '0 ; -10%', 'Yearly');
            $t = 'Reduce CAPEX & OPEX & CR Activity';
            $addItem($t, 'CR Activity by Project Development', 'RAF', 'CR IDR 200 Million', 'Yearly');

            // Special Assignment: AIIA
            $t = 'TASK FORCE - HR Talent Value System AIIA';
            $addItem($t, 'Fisibility Study', 'RAF', 'Identify requirement', 'Apr-26', 'Apr-26');
            $addItem($t, 'Desain DB & Mockup', 'RAF', 'DB & Mockup aligned', 'May-26', 'May-26');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Jun-26', 'Jun-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Jun-26', 'Jun-26');
            $addItem($t, 'HVPT', 'RAF', 'Ready to go live', 'Jun-26', 'Jun-26');
            $addItem($t, 'Go Live', 'RAF', 'Ready implement on AIIA', 'Jun-26', 'Jun-26');

            // Special Assignment: AII
            $t = 'TASK FORCE - HR Talent Value System AII';
            $addItem($t, 'Input Master Data', 'RAF', 'Data AII Ready to use', 'Jul-26', 'Jul-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Aug-26', 'Aug-26');
            $addItem($t, 'HVPT', 'RAF', 'Ready to go live', 'Aug-26', 'Aug-26');
            $addItem($t, 'Go Live', 'RAF', 'Already Implement on AII', 'Sep-26', 'Sep-26');

            // i-Beep
            $t = 'Develop integrated Budget Application (i-Beep)';
            $addItem($t, 'Fisibility Study', 'RAF', 'Identify requirement', 'Sep-26', 'Sep-26');
            $addItem($t, 'Mastering Data AII & AIIA', 'RAF', 'Data ready to be develop', 'Sep-26', 'Sep-26');
            $addItem($t, 'Prototype', 'RAF', 'DB & Mockup aligned', 'Oct-26', 'Oct-26');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Nov-26', 'Nov-26');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Nov-26', 'Nov-26');
            $addItem($t, 'HVPT', 'RAF', 'Ready to go live', 'Dec-26', 'Dec-26');
            $addItem($t, 'Go Live', 'RAF', 'Implement for AII & AIIA', 'Dec-26', 'Dec-26');

            // Absensi Real-Time
            $t = 'Sistem Monitor Absensi Real-Time';
            $addItem($t, 'Fisibility Study', 'RAF', 'Identify requirement', 'Jan-27', 'Jan-27');
            $addItem($t, 'Desain DB & Mockup', 'RAF', 'DB & Mockup aligned', 'Jan-27', 'Jan-27');
            $addItem($t, 'Development', 'FAB', 'Finish Program', 'Feb-27', 'Feb-27');
            $addItem($t, 'Testing', 'RAF', 'Zero Bug', 'Feb-27', 'Feb-27');
            $addItem($t, 'HVPT', 'RAF', 'Ready to go live', 'Mar-27', 'Mar-27');
            $addItem($t, 'Go Live', 'RAF', 'Implement for AII & AIIA', 'Mar-27', 'Mar-27');
        });
    }

    // ===== Helpers (sama seperti seeder sebelumnya) =====
    private function parseHumanRange(string $startNote, ?string $endNote, int $fyStart): array
    {
        $s = $this->parseHumanDate($startNote, $fyStart, true);
        $e = $endNote ? $this->parseHumanDate($endNote, $fyStart, false)
            : $this->parseHumanDate($startNote, $fyStart, false);

        if ($this->isYearly($startNote) || $this->isYearly((string)$endNote)) {
            $s = Carbon::create($fyStart, 4, 1)->startOfDay();
            $e = Carbon::create($fyStart + 1, 3, 31)->endOfDay();
        }
        if ($e->lt($s)) $e = $s->copy()->endOfMonth();
        return [$s, $e];
    }

    private function isYearly(string $s): bool
    {
        return preg_match('/yearly/i', $s) === 1;
    }

    private function parseHumanDate(string $s, int $fyStart, bool $asStart): Carbon
    {
        $s = trim($s ?? '');
        if ($s === '') {
            return $asStart
                ? Carbon::create($fyStart, 4, 1)->startOfDay()
                : Carbon::create($fyStart + 1, 3, 31)->endOfDay();
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return Carbon::createFromFormat('Y-m-d', $s)->{$asStart ? 'startOfDay' : 'endOfDay'}();
        }
        [$mon, $yy] = array_pad(preg_split('/[-\s]+/', $s), 2, null);
        $mon = strtolower($mon ?? '');
        $y   = (int) (intval($yy) >= 70 ? 1900 + intval($yy) : 2000 + intval($yy));
        $map = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'mei' => 5,
            'jun' => 6,
            'jul' => 7,
            'agt' => 8,
            'aug' => 8,
            'sep' => 9,
            'sept' => 9,
            'okt' => 10,
            'oct' => 10,
            'nov' => 11,
            'des' => 12,
            'dec' => 12,
        ];
        $m = $map[$mon] ?? 4;
        $d = Carbon::create($y, $m, 1, 0, 0, 0);
        return $asStart ? $d->startOfMonth() : $d->endOfMonth();
    }

    private function monthsCovered(Carbon $start, Carbon $end, int $fy): array
    {
        $fyStart = Carbon::create($fy, 4, 1)->startOfDay();
        $fyEnd   = Carbon::create($fy + 1, 3, 31)->endOfDay();
        $s = $start->copy()->max($fyStart);
        $e = $end->copy()->min($fyEnd);

        $list = [];
        $cur = $s->copy()->startOfMonth();
        while ($cur->lte($e)) {
            $token = $this->toAprMarToken($cur, $fy);
            if ($token) $list[] = $token;
            $cur->addMonth()->startOfMonth();
        }
        return array_values(array_intersect($this->APR_TO_MAR, array_unique($list)));
    }

    private function toAprMarToken(Carbon $d, int $fy): ?string
    {
        $m = (int)$d->month;
        $y = (int)$d->year;
        $idx = ($y === $fy) ? $m - 4 : $m + 8;
        if ($idx < 0 || $idx > 11) return null;
        return $this->APR_TO_MAR[$idx];
    }

    private function monthsToMask(array $months): int
    {
        $flip = array_flip($this->APR_TO_MAR);
        $mask = 0;
        foreach ($months as $t) {
            if (isset($flip[$t])) $mask |= (1 << $flip[$t]);
        }
        return $mask;
    }
}
