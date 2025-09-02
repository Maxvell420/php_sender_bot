<?php

namespace App\Libs\Telegram;

use Exception;

class TelegramRequest
{

    private string $tg_url = 'https://api.telegram.org';

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

    public function sendMessage(array $message): void
    {
        $url = $this->tg_url;
        $data = ['post_fields' => $message, 'method' => CURLOPT_POST];
        $url = $this->buildUrlFromAction(TelegramActions::sendMessage);
        $this->sendRequest($url, $data);
    }

    public function sendDocument(array $document): void
    {
        $url = $this->tg_url;
        $data = ['post_fields' => $document, 'method' => CURLOPT_POST];
        $url = $this->buildUrlFromAction(TelegramActions::sendDocument);
        $this->sendRequest($url, $data);
    }

    public function sendPhoto(array $photo): void
    {
        $url = $this->tg_url;
        $data = ['post_fields' => $photo, 'method' => CURLOPT_POST];
        $url = $this->buildUrlFromAction(TelegramActions::sendPhoto);
        $this->sendRequest($url, $data);
    }

    private function buildUrlFromAction(TelegramActions $action, ?int $offset = null): string
    {
        $query = '';

        if ($offset) {
            $query = '?' . http_build_query(
                [
                    'offset' => $offset,
                    'timeout' => 0,
                    'limit' => 10
                ]
            );
        }

        $tg_url = $this->tg_url;
        $secret = $this->secret;

        // Доставать из енама
        return match ($action) {
            TelegramActions::getUpdates => "$tg_url/bot$secret/getUpdates$query",
            TelegramActions::sendMessage => "$tg_url/bot$secret/sendMessage$query",
            TelegramActions::sendDocument => "$tg_url/bot$secret/sendDocument$query",
            TelegramActions::sendPhoto => "$tg_url/bot$secret/sendPhoto$query",
            default => throw new Exception('WRONG_ACTION', 404)
        };
    }

    private function sendRequest(string $url, array $params = []): array
    {

        // dd($params);
        $method = $params['method'] ?? CURLOPT_HTTPGET;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method == CURLOPT_POST) {
            curl_setopt($curl, $method, true);
            $post_fiels = $params['post_fields'];
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
