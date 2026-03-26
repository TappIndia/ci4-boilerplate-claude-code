<?php
// app/Views/admin/users/form.php
$isEdit     = !empty($user);
$formAction = $isEdit
    ? base_url("admin/users/{$user['id']}/update")
    : base_url('admin/users/store');
$page_title = $isEdit ? 'Edit User' : 'Create User';
$page_actions = '<a href="' . route_to('admin.users.index') . '" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Back
</a>';
?>
<?php $this->extend('layouts/admin_layout') ?>
<?php $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <i class="bi <?= $isEdit ? 'bi-pencil-square' : 'bi-person-plus' ?> me-2"></i>
                <?= $page_title ?>
            </div>
            <div class="card-body">
                <form method="post" action="<?= $formAction ?>" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control <?= session('errors.first_name') ? 'is-invalid' : '' ?>"
                                   value="<?= old('first_name', $user['first_name'] ?? '') ?>" required>
                            <?php if (session('errors.first_name')): ?>
                                <div class="invalid-feedback"><?= session('errors.first_name') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control <?= session('errors.last_name') ? 'is-invalid' : '' ?>"
                                   value="<?= old('last_name', $user['last_name'] ?? '') ?>" required>
                            <?php if (session('errors.last_name')): ?>
                                <div class="invalid-feedback"><?= session('errors.last_name') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                                   value="<?= old('email', $user['email'] ?? '') ?>" required>
                            <?php if (session('errors.email')): ?>
                                <div class="invalid-feedback"><?= session('errors.email') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= old('phone', $user['phone'] ?? '') ?>">
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Password <?= $isEdit ? '<span class="text-muted small">(leave blank to keep)</span>' : '<span class="text-danger">*</span>' ?>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField"
                                       class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                                       <?= !$isEdit ? 'required' : '' ?> minlength="8">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                                <?php if (session('errors.password')): ?>
                                    <div class="invalid-feedback"><?= session('errors.password') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <?php foreach (['active','inactive','banned','pending'] as $s): ?>
                                    <option value="<?= $s ?>" <?= old('status', $user['status'] ?? 'active') === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Role -->
                        <div class="col-md-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select <?= session('errors.role_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">Select role…</option>
                                <?php foreach ($roles ?? [] as $role): ?>
                                    <option value="<?= $role['id'] ?>"
                                        <?= (in_array($role['id'], $userRoles ?? []) || old('role_id') == $role['id']) ? 'selected' : '' ?>>
                                        <?= esc($role['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.role_id')): ?>
                                <div class="invalid-feedback"><?= session('errors.role_id') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit -->
                        <div class="col-12 d-flex gap-2 pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                <?= $isEdit ? 'Update User' : 'Create User' ?>
                            </button>
                            <a href="<?= route_to('admin.users.index') ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const field = document.getElementById('passwordField');
    const icon  = document.getElementById('togglePasswordIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>

<?php $this->endSection() ?>
