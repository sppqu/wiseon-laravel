<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'school_level',
        'address',
        'city',
        'phone',
        'email',
        'principal_name',
        'npsn',
        'is_active',
    ];

    public function parent()
    {
        return $this->belongsTo(Tenant::class, 'parent_id');
    }

    public function branches()
    {
        return $this->hasMany(Tenant::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(FoundationEmployee::class, 'tenant_id');
    }
}
