<?php

namespace App\Services;

use App\Models\Book;

class ApprovalService
{
    public function approve(
        Book $book,
        string $type,
        string $person
    ) {
        $book->approvals()
            ->updateOrCreate(

                [

                    'approval_type' =>
                        $type

                ],

                [

                    'approved_by' =>
                        $person,

                    'approved_at' =>
                        now()

                ]

            );
    }

    public function isReadyForISBN(
        Book $book
    ) {
        $required = [

            'editor',

            'layout',

            'author'

        ];

        foreach (
            $required as $type
        ) {

            if (
                !$book->approvals()
                    ->where(
                        'approval_type',
                        $type
                    )
                    ->exists()
            ) {

                return false;
            }
        }

        return true;
    }
}