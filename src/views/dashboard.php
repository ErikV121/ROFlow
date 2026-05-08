<?php

require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';


require_login();

$user = current_user();

$roRepo = new RepairOrderRepository($pdo);

if ($user['role'] === 'advisor') {
    $ros    = $roRepo->findAllActiveForDashboard();
    $counts = $roRepo->countsByStatus();
} elseif ($user['role'] === 'technician') {
    $myRos = $roRepo->findActiveByTechnicianIdForDashboard($user['user_id']);
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/templates/header.php';

if ($user['role'] === 'advisor') {
    require_once __DIR__ . '/dashboard_advisor.php';
} elseif ($user['role'] === 'technician') {
    require_once __DIR__ . '/dashboard_technician.php';
}

require_once __DIR__ . '/templates/footer.php';