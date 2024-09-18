<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        $attrs = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone_number' => 'required|digits:8',
        ]);

        $user = User::create([
            'name' => $attrs['name'],
            'email' => $attrs['email'],
            'password' => bcrypt($attrs['password']),
            'phone_number' => $attrs['phone_number'],
        ]);

        return response([
            'user' => $user,
            'phone_number' => $user->phone_number,
            'token' => $user->createToken('secret')->plainTextToken
        ], 200);
    }

    // Login user
    public function login(Request $request)
    {
        $attrs = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (!Auth::attempt($attrs)) {
            return response([
                'message' => 'Invalid credentials.'
            ], 403);
        }

        $user = auth()->user();

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
            'name' => 'required|string',
            'phone_number' => 'nullable|digits:8',
        ]);

        $image = $request->hasFile('image') 
            ? $this->saveImage($request->file('image'), 'public/profiles') 
            : null;

        auth()->user()->update([
            'name' => $attrs['name'],
            'phone_number' => $attrs['phone_number'] ?? auth()->user()->phone_number,
            'image' => $image
        ]);

        return response([
            'message' => 'User updated.',
            'user' => auth()->user(),
            'phone_number' => auth()->user()->phone_number
        ], 200);
    }
}
