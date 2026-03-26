<?php
/**
 * app/Views/components/crud_form.php
 * ─────────────────────────────────────────────────────────────
 * Renders a form from a field definition array.
 *
 * Usage:
 *   <?= view('components/crud_form', [
 *       'action'  => base_url('admin/products/store'),
 *       'record'  => $record ?? null,  // null = create mode
 *       'fields'  => [
 *           ['name'=>'name',        'label'=>'Product Name', 'type'=>'text',     'required'=>true,  'col'=>6],
 *           ['name'=>'sku',         'label'=>'SKU',          'type'=>'text',     'col'=>6],
 *           ['name'=>'price',       'label'=>'Price',        'type'=>'number',   'required'=>true,  'col'=>4, 'step'=>'0.01'],
 *           ['name'=>'stock',       'label'=>'Stock',        'type'=>'number',   'col'=>4],
 *           ['name'=>'status',      'label'=>'Status',       'type'=>'select',   'col'=>4,
 *               'options' => ['active'=>'Active','inactive'=>'Inactive']],
 *           ['name'=>'description', 'label'=>'Description',  'type'=>'textarea', 'rows'=>4],
 *           ['name'=>'is_featured', 'label'=>'Featured',     'type'=>'switch'],
 *           ['name'=>'image',       'label'=>'Image',        'type'=>'file',     'accept'=>'image/*'],
 *           ['name'=>'tags',        'label'=>'Tags',         'type'=>'tags'],
 *       ],
 *       'cancelUrl' => route_to('admin.products.index'),
 *       'submitLabel' => 'Save Product',
 *   ]) ?>
 */

$action      = $action      ?? '#';
$record      = $record      ?? null;
$fields      = $fields      ?? [];
$cancelUrl   = $cancelUrl   ?? null;
$submitLabel = $submitLabel ?? ($record ? 'Update' : 'Create');
$enctype     = '';

// Auto-detect file upload
foreach ($fields as $f) {
    if (in_array($f['type'] ?? 'text', ['file', 'image'])) {
        $enctype = 'enctype="multipart/form-data"';
        break;
    }
}
?>

