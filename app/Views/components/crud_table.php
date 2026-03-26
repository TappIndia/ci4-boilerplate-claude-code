{{--
  app/Views/components/crud_table.php
  ─────────────────────────────────────────────────────────────
  Reusable data table component.

  Usage from a view:
    <?= view('components/crud_table', [
        'columns'    => [
            ['label' => 'ID',    'key' => 'id',    'width' => '40px'],
            ['label' => 'Name',  'key' => 'name'],
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'Status','key' => 'status', 'badge' => [
                'active'=>'success','inactive'=>'secondary'
            ]],
        ],
        'rows'       => $rows,
        'pager'      => $pager,
        'routeEdit'  => 'admin.users.edit',
        'routeDel'   => fn($r) => base_url("admin/users/{$r['id']}/delete"),
        'routeView'  => 'admin.users.show',   // optional
        'search'     => $search ?? '',
        'searchUrl'  => current_url(),
    ]) ?>
--}}
<?php
// app/Views/components/crud_table.php

$columns   = $columns   ?? [];
$rows      = $rows      ?? [];
$pager     = $pager     ?? null;
$routeEdit = $routeEdit ?? null;
$routeDel  = $routeDel  ?? null;
$routeView = $routeView ?? null;
$search    = $search    ?? '';
$searchUrl = $searchUrl ?? current_url();
?>

<div class="card">
    <!-- Search -->
    <div class="card-header">
        <form method="get" action="<?= esc($searchUrl) ?>" class="d-flex gap-2 align-items-center">
            <div class="input-group input-group-sm" style="max-width:300px">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0"
                       placeholder="Search…"
                       value="<?= esc($search) ?>"
                       data-search-form>
            </div>
            <?php if ($search): ?>
                <a href="<?= esc($searchUrl) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i> Clear
                </a>
            <?php endif; ?>
            <span class="text-muted small ms-auto"><?= count($rows) ?> record(s)</span>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th <?= !empty($col['width']) ? "style=\"width:{$col['width']}\"" : '' ?>>
                            <?= esc($col['label']) ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if ($routeEdit || $routeDel || $routeView): ?>
                        <th style="width:110px">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php
                                    $val = $row[$col['key']] ?? '—';

                                    if (!empty($col['badge']) && is_array($col['badge'])) {
                                        $color = $col['badge'][$val] ?? 'secondary';
                                        echo "<span class=\"badge bg-{$color}-subtle text-{$color}\">" . esc(ucfirst((string) $val)) . "</span>";
                                    } elseif (!empty($col['date'])) {
                                        echo $val ? '<span class="text-muted small">' . date('M d, Y', strtotime($val)) . '</span>' : '—';
                                    } elseif (!empty($col['bool'])) {
                                        echo $val
                                            ? '<i class="bi bi-check-circle-fill text-success"></i>'
                                            : '<i class="bi bi-x-circle text-muted opacity-50"></i>';
                                    } else {
                                        echo '<span class="' . esc($col['class'] ?? '') . '">' . esc((string) $val) . '</span>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>

                            <?php if ($routeEdit || $routeDel || $routeView): ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($routeView): ?>
                                            <a href="<?= route_to($routeView, $row['id']) ?>"
                                               class="btn btn-action btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($routeEdit): ?>
                                            <a href="<?= route_to($routeEdit, $row['id']) ?>"
                                               class="btn btn-action btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($routeDel): ?>
                                            <?php $delUrl = is_callable($routeDel) ? $routeDel($row) : route_to($routeDel, $row['id']); ?>
                                            <form method="post" action="<?= esc($delUrl) ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                        class="btn btn-action btn-outline-danger"
                                                        data-confirm="Delete this record?"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= count($columns) + ($routeEdit || $routeDel || $routeView ? 1 : 0) ?>"
                            class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                            No records found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pager): ?>
        <div class="card-footer bg-transparent d-flex justify-content-center py-3">
            <?= $pager->links('default', 'bootstrap_5_full') ?>
        </div>
    <?php endif; ?>
</div>
