<?php

namespace App\Http\Controllers;

use App\Models\DepartmentCounter;
use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DepartmentCounterController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'staff_id' => 'required|int',
    //         'department_id' => 'required|int',
    //         'counter_id' => 'required|int',
    //     ]);

    //     return DepartmentCounter::create($request->all());

    //     return response()->json($departmentCounter, 201);
    // }

    public function update(Request $request)
    {
        $request->validate([
            'staff_id' => 'required',
            'department_id' => 'required',
            'counter_id' => 'required',
        ]);

        //\Log::info('Node Services:',  $request);

        // Find the department counter record based on department_id and counter_id
        $departmentCounter = DepartmentCounter::where('department_id', $request->input('department_id'))
        ->where('counter_id', $request->input('counter_id'))
        ->firstOrFail();

        // Update the staff_id based on the request data
        $departmentCounter->update([
            'staff_id' => $request->input('staff_id'),
        ]);

        return response()->json($departmentCounter, 200);
    }

    public function getDepartmentCounter($staff_id)
    {
        $departmentCounter = DepartmentCounter::where('staff_id', $staff_id)->with('department', 'counter')->first();

        if ($departmentCounter) {
            return response()->json([
                'department_name' => $departmentCounter->department->name,
                'department_id' => $departmentCounter->department->id,
                'counter_id' => $departmentCounter->counter->id,
            ], 200);
        } else {
            return response()->json(['error' => 'Not found'], 404);
        }
    }

    public function assignNewCounterId(Request $request)
    {
        $request->validate([
            'staff_id' => 'nullable|int',
            'department_id' => 'required|int',
        ]);

        $staff_id = $request->input('staff_id');
        $department_id = $request->input('department_id');

        // Get the highest counter_id for the given department_id
        $highestCounterId = DepartmentCounter::where('department_id', $department_id)->max('counter_id');

        // Increment the counter_id by 1
        $newCounterId = $highestCounterId + 1;

        // Check if the new counter_id exists in the Counter table
        $counterExists = Counter::where('id', $newCounterId)->exists();

        if ($counterExists) {
            // Create a new DepartmentCounter record with the new counter_id
            $departmentCounter = DepartmentCounter::create([
                'staff_id' => $request->input('staff_id'), 
                'department_id' => $department_id,
                'counter_id' => $newCounterId,
            ]);
        } else {
            // Create a new Counter record
            $counterCount = Counter::count();
            $counterCount++;

            $newCounter = Counter::create([
                'id' => $newCounterId,
                'name' => 'Counter ' . $counterCount,
                // Add other necessary fields for the Counter model here
            ]);

            // Create a new DepartmentCounter record with the new counter_id
            $departmentCounter = DepartmentCounter::create([
                'staff_id' => $request->input('staff_id'), // This could be null
                'department_id' => $department_id,
                'counter_id' => $newCounterId,
            ]);
        }

        return response()->json($departmentCounter, 201);
    }

    public function getCountersByDepartmentId($department_id)
    {
        $counters = DepartmentCounter::where('department_id', $department_id)
            ->with('counter')
            ->get()
            ->pluck('counter');

        if ($counters->isEmpty()) {
            return response()->json(['error' => 'No counters found for this department'], 404);
        }

        return response()->json($counters, 200);
    }

}
