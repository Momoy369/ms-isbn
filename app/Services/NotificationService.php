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
}