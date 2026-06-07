<?php

namespace App\Http\Controllers;

use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class AdminRouterController extends Controller
{
    public function index()
    {
        $routers = Router::latest()
            ->get();

        return view('admin.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('admin.routers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'nullable',
            'dns' => 'nullable',
            'assistance_phone' => 'nullable',
            'platform' => 'required',
            'status' => 'required',
        ]);

        $router = Router::create([
            'user_id' => null,
            'name' => $request->name,
            'location' => $request->location,
            'dns' => $request->dns,
            'assistance_phone' => $request->assistance_phone,
            'platform' => $request->platform,
            'status' => $request->status,
            'integration_key' => strtoupper(Str::random(8)),
        ]);

        ActivityLogger::log('router.created', $router, [
            'name' => $router->name,
            'owner_id' => $router->user_id,
            'status' => $router->status,
            'platform' => $router->platform,
        ], $request);

        return redirect()->route('admin.routers.index')
            ->with('success', 'Routeur ajoute avec succes.');
    }
}
