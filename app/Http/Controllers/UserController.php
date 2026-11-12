<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-02: Admin-only user management (route group is protected by AdminOnly). */
class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->withCount(['harvests', 'sales', 'expenses'])->orderBy('role')->orderBy('full_name')->get();
        return view('users.index', compact('users'));
    }

    public function create() { return view('users.form', ['user' => new User]); }
    public function edit(User $user) { return view('users.form', compact('user')); }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(null, true));
        User::create($data);
        return redirect()->route('users.index')->with('success', 'User account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate($this->rules($user, false));
        if ($user->isAdmin() && $data['role'] === 'Staff' && User::where('role', 'Admin')->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'The system must keep at least one Administrator.']);
        }
        if (blank($data['password'] ?? null)) unset($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'User account updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        return $this->tryDelete($user, 'users.index', 'User account');
    }

    private function rules(?User $user, bool $creating): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user?->user_id, 'user_id')],
            'email'     => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user?->user_id, 'user_id')],
            'role'      => ['required', Rule::in(['Admin', 'Staff'])],
            'password'  => [$creating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
