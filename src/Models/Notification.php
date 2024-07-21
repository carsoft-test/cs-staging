<?php

declare(strict_types=1);

namespace Webkul\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Notification\Contracts\Notification as NotificationContract;
use Webkul\Notification\Services\Telegram;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Notification extends Model implements NotificationContract
{
    protected $fillable = [
        'type',
        'read',
        'notifiable_type',
        'notifiable_id',
        'customer_id',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function notify()
    {
        return $this->belongsTo(
            NotificationTypeProxy::modelClass(),
            'notifiable_id',
            'id'
        );
    }

    public function toTelegram($notifiable)
    {
        // Determine the URL based on the type
        $_route = $this->type === 'order'
            ? route('admin.sales.orders.view', ['id' => $this->notifiable_id])
            : route('admin.writeprogram.view', ['id' => $this->notifiable_id]);

        // Format the message with the appropriate variables
        $text = "Your {$this->type} #{$this->notifiable_id} placed on {$this->updated_at->format('Y-m-d H:i:s')} has been {$this->notify->status}. <a href=\"{$_route}\">Track your {$this->type}</a>";

        return [
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $telegram = App::make(Telegram::class); // Resolve Telegram service

            $message = $model->toTelegram($model);
            if (!is_null($message)) {
                $telegram->sendMessage($message['text'], $message['parse_mode']);
            }
        });

        static::updated(function ($model) {
            $telegram = App::make(Telegram::class); // Resolve Telegram service

            $message = $model->toTelegram($model);
            if (!is_null($message)) {
                $telegram->sendMessage($message['text'], $message['parse_mode']);
            }
        });
    }
}
