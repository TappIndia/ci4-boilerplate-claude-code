<?php
// app/Views/admin/settings/index.php
$page_title    = 'Settings';
$page_subtitle = 'Configure your application settings';
?>
<?php $this->extend('layouts/admin_layout') ?>
<?php $this->section('content') ?>

<form method="post" action="<?= base_url('admin/settings/save') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Tab Nav -->
    <ul class="nav nav-tabs mb-4" id="settingTabs" role="tablist">
        <?php $first = true; ?>
        <?php foreach ($groups as $groupName => $settings): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $first ? 'active' : '' ?>"
                        id="tab-<?= $groupName ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-<?= $groupName ?>"
                        type="button" role="tab">
                    <?php $icons = ['general'=>'bi-sliders','email'=>'bi-envelope','security'=>'bi-shield-lock'] ?>
                    <i class="bi <?= $icons[$groupName] ?? 'bi-gear' ?> me-1"></i>
                    <?= ucfirst($groupName) ?>
                </button>
            </li>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </ul>

    <!-- Tab Panes -->
    <div class="tab-content">
        <?php $first = true; ?>
        <?php foreach ($groups as $groupName => $settings): ?>
            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>"
                 id="pane-<?= $groupName ?>" role="tabpanel">
                <div class="card">
                    <div class="card-header"><?= ucfirst($groupName) ?> Settings</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($settings as $s): ?>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?= esc($s['label']) ?>
                                        <?php if ($s['description']): ?>
                                            <span class="text-muted small ms-1" title="<?= esc($s['description']) ?>">
                                                <i class="bi bi-info-circle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </label>

                                    <?php if ($s['type'] === 'boolean'): ?>
                                        <div class="form-check form-switch mt-1">
                                            <input type="hidden" name="<?= esc($s['key']) ?>" value="0">
                                            <input class="form-check-input" type="checkbox"
                                                   name="<?= esc($s['key']) ?>"
                                                   value="1"
                                                   <?= $s['value'] ? 'checked' : '' ?>>
                                        </div>

                                    <?php elseif ($s['type'] === 'password'): ?>
                                        <div class="input-group">
                                            <input type="password"
                                                   name="<?= esc($s['key']) ?>"
                                                   class="form-control"
                                                   value="<?= esc($s['value']) ?>">
                                            <button type="button" class="btn btn-outline-secondary toggle-password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                    <?php elseif ($s['type'] === 'number'): ?>
                                        <input type="number"
                                               name="<?= esc($s['key']) ?>"
                                               class="form-control"
                                               value="<?= esc($s['value']) ?>">

                                    <?php elseif ($s['type'] === 'file'): ?>
                                        <input type="file"
                                               name="<?= esc($s['key']) ?>"
                                               class="form-control"
                                               accept="image/*">
                                        <?php if ($s['value']): ?>
                                            <div class="mt-2">
                                                <img src="<?= base_url($s['value']) ?>" height="36" alt="Current" class="rounded border">
                                                <span class="text-muted small ms-2">Current file</span>
                                            </div>
                                        <?php endif; ?>

                                    <?php elseif ($s['type'] === 'json'): ?>
                                        <textarea name="<?= esc($s['key']) ?>"
                                                  class="form-control font-mono"
                                                  rows="4"><?= esc($s['value']) ?></textarea>

                                    <?php else: ?>
                                        <input type="text"
                                               name="<?= esc($s['key']) ?>"
                                               class="form-control"
                                               value="<?= esc($s['value']) ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

    <!-- Save Button -->
    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i>Save Settings
        </button>
    </div>
</form>

<script>
// Toggle password visibility in settings
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
});
</script>

<?php $this->endSection() ?>
