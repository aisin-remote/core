<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Idp extends Model
{
    use HasFactory;

    protected $table = 'idp';
    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }

    public function hav()
    {
        return $this->belongsTo(HavDetail::class, 'hav_detail_id', 'id');
    }

    public function alc()
    {
        return $this->belongsTo(Alc::class, 'alc_id', 'id');
    }

    public function commentHistory()
    {
        return $this->hasMany(IdpCommentHistory::class, 'idp_id');
    }
    public function developments()
    {
        return $this->hasMany(Development::class, 'idp_id', 'id');
    }

    public function developmentsones()
    {
        return $this->hasMany(DevelopmentOne::class, 'idp_id', 'id');
    }

    public function backups()
    {
        return $this->hasMany(IdpBackup::class, 'idp_id');
    }

    public function lastBackup()
    {
        // versi terakhir per-IDP (berdasarkan kolom version)
        return $this->hasOne(IdpBackup::class, 'idp_id')->latestOfMany('version');
    }
}
