$(function () {
    // ---- RO modal ----
    var $modal = $('#ro-modal');
    var $openBtn = $('#open-modal-btn');
    var $cancelBtn = $('#cancel-btn');

    if ($modal.length && $openBtn.length && $cancelBtn.length) {
        $openBtn.on('click', function (e) {
            e.preventDefault();
            $modal.css('display', 'flex');
        });

        $cancelBtn.on('click', function () {
            $modal.css('display', 'none');
        });

        $modal.on('click', function (e) {
            if (e.target === this) $modal.css('display', 'none');
        });
    }

    // ---- Diagnosis modal ----
    var $diagModal = $('#diagnosis-modal');
    var $diagOpenBtn = $('#advance-to-diagnosis-btn');
    var $diagCancelBtn = $('#diagnosis-cancel-btn');

    if ($diagModal.length && $diagOpenBtn.length && $diagCancelBtn.length) {
        $diagOpenBtn.on('click', function () {
            $diagModal.css('display', 'flex');
        });
        $diagCancelBtn.on('click', function () {
            $diagModal.css('display', 'none');
        });
        $diagModal.on('click', function (e) {
            if (e.target === this) $diagModal.css('display', 'none');
        });
    }

    // ---- Tabs ----
    $('.tab').on('click', function () {
        var target = $(this).data('tab');
        $('.tab').removeClass('is-active');
        $(this).addClass('is-active');
        $('.tab-panel').each(function () {
            $(this).css('display', $(this).data('panel') === target ? 'block' : 'none');
        });
    });

    // ---- Inspection findings form ----
    (function () {
        var $addBtn = $('#add-finding-btn');
        var $list = $('#findings-list');
        var $template = $('#finding-template');
        var $form = $('#inspect-form');
        var $submitBtn = $('#submit-inspection-btn');

        if (!$addBtn.length || !$list.length || !$template.length) return;

        var nextIndex = 1; // row 0 already exists in HTML

        $addBtn.on('click', function () {
            var html = $template.html().replaceAll('__INDEX__', nextIndex);
            $list.append(html);
            nextIndex++;
        });

        // Event delegation: catches clicks on current AND future remove buttons
        $list.on('click', '.finding-remove', function () {
            if ($list.find('.finding-row').length === 1) {
                alert('At least one finding is required.');
                return;
            }
            $(this).closest('.finding-row').remove();
        });

        // Prevent double-submit
        if ($form.length && $submitBtn.length) {
            $form.on('submit', function () {
                $submitBtn.prop('disabled', true).text('Submitting…');
            });
        }
    })();

});