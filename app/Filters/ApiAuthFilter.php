<?php

// app/Filters/ApiAuthFilter.php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

/**
 * ApiAuthFilter
 * Validates Bearer token on every API request.
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow unauthenticated access to /api/v1/auth/login
        $uri = $request->getUri()->getPath();
        if (str_contains($uri, 'auth/login')) {
            return null;
        }

        $token = $this->extractToken($request);

        if (! $token) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Token missing'])
                ->setContentType('application/json');
        }

        $userModel = model(UserModel::class);
        $user      = $userModel->where('remember_token', hash('sha256', $token))
                               ->where('status', 'active')
                               ->first();

        if (! $user) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Invalid or expired token'])
                ->setContentType('application/json');
        }

        // Attach user to request globals
        $GLOBALS['api_user'] = $user;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function extractToken(RequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header && preg_match('/^Bearer\s+(\S+)$/', $header, $m)) {
            return $m[1];
        }
        return null;
    }
}
