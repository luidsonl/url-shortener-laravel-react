<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $tokenData = $this->authService->createTokenForUser($user);

        Mail::to($user->email)->send(new WelcomeEmail());

        return response()->json([
            'message' => 'User successfully created',
            ...$tokenData
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $tokenData = $this->authService->login($request->only(['email', 'password']));
            return response()->json($tokenData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $result = $this->authService->logout();
        return response()->json($result);
    }

    public function user(Request $request)
    {
        $user = $this->authService->user();
        return response()->json(['user' => $user]);
    }

    public function validateToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        
        $user = $this->authService->validateToken($request->token);
        
        if (!$user) {
            return response()->json(['valid' => false], 401);
        }

        return response()->json(['valid' => true, 'user' => $user]);
    }
}
