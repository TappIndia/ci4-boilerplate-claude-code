# CI4 + Tailwind CSS + DaisyUI + Alpine.js
## Complete Local Setup Guide — Every Command Included

---

# PHASE 0 — PREREQUISITES CHECK

## 0.1 Verify PHP (8.1+ required)
```bash
php -v
```
Expected output: `PHP 8.1.x` or higher.
If not installed → https://www.php.net/downloads

## 0.2 Verify Composer
```bash
composer -V
```
Expected output: `Composer version 2.x`
If not installed:
```bash
# Windows (download installer)
# https://getcomposer.org/Composer-Setup.exe

# macOS / Linux
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

## 0.3 Verify Node.js & npm (18+ required for Tailwind v4)
```bash
node -v
npm -v
```
If not installed → https://nodejs.org (LTS version)

## 0.4 Verify MySQL (8.x recommended)
```bash
mysql --version
```
If not installed:
- Windows: XAMPP / MySQL Installer
- macOS: `brew install mysql`
- Linux: `sudo apt install mysql-server`

## 0.5 Verify Git
```bash
git --version
```

---

# PHASE 1 — CREATE CODEIGNITER 4 PROJECT

## 1.1 Create project via Composer
```bash
composer create-project codeigniter4/appstarter ci4-app
```

## 1.2 Enter project directory
```bash
cd ci4-app
```

## 1.3 Verify CI4 installed correctly
```bash
php spark --version
```
Expected output: `CodeIgniter CLI Tool - Version X.X.X`

## 1.4 Copy environment file
```bash
# Windows
copy env .env

# macOS / Linux
cp env .env
```

## 1.5 Open .env and set environment to development
```bash
# Windows
notepad .env

# macOS (nano editor)
nano .env

# Linux
nano .env
```

Inside `.env`, change this line:
```
# CI_ENVIRONMENT = production
```
To:
```
CI_ENVIRONMENT = development
```

---

# PHASE 2 — DATABASE SETUP

## 2.1 Start MySQL and create the database
```bash
mysql -u root -p
```
Once logged in, run:
```sql
CREATE DATABASE ci4_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
EXIT;
```

## 2.2 Configure .env database credentials
```bash
nano .env
```
Find and update these lines (remove the `#` prefix to uncomment):
```ini
database.default.hostname = 127.0.0.1
database.default.database = ci4_app
database.default.username = root
database.default.password = YOUR_MYSQL_PASSWORD
database.default.DBDriver = MySQLi
database.default.port     = 3306
database.default.charset  = utf8mb4
```

## 2.3 Set base URL in .env
```ini
app.baseURL = 'http://localhost:8080/'
```

## 2.4 Test database connection
```bash
php spark db:connect
```
Expected: `Database connection is valid.`

---

# PHASE 3 — INSTALL TAILWIND CSS + DAISYUI

## 3.1 Initialize npm in the project root
```bash
npm init -y
```
This creates `package.json`.

## 3.2 Install Tailwind CSS v4 + DaisyUI v4
```bash
npm install tailwindcss @tailwindcss/vite daisyui
```

## 3.3 Install Vite as build tool
```bash
npm install --save-dev vite
```

## 3.4 Install Vite plugin for Laravel (works for CI4 too) as alternative, OR use plain Vite config
```bash
npm install --save-dev vite
```

## 3.5 Create Tailwind CSS input file
```bash
# Windows
mkdir public\assets\css
echo. > public\assets\css\app.css

# macOS / Linux
mkdir -p public/assets/css
touch public/assets/css/app.css
```

## 3.6 Write the Tailwind directives into app.css
```bash
# Windows (PowerShell)
Set-Content public\assets\css\app.css "@import 'tailwindcss';`n@plugin 'daisyui';"

# macOS / Linux
cat > public/assets/css/app.css << 'EOF'
@import "tailwindcss";
@plugin "daisyui";
EOF
```

## 3.7 Create vite.config.js
```bash
# Windows
echo. > vite.config.js
notepad vite.config.js

