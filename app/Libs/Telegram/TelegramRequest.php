<?php

namespace App\Libs\Telegram;

use Exception;

class TelegramRequest
{

    public function __construct(private string $secret) {}

    public function getUpdates(): array
    {
        $url = $this->buildUrlFromAction(TelegramActions::getUpdates);
        return $this->sendRequest($url);
    }

    private function buildUrlFromAction(TelegramActions $action): string
    {
        $tg_url = 'https://api.telegram.org';
        $secret = $this->secret;

        return match ($action) {
            TelegramActions::getUpdates => "$tg_url/bot$secret/getUpdates",
            default => throw new Exception('WRONG_ACTION', 404)
        };
    }

    private function sendRequest(string $url, array $params = []): array
    {
        $method = $params['method'] ?? CURLOPT_HTTPGET;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method == CURLOPT_POST) {
            curl_setopt($curl, $method, true);
            $post_fiels = [$params['post_fields']];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fiels);
        }

        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            throw new Exception('WRONG_CURL_RESPONSE', 500);
        }
    }
}
