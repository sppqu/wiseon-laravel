<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel fisik di MySQL (dari better-auth schema: 'user')
     */
    protected $table = 'user';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Izinkan semua pengguna aktif masuk ke dashboard
        return (bool) $this->is_active;
    }

    /**
     * Mengambil password dari tabel 'account' (kompatibilitas dengan Better-Auth)
     */
    public function getAuthPassword()
    {
        $account = DB::table('account')
            ->where('user_id', $this->id)
            ->where('provider_id', 'credential')
            ->first();

        return $account ? $account->password : null;
    }

    /**
     * Mutator agar saat create/update password di Laravel,
     * otomatis tersimpan ke tabel 'account'
     */
    public function setPasswordAttribute($value)
    {
        if (!$value) return;

        // Otomatis buat UUID jika belum ada ID
        if (!$this->id) {
            $this->id = (string) \Illuminate\Support\Str::uuid();
        }

        $hashed = \Illuminate\Support\Facades\Hash::needsRehash($value) 
            ? bcrypt($value) 
            : $value;

        DB::table('account')->updateOrInsert(
            [
                'user_id' => $this->id,
                'provider_id' => 'credential',
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'account_id' => $this->email,
                'password' => $hashed,
                'created_at' => now(),
            ]
        );
    }
}

