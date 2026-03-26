<?php

// app/Filters/AuthFilter.php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 * Protects routes that require an authenticated session.
 * Pass a role argument to restrict to specific roles:
 *   'filter' => 'auth:admin'
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Not logged in → redirect to login
        if (! $session->has('user_id')) {
            return redirect()->route('auth.login')
                             ->with('error', 'Please log in to continue.');
        }

        // Role check
        if ($arguments) {
            $requiredRole = $arguments[0];
            $userRoles    = $session->get('user_roles') ?? [];

            if (! in_array($requiredRole, $userRoles) && ! in_array('super_admin', $userRoles)) {
                return redirect()->back()
                                 ->with('error', 'You do not have permission to access this page.');
            }
        }

        return null; // Allow request to continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
