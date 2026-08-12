<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class HighTrafficAlertNotification extends Notification
{

    protected $trafficData;

    public function __construct($trafficData = [])
    {
        $this->trafficData = $trafficData;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $requestsPerMin = $this->trafficData['requests_per_minute'] ?? 'N/A';
        $connectedUsers = $this->trafficData['connected_users'] ?? 'N/A';
        
        return new DatabaseMessage([
            'title' => 'High Traffic Alert',
            'message' => "High traffic detected: $requestsPerMin req/min, $connectedUsers active users",
            'type' => 'high_traffic',
            'traffic_data' => $this->trafficData,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        $requestsPerMin = $this->trafficData['requests_per_minute'] ?? 'N/A';
        $connectedUsers = $this->trafficData['connected_users'] ?? 'N/A';
        
        return [
            'title' => 'High Traffic Alert',
            'message' => "High traffic detected: $requestsPerMin req/min, $connectedUsers active users",
            'type' => 'high_traffic',
            'traffic_data' => $this->trafficData,
        ];
    }
}
