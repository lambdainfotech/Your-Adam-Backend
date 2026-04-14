<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuthobartaSMSService
{
    protected string $baseUrl = 'https://sysadmin.muthobarta.com/api/v1';

    public function sendSMS(string $receiver, string $message, bool $removeDuplicate = true): array
    {
        $apiKey = config('services.muthobarta.api_key') ?: \App\Models\Setting::get('sms_muthobarta_api_key');

        if (empty($apiKey)) {
            Log::error('Muthobarta SMS API key is not configured.');
            return [
                'success' => false,
                'message' => 'SMS API key not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/send-sms", [
                'receiver' => $receiver,
                'message' => $message,
                'remove_duplicate' => $removeDuplicate,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            Log::error('Muthobarta SMS API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS gateway returned error: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Muthobarta SMS exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ];
        }
    }
}
