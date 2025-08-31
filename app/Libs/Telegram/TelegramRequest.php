<?php

namespace App\Libs\Telegram;

use Exception;

class TelegramRequest
{

    public function __construct(private string $secret) {}


    /**
     * Функция получения обновлений из telegram
     * @param int $offset на самом деле это update_id
     */
    public function getUpdates(?int $offset = null): array
    {
        $url = $this->buildUrlFromAction(TelegramActions::getUpdates, $offset);
        return $this->sendRequest($url);
    }

    private function buildUrlFromAction(TelegramActions $action, ?int $offset): string
    {
        $query = '';
        if ($offset) {
            $query = '?' . http_build_query([
                'offset' => $offset,
                'timeout' => 0,
                'limit' => 10
            ]);
        }
        $tg_url = 'https://api.telegram.org';
        $secret = $this->secret;

        return match ($action) {
            TelegramActions::getUpdates => "$tg_url/bot$secret/getUpdates$query",
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
