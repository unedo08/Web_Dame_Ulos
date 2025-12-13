<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'role_id'   => 'required|integer',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'created_id' => Auth::id() ?? 1
        ]);

        try {
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'User registered successfully',
                'token'   => $token,
                'user'    => $user->load('role'),
            ], 201);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'message'    => 'Login successful',
            'token'      => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user'       => auth()->user()->load('role'),
        ]);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    public function getUser()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json($user->load('role'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to fetch user'], 500);
        }
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'name'  => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();

        $user->update([
            'name'       => $request->name ?? $user->name,
            'email'      => $request->email ?? $user->email,
            'updated_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'User updated',
            'user'    => $user
        ]);
    }

    public function me(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Belum login'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'user_id' => Auth::id(),
            'name' => Auth::user()->name,
            'role' => Auth::user()->role,
            'user' => Auth::user()
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah'
            ], 400);
        }

        $user->update([
            'password'   => Hash::make($request->new_password),
            'updated_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Password berhasil diupdate'
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Tidak bisa hapus akun sendiri'
            ], 403);
        }

        $user->update([
            'deleted_id' => Auth::id()
        ]);

        $user->delete(); // soft delete

        return response()->json([
            'message' => 'User berhasil dihapus (soft delete)'
        ]);
    }

    public function getActiveUsers()
    {
        try {
            $users = User::with('role')
                ->whereNull('deleted_at')   // ⬅️ hanya user aktif
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'List active users',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
