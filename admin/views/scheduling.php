<?php
/**
 * Admin View: Schedule Availability
 *
 * Displays available scheduling slots and promo codes from the API.
 */

if (!defined('ABSPATH')) {
    exit;
}

$time_labels = [
    'am' => '8:00 AM - 11:00 AM',
    'md' => '11:00 AM - 2:00 PM',
    'pm' => '2:00 PM - 5:00 PM',
    'ev' => '5:00 PM - 8:00 PM'
];
?>

<div class="wrap ff-admin-wrap">
    <h1><?php esc_html_e('Schedule Availability', 'formflow-lite'); ?></h1>

    <!-- Instance Selector -->
    <div class="ff-card">
        <form method="get" action="">
            <input type="hidden" name="page" value="fffl-scheduling">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="instance_id"><?php esc_html_e('Select Form Instance', 'formflow-lite'); ?></label>
                    </th>
                    <td>
                        <select name="instance_id" id="instance_id" onchange="this.form.submit()">
                            <option value=""><?php esc_html_e('-- Select Instance --', 'formflow-lite'); ?></option>
                            <?php foreach ($instances as $inst) : ?>
                                <option value="<?php echo esc_attr($inst['id']); ?>"
                                    <?php selected($instance_id, $inst['id']); ?>>
                                    <?php echo esc_html($inst['name']); ?>
                                    <?php if (!$inst['is_active']) echo ' (Inactive)'; ?>
                                    <?php if ($inst['settings']['demo_mode'] ?? false) echo ' [DEMO]'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php if ($instance && empty($instance['settings']['demo_mode'])) : ?>
                <tr>
                    <th scope="row">
                        <label for="test_account"><?php esc_html_e('Test Account Number', 'formflow-lite'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="test_account" id="test_account"
                               value="<?php echo esc_attr($test_account ?? ''); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e('Enter account number to check availability', 'formflow-lite'); ?>">
                        <p class="description">
                            <?php esc_html_e('The IntelliSource API requires an account number to display scheduling availability.', 'formflow-lite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="start_date"><?php esc_html_e('Date Range', 'formflow-lite'); ?></label>
                    </th>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div>
                                <label for="start_date" style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;">Start</label>
                                <input type="date" name="start_date" id="start_date"
                                       value="<?php echo esc_attr($custom_start_date ?? ''); ?>"
                                       min="<?php echo esc_attr(date('Y-m-d')); ?>">
                            </div>
                            <span style="margin-top: 18px;">to</span>
                            <div>
                                <label for="end_date" style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;">End</label>
                                <input type="date" name="end_date" id="end_date"
                                       value="<?php echo esc_attr($custom_end_date ?? ''); ?>"
                                       min="<?php echo esc_attr(date('Y-m-d')); ?>">
                            </div>
                        </div>
                        <p class="description" style="margin-top: 8px;">
                            <?php esc_html_e('Leave blank to use defaults. The API typically returns ~15 days of availability.', 'formflow-lite'); ?>
                        </p>
                        <div style="margin-top: 8px; display: flex; gap: 8px;">
                            <button type="submit" class="button button-secondary">
                                <?php esc_html_e('Check Availability', 'formflow-lite'); ?>
                            </button>
                            <button type="button" class="button" id="fffl-run-diagnostics"
                                    data-instance-id="<?php echo esc_attr($instance['id']); ?>">
                                <span class="dashicons dashicons-admin-tools" style="margin-top: 3px;"></span>
                                <?php esc_html_e('Run API Diagnostics', 'formflow-lite'); ?>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </form>
    </div>

    <?php if ($instance) : ?>
        <div class="fffl-scheduling-layout">
            <!-- Schedule Availability Card -->
            <div class="ff-card ff-card-wide">
                <h2><?php esc_html_e('Available Installation Slots', 'formflow-lite'); ?></h2>

                <?php
                // Get blocked dates and capacity limits
                $scheduling_settings = $instance['settings']['scheduling'] ?? [];
                $blocked_dates = [];
                $blocked_dates_labels = [];
                if (!empty($scheduling_settings['blocked_dates'])) {
                    foreach ($scheduling_settings['blocked_dates'] as $blocked) {
                        if (!empty($blocked['date'])) {
                            $blocked_dates[] = $blocked['date'];
                            if (!empty($blocked['label'])) {
                                $blocked_dates_labels[$blocked['date']] = $blocked['label'];
                            }
                        }
                    }
                }
                $capacity_limits = $scheduling_settings['capacity_limits'] ?? [];
                $capacity_enabled = !empty($capacity_limits['enabled']);
                ?>

                <?php if (!empty($blocked_dates)) : ?>
                    <div class="notice notice-warning inline" style="margin: 10px 0;">
                        <p>
                            <strong><?php esc_html_e('Blocked dates:', 'formflow-lite'); ?></strong>
                            <?php
                            $blocked_display = [];
                            foreach ($blocked_dates as $bd) {
                                $label = $blocked_dates_labels[$bd] ?? '';
                                $formatted = date('M j, Y', strtotime($bd));
                                $blocked_display[] = $label ? "{$formatted} ({$label})" : $formatted;
                            }
                            echo esc_html(implode(', ', $blocked_display));
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($capacity_enabled) : ?>
                    <div class="notice notice-info inline" style="margin: 10px 0;">
                        <p>
                            <strong><?php esc_html_e('Custom capacity limits active:', 'formflow-lite'); ?></strong>
                            <?php
                            $limits_display = [];
                            $slot_names = ['am' => 'AM', 'md' => 'Mid-Day', 'pm' => 'PM', 'ev' => 'Evening'];
                            foreach (['am', 'md', 'pm', 'ev'] as $slot) {
                                if (isset($capacity_limits[$slot]) && $capacity_limits[$slot] !== '') {
                                    $limits_display[] = $slot_names[$slot] . ': ' . (int)$capacity_limits[$slot];
                                }
                            }
                            echo esc_html(implode(', ', $limits_display) ?: 'None');
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($schedule_data['error'])) : ?>
                    <div class="notice notice-error inline">
                        <p><?php echo esc_html($schedule_data['error']); ?></p>
                    </div>
                <?php elseif (isset($schedule_data['needs_account'])) : ?>
                    <div class="notice notice-info inline">
                        <p>
                            <span class="dashicons dashicons-info" style="color: #00a0d2;"></span>
                            <?php echo esc_html($schedule_data['message']); ?>
                        </p>
                    </div>
                <?php elseif (!empty($schedule_data['slots'])) : ?>
                    <?php
                    // Display region/account info banner
                    $region = $schedule_data['region'] ?? '';
                    $region_name = $schedule_data['region_name'] ?? '';
                    $fsr_no = $schedule_data['fsr_no'] ?? '';
                    $address = $schedule_data['address'] ?? [];
                    ?>
                    <?php if (!empty($region) || !empty($fsr_no)) : ?>
                    <div class="fffl-region-banner">
                        <div class="fffl-region-info">
                            <?php if (!empty($region_name)) : ?>
                                <span class="fffl-region-name">
                                    <span class="dashicons dashicons-location"></span>
                                    <strong><?php echo esc_html($region_name); ?></strong>
                                    <?php if (!empty($region) && $region !== $region_name) : ?>
                                        <span class="fffl-region-code">(<?php echo esc_html($region); ?>)</span>
                                    <?php endif; ?>
                                </span>
                            <?php elseif (!empty($region)) : ?>
                                <span class="fffl-region-name">
                                    <span class="dashicons dashicons-location"></span>
                                    <strong>Region: <?php echo esc_html($region); ?></strong>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($fsr_no)) : ?>
                                <span class="fffl-fsr-no">FSR#: <?php echo esc_html($fsr_no); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($address['city']) && !empty($address['state'])) : ?>
                        <div class="fffl-service-address">
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php
                            $addr_parts = [];
                            if (!empty($address['street'])) $addr_parts[] = $address['street'];
                            if (!empty($address['city'])) $addr_parts[] = $address['city'];
                            if (!empty($address['state'])) $addr_parts[] = $address['state'];
                            if (!empty($address['zip'])) $addr_parts[] = $address['zip'];
                            echo esc_html(implode(', ', $addr_parts));
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php
                    // Build slots data for calendar
                    $slots_by_date = [];
                    foreach ($schedule_data['slots'] as $slot) {
                        $date = $slot['date'];
                        $normalized_date = date('Y-m-d', strtotime($date));
                        $times = $slot['times'] ?? [];

                        $am_cap = $times['am']['capacity'] ?? 0;
                        $md_cap = $times['md']['capacity'] ?? 0;
                        $pm_cap = $times['pm']['capacity'] ?? 0;
                        $ev_cap = $times['ev']['capacity'] ?? 0;

                        // Apply capacity limits
                        if ($capacity_enabled) {
                            if (isset($capacity_limits['am']) && $capacity_limits['am'] !== '') {
                                $am_cap = min($am_cap, (int)$capacity_limits['am']);
                            }
                            if (isset($capacity_limits['md']) && $capacity_limits['md'] !== '') {
                                $md_cap = min($md_cap, (int)$capacity_limits['md']);
                            }
                            if (isset($capacity_limits['pm']) && $capacity_limits['pm'] !== '') {
                                $pm_cap = min($pm_cap, (int)$capacity_limits['pm']);
                            }
                            if (isset($capacity_limits['ev']) && $capacity_limits['ev'] !== '') {
                                $ev_cap = min($ev_cap, (int)$capacity_limits['ev']);
                            }
                        }

                        $total = $am_cap + $md_cap + $pm_cap + $ev_cap;
                        $is_blocked = in_array($normalized_date, $blocked_dates);

                        $slots_by_date[$normalized_date] = [
                            'formatted' => $slot['formatted_date'] ?? date('l, F j', strtotime($date)),
                            'am' => $am_cap,
                            'md' => $md_cap,
                            'pm' => $pm_cap,
                            'ev' => $ev_cap,
                            'total' => $total,
                            'blocked' => $is_blocked,
                            'blocked_label' => $blocked_dates_labels[$normalized_date] ?? ''
                        ];
                    }

                    // Get calendar month range
                    $first_slot_date = array_key_first($slots_by_date);
                    $last_slot_date = array_key_last($slots_by_date);
                    $calendar_start = date('Y-m-01', strtotime($first_slot_date));
                    $calendar_end = date('Y-m-t', strtotime($last_slot_date));
                    ?>

                    <div class="fffl-calendar-wrap">
                        <div class="fffl-calendar-container">
                            <?php
                            // Generate calendars for each month in range
                            $current_month = new DateTime($calendar_start);
                            $end_month = new DateTime($calendar_end);

                            while ($current_month <= $end_month) :
                                $month_start = $current_month->format('Y-m-01');
                                $month_end = $current_month->format('Y-m-t');
                                $days_in_month = (int)$current_month->format('t');
                                $first_day_of_week = (int)date('w', strtotime($month_start)); // 0=Sun, 6=Sat
                            ?>
                            <div class="fffl-calendar-month">
                                <div class="fffl-calendar-header">
                                    <strong><?php echo esc_html($current_month->format('F Y')); ?></strong>
                                </div>
                                <div class="fffl-calendar-grid">
                                    <div class="fffl-calendar-weekdays">
                                        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                                    </div>
                                    <div class="fffl-calendar-days">
                                        <?php
                                        // Empty cells for days before first of month
                                        for ($i = 0; $i < $first_day_of_week; $i++) {
                                            echo '<span class="fffl-day fffl-day-empty"></span>';
                                        }

                                        // Days of month
                                        for ($day = 1; $day <= $days_in_month; $day++) {
                                            $date_str = $current_month->format('Y-m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                                            $has_slots = isset($slots_by_date[$date_str]) && $slots_by_date[$date_str]['total'] > 0;
                                            $is_blocked = isset($slots_by_date[$date_str]) && $slots_by_date[$date_str]['blocked'];
                                            $slot_data = $slots_by_date[$date_str] ?? null;

                                            $day_class = 'fffl-day';
                                            if ($is_blocked) {
                                                $day_class .= ' fffl-day-blocked';
                                            } elseif ($has_slots) {
                                                $day_class .= ' fffl-day-available';
                                            } elseif ($slot_data) {
                                                $day_class .= ' fffl-day-no-slots';
                                            }

                                            if ($slot_data && !$is_blocked) {
                                                $data_attr = ' data-date="' . esc_attr($date_str) . '"';
                                                $data_attr .= ' data-formatted="' . esc_attr($slot_data['formatted']) . '"';
                                                $data_attr .= ' data-am="' . esc_attr($slot_data['am']) . '"';
                                                $data_attr .= ' data-md="' . esc_attr($slot_data['md']) . '"';
                                                $data_attr .= ' data-pm="' . esc_attr($slot_data['pm']) . '"';
                                                $data_attr .= ' data-ev="' . esc_attr($slot_data['ev']) . '"';
                                                $data_attr .= ' data-total="' . esc_attr($slot_data['total']) . '"';
                                            } else {
                                                $data_attr = '';
                                            }

                                            echo '<span class="' . esc_attr($day_class) . '"' . $data_attr . '>' . $day . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $current_month->modify('+1 month');
                            endwhile;
                            ?>
                        </div>

                        <!-- Time slots panel -->
                        <div class="fffl-timeslots-panel" id="fffl-timeslots-panel" style="display: none;">
                            <div class="fffl-timeslots-header">
                                <strong id="fffl-selected-date"></strong>
                                <button type="button" class="fffl-close-panel" id="fffl-close-panel">&times;</button>
                            </div>
                            <div class="fffl-timeslots-content">
                                <div class="fffl-timeslot" data-slot="am">
                                    <span class="fffl-timeslot-label">8:00 AM - 11:00 AM</span>
                                    <span class="fffl-timeslot-capacity" id="fffl-cap-am">-</span>
                                </div>
                                <div class="fffl-timeslot" data-slot="md">
                                    <span class="fffl-timeslot-label">11:00 AM - 2:00 PM</span>
                                    <span class="fffl-timeslot-capacity" id="fffl-cap-md">-</span>
                                </div>
                                <div class="fffl-timeslot" data-slot="pm">
                                    <span class="fffl-timeslot-label">2:00 PM - 5:00 PM</span>
                                    <span class="fffl-timeslot-capacity" id="fffl-cap-pm">-</span>
                                </div>
                                <div class="fffl-timeslot" data-slot="ev">
                                    <span class="fffl-timeslot-label">5:00 PM - 8:00 PM</span>
                                    <span class="fffl-timeslot-capacity" id="fffl-cap-ev">-</span>
                                </div>
                            </div>
                            <div class="fffl-timeslots-total">
                                <strong><?php esc_html_e('Total Available:', 'formflow-lite'); ?></strong>
                                <span id="fffl-cap-total">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="ff-schedule-legend" style="margin-top: 15px;">
                        <span class="ff-legend-item">
                            <span class="ff-legend-dot fffl-legend-available"></span>
                            <?php esc_html_e('Available', 'formflow-lite'); ?>
                        </span>
                        <span class="ff-legend-item">
                            <span class="ff-legend-dot fffl-legend-none"></span>
                            <?php esc_html_e('No Availability', 'formflow-lite'); ?>
                        </span>
                        <?php if (!empty($blocked_dates)) : ?>
                        <span class="ff-legend-item">
                            <span class="ff-legend-dot fffl-legend-blocked"></span>
                            <?php esc_html_e('Blocked Date', 'formflow-lite'); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                <?php else : ?>
                    <p class="ff-empty-state">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('No scheduling slots available or unable to fetch from API.', 'formflow-lite'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Promo Codes Card -->
            <div class="ff-card">
                <h2><?php esc_html_e('Promotional Codes', 'formflow-lite'); ?></h2>
                <?php if (!empty($promo_codes)) : ?>
                    <p class="description"><?php esc_html_e('These codes are available from the API for "How did you hear about us?" dropdown:', 'formflow-lite'); ?></p>
                    <div class="ff-promo-codes-scroll" style="max-height: 320px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">
                        <table class="widefat striped" style="border: none;">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Code', 'formflow-lite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($promo_codes as $code) : ?>
                                    <tr>
                                        <td><code><?php echo esc_html($code); ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="description" style="margin-top: 8px;">
                        <?php printf(esc_html__('%d codes available', 'formflow-lite'), count($promo_codes)); ?>
                    </p>
                <?php else : ?>
                    <p class="ff-empty-state">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('No promo codes available or unable to fetch from API.', 'formflow-lite'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>
        <div class="ff-card">
            <p class="ff-empty-state">
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e('Please select a form instance to view scheduling availability.', 'formflow-lite'); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Diagnostics Results Panel -->
    <div id="fffl-diagnostics-panel" class="ff-card" style="display: none; margin-top: 20px;">
        <h2>
            <span class="dashicons dashicons-admin-tools"></span>
            <?php esc_html_e('API Diagnostics Report', 'formflow-lite'); ?>
        </h2>
        <div id="fffl-diagnostics-results"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#fffl-run-diagnostics').on('click', function() {
        var $btn = $(this);
        var instanceId = $btn.data('instance-id');
        var testAccount = $('#test_account').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        $btn.prop('disabled', true).text('Running diagnostics...');
        $('#fffl-diagnostics-panel').show();
        $('#fffl-diagnostics-results').html('<p><span class="spinner is-active" style="float:none;margin:0 10px 0 0;"></span>Running diagnostics...</p>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fffl_scheduling_diagnostics',
                nonce: fffl_admin.nonce,
                instance_id: instanceId,
                test_account: testAccount,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    renderDiagnostics(response.data);
                } else {
                    $('#fffl-diagnostics-results').html(
                        '<div class="notice notice-error inline"><p>' +
                        (response.data.message || 'Diagnostics failed') +
                        '</p></div>'
                    );
                }
            },
            error: function() {
                $('#fffl-diagnostics-results').html(
                    '<div class="notice notice-error inline"><p>Network error running diagnostics.</p></div>'
                );
            },
            complete: function() {
                $btn.prop('disabled', false).html(
                    '<span class="dashicons dashicons-admin-tools" style="margin-top: 3px;"></span> Run API Diagnostics'
                );
            }
        });
    });

    function renderDiagnostics(data) {
        var html = '';

        // Instance Info
        html += '<h3>Instance Configuration</h3>';
        html += '<table class="widefat striped" style="margin-bottom: 20px;">';
        html += '<tr><th style="width:200px;">Instance</th><td>' + escapeHtml(data.instance.name) + ' (ID: ' + data.instance.id + ')</td></tr>';
        html += '<tr><th>API Endpoint</th><td><code>' + escapeHtml(data.instance.api_endpoint) + '</code></td></tr>';
        html += '<tr><th>Mode</th><td>' + (data.instance.demo_mode ? '<span style="color:#0073aa;">Demo Mode</span>' : (data.instance.test_mode ? '<span style="color:#dba617;">Test Mode</span>' : '<span style="color:#46b450;">Live Mode</span>')) + '</td></tr>';
        html += '</table>';

        // Test Results
        html += '<h3>Test Results</h3>';
        html += '<table class="widefat striped" style="margin-bottom: 20px;">';
        html += '<thead><tr><th style="width:200px;">Test</th><th>Status</th><th>Details</th></tr></thead><tbody>';

        for (var testName in data.tests) {
            var test = data.tests[testName];
            var statusIcon = getStatusIcon(test.status);
            var details = test.message;

            // Add extra info if available
            if (test.count !== undefined) {
                details += ' (Sample: ' + (test.sample ? test.sample.join(', ') : 'N/A') + ')';
            }
            if (test.fsr_no) {
                details += '<br><small>FSR#: ' + test.fsr_no + '</small>';
            }
            if (test.is_scheduled) {
                details += '<br><small>Has existing appointment</small>';
            }
            if (test.explanation) {
                details += '<br><small style="color:#666;">' + test.explanation + '</small>';
            }

            html += '<tr>';
            html += '<td><strong>' + formatTestName(testName) + '</strong></td>';
            html += '<td>' + statusIcon + '</td>';
            html += '<td>' + details + '</td>';
            html += '</tr>';
        }
        html += '</tbody></table>';

        // Raw API Info
        html += '<h3>API Call Details</h3>';
        html += '<div style="background:#f5f5f5;padding:15px;border-radius:4px;font-family:monospace;font-size:12px;overflow-x:auto;">';
        html += '<pre style="margin:0;white-space:pre-wrap;">' + escapeHtml(JSON.stringify(data.raw_responses, null, 2)) + '</pre>';
        html += '</div>';

        $('#fffl-diagnostics-results').html(html);
    }

    function getStatusIcon(status) {
        switch(status) {
            case 'success': return '<span style="color:#46b450;">✓ Success</span>';
            case 'error': return '<span style="color:#dc3232;">✗ Error</span>';
            case 'warning': return '<span style="color:#dba617;">⚠ Warning</span>';
            case 'skipped': return '<span style="color:#999;">○ Skipped</span>';
            case 'info': return '<span style="color:#0073aa;">ℹ Info</span>';
            default: return status;
        }
    }

    function formatTestName(name) {
        return name.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Calendar day click handler
    $(document).on('click', '.fffl-day[data-date]', function() {
        var $day = $(this);
        var date = $day.data('date');
        var formatted = $day.data('formatted');
        var am = parseInt($day.data('am')) || 0;
        var md = parseInt($day.data('md')) || 0;
        var pm = parseInt($day.data('pm')) || 0;
        var ev = parseInt($day.data('ev')) || 0;
        var total = parseInt($day.data('total')) || 0;

        // Update panel
        $('#fffl-selected-date').text(formatted);
        $('#fffl-cap-am').text(am > 0 ? am + ' available' : 'None').toggleClass('fffl-has-slots', am > 0);
        $('#fffl-cap-md').text(md > 0 ? md + ' available' : 'None').toggleClass('fffl-has-slots', md > 0);
        $('#fffl-cap-pm').text(pm > 0 ? pm + ' available' : 'None').toggleClass('fffl-has-slots', pm > 0);
        $('#fffl-cap-ev').text(ev > 0 ? ev + ' available' : 'None').toggleClass('fffl-has-slots', ev > 0);
        $('#fffl-cap-total').text(total);

        // Update timeslot row highlighting
        $('.fffl-timeslot').each(function() {
            var slot = $(this).data('slot');
            var cap = parseInt($day.data(slot)) || 0;
            $(this).toggleClass('fffl-slot-available', cap > 0);
        });

        // Highlight selected day
        $('.fffl-day').removeClass('fffl-day-selected');
        $day.addClass('fffl-day-selected');

        // Show panel
        $('#fffl-timeslots-panel').show();
    });

    // Close panel
    $('#fffl-close-panel').on('click', function() {
        $('#fffl-timeslots-panel').hide();
        $('.fffl-day').removeClass('fffl-day-selected');
    });
});
</script>

