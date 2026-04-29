<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'action' => 'nullable|string|max:50',
            'entity_type' => 'nullable|string|max:50',
            'entity_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = ActivityLog::with('user');

        if (!empty($validated['user_id'])) {
            $query->forUser($validated['user_id']);
        }

        if (!empty($validated['action'])) {
            $query->byAction($validated['action']);
        }

        if (!empty($validated['entity_type'])) {
            $query->forEntity($validated['entity_type'], $validated['entity_id'] ?? null);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $logs = $query->recent()->paginate(50)->withQueryString();

        // Get unique values for filters
        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $entityTypes = ActivityLog::select('entity_type')->distinct()->pluck('entity_type');

        return view('admin.activity-logs.index', compact('logs', 'actions', 'entityTypes'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function userLogs(Request $request, $userId)
    {
        $logs = ActivityLog::with('user')
            ->forUser($userId)
            ->recent()
            ->paginate(50)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'userId'));
    }

    public function entityLogs(Request $request, $entityType, $entityId = null)
    {
        $query = ActivityLog::with('user')->forEntity($entityType, $entityId);

        $logs = $query->recent()->paginate(50)->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'entityType', 'entityId'));
        
    }
}
