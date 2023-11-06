<?php

namespace Deri\Promotheus;
use GuzzleHttp\Client;

class IhsConnection
{
    protected string $base_url;
    protected array $config;
    protected array $headers;

    public function __construct(string $koders)
    {
        $configs = require __DIR__ . '/../config/config.php';
        $urls = require __DIR__ . '/../config/base_url.php';

        if (isset($configs[$koders])) {
            $this->config = $configs[$koders] ?: [];

            if (count($this->config) > 0) {
                $this->base_url = $urls[$this->config['env']];
            }
        }
    }

    public static function using(string $koders): self
    {
        return new self($koders);
    }

    public function send(string $nomor, int $kodedokter)
    {
        if (empty($this->config)) {
            return new IhsResource([
                'response' => null,
                'metaData' => [
                    'code' => 500,
                    'message' => 'Maaf, rumah sakit ini belum terdaftar di layanan kamihhhh.'
                ]
            ], '');
        }

        $this->generateHeader();
        $client = new Client(['base_uri' => $this->base_url, 'timeout' => 7]);

        try {
            $response = $client->post('api/rs/validate', [
                'headers' => $this->headers,
                'json' => [
                    'param' => $nomor,
                    'kodedokter' => $kodedokter
                ]
            ]);

            return new IhsResource(json_decode((string) $response->getBody(), 1), $this->config['consid'] . $this->config['conspas'] . $this->headers['X-timestamp']);
        }catch (\Exception $e) {
            return new IhsResource([
                    'response' => null,
                    'metaData' => [
                        'code' => 500,
                        'message' => $e->getMessage()
                    ]
                ], '');
        }
    }

    protected function generateHeader()
    {
        $timestamp = strtotime('now');

        $this->headers = [
            'User-Agent' => 'Deri/IhsClient',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-cons-id' => $this->config['consid'],
            'X-timestamp' => $timestamp,
            'X-signature' => $this->createSignature($timestamp),
            'user_key' => $this->config['userkey']
        ];
    }

    protected function createSignature(int $timestamp)
    {
        $data = "{$this->config['consid']}&{$timestamp}";
        $secretkey = $this->config['conspas'];
        $signature = hash_hmac('sha256', $data, $secretkey, true);

        return base64_encode($signature);
    }
}
