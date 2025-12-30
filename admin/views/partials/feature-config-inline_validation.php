<?php
/**
 * Inline Validation Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['inline_validation'] ?? [];
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row">
            <label><?php esc_html_e('Show Success Icons', 'formflow-lite'); ?></label>
        </th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][inline_validation][show_success_icons]" value="1"
                       <?php checked($settings['show_success_icons'] ?? true); ?>>
                <?php esc_html_e('Display checkmarks when fields are valid', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e('Validate On Blur', 'formflow-lite'); ?></label>
        </th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][inline_validation][validate_on_blur]" value="1"
                       <?php checked($settings['validate_on_blur'] ?? true); ?>>
                <?php esc_html_e('Validate when user leaves a field', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e('Validate On Keyup', 'formflow-lite'); ?></label>
        </th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][inline_validation][validate_on_keyup]" value="1"
                       <?php checked($settings['validate_on_keyup'] ?? false); ?>>
                <?php esc_html_e('Validate as user types (can be intensive)', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
</table>
