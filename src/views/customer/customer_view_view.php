<?php
use Enum\ApprovalStatus;

$totalCost = 0;
$approvedCost = 0;
$pendingCount = 0;
foreach ($findings as $f) {
    $totalCost += $f->getEstimatedCost();
    if ($f->isApproved()) {
        $approvedCost += $f->getEstimatedCost();
    }
    if ($f->isPending()) {
        $pendingCount++;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Approval — <?= e($ro->getRoNumber()) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/styles.css')) ?>">
    <style>
        body.customer-portal {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #f6f7f9;
            color: #1a1a1a;
            margin: 0;
        }
        .cp-wrap { max-width: 720px; margin: 0 auto; padding: 24px 16px 64px; }
        .cp-header {
            display:flex; align-items:center; gap:10px;
            padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .cp-header__logo {
            width:32px; height:32px; border-radius:8px; background:#111;
            display:flex; align-items:center; justify-content:center;
        }
        .cp-header__title { font-size: 18px; font-weight: 700; }
        .cp-header__ro { margin-left:auto; font-family:'DM Mono', monospace;
            font-size:13px; color:#6b7280; }
        .cp-card {
            background:#fff; border:1px solid #e5e7eb; border-radius: 12px;
            padding: 18px 20px; margin-bottom: 16px;
        }
        .cp-card__title {
            font-size: 11px; letter-spacing: 0.08em; color:#6b7280;
            text-transform: uppercase; margin-bottom: 10px; font-weight: 600;
        }
        .cp-row { display:flex; justify-content:space-between; padding: 4px 0; font-size: 14px; }
        .cp-row span { color:#6b7280; }
        .cp-finding {
            border:1px solid #e5e7eb; border-radius: 10px; padding: 16px;
            margin-bottom: 12px; background:#fff;
        }
        .cp-finding--approved { border-left: 4px solid #047857; }
        .cp-finding--declined { border-left: 4px solid #6b7280; }
        .cp-finding--pending  { border-left: 4px solid #e63946; }
        .cp-finding__head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        .cp-finding__title { font-weight: 600; font-size: 15px; }
        .cp-finding__cost  { font-weight: 700; font-size: 16px; white-space: nowrap; }
        .cp-finding__desc  { color:#4b5563; font-size: 14px; margin-top: 6px; line-height: 1.5; }
        .cp-finding__actions { margin-top: 12px; display:flex; gap:8px; align-items:center; }
        .cp-status-badge {
            display:inline-block; padding: 4px 10px; border-radius: 999px;
            font-size: 12px; font-weight: 600;
        }
        .cp-status-badge--approved { background:#d1fae5; color:#047857; }
        .cp-status-badge--declined { background:#f3f4f6; color:#374151; }
        .cp-btn {
            border:none; border-radius: 8px; padding: 8px 14px;
            font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit;
        }
        .cp-btn--approve { background:#047857; color:#fff; }
        .cp-btn--decline { background:#fff; color:#374151; border:1px solid #d1d5db; }
        .cp-btn--undo    { background:transparent; color:#6b7280;
            text-decoration: underline; padding: 4px 0; }
        .cp-totals {
            display:grid; grid-template-columns: 1fr auto; gap:6px;
            padding-top: 12px; margin-top: 12px; border-top: 1px solid #e5e7eb;
        }
        .cp-totals__label { color:#6b7280; }
        .cp-totals__final { font-size: 18px; font-weight: 700; padding-top: 6px; }
        .cp-instructions {
            background: #fef3c7; color: #78350f; padding: 12px 16px;
            border-radius: 8px; font-size: 14px; margin-bottom: 16px;
        }
        .cp-done {
            background: #d1fae5; color: #065f46; padding: 12px 16px;
            border-radius: 8px; font-size: 14px; margin-bottom: 16px;
        }
        .cp-inline-form { display: inline; margin: 0; }
    </style>
</head>
<body class="customer-portal">

<div class="cp-wrap">

    <header class="cp-header">
        <div class="cp-header__logo">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="#fff">
                <path d="M2 4h16v2H2zM2 9h10v2H2zM2 14h7v2H2z"/>
            </svg>
        </div>
        <div class="cp-header__title">Service Approval</div>
        <div class="cp-header__ro"><?= e($ro->getRoNumber()) ?></div>
    </header>

    <?php if ($pendingCount > 0): ?>
        <div class="cp-instructions">
            <strong>Hi <?= e($customer->getFullName()) ?>,</strong> please review the recommended services
            below and approve or decline each one. <?= $pendingCount ?> item<?= $pendingCount === 1 ? '' : 's' ?>
            still need<?= $pendingCount === 1 ? 's' : '' ?> a decision.
        </div>
    <?php else: ?>
        <div class="cp-done">
            ✓ Thanks! All items have been reviewed. Your service advisor will follow up shortly.
        </div>
    <?php endif; ?>

    <div class="cp-card">
        <div class="cp-card__title">Your Vehicle</div>
        <div class="cp-row"><span>Vehicle</span><strong><?= e($vehicle->getDisplayName()) ?></strong></div>
        <div class="cp-row"><span>VIN</span><strong><?= e($vehicle->getVin()) ?></strong></div>
        <div class="cp-row"><span>Mileage</span><strong><?= e(number_format($ro->getMileage())) ?> mi</strong></div>
        <div class="cp-row"><span>Reported issue</span><strong style="max-width:60%;text-align:right;"><?= e($ro->getComplaint()) ?></strong></div>
    </div>

    <div class="cp-card">
        <div class="cp-card__title">Recommended Services</div>

        <?php if (empty($findings)): ?>
            <p style="color:#6b7280;">No additional services were recommended.</p>
        <?php else: ?>
            <?php foreach ($findings as $f):
                $cls = $f->isApproved() ? 'approved' : ($f->isDeclined() ? 'declined' : 'pending');
                ?>
                <div class="cp-finding cp-finding--<?= $cls ?>">
                    <div class="cp-finding__head">
                        <div>
                            <div class="cp-finding__title"><?= e($f->getTitle()) ?></div>
                            <?php if ($f->getDescription()): ?>
                                <div class="cp-finding__desc"><?= e($f->getDescription()) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="cp-finding__cost">$<?= number_format($f->getEstimatedCost(), 2) ?></div>
                    </div>

                    <div class="cp-finding__actions">
                        <?php if ($f->isPending()): ?>
                            <form action="<?= e(customer_decision_url($ro->getCustomerToken())) ?>" method="post" class="cp-inline-form">
                                <input type="hidden" name="token" value="<?= e($ro->getCustomerToken()) ?>">
                                <input type="hidden" name="finding_id" value="<?= e($f->getId()) ?>">
                                <button type="submit" name="decision" value="approve" class="cp-btn cp-btn--approve">
                                    Approve
                                </button>
                                <button type="submit" name="decision" value="decline" class="cp-btn cp-btn--decline">
                                    Decline
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="cp-status-badge cp-status-badge--<?= $cls ?>">
                                <?= $f->isApproved() ? '✓ Approved' : '✗ Declined' ?>
                            </span>
                            <form action="<?= e(customer_decision_url($ro->getCustomerToken())) ?>" method="post" class="cp-inline-form">
                                <input type="hidden" name="token" value="<?= e($ro->getCustomerToken()) ?>">
                                <input type="hidden" name="finding_id" value="<?= e($f->getId()) ?>">
                                <input type="hidden" name="decision" value="reset">
                                <button type="submit" class="cp-btn cp-btn--undo">Change my mind</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cp-totals">
                <div class="cp-totals__label">Total estimated cost</div>
                <div>$<?= number_format($totalCost, 2) ?></div>

                <div class="cp-totals__label">You've approved</div>
                <div class="cp-totals__final">$<?= number_format($approvedCost, 2) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <p style="text-align:center;color:#9ca3af;font-size:12px;margin-top:24px;">
        Questions? Contact your service advisor.
    </p>

</div>

</body>
</html>
