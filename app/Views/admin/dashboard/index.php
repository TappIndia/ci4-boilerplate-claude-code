<?php
// app/Views/admin/dashboard/index.php
$page_title    = 'Dashboard';
$page_subtitle = 'Welcome back, ' . esc($current_user['first_name'] ?? 'User');
?>
<?php $this->extend('layouts/admin_layout') ?>
<?php $this->section('content') ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#667eea,#764ba2)">
            <div class="stat-card-icon" style="background:rgba(255,255,255,.18)">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-card-value"><?= esc($stats['total_users'] ?? 0) ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
            <div class="stat-card-icon" style="background:rgba(255,255,255,.18)">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="stat-card-value"><?= esc($stats['total_roles'] ?? 0) ?></div>
                <div class="stat-card-label">Roles</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
            <div class="stat-card-icon" style="background:rgba(255,255,255,.18)">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div>
                <div class="stat-card-value"><?= esc($stats['total_files'] ?? 0) ?></div>
                <div class="stat-card-label">Files</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#43e97b,#38f9d7)">
            <div class="stat-card-icon" style="background:rgba(255,255,255,.18)">
                <i class="bi bi-activity"></i>
            </div>
            <div>
                <div class="stat-card-value"><?= esc($stats['today_logins'] ?? 0) ?></div>
                <div class="stat-card-label">Logins Today</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Recent Users -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-people me-2"></i>Recent Users</span>
                <a href="<?= route_to('admin.users.index') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_users)): ?>
                                <?php foreach ($recent_users as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                     style="width:30px;height:30px;font-size:.75rem;background:#667eea">
                                                    <?= strtoupper(substr($u['first_name'], 0, 1)) ?>
                                                </div>
                                                <?= esc($u['first_name'] . ' ' . $u['last_name']) ?>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= esc($u['email']) ?></td>
                                        <td>
                                            <?php $sc = ['active'=>'success','inactive'=>'secondary','banned'=>'danger','pending'=>'warning'] ?>
                                            <span class="badge bg-<?= $sc[$u['status']] ?? 'secondary' ?>">
                                                <?= ucfirst($u['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No users yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-journal-text me-2"></i>Recent Activity</span>
                <a href="<?= route_to('admin.logs.index') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $log): ?>
                            <div class="list-group-item border-0 px-3 py-2">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="mt-1">
                                        <span class="badge rounded-pill bg-primary-subtle text-primary" style="font-size:.7rem">
                                            <?= esc($log['module']) ?>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="small fw-medium text-truncate"><?= esc($log['action']) ?></div>
                                        <div class="text-muted" style="font-size:.72rem"><?= esc($log['ip_address']) ?> · <?= date('M d H:i', strtotime($log['created_at'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4 small">No activity yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection() ?>
