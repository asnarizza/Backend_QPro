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
            'phone' => 'required|numeric', 
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'nullable|int',
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
        $success['user_id'] = $user->id;
        $success['name'] =$user->name;
        $success['phone'] = $user->phone;

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
            $success['user_id'] = $auth->id;
            $success['name'] = $auth->name;
            $success['phone'] = $auth->phone;
            $success['email'] = $auth->email;
            $success['role_id'] = $auth->role_id;

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

    public function getStaff()
    {
        $staff = User::where('role_id', 2)->get();
        return response()->json($staff);
    }
}
