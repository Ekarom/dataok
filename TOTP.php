<?php
class TOTP
{
    private static $base32Map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random base32 secret.
     */
    public static function generateSecret($length = 16)
    {
        $secret = "";
        for ($i = 0; $i < $length; $i++) {
            try {
                $secret .= self::$base32Map[random_int(0, 31)];
            } catch (Exception $e) {
                $secret .= self::$base32Map[rand(0, 31)];
            }
        }
        return $secret;
    }

    /**
     * Decodes a base32 string into binary data.
     */
    private static function base32Decode($in)
    {
        // Standardize input: uppercase and remove padding/invalid chars
        $in = strtoupper(trim($in));
        $in = str_replace('=', '', $in);
        
        $l = strlen($in);
        $n = 0;
        $j = 0;
        $binary = "";

        for ($i = 0; $i < $l; $i++) {
            $char = $in[$i];
            $pos = strpos(self::$base32Map, $char);
            
            if ($pos === false) continue; // Skip invalid characters

            $n = $n << 5;
            $n = $n + $pos;
            $j = $j + 5;

            if ($j >= 8) {
                $j = $j - 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }

    /**
     * Get the 6-digit TOTP code.
     */
    public static function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = self::base32Decode($secret);

        // Pack time into 8-byte binary string (RFC 6238)
        // Manual null padding ensures compatibility with 32-bit PHP systems
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N', $timeSlice);

        $hmac = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashpart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;

        return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a presented 6-digit code.
     */
    public static function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }

        return false;
    }
}
?>
