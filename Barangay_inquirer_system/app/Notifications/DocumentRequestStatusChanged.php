<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class DocumentRequestStatusChanged extends Notification
{

    protected $documentRequest;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($documentRequest, $status)
    {
        $this->documentRequest = $documentRequest;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Request Status Updated',
            'message' => 'Your ' . str_replace('_', ' ', $this->documentRequest->document_type) . ' request is now ' . $this->status . '.',
            'request_id' => $this->documentRequest->id,
            'status' => $this->status,
            'document_type' => $this->documentRequest->document_type,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Request Status Updated',
            'message' => 'Your ' . str_replace('_', ' ', $this->documentRequest->document_type) . ' request is now ' . $this->status . '.',
            'request_id' => $this->documentRequest->id,
            'status' => $this->status,
            'document_type' => $this->documentRequest->document_type,
        ];
    }
}
