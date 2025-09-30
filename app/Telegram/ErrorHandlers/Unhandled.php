<?php

namespace App\Telegram\ErrorHandlers;

use App\Models\ {
    Log,
    Update
};
use App\Repositories\ {
    LogRepository,
    UpdateRepository
};

class Unhandled {

    public function __construct(private UpdateRepository $updateRepository, private LogRepository $logRepository) {}

    public function handleErrorUpdate(array $data, string $error_message): void {
        $update_id = $this->updateRepository->getNextUpdateId();
        $update = new Update();
        $update->update_id = $update_id + 1;
        $this->updateRepository->persist($update);

        $this->handleSendData($data, $error_message);
    }

    private function handleSendData(array $data, string $error_message): void {
        $save_data = ['message' => $error_message, 'data' => $data];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void {
        $log = new Log();
        $log->info = $message;
        $this->logRepository->persist($log);
    }
}
