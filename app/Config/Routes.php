<?php

// =============================================================
// app/Config/Routes.php  — CI4 Universal Boilerplate
// =============================================================

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ------------------------------------------------------------------
// Global Options
// ------------------------------------------------------------------
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Website\HomeController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);   // Explicit routes only

// ==================================================================
// PUBLIC / WEBSITE ROUTES
// ==================================================================
$routes->group('', ['namespace' => 'App\Controllers\Website'], function ($routes) {
    $routes->get('/',        'HomeController::index',    ['as' => 'home']);
    $routes->get('about',    'HomeController::about',    ['as' => 'about']);
    $routes->get('contact',  'HomeController::contact',  ['as' => 'contact']);
    $routes->post('contact', 'HomeController::contactSubmit');
});

// ==================================================================
// AUTH ROUTES  (no middleware — login page must be public)
// ==================================================================
$routes->group('auth', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get( 'login',              'AuthController::login',         ['as' => 'auth.login']);
    $routes->post('login',              'AuthController::loginProcess');
    $routes->get( 'logout',             'AuthController::logout',        ['as' => 'auth.logout']);
    $routes->get( 'forgot-password',    'AuthController::forgotPassword',['as' => 'auth.forgot']);
    $routes->post('forgot-password',    'AuthController::forgotProcess');
    $routes->get( 'reset-password/(:any)', 'AuthController::resetPassword/$1', ['as' => 'auth.reset']);
    $routes->post('reset-password',     'AuthController::resetProcess');
});

// ==================================================================
// ADMIN ROUTES  — protected by AuthFilter + AdminFilter
// ==================================================================
$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => 'auth:admin',
], function ($routes) {

    // Dashboard
    $routes->get('/',          'DashboardController::index', ['as' => 'admin.dashboard']);
    $routes->get('dashboard',  'DashboardController::index');

    // ── Users ──────────────────────────────────────────────────
    $routes->get( 'users',                 'UserController::index',   ['as' => 'admin.users.index']);
    $routes->get( 'users/create',          'UserController::create',  ['as' => 'admin.users.create']);
    $routes->post('users/store',           'UserController::store',   ['as' => 'admin.users.store']);
    $routes->get( 'users/(:num)/edit',     'UserController::edit/$1', ['as' => 'admin.users.edit']);
    $routes->post('users/(:num)/update',   'UserController::update/$1');
    $routes->post('users/(:num)/delete',   'UserController::delete/$1');
    $routes->get( 'users/(:num)/view',     'UserController::show/$1', ['as' => 'admin.users.show']);

    // ── Roles ──────────────────────────────────────────────────
    $routes->get( 'roles',                 'RoleController::index',   ['as' => 'admin.roles.index']);
    $routes->get( 'roles/create',          'RoleController::create',  ['as' => 'admin.roles.create']);
    $routes->post('roles/store',           'RoleController::store');
    $routes->get( 'roles/(:num)/edit',     'RoleController::edit/$1');
    $routes->post('roles/(:num)/update',   'RoleController::update/$1');
    $routes->post('roles/(:num)/delete',   'RoleController::delete/$1');
    $routes->post('roles/(:num)/permissions', 'RoleController::updatePermissions/$1');

    // ── Menus ──────────────────────────────────────────────────
    $routes->get( 'menus',                 'MenuController::index',   ['as' => 'admin.menus.index']);
    $routes->post('menus/store',           'MenuController::store');
    $routes->post('menus/(:num)/update',   'MenuController::update/$1');
    $routes->post('menus/(:num)/delete',   'MenuController::delete/$1');
    $routes->get( 'menus/(:num)/items',    'MenuController::items/$1');
    $routes->post('menus/items/store',     'MenuController::storeItem');
    $routes->post('menus/items/(:num)/update', 'MenuController::updateItem/$1');
    $routes->post('menus/items/(:num)/delete', 'MenuController::deleteItem/$1');

    // ── Files ──────────────────────────────────────────────────
    $routes->get( 'files',                 'FileController::index',   ['as' => 'admin.files.index']);
    $routes->post('files/upload',          'FileController::upload');
    $routes->post('files/(:num)/delete',   'FileController::delete/$1');

    // ── Activity Logs ──────────────────────────────────────────
    $routes->get('logs',                   'LogController::index',    ['as' => 'admin.logs.index']);
    $routes->get('logs/(:num)',            'LogController::show/$1');

    // ── Settings ───────────────────────────────────────────────
    $routes->get( 'settings',              'SettingController::index', ['as' => 'admin.settings']);
    $routes->post('settings/save',         'SettingController::save');

    // ── Profile ────────────────────────────────────────────────
    $routes->get( 'profile',               'ProfileController::index', ['as' => 'admin.profile']);
    $routes->post('profile/update',        'ProfileController::update');
    $routes->post('profile/password',      'ProfileController::changePassword');
});

// ==================================================================
// API ROUTES  — protected by ApiAuthFilter
// ==================================================================
$routes->group('api/v1', [
    'namespace' => 'App\Controllers\Api',
    'filter'    => 'api_auth',
], function ($routes) {

    // Auth
    $routes->post('auth/login',    'AuthController::login');
    $routes->post('auth/logout',   'AuthController::logout');
    $routes->post('auth/refresh',  'AuthController::refresh');

    // Users
    $routes->get( 'users',              'UserController::index');
    $routes->post('users',              'UserController::store');
    $routes->get( 'users/(:num)',       'UserController::show/$1');
    $routes->put( 'users/(:num)',       'UserController::update/$1');
    $routes->delete('users/(:num)',     'UserController::delete/$1');

    // Settings (read-only from API)
    $routes->get('settings',            'SettingController::index');

    // Notifications
    $routes->get( 'notifications',              'NotificationController::index');
    $routes->post('notifications/(:num)/read',  'NotificationController::markRead/$1');
    $routes->post('notifications/read-all',     'NotificationController::markAllRead');
});
