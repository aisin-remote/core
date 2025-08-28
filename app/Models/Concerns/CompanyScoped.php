<?php

namespace App\Models\Concerns;

trait CompanyScoped
{
    public function scopeForCompany($query, ?string $company)
    {
        if (!$company) return $query; //hrd lihat semua
        return $query->whereHas('employee', function ($q) use ($company) {
            $q->where('company_name', $company);
        });
    }
}
