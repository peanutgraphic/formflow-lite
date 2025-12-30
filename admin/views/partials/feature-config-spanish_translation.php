<?php
/**
 * Spanish Translation Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['spanish_translation'] ?? [];
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row">
            <label for="translation_default_language"><?php esc_html_e('Default Language', 'formflow-lite'); ?></label>
        </th>
        <td>
            <select id="translation_default_language" name="settings[features][spanish_translation][default_language]">
                <option value="en" <?php selected($settings['default_language'] ?? 'en', 'en'); ?>>
                    <?php esc_html_e('English', 'formflow-lite'); ?>
                </option>
                <option value="es" <?php selected($settings['default_language'] ?? 'en', 'es'); ?>>
                    <?php esc_html_e('Spanish (Espa&ntilde;ol)', 'formflow-lite'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('The default language when the form loads', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Language Toggle', 'formflow-lite'); ?></th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][spanish_translation][show_language_toggle]" value="1"
                       <?php checked($settings['show_language_toggle'] ?? true); ?>>
                <?php esc_html_e('Show language toggle button on form', 'formflow-lite'); ?>
            </label>
            <p class="description"><?php esc_html_e('Allows users to switch between English and Spanish', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Auto-Detect Language', 'formflow-lite'); ?></th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][spanish_translation][auto_detect]" value="1"
                       <?php checked($settings['auto_detect'] ?? true); ?>>
                <?php esc_html_e('Automatically detect browser language preference', 'formflow-lite'); ?>
            </label>
            <p class="description"><?php esc_html_e('If the user\'s browser is set to Spanish, the form will default to Spanish', 'formflow-lite'); ?></p>
        </td>
    </tr>
</table>

<div class="ff-info-box">
    <p><strong><?php esc_html_e('Translation Coverage:', 'formflow-lite'); ?></strong></p>
    <ul>
        <li><?php esc_html_e('All form labels and field placeholders', 'formflow-lite'); ?></li>
        <li><?php esc_html_e('Button text and navigation', 'formflow-lite'); ?></li>
        <li><?php esc_html_e('Error messages and validation feedback', 'formflow-lite'); ?></li>
        <li><?php esc_html_e('Confirmation emails', 'formflow-lite'); ?></li>
        <li><?php esc_html_e('Progress indicators', 'formflow-lite'); ?></li>
    </ul>
</div>
