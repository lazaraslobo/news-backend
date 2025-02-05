<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\User;
use App\Models\UserPreference;
use App\Responses\UserResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('API Token')->plainTextToken;

            return JsonResponseHelper::success([
                'token' => $token,
                'user' => $user,
            ]);
        }
        return JsonResponseHelper::success(['error' => 'Unauthorized'], 401);
    }

    public function register(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // Make sure to include the `password_confirmation` field in your request
        ]);

        // Create the new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate a new Sanctum token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the created user and token
        return JsonResponseHelper::success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the token of the currently authenticated user
            $user = Auth::user();

            if (!$user) {
                // Throw an UnauthorizedHttpException if the user is not authenticated
                throw new UnauthorizedHttpException('Bearer', 'Unauthenticated');
            }

            // Optionally, you can revoke all tokens for this user
            $user->tokens()->delete();

            // If you're using Laravel session to store user data
            $request->session()->invalidate();

            // Regenerate the session token to prevent session fixation attacks
            $request->session()->regenerateToken();

            return JsonResponseHelper::success(['message' => 'Logged out successfully']);
        }catch (\Exception $exception){
            return JsonResponseHelper::success([], "CSRF token set failed", 401);
        }
    }

    public function updateUserPreferences(Request $request){
        $user = Auth::user();

        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        (new UserPreference())::updateOrInsertPreference(
            $user->id,
            $request->key,
            json_encode($request->value)
        );

        return JsonResponseHelper::success([
            "user" => new UserResponse(Auth::user()),
            'message' => 'Preferences updated successfully'
        ]);
    }
}
