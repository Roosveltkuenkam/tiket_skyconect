<?php

namespace App\Http\Controllers;

use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRouterController extends Controller
{
    public function index()
    {
        $routers = Router::latest()->get();

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

        Router::create([
            'name' => $request->name,
            'location' => $request->location,
            'dns' => $request->dns,
            'assistance_phone' => $request->assistance_phone,
            'platform' => $request->platform,
            'status' => $request->status,
            'integration_key' => strtoupper(Str::random(8)),
        ]);

        return redirect()->route('admin.routers.index')
            ->with('success', 'Routeur ajouté avec succès.');
    }
}