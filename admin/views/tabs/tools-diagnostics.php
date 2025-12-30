<?php
/**
 * Tools Tab: Diagnostics
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<p class="description" style="font-size: 14px;">
    <?php esc_html_e('Run comprehensive health checks to verify all plugin systems are functioning correctly.', 'formflow-lite'); ?>
</p>

<!-- Quick Health Status -->
<div class="fffl-diagnostics-quick-status" id="ff-quick-status">
    <div class="ff-quick-status-loading">
        <span class="spinner is-active" style="float: none; margin: 0 10px 0 0;"></span>
        <?php esc_html_e('Checking system health...', 'formflow-lite'); ?>
    </div>
</div>

<!-- Test Controls -->
<div class="ff-card" style="margin-top: 20px;">
    <h2 style="margin-top: 0;"><?php esc_html_e('Run Full Diagnostics', 'formflow-lite'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="fffl-test-instance"><?php esc_html_e('Test Instance', 'formflow-lite'); ?></label>
            </th>
            <td>
                <select id="fffl-test-instance" style="min-width: 300px;">
                    <option value=""><?php esc_html_e('-- No instance (skip API tests) --', 'formflow-lite'); ?></option>
                    <?php foreach ($instances as $instance): ?>
                        <option value="<?php echo esc_attr($instance['id']); ?>">
                            <?php echo esc_html($instance['name']); ?>
                            <?php if ($instance['settings']['demo_mode'] ?? false): ?>
                                (Demo Mode)
                            <?php endif; ?>
                            <?php if (!$instance['is_active']): ?>
                                (Inactive)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    <?php esc_html_e('Select a form instance to include API connectivity tests.', 'formflow-lite'); ?>
                </p>
            </td>
        </tr>
    </table>

    <p>
        <button type="button" id="ff-run-diagnostics" class="button button-primary button-hero">
            <span class="dashicons dashicons-admin-tools" style="margin-top: 4px;"></span>
            <?php esc_html_e('Run Full Diagnostics', 'formflow-lite'); ?>
        </button>
    </p>
</div>

<!-- Results Container -->
<div id="fffl-diagnostics-results" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h2 style="margin: 0;"><?php esc_html_e('Diagnostic Results', 'formflow-lite'); ?></h2>
        <button type="button" id="ff-export-diagnostics" class="button">
            <span class="dashicons dashicons-download" style="margin-top: 4px;"></span>
            <?php esc_html_e('Export Results', 'formflow-lite'); ?>
        </button>
    </div>

    <!-- Summary -->
    <div id="ff-results-summary" class="ff-results-summary" style="margin-bottom: 20px;"></div>

    <!-- Detailed Results -->
    <div id="ff-results-details" class="ff-card"></div>
</div>

<!-- Debug Tools -->
<div class="ff-card" style="margin-top: 20px;">
    <h2><?php esc_html_e('Debug Tools', 'formflow-lite'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Rate Limiting', 'formflow-lite'); ?></th>
            <td>
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('fffl_clear_rate_limits'); ?>
                    <button type="submit" name="fffl_clear_rate_limits" class="button">
                        <?php esc_html_e('Clear All Rate Limits', 'formflow-lite'); ?>
                    </button>
                </form>
                <p class="description">
                    <?php esc_html_e('Clear all IP-based rate limiting to resolve "Too many requests" errors.', 'formflow-lite'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Transient Cache', 'formflow-lite'); ?></th>
            <td>
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('fffl_clear_transients'); ?>
                    <button type="submit" name="fffl_clear_transients" class="button">
                        <?php esc_html_e('Clear Plugin Transients', 'formflow-lite'); ?>
                    </button>
                </form>
                <p class="description">
                    <?php esc_html_e('Clear cached data like API health status and analytics summaries.', 'formflow-lite'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    // Store diagnostic data for export
    var diagnosticData = null;

    // Quick health check on page load
    $.post(fffl_admin.ajax_url, {
        action: 'fffl_quick_health_check',
        nonce: fffl_admin.nonce
    }, function(response) {
        if (response.success) {
            var status = response.data;
            var statusClass = status.overall === 'healthy' ? 'ff-status-healthy' : (status.overall === 'warning' ? 'ff-status-warning' : 'ff-status-error');
            var statusIcon = status.overall === 'healthy' ? 'yes-alt' : (status.overall === 'warning' ? 'warning' : 'dismiss');
            var statusText = status.overall === 'healthy' ? '<?php echo esc_js(__('All Systems Operational', 'formflow-lite')); ?>' :
                           (status.overall === 'warning' ? '<?php echo esc_js(__('Some Issues Detected', 'formflow-lite')); ?>' :
                           '<?php echo esc_js(__('Critical Issues Found', 'formflow-lite')); ?>');

            var html = '<div class="ff-quick-status-result ' + statusClass + '">';
            html += '<span class="dashicons dashicons-' + statusIcon + '"></span>';
            html += '<strong>' + statusText + '</strong>';
            if (status.issues && status.issues.length > 0) {
                html += '<p style="margin: 5px 0 0;">' + status.issues.join(', ') + '</p>';
            }
            html += '</div>';

            $('#ff-quick-status').html(html);
        }
    });

    // Run full diagnostics
    $('#ff-run-diagnostics').on('click', function() {
        var $btn = $(this);
        var instanceId = $('#fffl-test-instance').val();

        $btn.prop('disabled', true).find('.dashicons').addClass('ff-spin');

        $.post(fffl_admin.ajax_url, {
            action: 'fffl_run_diagnostics',
            nonce: fffl_admin.nonce,
            instance_id: instanceId
        }, function(response) {
            $btn.prop('disabled', false).find('.dashicons').removeClass('ff-spin');

            if (response.success) {
                diagnosticData = response.data;
                renderDiagnosticResults(response.data);
                $('#fffl-diagnostics-results').show();
            } else {
                alert(response.data.message || '<?php echo esc_js(__('Diagnostics failed.', 'formflow-lite')); ?>');
            }
        });
    });

    function renderDiagnosticResults(data) {
        // Use summary from backend or calculate from tests
        var passed = data.summary ? data.summary.passed : 0;
        var failed = data.summary ? data.summary.failed : 0;
        var warnings = data.summary ? data.summary.warnings : 0;

        var summary = '<div class="ff-results-grid">';
        summary += '<div class="ff-result-stat ff-stat-pass"><span class="count" style="color: #46b450; font-size: 48px; font-weight: bold;">' + passed + '</span><span class="label"><?php echo esc_js(__('Passed', 'formflow-lite')); ?></span></div>';
        summary += '<div class="ff-result-stat ff-stat-fail"><span class="count" style="color: #dc3232; font-size: 48px; font-weight: bold;">' + failed + '</span><span class="label"><?php echo esc_js(__('Failed', 'formflow-lite')); ?></span></div>';
        if (warnings > 0) {
            summary += '<div class="ff-result-stat ff-stat-warning"><span class="count" style="color: #ffb900; font-size: 48px; font-weight: bold;">' + warnings + '</span><span class="label"><?php echo esc_js(__('Warnings', 'formflow-lite')); ?></span></div>';
        }
        summary += '</div>';

        $('#ff-results-summary').html(summary);

        // Group tests by category
        var tests = data.tests || [];
        var grouped = {};
        for (var i = 0; i < tests.length; i++) {
            var test = tests[i];
            var cat = test.category || 'General';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(test);
        }

        var details = '';
        for (var category in grouped) {
            details += '<h3>' + category + '</h3>';
            details += '<table class="widefat striped">';
            details += '<thead><tr><th><?php echo esc_js(__('Test', 'formflow-lite')); ?></th><th><?php echo esc_js(__('Status', 'formflow-lite')); ?></th><th><?php echo esc_js(__('Message', 'formflow-lite')); ?></th></tr></thead>';
            details += '<tbody>';

            var catTests = grouped[category];
            for (var i = 0; i < catTests.length; i++) {
                var test = catTests[i];
                var icon, cls;
                if (test.status === 'passed') {
                    icon = 'yes';
                    cls = 'ff-status-pass';
                } else if (test.status === 'warning') {
                    icon = 'warning';
                    cls = 'ff-status-warning';
                } else {
                    icon = 'no';
                    cls = 'ff-status-fail';
                }
                details += '<tr>';
                details += '<td>' + test.name + '</td>';
                details += '<td><span class="dashicons dashicons-' + icon + ' ' + cls + '" style="color: ' + (test.status === 'passed' ? '#46b450' : (test.status === 'warning' ? '#ffb900' : '#dc3232')) + ';"></span></td>';
                details += '<td>' + (test.message || '') + '</td>';
                details += '</tr>';
            }

            details += '</tbody></table>';
        }

        $('#ff-results-details').html(details);
    }

    // Export diagnostics results
    $('#ff-export-diagnostics').on('click', function() {
        if (!diagnosticData) {
            alert('<?php echo esc_js(__('No diagnostic data to export. Please run diagnostics first.', 'formflow-lite')); ?>');
            return;
        }

        // Build export content
        var exportLines = [];
        var timestamp = new Date().toISOString();

        exportLines.push('FormFlow Lite - Diagnostic Report');
        exportLines.push('================================');
        exportLines.push('Generated: ' + timestamp);
        exportLines.push('Site URL: ' + window.location.origin);
        exportLines.push('');

        // Summary
        exportLines.push('SUMMARY');
        exportLines.push('-------');
        if (diagnosticData.summary) {
            exportLines.push('Passed: ' + diagnosticData.summary.passed);
            exportLines.push('Failed: ' + diagnosticData.summary.failed);
            exportLines.push('Warnings: ' + diagnosticData.summary.warnings);
        }
        exportLines.push('');

        // System Info
        exportLines.push('SYSTEM INFORMATION');
        exportLines.push('------------------');
        exportLines.push('FormFlow Lite Version: ' + (diagnosticData.version || 'Unknown'));
        exportLines.push('PHP Version: ' + (diagnosticData.php_version || 'Unknown'));
        exportLines.push('WordPress Version: ' + (diagnosticData.wp_version || 'Unknown'));
        exportLines.push('Diagnostic Timestamp: ' + (diagnosticData.timestamp || 'Unknown'));
        exportLines.push('');

        // Test Results grouped by category
        var tests = diagnosticData.tests || [];
        var grouped = {};
        for (var i = 0; i < tests.length; i++) {
            var test = tests[i];
            var cat = test.category || 'General';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(test);
        }

        exportLines.push('TEST RESULTS');
        exportLines.push('------------');

        for (var category in grouped) {
            exportLines.push('');
            exportLines.push('[' + category + ']');
            var catTests = grouped[category];
            for (var i = 0; i < catTests.length; i++) {
                var test = catTests[i];
                var statusSymbol = test.status === 'passed' ? '✓' : (test.status === 'warning' ? '!' : '✗');
                exportLines.push('  ' + statusSymbol + ' ' + test.name + ': ' + test.status.toUpperCase());
                if (test.message) {
                    exportLines.push('    ' + test.message);
                }
            }
        }

        // Create download
        var content = exportLines.join('\n');
        var blob = new Blob([content], { type: 'text/plain' });
        var url = URL.createObjectURL(blob);

        var a = document.createElement('a');
        a.href = url;
        a.download = 'formflow-diagnostics-' + timestamp.replace(/[:.]/g, '-').slice(0, 19) + '.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
});
</script>
