<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SystemBackupCompletedNotification extends Notification
{

    protected $backupStatus;
    protected $message;

    public function __construct($status, $message = null)
    {
        $this->backupStatus = $status;
        $this->message = $message ?? ($status === 'success' ? 'System backup completed successfully' : 'System backup failed - manual intervention may be required');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'System Backup ' . ucfirst($this->backupStatus),
            'message' => $this->message,
            'type' => 'backup_' . $this->backupStatus,
            'status' => $this->backupStatus,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'System Backup ' . ucfirst($this->backupStatus),
            'message' => $this->message,
            'type' => 'backup_' . $this->backupStatus,
            'status' => $this->backupStatus,
        ];
    }
}
