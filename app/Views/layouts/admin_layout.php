<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($page_title ?? 'Dashboard') ?> — <?= esc($site_name ?? 'Admin') ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Google Fonts: DM Sans + DM Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════ -->
<div id="sidebar" class="sidebar d-flex flex-column">

    <!-- Brand -->
    <div class="sidebar-brand d-flex align-items-center gap-2 px-3 py-4">
        <?php if (!empty($site_logo)): ?>
            <img src="<?= base_url($site_logo) ?>" alt="Logo" height="32">
        <?php else: ?>
            <span class="sidebar-brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
        <?php endif; ?>
        <span class="sidebar-brand-name fw-semibold"><?= esc($site_name ?? 'Admin') ?></span>
        <button class="btn btn-link ms-auto sidebar-toggle d-lg-none text-white p-0" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav flex-grow-1 overflow-auto px-2 pb-4">
        <?php foreach ($menu_items ?? [] as $item): ?>
            <?php if (!empty($item['children'])): ?>
                <!-- Parent with children -->
                <div class="sidebar-group mb-1">
                    <a href="#menu-<?= $item['id'] ?>"
                       class="sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-2 collapsed"
                       data-bs-toggle="collapse"
                       aria-expanded="false">
                        <i class="bi <?= esc($item['icon'] ?? 'bi-dot') ?>"></i>
                        <span><?= esc($item['title']) ?></span>
                        <i class="bi bi-chevron-right ms-auto sidebar-arrow"></i>
                    </a>
                    <div class="collapse sidebar-sub" id="menu-<?= $item['id'] ?>">
                        <?php foreach ($item['children'] as $child): ?>
                            <a href="<?= route_to($child['route'] ?? '#') ?>"
                               class="sidebar-link sidebar-link--sub d-flex align-items-center gap-2 px-4 py-2 rounded-2 <?= current_url() === route_to($child['route'] ?? '#') ? 'active' : '' ?>">
                                <i class="bi <?= esc($child['icon'] ?? 'bi-dot') ?> small"></i>
                                <span><?= esc($child['title']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= route_to($item['route'] ?? '#') ?>"
                   class="sidebar-link d-flex align-items-center gap-2 px-3 py-2 rounded-2 mb-1 <?= strpos(current_url(), $item['route'] ?? '') !== false ? 'active' : '' ?>">
                    <i class="bi <?= esc($item['icon'] ?? 'bi-circle') ?>"></i>
                    <span><?= esc($item['title']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="badge bg-warning ms-auto"><?= esc($item['badge']) ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar Footer: Avatar -->
    <div class="sidebar-footer d-flex align-items-center gap-2 px-3 py-3 border-top border-white border-opacity-10">
        <div class="avatar-sm">
            <?php if (!empty($current_user['avatar'])): ?>
                <img src="<?= base_url($current_user['avatar']) ?>" class="rounded-circle" width="36" height="36" alt="Avatar">
            <?php else: ?>
                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
                    <?= strtoupper(substr($current_user['first_name'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1 overflow-hidden">
            <div class="text-white fw-medium text-truncate small">
                <?= esc(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? '')) ?>
            </div>
            <div class="text-white text-opacity-60 text-truncate" style="font-size:.75rem">
                <?= esc($current_user['email'] ?? '') ?>
            </div>
        </div>
        <a href="<?= route_to('auth.logout') ?>" class="text-white text-opacity-60 hover-opacity-100" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</div><!-- /#sidebar -->

<!-- ═══════════════════════════════════════════════════════
     MAIN WRAPPER
═══════════════════════════════════════════════════════ -->
<div id="mainWrapper" class="main-wrapper d-flex flex-column min-vh-100">

    <!-- TOP NAVBAR -->
    <header class="topbar d-flex align-items-center px-3 px-lg-4 gap-3 border-bottom">
        <!-- Hamburger -->
        <button class="btn btn-link text-body p-0" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="d-none d-md-flex align-items-center">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= route_to('admin.dashboard') ?>">Home</a></li>
                <?php foreach ($breadcrumbs ?? [] as $bc): ?>
                    <?php if (!empty($bc['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?= esc($bc['url']) ?>"><?= esc($bc['title']) ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?= esc($bc['title']) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>

        <div class="ms-auto d-flex align-items-center gap-2">

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link text-body position-relative p-2" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if (($unread_count ?? 0) > 0): ?>
                        <span class="position-absolute top-1 start-75 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                            <?= $unread_count > 99 ? '99+' : $unread_count ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow notification-dropdown p-0" style="width:340px;max-height:420px;overflow-y:auto">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <span class="fw-semibold small">Notifications</span>
                        <a href="#" class="text-muted small" id="markAllRead">Mark all read</a>
                    </div>
                    <div id="notificationList">
                        <div class="text-center text-muted py-4 small">Loading…</div>
                    </div>
                </div>
            </div>

            <!-- Theme Toggle -->
            <button class="btn btn-link text-body p-2" id="themeToggle" title="Toggle dark mode">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </button>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-body d-flex align-items-center gap-2 p-1 text-decoration-none" data-bs-toggle="dropdown">
                    <div class="avatar-sm-inline rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold" style="width:32px;height:32px;font-size:.8rem">
                        <?= strtoupper(substr($current_user['first_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline small fw-medium">
                        <?= esc($current_user['first_name'] ?? 'User') ?>
                    </span>
                    <i class="bi bi-chevron-down small opacity-50"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item" href="<?= route_to('admin.profile') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?= route_to('admin.settings') ?>"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= route_to('auth.logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-grow-1 p-3 p-lg-4">

        <!-- Flash Messages -->
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div><?= session('success') ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div><?= session('error') ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <strong><i class="bi bi-exclamation-triangle me-1"></i>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1 ps-3 small">
                    <?php foreach (session('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex align-items-start justify-content-between mb-4">
            <div>
                <h4 class="mb-1 fw-semibold"><?= esc($page_title ?? 'Dashboard') ?></h4>
                <?php if (!empty($page_subtitle)): ?>
                    <p class="text-muted mb-0 small"><?= esc($page_subtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($page_actions)): ?>
                <div class="d-flex gap-2"><?= $page_actions ?></div>
            <?php endif; ?>
        </div>

        <!-- CHILD VIEW INJECTED HERE -->
        <?= $content ?? '' ?>

    </main>

    <!-- FOOTER -->
    <footer class="topbar border-top d-flex align-items-center justify-content-between px-4 py-2 small text-muted">
        <span>&copy; <?= date('Y') ?> <?= esc($site_name ?? 'App') ?>. All rights reserved.</span>
        <span>Built with <a href="https://codeigniter.com" target="_blank" class="text-muted">CodeIgniter 4</a></span>
    </footer>

</div><!-- /#mainWrapper -->

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Admin JS -->
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
</body>
</html>
