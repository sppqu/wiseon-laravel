<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:set-password {email : Email pengguna} {password : Password baru}';

    /**
     * The console command description.
     */
    protected $description = 'Set atau reset password pengguna ke Bcrypt (kompatibel Next.js & Laravel)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email '{$email}' tidak ditemukan.");
            return 1;
        }

        $bcryptHash = password_hash($password, PASSWORD_BCRYPT);

        // Update di tabel account
        $updated = DB::table('account')
            ->where('user_id', $user->id)
            ->where('provider_id', 'credential')
            ->update(['password' => $bcryptHash]);

        if (!$updated) {
            DB::table('account')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'account_id' => $user->id,
                'provider_id' => 'credential',
                'password' => $bcryptHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("✓ Password untuk '{$email}' berhasil diubah ke format Bcrypt.");
        $this->line("Akun sekarang bisa login di Laravel Filament maupun Next.js.");
        return 0;
    }
}
