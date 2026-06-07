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

        $logs->getCollection()->transform(function ($log) {
            $log->safe_details = $this->safeDetails($log->details ?: []);

            return $log;
        });

        $users = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $resources = ActivityLog::whereNotNull('resource_type')
            ->select('resource_type')
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type');

        return view('admin.audit.index', compact('logs', 'users', 'actions', 'resources'));
    }

    private function safeDetails(array $details)
    {
        $rows = [];

        foreach ($details as $key => $value) {
            if ($this->isSensitiveKey($key)) {
                $rows[] = [$this->label($key), __('ui.audit.hidden')];
                continue;
            }

            if (in_array($key, ['old_values', 'new_values'], true)) {
                continue;
            }

            if ($key === 'filters' && is_array($value)) {
                $summary = $this->safeAssociativeSummary($value);
                $rows[] = [__('ui.audit.filters'), $summary ?: __('ui.audit.not_available')];
                continue;
            }

            if (is_array($value)) {
                $rows[] = [$this->label($key), $this->safeAssociativeSummary($value) ?: __('ui.audit.list_count', ['count' => count($value)])];
                continue;
            }

            $rows[] = [$this->label($key), $this->maskValue($key, $value)];
        }

        $changedFields = $this->changedFields($details);

        if ($changedFields) {
            $rows[] = [__('ui.audit.changed_fields'), implode(', ', $changedFields)];
        }

        return $rows;
    }

    private function changedFields(array $details)
    {
        $old = $details['old_values'] ?? [];
        $new = $details['new_values'] ?? [];

        if (! is_array($old) || ! is_array($new)) {
            return [];
        }

        $ignored = ['id', 'created_at', 'updated_at', 'email_verified_at', 'remember_token', 'password'];
        $fields = array_unique(array_merge(array_keys($old), array_keys($new)));

        return array_values(array_filter($fields, function ($field) use ($old, $new, $ignored) {
            if (in_array($field, $ignored, true) || $this->isSensitiveKey($field)) {
                return false;
            }

            return ($old[$field] ?? null) !== ($new[$field] ?? null);
        }));
    }

    private function safeAssociativeSummary(array $values)
    {
        $parts = [];

        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey($key) || $value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $parts[] = $this->label($key) . ': ' . __('ui.audit.list_count', ['count' => count($value)]);
                continue;
            }

            $parts[] = $this->label($key) . ': ' . $this->maskValue($key, $value);
        }

        return implode(' | ', array_slice($parts, 0, 6));
    }

    private function maskValue($key, $value)
    {
        if ($this->isSensitiveKey($key)) {
            return __('ui.audit.hidden');
        }

        if (is_bool($value)) {
            return $value ? __('ui.audit.yes') : __('ui.audit.no');
        }

        return (string) $value;
    }

    private function isSensitiveKey($key)
    {
        foreach (['password', 'token', 'secret', 'raw_response', 'api_key', 'private_key', 'integration_key', 'email', 'phone'] as $sensitive) {
            if (stripos((string) $key, $sensitive) !== false) {
                return true;
            }
        }

        return false;
    }

    private function label($key)
    {
        return ucfirst(str_replace('_', ' ', (string) $key));
    }
}
