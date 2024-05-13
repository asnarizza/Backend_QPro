<?php

namespace App\Http\Controllers;

use App\Models\DepartmentCounter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentCounterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|int',
            'department_id' => 'required|int',
            'counter_id' => 'required|int',
        ]);

        return DepartmentCounter::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(DepartmentCounter $departmentCounter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DepartmentCounter $departmentCounter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentCounterRequest $request, DepartmentCounter $departmentCounter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepartmentCounter $departmentCounter)
    {
        //
    }
}
