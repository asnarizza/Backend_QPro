<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        // validate the incoming registration data
        $validator = Validator::make($request->all(), 
        [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
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
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        // generate authentication token for user
        $success['token'] =$user->createToken('auth_token')->plainTextToken;
        $success['name'] =$user->name;

        // return success response with user details
        return response()->json(
        [
            'success' => true,
            'message' => 'Registration Successful',
            'data'=> $success
        ]);
    }

    public function login(Request $request)
    {
        // attempt to authenticate the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) 
        {
            // if auth succeeds, get the authenticated user
            $auth = Auth::user();

            // generate authentication token for user
            $success['token'] = $auth-> createToken('auth_token')->plainTextToken;
            $success['name'] = $auth->name;
            $success['email'] = $auth->email;

            // return success response with token and user details
            return response()->json([
                'success' => true,
                'message' => 'Login Successful',
                'data'=> $success
            ]);

        } else {
            // if auth fails, retrun error response
            return response()->json([
                'success' => false,
                'message' => 'Incorrect email or password',
                'data'=> null
            ]);

        }
    }

    // public function logout(Request $request)
    // {
    //     // Check if the user is authenticated
    //     if (Auth::check()) {
    //         // Revoke the user's current token
    //         $request->user()->currentAccessToken()->delete();

    //         // Return a success response
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Logout Successful',
    //         ]);
    //     } else {
    //         // If the user is not authenticated, return an error response
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User is not authenticated',
    //         ]);
    //     }
    // }

    public function logout(Request $request)
{
    // Check if the user is authenticated
    if (Auth::check()) {
        // Revoke the user's current token
        $request->user()->currentAccessToken()->delete();

        // Log successful logout
        Log::info('User logged out successfully: ' . $request->user()->id);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Logout Successful',
        ]);
    } else {
        // If the user is not authenticated, log an error
        Log::error('User is not authenticated for logout');

        // Return an error response
        return response()->json([
            'success' => false,
            'message' => 'User is not authenticated',
        ]);
    }
}
}