# macOS / Linux
touch vite.config.js
nano vite.config.js
```

Paste this content into `vite.config.js`:
```js
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
  plugins: [
    tailwindcss(),
  ],
  build: {
    outDir: 'public/assets/dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        app: 'public/assets/css/app.css',
        admin: 'public/assets/js/admin.js',
      },
      output: {
        assetFileNames: 'css/[name][extname]',
        entryFileNames: 'js/[name].js',
      },
    },
  },
  server: {
    origin: 'http://localhost:5173',
  },
});
```

## 3.8 Add build scripts to package.json
```bash
# Open package.json
# Windows
notepad package.json

# macOS / Linux
nano package.json
```

Update the `"scripts"` section:
```json
"scripts": {
  "dev": "vite",
  "build": "vite build",
  "preview": "vite preview"
}
```

## 3.9 Create the JS output directory and admin.js entry point
```bash
# Windows
mkdir public\assets\js
echo. > public\assets\js\admin.js

# macOS / Linux
mkdir -p public/assets/js
touch public/assets/js/admin.js
```

## 3.10 Test Tailwind build works
```bash
npm run build
```
Expected: `public/assets/dist/css/app.css` and `public/assets/dist/js/admin.js` created.

---

# PHASE 4 — INSTALL ALPINE.JS

## 4.1 Install Alpine.js via npm
```bash
npm install alpinejs
```

## 4.2 Write Alpine.js initialization in admin.js
```bash
# Windows
notepad public\assets\js\admin.js

# macOS / Linux
nano public/assets/js/admin.js
```

Paste this content:
```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

## 4.3 Rebuild to include Alpine.js
```bash
npm run build
```

---

# PHASE 5 — CI4 FOLDER STRUCTURE (via spark make)

## 5.1 Create all Controller namespaces using spark
```bash
# Admin controllers
php spark make:controller Admin/AdminBaseController --suffix
php spark make:controller Admin/DashboardController --suffix
php spark make:controller Admin/UserController --suffix
php spark make:controller Admin/RoleController --suffix
php spark make:controller Admin/SettingController --suffix
php spark make:controller Admin/FileController --suffix
php spark make:controller Admin/CrudController --suffix

# API controllers
php spark make:controller Api/ApiBaseController --suffix
php spark make:controller Api/AuthController --suffix
php spark make:controller Api/UserController --suffix

# Website controllers
php spark make:controller Website/HomeController --suffix

# Auth controller (root level)
php spark make:controller AuthController --suffix
```

## 5.2 Create all Models using spark
```bash
php spark make:model UserModel --suffix
php spark make:model RoleModel --suffix
php spark make:model PermissionModel --suffix
php spark make:model MenuModel --suffix
php spark make:model SettingModel --suffix
php spark make:model ActivityLogModel --suffix
php spark make:model NotificationModel --suffix
php spark make:model FileModel --suffix
```

## 5.3 Create Filters using spark
```bash
php spark make:filter AuthFilter
php spark make:filter ApiAuthFilter
php spark make:filter ActivityLogFilter
```

## 5.4 Create Database Migrations using spark
```bash
php spark make:migration CreateRolesTable
php spark make:migration CreateUsersTable
php spark make:migration CreatePermissionsTable
php spark make:migration CreateRolePermissionsTable
php spark make:migration CreateUserRolesTable
php spark make:migration CreateModulesTable
php spark make:migration CreateMenusTable
php spark make:migration CreateMenuItemsTable
php spark make:migration CreateSettingsTable
php spark make:migration CreateActivityLogsTable
php spark make:migration CreateNotificationsTable
php spark make:migration CreateFilesTable
php spark make:migration CreateAuditTrailTable
```

## 5.5 Create Database Seeder using spark
```bash
php spark make:seeder InitialDataSeeder
```

## 5.6 Create View directories manually (spark doesn't make view folders)
```bash
# Windows
mkdir app\Views\layouts
mkdir app\Views\components
mkdir app\Views\auth
mkdir app\Views\admin\dashboard
mkdir app\Views\admin\users
mkdir app\Views\admin\roles
mkdir app\Views\admin\settings
mkdir app\Views\admin\files
mkdir app\Views\website

# macOS / Linux
mkdir -p app/Views/layouts
mkdir -p app/Views/components
mkdir -p app/Views/auth
mkdir -p app/Views/admin/{dashboard,users,roles,settings,files}
mkdir -p app/Views/website
```

