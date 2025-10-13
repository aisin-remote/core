<?php

namespace App\Http\Controllers;

use App\Helpers\ReviewHelper;
use App\Models\IpaHeader;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PerformanceReviewController extends Controller
{
    private function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    private function fail(string $message = 'Error', int $code = 400, $errors = null)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /** util: rata-rata array numeric (2 desimal) */
    private function avgOrNull(?array $arr): ?float
    {
        if (!$arr) return null;
        $nums = array_values(array_filter(array_map(function ($v) {
            if ($v === null || $v === '') return null;
            $n = $this->num($v);
            return is_numeric($n) ? (float)$n : null;
        }, $arr), fn($v) => $v !== null));
        if (!count($nums)) return null;
        return round(array_sum($nums) / count($nums), 2);
    }

    /** util: normalisasi angka (mendukung "4,83") */
    private function num($v): float
    {
        if (is_string($v)) {
            $v = str_replace(',', '.', $v);
        }
        return (float)$v;
    }

    /** GET /reviews (page) */
    public function index(Request $req)
    {
        $meId = optional(auth()->user())->employee?->id;
        if (!$meId) {
            abort(403, 'Employee not found for current user');
        }

        $year = now()->year;
        $ipas = IpaHeader::where('employee_id', $meId)
            ->where('on_year', $year) // ganti ke 'year' jika skema kamu pakai kolom 'year'
            ->first();

        $ipa = $ipas ? [
            'id'          => $ipas->id,
            'grand_total' => $ipas->grand_total,
        ] : null;

        // Blade kamu expect variabel $ipa
        return view('website.performance_review.index', compact('ipa'));
    }

    /** GET /reviews/init (JSON list milik karyawan login) */
    public function init(Request $req)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) return $this->fail('Employee not found for current user', 403);

        $perPage = (int)($req->integer('per_page') ?: 10);

        $q = PerformanceReview::query()
            ->with(['employee:id,name,grade'])
            ->where('employee_id', $me->id)
            ->when($req->filled('year'),   fn($qq) => $qq->where('year', (int)$req->input('year')))
            ->when($req->filled('period'), fn($qq) => $qq->where('period', (string)$req->input('period')))
            ->orderByDesc('year')->orderBy('period');

        $paginated = $q->paginate($perPage);

        return $this->ok($paginated);
    }

    /** GET /reviews/{id} (hanya milik sendiri) */
    public function show($id)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) return $this->fail('Employee not found for current user', 403);

        $review = PerformanceReview::with(['employee:id,name,grade', 'ipaHeader:id,grand_total'])
            ->where('employee_id', $me->id)
            ->find($id);

        if (!$review) return $this->fail('Review not found', 404);

        return $this->ok($review);
    }

    /**
     * POST /reviews
     * Payload:
     * {
     *   "year": 2025,
     *   "period": {
     *      "mid": { "a_grand_total_ipa": 136, "b1_items":[...], "b2_items":[...], "b1_pdca_values":4.14, "b2_people_mgmt":4.25 },
     *      "one": { ... }
     *   }
     * }
     * Boleh kirim salah satu (mid/one) atau keduanya.
     */
    public function store(Request $req)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) return $this->fail('Employee not found for current user', 403);

        $validated = $req->validate([
            'year'          => ['required', 'integer', 'min:2000'],
            'period'        => ['required', 'array'],
            'period.mid'    => ['sometimes', 'array'],
            'period.one'    => ['sometimes', 'array'],

            'period.*.a_grand_total_ipa' => ['nullable', 'numeric'],
            'period.*.b1_items'          => ['nullable', 'array', 'max:7'],
            'period.*.b1_items.*'        => ['nullable', 'numeric', 'between:1,5'],
            'period.*.b2_items'          => ['nullable', 'array', 'max:4'],
            'period.*.b2_items.*'        => ['nullable', 'numeric', 'between:1,5'],
            'period.*.b1_pdca_values'    => ['nullable', 'numeric', 'between:1,5'],
            'period.*.b2_people_mgmt'    => ['nullable', 'numeric', 'between:1,5'],
        ]);

        $year    = (int)$validated['year'];
        $periods = $validated['period'];
        $saved   = [];

        try {
            DB::beginTransaction();

            foreach (['mid', 'one'] as $p) {
                if (!isset($periods[$p]) || !is_array($periods[$p])) continue;
                $data = $periods[$p];

                $grandRaw = $data['a_grand_total_ipa'] ?? null;
                if ($grandRaw === null || $grandRaw === '') {
                    Log::warning("Skip $p period — no a_grand_total_ipa for employee {$me->id}");
                    continue;
                }
                $grand = $this->num($grandRaw);

                $b1Items = array_values($data['b1_items'] ?? []);
                $b2Items = array_values($data['b2_items'] ?? []);

                $b1 = $this->avgOrNull($b1Items);
                $b2 = $this->avgOrNull($b2Items);
                $b1 = $b1 ?? (isset($data['b1_pdca_values']) ? $this->num($data['b1_pdca_values']) : null);
                $b2 = $b2 ?? (isset($data['b2_people_mgmt']) ? $this->num($data['b2_people_mgmt']) : null);

                if ($b1 === null || $b2 === null) {
                    Log::warning("Skip $p period — incomplete B1/B2 for employee {$me->id}");
                    continue;
                }

                // bobot & nilai
                $weights     = ReviewHelper::weightsForGrade($me->grade);
                $resultValue = ReviewHelper::computeResultValue($grand);
                $finalValue  = ReviewHelper::calculateFinalValue($resultValue, $b1, $b2, $weights);
                $grading     = ReviewHelper::gradeFromFinalValue($finalValue);

                $review = PerformanceReview::updateOrCreate(
                    ['employee_id' => $me->id, 'year' => $year, 'period' => $p],
                    [
                        'ipa_header_id'   => null,
                        'result_percent'  => $grand,
                        'result_value'    => $resultValue,

                        'b1_items'        => $b1Items ?: null,
                        'b2_items'        => $b2Items ?: null,
                        'b1_pdca_values'  => $b1,
                        'b2_people_mgmt'  => $b2,

                        'weight_result'   => 0.50,
                        'weight_b1'       => $weights['b1'],
                        'weight_b2'       => $weights['b2'],
                        'final_value'     => $finalValue,
                        'grading'         => $grading,
                    ]
                );

                $saved[] = $review;
                Log::info("PerformanceReview {$p} saved for employee {$me->id}", ['review_id' => $review->id]);
            }

            DB::commit();
            return $this->ok($saved, 'Reviews saved successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed saving PerformanceReview for employee {$me->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'payload' => $req->all(),
            ]);
            return $this->fail('Failed to save reviews', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * PUT /reviews/{id}
     * Kompatibel dengan form lama (flat) untuk edit satu record.
     * Body optional:
     *  - year, period (mid|one)
     *  - grand_total_pct (angka)
     *  - b1_items[] / b2_items[] (jika dikirim, dipakai & disimpan)
     *  - b1_pdca_values / b2_people_mgmt (fallback jika array tidak dikirim)
     */
    public function update(Request $req, $id)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) return $this->fail('Employee not found for current user', 403);

        $review = PerformanceReview::where('employee_id', $me->id)->find($id);
        if (!$review) return $this->fail('Review not found', 404);

        $data = $req->validate([
            'year'            => ['nullable', 'integer', 'min:2000'],
            'period'          => ['nullable', Rule::in(['mid', 'one'])],

            'grand_total_pct' => ['nullable'],

            'b1_items'        => ['nullable', 'array', 'max:7'],
            'b1_items.*'      => ['nullable', 'numeric', 'between:1,5'],
            'b2_items'        => ['nullable', 'array', 'max:4'],
            'b2_items.*'      => ['nullable', 'numeric', 'between:1,5'],

            'b1_pdca_values'  => ['nullable', 'numeric', 'between:1,5'],
            'b2_people_mgmt'  => ['nullable', 'numeric', 'between:1,5'],
        ]);

        try {
            DB::beginTransaction();

            $grand = $review->result_percent;
            if (array_key_exists('grand_total_pct', $data) && $data['grand_total_pct'] !== null && $data['grand_total_pct'] !== '') {
                $grand = $this->num($data['grand_total_pct']);
            }

            $b1Items = array_key_exists('b1_items', $data) ? array_values($data['b1_items'] ?? []) : $review->b1_items;
            $b2Items = array_key_exists('b2_items', $data) ? array_values($data['b2_items'] ?? []) : $review->b2_items;

            $b1FromArr = $this->avgOrNull($b1Items ?? null);
            $b2FromArr = $this->avgOrNull($b2Items ?? null);

            $b1 = $b1FromArr;
            if ($b1 === null) {
                if (array_key_exists('b1_pdca_values', $data) && $data['b1_pdca_values'] !== null && $data['b1_pdca_values'] !== '') {
                    $b1 = $this->num($data['b1_pdca_values']);
                } else {
                    $b1 = (float)$review->b1_pdca_values;
                }
            }

            $b2 = $b2FromArr;
            if ($b2 === null) {
                if (array_key_exists('b2_people_mgmt', $data) && $data['b2_people_mgmt'] !== null && $data['b2_people_mgmt'] !== '') {
                    $b2 = $this->num($data['b2_people_mgmt']);
                } else {
                    $b2 = (float)$review->b2_people_mgmt;
                }
            }

            $weights     = ReviewHelper::weightsForGrade($me->grade);
            $resultValue = ReviewHelper::computeResultValue($grand);
            $finalValue  = ReviewHelper::calculateFinalValue($resultValue, $b1, $b2, $weights);
            $grading     = ReviewHelper::gradeFromFinalValue($finalValue);

            $review->fill([
                'year'            => $data['year']   ?? $review->year,
                'period'          => $data['period'] ?? $review->period,
                'result_percent'  => $grand,
                'result_value'    => $resultValue,

                'b1_items'        => $b1Items ?: null,
                'b2_items'        => $b2Items ?: null,
                'b1_pdca_values'  => $b1,
                'b2_people_mgmt'  => $b2,

                'weight_result'   => 0.50,
                'weight_b1'       => $weights['b1'],
                'weight_b2'       => $weights['b2'],
                'final_value'     => $finalValue,
                'grading'         => $grading,
            ])->save();

            DB::commit();
            return $this->ok($review->fresh()->load(['employee:id,name,grade', 'ipaHeader:id,grand_total']), 'Review updated');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed updating PerformanceReview {$id} for employee {$me->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'payload' => $req->all(),
            ]);
            return $this->fail('Failed to update review', 500, ['exception' => $e->getMessage()]);
        }
    }

    /** DELETE /reviews/{id} (hanya milik sendiri) */
    public function destroy($id)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) return $this->fail('Employee not found for current user', 403);

        $review = PerformanceReview::where('employee_id', $me->id)->find($id);
        if (!$review) return $this->fail('Review not found', 404);

        try {
            DB::beginTransaction();
            $review->delete();
            DB::commit();
            Log::info("PerformanceReview {$id} deleted for employee {$me->id}");
            return $this->ok(null, 'Review deleted');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed deleting PerformanceReview {$id} for employee {$me->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->fail('Failed to delete review', 500, ['exception' => $e->getMessage()]);
        }
    }
}
