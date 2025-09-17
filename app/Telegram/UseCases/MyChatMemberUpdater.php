<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MyChatMemberUpdate;
use App\Models\User;
use App\Telegram\Enums\ChatType;

class MyChatMemberUpdater {

    public function handleUpdate(MyChatMemberUpdate $values): void {
        match($values->getChatType()) {
            ChatType::Channel => $this->createBotChatRole($values),
            ChatType::Private => $this->createPrivateUser($values)
        };
    }

    private function createBotChatRole(MyChatMemberUpdate $values): void {}

    private function createPrivateUser(MyChatMemberUpdate $values): void {
        $user_id = $values->getUserId();
        $user = (new User)->findByTgId($user_id);
        $status = $values->isMember();

        if( !$user ) {
            $user = $this->createNewUser($values->getUserId(), $status, $values->getUserName(),);
        }

        if( $status ) {
            $user->setMember();
        }
        else {
            $user->setKicked();
        }

        $user->save();
    }

    private function createNewUser(int $tg_id, bool $member, ?string $user_name,): User {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;
        $admin = env('TG_USER');

        if( $admin == $user->tg_id ) {
            $user->is_admin = 'yes';
        }

        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