## 5.7 Create View files using touch/echo
```bash
# Windows
echo. > app\Views\layouts\admin_layout.php
echo. > app\Views\layouts\website_layout.php
echo. > app\Views\components\crud_table.php
echo. > app\Views\components\crud_form.php
echo. > app\Views\components\alert.php
echo. > app\Views\auth\login.php
echo. > app\Views\auth\forgot_password.php
echo. > app\Views\auth\reset_password.php
echo. > app\Views\admin\dashboard\index.php
echo. > app\Views\admin\users\index.php
echo. > app\Views\admin\users\form.php
echo. > app\Views\admin\users\show.php
echo. > app\Views\admin\roles\index.php
echo. > app\Views\admin\roles\form.php
echo. > app\Views\admin\settings\index.php
echo. > app\Views\admin\files\index.php
echo. > app\Views\website\home.php
echo. > app\Views\website\about.php
echo. > app\Views\website\contact.php

# macOS / Linux
touch app/Views/layouts/admin_layout.php
touch app/Views/layouts/website_layout.php
touch app/Views/components/crud_table.php
touch app/Views/components/crud_form.php
touch app/Views/components/alert.php
touch app/Views/auth/login.php
touch app/Views/auth/forgot_password.php
touch app/Views/auth/reset_password.php
touch app/Views/admin/dashboard/index.php
touch app/Views/admin/users/index.php
touch app/Views/admin/users/form.php
touch app/Views/admin/users/show.php
touch app/Views/admin/roles/index.php
touch app/Views/admin/roles/form.php
touch app/Views/admin/settings/index.php
touch app/Views/admin/files/index.php
touch app/Views/website/home.php
touch app/Views/website/about.php
touch app/Views/website/contact.php
```

## 5.8 Create public upload directory
```bash
# Windows
mkdir public\uploads

# macOS / Linux
mkdir -p public/uploads
chmod 755 public/uploads
```

## 5.9 Verify full structure looks correct
```bash
# Windows
tree app /F

# macOS / Linux
find app -type f | sort
```

---

# PHASE 6 — CONFIGURE TAILWIND + DAISYUI THEME

## 6.1 Update app.css with DaisyUI theme configuration
```bash
# Windows
notepad public\assets\css\app.css

# macOS / Linux
nano public/assets/css/app.css
```

Replace content with:
```css
@import "tailwindcss";
@plugin "daisyui" {
  themes: light, dark, cupcake;
}

/* Custom component layer overrides */
@layer components {
  .sidebar-link {
    @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150;
  }
  .sidebar-link:hover {
    @apply bg-base-content/10;
  }
  .sidebar-link.active {
    @apply bg-primary text-primary-content;
  }
  .btn-action {
    @apply btn btn-xs btn-ghost;
  }
  .stat-card {
    @apply stat bg-base-100 rounded-2xl shadow-sm;
  }
}
```

## 6.2 Rebuild CSS after theme config
```bash
npm run build
```

---

# PHASE 7 — CONFIGURE ROUTES

## 7.1 Open Routes.php
```bash
# Windows
notepad app\Config\Routes.php

# macOS / Linux
nano app/Config/Routes.php
```

## 7.2 Replace default routes with the full boilerplate routes
Paste the full Routes.php content (from the boilerplate) which covers:
- Public/website routes at `/`
- Auth routes under `/auth/`
- Admin routes under `/admin/` with `filter => 'auth:admin'`
- API routes under `/api/v1/` with `filter => 'api_auth'`

Key settings to apply at the top:
```php
$routes->setAutoRoute(false);       // Security: explicit routes only
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Website\HomeController');
$routes->setDefaultMethod('index');
```

---

# PHASE 8 — CONFIGURE FILTERS

## 8.1 Open Filters.php
```bash
# Windows
notepad app\Config\Filters.php

# macOS / Linux
nano app/Config/Filters.php
```

## 8.2 Register custom filter aliases
Add to the `$aliases` array:
```php
'auth'         => \App\Filters\AuthFilter::class,
'api_auth'     => \App\Filters\ApiAuthFilter::class,
'log_activity' => \App\Filters\ActivityLogFilter::class,
```

