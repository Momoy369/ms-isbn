<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where(
            'user_id',
            auth()->id()
        )
            ->latest()
            ->paginate(20);

        return view(
            'notifications.index',
            compact('notifications')
        );
    }

    public function read(
        Notification $notification
    ) {
        abort_unless(

            $notification->user_id
            ==
            auth()->id(),

            403

        );

        $notification->update([

            'is_read' => true

        ]);

        return back();
    }

    public function readAll()
    {
        Notification::where(
            'user_id',
            auth()->id()
        )
            ->where(
                'is_read',
                false
            )
            ->update([

                'is_read' => true

            ]);

        return back();
    }
}