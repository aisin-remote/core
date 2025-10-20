<?php
// App/Services/RtcCandidateFinderService.php
namespace App\Services;

use App\Helpers\RtcTarget;
use App\Models\Employee;
use App\Models\Icp;
use Illuminate\Support\Collection;

class RtcCandidateFinderService
{
    /**
     * Cari kandidat via Eloquent (Employee -> Icp -> IcpDetail).
     *
     * @param string $kodeRTC AS/S/SS/AM/M/SM/AGM/GM/SGM
     * @param string|null $company null = semua company
     * @param int|null $yearNow override tahun
     * @return \Illuminate\Support\Collection
     */
    public function find(string $kodeRTC, ?string $company = null, ?int $yearNow = null): Collection
    {
        $target = RtcTarget::map($kodeRTC);
        if (!$target) return collect();

        $year = $yearNow ?: now()->year;
        $position = $target['position'];
        $levels = (array) $target['levels'];

        $employees = Employee::query()
            ->when($company, fn($q) => $q->where('company_name', $company))
            ->whereHas('icp', fn($q) => $q->where('status', Icp::STATUS_APPROVED))
            ->whereHas('icp.details', function ($q) use ($position, $levels, $year) {
                $q->where('position', $position)
                    ->whereIn('level', $levels)
                    ->where('plan_year', '>=', $year);
            })
            ->with([
                'icp' => function ($q) {
                    $q->where('status', Icp::STATUS_APPROVED);
                },
                'icp.details' => function ($q) use ($position, $levels, $year) {
                    $q->where('position', $position)
                        ->whereIn('level', $levels)
                        ->where('plan_year', '>=', $year)
                        ->orderBy('plan_year', 'asc');
                }
            ])
            ->get(['id', 'name', 'company_name']);

        return $employees->map(function ($e) use ($year) {
            $allDetails = $e->icp
                ->flatMap(fn($icp) => $icp->details)
                ->sortBy('plan_year')
                ->values();

            $d = $allDetails->first();
            if (!$d) return null;

            $diff = (int) $d->plan_year - $year;
            $term = self::classifyTerm($diff);

            return [
                'employee_id'  => (int) $e->id,
                'name'         => (string) $e->name,
                'company_name' => (string) $e->company_name,
                'job_function' => (string) $d->job_function,
                'position'     => (string) $d->position,
                'level'        => (string) $d->level,
                'plan_year'    => (int) $d->plan_year,
                'term'         => $term,
            ];
        })->filter()->values();
    }

    public static function classifyTerm(int $diff): string
    {
        if ($diff <= 1) return 'short';
        if ($diff <= 3) return 'mid';
        return 'long';
    }
}
