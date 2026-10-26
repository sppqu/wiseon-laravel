<?php

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class BetterAuthHasher implements Hasher
{
    /**
     * Get information about the given hashed value.
     */
    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }

    /**
     * Hash the given value using standard Bcrypt.
     */
    public function make($value, array $options = [])
    {
        $cost = $options['rounds'] ?? 12;
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Check the given plain value against a hash.
     * Mendukung:
     * 1. Bcrypt standar ($2y$, $2a$, $2b$)
     * 2. Better-Auth scrypt (<salt_hex>:<key_hex>)
     * 3. Plaintext fallback
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (empty($hashedValue) || !is_string($hashedValue)) {
            return false;
        }

        // 1. Jika hash berupa Bcrypt standar ($2y$, $2a$, atau $2b$)
        if (str_starts_with($hashedValue, '$2y$') || str_starts_with($hashedValue, '$2a$') || str_starts_with($hashedValue, '$2b$')) {
            return password_verify($value, $hashedValue);
        }

        // 2. Jika hash berupa format Better-Auth scrypt (salt_hex:key_hex)
        if (str_contains($hashedValue, ':')) {
            $parts = explode(':', $hashedValue);
            if (count($parts) === 2) {
                [$salt, $expectedKey] = $parts;
                
                $calculatedKey = $this->calculateScryptKey($value, $salt);
                if ($calculatedKey && hash_equals(trim($expectedKey), trim($calculatedKey))) {
                    // Otomatis upgrade ke Bcrypt di database agar login berikutnya instan (native PHP)
                    $this->upgradeToBcrypt($hashedValue, $value);
                    return true;
                }
            }
        }

        // 3. Fallback: kecocokan plaintext (untuk user lama sebelum migrasi hash)
        if ($hashedValue === $value) {
            $this->upgradeToBcrypt($hashedValue, $value);
            return true;
        }

        return false;
    }

    /**
     * Menghitung scrypt key menggunakan runtime yang aman (Symfony Process / CLI)
     */
    protected function calculateScryptKey(string $password, string $salt): ?string
    {
        $script = 'const crypto=require("crypto");crypto.scrypt(process.argv[1],process.argv[2],64,{N:16384,r:16,p:1,maxmem:67108864},(e,k)=>{if(e)process.exit(1);process.stdout.write(k.toString("hex"));});';

        // Metode 1: Menggunakan Symfony Process (cara standar Laravel)
        try {
            if (class_exists(Process::class)) {
                $process = new Process(['node', '-e', $script, $password, $salt]);
                $process->setTimeout(4);
                $process->run();
                if ($process->isSuccessful()) {
                    $out = trim($process->getOutput());
                    if (!empty($out)) return $out;
                }
            }
        } catch (\Throwable $e) {}

        // Metode 2: proc_open jika tersedia
        if (function_exists('proc_open')) {
            try {
                $cmd = sprintf(
                    'node -e "%s" %s %s',
                    addcslashes($script, '"\\'),
                    escapeshellarg($password),
                    escapeshellarg($salt)
                );
                $descriptors = [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ];
                $proc = @proc_open($cmd, $descriptors, $pipes);
                if (is_resource($proc)) {
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                    if (!empty(trim($output))) return trim($output);
                }
            } catch (\Throwable $e) {}
        }

        // Metode 3: exec jika tersedia
        if (function_exists('exec')) {
            try {
                $cmd = sprintf(
                    'node -e "%s" %s %s',
                    addcslashes($script, '"\\'),
                    escapeshellarg($password),
                    escapeshellarg($salt)
                );
                $output = [];
                @exec($cmd, $output);
                $joined = trim(implode('', $output));
                if (!empty($joined)) return $joined;
            } catch (\Throwable $e) {}
        }

        // Metode 4: shell_exec HANYA jika fungsi tersebut benar-benar ada
        if (function_exists('shell_exec')) {
            try {
                $cmd = sprintf(
                    'node -e "%s" %s %s',
                    addcslashes($script, '"\\'),
                    escapeshellarg($password),
                    escapeshellarg($salt)
                );
                $out = @shell_exec($cmd);
                if (!empty(trim($out))) return trim($out);
            } catch (\Throwable $e) {}
        }

        return null;
    }

    /**
     * Memperbarui hash lama di database menjadi Bcrypt standar
     */
    protected function upgradeToBcrypt(string $oldHash, string $plainPassword): void
    {
        try {
            $newBcrypt = password_hash($plainPassword, PASSWORD_BCRYPT);
            DB::table('account')
                ->where('password', $oldHash)
                ->update(['password' => $newBcrypt]);
        } catch (\Throwable $e) {}
    }

    /**
     * Check if the given hash has been hashed using the given options.
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }
}
