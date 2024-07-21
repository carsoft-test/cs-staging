<?php //declare(strict_types=1);
namespace Webkul\Notification\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;

class NotificationRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Notification\Contracts\Notification';
    }

    /**
     * Return Filtered Notification resources.
     */
    public function getParamsData(array $params): array
    {
        $query = $this->model->with('notify');

        if (isset($params['status']) && $params['status'] != 'All') {
            $query->whereHas('notify', function ($q) use ($params) {
                $q->where(['status' => $params['status']]);
            });
        }

        if (isset($params['read']) && isset($params['limit'])) {
            $query->where('read', $params['read'])->limit($params['limit']);
            $query->whereHas('notify', function ($q) {
                $q->whereNotIn('status', ['file_completed']);
            });
        } elseif (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        $notifications = $query->latest()->paginate($params['limit'] ?? 10);

        $statusCounts = $this->model->join('notification_types', 'notifications.notifiable_id', '=', 'notification_types.id')
            ->select('notification_types.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('notification_types.status')
            ->get();

        return ['notifications' => $notifications, 'status_counts' => $statusCounts];
    }

    /**
     * Return Notification resources.
     *
     * @return array
     */
    public function getAll(array $params = [])
    {
        $query = $this->model->with('notify')
            ->whereHas('notify', function ($query) {
                $query->whereNotIn(
                    'notification_types.status',
                    ['file_completed']
                );
            });
        $notifications = $query->latest()->paginate($params['limit'] ?? 10);
        $statusCounts = $this->model->join('notification_types', 'notifications.notifiable_id', '=', 'notification_types.id')
            ->select('notification_types.status', DB::raw('COUNT(*) as status_count'))
            ->whereNotIn('notification_types.status', ['file_completed'])
            ->groupBy('notification_types.status')
            ->get();

        return ['notifications' => $notifications, 'status_counts' => $statusCounts];
    }

    public function getCustomerParamsData(array $params): array
    {
        $query = $this->model->with('notify');

        if (isset($params['status']) && $params['status'] != 'All') {
            $query->whereHas('notify', function ($q) use ($params) {
                $q->where(['status' => $params['status']]);
            });
        }

        if (isset($params['read']) && isset($params['limit'])) {
            $query->where('read', $params['read'])->limit($params['limit']);
            $query->whereHas('notify', function ($q) {
                $q->whereIn('status', ['file_completed', 'pending', 'cancel', 'completed']);
            });

        } elseif (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        $notifications = $query->where(
            'customer_id',
            auth()->guard('customer')->id()
        )->latest()->paginate($params['limit'] ?? 10);

        $statusCounts = $this->model->join('notification_types', function ($join) {
            $join->on('notifications.notifiable_id', '=', 'notification_types.id');
            $join->on('notifications.type', '=', 'notification_types.notifiable_type');
            $join->where('notifications.customer_id', auth()->guard('customer')->id());
            $join->whereIn('notification_types.status', ['file_completed', 'pending', 'cancel', 'completed']);
        })
            ->where('notifications.customer_id', auth()->guard('customer')->id())
            ->select('notification_types.status', DB::raw('COUNT(*) as status_count'))
            ->groupBy('notification_types.status')
            ->get();

        return ['notifications' => $notifications, 'status_counts' => $statusCounts];
    }

    public function getCustomerAll(array $params = [])
    {
        $query = $this->model->with('notify')
            ->where(
                'customer_id',
                auth()->guard('customer')->id()
            )
            ->whereHas('notify', function ($query) {
                $query->whereNotIn(
                    'notification_types.status',
                    ['file_created']
                );
            });
        $notifications = $query->latest()->paginate($params['limit'] ?? 10);
        $statusCounts = $this->model->join('notification_types', 'notifications.notifiable_id', '=', 'notification_types.id')
            ->select('notification_types.status', DB::raw('COUNT(*) as status_count'))
            ->where(
                'notifications.customer_id',
                auth()->guard('customer')->id()
            )
            ->whereNotIn('notification_types.status', ['file_created'])
            ->groupBy('notification_types.status')
            ->get();

        return ['notifications' => $notifications, 'status_counts' => $statusCounts];

    }
}