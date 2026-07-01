<?php

namespace App\Services;
use App\Models\Notification;
use App\Models\Book;

class NotificationService
{
    public function send(
        int $userId,
        string $title,
        string $message,
        ?int $bookId = null
    ) {
        Notification::create([

            'user_id' => $userId,

            'book_id' => $bookId,

            'title' => $title,

            'message' => $message

        ]);
    }

    public function sendToBookTeam(
        Book $book,
        string $title,
        string $message,
        int $excludeUserId = null
    ) {
        foreach (
            $book->notificationRecipients()
            as $user
        ) {

            if (
                $excludeUserId &&
                $user->id ==
                $excludeUserId
            ) {
                continue;
            }

            $this->send(

                $user->id,

                $title,

                $message,

                $book->id

            );
        }
    }

    public function sendToBookRoles(
        Book $book,
        array $roles,
        string $title,
        string $message,
        ?int $excludeUserId = null
    ): void {
        $roles = array_values(array_unique(array_filter(array_map('strval', $roles))));

        if (empty($roles)) {
            return;
        }

        $recipients = collect();

        foreach ($book->assignments as $assignment) {
            if ($assignment->user && in_array((string) $assignment->role, $roles, true)) {
                $recipients->push($assignment->user);
            }
        }

        foreach ($recipients->unique('id') as $user) {
            if ($excludeUserId && (int) $user->id === (int) $excludeUserId) {
                continue;
            }

            $this->send(
                (int) $user->id,
                $title,
                $message,
                $book->id
            );
        }
    }
}