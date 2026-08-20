<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => Role::orderBy('display_name')->get()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        $user->roles()->sync($request->input('roles', []));

        $this->audit->log(AuditAction::Created, $user, ['name' => $user->name]);

        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('display_name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->string('password'))]);
        }

        $user->roles()->sync($request->input('roles', []));

        $this->audit->log(AuditAction::Updated, $user, ['name' => $user->name]);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث المستخدم.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $this->audit->log(AuditAction::Deleted, $user, ['name' => $user->name]);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم.');
    }
}
