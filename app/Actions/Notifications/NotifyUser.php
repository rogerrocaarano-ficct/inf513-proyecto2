<?php

namespace App\Actions\Notifications;

use App\Models\GraduationProcess;
use App\Models\Notification;
use App\Models\User;

/**
 * Leaves a notification inside the system for a user (C-005: never by email or another channel).
 */
class NotifyUser
{
    public function handle(User $recipient, string $message, ?GraduationProcess $process = null): Notification
    {
        return Notification::query()->create([
            'user_id' => $recipient->id,
            'graduation_process_id' => $process?->id,
            'message' => $message,
        ]);
    }
}
