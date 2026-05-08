<?php
// public/ro_action.php

require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/CustomerRepository.php';
require_once __DIR__ . '/../repository/VehicleRepository.php';
require_once __DIR__ . '/../service/RepairOrderService.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/dashboard'); }
verify_csrf();

$user = current_user();
if ($user['role'] !== 'advisor') {
    set_flash('error', 'Only advisors can perform this action.');
    redirect('/dashboard');
}

$action = $_POST['action'] ?? '';
$roId   = (int) ($_POST['ro_id'] ?? 0);
if ($roId <= 0) { redirect('/dashboard'); }

$service = new RepairOrderService(
    $pdo,
    new RepairOrderRepository($pdo),
    new CustomerRepository($pdo),
    new VehicleRepository($pdo)
);

try {
    switch ($action) {
        case 'start_diagnosis':
            $techId = (int) ($_POST['technician_id'] ?? 0);
            if ($techId <= 0) {
                throw new InvalidArgumentException('Please select a technician.');
            }
            $service->startDiagnosis($roId, $techId, $user['user_id']);
            set_flash('success', 'Sent to diagnosis.');
            break;

        case 'send_for_approval':
            // legacy alias — same as 'advance' from Inspected
            $service->sendForCustomerApproval($roId, $user['user_id']);
            set_flash('success', 'Customer approval link generated.');
            break;

        case 'advance':
            $service->advanceToNext($roId, $user['user_id']);
            set_flash('success', 'Moved to the next stage.');
            break;

        case 'revert':
            $service->revertStatus($roId, $user['user_id']);
            set_flash('success', 'Stepped back one stage.');
            break;

        default:
            throw new InvalidArgumentException('Unknown action.');
    }
} catch (InvalidArgumentException | RuntimeException $e) {
    set_flash('error', $e->getMessage());
} catch (\Throwable $e) {
    error_log('RO action failed: ' . $e->getMessage());
    set_flash('error', 'Action failed. Please try again.');
}

header('Location: ' . repair_order_url($roId));
exit;
