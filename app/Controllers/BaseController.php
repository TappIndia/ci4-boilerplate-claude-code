<?php

// app/Controllers/BaseController.php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\ActivityLogModel;
use App\Models\SettingModel;

class BaseController extends Controller
{
    protected IncomingRequest|CLIRequest $request;

    protected array $helpers = ['url', 'form', 'session', 'text', 'filesystem'];

    // ------------------------------------------------------------------
    // Shared services / models (lazy-loaded)
    // ------------------------------------------------------------------
    protected ?SettingModel $settingModel = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->settingModel = model(SettingModel::class);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Return currently logged-in user data from session. */
    protected function currentUser(): ?array
    {
        return session()->get('user') ?? null;
    }

    protected function currentUserId(): ?int
    {
        return session()->get('user_id') ?? null;
    }

    /** Check if the logged-in user has a given permission. */
    protected function can(string $permission): bool
    {
        $permissions = session()->get('user_permissions') ?? [];
        $roles       = session()->get('user_roles')       ?? [];

        return in_array('super_admin', $roles)
            || in_array($permission, $permissions);
    }

    /** Abort with 403 if permission is missing. */
    protected function authorize(string $permission): void
    {
        if (! $this->can($permission)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'You are not authorized to perform this action.'
            );
        }
    }

    /** Shorthand: redirect back with flash error. */
    protected function denied(string $msg = 'Access denied.'): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->back()->with('error', $msg);
    }

    /** Log an action to activity_logs. */
    protected function logActivity(string $action, string $module, ?string $description = null, array $extra = []): void
    {
        try {
            model(ActivityLogModel::class)->insert([
                'user_id'     => $this->currentUserId(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => substr($this->request->getUserAgent()->getAgentString(), 0, 255),
                'url'         => (string) $this->request->getUri(),
                'method'      => strtoupper($this->request->getMethod()),
                'extra'       => $extra ? json_encode($extra) : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'logActivity failed: ' . $e->getMessage());
        }
    }

    /** Get a setting value by key. */
    protected function setting(string $key, mixed $default = null): mixed
    {
        return $this->settingModel?->getValue($key) ?? $default;
    }

    /** Standard pagination helper — returns ['data'=>[], 'pager'=>Pager]. */
    protected function paginate(\CodeIgniter\Model $model, int $perPage = 15): array
    {
        $perPage = (int) ($this->request->getGet('per_page') ?? $perPage);
        $data    = $model->paginate($perPage);

        return ['data' => $data, 'pager' => $model->pager];
    }
}
