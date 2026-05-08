<?php
// templates/header.php
// ============================================================
// Shared page header. Expects $pageTitle to be set (optional).
// Renders the dark sidebar for logged-in users.
// ============================================================

$user = current_user();
$pageTitle = $pageTitle ?? 'HEADER';
$flash = get_flash();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$dashboardActive = str_ends_with(rtrim($currentPath, '/'), '/dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/styles.css')) ?>">
</head>
<body class="<?= $user ? 'app-shell' : 'no-shell' ?>">

<?php if ($user): ?>
    <!-- Dark sidebar (advisor + technician share this) -->
    <aside class="sidebar">
        <div class="sidebar__brand">
            <div class="sidebar__logo">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="#fff">
                    <path d="M2 4h16v2H2zM2 9h10v2H2zM2 14h7v2H2z"/>
                </svg>
            </div>
            <span class="sidebar__brand-name">ROFlow</span>
        </div>

        <div class="sidebar__role-label"><?= e(ucfirst($user['role'])) ?></div>

        <nav class="sidebar__nav">
            <a href="<?= e(url('/dashboard')) ?>"
               class="sidebar__link <?= $dashboardActive ? 'is-active' : '' ?>">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                    <rect x="2" y="2" width="7" height="7" rx="1"/>
                    <rect x="11" y="2" width="7" height="7" rx="1"/>
                    <rect x="2" y="11" width="7" height="7" rx="1"/>
                    <rect x="11" y="11" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>


        </nav>

        <div class="sidebar__user">
            <div class="sidebar__avatar"><?= e(strtoupper(substr($user['full_name'], 0, 1) . substr(strstr($user['full_name'], ' '), 1, 1))) ?></div>
            <div class="sidebar__user-info">
                <div class="sidebar__user-name"><?= e($user['full_name']) ?></div>
                <a class="sidebar__logout" href="<?= e(url('/logout')) ?>">Sign out</a>
            </div>
        </div>
    </aside>

    <main class="main">
<?php else: ?>
    <main class="main main--no-sidebar">
<?php endif; ?>

<?php if (!empty($flash)): ?>
    <div class="flash-stack">
        <?php foreach ($flash as $f): ?>
            <div class="flash flash--<?= e($f['type']) ?>" style="position:relative;padding-right:36px;">
                <span><?= e($f['message']) ?></span>
                <button type="button"
                        class="flash__close"
                        onclick="this.parentElement.remove()"
                        aria-label="Dismiss"
                        style="position:absolute;top:50%;right:8px;transform:translateY(-50%);
                               background:transparent;border:none;cursor:pointer;
                               font-size:18px;line-height:1;padding:4px 8px;
                               color:inherit;opacity:0.6;">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
