<?php

// app/Controllers/Admin/RoleController.php

namespace App\Controllers\Admin;

use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleController extends AdminBaseController
{
    private RoleModel       $roleModel;
    private PermissionModel $permModel;

    public function __construct()
    {
        $this->roleModel = model(RoleModel::class);
        $this->permModel = model(PermissionModel::class);
    }

    // GET /admin/roles
    public function index(): string
    {
        $this->authorize('roles.read');
        $roles = $this->roleModel->where('deleted_at', null)->orderBy('id')->findAll();
        return $this->render('admin/roles/index', ['page_title' => 'Roles', 'roles' => $roles]);
    }

    // GET /admin/roles/create
    public function create(): string
    {
        $this->authorize('roles.create');
        $permsByModule = $this->getPermsByModule();
        return $this->render('admin/roles/form', [
            'page_title'    => 'Create Role',
            'role'          => null,
            'rolePerms'     => [],
            'permsByModule' => $permsByModule,
        ]);
    }

    // POST /admin/roles/store
    public function store()
    {
        $this->authorize('roles.create');

        $rules = [
            'name'  => 'required|max_length[60]|is_unique[roles.name]|alpha_dash',
            'label' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = $this->roleModel->insert([
            'name'        => strtolower($this->request->getPost('name')),
            'label'       => $this->request->getPost('label'),
            'description' => $this->request->getPost('description'),
            'is_active'   => (int) ($this->request->getPost('is_active') ?? 1),
        ]);

        $permIds = $this->request->getPost('permissions') ?? [];
        if ($permIds) {
            $this->roleModel->syncPermissions($roleId, array_map('intval', $permIds));
        }

        $this->logActivity('create_role', 'roles', "Created role ID {$roleId}");
        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    // GET /admin/roles/:id/edit
    public function edit(int $id): string
    {
        $this->authorize('roles.update');
        $role  = $this->roleModel->find($id);
        if (! $role) return redirect()->route('admin.roles.index')->with('error', 'Role not found.');

        $permsByModule = $this->getPermsByModule();
        $rolePerms     = $this->roleModel->getRolePermissions($id);

        return $this->render('admin/roles/form', [
            'page_title'    => 'Edit Role',
            'role'          => $role,
            'rolePerms'     => $rolePerms,
            'permsByModule' => $permsByModule,
        ]);
    }

    // POST /admin/roles/:id/update
    public function update(int $id)
    {
        $this->authorize('roles.update');

        $rules = [
            'name'  => "required|max_length[60]|is_unique[roles.name,id,{$id}]|alpha_dash",
            'label' => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->roleModel->update($id, [
            'name'        => strtolower($this->request->getPost('name')),
            'label'       => $this->request->getPost('label'),
            'description' => $this->request->getPost('description'),
            'is_active'   => (int) ($this->request->getPost('is_active') ?? 1),
        ]);

        $permIds = $this->request->getPost('permissions') ?? [];
        $this->roleModel->syncPermissions($id, array_map('intval', $permIds));

        $this->logActivity('update_role', 'roles', "Updated role ID {$id}");
        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    // POST /admin/roles/:id/delete
    public function delete(int $id)
    {
        $this->authorize('roles.delete');
        $this->roleModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete_role', 'roles', "Deleted role ID {$id}");
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    private function getPermsByModule(): array
    {
        $perms  = $this->permModel->orderBy('module')->orderBy('action')->findAll();
        $grouped = [];
        foreach ($perms as $p) {
            $grouped[$p['module']][] = $p;
        }
        return $grouped;
    }
}
