<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        return Counter::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'staff_id' => 'required|exists:staff,id',
        ]);

        return Counter::create($request->all());
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Counter $counter)
    {
        //
    }
}
