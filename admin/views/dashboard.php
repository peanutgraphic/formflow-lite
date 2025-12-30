<?php
/**
 * Admin Dashboard View
 *
 * Displays overview of form instances and statistics.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap ff-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('FormFlow', 'formflow-lite'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-instance-editor')); ?>" class="page-title-action">
        <?php esc_html_e('Add New Form', 'formflow-lite'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Quick Actions Bar -->
    <div class="ff-quick-actions-bar">
        <div class="ff-quick-actions-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-instance-editor')); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('New Form', 'formflow-lite'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-data&tab=submissions')); ?>" class="button">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('View Data', 'formflow-lite'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-data&tab=analytics')); ?>" class="button">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php esc_html_e('Analytics', 'formflow-lite'); ?>
            </a>
            <?php if (!empty($instances)) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-test')); ?>" class="button">
                <span class="dashicons dashicons-visibility"></span>
                <?php esc_html_e('Test Forms', 'formflow-lite'); ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="ff-quick-actions-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-tools&tab=diagnostics')); ?>" class="button button-link ff-quick-link">
                <span class="dashicons dashicons-heart"></span>
                <?php esc_html_e('Diagnostics', 'formflow-lite'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-tools&tab=settings')); ?>" class="button button-link ff-quick-link">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('Settings', 'formflow-lite'); ?>
            </a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="fffl-dashboard-grid">
        <!-- All Time Stats -->
        <div class="ff-stats-section">
            <h3 class="ff-stats-heading"><?php esc_html_e('All Time', 'formflow-lite'); ?></h3>
            <div class="ff-stats-cards">
                <div class="ff-stat-card">
                    <div class="ff-stat-number"><?php echo esc_html($stats['total']); ?></div>
                    <div class="ff-stat-label"><?php esc_html_e('Total Submissions', 'formflow-lite'); ?></div>
                </div>
                <div class="ff-stat-card ff-stat-success">
                    <div class="ff-stat-number"><?php echo esc_html($stats['completed']); ?></div>
                    <div class="ff-stat-label"><?php esc_html_e('Completed', 'formflow-lite'); ?></div>
                </div>
                <div class="ff-stat-card ff-stat-warning">
                    <div class="ff-stat-number"><?php echo esc_html($stats['in_progress']); ?></div>
                    <div class="ff-stat-label"><?php esc_html_e('In Progress', 'formflow-lite'); ?></div>
                </div>
                <div class="ff-stat-card ff-stat-info">
                    <div class="ff-stat-number"><?php echo esc_html($stats['completion_rate']); ?>%</div>
                    <div class="ff-stat-label"><?php esc_html_e('Completion Rate', 'formflow-lite'); ?></div>
                </div>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="ff-stats-section ff-stats-today">
            <h3 class="ff-stats-heading"><?php esc_html_e('Today', 'formflow-lite'); ?></h3>
            <div class="ff-stats-cards ff-stats-cards-small">
                <div class="ff-stat-card">
                    <div class="ff-stat-number"><?php echo esc_html($today_stats['total']); ?></div>
                    <div class="ff-stat-label"><?php esc_html_e('Submissions', 'formflow-lite'); ?></div>
                </div>
                <div class="ff-stat-card ff-stat-success">
                    <div class="ff-stat-number"><?php echo esc_html($today_stats['completed']); ?></div>
                    <div class="ff-stat-label"><?php esc_html_e('Completed', 'formflow-lite'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Health Status -->
    <?php if (!empty($instances)) : ?>
    <div class="ff-card ff-api-health-card">
        <div class="ff-card-header">
            <h2><?php esc_html_e('API Status', 'formflow-lite'); ?></h2>
            <button type="button" id="ff-refresh-health" class="button button-small">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Check Now', 'formflow-lite'); ?>
            </button>
        </div>

        <div id="ff-api-health-status" class="ff-api-health-grid">
            <?php if ($api_health) : ?>
                <?php foreach ($instances as $instance) :
                    $health = $api_health[$instance['id']] ?? null;
                    $status_class = 'unknown';
                    $status_label = __('Unknown', 'formflow-lite');
                    $latency = '';

                    if ($health) {
                        $status_class = $health['status'];
                        switch ($health['status']) {
                            case 'healthy':
                                $status_label = __('Healthy', 'formflow-lite');
                                break;
                            case 'degraded':
                                $status_label = __('Degraded', 'formflow-lite');
                                break;
                            case 'slow':
                                $status_label = __('Slow', 'formflow-lite');
                                break;
                            case 'error':
                                $status_label = __('Error', 'formflow-lite');
                                break;
                            case 'demo':
                                $status_label = __('Demo Mode', 'formflow-lite');
                                break;
                            case 'unconfigured':
                                $status_label = __('Not Configured', 'formflow-lite');
                                break;
                        }
                        if (!empty($health['latency_ms'])) {
                            $latency = $health['latency_ms'] . 'ms';
                        }
                    }
                ?>
                <div class="ff-api-health-item" data-instance-id="<?php echo esc_attr($instance['id']); ?>">
                    <div class="ff-api-health-indicator ff-health-<?php echo esc_attr($status_class); ?>"></div>
                    <div class="ff-api-health-info">
                        <div class="ff-api-health-name"><?php echo esc_html($instance['name']); ?></div>
                        <div class="ff-api-health-details">
                            <span class="ff-api-health-status"><?php echo esc_html($status_label); ?></span>
                            <?php if ($latency) : ?>
                                <span class="ff-api-health-latency"><?php echo esc_html($latency); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($health['error'])) : ?>
                        <div class="ff-api-health-error" title="<?php echo esc_attr($health['error']); ?>">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="ff-api-health-empty">
                    <p><?php esc_html_e('Click "Check Now" to test API connections.', 'formflow-lite'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($api_health) : ?>
        <div class="ff-api-health-footer">
            <span class="ff-api-health-legend">
                <span class="ff-health-dot ff-health-healthy"></span> <?php esc_html_e('Healthy', 'formflow-lite'); ?>
                <span class="ff-health-dot ff-health-degraded"></span> <?php esc_html_e('Degraded', 'formflow-lite'); ?>
                <span class="ff-health-dot ff-health-error"></span> <?php esc_html_e('Error', 'formflow-lite'); ?>
                <span class="ff-health-dot ff-health-demo"></span> <?php esc_html_e('Demo', 'formflow-lite'); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- API Usage / Rate Limit Monitoring -->
    <div class="ff-card ff-api-usage-card">
        <div class="ff-card-header">
            <h2><?php esc_html_e('API Usage', 'formflow-lite'); ?></h2>
            <div class="ff-card-actions">
                <select id="ff-api-usage-period" class="ff-select-small">
                    <option value="hour"><?php esc_html_e('Last Hour', 'formflow-lite'); ?></option>
                    <option value="day" selected><?php esc_html_e('Last 24 Hours', 'formflow-lite'); ?></option>
                    <option value="week"><?php esc_html_e('Last 7 Days', 'formflow-lite'); ?></option>
                    <option value="month"><?php esc_html_e('Last 30 Days', 'formflow-lite'); ?></option>
                </select>
                <button type="button" id="ff-refresh-api-usage" class="button button-small">
                    <span class="dashicons dashicons-update"></span>
                </button>
            </div>
        </div>

        <div id="ff-api-usage-content" class="ff-api-usage-grid">
            <div class="ff-api-usage-loading">
                <span class="spinner is-active"></span>
                <?php esc_html_e('Loading API usage data...', 'formflow-lite'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Instances Table -->
    <div class="ff-card">
        <div class="ff-card-header">
            <h2><?php esc_html_e('Form Instances', 'formflow-lite'); ?></h2>
            <p class="description" style="margin: 0;"><?php esc_html_e('Drag rows to reorder forms', 'formflow-lite'); ?></p>
        </div>

        <?php if (empty($instances)) : ?>
            <div class="ff-empty-state">
                <span class="dashicons dashicons-forms"></span>
                <p><?php esc_html_e('No form instances yet.', 'formflow-lite'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-instance-editor')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Your First Form', 'formflow-lite'); ?>
                </a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped ff-sortable-table" id="ff-instances-table">
                <thead>
                    <tr>
                        <th class="column-drag" style="width: 30px;"></th>
                        <th class="column-name"><?php esc_html_e('Name', 'formflow-lite'); ?></th>
                        <th class="column-shortcode"><?php esc_html_e('Shortcode', 'formflow-lite'); ?></th>
                        <th class="column-utility"><?php esc_html_e('Utility', 'formflow-lite'); ?></th>
                        <th class="column-stats"><?php esc_html_e('Stats', 'formflow-lite'); ?></th>
                        <th class="column-status"><?php esc_html_e('Status', 'formflow-lite'); ?></th>
                        <th class="column-actions"><?php esc_html_e('Actions', 'formflow-lite'); ?></th>
                    </tr>
                </thead>
                <tbody id="ff-instances-sortable">
                    <?php foreach ($instances as $instance) :
                        $inst_stats = $instance_stats[$instance['id']] ?? ['total' => 0, 'completed' => 0];
                    ?>
                        <tr class="ff-sortable-row" data-instance-id="<?php echo esc_attr($instance['id']); ?>">
                            <td class="column-drag">
                                <span class="ff-drag-handle" title="<?php esc_attr_e('Drag to reorder', 'formflow-lite'); ?>">
                                    <span class="dashicons dashicons-menu"></span>
                                </span>
                            </td>
                            <td class="column-name">
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-instance-editor&id=' . $instance['id'])); ?>">
                                        <?php echo esc_html($instance['name']); ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="ff-form-type"><?php echo esc_html(ucfirst($instance['form_type'])); ?></span>
                                </div>
                            </td>
                            <td class="column-shortcode">
                                <code class="ff-shortcode" onclick="navigator.clipboard.writeText(this.innerText)" title="<?php esc_attr_e('Click to copy', 'formflow-lite'); ?>">
                                    [fffl_form instance="<?php echo esc_attr($instance['slug']); ?>"]
                                </code>
                            </td>
                            <td class="column-utility">
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $instance['utility']))); ?>
                            </td>
                            <td class="column-stats">
                                <span class="ff-mini-stat" title="<?php esc_attr_e('Completed / Total', 'formflow-lite'); ?>">
                                    <span class="ff-mini-stat-completed"><?php echo esc_html($inst_stats['completed']); ?></span>
                                    <span class="ff-mini-stat-separator">/</span>
                                    <span class="ff-mini-stat-total"><?php echo esc_html($inst_stats['total']); ?></span>
                                </span>
                                <?php if ($inst_stats['total'] > 0) : ?>
                                    <div class="ff-mini-progress">
                                        <div class="ff-mini-progress-bar" style="width: <?php echo esc_attr($inst_stats['completion_rate'] ?? 0); ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="column-status">
                                <?php if ($instance['is_active']) : ?>
                                    <span class="ff-status ff-status-active">
                                        <?php esc_html_e('Active', 'formflow-lite'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="ff-status ff-status-inactive">
                                        <?php esc_html_e('Inactive', 'formflow-lite'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($instance['form_type'] === 'external') : ?>
                                    <span class="ff-status ff-status-external" title="<?php esc_attr_e('Redirects to external enrollment platform', 'formflow-lite'); ?>">
                                        <?php esc_html_e('External', 'formflow-lite'); ?>
                                    </span>
                                <?php elseif ($instance['settings']['demo_mode'] ?? false) : ?>
                                    <span class="ff-status ff-status-demo">
                                        <?php esc_html_e('Demo', 'formflow-lite'); ?>
                                    </span>
                                <?php elseif ($instance['test_mode']) : ?>
                                    <span class="ff-status ff-status-test">
                                        <?php esc_html_e('Test', 'formflow-lite'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="column-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-instance-editor&id=' . $instance['id'])); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'formflow-lite'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-data&tab=analytics&instance_id=' . $instance['id'])); ?>" class="button button-small">
                                    <?php esc_html_e('Analytics', 'formflow-lite'); ?>
                                </a>
                                <button type="button" class="button button-small button-link-delete ff-delete-instance" data-id="<?php echo esc_attr($instance['id']); ?>">
                                    <?php esc_html_e('Delete', 'formflow-lite'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Recent Submissions -->
    <?php if (!empty($recent_submissions)) : ?>
    <div class="ff-card">
        <div class="ff-card-header">
            <h2><?php esc_html_e('Recent Submissions', 'formflow-lite'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-logs')); ?>" class="button button-small">
                <?php esc_html_e('View All', 'formflow-lite'); ?>
            </a>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-date"><?php esc_html_e('Date', 'formflow-lite'); ?></th>
                    <th class="column-form"><?php esc_html_e('Form', 'formflow-lite'); ?></th>
                    <th class="column-customer"><?php esc_html_e('Customer', 'formflow-lite'); ?></th>
                    <th class="column-device"><?php esc_html_e('Device', 'formflow-lite'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'formflow-lite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_submissions as $submission) :
                    $form_data = $submission['form_data'] ?? [];
                    $customer_name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
                    if (empty($customer_name)) {
                        $customer_name = $submission['account_number'] ?? __('Anonymous', 'formflow-lite');
                    }
                    $device_type = $form_data['device_type'] ?? '';
                    $device_label = $device_type === 'thermostat' ? __('Thermostat', 'formflow-lite') : ($device_type === 'dcu' ? __('Outdoor Switch', 'formflow-lite') : '—');

                    // Find instance name
                    $instance_name = '—';
                    foreach ($instances as $inst) {
                        if ($inst['id'] == $submission['instance_id']) {
                            $instance_name = $inst['name'];
                            break;
                        }
                    }
                ?>
                    <tr>
                        <td class="column-date">
                            <span title="<?php echo esc_attr($submission['created_at']); ?>">
                                <?php echo esc_html(human_time_diff(strtotime($submission['created_at']), current_time('timestamp')) . ' ' . __('ago', 'formflow-lite')); ?>
                            </span>
                        </td>
                        <td class="column-form">
                            <?php echo esc_html($instance_name); ?>
                        </td>
                        <td class="column-customer">
                            <?php echo esc_html($customer_name); ?>
                        </td>
                        <td class="column-device">
                            <?php echo esc_html($device_label); ?>
                        </td>
                        <td class="column-status">
                            <?php
                            $status_class = 'ff-status-' . ($submission['status'] ?? 'in_progress');
                            $status_label = ucfirst(str_replace('_', ' ', $submission['status'] ?? 'in_progress'));
                            ?>
                            <span class="ff-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                            <?php if (!empty($submission['is_test_data'])) : ?>
                                <span class="ff-status ff-status-test"><?php esc_html_e('Test', 'formflow-lite'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($instances)) : ?>
    <!-- Shortcode Generator -->
    <div class="ff-card ff-shortcode-generator">
        <h2><?php esc_html_e('Shortcode Generator', 'formflow-lite'); ?></h2>
        <p class="description"><?php esc_html_e('Generate a shortcode to embed a form on any page or post.', 'formflow-lite'); ?></p>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ff-gen-instance"><?php esc_html_e('Select Form', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <select id="ff-gen-instance" class="regular-text">
                        <?php foreach ($instances as $instance) : ?>
                            <option value="<?php echo esc_attr($instance['slug']); ?>">
                                <?php echo esc_html($instance['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="ff-gen-class"><?php esc_html_e('Custom CSS Class', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <input type="text" id="ff-gen-class" class="regular-text" placeholder="<?php esc_attr_e('Optional', 'formflow-lite'); ?>">
                    <p class="description"><?php esc_html_e('Add a custom CSS class for styling.', 'formflow-lite'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Generated Shortcode', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <div class="ff-generated-shortcode-wrap">
                        <code id="ff-generated-shortcode" class="ff-shortcode-display">[fffl_form instance="<?php echo esc_attr($instances[0]['slug'] ?? ''); ?>"]</code>
                        <button type="button" id="ff-copy-shortcode" class="button">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php esc_html_e('Copy', 'formflow-lite'); ?>
                        </button>
                    </div>
                    <p class="description ff-copy-success" id="ff-copy-success" style="display:none; color:#46b450;">
                        <?php esc_html_e('Copied to clipboard!', 'formflow-lite'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function updateShortcode() {
            var instance = $('#ff-gen-instance').val();
            var cssClass = $('#ff-gen-class').val().trim();

            var shortcode = '[fffl_form instance="' + instance + '"';
            if (cssClass) {
                shortcode += ' class="' + cssClass + '"';
            }
            shortcode += ']';

            $('#ff-generated-shortcode').text(shortcode);
        }

        $('#ff-gen-instance, #ff-gen-class').on('input change', updateShortcode);

        $('#ff-copy-shortcode').on('click', function() {
            var shortcode = $('#ff-generated-shortcode').text();
            navigator.clipboard.writeText(shortcode).then(function() {
                $('#ff-copy-success').fadeIn().delay(2000).fadeOut();
            });
        });

        // Also allow clicking the shortcode itself to copy
        $('#ff-generated-shortcode').on('click', function() {
            navigator.clipboard.writeText($(this).text()).then(function() {
                $('#ff-copy-success').fadeIn().delay(2000).fadeOut();
            });
        });
    });
    </script>
    <?php endif; ?>

    <!-- Quick Start Guide -->
    <div class="ff-card ff-quick-start">
        <h2><?php esc_html_e('Quick Start Guide', 'formflow-lite'); ?></h2>
        <ol>
            <li><?php esc_html_e('Create a new form instance by clicking "Add New Form"', 'formflow-lite'); ?></li>
            <li><?php esc_html_e('Select your utility from the dropdown (API settings will auto-fill)', 'formflow-lite'); ?></li>
            <li><?php esc_html_e('Enter your API password and test the connection', 'formflow-lite'); ?></li>
            <li><?php esc_html_e('Copy the shortcode and paste it into any page or post', 'formflow-lite'); ?></li>
        </ol>
    </div>
</div>
