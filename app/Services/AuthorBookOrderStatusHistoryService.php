<?php

namespace App\Services;

use App\Models\AuthorBookOrder;
use App\Models\AuthorBookOrderStatusHistory;

class AuthorBookOrderStatusHistoryService
{
    public function record(
        AuthorBookOrder $order,
        ?string $fromStatus,
        string $toStatus,
        ?string $note = null,
        ?int $changedByUserId = null,
        ?string $context = null
    ): AuthorBookOrderStatusHistory {
        return AuthorBookOrderStatusHistory::create([
            'author_book_order_id' => $order->id,
            'changed_by_user_id' => $changedByUserId,
            'context' => $context,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }
}
