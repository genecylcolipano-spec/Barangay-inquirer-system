<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class NewDocumentRequestSubmitted extends Notification
{
    protected $documentRequest;
    protected $requester;

    /**
     * Create a new notification instance.
     */
    public function __construct($documentRequest, $requester)
    {
        $this->documentRequest = $documentRequest;
        $this->requester = $requester;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Document Request Submitted')
            ->greeting('New Document Request')
            ->line("{$this->requester->name} has submitted a new {$this->documentRequest->document_type} request.")
            ->line('Request ID: ' . $this->documentRequest->id)
            ->line('Requester: ' . $this->requester->name . ' (' . $this->requester->email . ')')
            ->line('Document Type: ' . str_replace('_', ' ', $this->documentRequest->document_type))
            ->line('Status: ' . $this->documentRequest->status)
            ->action('View Request', url('/admin/requests/' . $this->documentRequest->id))
            ->salutation('Barangay Inquirer System');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'New Document Request',
            'message' => "{$this->requester->name} submitted a new {$this->documentRequest->document_type} request",
            'request_id' => $this->documentRequest->id,
            'requester_name' => $this->requester->name,
            'document_type' => $this->documentRequest->document_type,
            'status' => $this->documentRequest->status,
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Document Request',
            'message' => "{$this->requester->name} submitted a new {$this->documentRequest->document_type} request",
            'request_id' => $this->documentRequest->id,
            'requester_name' => $this->requester->name,
            'document_type' => $this->documentRequest->document_type,
            'status' => $this->documentRequest->status,
        ];
    }
}
