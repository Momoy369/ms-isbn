<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('book_assignments')
            ->join('books', 'books.id', '=', 'book_assignments.book_id')
            ->select([
                'book_assignments.id as assignment_id',
                'book_assignments.completed_at',
                'book_assignments.assigned_at',
                'books.created_at as book_created_at',
            ])
            ->orderBy('book_assignments.id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    if (!empty($row->completed_at)) {
                        continue;
                    }

                    $startAt = $row->book_created_at
                        ? \Carbon\Carbon::parse($row->book_created_at)
                        : now();

                    $deadlineAt = $startAt->copy()->addMonthNoOverflow();
                    $slaDays = (int) $startAt->diffInDays($deadlineAt);

                    DB::table('book_assignments')
                        ->where('id', $row->assignment_id)
                        ->update([
                            'sla_days' => $slaDays,
                            'deadline_at' => $deadlineAt,
                            'assigned_at' => $row->assigned_at ?: now(),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No-op: backfill migration intentionally does not restore old per-role short SLA.
    }
};
