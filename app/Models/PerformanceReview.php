<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'period',
        'ipa_header_id',
        'result_percent',
        'result_value',
        'b1_pdca_values',
        'b1_items',
        'b2_people_mgmt',
        'b2_items',
        'weight_result',
        'weight_b1',
        'weight_b2',
        'final_value',
        'grading',
        'status'
    ];

    protected $casts = [
        'b1_items'       => 'array',
        'b2_items'       => 'array',
        'year'           => 'integer',
        'b1_pdca_values' => 'float',
        'b2_people_mgmt' => 'float',
        'result_percent' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function ipaHeader()
    {
        return $this->belongsTo(IpaHeader::class);
    }
}
