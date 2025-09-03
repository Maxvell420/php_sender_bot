<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MyChatMemberUpdate;
use App\Models\User;
use App\Telegram\Updates\Update as UpdateInterface;

class MyChatMemberUpdater extends UpdateHandler {

    public function handleUpdate(UpdateInterface $values): void {
        /**
         * @var MyChatMemberUpdate $values
         */

        $user_id = $values->getUserId();
        $user = (new User)->findByTgId($user_id);
        $status = $values->isMember();

        if( !$user ) {
            $user = $this->createNewUser($values->getUserName(), $values->getUserId(), $status);
        }

        if( $status ) {
            $user->setMember();
        }
        else {
            $user->setKicked();
        }

        $user->save();
    }

    private function createNewUser(string $user_name, int $tg_id, bool $member): User {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;
        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
