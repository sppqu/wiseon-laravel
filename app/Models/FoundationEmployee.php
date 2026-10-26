<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundationEmployee extends Model
{
    protected $table = 'foundation_employees';

    protected $fillable = [
        'tenant_id',
        'branch_unit',
        'nik',
        'nip_yayasan',
        'name',
        'gender',
        'category',
        'position',
        'department',
        'employment_status',
        'phone',
        'email',
        'address',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
