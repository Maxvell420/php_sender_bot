<?php

namespace App\Telegram\UseCases;

use App\Libs\Telegram\TelegramActions;
use App\Models\{
    State,
    BotChannel,
    User
};
use App\Telegram\Updates\ChannelPostUpdate;

class ChannelPostUpdater
{

    public function __construct(private MessageBuilder $messageBuilder) {}

    public function handleUpdate(ChannelPostUpdate $update): void
    {
        $channel_id = $update->getChatId();
        $bot_channel = new BotChannel()->findByChannelId($channel_id);

        if (!$bot_channel) {
            return;
        }

        $user = new User()->findByTgId($bot_channel->tg_id);

        if (!$user || !$user->isAdmin()) {
            return;
        }

        $state = new State()->findByUser($user->tg_id);

        if (!$state) {
            return;
        }

        if ($update->hasMediaGroup()) {
            $json = json_decode($state->json, true);

            if (!$json) {
                $media_group = $update->getMediaGroup();
            } elseif (!isset($json['media_group_id'])) {
                $media_group = $update->getMediaGroup();
            } else {
                $media_group = $json['media_group_id'];
            }

            if ($media_group != $update->getMediaGroup()) {
                $media_group = $update->getMediaGroup();
                $message_ids = [$update->getMessageId()];
            } else {
                if (isset($json['data']['message_ids'])) {
                    $message_ids = json_decode($json['data']['message_ids'], true);
                } else {
                    $message_ids = [];
                }

                $message_ids[] = $update->getMessageId();
            }

            $new_json = [
                'method' => TelegramActions::copyMessages->value,
                'media_group_id' => $media_group,
                'data' => $this->messageBuilder->buildCopyMessages($state->actor_id, $channel_id, $message_ids)
            ];

            $state->json = $new_json;
            $state->save();
        } else {
            $state->json = [
                'method' => TelegramActions::copyMessage->value,
                'data' => $this->messageBuilder->buildCopyMessage($state->actor_id, $channel_id, $update->getMessageId())
            ];
            $state->save();
        }
    }
}
