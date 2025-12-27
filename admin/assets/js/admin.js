/**
 * Admin JavaScript for Demo Data Pilot
 */

(function ($) {
    'use strict';

    const DDP = {
        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).on('click', '.ddp-generate-btn', this.handleGenerate);
            $(document).on('click', '.ddp-cleanup-btn', this.handleCleanup);
            $(document).on('click', '#ddp-clear-logs', this.handleClearLogs);
        },

        /**
         * Handle generate button click
         */
        handleGenerate: function (e) {
            e.preventDefault();

            const $btn = $(this);
            const $dataType = $btn.closest('.ddp-data-type');
            const generator = $btn.data('generator');
            const type = $btn.data('type');
            const count = $dataType.find('.ddp-count-input').val();

            if (!generator || !type || !count) {
                DDP.showNotice('error', demoDataPilot.i18n.error);
                return;
            }

            // Disable button and show loading
            $btn.addClass('ddp-button-loading').prop('disabled', true);

            // Show progress bar
            const $progressBar = $dataType.find('.ddp-progress-bar');
            $progressBar.show();
            DDP.updateProgress($progressBar, 0);

            // Make AJAX request
            $.ajax({
                url: demoDataPilot.ajax_url,
                type: 'POST',
                data: {
                    action: 'ddp_generate_data',
                    nonce: demoDataPilot.nonce,
                    generator: generator,
                    type: type,
                    count: count,
                    args: JSON.stringify({})
                },
                success: function (response) {
                    if (response.success) {
                        DDP.updateProgress($progressBar, 100);
                        DDP.showNotice('success', demoDataPilot.i18n.success);

                        // Update count badge
                        const $badge = $dataType.find('.ddp-count-badge');
                        const currentCount = parseInt($badge.text()) || 0;
                        $badge.text((currentCount + response.data.count) + ' generated');

                        // Show cleanup button if hidden
                        if (!$dataType.find('.ddp-cleanup-btn').length) {
                            const $cleanupBtn = $('<button type="button" class="button ddp-cleanup-btn">')
                                .attr('data-generator', generator)
                                .attr('data-type', type)
                                .html('<span class="dashicons dashicons-trash"></span> ' + demoDataPilot.i18n.cleanup_label);
                            $dataType.find('.ddp-button-group').append($cleanupBtn);
                        }
                    } else {
                        DDP.showNotice('error', response.data.message || demoDataPilot.i18n.error);
                        $progressBar.hide();
                    }
                },
                error: function () {
                    DDP.showNotice('error', demoDataPilot.i18n.error);
                    $progressBar.hide();
                },
                complete: function () {
                    $btn.removeClass('ddp-button-loading').prop('disabled', false);
                }
            });
        },

        /**
         * Handle cleanup button click
         */
        handleCleanup: function (e) {
            e.preventDefault();

            if (!confirm(demoDataPilot.i18n.cleanup_confirm)) {
                return;
            }

            const $btn = $(this);
            const generator = $btn.data('generator');
            const type = $btn.data('type');

            if (!generator || !type) {
                DDP.showNotice('error', demoDataPilot.i18n.error);
                return;
            }

            // Disable button and show loading
            $btn.addClass('ddp-button-loading').prop('disabled', true);

            // Make AJAX request
            $.ajax({
                url: demoDataPilot.ajax_url,
                type: 'POST',
                data: {
                    action: 'ddp_cleanup_data',
                    nonce: demoDataPilot.nonce,
                    generator: generator,
                    type: type
                },
                success: function (response) {
                    if (response.success) {
                        DDP.showNotice('success', demoDataPilot.i18n.cleanup_success);

                        // Update count badge
                        const $badge = $btn.closest('.ddp-data-type').find('.ddp-count-badge');
                        $badge.text('0 generated');

                        // Hide cleanup button
                        $btn.remove();
                    } else {
                        DDP.showNotice('error', response.data.message || demoDataPilot.i18n.error);
                    }
                },
                error: function () {
                    DDP.showNotice('error', demoDataPilot.i18n.error);
                },
                complete: function () {
                    $btn.removeClass('ddp-button-loading').prop('disabled', false);
                }
            });
        },

        /**
         * Handle clear logs button click
         */
        handleClearLogs: function (e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to clear all logs?')) {
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: demoDataPilot.ajax_url,
                type: 'POST',
                data: {
                    action: 'ddp_clear_logs',
                    nonce: demoDataPilot.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('.ddp-logs-list').remove();
                        $('.ddp-logs-section').append('<p class="ddp-no-logs">No activity yet. Start generating data to see logs here.</p>');
                        $btn.remove();
                        DDP.showNotice('success', 'Logs cleared successfully!');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Update progress bar
         */
        updateProgress: function ($progressBar, percentage) {
            const $fill = $progressBar.find('.ddp-progress-fill');
            const $text = $progressBar.find('.ddp-progress-text');

            $fill.css('width', percentage + '%');
            $text.text(Math.round(percentage) + '%');
        },

        /**
         * Show admin notice
         */
        showNotice: function (type, message) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');

            $('.ddp-admin-page').prepend($notice);

            // Auto-dismiss after 5 seconds
            setTimeout(function () {
                $notice.fadeOut(function () {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        DDP.init();
    });

})(jQuery);