## 8.3 Apply log_activity filter globally to admin/api
Add to the `$filters` array:
```php
public array $filters = [
    'log_activity' => ['before' => ['admin/*', 'api/*']],
];
```

---

# PHASE 9 — WRITE MIGRATIONS AND RUN THEM

## 9.1 Edit each migration file created in Phase 5
Open and fill in migration files. Example for CreateUsersTable:
```bash
# Windows
notepad app\Database\Migrations\<timestamp>_CreateUsersTable.php

# macOS / Linux
nano app/Database/Migrations/*CreateUsersTable.php
```

## 9.2 OR skip writing migrations and import SQL directly
```bash
mysql -u root -p ci4_app < database_schema.sql
```
Confirm:
```bash
mysql -u root -p ci4_app -e "SHOW TABLES;"
```
Expected: 13 tables listed.

## 9.3 If using spark migrations, run them all
```bash
php spark migrate
```

## 9.4 Run the seeder to populate default data
```bash
php spark db:seed InitialDataSeeder
```
Expected output:
```
✅  Initial seed data created successfully.
    Super Admin: admin@example.com / Admin@1234
```

## 9.5 Verify data was seeded
```bash
mysql -u root -p ci4_app -e "SELECT id, email, status FROM users;"
mysql -u root -p ci4_app -e "SELECT id, name, label FROM roles;"
mysql -u root -p ci4_app -e "SELECT id, key, value FROM settings LIMIT 5;"
```

---

# PHASE 10 — WRITE ADMIN LAYOUT WITH TAILWIND/DAISYUI

## 10.1 Open admin_layout.php
```bash
# Windows
notepad app\Views\layouts\admin_layout.php

# macOS / Linux
nano app/Views/layouts/admin_layout.php
```

## 10.2 Scaffold structure (Tailwind/DaisyUI equivalent of admin_layout)
Key DaisyUI classes to use in the layout:
```html
<!DOCTYPE html>
<html lang="en" data-theme="light" x-data="adminApp()" :data-theme="theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($page_title ?? 'Dashboard') ?></title>

    <!-- Compiled Tailwind + DaisyUI -->
    <link rel="stylesheet" href="<?= base_url('assets/dist/css/app.css') ?>">
</head>
<body class="bg-base-200 min-h-screen">

<!-- Drawer layout (DaisyUI sidebar pattern) -->
<div class="drawer lg:drawer-open">
  <input id="drawer-toggle" type="checkbox" class="drawer-toggle" />

  <!-- Page content -->
  <div class="drawer-content flex flex-col min-h-screen">
    <!-- Navbar -->
    <navbar class="navbar bg-base-100 shadow-sm sticky top-0 z-30">
      <label for="drawer-toggle" class="btn btn-ghost lg:hidden">
        <!-- hamburger icon -->
      </label>
      <!-- ... topbar content -->
    </navbar>

    <!-- Main -->
    <main class="flex-1 p-4 lg:p-6">
      <?= $content ?? '' ?>
    </main>

    <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-300">
      <!-- footer -->
    </footer>
  </div>

  <!-- Sidebar drawer -->
  <div class="drawer-side z-40">
    <label for="drawer-toggle" class="drawer-overlay"></label>
    <aside class="bg-base-100 w-64 min-h-full flex flex-col shadow-xl">
      <!-- sidebar content -->
    </aside>
  </div>
</div>

<!-- Alpine.js + Admin JS -->
<script src="<?= base_url('assets/dist/js/admin.js') ?>"></script>
</body>
</html>
```

---

# PHASE 11 — WRITE ADMIN.JS WITH ALPINE.JS DATA

## 11.1 Open admin.js
```bash
# Windows
notepad public\assets\js\admin.js

# macOS / Linux
nano public/assets/js/admin.js
```

