<?php

// app/Filters/ActivityLogFilter.php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ActivityLogModel;

/**
 * ActivityLogFilter
 * Records every admin/api request to activity_logs.
 */
class ActivityLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null; // Nothing to do before
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only log modifying operations
        $method = strtoupper($request->getMethod());
        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return null;
        }

        try {
            $session = session();
            $uri     = $request->getUri()->getPath();
            $segment = explode('/', trim($uri, '/'));
            $module  = $segment[1] ?? 'unknown';   // admin/{module}/...
            $action  = $method . ':' . $uri;

            model(ActivityLogModel::class)->insert([
                'user_id'    => $session->get('user_id'),
                'session_id' => $session->session_id ?? null,
                'action'     => $action,
                'module'     => $module,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => substr($request->getUserAgent()->getAgentString(), 0, 255),
                'url'        => (string) $request->getUri(),
                'method'     => $method,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogFilter: ' . $e->getMessage());
        }

        return null;
    }
}
