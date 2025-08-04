<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('settings.users.index', compact('users'));
    }

    public function create()
    {
        return view('settings.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('settings.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $isLastAdmin = ($user->role === 'admin' and User::where('role', 'admin')->count() <= 1);

        return view('settings.users.edit', compact('user', 'isLastAdmin'));
    }

    public function update(Request $request, User $user)
    {
        // Security check: prevent changing the role of the last administrator.
        if ($user->role === 'admin' && $request->role === 'user' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot change the role of the last administrator.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',email,' . $user->id],
            'role' => ['required', 'string', 'in:user,admin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Security check: if a user demotes themselves, redirect them to the dashboard.
        if ($user->id === Auth::id() && $user->role !== 'admin') {
            return redirect()->route('dashboard')->with('success', 'Your role has been updated. You no longer have admin access.');
        }

        return redirect()->route('settings.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Security check: prevent an admin from deleting their own account via this panel.
        if ($user->id === Auth::id()) {
            return redirect()->route('settings.users.index')->with('error', 'You cannot delete your own account from this panel. Please use the profile page.');
        }

        // Security check: prevent deleting the last administrator.
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('settings.users.index')->with('error', 'Cannot delete the last administrator.');
        }

        $user->delete();
        return redirect()->route('settings.users.index')->with('success', 'User deleted successfully.');
    }
}
