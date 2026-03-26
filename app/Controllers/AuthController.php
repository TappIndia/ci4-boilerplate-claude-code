<?php

// app/Controllers/AuthController.php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->roleModel = model(RoleModel::class);
    }

    // ── GET /auth/login ──────────────────────────────────────────────
    public function login(): string
    {
        if (session()->has('user_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth/login', ['site_name' => setting('site_name', 'CI4 App')]);
    }

    // ── POST /auth/login ─────────────────────────────────────────────
    public function loginProcess()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)
                                ->where('deleted_at', null)
                                ->first();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            $this->logActivity('login_failed', 'auth', "Failed login for {$email}");
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Invalid email or password.');
        }

        if ($user['status'] !== 'active') {
            return redirect()->back()
                             ->with('error', 'Your account is not active.');
        }

        // Load roles + permissions
        $roles       = $this->roleModel->getUserRoleNames($user['id']);
        $permissions = $this->roleModel->getUserPermissions($user['id']);

        // Hydrate session
        $session = session();
        $session->set([
            'user_id'          => $user['id'],
            'user'             => $user,
            'user_roles'       => $roles,
            'user_permissions' => $permissions,
        ]);

        // Update last login
        $this->userModel->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->getIPAddress(),
        ]);

        $this->logActivity('login_success', 'auth', "User {$email} logged in");

        return redirect()->route('admin.dashboard');
    }

    // ── GET /auth/logout ─────────────────────────────────────────────
    public function logout()
    {
        $this->logActivity('logout', 'auth');
        session()->destroy();
        return redirect()->route('auth.login')->with('success', 'You have been logged out.');
    }

    // ── GET /auth/forgot-password ────────────────────────────────────
    public function forgotPassword(): string
    {
        return view('auth/forgot_password');
    }

    // ── POST /auth/forgot-password ───────────────────────────────────
    public function forgotProcess()
    {
        $email = $this->request->getPost('email');
        $user  = $this->userModel->where('email', $email)->first();

        // Always show the same message to prevent user enumeration
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->userModel->update($user['id'], [
                'reset_token'         => hash('sha256', $token),
                'reset_token_expires' => $expires,
            ]);

            // TODO: Send email with reset link
            // Email::send('auth/reset_email', compact('user', 'token'));
        }

        return redirect()->back()
                         ->with('success', 'If that email exists, a reset link has been sent.');
    }

    // ── GET /auth/reset-password/:token ─────────────────────────────
    public function resetPassword(string $token): string
    {
        return view('auth/reset_password', compact('token'));
    }

    // ── POST /auth/reset-password ────────────────────────────────────
    public function resetProcess()
    {
        $rules = [
            'token'    => 'required',
            'password' => 'required|min_length[8]',
            'confirm'  => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $user  = $this->userModel
            ->where('reset_token', hash('sha256', $token))
            ->where('reset_token_expires >', date('Y-m-d H:i:s'))
            ->first();

        if (! $user) {
            return redirect()->route('auth.forgot')
                             ->with('error', 'Invalid or expired reset link.');
        }

        $this->userModel->update($user['id'], [
            'password_hash'       => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'reset_token'         => null,
            'reset_token_expires' => null,
        ]);

        return redirect()->route('auth.login')
                         ->with('success', 'Password updated. Please log in.');
    }
}
