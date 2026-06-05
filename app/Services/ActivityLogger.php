<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $resource = null, array $details = [], Request $request = null)
    {
        $request = $request ?: request();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => self::resourceType($resource),
            'resource_id' => self::resourceId($resource),
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 1000) : null,
            'route_name' => $request && $request->route() ? $request->route()->getName() : null,
            'method' => $request ? $request->method() : null,
            'details' => self::sanitize($details),
            'created_at' => now(),
        ]);
    }

    private static function resourceType($resource)
    {
        if ($resource instanceof Model) {
            return get_class($resource);
        }

        return is_string($resource) ? $resource : null;
    }

    private static function resourceId($resource)
    {
        if ($resource instanceof Model) {
            return $resource->getKey();
        }

        return null;
    }

    private static function sanitize(array $details)
    {
        $blocked = ['password', 'token', 'secret', 'remember_token', 'raw_response'];

        foreach ($details as $key => $value) {
            foreach ($blocked as $blockedKey) {
                if (stripos((string) $key, $blockedKey) !== false) {
                    $details[$key] = '[hidden]';
                    continue 2;
                }
            }

            if (is_array($value)) {
                $details[$key] = self::sanitize($value);
            }
        }

        return $details;
    }
}
