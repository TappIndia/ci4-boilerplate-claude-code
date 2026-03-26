<?php

// app/Controllers/Admin/DashboardController.php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ActivityLogModel;
use App\Models\FileModel;

class DashboardController extends AdminBaseController
{
    public function index(): string
    {
        $db = db_connect();

        $stats = [
            'total_users'  => model(UserModel::class)->where('deleted_at', null)->countAllResults(),
            'total_roles'  => model(RoleModel::class)->where('deleted_at', null)->countAllResults(),
            'total_files'  => model(FileModel::class)->where('deleted_at', null)->countAllResults(),
            'today_logins' => model(ActivityLogModel::class)
                ->where('action', 'login_success')
                ->where('DATE(created_at)', date('Y-m-d'))
                ->countAllResults(),
        ];

        $recentUsers = model(UserModel::class)
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->findAll();

        $recentActivity = model(ActivityLogModel::class)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();

        return $this->render('admin/dashboard/index', [
            'page_title'      => 'Dashboard',
            'stats'           => $stats,
            'recent_users'    => $recentUsers,
            'recent_activity' => $recentActivity,
        ]);
    }
}
