<?php
require_once __DIR__ . '/../models/enum/RepairOrderStatus.php';

use Enum\RepairOrderStatus; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Overview of all repair orders</p>
    </div>
    <a href="#" id="open-modal-btn" class="btn btn--primary">
        <span class="btn__plus">+</span> New RO
    </a>
</div>

<div id="ro-modal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <h2 style="font-size:16px;font-weight:700;margin-bottom:16px;">New Repair Order</h2>

        <form id="new-ro-form" action="<?= e(url('/repair-orders')) ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-field">
                <label class="form-field__label">Customer name</label>
                <input type="text" name="customer" required>
            </div>

            <div class="form-field">
                <label class="form-field__label">Phone</label>
                <input type="tel" name="phone" placeholder="(555) 555-5555">
            </div>

            <div class="form-field">
                <label class="form-field__label">Email (optional)</label>
                <input type="email" name="email" placeholder="customer@example.com">
            </div>

            <div style="display:grid;grid-template-columns:90px 1fr 1fr;gap:10px;">
                <div class="form-field">
                    <label class="form-field__label">Year</label>
                    <input type="number" name="year" min="1900" max="2099" required>
                </div>
                <div class="form-field">
                    <label class="form-field__label">Make</label>
                    <input type="text" name="make" required>
                </div>
                <div class="form-field">
                    <label class="form-field__label">Model</label>
                    <input type="text" name="model" required>
                </div>
            </div>

            <div class="form-field">
                <label class="form-field__label">VIN</label>
                <input type="text" name="vin" maxlength="17" required>
            </div>

            <div class="form-field">
                <label class="form-field__label">Color (optional)</label>
                <input type="text" name="color" placeholder="e.g. Silver">
            </div>

            <div class="form-field">
                <label class="form-field__label">Mileage</label>
                <input type="number" name="mileage" min="0">
            </div>

            <div class="form-field">
                <label class="form-field__label">Complaint</label>
                <textarea name="complaint" rows="3" required></textarea>
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
                <button type="button" id="cancel-btn" class="btn btn--secondary">Cancel</button>
                <button type="submit" class="btn btn--primary">Create RO</button>
            </div>
        </form>
    </div>
</div>


<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-card__label">Open ROs</span>
        <span class="stat-card__value"><?= e(array_sum($counts) - ($counts['closed'] ?? 0)) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Awaiting Approval</span>
        <span class="stat-card__value stat-card__value--red"><?= e($counts['awaiting_approval'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">In Repair</span>
        <span class="stat-card__value stat-card__value--purple"><?= e($counts['repair'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Ready for Pickup</span>
        <span class="stat-card__value stat-card__value--green"><?= e($counts['ready_for_pickup'] ?? 0) ?></span>
    </div>
</div>

<!-- Search + table -->
<div class="toolbar">
    <input type="text" id="ro-search" class="search-input"
           placeholder="Search by RO#, customer, vehicle…">
</div>

<div class="table-card">
    <table class="ro-table">
        <thead>
        <tr>
            <th>RO #</th>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Complaint</th>
            <th>Status</th>
            <th>Tech</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="ro-tbody">
        <?php if (empty($ros)): ?>
            <tr><td colspan="7" class="empty-row">No repair orders yet.</td></tr>
        <?php else: ?>
            <?php foreach ($ros as $ro):
                $status = RepairOrderStatus::tryFrom($ro['status']);
                if ($status === null) {
                    continue;
                }
                ?>
                <tr class="ro-row" onclick="window.location='<?= e(repair_order_url((int) $ro['id'])) ?>'">
                    <td class="ro-number"><?= e($ro['ro_number']) ?></td>
                    <td><?= e($ro['customer_name']) ?></td>
                    <td class="muted">
                        <?= e($ro['vehicle_year']) ?> <?= e($ro['vehicle_make']) ?> <?= e($ro['vehicle_model']) ?>
                    </td>
                    <td class="muted truncate"><?= e($ro['complaint']) ?></td>
                    <td>
                        <span class="status-badge"
                              style="color: <?= e($status->color()) ?>; background: <?= e($status->bg()) ?>;">
                            <span class="status-badge__dot" style="background: <?= e($status->color()) ?>;"></span>
                            <?= e($status->label()) ?>
                        </span>
                    </td>
                    <td class="muted">
                        <?= $ro['technician_name']
                                ? e($ro['technician_name'])
                                : '<em class="unassigned">Unassigned</em>' ?>
                    </td>
                    <td class="chevron">›</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
