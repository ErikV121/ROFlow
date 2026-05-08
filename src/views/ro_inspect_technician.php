<div class="ro-detail-header">
    <div>
        <a href="<?= e(url('/dashboard')) ?>" class="back-link">‹ My Queue</a>
        <div class="ro-detail-title-row">
            <h1 class="ro-detail-number"><?= e($ro->getRoNumber()) ?></h1>
        </div>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-card">
        <div class="detail-card__title">CUSTOMER</div>
        <div class="detail-row"><span>Name</span><strong><?= e($customer->getFullName()) ?></strong></div>
        <div class="detail-row"><span>Phone</span><strong><?= e($customer->getPhone() ?? '—') ?></strong></div>
    </div>
    <div class="detail-card">
        <div class="detail-card__title">VEHICLE</div>
        <div class="detail-row"><span>Vehicle</span><strong><?= e($vehicle->getDisplayName()) ?></strong></div>
        <div class="detail-row"><span>Mileage</span><strong><?= e(number_format($ro->getMileage())) ?> mi</strong></div>
    </div>
</div>

<div class="detail-card detail-card--full">
    <div class="detail-card__title">CUSTOMER COMPLAINT</div>
    <p><?= e($ro->getComplaint()) ?></p>
</div>

<form method="post" action="<?= e(inspection_url($ro->getId())) ?>" id="inspect-form">
    <?= csrf_field() ?>
    <input type="hidden" name="ro_id" value="<?= e($ro->getId()) ?>">

    <div class="detail-card detail-card--full">
        <div class="detail-card__title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>FINDINGS</span>
            <button type="button" id="add-finding-btn" class="btn btn--secondary">+ Add finding</button>
        </div>

        <div id="findings-list">
            <div class="finding-row">
                <div class="form-field">
                    <label class="form-field__label">Title</label>
                    <input type="text" name="findings[0][title]" maxlength="100">
                </div>
                <div class="form-field">
                    <label class="form-field__label">Description</label>
                    <textarea name="findings[0][description]" rows="2"></textarea>
                </div>
                <button type="button" class="finding-remove" aria-label="Remove">×</button>
            </div>
        </div>
    </div>

    <template id="finding-template">
        <div class="finding-row">
            <div class="form-field">
                <label class="form-field__label">Title</label>
                <input type="text" name="findings[__INDEX__][title]" maxlength="100">
            </div>
            <div class="form-field">
                <label class="form-field__label">Description</label>
                <textarea name="findings[__INDEX__][description]" rows="2"></textarea>
            </div>
            <button type="button" class="finding-remove" aria-label="Remove">×</button>
        </div>
    </template>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:18px;">
        <a href="<?= e(url('/dashboard')) ?>" class="btn btn--secondary">Cancel</a>
        <button type="submit" class="btn btn--primary" id="submit-inspection-btn">Submit Inspection</button>
    </div>
</form>