<form method="post" action="<?= esc($action) ?>" <?= $enctype ?> autocomplete="off">
    <?= csrf_field() ?>

    <div class="row g-3">
        <?php foreach ($fields as $field): ?>
            <?php
            $name     = $field['name']     ?? '';
            $label    = $field['label']    ?? ucfirst($name);
            $type     = $field['type']     ?? 'text';
            $required = $field['required'] ?? false;
            $col      = $field['col']      ?? 12;
            $colClass = "col-md-{$col}";
            $value    = old($name, $record[$name] ?? $field['default'] ?? '');
            $error    = session("errors.{$name}");
            $helpText = $field['help']     ?? '';
            $attrs    = '';

            if ($required)          $attrs .= ' required';
            if ($field['min']  ?? false) $attrs .= ' min="'  . esc($field['min'])  . '"';
            if ($field['max']  ?? false) $attrs .= ' max="'  . esc($field['max'])  . '"';
            if ($field['step'] ?? false) $attrs .= ' step="' . esc($field['step']) . '"';
            if ($field['placeholder'] ?? false) $attrs .= ' placeholder="' . esc($field['placeholder']) . '"';
            ?>

            <?php if ($type === 'hidden'): ?>
                <input type="hidden" name="<?= esc($name) ?>" value="<?= esc($value) ?>">
                <?php continue; ?>
            <?php endif; ?>

            <div class="<?= $colClass ?>">
                <?php if ($type !== 'switch'): ?>
                    <label class="form-label">
                        <?= esc($label) ?>
                        <?= $required ? '<span class="text-danger">*</span>' : '' ?>
                    </label>
                <?php endif; ?>

                <?php switch ($type):
                    case 'textarea': ?>
                        <textarea name="<?= esc($name) ?>"
                                  class="form-control <?= $error ? 'is-invalid' : '' ?>"
                                  rows="<?= $field['rows'] ?? 3 ?>"
                                  <?= $attrs ?>><?= esc($value) ?></textarea>
                    <?php break; ?>

                    <?php case 'select': ?>
                        <select name="<?= esc($name) ?>"
                                class="form-select <?= $error ? 'is-invalid' : '' ?>"
                                <?= $attrs ?>>
                            <?php if ($field['placeholder'] ?? false): ?>
                                <option value=""><?= esc($field['placeholder']) ?></option>
                            <?php endif; ?>
                            <?php foreach (($field['options'] ?? []) as $optVal => $optLabel): ?>
                                <option value="<?= esc($optVal) ?>" <?= $value == $optVal ? 'selected' : '' ?>>
                                    <?= esc($optLabel) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php break; ?>

                    <?php case 'switch': ?>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="<?= esc($name) ?>" value="0">
                            <input class="form-check-input" type="checkbox"
                                   name="<?= esc($name) ?>"
                                   value="1"
                                   id="switch_<?= esc($name) ?>"
                                   <?= $value ? 'checked' : '' ?>>
                            <label class="form-check-label" for="switch_<?= esc($name) ?>">
                                <?= esc($label) ?>
                            </label>
                        </div>
                    <?php break; ?>

                    <?php case 'file':
                    case 'image': ?>
                        <input type="file"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               accept="<?= esc($field['accept'] ?? ($type === 'image' ? 'image/*' : '*')) ?>">
                        <?php if ($value): ?>
                            <div class="mt-2">
                                <?php if ($type === 'image'): ?>
                                    <img src="<?= base_url($value) ?>" height="50" class="rounded border" alt="">
                                <?php else: ?>
                                    <a href="<?= base_url($value) ?>" target="_blank" class="small text-muted">
                                        <i class="bi bi-paperclip me-1"></i>Current file
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php break; ?>

                    <?php case 'date': ?>
                        <input type="date"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               value="<?= esc($value ? date('Y-m-d', strtotime($value)) : '') ?>"
                               <?= $attrs ?>>
                    <?php break; ?>

                    <?php case 'datetime': ?>
                        <input type="datetime-local"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               value="<?= esc($value ? date('Y-m-d\TH:i', strtotime($value)) : '') ?>"
                               <?= $attrs ?>>
                    <?php break; ?>

                    <?php case 'number': ?>
                        <input type="number"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               value="<?= esc($value) ?>"
                               <?= $attrs ?>>
                    <?php break; ?>

                    <?php case 'email': ?>
                        <input type="email"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               value="<?= esc($value) ?>"
                               <?= $attrs ?>>
                    <?php break; ?>

                    <?php case 'password': ?>
                        <div class="input-group">
                            <input type="password"
                                   name="<?= esc($name) ?>"
                                   id="pw_<?= esc($name) ?>"
                                   class="form-control <?= $error ? 'is-invalid' : '' ?>"
                                   <?= $attrs ?>>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="var f=document.getElementById('pw_<?= esc($name) ?>');f.type=f.type==='password'?'text':'password'">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    <?php break; ?>

                    <?php default: // text, etc. ?>
                        <input type="<?= esc($type) ?>"
                               name="<?= esc($name) ?>"
                               class="form-control <?= $error ? 'is-invalid' : '' ?>"
                               value="<?= esc($value) ?>"
                               <?= $attrs ?>>
                    <?php break; ?>

                <?php endswitch; ?>

                <?php if ($error): ?>
                    <div class="invalid-feedback d-block"><?= esc($error) ?></div>
                <?php elseif ($helpText): ?>
                    <div class="form-text"><?= esc($helpText) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Submit Row -->
        <div class="col-12 d-flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i><?= esc($submitLabel) ?>
            </button>
            <?php if ($cancelUrl): ?>
                <a href="<?= esc($cancelUrl) ?>" class="btn btn-outline-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </div>
</form>