## 11.2 Write Alpine.js app data and CSRF helper
```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ── Global Alpine component ──────────────────────────────────
document.addEventListener('alpine:init', () => {
  Alpine.data('adminApp', () => ({
    theme: localStorage.getItem('theme') || 'light',
    sidebarOpen: false,
    notifications: [],

    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light';
      localStorage.setItem('theme', this.theme);
      document.documentElement.setAttribute('data-theme', this.theme);
    },

    async loadNotifications() {
      const res = await fetch('/api/v1/notifications', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const json = await res.json();
      this.notifications = json.data || [];
    },

    async markAllRead() {
      await fetch('/api/v1/notifications/read-all', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Content-Type': 'application/json',
        }
      });
      this.notifications = this.notifications.map(n => ({ ...n, is_read: 1 }));
    }
  }));

  // ── Confirm delete ─────────────────────────────────────────
  Alpine.directive('confirm', (el, { expression }) => {
    el.addEventListener('click', (e) => {
      if (!confirm(expression || 'Are you sure?')) e.preventDefault();
    });
  });
});

Alpine.start();

// ── CSRF-aware fetch helper ─────────────────────────────────
window.apiRequest = async (url, method = 'GET', data = null) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const opts  = {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': token,
    },
  };
  if (data && method !== 'GET') opts.body = JSON.stringify(data);
  const res  = await fetch(url, opts);
  const json = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(json.message ?? `HTTP ${res.status}`);
  return json;
};

// ── Toast notification helper ───────────────────────────────
window.showToast = (message, type = 'success') => {
  const toastTypes = {
    success: 'alert-success',
    error:   'alert-error',
    warning: 'alert-warning',
    info:    'alert-info',
  };
  const id = 'toast-' + Date.now();
  const html = `
    <div id="${id}" class="alert ${toastTypes[type] || 'alert-success'} shadow-lg min-w-64">
      <span>${message}</span>
    </div>`;

  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast toast-end toast-bottom z-50 fixed';
    document.body.appendChild(container);
  }
  container.insertAdjacentHTML('beforeend', html);
  setTimeout(() => document.getElementById(id)?.remove(), 4000);
};

// ── Debounced search ────────────────────────────────────────
document.querySelectorAll('[data-search-form]').forEach(input => {
  let timer;
  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => input.closest('form')?.submit(), 400);
  });
});
```

## 11.3 Rebuild after editing admin.js
```bash
npm run build
```

---

# PHASE 12 — VERIFY THE FULL SETUP

