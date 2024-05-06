<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class StaffController extends Controller
{
    public function index()
    {
        return Staff::all();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate the incoming registration data
        $validator = Validator::make($request->all(), 
        [
            'name' => 'required',
            'email' => 'required|email|unique:staff,email',
            'role' => 'required',
            
        ]);

        // if validation fails, return validation errors
        if ($validator->fails())
        {
            return response()->json(
            [
                'success' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors()
            ]);
        }

        // if validation passes, create a new user
        $input = $request->all();
        $staff = Staff::create($input);

        // return success response with user details
        return response()->json(
        [
            'success' => true,
            'message' => 'Registration Successful',
            'data'=> $staff
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        return $staff;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        //
    }
}
