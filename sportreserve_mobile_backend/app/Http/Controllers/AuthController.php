<?php
namespace App\Http\Controllers;
use App\Events\UserLoginSucceeded;
use App\Events\UserRegistered;
use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // ✅ Registro de usuario
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed', // ✅ necesita también 'password_confirmation'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'user', // ✅ asigna automáticamente el rol de usuario
                'must_change_password' => false,
            ]);

            AdminNotification::create([
                'type' => 'usuario',
                'title' => 'Nuevo usuario registrado',
                'body' => "{$user->name} se registró desde la app móvil.",
            ]);

            UserRegistered::dispatch($user, $request);

            return response()->json([
                'message' => 'Usuario registrado correctamente.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user', // ✅ añade el rol en la respuesta
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno al registrar el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ✅ Inicio de sesión
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);



        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }
        // Actualizar último inicio de sesión
        $user->last_login_at = now();
        $user->save();
        // Crear token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;
        UserLoginSucceeded::dispatch($user, $request);
        $rawPayload = [
            'message' => 'Inicio de sesión exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_url' => $user->photo_url,
                'role' => $user->role,
                'must_change_password' => (bool) $user->must_change_password,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ];

        \Log::info('DEBUG_LOGIN_RAW_PAYLOAD', [
            'payload_json' => json_encode($rawPayload),
        ]);

        return response()->json($rawPayload, 200);
    }

    // ✅ Perfil del usuario autenticado
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado.'
                ], 401);
            }

            return response()->json([
                'message' => 'Perfil obtenido correctamente.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo_url' => $user->photo_url,
                    'role' => $user->role, // ✅ nuevo campo visible al frontend
                    'must_change_password' => (bool) $user->must_change_password,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el perfil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Logout
    public function logout(Request $request)
    {
        try {
            // Elimina solo el token actual del usuario
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Sesión cerrada correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Cambiar contraseña
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual no es correcta.',
                ], 422);
            }

            $user->forceFill([
                'password' => Hash::make($validated['new_password']),
                'must_change_password' => false,
            ])->save();

            return response()->json([
                'message' => 'Contraseña actualizada correctamente.',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno al actualizar la contraseña.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //Subir o actualizar foto de perfil
    public function updatePhoto(Request $request)
    {
        try {
            $user = $request->user();
            // ✅ Validar archivo
            $validated = $request->validate([
                'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);
            // ✅ Guardar la nueva foto
            $path = $request->file('photo')->store('profile_photos', 'public');
            // ✅ Eliminar foto anterior (si existía)
            if ($user->photo_url) {
                // Extraer solo el nombre del archivo anterior si era una URL completa
                $oldPath = str_replace(asset('storage/') . '/', '', $user->photo_url);
                if (\Storage::disk('public')->exists($oldPath)) {
                    \Storage::disk('public')->delete($oldPath);
                }
            }
            // ✅ Actualizar registro del usuario con URL pública completa
            $user->photo_url = asset('storage/' . $path);
            $user->save();
            return response()->json([
                'message' => 'Foto de perfil actualizada correctamente.',
                'photo_url' => $user->photo_url,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno al subir la foto.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

