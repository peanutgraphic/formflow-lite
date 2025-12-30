<?php
/**
 * UTM Tracking Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['utm_tracking'] ?? [];
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row"><?php esc_html_e('Track Parameters', 'formflow-lite'); ?></th>
        <td>
            <fieldset>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][utm_tracking][track_referrer]" value="1"
                           <?php checked($settings['track_referrer'] ?? true); ?>>
                    <?php esc_html_e('HTTP Referrer', 'formflow-lite'); ?>
                </label>
                <br>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][utm_tracking][track_landing_page]" value="1"
                           <?php checked($settings['track_landing_page'] ?? true); ?>>
                    <?php esc_html_e('Landing Page URL', 'formflow-lite'); ?>
                </label>
            </fieldset>
            <p class="description">
                <?php esc_html_e('UTM parameters (source, medium, campaign, term, content) are always tracked when present in the URL.', 'formflow-lite'); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Pass to API', 'formflow-lite'); ?></th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][utm_tracking][pass_to_api]" value="1"
                       <?php checked($settings['pass_to_api'] ?? false); ?>>
                <?php esc_html_e('Include UTM data in API submission', 'formflow-lite'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Only enable if the API supports custom fields. UTM data is always stored locally.', 'formflow-lite'); ?>
            </p>
        </td>
    </tr>
</table>
