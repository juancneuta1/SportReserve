<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\AdminActionNotifier;

class UsuarioAdminController extends Controller
{
    public function index(): View
    {
        $usuarios = User::withCount(['reservas', 'calificaciones'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $temporaryPassword = Str::random(10);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        AdminNotification::create([
            'type' => 'usuario',
            'title' => 'Nuevo usuario registrado',
            'body' => "{$user->name} ({$user->email}) se agregó al sistema.",
        ]);

        AdminActionNotifier::send(
            $request->user(),
            'Creación de usuario',
            "Se registró un nuevo usuario en el sistema administrativo.",
            [
                'Usuario' => "{$user->name} ({$user->email})",
                'Rol' => ucfirst($user->role),
                'Generado por' => $request->user()?->email ?? 'Desconocido',
            ]
        );

        return redirect()
            ->route('admin.usuarios')
            ->with('status', 'Usuario creado correctamente.')
            ->with('generated_user_credentials', [
                'email' => $user->email,
                'password' => $temporaryPassword,
            ]);
    }

    public function show(User $usuario): View
    {
        $usuario->loadCount(['reservas', 'calificaciones']);

        $ultimaReserva = $usuario->reservas()
            ->with('cancha')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->first();

        return view('admin.usuarios.show', compact('usuario', 'ultimaReserva'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'role' => ['required', 'in:admin,user'],
            'admin_password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['admin_password'], Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'La contraseña del administrador no coincide.'])->withInput();
        }

        $usuario->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        AdminActionNotifier::send(
            $request->user(),
            'Actualización de usuario',
            "Se modificó la información del usuario {$usuario->name}.",
            [
                'Correo' => $usuario->email,
                'Rol actual' => ucfirst($usuario->role),
                'Administrador' => $request->user()?->email ?? 'Desconocido',
            ]
        );

        return redirect()
            ->route('admin.usuarios.show', $usuario)
            ->with('status', 'Datos del usuario actualizados correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'admin_password' => ['required', 'string'],
        ]);

        $admin = $request->user();

        if (! $admin || ! Hash::check($validated['admin_password'], $admin->password)) {
            return back()
                ->withErrors(['delete_' . $usuario->id => 'La contraseña del administrador no coincide.'])
                ->with('show_delete_modal', $usuario->id);
        }

        if ($usuario->id === $admin->id) {
            return back()
                ->withErrors(['delete_' . $usuario->id => 'No puedes eliminar tu propia cuenta.'])
                ->with('show_delete_modal', $usuario->id);
        }

        $usuarioInfo = "{$usuario->name} ({$usuario->email})";
        $rol = $usuario->role;

        $usuario->delete();

        AdminActionNotifier::send(
            $admin,
            'Eliminación de usuario',
            "Se eliminó la cuenta {$usuarioInfo}.",
            [
                'Rol anterior' => ucfirst($rol),
                'Administrador' => $admin->email,
            ]
        );

        return redirect()
            ->route('admin.usuarios')
            ->with('status', 'Usuario eliminado correctamente.');
    }
}
