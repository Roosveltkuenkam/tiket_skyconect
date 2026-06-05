<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->filled('resource_type'), function ($query) use ($request) {
                $query->where('resource_type', $request->resource_type);
            })
            ->when($request->filled('ip'), function ($query) use ($request) {
                $query->where('ip_address', 'like', '%' . $request->ip . '%');
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest('created_at')
            ->paginate(50)
            ->appends($request->query());

        $users = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $resources = ActivityLog::whereNotNull('resource_type')
            ->select('resource_type')
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type');

        return view('admin.audit.index', compact('logs', 'users', 'actions', 'resources'));
    }
}
