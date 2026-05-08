<?php use Enum\RepairOrderStatus; ?>

<?php
$status = $ro->getStatus();

$steps = [
        RepairOrderStatus::Intake,
        RepairOrderStatus::Diagnosis,
        RepairOrderStatus::Inspected,
        RepairOrderStatus::AwaitingApproval,
        RepairOrderStatus::Repair,
        RepairOrderStatus::ReadyForPickup,
        RepairOrderStatus::Closed,
];
$currentIdx = array_search($status, $steps, true);

// Findings + totals
$hasFindings = count($findings) > 0;
$totalCost = 0;
$approvedCost = 0;
foreach ($findings as $f) {
    $totalCost += $f->getEstimatedCost();
    if ($f->isApproved()) {
        $approvedCost += $f->getEstimatedCost();
    }
}

$editable = in_array($status, [
        RepairOrderStatus::Diagnosis,
        RepairOrderStatus::Inspected,
], true);

// Label for the forward action button (placed at the bottom of Summary)
$advanceLabel = match ($status) {
    RepairOrderStatus::Intake          => 'Advance to Diagnosis ›',
    RepairOrderStatus::Diagnosis       => $hasFindings ? 'Advance to Inspection Complete ›' : null,
    RepairOrderStatus::Inspected       => 'Generate Customer Link ›',
    RepairOrderStatus::AwaitingApproval=> 'Move to Repair ›',
    RepairOrderStatus::Repair          => 'Mark Ready for Pickup ›',
    RepairOrderStatus::ReadyForPickup  => 'Close RO ›',
    default                            => null,
};
?>

<div class="ro-detail-header">
    <div>
        <a href="<?= e(url('/dashboard')) ?>" class="back-link">‹ Dashboard</a>
        <div class="ro-detail-title-row">
            <h1 class="ro-detail-number"><?= e($ro->getRoNumber()) ?></h1>
            <span class="status-badge"
                  style="color: <?= e($status->color()) ?>; background: <?= e($status->bg()) ?>;">
                <span class="status-badge__dot" style="background: <?= e($status->color()) ?>;"></span>
                <?= e($status->label()) ?>
            </span>
        </div>
    </div>

    <?php if ($status->previous() !== null): ?>
        <form action="<?= e(repair_order_action_url($ro->getId())) ?>" method="post" style="margin:0;"
              onsubmit="return confirm('Step back to <?= e($status->previous()->label()) ?>?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="revert">
            <input type="hidden" name="ro_id" value="<?= e($ro->getId()) ?>">
            <button type="submit" class="btn btn--secondary">‹ Previous</button>
        </form>
    <?php endif; ?>
</div>

<div class="stepper">
    <?php foreach ($steps as $i => $step): ?>
        <div class="stepper__node <?= $i <= $currentIdx ? 'is-done' : '' ?> <?= $i === $currentIdx ? 'is-current' : '' ?>">
            <div class="stepper__dot"></div>
            <div class="stepper__label"><?= e($step->label()) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="tabs">
    <button class="tab is-active" data-tab="summary">Summary</button>
    <button class="tab" data-tab="findings">Findings (<?= count($findings) ?>)</button>
</div>

