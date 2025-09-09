<?php

namespace App\Libs\Telegram;

use Exception;

class TelegramRequest {

    private string $tg_url = 'https://api.telegram.org';

    public function __construct(private string $secret) {}

    /**
     * Функция получения обновлений из telegram
     * @param int $offset на самом деле это update_id
     */
    public function getUpdates(?int $offset = null, ?int $timeout = null): array {
        $url = $this->buildUrlFromAction(TelegramActions::getUpdates, $offset, $timeout);
        return $this->sendRequest($url);
    }

    public function sendMessage(TelegramActions $action, array $message): void {
        $url = $this->tg_url;
        $data = ['post_fields' => $message, 'method' => CURLOPT_POST];
        $url = $this->buildUrlFromAction($action);
        $this->sendRequest($url, $data);
    }

    private function buildUrlFromAction(TelegramActions $action, ?int $offset = null, ?int $timeout = null): string {
        $query = '';

        if( !$timeout ) {
            $timeout = 0;
        }

        if( $offset ) {
            $query = '?' . http_build_query(
                [
                    'offset' => $offset,
                    'timeout' => $timeout,
                    'limit' => 10
                ]
            );
        }

        $tg_url = $this->tg_url;
        $secret = $this->secret;

        return "$tg_url/bot$secret/$action->value$query";
    }

    private function sendRequest(string $url, array $params = []): array {
        // dd(mb_strlen($params['post_fields']['caption']));
        $method = $params['method'] ?? CURLOPT_HTTPGET;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if( $method == CURLOPT_POST ) {
            curl_setopt($curl, $method, true);
            $post_fiels = $params['post_fields'];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fiels);
        }

        $response = curl_exec($curl);
        dd($response);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if( $httpCode == 200 ) {
            return json_decode($response, true);
        }
        elseif( $httpCode == 400 ) {
            return [];
        }
        else {
            throw new Exception('WRONG_CURL_RESPONSE', $httpCode);
        }
    }
}
