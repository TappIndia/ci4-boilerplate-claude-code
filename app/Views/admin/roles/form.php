<?php
// app/Views/admin/roles/form.php
$isEdit     = !empty($role);
$formAction = $isEdit ? base_url("admin/roles/{$role['id']}/update") : base_url('admin/roles/store');
$page_actions = '<a href="' . route_to('admin.roles.index') . '" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Back
</a>';
?>
<?php $this->extend('layouts/admin_layout') ?>
<?php $this->section('content') ?>

<form method="post" action="<?= $formAction ?>">
    <?= csrf_field() ?>

    <div class="row g-4">

        <!-- Left: Role Details -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-shield-lock me-2"></i>Role Details</div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span>
                            <span class="text-muted small">(slug, e.g. content_editor)</span>
                        </label>
                        <input type="text" name="name" class="form-control font-mono"
                               value="<?= old('name', $role['name'] ?? '') ?>"
                               placeholder="my_role" required>
                        <div class="form-text">Lowercase letters, numbers, underscores only.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control"
                               value="<?= old('label', $role['label'] ?? '') ?>"
                               placeholder="My Role" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="What can this role do?"><?= old('description', $role['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               <?= old('is_active', $role['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>
                        <?= $isEdit ? 'Update Role' : 'Create Role' ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Permissions Matrix -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-key me-2"></i>Permissions</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="checkAll">
                            <i class="bi bi-check2-all me-1"></i>All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheckAll">
                            <i class="bi bi-x-lg me-1"></i>None
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($permsByModule)): ?>
                        <p class="text-muted text-center py-4">No permissions defined yet.</p>
                    <?php else: ?>
                        <?php foreach ($permsByModule as $module => $perms): ?>
                            <div class="mb-4">
                                <!-- Module header -->
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary text-uppercase" style="font-size:.72rem;letter-spacing:.05em">
                                        <?= esc($module) ?>
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-muted check-module" data-module="<?= esc($module) ?>">
                                        Select all
                                    </button>
                                </div>

                                <!-- Permission checkboxes -->
                                <div class="row g-2">
                                    <?php foreach ($perms as $p): ?>
                                        <div class="col-sm-6 col-xl-3">
                                            <label class="d-flex align-items-center gap-2 p-2 border rounded-2 cursor-pointer perm-item" style="font-size:.82rem">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="<?= $p['id'] ?>"
                                                       class="form-check-input perm-checkbox mb-0 flex-shrink-0"
                                                       data-module="<?= esc($module) ?>"
                                                       <?= in_array($p['id'], $rolePerms ?? []) ? 'checked' : '' ?>>
                                                <span>
                                                    <span class="d-block fw-medium"><?= esc(ucfirst($p['action'])) ?></span>
                                                    <span class="text-muted" style="font-size:.72rem"><?= esc($p['name']) ?></span>
                                                </span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Highlight checked permission cards
function updateCard(cb) {
    const card = cb.closest('.perm-item');
    card.classList.toggle('border-primary', cb.checked);
    card.classList.toggle('bg-primary-subtle', cb.checked);
}

document.querySelectorAll('.perm-checkbox').forEach(cb => {
    updateCard(cb);
    cb.addEventListener('change', () => updateCard(cb));
});

// Select all / none
document.getElementById('checkAll')?.addEventListener('click', () => {
    document.querySelectorAll('.perm-checkbox').forEach(cb => { cb.checked = true; updateCard(cb); });
});
document.getElementById('uncheckAll')?.addEventListener('click', () => {
    document.querySelectorAll('.perm-checkbox').forEach(cb => { cb.checked = false; updateCard(cb); });
});

// Select all per module
document.querySelectorAll('.check-module').forEach(btn => {
    btn.addEventListener('click', () => {
        const mod = btn.dataset.module;
        const cbs = document.querySelectorAll(`.perm-checkbox[data-module="${mod}"]`);
        const allChecked = [...cbs].every(c => c.checked);
        cbs.forEach(cb => { cb.checked = !allChecked; updateCard(cb); });
        btn.textContent = allChecked ? 'Select all' : 'Deselect all';
    });
});
</script>

<?php $this->endSection() ?>
