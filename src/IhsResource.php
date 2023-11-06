<?php

namespace Deri\Promotheus;

class IhsResource
{
    protected string $key;
    protected array $response;
    protected array $meta_data;
    protected string $decrypted;

    public function __construct(array $response, string $key)
    {
        $this->key = $key;
        $this->response = $response;
        $this->meta_data = $response['metaData'];
    }

    protected function decrypt()
    {
        $this->decrypted = Cryptor::decrypt($this->response['response'], $this->key);
    }

    public function json()
    {
        try {
            $this->decrypt();
            return json_decode($this->decrypted, 1);
        } catch (\Throwable $th) {
            return $this->response;
        }   
    }

    public function getMetaData()
    {
        return $this->meta_data;
    }
}
