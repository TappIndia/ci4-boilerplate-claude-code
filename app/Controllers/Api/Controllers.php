<?php

// app/Controllers/Api/ApiBaseController.php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ApiBaseController
 * JSON-only responses, no view rendering.
 */
class ApiBaseController extends BaseController
{
    protected function respond(mixed $data, int $status = 200, string $message = 'success'): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'status'  => $status < 400 ? 'success' : 'error',
                'message' => $message,
                'data'    => $data,
            ]);
    }

    protected function respondCreated(mixed $data, string $message = 'Resource created'): ResponseInterface
    {
        return $this->respond($data, 201, $message);
    }

    protected function respondError(string $message, int $status = 400, mixed $errors = null): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
                'errors'  => $errors,
            ]);
    }

    protected function respondNotFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->respondError($message, 404);
    }

    protected function respondPaginated(array $data, object $pager, int $total): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'current_page' => $pager->getCurrentPage(),
                'per_page'     => $pager->getPerPage(),
                'total'        => $total,
                'last_page'    => $pager->getPageCount(),
            ],
        ]);
    }

    protected function apiUser(): ?array
    {
        return $GLOBALS['api_user'] ?? null;
    }
}


// ──────────────────────────────────────────────────────────────────────────────
// app/Controllers/Api/UserController.php
// ──────────────────────────────────────────────────────────────────────────────

namespace App\Controllers\Api;

use App\Models\UserModel;

class UserController extends ApiBaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    // GET /api/v1/users
    public function index()
    {
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);

        $this->userModel->where('deleted_at', null)
                        ->select('id,uuid,first_name,last_name,email,status,created_at')
                        ->orderBy('created_at', 'DESC');

        $users = $this->userModel->paginate($perPage);
        $pager = $this->userModel->pager;
        $total = $this->userModel->countAllResults(false);

        return $this->respondPaginated($users, $pager, $total);
    }

    // GET /api/v1/users/:id
    public function show(int $id)
    {
        $user = $this->userModel
            ->select('id,uuid,first_name,last_name,email,status,created_at')
            ->where('deleted_at', null)
            ->find($id);

        if (! $user) return $this->respondNotFound();

        return $this->respond($user);
    }

    // POST /api/v1/users
    public function store()
    {
        $rules = [
            'first_name' => 'required|max_length[80]',
            'last_name'  => 'required|max_length[80]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[8]',
        ];

        if (! $this->validate($rules)) {
            return $this->respondError('Validation failed', 422, $this->validator->getErrors());
        }

        $body   = $this->request->getJSON(true);
        $userId = $this->userModel->insert([
            'uuid'          => service('uuid')->uuid4()->toString(),
            'first_name'    => $body['first_name'],
            'last_name'     => $body['last_name'],
            'email'         => $body['email'],
            'password_hash' => password_hash($body['password'], PASSWORD_DEFAULT),
            'status'        => 'active',
        ]);

        $user = $this->userModel->select('id,uuid,first_name,last_name,email,status')->find($userId);
        return $this->respondCreated($user);
    }

    // PUT /api/v1/users/:id
    public function update(int $id)
    {
        $user = $this->userModel->where('deleted_at', null)->find($id);
        if (! $user) return $this->respondNotFound();

        $body  = $this->request->getJSON(true);
        $rules = [
            'email' => "permit_empty|valid_email|is_unique[users.email,id,{$id}]",
        ];

        if (! $this->validate($rules)) {
            return $this->respondError('Validation failed', 422, $this->validator->getErrors());
        }

        $allowed = ['first_name', 'last_name', 'email', 'phone', 'status'];
        $update  = array_intersect_key($body, array_flip($allowed));

        $this->userModel->update($id, $update);

        return $this->respond($this->userModel->find($id), message: 'User updated');
    }

    // DELETE /api/v1/users/:id
    public function delete(int $id)
    {
        $user = $this->userModel->where('deleted_at', null)->find($id);
        if (! $user) return $this->respondNotFound();

        $this->userModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->respond(null, 200, 'User deleted');
    }
}


// ──────────────────────────────────────────────────────────────────────────────
// app/Controllers/Api/AuthController.php
// ──────────────────────────────────────────────────────────────────────────────

namespace App\Controllers\Api;

use App\Models\UserModel;

class AuthController extends ApiBaseController
{
    public function login()
    {
        $body  = $this->request->getJSON(true);
        $email = $body['email']    ?? '';
        $pass  = $body['password'] ?? '';

        $userModel = model(UserModel::class);
        $user      = $userModel->where('email', $email)
                               ->where('status', 'active')
                               ->where('deleted_at', null)
                               ->first();

        if (! $user || ! password_verify($pass, $user['password_hash'])) {
            return $this->respondError('Invalid credentials', 401);
        }

        // Generate token
        $rawToken = bin2hex(random_bytes(40));
        $userModel->update($user['id'], [
            'remember_token' => hash('sha256', $rawToken),
            'last_login_at'  => date('Y-m-d H:i:s'),
            'last_login_ip'  => $this->request->getIPAddress(),
        ]);

        return $this->respond([
            'token' => $rawToken,
            'user'  => [
                'id'         => $user['id'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
            ],
        ]);
    }

    public function logout()
    {
        $apiUser = $this->apiUser();
        if ($apiUser) {
            model(UserModel::class)->update($apiUser['id'], ['remember_token' => null]);
        }
        return $this->respond(null, 200, 'Logged out');
    }
}