<div class="tab-panel" data-panel="findings" style="display:none;">
    <?php if (empty($findings)): ?>
        <div class="detail-card detail-card--full">
            <p class="muted">No findings yet. The technician hasn't submitted an inspection.</p>
        </div>
    <?php else: ?>
        <form action="<?= e(repair_order_findings_url($ro->getId())) ?>" method="post" id="findings-form">
            <?= csrf_field() ?>
            <input type="hidden" name="ro_id" value="<?= e($ro->getId()) ?>">

            <div class="detail-card detail-card--full">
                <div class="detail-card__title" style="display:flex;justify-content:space-between;align-items:center;">
                    <span>FINDINGS (<?= count($findings) ?>)</span>
                    <span class="muted">Estimated total: $<?= number_format($totalCost, 2) ?></span>
                </div>

                <?php foreach ($findings as $f): ?>
                    <div class="finding-edit" style="border-bottom:1px solid var(--border, #eee); padding:12px 0;">
                        <div class="finding-edit__row" style="display:grid;grid-template-columns:2fr 1fr;gap:10px;">
                            <div class="form-field">
                                <label class="form-field__label">Title</label>
                                <input type="text"
                                       name="findings[<?= e($f->getId()) ?>][title]"
                                       value="<?= e($f->getTitle()) ?>"
                                       maxlength="100" required <?= $editable ? '' : 'disabled' ?>>
                            </div>

                            <div class="form-field">
                                <label class="form-field__label">Estimated cost ($)</label>
                                <input type="number"
                                       name="findings[<?= e($f->getId()) ?>][estimated_cost]"
                                       step="0.01" min="0"
                                       value="<?= e(number_format($f->getEstimatedCost(), 2, '.', '')) ?>"
                                        <?= $editable ? '' : 'disabled' ?>>
                            </div>
                        </div>

                        <div class="form-field">
                            <label class="form-field__label">Description</label>
                            <textarea name="findings[<?= e($f->getId()) ?>][description]"
                                      rows="2" <?= $editable ? '' : 'disabled' ?>><?= e($f->getDescription() ?? '') ?></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($editable): ?>
                    <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                        <button type="submit" class="btn btn--primary">Save All Findings</button>
                    </div>
                <?php else: ?>
                    <p class="muted" style="margin-top:12px;">
                        Findings are locked at this stage. Step back to "Inspection Complete" to edit.
                    </p>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<div class="tab-panel" data-panel="summary">
    <div class="detail-grid">
        <div class="detail-card">
            <div class="detail-card__title">CUSTOMER</div>
            <div class="detail-row"><span>Name</span><strong><?= e($customer->getFullName()) ?></strong></div>
            <div class="detail-row"><span>Phone</span><strong><?= e($customer->getPhone() ?? '—') ?></strong></div>
            <div class="detail-row"><span>Email</span><strong><?= e($customer->getEmail() ?? '—') ?></strong></div>
        </div>

        <div class="detail-card">
            <div class="detail-card__title">VEHICLE</div>
            <div class="detail-row"><span>Vehicle</span><strong><?= e($vehicle->getDisplayName()) ?></strong></div>
            <div class="detail-row"><span>Color</span><strong><?= e($vehicle->getColor() ?? '—') ?></strong></div>
            <div class="detail-row"><span>Mileage</span><strong><?= e(number_format($ro->getMileage())) ?> mi</strong></div>
            <div class="detail-row"><span>VIN</span><strong><?= e($vehicle->getVin()) ?></strong></div>
        </div>
    </div>

    <div class="detail-card detail-card--full">
        <div class="detail-card__title">CUSTOMER COMPLAINT</div>
        <p><?= e($ro->getComplaint()) ?></p>
    </div>

    <div class="detail-card detail-card--full">
        <div class="detail-card__title">ASSIGNMENT</div>
        <div class="detail-row">
            <span>Technician</span>
            <strong><?= $tech ? e($tech->getFullName()) : '<em class="unassigned">Unassigned</em>' ?></strong>
        </div>
    </div>

    <div class="detail-card detail-card--full">
        <div class="detail-card__title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>FINDINGS &amp; COSTS</span>
            <span class="muted"><?= count($findings) ?> item<?= count($findings) === 1 ? '' : 's' ?></span>
        </div>

        <?php if (empty($findings)): ?>
            <p class="muted">No findings recorded yet.</p>
        <?php else: ?>
            <table class="ro-table" style="width:100%;">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th style="text-align:right;">Cost</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($findings as $f): ?>
                    <tr>
                        <td><?= e($f->getTitle()) ?></td>
                        <td class="muted"><?= e($f->getApprovalStatus()->label()) ?></td>
                        <td style="text-align:right;">$<?= number_format($f->getEstimatedCost(), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right;"><strong>Estimated total</strong></td>
                    <td style="text-align:right;"><strong>$<?= number_format($totalCost, 2) ?></strong></td>
                </tr>
                <?php if ($approvedCost > 0 && $approvedCost !== $totalCost): ?>
                    <tr>
                        <td colspan="2" style="text-align:right;" class="muted">Customer-approved</td>
                        <td style="text-align:right;" class="muted">$<?= number_format($approvedCost, 2) ?></td>
                    </tr>
                <?php endif; ?>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($advanceLabel !== null || $status === RepairOrderStatus::AwaitingApproval): ?>
        <div class="detail-card detail-card--full"
             style="display:flex;justify-content:flex-end;align-items:center;gap:8px;">

            <?php if ($status === RepairOrderStatus::AwaitingApproval && $ro->getCustomerToken()): ?>
                <a href="<?= e(customer_review_url($ro->getCustomerToken())) ?>"
                   class="btn btn--secondary" target="_blank">View Customer Link ↗</a>
            <?php endif; ?>

            <?php if ($status === RepairOrderStatus::Intake): ?>
                <button type="button" id="advance-to-diagnosis-btn" class="btn btn--primary">
                    <?= e($advanceLabel) ?>
                </button>
            <?php elseif ($advanceLabel !== null): ?>
                <form action="<?= e(repair_order_action_url($ro->getId())) ?>" method="post" style="margin:0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="advance">
                    <input type="hidden" name="ro_id" value="<?= e($ro->getId()) ?>">
                    <button type="submit" class="btn btn--primary"><?= e($advanceLabel) ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div id="diagnosis-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:16px;">Advance to Diagnosis</h2>
        <p class="muted" style="margin-bottom:16px;">Select a technician to assign this RO to.</p>
        <form action="<?= e(repair_order_action_url($ro->getId())) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="start_diagnosis">
            <input type="hidden" name="ro_id" value="<?= e($ro->getId()) ?>">

            <div class="form-field">
                <label class="form-field__label">Technician</label>
                <select name="technician_id" required>
                    <option value="">— Choose —</option>
                    <?php foreach ($technicians as $t): ?>
                        <option value="<?= e($t->getId()) ?>"><?= e($t->getFullName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;">
                <button type="button" id="diagnosis-cancel-btn" class="btn btn--secondary">Cancel</button>
                <button type="submit" class="btn btn--primary">Assign &amp; Advance</button>
            </div>
        </form>
    </div>
</div>
