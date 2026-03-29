<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * POST /api/auth/register
     * Body: { name, email, password, password_confirmation }
     *
     * @return JsonResponse  201 { user, token }  |  422 { message, errors }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // hashed automatically via cast
        ]);

        $token = $user->createToken('foodify-mobile', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => $this->userResource($user),
            'token'   => $token,
        ], 201);
    }

    /**
     * Log in an existing user.
     *
     * POST /api/auth/login
     * Body: { email, password }
     *
     * @return JsonResponse  200 { user, token }  |  422 { message, errors }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all previous tokens issued to this user (single-session policy)
        $user->tokens()->delete();

        $token = $user->createToken('foodify-mobile', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => $this->userResource($user),
            'token'   => $token,
        ]);
    }

    /**
     * Return the currently authenticated user.
     *
     * GET /api/auth/me
     * Header: Authorization: Bearer {token}
     *
     * @return JsonResponse  200 { user }  |  401
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userResource($request->user()),
        ]);
    }

    /**
     * Log out the current token.
     *
     * POST /api/auth/logout
     * Header: Authorization: Bearer {token}
     *
     * @return JsonResponse  200 { message }
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke only the token used in this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Log out from ALL devices (revoke every token).
     *
     * POST /api/auth/logout-all
     * Header: Authorization: Bearer {token}
     *
     * @return JsonResponse  200 { message }
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────

    /**
     * Shape the user object returned to the client.
     *
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'phone'      => $user->phone,
            'avatar'     => $user->avatar,
            'is_active'  => $user->is_active,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
