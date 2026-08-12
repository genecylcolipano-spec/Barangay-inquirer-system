<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContactMessageSubmitted extends Notification
{

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Contact Message: ' . ($this->payload['subject'] ?? ''))
            ->greeting('New Contact Form Submission')
            ->line('You have received a new contact message from the public website.')
            ->line('Name: ' . ($this->payload['name'] ?? ''))
            ->line('Email: ' . ($this->payload['email'] ?? ''))
            ->line('Phone: ' . ($this->payload['phone'] ?? 'N/A'))
            ->line('Subject: ' . ($this->payload['subject'] ?? ''))
            ->line('Message:')
            ->line($this->payload['message'] ?? '')
            ->salutation('Barangay Inquirer System');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'contact_message',
            'title' => 'New contact message received',
            'name' => $this->payload['name'] ?? null,
            'email' => $this->payload['email'] ?? null,
            'phone' => $this->payload['phone'] ?? null,
            'subject' => $this->payload['subject'] ?? null,
            'message' => $this->payload['message'] ?? null,
        ];
    }
}
