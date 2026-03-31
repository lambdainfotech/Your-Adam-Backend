<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::with('user');
        
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }
        
        $notifications = $query->recent()->paginate(50)->withQueryString();
        
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = User::active()->select('id', 'name', 'email')->get();
        $types = ['system', 'order', 'promotion', 'alert'];
        
        return view('admin.notifications.create', compact('users', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required_without:send_to_all|array',
            'user_ids.*' => 'exists:users,id',
            'send_to_all' => 'boolean',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image_url' => 'nullable|string|max:500',
            'action_url' => 'nullable|string|max:500',
        ]);
        
        if ($request->boolean('send_to_all')) {
            // Send to all active users
            User::active()->chunk(100, function ($users) use ($validated) {
                foreach ($users as $user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => $validated['type'],
                        'title' => $validated['title'],
                        'body' => $validated['body'],
                        'image_url' => $validated['image_url'] ?? null,
                        'action_url' => $validated['action_url'] ?? null,
                    ]);
                }
            });
        } else {
            foreach ($validated['user_ids'] as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'image_url' => $validated['image_url'] ?? null,
                    'action_url' => $validated['action_url'] ?? null,
                ]);
            }
        }
        
        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notifications sent successfully.');
    }

    public function show(Notification $notification)
    {
        $notification->load('user');
        return view('admin.notifications.show', compact('notification'));
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        
        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    public function logs()
    {
        $emailLogs = \App\Models\EmailLog::recent()->limit(50)->get();
        $smsLogs = \App\Models\SmsLog::recent()->limit(50)->get();
        
        return view('admin.notifications.logs', compact('emailLogs', 'smsLogs'));
    }
}
