<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');
        
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }
        
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }
        
        if ($request->filled('entity_type')) {
            $query->forEntity($request->entity_type, $request->entity_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
