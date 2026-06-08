<?php

namespace App\Support;

class Google2FA
{
    /**
     * Generate a random base32 string for the 2FA secret.
     */
    public static function generateSecretKey($length = 16): string
    {
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify a 6-digit code against the given secret.
     */
    public static function verifyKey($secret, $code, $discrepancy = 1): bool
    {
        $currentTimeSlice = floor(time() / 30);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals((string) $calculatedCode, (string) $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate the TOTP code for a specific time slice.
     */
    public static function getCode($secret, $timeSlice): string
    {
        $secretKey = self::base32Decode($secret);
        
        // Pack time into 64-bit binary string (big-endian)
        $time = pack("N", 0) . pack("N", $timeSlice);
        
        // Generate HMAC-SHA1
        $hashHmac = hash_hmac('sha1', $time, $secretKey, true);
        
        // Dynamic truncation
        $offset = ord(substr($hashHmac, -1)) & 0x0F;
        $hashPart = substr($hashHmac, $offset, 4);
        
        // Unpack to integer
        $value = unpack('N', $hashPart);
        $value = $value[1];
        
        // Mask most significant bit
        $value = $value & 0x7FFFFFFF;
        
        // Modulo 1 million for 6 digits
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the URL for the QR code provisioning.
     */
    public static function getQRCodeUrl($company, $holder, $secret): string
    {
        $company = rawurlencode($company);
        $holder = rawurlencode($holder);
        return "otpauth://totp/{$company}:{$holder}?secret={$secret}&issuer={$company}";
    }

    /**
     * Decode a base32 string into binary.
     */
    private static function base32Decode($secret)
    {
        if (empty($secret)) return '';
        
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];
        
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) {
                return false;
            }
        }
        
        $secret = str_replace('=', '', $secret);
        $secretArray = str_split($secret);
        $binaryString = '';
        
        for ($i = 0; $i < count($secretArray); $i = $i + 8) {
            $x = '';
            if (!in_array($secretArray[$i], str_split($base32chars))) return false;
            
            for ($j = 0; $j < 8; $j++) {
                if (!isset($secretArray[$i + $j])) break;
                $x .= str_pad(base_convert($base32charsFlipped[$secretArray[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                if (strlen($eightBits[$z]) < 8) continue;
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y : '';
            }
        }
        
        return $binaryString;
    }
}
