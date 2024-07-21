<?php declare(strict_types=1); 

namespace Webkul\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Notification\Contracts\NotificationType as NotificationTypeContract;

class NotificationType extends Model implements NotificationTypeContract
{
    protected $table = "notification_types";

    protected $fillable = [
        'id',
        'status',
        'notifiable_type',
        'created_at'
      
    ];
    protected $appends = ['datetime'];

    public function getDatetimeAttribute()
    {
        return $this->updated_at?->diffForHumans();
    }
   
}