<?php
/**
 * Appointment Self-Service Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['appointment_self_service'] ?? [];
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row"><?php esc_html_e('Allowed Actions', 'formflow-lite'); ?></th>
        <td>
            <fieldset>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][appointment_self_service][allow_reschedule]" value="1"
                           <?php checked($settings['allow_reschedule'] ?? true); ?>>
                    <?php esc_html_e('Allow customers to reschedule appointments', 'formflow-lite'); ?>
                </label>
                <br>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][appointment_self_service][allow_cancel]" value="1"
                           <?php checked($settings['allow_cancel'] ?? true); ?>>
                    <?php esc_html_e('Allow customers to cancel appointments', 'formflow-lite'); ?>
                </label>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="reschedule_deadline"><?php esc_html_e('Reschedule Deadline', 'formflow-lite'); ?></label>
        </th>
        <td>
            <select id="reschedule_deadline" name="settings[features][appointment_self_service][reschedule_deadline_hours]">
                <option value="12" <?php selected($settings['reschedule_deadline_hours'] ?? 24, 12); ?>>
                    <?php esc_html_e('12 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="24" <?php selected($settings['reschedule_deadline_hours'] ?? 24, 24); ?>>
                    <?php esc_html_e('24 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="48" <?php selected($settings['reschedule_deadline_hours'] ?? 24, 48); ?>>
                    <?php esc_html_e('48 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="72" <?php selected($settings['reschedule_deadline_hours'] ?? 24, 72); ?>>
                    <?php esc_html_e('72 hours before appointment', 'formflow-lite'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('How far in advance customers must reschedule', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="cancel_deadline"><?php esc_html_e('Cancellation Deadline', 'formflow-lite'); ?></label>
        </th>
        <td>
            <select id="cancel_deadline" name="settings[features][appointment_self_service][cancel_deadline_hours]">
                <option value="12" <?php selected($settings['cancel_deadline_hours'] ?? 24, 12); ?>>
                    <?php esc_html_e('12 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="24" <?php selected($settings['cancel_deadline_hours'] ?? 24, 24); ?>>
                    <?php esc_html_e('24 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="48" <?php selected($settings['cancel_deadline_hours'] ?? 24, 48); ?>>
                    <?php esc_html_e('48 hours before appointment', 'formflow-lite'); ?>
                </option>
                <option value="72" <?php selected($settings['cancel_deadline_hours'] ?? 24, 72); ?>>
                    <?php esc_html_e('72 hours before appointment', 'formflow-lite'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('How far in advance customers must cancel', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Cancellation Reason', 'formflow-lite'); ?></th>
        <td>
            <label class="ff-checkbox-label">
                <input type="checkbox" name="settings[features][appointment_self_service][require_reason_for_cancel]" value="1"
                       <?php checked($settings['require_reason_for_cancel'] ?? true); ?>>
                <?php esc_html_e('Require customers to provide a reason when cancelling', 'formflow-lite'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="token_expiry"><?php esc_html_e('Link Expiry', 'formflow-lite'); ?></label>
        </th>
        <td>
            <select id="token_expiry" name="settings[features][appointment_self_service][token_expiry_days]">
                <option value="7" <?php selected($settings['token_expiry_days'] ?? 30, 7); ?>>
                    <?php esc_html_e('7 days', 'formflow-lite'); ?>
                </option>
                <option value="14" <?php selected($settings['token_expiry_days'] ?? 30, 14); ?>>
                    <?php esc_html_e('14 days', 'formflow-lite'); ?>
                </option>
                <option value="30" <?php selected($settings['token_expiry_days'] ?? 30, 30); ?>>
                    <?php esc_html_e('30 days', 'formflow-lite'); ?>
                </option>
                <option value="60" <?php selected($settings['token_expiry_days'] ?? 30, 60); ?>>
                    <?php esc_html_e('60 days', 'formflow-lite'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('How long the management link in confirmation emails remains valid', 'formflow-lite'); ?></p>
        </td>
    </tr>
</table>

<div class="ff-info-box">
    <p><strong><?php esc_html_e('How It Works:', 'formflow-lite'); ?></strong></p>
    <p><?php esc_html_e('After enrollment, customers receive a confirmation email with a secure link to manage their appointment. They can reschedule or cancel within the configured deadlines. All changes are logged and notification emails are sent.', 'formflow-lite'); ?></p>
</div>
