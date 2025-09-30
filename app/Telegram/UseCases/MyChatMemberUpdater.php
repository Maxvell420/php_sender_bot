<?php

namespace App\Telegram\UseCases;

use App\Models\BotChannel;
use App\Models\User;
use App\Telegram\Updates\MyChatMemberUpdate;
use App\Repositories\ {
    BotChannelRepository,
    UserRepository
};

use App\Telegram\Enums\ {
    ChannelUserStatus,
    ChatType
};

class MyChatMemberUpdater {

    public function __construct(private UserRepository $userRepository, private BotChannelRepository $botChannelRepository) {}

    public function handleUpdate(MyChatMemberUpdate $values): void {
        match ($values->getChatType()) {
            ChatType::Channel => $this->createBotChatRole($values),
            ChatType::Private => $this->createPrivateUser($values)
        };
    }

    private function createBotChatRole(MyChatMemberUpdate $values): void {
        // пока что только для бота
        if( env('TG_BOT_ID') != $values->getNewChatMemberUserId() ) {
            return;
        }

        $channel = $this->botChannelRepository->findByChannelId($values->getChatId());
        $status = match ($values->getNewStatus()) {
            ChannelUserStatus::Administrator => 1,
            ChannelUserStatus::Member => 2,
            ChannelUserStatus::Restricted => 3,
            ChannelUserStatus::Left => 4,
            ChannelUserStatus::Kicked => 5
        };

        if( !$channel ) {
            $channel = new BotChannel();
            $channel->channel_id = $values->getChatId();
            $channel->status = $status;
            $channel->tg_id = $values->getUserId();
        }
        else {
            $channel->status = $status;
            $channel->save();
        }

        $this->botChannelRepository->persist($channel);
    }

    private function createPrivateUser(MyChatMemberUpdate $values): void {
        $user_id = $values->getUserId();
        $user = $this->userRepository->findByTgId($user_id);
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
        $this->userRepository->persist($user);
        return $user;
    }
}
