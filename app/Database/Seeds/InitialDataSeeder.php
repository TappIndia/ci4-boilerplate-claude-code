<?php

// app/Database/Migrations/2024-01-01-000001_CreateUsersTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uuid' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'last_name'  => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 180],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'avatar'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'     => [
                'type'       => 'ENUM',
                'constraint' => ['active','inactive','banned','pending'],
                'default'    => 'pending',
            ],
            'email_verified'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'email_verify_token'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reset_token'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reset_token_expires' => ['type' => 'DATETIME', 'null' => true],
            'last_login_at'       => ['type' => 'DATETIME', 'null' => true],
            'last_login_ip'       => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'remember_token'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => false],
            'updated_at'          => ['type' => 'DATETIME', 'null' => false],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('uuid');
        $this->forge->addKey('status');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}


// ──────────────────────────────────────────────────────────────────────────────
// app/Database/Seeds/InitialDataSeeder.php
// ──────────────────────────────────────────────────────────────────────────────

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user
        $userId = $this->createSuperAdmin();
        $this->createDefaultRoles($userId);
        $this->createDefaultSettings();
        $this->createDefaultMenus();

        echo "✅  Initial seed data created successfully.\n";
        echo "    Super Admin: admin@example.com / Admin@1234\n";
    }

    private function createSuperAdmin(): int
    {
        $existing = $this->db->table('users')->where('email', 'admin@example.com')->get()->getRow();
        if ($existing) return $existing->id;

        $this->db->table('users')->insert([
            'uuid'           => $this->generateUuid(),
            'first_name'     => 'Super',
            'last_name'      => 'Admin',
            'email'          => 'admin@example.com',
            'password_hash'  => password_hash('Admin@1234', PASSWORD_DEFAULT),
            'status'         => 'active',
            'email_verified' => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insertID();
    }

    private function createDefaultRoles(int $superAdminId): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Administrator', 'description' => 'Full access'],
            ['name' => 'admin',       'label' => 'Administrator',       'description' => 'Manage all modules'],
            ['name' => 'editor',      'label' => 'Editor',              'description' => 'Create/edit content'],
            ['name' => 'viewer',      'label' => 'Viewer',              'description' => 'Read-only'],
        ];

        foreach ($roles as $role) {
            $existing = $this->db->table('roles')->where('name', $role['name'])->get()->getRow();
            if ($existing) continue;

            $this->db->table('roles')->insert(array_merge($role, [
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }

        // Assign super_admin role to super admin user
        $superRole = $this->db->table('roles')->where('name', 'super_admin')->get()->getRow();
        if ($superRole) {
            $this->db->table('user_roles')->insertIgnore([
                'user_id'    => $superAdminId,
                'role_id'    => $superRole->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function createDefaultSettings(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'site_name',    'value' => 'CI4 Boilerplate', 'type' => 'text',   'label' => 'Site Name',    'is_public' => 1],
            ['group' => 'general', 'key' => 'site_tagline', 'value' => 'Built with CI4',  'type' => 'text',   'label' => 'Tagline',      'is_public' => 1],
            ['group' => 'general', 'key' => 'site_email',   'value' => 'admin@example.com','type' => 'text',  'label' => 'Admin Email',  'is_public' => 0],
            ['group' => 'general', 'key' => 'per_page',     'value' => '15',              'type' => 'number', 'label' => 'Rows Per Page','is_public' => 0],
            ['group' => 'security','key' => 'login_attempts','value' => '5',              'type' => 'number', 'label' => 'Max Login Attempts','is_public' => 0],
        ];

        foreach ($settings as $s) {
            $existing = $this->db->table('settings')
                ->where('group', $s['group'])
                ->where('key', $s['key'])
                ->get()->getRow();
            if ($existing) continue;

            $this->db->table('settings')->insert(array_merge($s, [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }
    }

    private function createDefaultMenus(): void
    {
        $existing = $this->db->table('menus')->where('name', 'admin_sidebar')->get()->getRow();
        if ($existing) return;

        $this->db->table('menus')->insert([
            'name'       => 'admin_sidebar',
            'label'      => 'Admin Sidebar',
            'location'   => 'admin',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $menuId = $this->db->insertID();

        $items = [
            ['title' => 'Dashboard',    'route' => 'admin.dashboard',   'icon' => 'bi-speedometer2',  'sort_order' => 1],
            ['title' => 'Users',        'route' => 'admin.users.index', 'icon' => 'bi-people',        'sort_order' => 2, 'permission' => 'users.read'],
            ['title' => 'Roles',        'route' => 'admin.roles.index', 'icon' => 'bi-shield-lock',   'sort_order' => 3, 'permission' => 'roles.read'],
            ['title' => 'Files',        'route' => 'admin.files.index', 'icon' => 'bi-folder',        'sort_order' => 4, 'permission' => 'files.read'],
            ['title' => 'Activity Log', 'route' => 'admin.logs.index',  'icon' => 'bi-journal-text',  'sort_order' => 5],
            ['title' => 'Settings',     'route' => 'admin.settings',    'icon' => 'bi-gear',          'sort_order' => 6, 'permission' => 'settings.read'],
        ];

        foreach ($items as $item) {
            $this->db->table('menu_items')->insert(array_merge([
                'menu_id'    => $menuId,
                'parent_id'  => null,
                'url'        => null,
                'badge'      => null,
                'target'     => '_self',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], $item));
        }
    }

    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
