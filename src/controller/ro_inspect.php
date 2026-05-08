<?php

require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/CustomerRepository.php';
require_once __DIR__ . '/../repository/VehicleRepository.php';
require_once __DIR__ . '/../repository/InspectionFindingRepository.php';
require_once __DIR__ . '/../service/InspectionService.php';

require_login();
$user = current_user();

if ($user['role'] !== 'technician') {
    set_flash('error', 'Only technicians can perform inspections.');
    redirect('/dashboard');
}

$roId = (int) ($_GET['id'] ?? $_POST['ro_id'] ?? 0);
if ($roId <= 0) { redirect('/dashboard'); }

$roRepo       = new RepairOrderRepository($pdo);
$customerRepo = new CustomerRepository($pdo);
$vehicleRepo  = new VehicleRepository($pdo);
$findingRepo  = new InspectionFindingRepository($pdo);

$ro = $roRepo->findById($roId);
if ($ro === null) {
    set_flash('error', 'Repair order not found.');
    redirect('/dashboard');
}

// Authorization: must be the assigned tech
if ($ro->getTechnicianId() !== $user['user_id']) {
    set_flash('error', 'This RO is not assigned to you.');
    redirect('/dashboard');
}

// ---- POST: submit inspection ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $service = new InspectionService($pdo, $roRepo, $findingRepo);
    try {
        $findings = $_POST['findings'] ?? [];
        $service->submitInspection($roId, $user['user_id'], $findings);
        set_flash('success', 'Inspection submitted.');
        redirect('/dashboard');
    } catch (InvalidArgumentException | RuntimeException $e) {
        set_flash('error', $e->getMessage());
        header('Location: ' . inspection_url($roId));
        exit;
    } catch (\Throwable $e) {
        error_log('Inspection submit failed: ' . $e->getMessage());
        set_flash('error', 'Submission failed. Please try again.');
        header('Location: ' . inspection_url($roId));
        exit;
    }
}

// ---- GET: render the form ----
$vehicle  = $vehicleRepo->findById($ro->getVehicleId());
$customer = $customerRepo->findById($vehicle->getCustomerId());

$pageTitle = 'Inspect ' . $ro->getRoNumber();
require_once __DIR__ . '/../views/templates/header.php';
require_once __DIR__ . '/../views/ro_inspect_technician.php';
require_once __DIR__ . '/../views/templates/footer.php';
