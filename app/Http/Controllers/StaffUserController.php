<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\StoreStaffUserRequest;
use App\Http\Requests\UpdateStaffUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffUserController extends Controller
{
    /**
     * List staff accounts with search and role/status filters.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', '%'.$search.'%')
                        ->orWhere('email', 'ilike', '%'.$search.'%');
                });
            })
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('staff-users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'roles' => UserRole::assignableCases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('staff-users.create', [
            'roles' => UserRole::assignableCases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function store(StoreStaffUserRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'status' => $request->input('status'),
            'password' => Hash::make($request->input('password')),
            'email_verified_at' => now(),
        ]);

        AuditLogger::log(
            AuditActionType::UserCreated,
            "Created staff account for {$user->name} ({$user->role->label()}).",
            $user,
            ['email' => $user->email, 'role' => $user->role->value, 'status' => $user->status->value],
        );

        return redirect()
            ->route('staff-users.edit', $user)
            ->with('success', 'Staff user created successfully.');
    }

    public function edit(User $user): View
    {
        return view('staff-users.edit', [
            'staffUser' => $user,
            'roles' => UserRole::assignableCases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UpdateStaffUserRequest $request, User $user): RedirectResponse
    {
        $changes = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'status' => $request->input('status'),
        ];

        if ($request->filled('password')) {
            $changes['password'] = Hash::make($request->input('password'));
        }

        $user->update($changes);

        $description = $request->filled('password')
            ? "Updated staff account for {$user->name} and reset password."
            : "Updated staff account for {$user->name}.";

        AuditLogger::log(
            AuditActionType::UserUpdated,
            $description,
            $user,
            [
                'email' => $user->email,
                'role' => $user->role->value,
                'status' => $user->status->value,
                'password_reset' => $request->filled('password'),
            ],
        );

        return redirect()
            ->route('staff-users.edit', $user)
            ->with('success', 'Staff user updated successfully.');
    }
}
