<?php

namespace Webkul\Notification\services;

use Illuminate\Support\Facades\Http;

class Telegram
{
    protected $token;
    protected $channelId;

    public function __construct()
    {
        $this->token = config('telegram.bot_token');
        $this->channelId = config('telegram.channel_id');
    }

    public function sendMessage($text, $parse_mode = 'HTML'): void
    {
        Http::get("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->channelId,
            'text' => $text,
            'parse_mode' => $parse_mode,
        ]);

    }
}
