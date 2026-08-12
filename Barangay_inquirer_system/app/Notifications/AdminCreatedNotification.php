<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AdminCreatedNotification extends Notification
{

    protected $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'New Admin Created',
            'message' => 'Admin account created: ' . $this->admin->name,
            'type' => 'admin_created',
            'admin_id' => $this->admin->id,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Admin Created',
            'message' => 'Admin account created: ' . $this->admin->name,
            'type' => 'admin_created',
            'admin_id' => $this->admin->id,
        ];
    }
}
