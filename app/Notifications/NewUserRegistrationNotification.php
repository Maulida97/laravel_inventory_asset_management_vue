<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $newUser
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'AssetFlow');

        return (new MailMessage)
            ->subject(__('Pendaftaran Akun Baru Menunggu Persetujuan — :appName', ['appName' => $appName]))
            ->view('emails.new-user-registration', [
                'admin' => $notifiable,
                'newUser' => $this->newUser,
                'appName' => $appName,
                'reviewUrl' => route('admin.user-registrations.index'),
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
            'user_id' => $this->newUser->id,
            'name' => $this->newUser->name,
            'email' => $this->newUser->email,
            'employee_id' => $this->newUser->employee_id,
            'department_id' => $this->newUser->department_id,
            'department_name' => $this->newUser->department?->name,
            'position' => $this->newUser->position,
            'status' => 'pending',
            'action_url' => route('admin.user-registrations.index'),
        ];
    }
}
