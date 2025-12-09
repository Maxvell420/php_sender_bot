<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\{
    TelegramApiException,
};
use App\Telegram\{
    Enums,
    TelegramRequestFacade,
    TelegramUpdatesFacade
};
use App\Models\{
    Update
};

use App\Telegram\Updates\{
    CallbackQueryUpdate,
    ChannelPostUpdate,
    MyChatMemberUpdate,
    MessageUpdate
};
use App\Telegram\Updates\Update as UpdateInterface;
use App\Telegram\Exceptions\TelegramBaseException;
use Error;

// Корневой класс который все разруливает
class Updates
{

    public function __construct(
        private TelegramUpdatesFacade $telegramFacade,
        private TelegramRequestFacade $telegramRequestFacade
    ) {}

    public function work(): void
    {
        $job = $this->telegramFacade->findFirstJobNotCompleted();

        if ($job) {
            // Возможно как-то тут стоит обрабатывать ошибки...
            $this->telegramFacade->handleJob($job);
        }

        $update_id = $this->telegramFacade->getNextUpdateId();

        try {
            $updates = $this->telegramRequestFacade->getUpdates($update_id, 30);
        } catch (TelegramApiException $e) {
            $this->telegramFacade->handleWrongGetUpdates($e->getMessage());
            return;
        }

        if (empty($updates['result'])) {
            return;
        }

        foreach ($updates['result'] as $update) {
            $this->handleUpdate($update, $update_id);
        }
    }

    private function handleUpdate(array $data, int $max_update_id): void
    {
        foreach (Enums\UpdateType::cases() as $case) {
            if (isset($data[$case->value])) {
                $update = match ($case) {
                    Enums\UpdateType::MyChatMember => $this->buildVO(MyChatMemberUpdate::class, $data),
                    Enums\UpdateType::Message => $this->buildVO(MessageUpdate::class, $data),
                    Enums\UpdateType::CallbackQuery => $this->buildVO(CallbackQueryUpdate::class, $data),
                    Enums\UpdateType::ChannelPost => $this->buildVO(ChannelPostUpdate::class, $data)
                };
            }
        }

        if (!isset($update)) {
            throw new Error('WRONG_UPDATE');
        }

        if ($max_update_id + 1 != $update->getUpdateId()) {
            $this->dropUpdates();
        }

        $this->saveUpdate($update->getUpdateId());
        $this->processUpdate($update);
    }

    private function dropUpdates()
    {
        $updates = Update::all();
        foreach ($updates as $update) {
            $update->delete();
        }
    }


    private function processUpdate(UpdateInterface $update)
    {
        try {
            match ($update->getType()) {
                Enums\UpdateType::MyChatMember => $this->telegramFacade->handleNewChatmember($update),
                Enums\UpdateType::Message => $this->telegramFacade->handleMessage($update),
                Enums\UpdateType::CallbackQuery => $this->telegramFacade->handleCallback($update),
                ENums\UpdateType::ChannelPost => $this->telegramFacade->handleChannelPost($update)
            };
        } catch (TelegramBaseException $e) {
            $this->telegramFacade->handleWrongUpdate($update, $e->getMessage());
        } catch (TelegramApiException $e) {
            $this->telegramFacade->handleWrongTelegramRequest($update, $e->getMessage(), $e->getCode());
        }
    }

    private function saveUpdate(int $update_id)
    {
        $update = new Update;
        $update->update_id = $update_id;

        $this->telegramFacade->persistUpdate($update);
    }

    private function buildVO(string $class, array $data): UpdateInterface
    {
        return $class::from($data);
    }

    public function handleError(string $message): void
    {
        // Добавить тут обработчик ?
        $this->telegramFacade->handleErrorUpdate($message);
    }

    public function handleException(string $message): void
    {
        $this->telegramFacade->handleException($message);
    }
}