<style>
/* Calendar Styles */
.fffl-calendar-wrap {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-start;
}

.fffl-calendar-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.fffl-calendar-month {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    min-width: 280px;
}

.fffl-calendar-header {
    text-align: center;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.fffl-calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 5px;
}

.fffl-calendar-weekdays span {
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: #666;
    padding: 5px 0;
}

.fffl-calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 3px;
}

.fffl-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    border-radius: 4px;
    background: #f9f9f9;
    color: #999;
    min-height: 32px;
}

.fffl-day-empty {
    background: transparent;
}

.fffl-day-available {
    background: #c6efce;
    color: #006100;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}

.fffl-day-available:hover {
    background: #a3e4b0;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.fffl-day-no-slots {
    background: #f0f0f0;
    color: #999;
    cursor: pointer;
}

.fffl-day-no-slots:hover {
    background: #e5e5e5;
}

.fffl-day-blocked {
    background: #fce4e4;
    color: #c00;
    text-decoration: line-through;
}

.fffl-day-selected {
    outline: 3px solid #2271b1;
    outline-offset: 1px;
}

/* Time slots panel */
.fffl-timeslots-panel {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    min-width: 260px;
    max-width: 300px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.fffl-timeslots-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.fffl-timeslots-header strong {
    font-size: 14px;
}

.fffl-close-panel {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
    padding: 0 5px;
    line-height: 1;
}

.fffl-close-panel:hover {
    color: #c00;
}

.fffl-timeslot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    margin-bottom: 6px;
    background: #f9f9f9;
    border-radius: 4px;
    border-left: 3px solid #ddd;
}

.fffl-timeslot.fffl-slot-available {
    background: #edf7ed;
    border-left-color: #46b450;
}

.fffl-timeslot-label {
    font-size: 13px;
    color: #333;
}

.fffl-timeslot-capacity {
    font-size: 12px;
    color: #666;
}

.fffl-timeslot-capacity.fffl-has-slots {
    color: #006100;
    font-weight: 600;
}

.fffl-timeslots-total {
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
    margin-top: 10px;
    border-top: 1px solid #eee;
    font-size: 14px;
}

/* Legend dots */
.fffl-legend-available {
    background: #c6efce !important;
    border-color: #006100 !important;
}

.fffl-legend-none {
    background: #f0f0f0 !important;
    border-color: #999 !important;
}

.fffl-legend-blocked {
    background: #fce4e4 !important;
    border-color: #c00 !important;
}

/* Region Banner */
.fffl-region-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.fffl-region-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.fffl-region-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}

.fffl-region-name .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.fffl-region-code {
    font-weight: normal;
    opacity: 0.8;
    font-size: 14px;
}

.fffl-fsr-no {
    background: rgba(255,255,255,0.2);
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-family: monospace;
}

.fffl-service-address {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    font-size: 13px;
    opacity: 0.9;
}

.fffl-service-address .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
</style>
