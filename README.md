# CI4 Universal Boilerplate

> A production-ready CodeIgniter 4 application scaffold with Role-Based Access Control, DB-driven menus, generic CRUD, REST API, and a polished Bootstrap 5 admin panel.

---

## Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Backend   | PHP 8.1+ · CodeIgniter 4.x          |
| Database  | MySQL 8.x                           |
| Frontend  | Bootstrap 5.3 · Bootstrap Icons · DM Sans |
| API       | RESTful JSON · Bearer token auth    |

---

## Project Structure

```
ci4-boilerplate/
│
├── app/
│   ├── Config/
│   │   ├── Filters.php          ← Filter aliases (auth, api_auth, log_activity)
│   │   └── Routes.php           ← All routes (public, admin, api)
│   │
│   ├── Controllers/
│   │   ├── BaseController.php           ← Shared: logActivity(), can(), setting()
│   │   ├── AuthController.php           ← Login / logout / password reset
│   │   │
│   │   ├── Admin/
│   │   │   ├── AdminBaseController.php  ← Injects sidebar, user, notifications
│   │   │   ├── CrudController.php       ← Generic CRUD base (extend for any module)
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php
│   │   │   ├── RoleController.php
│   │   │   ├── SettingController.php
│   │   │   └── FileController.php
│   │   │
│   │   ├── Api/
│   │   │   ├── ApiBaseController.php    ← JSON respond helpers
│   │   │   ├── AuthController.php       ← Token login/logout
│   │   │   └── UserController.php       ← REST CRUD
│   │   │
│   │   └── Website/
│   │       └── HomeController.php
│   │
│   ├── Filters/
│   │   ├── AuthFilter.php          ← Session guard + role check
│   │   ├── ApiAuthFilter.php       ← Bearer token validation
│   │   └── ActivityLogFilter.php   ← Auto-log POST/PUT/DELETE
│   │
│   ├── Models/
│   │   ├── UserModel.php           ← +assignRole, syncRoles, getUserRoleNames
│   │   ├── RoleModel.php           ← +getUserPermissions, syncPermissions
│   │   ├── MenuModel.php           ← +getSidebarMenu (nested, permission-filtered)
│   │   ├── SettingModel.php        ← +getValue, getGroup, saveMany (cached)
│   │   ├── ActivityLogModel.php
│   │   ├── NotificationModel.php   ← +createFor, markRead, markAllReadFor
│   │   ├── FileModel.php           ← +getForModule, humanSize
│   │   └── PermissionModel.php     ← +seedForModule
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   └── admin_layout.php    ← Full sidebar + topbar shell
│   │   │
│   │   ├── components/
│   │   │   ├── crud_table.php      ← Reusable data table (columns config array)
│   │   │   └── crud_form.php       ← Reusable form (fields config array)
│   │   │
│   │   ├── auth/
│   │   │   └── login.php           ← Split-panel login UI
│   │   │
│   │   ├── admin/
│   │   │   ├── dashboard/index.php
│   │   │   ├── users/{index,form,show}.php
│   │   │   ├── roles/{index,form}.php
│   │   │   └── settings/index.php
│   │   │
│   │   └── website/
│   │       ├── home.php
│   │       ├── about.php
│   │       └── contact.php
│   │
│   └── Database/
│       ├── Migrations/
│       │   └── 2024-01-01-000001_CreateUsersTable.php
│       └── Seeds/
│           └── InitialDataSeeder.php   ← Creates super admin + default data
│
├── public/
│   └── assets/
│       ├── css/admin.css            ← Sidebar, stats cards, tables, dark mode
│       └── js/admin.js              ← Sidebar toggle, dark mode, AJAX, toasts
│
└── database_schema.sql              ← Full 13-table MySQL schema + seed data
```

---

## Database Tables (13)

| #  | Table            | Purpose                                 |
|----|------------------|-----------------------------------------|
| 1  | `users`          | User accounts with soft delete          |
| 2  | `roles`          | Role definitions                        |
| 3  | `permissions`    | Granular permission flags               |
| 4  | `role_permissions` | Role ↔ Permission many-to-many        |
| 5  | `user_roles`     | User ↔ Role many-to-many               |
| 6  | `menus`          | Menu groups (admin, website)            |
| 7  | `menu_items`     | Nested menu items with permission gate  |
| 8  | `modules`        | Module registry                         |
| 9  | `settings`       | Key-value application config            |
| 10 | `activity_logs`  | Every user action logged                |
| 11 | `notifications`  | Per-user notification inbox             |
| 12 | `files`          | Uploaded file registry                  |
| 13 | `audit_trail`    | Before/after change log                 |

---

## Installation

### 1. Clone & install
```bash
git clone <repo> ci4-app
cd ci4-app
composer install
cp env .env
```

### 2. Configure `.env`
```ini
CI_ENVIRONMENT = development

database.default.hostname = 127.0.0.1
database.default.database = ci4_boilerplate
database.default.username = root
database.default.password = secret
database.default.DBDriver = MySQLi
database.default.port     = 3306

app.baseURL = 'http://localhost:8080/'
```

