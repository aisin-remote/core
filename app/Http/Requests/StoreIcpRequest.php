<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\RtcTarget;

class StoreIcpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mapAll = RtcTarget::mapAll();
        $positionsUnique = collect($mapAll)
            ->pluck('position')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        $validPositionsCsv = implode(',', array_map(
            fn($p) => str_replace(',', '\,', $p),
            $positionsUnique
        ));

        return [
            'employee_id'               => ['required', 'exists:employees,id'],
            'employee_current_position' => ['required', 'string'],
            'aspiration'                => ['required', 'string'],
            'readiness'                 => ['required', 'integer'],
            "career_target_code"        => ["required", "string", "in:$validPositionsCsv"],
            'date'                      => ['required', 'date'],

            // ===== STAGES =====
            'stages'                        => ['required', 'array', 'min:1'],

            'stages.*.year'                 => ['required', 'integer', 'min:1900', 'max:2999'],
            'stages.*.job_function'         => ['required', 'string'],
            'stages.*.job_source'           => ['nullable', 'in:department,division'],

            "stages.*.position_code"        => ["nullable", "string", "in:$validPositionsCsv"],
            'stages.*.level'                => ['required', 'string'],

            // ===== DETAILS =====
            'stages.*.details'                            => ['required', 'array', 'min:1'],

            'stages.*.details.*.current_technical'        => ['required', 'string'],
            'stages.*.details.*.current_nontechnical'     => ['required', 'string'],

            'stages.*.details.*.required_technical'       => ['required', 'string'],
            'stages.*.details.*.required_nontechnical'    => ['required', 'string'],

            'stages.*.details.*.development_technical'    => ['required', 'string'],
            'stages.*.details.*.development_nontechnical' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'stages.required'                             => 'Minimal 1 tahun harus ditambahkan.',
            'stages.*.details.required'                   => 'Setiap Stage minimal punya 1 Detail.',
            'career_target_code.in'                       => 'Career Target tidak valid.',
            'stages.*.position_code.in'                   => 'Position pada Stage tidak valid.',
            'employee_current_position.required'          => 'Data posisi saat ini tidak ditemukan.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {

            $data           = $this->all();
            $stages         = $data['stages'] ?? [];
            $careerTarget   = $data['career_target_code'] ?? null;          // nama posisi target ("Direktur")
            $currentPosName = $data['employee_current_position'] ?? null;   // nama posisi saat ini ("Act GM")

            $ladderCodes = RtcTarget::order();
            $ladderMap   = RtcTarget::mapAll();
            $positionRank = [];
            $rankCounter  = 0;
            foreach ($ladderCodes as $code) {
                $posName = $ladderMap[$code]['position'] ?? null;
                if (!$posName) continue;
                if (!array_key_exists($posName, $positionRank)) {
                    $positionRank[$posName] = $rankCounter;
                    $rankCounter++;
                }
            }

            $getRank = function (?string $posName) use ($positionRank): ?int {
                if (!$posName) return null;
                return $positionRank[$posName] ?? null;
            };

            $careerRank  = $getRank($careerTarget);
            $currentRank = $getRank($currentPosName);

            $effectiveStages = [];

            foreach ($stages as $idx => $stage) {
                $givenPos = $stage['position_code'] ?? null;

                $isLast = ($idx === count($stages) - 1);

                if (!$givenPos || $givenPos === '') {
                    if ($isLast) {
                        $effectivePos = $careerTarget;
                    } else {
                        $v->errors()->add(
                            "stages.$idx.position_code",
                            "Position pada stage ke-" . ($idx + 1) . " wajib diisi."
                        );
                        $effectivePos = null;
                    }
                } else {
                    $effectivePos = $givenPos;
                }

                $stage['_effective_position'] = $effectivePos;
                $effectiveStages[$idx] = $stage;
            }
            $this->merge([
                'stages' => $effectiveStages,
            ]);

            if (!empty($stages)) {
                $baseYear = (int)($stages[0]['year'] ?? 0);

                foreach ($stages as $idx => $s) {
                    $yr       = (int)($s['year'] ?? 0);
                    $expected = $baseYear + $idx;

                    if ($yr !== $expected) {
                        $v->errors()->add(
                            "stages.$idx.year",
                            "Tahun pada stage ke-" . ($idx + 1) . " harus $expected."
                        );
                    }
                }
            }

            if (!empty($effectiveStages) && $careerTarget) {
                $lastIdx        = count($effectiveStages) - 1;
                $lastEffective  = $effectiveStages[$lastIdx]['_effective_position'] ?? null;

                if ($lastEffective !== $careerTarget) {
                    $v->errors()->add(
                        "stages.$lastIdx.position_code",
                        "Stage terakhir harus sama dengan Career Target."
                    );
                }
            }

            if ($careerRank !== null && $currentRank !== null) {
                foreach ($effectiveStages as $idx => $s) {

                    $posNameEff = $s['_effective_position'] ?? null;
                    $stgRank = $getRank($posNameEff);

                    if ($stgRank === null) {
                        $v->errors()->add(
                            "stages.$idx.position_code",
                            "Position stage ke-" . ($idx + 1) . " tidak dikenali dalam jenjang karier."
                        );
                        continue;
                    }

                    if ($stgRank < $currentRank) {
                        $v->errors()->add(
                            "stages.$idx.position_code",
                            "Position stage ke-" . ($idx + 1) . " tidak boleh lebih rendah dari posisi saat ini."
                        );
                    }

                    if ($stgRank > $careerRank) {
                        $v->errors()->add(
                            "stages.$idx.position_code",
                            "Position stage ke-" . ($idx + 1) . " tidak boleh lebih tinggi dari Career Target."
                        );
                    }
                }
            }
            foreach ($stages as $idx => $s) {
                $details = $s['details'] ?? [];
                if (empty($details)) {
                    $v->errors()->add(
                        "stages.$idx.details",
                        "Setiap stage harus punya minimal 1 Detail."
                    );
                }
            }
        });
    }
}
