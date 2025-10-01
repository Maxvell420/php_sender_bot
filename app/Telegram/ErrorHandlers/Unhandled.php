<?php

namespace App\Telegram\ErrorHandlers;

use App\Models\{
    Log,
    Update
};
use App\Repositories\{
    LogRepository,
    UpdateRepository
};

class Unhandled
{

    public function __construct(private UpdateRepository $updateRepository, private LogRepository $logRepository) {}

    public function handleErrorUpdate(string $error_message): void
    {
        $update_id = $this->updateRepository->getNextUpdateId();
        $update = new Update();
        $update->update_id = $update_id;
        $this->updateRepository->persist($update);

        $this->handleSendData($error_message);
    }

    public function handleException(string $message): void
    {
        $this->handleSendData($message);
    }

    private function handleSendData(string $error_message): void
    {
        $save_data = ['message' => $error_message, 'data' => 'no data'];
        $data = json_encode($save_data);
        $this->logError($data);
    }

    private function logError(string $message): void
    {
        $log = new Log();
        $log->info = $message;
        $this->logRepository->persist($log);
    }
}
