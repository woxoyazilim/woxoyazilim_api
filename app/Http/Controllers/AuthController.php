<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['error' => 'Email veya şifre hatalı'], 401);

        $storedHash = $user->password;
        if (str_starts_with($storedHash, '$2b$')) {
            $storedHash = '$2y$' . substr($storedHash, 4);
        }

        if (!password_verify($request->password, $storedHash)) {
            return response()->json(['error' => 'Email veya şifre hatalı'], 401);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Eski tokenları sil
        $user->tokens()->delete();
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => ['id' => $user->id, 'email' => $user->email, 'name' => $user->name, 'role' => $user->role],
            'token' => $token,
        ])->cookie('woxo-admin-token', $token, 60 * 24 * 7, '/', null, false, false);
    }

    public function logout(Request $request)
    {
        // Token'dan kullanıcı bul
        $user = $this->getUserFromRequest($request);
        if ($user) $user->tokens()->delete();
        
        return response()->json(['success' => true])->cookie('woxo-admin-token', '', -1);
    }

    public function session(Request $request)
    {
        $user = $this->getUserFromRequest($request);
        if (!$user) return response()->json(['user' => null]);
        return response()->json([
            'user' => ['id' => $user->id, 'email' => $user->email, 'name' => $user->name, 'role' => $user->role]
        ]);
    }

    public function setupAdmin(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|min:6']);

        $existing = User::where('email', $request->email)->first();
        if ($existing) {
            return response()->json(['success' => true, 'message' => 'Admin user already exists', 'email' => $request->email, 'alreadyExists' => true]);
        }

        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name ?: 'Admin User',
            'role' => 'admin',
        ]);

        return response()->json(['success' => true, 'message' => 'Admin user created', 'email' => $user->email, 'id' => $user->id]);
    }

    /**
     * Token'ı header, cookie veya query'den bul ve kullanıcıyı döndür
     */
    private function getUserFromRequest(Request $request): ?User
    {
        // 1. Authorization header
        $token = null;
        $auth = $request->header('Authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            $token = substr($auth, 7);
        }

        // 2. Cookie
        if (!$token) {
            $token = $request->cookie('woxo-admin-token');
        }

        // 3. localStorage token (credentials: include ile gelmez ama query'den gelebilir)
        if (!$token) return null;

        // Sanctum token'ı parse et
        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) return null;

        return $accessToken->tokenable;
    }
}
