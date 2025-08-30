<?php

namespace App\Telegram\UseCases;

use App\Telegram\{
    Enums
};
// Корневой класс который все разруливает
class Updates
{
    public function __construct(private MyChatMemberUpdater $myChatMember = new MyChatMemberUpdater) {}

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
        match ($case) {
            Enums\UpdateType::MyChatMember => $this->myChatMember->handleUpdate($update)
        };
    }
}
