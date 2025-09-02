<?php

namespace App\Telegram\UseCases;

use App\Telegram\ {
    Enums
};
use App\Models\ {
    Update,
    State
};
use App\Telegram\Updates\ {
    MyChatMemberUpdate,
    MessageUpdate
};
use App\Telegram\Updates\Update as UpdateInterface;
use Throwable;
use App\Http\Exceptions\ValidationException;
// Корневой класс который все разруливает
class Updates {

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
        [$updater, $values] = match ($case) {
            Enums\UpdateType::MyChatMember => [new MyChatMemberUpdater, $this->buildVO(MyChatMemberUpdate::class, $update)],
            Enums\UpdateType::Message => [new MessageUpdater, $this->buildVO(MessageUpdate::class, $update)],
        };

        $need_handle = true;

        if( $values->hasFrom() ) {
            // если нашли юзера и у него есть state то первое действие это всегда state
            $values->getUserId();
            $state = new State()->findByUser($values->getUserId());

            if( $values instanceof MessageUpdate ) {
                if( $state ) {
                    $updater = new StateUpdater;
                    $need_handle = $updater->handleUpdate($values, $state);
                }
            }

            $state->delete();
        }

        if( $need_handle ) {
            $updater->handleUpdate($values);
        }

        $this->saveUpdate($values);
    }

    private function saveUpdate(UpdateInterface $data) {
        $update = new Update;
        $update->update_id = $data->getUpdateId();
        $update->save();
    }

    private function buildVO(string $class, array $data): UpdateInterface {
        try {
            return $class::from($data);
        } catch (Throwable) {
            throw new ValidationException('WRONG_DATA', 422);
        }
    }
}
