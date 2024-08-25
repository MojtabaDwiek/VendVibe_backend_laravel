<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        // Validate fields
        $attrs = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone_number' => 'required|string|max:8',
        ]);

        // Create user
        $user = User::create([
            'name' => $attrs['name'],
            'email' => $attrs['email'],
            'password' => bcrypt($attrs['password']),
            'phone_number' => $attrs['phone_number'],
        ]);

        // Return user & token in response
        return response([
            'user' => $user,
            'phone_number' => $user->phone_number,
            'token' => $user->createToken('secret')->plainTextToken
        ], 200);
    }

    // Login user
    public function login(Request $request)
    {
        // Validate fields
        $attrs = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Attempt login
        if (!Auth::attempt($attrs)) {
            return response([
                'message' => 'Invalid credentials.'
            ], 403);
        }

        $user = auth()->user();

        // Return user & token in response
        return response([
            'user' => $user,
            'phone_number' => $user->phone_number,
            'token' => $user->createToken('secret')->plainTextToken
        ], 200);
    }

    // Logout user
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'Logout success.'
        ], 200);
    }

    // Get user details
    public function user()
    {
        $user = auth()->user();

        return response([
            'user' => $user,
            'phone_number' => $user->phone_number,
        ], 200);
    }

    // Update user
    public function update(Request $request)
    {
        $attrs = $request->validate([
            'name' => 'required|string'
        ]);

        $image = $request->hasFile('image') 
            ? $this->saveImage($request->file('image'), 'profiles') 
            : null;

        auth()->user()->update([
            'name' => $attrs['name'],
            'image' => $image
        ]);

        return response([
            'message' => 'User updated.',
            'user' => auth()->user(),
            'phone_number' => auth()->user()->phone_number
        ], 200);
    }

   
}
