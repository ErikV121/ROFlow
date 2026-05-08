<?php
require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/InspectionFindingRepository.php';
require_once __DIR__ . '/../service/InspectionService.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/dashboard'); }
verify_csrf();

$user = current_user();
if ($user['role'] !== 'advisor') {
    set_flash('error', 'Only advisors can edit findings.');
    redirect('/dashboard');
}

$roId = (int) ($_POST['ro_id'] ?? 0);
if ($roId <= 0) { redirect('/dashboard'); }

$findings = $_POST['findings'] ?? [];
if (!is_array($findings)) { $findings = []; }

$service = new InspectionService(
    $pdo,
    new RepairOrderRepository($pdo),
    new InspectionFindingRepository($pdo)
);

try {
    $service->updateMany($roId, $user['user_id'], $findings);
    set_flash('success', 'Findings saved.');
} catch (InvalidArgumentException | RuntimeException $e) {
    set_flash('error', $e->getMessage());
} catch (\Throwable $e) {
    error_log('Findings save failed: ' . $e->getMessage());
    set_flash('error', 'Save failed.');
}

header('Location: ' . repair_order_url($roId));
exit;