## 12.1 Check PHP can find CI4 correctly
```bash
php spark routes
```
Expected: all routes listed (home, auth/*, admin/*, api/v1/*)

## 12.2 Check for any missing files or autoload errors
```bash
php spark list
```
No errors should appear.

## 12.3 Run a dry-migration to check migration files syntax
```bash
php spark migrate --dry-run
```

## 12.4 Check Tailwind output file size (should be > 0)
```bash
# Windows
dir public\assets\dist\css\app.css

# macOS / Linux
ls -lh public/assets/dist/css/app.css
```

## 12.5 Check Alpine.js is in the bundle
```bash
# macOS / Linux
grep -c "Alpine" public/assets/dist/js/admin.js

# Windows PowerShell
Select-String -Path "public\assets\dist\js\admin.js" -Pattern "Alpine"
```

---

# PHASE 13 — START DEV SERVER (TWO TERMINALS)

## Terminal 1 — Start Vite dev server (hot reload CSS/JS)
```bash
npm run dev
```
Expected output:
```
  VITE v5.x ready in XXX ms
  ➜  Local:   http://localhost:5173/
```

## Terminal 2 — Start CI4 dev server
```bash
php spark serve
```
Expected output:
```
CodeIgniter development server started on http://localhost:8080
```

## Open in browser
```
http://localhost:8080/auth/login
```

Login credentials:
- Email: `admin@example.com`
- Password: `Admin@1234`

---

# PHASE 14 — PRODUCTION BUILD

## 14.1 Build optimized CSS + JS for production
```bash
npm run build
```

## 14.2 Update .env to production
```bash
nano .env
```
Change:
```ini
CI_ENVIRONMENT = production
```

## 14.3 Update asset paths in layout to use dist/ files
In `admin_layout.php`, link to compiled files:
```html
<link rel="stylesheet" href="<?= base_url('assets/dist/css/app.css') ?>">
<script type="module" src="<?= base_url('assets/dist/js/admin.js') ?>"></script>
```

## 14.4 Set proper permissions on storage and writable directories
```bash
# macOS / Linux only
chmod -R 775 writable/
chmod -R 775 public/uploads/
```

## 14.5 Enable CSRF in production Filters.php
```bash
nano app/Config/Filters.php
```
Uncomment `'csrf'` in `$globals['before']`:
```php
'before' => [
    'honeypot',
    'csrf',    // ← uncomment this
],
```

---

# PHASE 15 — OPTIONAL: LIVEWIRE-STYLE HOT RELOAD IN DEV

## 15.1 Update vite.config.js for hot reload with CI4
```bash
nano vite.config.js
```

Update server section:
```js
server: {
  host: 'localhost',
  port: 5173,
  origin: 'http://localhost:5173',
  watch: {
    usePolling: true,
  },
},
```

## 15.2 In dev, use Vite-served assets in admin_layout.php
```php
<?php
$isDev = ENVIRONMENT === 'development';
$viteBase = 'http://localhost:5173';
?>

<?php if ($isDev): ?>
  <script type="module" src="<?= $viteBase ?>/@vite/client"></script>
  <link rel="stylesheet" href="<?= $viteBase ?>/public/assets/css/app.css">
  <script type="module" src="<?= $viteBase ?>/public/assets/js/admin.js"></script>
<?php else: ?>
  <link rel="stylesheet" href="<?= base_url('assets/dist/css/app.css') ?>">
  <script type="module" src="<?= base_url('assets/dist/js/admin.js') ?>"></script>
<?php endif; ?>
```

---

# PHASE 16 — COMMON TROUBLESHOOTING

## Problem: `php spark serve` shows 404 on all routes
Fix: Check `.env` has `app.baseURL` set correctly and Routes.php has `setAutoRoute(false)`.

## Problem: Tailwind classes not generating / CSS is empty
Fix:
```bash
# Ensure content paths in vite config cover your PHP files
# Rebuild from scratch
rm -rf node_modules
npm install
npm run build
```

## Problem: DaisyUI components not styled
Fix: Confirm `@plugin "daisyui"` is in app.css, not `@tailwind base/components/utilities`.
DaisyUI v4 uses plugin syntax, not `require()`.

## Problem: Alpine.js `x-data` not initializing
Fix: Make sure `admin.js` has `type="module"` in the script tag:
```html
<script type="module" src="...admin.js"></script>
```

## Problem: CSRF mismatch on AJAX
Fix: Include the CSRF token in all AJAX headers:
```js
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
```
And make sure `<meta name="csrf-token" content="<?= csrf_hash() ?>">` is in `<head>`.

## Problem: Database migration fails
Fix:
```bash
php spark migrate:rollback
php spark migrate
```
Or re-import the SQL file directly:
```bash
mysql -u root -p ci4_app < database_schema.sql
```

## Problem: `composer create-project` is slow
Fix:
```bash
composer config -g repo.packagist composer https://packagist.org
composer create-project codeigniter4/appstarter ci4-app --no-install
cd ci4-app
composer install --prefer-dist
```

---

# QUICK REFERENCE CARD

## Daily development commands
```bash
# Start both servers (two terminals)
npm run dev          # Terminal 1 — Vite hot reload
php spark serve      # Terminal 2 — CI4 server

# After changing PHP/Controller/Model
# (nothing needed — CI4 auto-reloads)

# After changing CSS/JS
# (Vite auto-reloads in dev mode)

# Create new module scaffold
php spark make:controller Admin/ProductController --suffix
php spark make:model ProductModel --suffix
php spark make:migration CreateProductsTable
php spark migrate
```

## Useful spark commands
```bash
php spark routes              # List all registered routes
php spark db:seed NameSeeder  # Run a specific seeder
php spark migrate:status      # Show migration run status
php spark cache:clear         # Clear all CI4 caches
php spark env                 # Show current environment
php spark make:command Name   # Create a custom CLI command
```

## npm commands
```bash
npm run dev      # Start Vite dev server with HMR
npm run build    # Production build (minified, hashed)
npm run preview  # Preview production build locally
```
