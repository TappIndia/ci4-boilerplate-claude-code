<?php
// app/Views/admin/users/index.php
$page_title   = 'Users';
$page_actions = '<a href="' . route_to('admin.users.create') . '" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Add User
</a>';
?>
<?php $this->extend('layouts/admin_layout') ?>
<?php $this->section('content') ?>

<div class="card">
    <!-- Search Bar -->
    <div class="card-header">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0 ps-0"
                           placeholder="Search name, email…"
                           value="<?= esc($search ?? '') ?>"
                           data-search-form>
                </div>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active"   <?= ($filter_status ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filter_status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="banned"   <?= ($filter_status ?? '') === 'banned'   ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>
            <?php if (!empty($search) || !empty($filter_status)): ?>
                <div class="col-auto">
                    <a href="<?= route_to('admin.users.index') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x me-1"></i>Clear
                    </a>
                </div>
            <?php endif; ?>
            <div class="col-auto ms-auto text-muted small">
                <?= count($users ?? []) ?> record(s) found
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th style="width:130px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted small"><?= $u['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($u['avatar']): ?>
                                        <img src="<?= base_url($u['avatar']) ?>" class="rounded-circle" width="30" height="30" alt="">
                                    <?php else: ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                             style="width:30px;height:30px;font-size:.75rem;background:#667eea">
                                            <?= strtoupper(substr($u['first_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></span>
                                </div>
                            </td>
                            <td><?= esc($u['email']) ?></td>
                            <td>
                                <?php foreach ($u['roles'] ?? [] as $role): ?>
                                    <span class="badge bg-secondary-subtle text-secondary me-1"><?= esc($role) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php $sc = ['active'=>'success','inactive'=>'secondary','banned'=>'danger','pending'=>'warning'] ?>
                                <span class="badge bg-<?= $sc[$u['status']] ?? 'secondary' ?>-subtle text-<?= $sc[$u['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= $u['last_login_at'] ? date('M d, H:i', strtotime($u['last_login_at'])) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= route_to('admin.users.show', $u['id']) ?>"
                                       class="btn btn-action btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= route_to('admin.users.edit', $u['id']) ?>"
                                       class="btn btn-action btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($u['id'] !== ($current_user['id'] ?? 0)): ?>
                                        <form method="post" action="<?= base_url("admin/users/{$u['id']}/delete") ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="btn btn-action btn-outline-danger"
                                                    data-confirm="Delete user <?= esc($u['first_name']) ?>?"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                            No users found.
                            <a href="<?= route_to('admin.users.create') ?>">Add the first one</a>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($pager)): ?>
        <div class="card-footer bg-transparent d-flex justify-content-center py-3">
            <?= $pager->links('default', 'bootstrap_5_full') ?>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection() ?>
