<?php

namespace App\Telegram\UseCases;

use App\Telegram\Updates\MyChatMemberUpdate;

class MyChatMemberUpdater extends UpdateHandler
{
    protected string $class = MyChatMemberUpdate::class;

    public function handleUpdate(array $data)
    {
        $data = $this->buildVO($data);
        dd($data);
    }
}
