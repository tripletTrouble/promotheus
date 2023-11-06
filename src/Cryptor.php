<?php

namespace Deri\Promotheus;

use LZCompressor\LZString;

class Cryptor
{
    public static function decrypt(string $string, string $key)
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr($key_hash, 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);
    
        return LZString::decompressFromEncodedURIComponent($output);
    }
}
