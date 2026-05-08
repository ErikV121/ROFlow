<?php

require_once __DIR__ . '/../../config/Config.php';
$pdo = require __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../helper/helper.php';
require_once __DIR__ . '/../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../repository/CustomerRepository.php';
require_once __DIR__ . '/../repository/VehicleRepository.php';
require_once __DIR__ . '/../service/RepairOrderService.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/dashboard');
}

verify_csrf();

$user = current_user();
if ($user['role'] !== 'advisor') {
    set_flash('error', 'Only advisors can create repair orders.');
    redirect('/dashboard');
}

$service = new RepairOrderService(
    $pdo,
    new RepairOrderRepository($pdo),
    new CustomerRepository($pdo),
    new VehicleRepository($pdo)
);

try {
    $ro = $service->createIntake([
        'customer_name' => trim($_POST['customer'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'vin' => trim($_POST['vin'] ?? ''),
        'year' => $_POST['year'] ?? null,
        'make' => trim($_POST['make'] ?? ''),
        'model' => trim($_POST['model'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'mileage' => $_POST['mileage'] ?? 0,
        'complaint' => trim($_POST['complaint'] ?? ''),
    ], $user['user_id']);

    set_flash('success', "Created {$ro->getRoNumber()}.");
    header('Location: ' . repair_order_url($ro->getId()));
    exit;

} catch (InvalidArgumentException $e) {
    set_flash('error', $e->getMessage());
    redirect('/dashboard');

} catch (\Throwable $e) {
    error_log('RO create failed: ' . $e->getMessage());
    set_flash('error', 'Could not create the RO. Please try again.');
    redirect('/dashboard');
}
