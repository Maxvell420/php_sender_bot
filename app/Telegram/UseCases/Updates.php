<?php

namespace App\Telegram\UseCases;

use App\Telegram\ {
    Enums
};
use App\Models\Update;
use App\Telegram\Updates\Update as UpdateInterface;
// Корневой класс который все разруливает
class Updates {

    public function __construct(
        private MyChatMemberUpdater $myChatMember = new MyChatMemberUpdater,
        private MessageUpdater $messageUpdater = new MessageUpdater
    ) {}

    public function handleUpdates(array $updates): void {
        foreach($updates as $update) {
            foreach(Enums\UpdateType::cases() as $case) {
                if( isset($update[$case->value]) ) {
                    $this->handleUpdate($case, $update);
                }
            }
        }
    }

    private function handleUpdate(Enums\UpdateType $case, array $update): void {
        $updater = match ($case) {
            Enums\UpdateType::MyChatMember => $this->myChatMember,
            Enums\UpdateType::Message => $this->messageUpdater
        };

        $values = $updater->handleUpdate($update);

        $this->saveUpdate($values);
    }

    protected function saveUpdate(UpdateInterface $data) {
        $update = new Update;
        $update->update_id = $data->getUpdateId();
        $update->save();
    }
}
