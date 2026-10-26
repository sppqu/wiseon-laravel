<?php

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;

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
     * Hash the given value.
     */
    public function make($value, array $options = [])
    {
        $cost = $options['rounds'] ?? 12;
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Check the given plain value against a hash.
     * Mendukung:
     * 1. Bcrypt ($2y$, $2a$)
     * 2. Better-Auth scrypt (<salt_hex>:<key_hex>)
     * 3. Plaintext fallback
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (empty($hashedValue) || !is_string($hashedValue)) {
            return false;
        }

        // 1. Jika hash berupa Bcrypt standar ($2y$ atau $2a$)
        if (str_starts_with($hashedValue, '$2y$') || str_starts_with($hashedValue, '$2a$')) {
            return password_verify($value, $hashedValue);
        }

        // 2. Jika hash berupa format Better-Auth scrypt (salt_hex:key_hex)
        if (str_contains($hashedValue, ':')) {
            $parts = explode(':', $hashedValue);
            if (count($parts) === 2) {
                [$salt, $expectedKey] = $parts;
                
                // Gunakan node.js scrypt bawaan untuk verifikasi secepat kilat (~80ms)
                $cmd = sprintf(
                    'node -e "const crypto=require(\'crypto\');crypto.scrypt(process.argv[1],process.argv[2],64,{N:16384,r:16,p:1,maxmem:67108864},(e,k)=>{if(e)process.exit(1);process.stdout.write(k.toString(\'hex\'));});" %s %s',
                    escapeshellarg($value),
                    escapeshellarg($salt)
                );

                $calculatedKey = @shell_exec($cmd);
                if ($calculatedKey && hash_equals(trim($expectedKey), trim($calculatedKey))) {
                    return true;
                }
            }
        }

        // 3. Fallback: kecocokan plaintext (untuk user lama sebelum migrasi hash)
        if ($hashedValue === $value) {
            return true;
        }

        return false;
    }

    /**
     * Check if the given hash has been hashed using the given options.
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }
}