### 3. Run schema
```bash
mysql -u root -p ci4_boilerplate < database_schema.sql
```

**OR** use CI4 migrations + seeder:
```bash
php spark migrate
php spark db:seed InitialDataSeeder
```

### 4. Serve
```bash
php spark serve
```

Open: http://localhost:8080/auth/login

**Default credentials:**
- Email: `admin@example.com`
- Password: `Admin@1234`

---

## RBAC Quick Reference

```php
// Check permission in any controller
$this->can('users.create');          // bool
$this->authorize('users.delete');    // throws 404 if not allowed

// Check in views
if (can('settings.update')): ?>
    <a href="...">Settings</a>
<?php endif; ?>
```

---

## Generic CRUD — Create a new module in 3 steps

**Step 1:** Create your model
```php
class ProductModel extends Model {
    protected $table = 'products';
    protected $allowedFields = ['name','sku','price','status','deleted_at'];
}
```

**Step 2:** Create your controller
```php
class ProductController extends CrudController {
    protected string $modelClass  = ProductModel::class;
    protected string $module      = 'products';
    protected string $routePrefix = 'admin.products';
    protected string $viewPrefix  = 'admin/products';
    protected array  $searchFields= ['name','sku'];

    protected function validationRules(?int $id = null): array {
        return [
            'name'  => 'required|max_length[200]',
            'price' => 'required|decimal',
        ];
    }
}
```

**Step 3:** Add routes
```php
$routes->get( 'products',              'Admin\ProductController::index',  ['as'=>'admin.products.index']);
$routes->get( 'products/create',       'Admin\ProductController::create');
$routes->post('products/store',        'Admin\ProductController::store');
$routes->get( 'products/(:num)/edit',  'Admin\ProductController::edit/$1');
$routes->post('products/(:num)/update','Admin\ProductController::update/$1');
$routes->post('products/(:num)/delete','Admin\ProductController::delete/$1');
```

---

## API Usage

### Authenticate
```http
POST /api/v1/auth/login
Content-Type: application/json

{ "email": "admin@example.com", "password": "Admin@1234" }
```

Response:
```json
{
  "status": "success",
  "data": {
    "token": "abc123...",
    "user": { "id": 1, "email": "admin@example.com" }
  }
}
```

### Subsequent requests
```http
GET /api/v1/users
Authorization: Bearer abc123...
```

---

## Reusable View Components

### Dynamic Table
```php
<?= view('components/crud_table', [
    'columns' => [
        ['label' => 'ID',     'key' => 'id',     'width' => '50px'],
        ['label' => 'Name',   'key' => 'name'],
        ['label' => 'Status', 'key' => 'status', 'badge' => ['active'=>'success','inactive'=>'secondary']],
        ['label' => 'Date',   'key' => 'created_at', 'date' => true],
    ],
    'rows'      => $rows,
    'pager'     => $pager,
    'routeEdit' => 'admin.products.edit',
    'routeDel'  => fn($r) => base_url("admin/products/{$r['id']}/delete"),
]) ?>
```

### Dynamic Form
```php
<?= view('components/crud_form', [
    'action' => base_url('admin/products/store'),
    'record' => $record ?? null,
    'fields' => [
        ['name'=>'name',   'label'=>'Name',   'type'=>'text',   'required'=>true, 'col'=>6],
        ['name'=>'price',  'label'=>'Price',  'type'=>'number', 'required'=>true, 'col'=>3, 'step'=>'0.01'],
        ['name'=>'status', 'label'=>'Status', 'type'=>'select', 'col'=>3,
            'options'=>['active'=>'Active','inactive'=>'Inactive']],
        ['name'=>'description','label'=>'Description','type'=>'textarea','rows'=>4],
        ['name'=>'is_featured','label'=>'Featured','type'=>'switch'],
    ],
    'cancelUrl' => route_to('admin.products.index'),
]) ?>
```

---

## Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| **Explicit routes only** | `$routes->setAutoRoute(false)` — predictable, secure |
| **Soft deletes everywhere** | `deleted_at` column; data is never permanently lost |
| **SettingModel cache** | Static `$cache[]` avoids repeated DB hits per request |
| **MenuModel nesting** | Recursive `nestItems()` builds unlimited depth from flat DB rows |
| **CrudController base** | 90% of admin modules need identical list/add/edit/delete — DRY |
| **Filter-based logging** | `ActivityLogFilter` auto-logs all POST/PUT/DELETE without controller code |
| **Bearer token API auth** | Simple stateless auth; swap for JWT by extending `ApiAuthFilter` |

---

## Security Checklist

- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Enable CSRF filter in `app/Config/Filters.php`
- [ ] Set strong `app.encryptionKey`
- [ ] Use HTTPS; set `cookie.secure = true`
- [ ] Configure `session.expiration`
- [ ] Review `login_attempts` setting
- [ ] Restrict `public/uploads` from script execution in nginx/apache

---

## License

MIT — Free to use, modify, and distribute.
