<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MyChatMemberUpdate;
use App\Models\User;

class MyChatMemberUpdater extends UpdateHandler
{
    protected string $class = MyChatMemberUpdate::class;

    public function handleUpdate(array $data)
    {
        /**
         * @var MyChatMemberUpdate $values
         */
        $values = $this->buildVO($data);

        $user_id = $values->getUserId();
        $user = (new User)->findByTgId($user_id);
        $status = $values->isMember();

        if (!$user) {
            $user = $this->createNewUser($values->getUserName(), $values->getUserId(), $status);
        }

        if ($status) {
            $user->setMember();
        } else {
            $user->setKicked();
        }

        $user->save();

        $this->saveUpdate($values);
    }

    private function createNewUser(string $user_name, int $tg_id, bool $member): User
    {
        $user = new User();
        $user->user_name = $user_name;
        $user->tg_id = $tg_id;
        $member == true ? $user->setMember() : $user->setKicked();
        $user->save();
        return $user;
    }
}
