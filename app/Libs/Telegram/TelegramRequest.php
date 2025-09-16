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
        $params = ['timeout' => $timeout];
        return $this->sendRequest($url, params:$params);
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
        $timeout = $params['timeout'] ?? 10 + 1;
        $method = $params['method'] ?? CURLOPT_HTTPGET;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if( $method == CURLOPT_POST ) {
            curl_setopt($curl, $method, true);
            $post_fiels = $params['post_fields'];
            // dd($post_fiels);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fiels);
        }

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout + 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout + 1);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $decoded_response = json_decode($response, true);

        if( $httpCode == 200 ) {
            return $decoded_response;
        }
        elseif( str_starts_with($httpCode, 4) ) {
            throw new TelegramApiException($decoded_response['description'], $httpCode);
        }
        elseif( is_bool($response) ) {
            throw new Exception('Ошибка с сетью?', $httpCode);
        }
        else {
            return [];
        }
    }
}
