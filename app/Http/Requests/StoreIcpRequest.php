<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\RtcTarget;

class StoreIcpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // pastikan tidak menyebabkan 403
        return true;
    }

    public function rules(): array
    {
        $validCodes = implode(',', RtcTarget::allCodes());

        return [
            // header
            'employee_id'        => ['required', 'exists:employees,id'],
            'aspiration'         => ['required', 'string'],
            'career_target_code' => ["required", "string", "in:$validCodes"],
            'date'               => ['required', 'date'],

            // stages
            'stages'                 => ['required', 'array', 'min:1'],
            'stages.*.year'          => ['required', 'integer', 'min:1900', 'max:2999'],
            'stages.*.job_function'  => ['required', 'string'],
            'stages.*.job_source'    => ['nullable', 'in:department,division'],
            'stages.*.position_code' => ["required", "string", "in:$validCodes"],
            'stages.*.level'         => ['required', 'string'],

            // details
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
            'stages.required' => 'Minimal 1 tahun harus ditambahkan',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $data   = $this->all();
            $career = strtoupper($data['career_target_code'] ?? '');

            // 1) Tahun berurut: mulai tahun stage [0] + bertambah 1 per index
            $stages = $data['stages'] ?? [];
            if (!empty($stages)) {
                // ambil tahun pertama stages
                $baseYear = (int)($stages[0]['year'] ?? 0);
                foreach ($stages as $idx => $s) {
                    $yr  = (int)($s['year'] ?? 0);
                    $expected = $baseYear + $idx;

                    if ($yr !== $expected) {
                        $v->errors()->add(
                            "stages.$idx.year",
                            "Tahun stage-$idx harus $expected."
                        );
                    }
                }
            }
        });
    }
}
