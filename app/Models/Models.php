<?php

// =============================================================
// app/Models/UserModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes= true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'uuid', 'first_name', 'last_name', 'email', 'phone',
        'password_hash', 'avatar', 'status', 'email_verified',
        'email_verify_token', 'reset_token', 'reset_token_expires',
        'last_login_at', 'last_login_ip', 'remember_token',
    ];

    protected $validationRules = [
        'email'         => 'required|valid_email',
        'password_hash' => 'required',
    ];

    protected $hiddenFields = ['password_hash', 'remember_token', 'reset_token'];

    // ------------------------------------------------------------------
    public function assignRole(int $userId, int $roleId): void
    {
        db_connect()->table('user_roles')->insertIgnore([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $db = db_connect();
        $db->table('user_roles')->where('user_id', $userId)->delete();
        foreach ($roleIds as $roleId) {
            $db->table('user_roles')->insertIgnore([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function getUserRoleIds(int $userId): array
    {
        return db_connect()
            ->table('user_roles')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray()
            ? array_column(db_connect()->table('user_roles')->where('user_id', $userId)->get()->getResultArray(), 'role_id')
            : [];
    }

    public function getUserRoleNames(int $userId): array
    {
        return array_column(
            db_connect()
                ->table('roles r')
                ->join('user_roles ur', 'ur.role_id = r.id')
                ->where('ur.user_id', $userId)
                ->get()->getResultArray(),
            'name'
        );
    }

    /** Full name shortcut */
    public function getFullName(array $user): string
    {
        return trim($user['first_name'] . ' ' . $user['last_name']);
    }
}


// =============================================================
// app/Models/RoleModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes= true;

    protected $allowedFields = ['name', 'label', 'description', 'is_active'];

    // ------------------------------------------------------------------
    /** All permission names assigned to a user via their roles. */
    public function getUserPermissions(int $userId): array
    {
        $db = db_connect();
        $rows = $db->table('permissions p')
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->join('user_roles ur',       'ur.role_id = rp.role_id')
            ->where('ur.user_id', $userId)
            ->distinct()
            ->get()->getResultArray();

        return array_column($rows, 'name');
    }

    /** Role names for a user. */
    public function getUserRoleNames(int $userId): array
    {
        $rows = db_connect()
            ->table('roles r')
            ->join('user_roles ur', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->get()->getResultArray();

        return array_column($rows, 'name');
    }

    /** All permissions for a role. */
    public function getRolePermissions(int $roleId): array
    {
        $rows = db_connect()
            ->table('permissions p')
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();

        return array_column($rows, 'id');
    }

    /** Sync permissions to a role. */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = db_connect();
        $db->table('role_permissions')->where('role_id', $roleId)->delete();
        foreach ($permissionIds as $permId) {
            $db->table('role_permissions')->insertIgnore([
                'role_id'       => $roleId,
                'permission_id' => (int) $permId,
            ]);
        }
    }
}


// =============================================================
// app/Models/MenuModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table         = 'menus';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'label', 'location', 'is_active'];

    // ------------------------------------------------------------------
    /**
     * Build nested sidebar menu for a given menu slug,
     * filtered by user permissions and roles.
     *
     * Returns a nested array:
     * [
     *   ['title'=>'Dashboard', 'route'=>'...', 'icon'=>'...', 'children'=>[]]
     * ]
     */
    public function getSidebarMenu(string $menuName, array $permissions, array $roles): array
    {
        $db = db_connect();

        $menu = $db->table('menus')
                   ->where('name', $menuName)
                   ->where('is_active', 1)
                   ->get()->getRowArray();

        if (! $menu) return [];

        $items = $db->table('menu_items')
                    ->where('menu_id', $menu['id'])
                    ->where('is_active', 1)
                    ->where('deleted_at', null)
                    ->orderBy('parent_id', 'ASC')
                    ->orderBy('sort_order', 'ASC')
                    ->get()->getResultArray();

        // Filter by permission
        $isSuperAdmin = in_array('super_admin', $roles);
        $items = array_filter($items, function ($item) use ($isSuperAdmin, $permissions) {
            if (! $item['permission']) return true;
            return $isSuperAdmin || in_array($item['permission'], $permissions);
        });

        return $this->nestItems(array_values($items));
    }

    /** Convert flat list with parent_id into nested tree. */
    private function nestItems(array $items, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $item['children'] = $this->nestItems($items, $item['id']);
                $branch[] = $item;
            }
        }
        return $branch;
    }
}


// =============================================================
// app/Models/SettingModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['group', 'key', 'value', 'type', 'label', 'description', 'is_public'];

    private static array $cache = [];

    // ------------------------------------------------------------------
    public function getValue(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $row = $this->where('key', $key)->first();
        if (! $row) return $default;

        $value = $this->castValue($row['value'], $row['type']);
        self::$cache[$key] = $value;

        return $value;
    }

    public function getGroup(string $group): array
    {
        $rows = $this->where('group', $group)->findAll();
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['key']] = $this->castValue($row['value'], $row['type']);
        }
        return $out;
    }

    public function saveValue(string $key, mixed $value): void
    {
        $this->where('key', $key)->set(['value' => $value])->update();
        self::$cache[$key] = $value;  // Invalidate inline
    }

    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->saveValue($key, $value);
        }
    }

    private function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'number'  => (float) $value,
            'boolean' => (bool) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}


// =============================================================
// app/Models/ActivityLogModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id', 'session_id', 'action', 'module',
        'description', 'ip_address', 'user_agent',
        'url', 'method', 'extra',
    ];
}


// =============================================================
// app/Models/NotificationModel.php
// =============================================================

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id','type','title','message','data','url','is_read','read_at'];

    public function createFor(int $userId, string $title, string $message, string $type = 'info', ?string $url = null): void
    {
        $this->insert(compact('user_id', 'title', 'message', 'type', 'url'));
    }

    public function markRead(int $id): void
    {
        $this->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllReadFor(int $userId): void
    {
        $this->where('user_id', $userId)
             ->where('is_read', 0)
             ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
             ->update();
    }
}
