<?php
/**
 * Tools Tab: Settings - Lite Version
 *
 * Simplified settings without license management.
 *
 * @package FormFlow Lite
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('fffl_settings');

// Get current settings
$settings = get_option('fffl_settings', []);
?>

<form method="post">
    <?php wp_nonce_field('fffl_settings_nonce'); ?>

    <div class="ff-card">
        <h2><?php esc_html_e('Session & Security', 'formflow-lite'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="session_timeout_minutes"><?php esc_html_e('Session Timeout', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <input type="number" id="session_timeout_minutes" name="session_timeout_minutes"
                           value="<?php echo esc_attr($settings['session_timeout_minutes'] ?? 30); ?>"
                           min="5" max="120" class="small-text"> <?php esc_html_e('minutes', 'formflow-lite'); ?>
                    <p class="description">
                        <?php esc_html_e('How long form sessions remain valid after inactivity.', 'formflow-lite'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rate_limit_preset"><?php esc_html_e('Rate Limiting', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <?php
                    $disable_rate_limit = !empty($settings['disable_rate_limit']);
                    $current_requests = $settings['rate_limit_requests'] ?? 120;
                    $current_window = $settings['rate_limit_window'] ?? 60;
                    ?>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="disable_rate_limit" value="1" <?php checked($disable_rate_limit); ?> id="disable_rate_limit">
                            <?php esc_html_e('Disable rate limiting entirely', 'formflow-lite'); ?>
                        </label>
                        <p class="description" style="margin-left: 24px; color: #d63638;">
                            <?php esc_html_e('Warning: Only disable if experiencing persistent 429 errors. This removes abuse protection.', 'formflow-lite'); ?>
                        </p>
                    </fieldset>

                    <div id="rate_limit_options" style="margin-top: 15px; <?php echo $disable_rate_limit ? 'opacity: 0.5;' : ''; ?>">
                        <label for="rate_limit_preset" style="font-weight: 500;"><?php esc_html_e('Preset:', 'formflow-lite'); ?></label>
                        <select id="rate_limit_preset" style="margin-left: 5px;">
                            <option value="custom"><?php esc_html_e('Custom', 'formflow-lite'); ?></option>
                            <option value="strict" <?php selected($current_requests == 60 && $current_window == 60); ?>><?php esc_html_e('Strict (60/min) - High security', 'formflow-lite'); ?></option>
                            <option value="normal" <?php selected($current_requests == 120 && $current_window == 60); ?>><?php esc_html_e('Normal (120/min) - Recommended', 'formflow-lite'); ?></option>
                            <option value="relaxed" <?php selected($current_requests == 200 && $current_window == 60); ?>><?php esc_html_e('Relaxed (200/min) - If seeing 429 errors', 'formflow-lite'); ?></option>
                            <option value="very_relaxed" <?php selected($current_requests == 300 && $current_window == 60); ?>><?php esc_html_e('Very Relaxed (300/min) - For high-traffic forms', 'formflow-lite'); ?></option>
                        </select>

                        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                            <label style="font-weight: 500;"><?php esc_html_e('Custom Values:', 'formflow-lite'); ?></label>
                            <div style="margin-top: 8px;">
                                <input type="number" id="rate_limit_requests" name="rate_limit_requests"
                                       value="<?php echo esc_attr($current_requests); ?>"
                                       min="10" max="1000" class="small-text" style="width: 70px;">
                                <?php esc_html_e('requests per', 'formflow-lite'); ?>
                                <input type="number" id="rate_limit_window" name="rate_limit_window"
                                       value="<?php echo esc_attr($current_window); ?>"
                                       min="10" max="300" class="small-text" style="width: 60px;">
                                <?php esc_html_e('seconds per IP address', 'formflow-lite'); ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="ff-card">
        <h2><?php esc_html_e('Third-Party Integrations', 'formflow-lite'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="google_places_api_key"><?php esc_html_e('Google Places API Key', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <input type="text" id="google_places_api_key" name="google_places_api_key" class="regular-text"
                           value="<?php echo esc_attr($settings['google_places_api_key'] ?? ''); ?>"
                           placeholder="AIza...">
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('Optional: Enable address autocomplete on forms. Get a key from the %sGoogle Cloud Console%s.', 'formflow-lite'),
                            '<a href="https://console.cloud.google.com/apis/credentials" target="_blank">',
                            '</a>'
                        );
                        ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="ff-card">
        <h2><?php esc_html_e('Data Retention', 'formflow-lite'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cleanup_abandoned_hours"><?php esc_html_e('Abandoned Sessions', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <input type="number" id="cleanup_abandoned_hours" name="cleanup_abandoned_hours"
                           value="<?php echo esc_attr($settings['cleanup_abandoned_hours'] ?? 24); ?>"
                           min="1" max="168" class="small-text"> <?php esc_html_e('hours', 'formflow-lite'); ?>
                    <p class="description">
                        <?php esc_html_e('Mark incomplete submissions as abandoned after this time.', 'formflow-lite'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="log_retention_days"><?php esc_html_e('Log Retention', 'formflow-lite'); ?></label>
                </th>
                <td>
                    <input type="number" id="log_retention_days" name="log_retention_days"
                           value="<?php echo esc_attr($settings['log_retention_days'] ?? 90); ?>"
                           min="7" max="365" class="small-text"> <?php esc_html_e('days', 'formflow-lite'); ?>
                    <p class="description">
                        <?php esc_html_e('Automatically delete activity logs older than this.', 'formflow-lite'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="ff-card">
        <h2><?php esc_html_e('Encryption', 'formflow-lite'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Encryption Status', 'formflow-lite'); ?></th>
                <td>
                    <?php
                    $encryption = new \FFFL\Encryption();
                    $test_result = $encryption->test();
                    ?>
                    <?php if ($test_result) : ?>
                        <span class="ff-status ff-status-active">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('Working', 'formflow-lite'); ?>
                        </span>
                    <?php else : ?>
                        <span class="ff-status ff-status-error">
                            <span class="dashicons dashicons-no"></span>
                            <?php esc_html_e('Error', 'formflow-lite'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Custom Encryption Key', 'formflow-lite'); ?></th>
                <td>
                    <?php if (defined('FFFL_ENCRYPTION_KEY')) : ?>
                        <span class="ff-status ff-status-active">
                            <?php esc_html_e('Custom key defined in wp-config.php', 'formflow-lite'); ?>
                        </span>
                    <?php else : ?>
                        <span class="ff-status ff-status-warning">
                            <?php esc_html_e('Using WordPress auth salt (default)', 'formflow-lite'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="ff-card">
        <h2><?php esc_html_e('System Information', 'formflow-lite'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Plugin Version', 'formflow-lite'); ?></th>
                <td><code><?php echo esc_html(FFFL_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('PHP Version', 'formflow-lite'); ?></th>
                <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('WordPress Version', 'formflow-lite'); ?></th>
                <td><code><?php echo esc_html(get_bloginfo('version')); ?></code></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('SSL Status', 'formflow-lite'); ?></th>
                <td>
                    <?php if (is_ssl()) : ?>
                        <span class="ff-status ff-status-active">
                            <span class="dashicons dashicons-lock"></span>
                            <?php esc_html_e('Active', 'formflow-lite'); ?>
                        </span>
                    <?php else : ?>
                        <span class="ff-status ff-status-warning">
                            <span class="dashicons dashicons-unlock"></span>
                            <?php esc_html_e('Not Active', 'formflow-lite'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <p class="submit">
        <input type="submit" name="fffl_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'formflow-lite'); ?>">
    </p>
</form>

<script>
jQuery(document).ready(function($) {
    var presets = {
        'strict': { requests: 60, window: 60 },
        'normal': { requests: 120, window: 60 },
        'relaxed': { requests: 200, window: 60 },
        'very_relaxed': { requests: 300, window: 60 }
    };

    $('#rate_limit_preset').on('change', function() {
        var preset = $(this).val();
        if (preset !== 'custom' && presets[preset]) {
            $('#rate_limit_requests').val(presets[preset].requests);
            $('#rate_limit_window').val(presets[preset].window);
        }
    });

    $('#disable_rate_limit').on('change', function() {
        if ($(this).is(':checked')) {
            $('#rate_limit_options').css('opacity', '0.5');
            $('#rate_limit_requests, #rate_limit_window, #rate_limit_preset').prop('disabled', true);
        } else {
            $('#rate_limit_options').css('opacity', '1');
            $('#rate_limit_requests, #rate_limit_window, #rate_limit_preset').prop('disabled', false);
        }
    });

    if ($('#disable_rate_limit').is(':checked')) {
        $('#rate_limit_requests, #rate_limit_window, #rate_limit_preset').prop('disabled', true);
    }
});
</script>
