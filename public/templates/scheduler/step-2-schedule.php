<?php
/**
 * Scheduler Step 2: Select Appointment
 *
 * User selects installation date and time slot.
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_date = $form_data['schedule_date'] ?? '';
$selected_time = $form_data['schedule_time'] ?? '';
$customer_name = ($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? '');
$address = $form_data['address'] ?? [];
?>

<div class="ff-step" data-step="2">
    <h2 class="ff-step-title"><?php esc_html_e('Select Your Appointment', 'formflow-lite'); ?></h2>

    <?php if (!empty($customer_name) || !empty($address)) : ?>
    <div class="ff-customer-info-box">
        <?php if (!empty(trim($customer_name))) : ?>
            <p><strong><?php echo esc_html(trim($customer_name)); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($address['street'])) : ?>
            <p><?php echo esc_html($address['street']); ?></p>
            <p><?php echo esc_html(($address['city'] ?? '') . ', ' . ($address['state'] ?? '') . ' ' . ($address['zip'] ?? '')); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form class="ff-step-form" id="ff-scheduler-step-2-form">
        <div class="ff-schedule-container">
            <!-- Calendar Section -->
            <div class="ff-calendar-section">
                <div class="ff-calendar-header">
                    <button type="button" class="ff-calendar-nav ff-calendar-prev" aria-label="<?php esc_attr_e('Previous week', 'formflow-lite'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <span class="ff-calendar-month" id="ff-calendar-month"></span>
                    <button type="button" class="ff-calendar-nav ff-calendar-next" aria-label="<?php esc_attr_e('Next week', 'formflow-lite'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="ff-calendar-grid" id="ff-calendar-grid">
                    <div class="ff-calendar-loading">
                        <svg class="ff-spinner" viewBox="0 0 24 24" width="32" height="32">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" />
                        </svg>
                        <span><?php esc_html_e('Loading available dates...', 'formflow-lite'); ?></span>
                    </div>
                </div>

                <div class="ff-calendar-legend">
                    <span class="ff-legend-item">
                        <span class="ff-legend-dot ff-legend-available"></span>
                        <?php esc_html_e('Available', 'formflow-lite'); ?>
                    </span>
                    <span class="ff-legend-item">
                        <span class="ff-legend-dot ff-legend-selected"></span>
                        <?php esc_html_e('Selected', 'formflow-lite'); ?>
                    </span>
                </div>
            </div>

            <!-- Time Slots Section -->
            <div class="ff-timeslots-section">
                <h3 class="ff-timeslots-title"><?php esc_html_e('Available Time Slots', 'formflow-lite'); ?></h3>
                <p class="ff-timeslots-instruction" id="ff-timeslots-instruction">
                    <?php esc_html_e('Please select a date to see available time slots.', 'formflow-lite'); ?>
                </p>

                <div class="ff-timeslots-grid" id="ff-timeslots-grid" style="display:none;"></div>

                <div class="ff-timeslots-loading" id="ff-timeslots-loading" style="display:none;">
                    <svg class="ff-spinner" viewBox="0 0 24 24" width="24" height="24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" />
                    </svg>
                    <span><?php esc_html_e('Loading time slots...', 'formflow-lite'); ?></span>
                </div>

                <div class="ff-timeslots-empty" id="ff-timeslots-empty" style="display:none;">
                    <?php esc_html_e('No time slots available for this date.', 'formflow-lite'); ?>
                </div>
            </div>
        </div>

        <!-- Selected Appointment Summary -->
        <div class="ff-appointment-summary" id="ff-appointment-summary" style="display:none;">
            <h3 class="ff-summary-title"><?php esc_html_e('Your Selected Appointment', 'formflow-lite'); ?></h3>
            <div class="ff-summary-details">
                <div class="ff-summary-item">
                    <span class="ff-summary-label"><?php esc_html_e('Date:', 'formflow-lite'); ?></span>
                    <span class="ff-summary-value" id="ff-summary-date"></span>
                </div>
                <div class="ff-summary-item">
                    <span class="ff-summary-label"><?php esc_html_e('Time:', 'formflow-lite'); ?></span>
                    <span class="ff-summary-value" id="ff-summary-time"></span>
                </div>
            </div>
        </div>

        <input type="hidden" name="schedule_date" id="schedule_date" value="<?php echo esc_attr($selected_date); ?>">
        <input type="hidden" name="schedule_time" id="schedule_time" value="<?php echo esc_attr($selected_time); ?>">
        <input type="hidden" name="schedule_fsr" id="schedule_fsr" value="">

        <div class="ff-step-actions">
            <button type="button" class="ff-btn ff-btn-secondary ff-btn-prev">
                <span class="ff-btn-arrow">&larr;</span>
                <?php esc_html_e('Back', 'formflow-lite'); ?>
            </button>
            <button type="submit" class="ff-btn ff-btn-primary ff-btn-next" disabled>
                <span class="ff-btn-text"><?php esc_html_e('Confirm Appointment', 'formflow-lite'); ?></span>
                <span class="ff-btn-loading" style="display:none;">
                    <svg class="ff-spinner" viewBox="0 0 24 24" width="20" height="20">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" />
                    </svg>
                    <?php esc_html_e('Scheduling...', 'formflow-lite'); ?>
                </span>
            </button>
        </div>
    </form>
</div>
