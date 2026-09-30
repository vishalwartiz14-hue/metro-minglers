<?php

namespace App\Http\Controllers;

use App\Models\Mingle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(25),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $mingle = Mingle::query()->find($item->data['mingle_id'] ?? null);

        return $mingle ? to_route('mingles.show', $mingle)->withFragment('updates') : to_route('notifications.index');
    }
}
