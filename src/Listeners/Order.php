<?php declare(strict_types=1); 

namespace Webkul\Notification\Listeners;

use Webkul\Notification\Events\CreateOrderNotification;
use Webkul\Notification\Events\UpdateOrderNotification;
use Webkul\Notification\Repositories\NotificationRepository;
use Webkul\WriteProgram\Repositories\WriteProgramRepository;

class Order
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected NotificationRepository $notificationRepository,
        protected WriteProgramRepository $writableRepository
    ){}

    /**
     * Create a new resource.
     *
     * @return void
     */
    public function createOrder($order)
    {
        $order->notifications()->create([
            'type' => 'order', 
            'customer_id' => auth()->guard('customer')->id()
        ]);

        event(new CreateOrderNotification);
    }

    /**
     * Fire an Event when the order status is updated.
     *
     * @return void
     */
    public function updateOrder($order)
    {
        if ($order->status == 'completed') {
            $this->writableRepository->subscribed_plan($order->id);
        }
        event(new UpdateOrderNotification([
            'id'     => $order->id,
            'status' => $order->status,
        ]));
    }
}
