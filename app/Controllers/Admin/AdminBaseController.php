<?php

// app/Controllers/Admin/AdminBaseController.php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\NotificationModel;

/**
 * AdminBaseController
 * All Admin panel controllers extend this.
 * Injects sidebar, settings, and notification data into every view.
 */
class AdminBaseController extends BaseController
{
    protected array $viewData = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Global view data available in every admin view
        $this->viewData = [
            'site_name'     => $this->setting('site_name', 'CI4 Admin'),
            'site_logo'     => $this->setting('site_logo'),
            'current_user'  => $this->currentUser(),
            'menu_items'    => $this->buildSidebarMenu(),
            'unread_count'  => $this->unreadNotifications(),
        ];
    }

    // ------------------------------------------------------------------
    // Render an admin view — automatically wraps in the admin layout
    // ------------------------------------------------------------------
    protected function render(string $view, array $data = []): string
    {
        $data = array_merge($this->viewData, $data);
        return view($view, $data);
    }

    // ------------------------------------------------------------------
    // Build sidebar menu from DB for the logged-in user
    // ------------------------------------------------------------------
    private function buildSidebarMenu(): array
    {
        $menuModel = model(MenuModel::class);
        return $menuModel->getSidebarMenu(
            'admin_sidebar',
            session()->get('user_permissions') ?? [],
            session()->get('user_roles') ?? []
        );
    }

    private function unreadNotifications(): int
    {
        $userId = $this->currentUserId();
        if (! $userId) return 0;
        return model(NotificationModel::class)
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }
}
