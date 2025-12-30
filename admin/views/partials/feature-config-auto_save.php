<?php
/**
 * Auto-Save Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['auto_save'] ?? [];
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row">
            <label for="auto_save_interval"><?php esc_html_e('Save Interval', 'formflow-lite'); ?></label>
        </th>
        <td>
            <select id="auto_save_interval" name="settings[features][auto_save][interval_seconds]">
                <option value="30" <?php selected($settings['interval_seconds'] ?? 60, 30); ?>>
                    <?php esc_html_e('Every 30 seconds', 'formflow-lite'); ?>
                </option>
                <option value="60" <?php selected($settings['interval_seconds'] ?? 60, 60); ?>>
                    <?php esc_html_e('Every 1 minute', 'formflow-lite'); ?>
                </option>
                <option value="120" <?php selected($settings['interval_seconds'] ?? 60, 120); ?>>
                    <?php esc_html_e('Every 2 minutes', 'formflow-lite'); ?>
                </option>
                <option value="300" <?php selected($settings['interval_seconds'] ?? 60, 300); ?>>
                    <?php esc_html_e('Every 5 minutes', 'formflow-lite'); ?>
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e('Local Storage Backup', 'formflow-lite'); ?></label>
        </th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][auto_save][use_local_storage]" value="1"
                       <?php checked($settings['use_local_storage'] ?? true); ?>>
                <?php esc_html_e('Also save to browser localStorage as backup', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e('Show Save Indicator', 'formflow-lite'); ?></label>
        </th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][auto_save][show_save_indicator]" value="1"
                       <?php checked($settings['show_save_indicator'] ?? true); ?>>
                <?php esc_html_e('Display "Auto-saved" message when saving', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
</table>
