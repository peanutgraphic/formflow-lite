<?php
/**
 * Capacity Management Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['capacity_management'] ?? [];
$blackout_dates = $settings['blackout_dates'] ?? [];
if (is_string($blackout_dates)) {
    $blackout_dates = json_decode($blackout_dates, true) ?? [];
}
?>

<table class="form-table ff-feature-config-table">
    <tr>
        <th scope="row">
            <label for="daily_cap"><?php esc_html_e('Daily Cap', 'formflow-lite'); ?></label>
        </th>
        <td>
            <input type="number" id="daily_cap" name="settings[features][capacity_management][daily_cap]"
                   class="small-text" min="0" value="<?php echo esc_attr($settings['daily_cap'] ?? 0); ?>">
            <p class="description"><?php esc_html_e('Maximum appointments per day (0 = unlimited)', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="per_slot_cap"><?php esc_html_e('Per-Slot Cap', 'formflow-lite'); ?></label>
        </th>
        <td>
            <input type="number" id="per_slot_cap" name="settings[features][capacity_management][per_slot_cap]"
                   class="small-text" min="0" value="<?php echo esc_attr($settings['per_slot_cap'] ?? 0); ?>">
            <p class="description"><?php esc_html_e('Maximum appointments per time slot (0 = unlimited)', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Blackout Dates', 'formflow-lite'); ?></th>
        <td>
            <div id="ff-blackout-dates">
                <?php if (!empty($blackout_dates)): ?>
                    <?php foreach ($blackout_dates as $i => $blackout): ?>
                        <div class="ff-blackout-item">
                            <?php if (!empty($blackout['date'])): ?>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][date]"
                                       value="<?php echo esc_attr($blackout['date']); ?>">
                                <input type="text" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][reason]"
                                       value="<?php echo esc_attr($blackout['reason'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Reason (optional)', 'formflow-lite'); ?>">
                            <?php elseif (!empty($blackout['start'])): ?>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][start]"
                                       value="<?php echo esc_attr($blackout['start']); ?>">
                                <span>to</span>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][end]"
                                       value="<?php echo esc_attr($blackout['end']); ?>">
                                <input type="text" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][reason]"
                                       value="<?php echo esc_attr($blackout['reason'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Reason (optional)', 'formflow-lite'); ?>">
                            <?php endif; ?>
                            <button type="button" class="button ff-remove-blackout">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="ff-blackout-actions">
                <button type="button" class="button" id="ff-add-blackout-date">
                    <?php esc_html_e('Add Single Date', 'formflow-lite'); ?>
                </button>
                <button type="button" class="button" id="ff-add-blackout-range">
                    <?php esc_html_e('Add Date Range', 'formflow-lite'); ?>
                </button>
            </div>
            <p class="description"><?php esc_html_e('Dates when scheduling is not available (holidays, etc.)', 'formflow-lite'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Waitlist', 'formflow-lite'); ?></th>
        <td>
            <fieldset>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][capacity_management][enable_waitlist]" value="1"
                           <?php checked($settings['enable_waitlist'] ?? false); ?>>
                    <?php esc_html_e('Enable waitlist when slots are full', 'formflow-lite'); ?>
                </label>
                <br>
                <label class="ff-checkbox-label">
                    <input type="checkbox" name="settings[features][capacity_management][waitlist_notification]" value="1"
                           <?php checked($settings['waitlist_notification'] ?? true); ?>>
                    <?php esc_html_e('Notify waitlist when slots become available', 'formflow-lite'); ?>
                </label>
            </fieldset>
        </td>
    </tr>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var blackoutIndex = <?php echo count($blackout_dates); ?>;
    var container = document.getElementById('ff-blackout-dates');

    document.getElementById('ff-add-blackout-date').addEventListener('click', function() {
        var html = '<div class="ff-blackout-item">' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][date]">' +
            '<input type="text" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][reason]" placeholder="<?php esc_attr_e('Reason (optional)', 'formflow-lite'); ?>">' +
            '<button type="button" class="button ff-remove-blackout">&times;</button>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        blackoutIndex++;
    });

    document.getElementById('ff-add-blackout-range').addEventListener('click', function() {
        var html = '<div class="ff-blackout-item">' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][start]">' +
            '<span>to</span>' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][end]">' +
            '<input type="text" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][reason]" placeholder="<?php esc_attr_e('Reason (optional)', 'formflow-lite'); ?>">' +
            '<button type="button" class="button ff-remove-blackout">&times;</button>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        blackoutIndex++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('ff-remove-blackout')) {
            e.target.closest('.ff-blackout-item').remove();
        }
    });
});
</script>
