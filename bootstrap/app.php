<?php

require __DIR__ . '/../vendor/autoload.php';

use Deri\Promotheus\IhsConnection;
use Deri\Promotheus\Output;


return new class
{
    protected static function validate(array $request) {
        if(empty($_REQUEST['koders'])) {
            return 'Maaf, rumah sakit ini belum terdaftar di layanan kami';
        }
        
        if (empty($_REQUEST['noka'])) {
            return 'Nomor Kartu/NIK harus diisi';
        }
        
        if (empty($_REQUEST['kodedokter'])) {
            return 'Kode dokter harus diisi';
        }
        
        if (strlen($_REQUEST['noka']) < 13) {
            return 'Nomor kartu peserta tidak boleh kurang dari 13 digit';
        }
        
        if (strlen($_REQUEST['noka']) > 13 && strlen($_REQUEST['noka']) < 16) {
            return 'NIK tidak boleh kurang dari 16 digit';
        }
        
        if (strlen($_REQUEST['noka']) > 16) {
            return 'NIK tidak boleh lebih dari 16 digit';
        }
        
        if (preg_match("/[^0-9]/", trim($_REQUEST['kodedokter']))) {
            return 'Kode dokter hanya boleh berisi angka';
        }
        
        return $request;
    }
    
    protected static function send(array $request) {
        $validated = self::validate($request);
    
        if (is_array($validated)) {
            $response = IhsConnection::using($_REQUEST['koders'])->send($_REQUEST['noka'], intval($_REQUEST['kodedokter']));
        
            if ($response->getMetaData()['code'] < 300) {
                $json = $response->json();
            
                return $json;
            }
            
            return $response->getMetaData()['message'];
        }
    
        return $validated;
    }
    
    public function up($request) {
        $result = self::send($request);
    
        if (is_array($result)) {
            return Output::redirect($result['url']);
        }
    
        return Output::showError($result);
    }
};