<?php

require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/CustomerRepository.php';
require_once __DIR__ . '/../repository/VehicleRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/InspectionFindingRepository.php';
use Enum\Role;

require_login();
$user = current_user();

$roId = (int) ($_GET['id'] ?? 0);
if ($roId <= 0) { redirect('/dashboard'); }

$roRepo       = new RepairOrderRepository($pdo);
$customerRepo = new CustomerRepository($pdo);
$vehicleRepo  = new VehicleRepository($pdo);
$userRepo     = new UserRepository($pdo);
$findingRepo  = new InspectionFindingRepository($pdo);

$ro = $roRepo->findById($roId);
if ($ro === null) {
    set_flash('error', 'Repair order not found.');
    redirect('/dashboard');
}

$vehicle  = $vehicleRepo->findById($ro->getVehicleId());
$customer = $customerRepo->findById($vehicle->getCustomerId());
$tech     = $ro->getTechnicianId() ? $userRepo->findById($ro->getTechnicianId()) : null;
$findings = $findingRepo->findAllByRepairOrderId($roId);
$technicians = $userRepo->findAllByRole(Role::Technician);

$pageTitle = $ro->getRoNumber();
require_once __DIR__ . '/../views/templates/header.php';
require_once __DIR__ . '/../views/ro_view_advisor.php';
require_once __DIR__ . '/../views/templates/footer.php';
