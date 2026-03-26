<?php

// app/Controllers/Admin/UserController.php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends AdminBaseController
{
    private UserModel $userModel;
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->roleModel = model(RoleModel::class);
    }

    // ── GET /admin/users ─────────────────────────────────────────────
    public function index(): string
    {
        $this->authorize('users.read');

        $search = $this->request->getGet('search');

        if ($search) {
            $this->userModel->groupStart()
                ->like('first_name', $search)
                ->orLike('last_name',  $search)
                ->orLike('email',      $search)
                ->groupEnd();
        }

        $this->userModel->where('deleted_at', null)
                        ->orderBy('created_at', 'DESC');

        ['data' => $users, 'pager' => $pager] = $this->paginate($this->userModel, 15);

        return $this->render('admin/users/index', compact('users', 'pager', 'search'));
    }

    // ── GET /admin/users/create ──────────────────────────────────────
    public function create(): string
    {
        $this->authorize('users.create');
        $roles = $this->roleModel->where('is_active', 1)->findAll();
        return $this->render('admin/users/form', ['roles' => $roles, 'user' => null]);
    }

    // ── POST /admin/users/store ──────────────────────────────────────
    public function store()
    {
        $this->authorize('users.create');

        $rules = [
            'first_name' => 'required|max_length[80]',
            'last_name'  => 'required|max_length[80]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[8]',
            'role_id'    => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $userId = $this->userModel->insert([
            'uuid'          => service('uuid')->uuid4()->toString(),
            'first_name'    => $this->request->getPost('first_name'),
            'last_name'     => $this->request->getPost('last_name'),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'        => $this->request->getPost('status') ?? 'active',
            'email_verified'=> 1,
        ]);

        // Assign role
        $this->userModel->assignRole($userId, (int) $this->request->getPost('role_id'));

        $this->logActivity('create_user', 'users', "Created user ID {$userId}");

        return redirect()->route('admin.users.index')
                         ->with('success', 'User created successfully.');
    }

    // ── GET /admin/users/:id/edit ────────────────────────────────────
    public function edit(int $id): string
    {
        $this->authorize('users.update');

        $user  = $this->userModel->where('deleted_at', null)->find($id);
        $roles = $this->roleModel->where('is_active', 1)->findAll();
        $userRoles = $this->userModel->getUserRoleIds($id);

        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'User not found.');
        }

        return $this->render('admin/users/form', compact('user', 'roles', 'userRoles'));
    }

    // ── POST /admin/users/:id/update ─────────────────────────────────
    public function update(int $id)
    {
        $this->authorize('users.update');

        $rules = [
            'first_name' => 'required|max_length[80]',
            'last_name'  => 'required|max_length[80]',
            'email'      => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role_id'    => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone'),
            'status'     => $this->request->getPost('status'),
        ];

        $newPassword = $this->request->getPost('password');
        if ($newPassword) {
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        $this->userModel->syncRoles($id, [(int) $this->request->getPost('role_id')]);

        $this->logActivity('update_user', 'users', "Updated user ID {$id}");

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    // ── POST /admin/users/:id/delete ─────────────────────────────────
    public function delete(int $id)
    {
        $this->authorize('users.delete');

        // Prevent self-deletion
        if ($id === $this->currentUserId()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Soft delete
        $this->userModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete_user', 'users', "Soft-deleted user ID {$id}");

        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted.');
    }

    // ── GET /admin/users/:id/view ────────────────────────────────────
    public function show(int $id): string
    {
        $this->authorize('users.read');

        $user  = $this->userModel->where('deleted_at', null)->find($id);
        $roles = $this->userModel->getUserRoleNames($id);

        if (! $user) {
            return redirect()->route('admin.users.index')->with('error', 'User not found.');
        }

        return $this->render('admin/users/show', compact('user', 'roles'));
    }
}
