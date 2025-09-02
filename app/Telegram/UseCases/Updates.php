<?php

namespace App\Telegram\UseCases;

use App\Telegram\{
    Enums
};
use App\Models\Update;
use App\Telegram\Updates\{MyChatMemberUpdate, MessageUpdate};
use App\Telegram\Updates\Update as UpdateInterface;
use Throwable;
use App\Http\Exceptions\ValidationException;
// Корневой класс который все разруливает
class Updates
{
    public function handleUpdates(array $updates): void
    {
        foreach ($updates as $update) {
            foreach (Enums\UpdateType::cases() as $case) {
                if (isset($update[$case->value])) {
                    $this->handleUpdate($case, $update);
                }
            }
        }
    }

    private function handleUpdate(Enums\UpdateType $case, array $update): void
    {

        [$updater, $values] = match ($case) {
            Enums\UpdateType::MyChatMember => [new MyChatMemberUpdater, $this->buildVO(MyChatMemberUpdate::class, $update)],
            Enums\UpdateType::Message => [new MessageUpdater, $this->buildVO(MessageUpdate::class, $update)],
        };

        $updater->handleUpdate($values);

        $this->saveUpdate($values);
    }

    private function saveUpdate(UpdateInterface $data)
    {
        $update = new Update;
        $update->update_id = $data->getUpdateId();
        $update->save();
    }

    private function buildVO(string $class, array $data): UpdateInterface
    {
        try {
            return $class::from($data);
        } catch (Throwable) {
            throw new ValidationException('WRONG_DATA', 422);
        }
    }
}
