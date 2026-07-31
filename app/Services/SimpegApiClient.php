<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SimpegApiClient
{
    public function login(): string
    {
        $this->ensureConfigured();

        $response = $this->http()
            ->asMultipart()
            ->post($this->url('auth/login'), [
                'email' => (string) config('simpeg.email'),
                'password' => (string) config('simpeg.password'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Login API SIMPEG gagal dengan HTTP '.$response->status().'.');
        }

        $token = trim((string) (
            $response->json('token')
            ?? $response->json('data.token')
            ?? $response->json('access_token')
        ));

        if ($token === '') {
            throw new RuntimeException('Token tidak ditemukan pada respons login API SIMPEG.');
        }

        return $token;
    }

    public function employees(int $start, int $limit, string $token): array
    {
        $response = $this->http()
            ->acceptJson()
            ->withToken($token)
            ->asMultipart()
            ->post($this->url('pegawai'), [
                'satker' => (string) config('simpeg.satker_code'),
                'start' => max(0, $start),
                'limit' => min(400, max(1, $limit)),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Pengambilan data pegawai gagal dengan HTTP '.$response->status().'.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Respons data pegawai SIMPEG bukan JSON yang valid.');
        }

        $data = data_get($payload, 'data', []);
        if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        if (! is_array($data)) {
            throw new RuntimeException('Daftar pegawai tidak ditemukan pada respons SIMPEG.');
        }

        return [
            'total' => max(0, (int) (
                data_get($payload, 'total')
                ?? data_get($payload, 'data.total')
                ?? count($data)
            )),
            'data' => array_values(array_filter($data, 'is_array')),
        ];
    }

    private function ensureConfigured(): void
    {
        foreach (['email', 'password', 'satker_code'] as $key) {
            if (blank(config('simpeg.'.$key))) {
                throw new RuntimeException('Konfigurasi SIMPEG_'.strtoupper($key).' belum diisi.');
            }
        }
    }

    private function url(string $path): string
    {
        return rtrim((string) config('simpeg.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function http(): PendingRequest
    {
        return Http::timeout((int) config('simpeg.timeout', 60))
            ->retry(2, 750, throw: false)
            ->withOptions(['verify' => config('simpeg.verify_ssl', true)]);
    }
}
