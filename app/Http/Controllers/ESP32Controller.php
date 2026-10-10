<?php

namespace App\Http\Controllers;

use App\Models\ESP32Registry;
use Illuminate\Http\Request;

class ESP32Controller extends Controller
{
    public function dashboard()
    {
        $records = ESP32Registry::whereToday('created_at')->latest()->get();
        return view('dashboard', compact('records'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ESP32Registry::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return ESP32Registry::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(ESP32Registry $eSP32Registry)
    {
        return $eSP32Registry;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ESP32Registry $eSP32Registry)
    {
        $eSP32Registry->update($request->all());
        return $eSP32Registry;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ESP32Registry $eSP32Registry)
    {
        $eSP32Registry->delete();
        return response()->noContent();
    }
}
