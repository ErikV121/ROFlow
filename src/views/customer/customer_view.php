<?php

require_once __DIR__ . '/../../../config/Config.php';
$pdo = require __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../helper/helper.php';
require_once __DIR__ . '/../../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../../repository/CustomerRepository.php';
require_once __DIR__ . '/../../repository/VehicleRepository.php';
require_once __DIR__ . '/../../repository/InspectionFindingRepository.php';

$token = trim($_GET['token'] ?? '');

$roRepo = new RepairOrderRepository($pdo);
$ro = $token !== '' ? $roRepo->findByCustomerToken($token) : null;

if ($ro === null) {
    // Invalid, expired, reverted, or not in awaiting_approval anymore.
    http_response_code(404);
    $errorTitle   = 'Link not available';
    $errorMessage = "This approval link is no longer active. It may have expired, been replaced, or the work order has moved on. Please contact the shop if you have questions.";
    require __DIR__ . '/customer_invalid.php';
    exit;
}

$vehicleRepo  = new VehicleRepository($pdo);
$customerRepo = new CustomerRepository($pdo);
$findingRepo  = new InspectionFindingRepository($pdo);

$vehicle  = $vehicleRepo->findById($ro->getVehicleId());
$customer = $customerRepo->findById($vehicle->getCustomerId());
$findings = $findingRepo->findAllByRepairOrderId($ro->getId());

require __DIR__ . '/customer_view_view.php';
