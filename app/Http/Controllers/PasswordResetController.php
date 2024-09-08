<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function reset(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',  // password_confirmation field must also be sent
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password has been updated successfully.'
        ], 200);
    }
}
