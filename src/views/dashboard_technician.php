<?php use Enum\RepairOrderStatus; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Queue</h1>
        <p class="page-subtitle"><?= count($myRos) ?> active assignment<?= count($myRos) !== 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="ro-list">
    <?php if (empty($myRos)): ?>
        <div class="empty-state">
            <p>No active assignments. Check back later.</p>
        </div>
    <?php else: ?>
        <?php foreach ($myRos as $ro):
            $status = RepairOrderStatus::from($ro['status']);
            $canInspect = $status === RepairOrderStatus::Diagnosis && empty($ro['inspection_submitted_at']);
            $inspected = !empty($ro['inspection_submitted_at']);
            ?>
            <div class="ro-card <?= $canInspect ? 'ro-card--actionable' : '' ?>"
                    <?php if ($canInspect): ?>
                        onclick="window.location='<?= e(inspection_url((int) $ro['id'])) ?>'"
                    <?php endif; ?>>
                <div class="ro-card__body">
                    <div class="ro-card__header">
                        <span class="ro-card__number"><?= e($ro['ro_number']) ?></span>
                        <span class="status-badge"
                              style="color: <?= e($status->color()) ?>; background: <?= e($status->bg()) ?>;">
                            <?= e($status->label()) ?>
                        </span>
                    </div>
                    <div class="ro-card__vehicle">
                        <?= e($ro['vehicle_year']) ?> <?= e($ro['vehicle_make']) ?> <?= e($ro['vehicle_model']) ?>
                        <span class="muted">— <?= e($ro['customer_name']) ?></span>
                    </div>
                    <div class="ro-card__complaint muted truncate">
                        <?= e($ro['complaint']) ?>
                    </div>
                </div>
                <div class="ro-card__action">
                    <?php if ($canInspect): ?>
                        <button class="btn btn--primary">Start Inspection</button>
                    <?php elseif ($inspected): ?>
                        <span class="status-text status-text--green">✓ Submitted</span>
                    <?php else: ?>
                        <span class="status-text muted">
                            <?= $status === RepairOrderStatus::Intake ? 'Awaiting assignment' : 'In progress' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
