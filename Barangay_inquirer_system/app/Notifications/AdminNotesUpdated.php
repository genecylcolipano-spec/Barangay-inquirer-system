<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AdminNotesUpdated extends Notification
{

    protected $documentRequest;
    protected $oldNotes;
    protected $newNotes;

    /**
     * Create a new notification instance.
     */
    public function __construct($documentRequest, $oldNotes, $newNotes)
    {
        $this->documentRequest = $documentRequest;
        $this->oldNotes = $oldNotes;
        $this->newNotes = $newNotes;
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
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $documentType = ucfirst(str_replace('_', ' ', $this->documentRequest->document_type));

        if (empty($this->oldNotes) && !empty($this->newNotes)) {
            // New notes added
            $message = "Admin has added notes to your {$documentType} request.";
        } elseif (!empty($this->oldNotes) && !empty($this->newNotes)) {
            // Notes updated
            $message = "Admin has updated the notes on your {$documentType} request.";
        } else {
            // Notes removed
            $message = "Admin has removed notes from your {$documentType} request.";
        }

        \Log::info('Sending AdminNotesUpdated notification', [
            'user_id' => $notifiable->id,
            'request_id' => $this->documentRequest->id,
            'message' => $message
        ]);

        return new DatabaseMessage([
            'title' => 'Request Notes Updated',
            'message' => $message,
            'request_id' => $this->documentRequest->id,
            'document_type' => $this->documentRequest->document_type,
            'action' => 'notes_updated',
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
            'document_request_id' => $this->documentRequest->id,
            'old_notes' => $this->oldNotes,
            'new_notes' => $this->newNotes,
        ];
    }
}