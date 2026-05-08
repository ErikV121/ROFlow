<?php
// public/customer_decision.php
// Records the customer's approve/decline decision for a single finding.
// Token-gated. No CSRF (no session-based auth to protect against).

require_once __DIR__ . '/../../../config/Config.php';
$pdo = require __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../helper/helper.php';
require_once __DIR__ . '/../../repository/RepairOrderRepository.php';
require_once __DIR__ . '/../../repository/InspectionFindingRepository.php';

use Enum\ApprovalStatus;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$token     = trim($_POST['token'] ?? '');
$findingId = (int) ($_POST['finding_id'] ?? 0);
$decision  = $_POST['decision'] ?? '';

if ($token === '' || $findingId <= 0) {
    http_response_code(400);
    exit('Missing required fields.');
}

$roRepo = new RepairOrderRepository($pdo);
$ro = $roRepo->findByCustomerToken($token);

if ($ro === null) {
    http_response_code(404);
    exit('This link is no longer active.');
}

$findingRepo = new InspectionFindingRepository($pdo);

try {
    if ($decision === 'reset') {
        $findingRepo->resetToPending($findingId, $ro->getId());
    } else {
        $status = match ($decision) {
            'approve' => ApprovalStatus::Approved,
            'decline' => ApprovalStatus::Declined,
            default   => null,
        };
        if ($status === null) {
            http_response_code(400);
            exit('Invalid decision.');
        }
        $findingRepo->recordCustomerDecision($findingId, $ro->getId(), $status);
    }
} catch (\Throwable $e) {
    error_log('Customer decision failed: ' . $e->getMessage());
    // Fall through to redirect; don't leak internals.
}

header('Location: ' . customer_review_url($token));
exit;
