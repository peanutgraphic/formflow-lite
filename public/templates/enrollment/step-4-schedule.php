<?php
/**
 * Enrollment Step 4: Schedule Installation
 *
 * User selects installation date and time slot.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Import content helper function
use function FFFL\Frontend\fffl_get_content;

$selected_date = $form_data['schedule_date'] ?? '';
$selected_time = $form_data['schedule_time'] ?? '';
$device_type = $form_data['device_type'] ?? 'thermostat';

// Get customizable content
$step_title = fffl_get_content($instance, 'step4_title', __('Schedule Your Installation', 'formflow-lite'));
$help_scheduling = fffl_get_content($instance, 'help_scheduling', __('Select a convenient date and time for your free installation appointment, or skip to schedule later.', 'formflow-lite'));
$btn_back = fffl_get_content($instance, 'btn_back', __('Back', 'formflow-lite'));
?>

<div class="ff-step" data-step="4">
    <h2 class="ff-step-title"><?php echo esc_html($step_title); ?></h2>
    <p class="ff-step-description">
        <?php echo esc_html($help_scheduling); ?>
    </p>
    <p class="ff-step-description ff-schedule-optional">
        <em><?php esc_html_e('Scheduling is optional. You can skip this step and someone will contact you to schedule, or you can schedule online later.', 'formflow-lite'); ?></em>
    </p>

    <form class="ff-step-form" id="ff-step-4-form">
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
                    <!-- Calendar days will be populated by JavaScript -->
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
                    <span class="ff-legend-item">
                        <span class="ff-legend-dot ff-legend-unavailable"></span>
                        <?php esc_html_e('Unavailable', 'formflow-lite'); ?>
                    </span>
                </div>
            </div>

            <!-- Time Slots Section -->
            <div class="ff-timeslots-section">
                <h3 class="ff-timeslots-title"><?php esc_html_e('Available Time Slots', 'formflow-lite'); ?></h3>
                <p class="ff-timeslots-instruction" id="ff-timeslots-instruction">
                    <?php esc_html_e('Please select a date to see available time slots.', 'formflow-lite'); ?>
                </p>

                <div class="ff-timeslots-grid" id="ff-timeslots-grid" style="display:none;">
                    <!-- Time slots will be populated by JavaScript -->
                </div>

                <div class="ff-timeslots-loading" id="ff-timeslots-loading" style="display:none;">
                    <svg class="ff-spinner" viewBox="0 0 24 24" width="24" height="24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" />
                    </svg>
                    <span><?php esc_html_e('Loading time slots...', 'formflow-lite'); ?></span>
                </div>

                <div class="ff-timeslots-empty" id="ff-timeslots-empty" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="24" height="24">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    <span><?php esc_html_e('No time slots available for this date. Please select another date.', 'formflow-lite'); ?></span>
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
                <div class="ff-summary-item">
                    <span class="ff-summary-label"><?php esc_html_e('Device:', 'formflow-lite'); ?></span>
                    <span class="ff-summary-value" id="ff-summary-device">
                        <?php echo $device_type === 'thermostat'
                            ? esc_html__('Web-Programmable Thermostat', 'formflow-lite')
                            : esc_html__('Outdoor Switch', 'formflow-lite'); ?>
                    </span>
                </div>
            </div>
        </div>

        <input type="hidden" name="schedule_date" id="schedule_date" value="<?php echo esc_attr($selected_date); ?>">
        <input type="hidden" name="schedule_time" id="schedule_time" value="<?php echo esc_attr($selected_time); ?>">
        <input type="hidden" name="schedule_fsr" id="schedule_fsr" value="">

        <div class="ff-installation-info">
            <h4><?php esc_html_e('What to Expect', 'formflow-lite'); ?></h4>
            <ul>
                <li><?php esc_html_e('Installation is completely FREE', 'formflow-lite'); ?></li>
                <li><?php esc_html_e('A certified technician will arrive during your selected time window', 'formflow-lite'); ?></li>
                <?php if ($device_type === 'thermostat') : ?>
                    <li><?php esc_html_e('Installation typically takes 30-45 minutes', 'formflow-lite'); ?></li>
                    <li><?php esc_html_e('The technician will show you how to use your new thermostat', 'formflow-lite'); ?></li>
                <?php else : ?>
                    <li><?php esc_html_e('Installation typically takes 15-30 minutes', 'formflow-lite'); ?></li>
                    <li><?php esc_html_e('The switch is installed on your outdoor unit - no indoor access needed', 'formflow-lite'); ?></li>
                <?php endif; ?>
                <li><?php esc_html_e('An adult (18+) must be present for the installation', 'formflow-lite'); ?></li>
            </ul>
        </div>

        <div class="ff-step-actions">
            <button type="button" class="ff-btn ff-btn-secondary ff-btn-prev">
                <span class="ff-btn-arrow">&larr;</span>
                <?php echo esc_html($btn_back); ?>
            </button>
            <button type="submit" class="ff-btn ff-btn-primary ff-btn-next" id="ff-schedule-continue">
                <span class="ff-btn-text ff-btn-text-skip"><?php esc_html_e('Skip & Continue', 'formflow-lite'); ?></span>
                <span class="ff-btn-text ff-btn-text-confirm" style="display:none;"><?php esc_html_e('Confirm Appointment', 'formflow-lite'); ?></span>
                <span class="ff-btn-arrow">&rarr;</span>
            </button>
        </div>
    </form>
</div>
