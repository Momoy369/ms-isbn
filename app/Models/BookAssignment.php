<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BookAssignment extends Model
{
    protected $fillable = [

        'book_id',

        'user_id',

        'role',

        'person_name',

        'assigned_at',

        'completed_at',

        'sla_days',

        'deadline_at'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }



    public function getSlaStatus()
    {
        if (
            $this->completed_at
        ) {

            return 'completed';
        }

        if (
            now()->gt(
                $this->deadline_at
            )
        ) {

            return 'overdue';
        }

        return 'on_track';
    }

    public function lateDays()
    {
        if (
            !$this->deadline_at
        ) {
            return 0;
        }

        if (
            now()->lte(
                $this->deadline_at
            )
        ) {
            return 0;
        }

        return now()
            ->diffInDays(
                $this->deadline_at
            );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function getWarningLevel()
    {
        if ($this->completed_at) {
            return 'completed';
        }

        if (
            now()->gt(
                $this->deadline_at
            )
        ) {
            return 'overdue';
        }

        if (
            now()->diffInHours(
                $this->deadline_at,
                false
            ) <= 24
        ) {
            return 'warning';
        }

        return 'safe';
    }
}