<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CounterController extends Controller
{
    public function index()
    {
        return Counter::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        return Counter::create($request->all());
    }

    public function delete($id)
    {
        $counter = Counter::find($id);

        if (!$counter) {
            return response()->json(['message' => 'Counter not found'], Response::HTTP_NOT_FOUND);
        }

        $counter->delete();

        return response()->json(['message' => 'Counter deleted successfully'], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(Counter $counter)
    {
        return $counter;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Counter $counter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCounterRequest $request, Counter $counter)
    {
        //
    }

    
}
