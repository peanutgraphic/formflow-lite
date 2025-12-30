<?php
/**
 * Hooks Reference
 *
 * This file documents all available hooks (actions and filters) in the plugin.
 * It serves as both documentation and a reference for developers.
 *
 * @package FormFlow
 * @since 2.0.0
 */

namespace FFFL;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Hooks
 *
 * Contains constants for all hook names and helper methods for hook documentation.
 */
class Hooks {

    // =========================================================================
    // CONNECTOR HOOKS
    // =========================================================================

    /**
     * Action: Register API connectors
     *
     * External plugins should hook here to register their connectors.
     *
     * @param ConnectorRegistry $registry The registry instance
     *
     * @example
     * add_action('fffl_register_connectors', function($registry) {
     *     $registry->register(new MyApiConnector());
     * });
     */
    public const REGISTER_CONNECTORS = 'fffl_register_connectors';

    /**
     * Action: After bundled connectors are loaded
     *
     * Fires after all bundled connectors have been loaded.
     */
    public const CONNECTORS_LOADED = 'fffl_connectors_loaded';

    /**
     * Action: Connector registered
     *
     * @param string $id Connector ID
     * @param ApiConnectorInterface $connector The connector instance
     */
    public const CONNECTOR_REGISTERED = 'fffl_connector_registered';

    /**
     * Filter: Modify registered connectors
     *
     * @param array $connectors Array of registered connectors
     * @return array Modified connectors array
     */
    public const REGISTERED_CONNECTORS = 'fffl_registered_connectors';

    /**
     * Filter: Get default connector ID
     *
     * @param string $default_id The default connector ID
     * @param array $connectors All registered connectors
     * @return string Modified default ID
     */
    public const DEFAULT_CONNECTOR = 'fffl_default_connector';

    // =========================================================================
    // BRANDING HOOKS
    // =========================================================================

    /**
     * Filter: Modify branding settings
     *
     * @param array $settings Current branding settings
     * @return array Modified settings
     */
    public const BRANDING_SETTINGS = 'fffl_branding_settings';

    /**
     * Filter: Custom CSS output
     *
     * @param string $css Generated CSS
     * @param array $settings Branding settings
     * @return string Modified CSS
     */
    public const BRANDING_CSS = 'fffl_branding_css';

    // =========================================================================
    // FORM HOOKS
    // =========================================================================

    /**
     * Filter: Modify form configuration before rendering
     *
     * @param array $config Form configuration
     * @param int $instance_id Instance ID
     * @return array Modified configuration
     */
    public const FORM_CONFIG = 'fffl_form_config';

    /**
     * Filter: Modify form data before submission
     *
     * @param array $data Form data
     * @param int $instance_id Instance ID
     * @return array Modified data
     */
    public const FORM_DATA = 'fffl_form_data';

    /**
     * Action: Before form step renders
     *
     * @param int $step Current step number
     * @param array $data Form data
     * @param int $instance_id Instance ID
     */
    public const BEFORE_STEP = 'fffl_before_step';

    /**
     * Action: After form step renders
     *
     * @param int $step Current step number
     * @param array $data Form data
     * @param int $instance_id Instance ID
     */
    public const AFTER_STEP = 'fffl_after_step';

    /**
     * Filter: Validate step data
     *
     * @param array $errors Existing errors
     * @param int $step Step number
     * @param array $data Step data
     * @param int $instance_id Instance ID
     * @return array Modified errors
     */
    public const VALIDATE_STEP = 'fffl_validate_step';

    // =========================================================================
    // ENROLLMENT HOOKS
    // =========================================================================

    /**
     * Filter: Modify enrollment data before API submission
     *
     * @param array $data Enrollment data
     * @param int $instance_id Instance ID
     * @return array Modified data
     */
    public const ENROLLMENT_DATA = 'fffl_enrollment_data';

    /**
     * Action: After successful enrollment
     *
     * @param int $submission_id Submission ID
     * @param int $instance_id Instance ID
     * @param array $form_data Form data
     */
    public const ENROLLMENT_COMPLETED = 'fffl_enrollment_completed';

    /**
     * Action: After failed enrollment
     *
     * @param int $submission_id Submission ID
     * @param int $instance_id Instance ID
     * @param array $error Error details
     */
    public const ENROLLMENT_FAILED = 'fffl_enrollment_failed';

    /**
     * Action: After account validation
     *
     * @param string $account_number Account number
     * @param int $instance_id Instance ID
     * @param array $result Validation result
     */
    public const ACCOUNT_VALIDATED = 'fffl_account_validated';

    // =========================================================================
    // SCHEDULING HOOKS
    // =========================================================================

    /**
     * Filter: Modify available schedule slots
     *
     * @param array $slots Available slots
     * @param array $data Request data
     * @param int $instance_id Instance ID
     * @return array Modified slots
     */
    public const SCHEDULE_SLOTS = 'fffl_schedule_slots';

    /**
     * Action: After appointment booked
     *
     * @param int $submission_id Submission ID
     * @param array $schedule_data Schedule data
     */
    public const APPOINTMENT_BOOKED = 'fffl_appointment_booked';

    /**
     * Action: After appointment cancelled
     *
     * @param int $submission_id Submission ID
     * @param string $reason Cancellation reason
     */
    public const APPOINTMENT_CANCELLED = 'fffl_appointment_cancelled';

    /**
     * Action: After appointment rescheduled
     *
     * @param int $submission_id Submission ID
     * @param array $old_schedule Old schedule
     * @param array $new_schedule New schedule
     */
    public const APPOINTMENT_RESCHEDULED = 'fffl_appointment_rescheduled';

    // =========================================================================
    // NOTIFICATION HOOKS
    // =========================================================================

    /**
     * Filter: Modify confirmation email content
     *
     * @param string $content Email content
     * @param array $form_data Form data
     * @param int $instance_id Instance ID
     * @return string Modified content
     */
    public const CONFIRMATION_EMAIL_CONTENT = 'fffl_confirmation_email_content';

    /**
     * Filter: Modify email headers
     *
     * @param array $headers Email headers
     * @param string $email_type Email type (confirmation, reminder, etc.)
     * @param int $instance_id Instance ID
     * @return array Modified headers
     */
    public const EMAIL_HEADERS = 'fffl_email_headers';

    /**
     * Filter: Modify SMS message content
     *
     * @param string $message SMS message
     * @param string $sms_type SMS type
     * @param array $data Submission data
     * @return string Modified message
     */
    public const SMS_MESSAGE = 'fffl_sms_message';

    /**
     * Action: Before notification sent
     *
     * @param string $type Notification type
     * @param array $data Notification data
     */
    public const BEFORE_NOTIFICATION = 'fffl_before_notification';

    /**
     * Action: After notification sent
     *
     * @param string $type Notification type
     * @param array $data Notification data
     * @param bool $success Whether send was successful
     */
    public const AFTER_NOTIFICATION = 'fffl_after_notification';

    // =========================================================================
    // WEBHOOK HOOKS
    // =========================================================================

    /**
     * Filter: Modify webhook payload
     *
     * @param array $payload Webhook payload
     * @param string $event Event name
     * @param int $instance_id Instance ID
     * @return array Modified payload
     */
    public const WEBHOOK_PAYLOAD = 'fffl_webhook_payload';

    /**
     * Action: After webhook delivered
     *
     * @param string $event Event name
     * @param string $url Webhook URL
     * @param int $status HTTP status code
     */
    public const WEBHOOK_DELIVERED = 'fffl_webhook_delivered';

    // =========================================================================
    // ADMIN HOOKS
    // =========================================================================

    /**
     * Filter: Modify admin menu items
     *
     * @param array $menu_items Menu items
     * @return array Modified menu items
     */
    public const ADMIN_MENU_ITEMS = 'fffl_admin_menu_items';

    /**
     * Action: Admin settings saved
     *
     * @param array $settings New settings
     * @param array $old_settings Previous settings
     */
    public const SETTINGS_SAVED = 'fffl_settings_saved';

    /**
     * Filter: Modify instance settings before save
     *
     * @param array $settings Instance settings
     * @param int $instance_id Instance ID
     * @return array Modified settings
     */
    public const INSTANCE_SETTINGS = 'fffl_instance_settings';

    // =========================================================================
    // FEATURE HOOKS
    // =========================================================================

    /**
     * Filter: Modify available features
     *
     * @param array $features Available features
     * @return array Modified features
     */
    public const AVAILABLE_FEATURES = 'fffl_available_features';

    /**
     * Filter: Check if feature is enabled
     *
     * @param bool $enabled Whether feature is enabled
     * @param string $feature Feature key
     * @param int $instance_id Instance ID
     * @return bool Modified enabled state
     */
    public const FEATURE_ENABLED = 'fffl_feature_enabled';

    /**
     * Action: Feature toggled
     *
     * @param string $feature Feature key
     * @param bool $enabled New enabled state
     * @param int $instance_id Instance ID
     */
    public const FEATURE_TOGGLED = 'fffl_feature_toggled';

    // =========================================================================
    // SECURITY HOOKS
    // =========================================================================

    /**
     * Filter: Modify fraud risk score
     *
     * @param int $score Risk score (0-100)
     * @param array $checks Individual check results
     * @param array $data Submission data
     * @return int Modified score
     */
    public const FRAUD_RISK_SCORE = 'fffl_fraud_risk_score';

    /**
     * Action: High risk submission detected
     *
     * @param int $score Risk score
     * @param array $data Submission data
     * @param string $action Action taken (flag, block, challenge)
     */
    public const FRAUD_DETECTED = 'fffl_fraud_detected';

    /**
     * Filter: Rate limit parameters
     *
     * @param array $params Rate limit params (requests, window)
     * @param string $context Context (form, api, etc.)
     * @return array Modified params
     */
    public const RATE_LIMIT = 'fffl_rate_limit';

    // =========================================================================
    // DATA HOOKS
    // =========================================================================

    /**
     * Filter: Modify submission data before storage
     *
     * @param array $data Submission data
     * @param int $instance_id Instance ID
     * @return array Modified data
     */
    public const BEFORE_SAVE_SUBMISSION = 'fffl_before_save_submission';

    /**
     * Action: After submission saved
     *
     * @param int $submission_id Submission ID
     * @param array $data Submission data
     */
    public const AFTER_SAVE_SUBMISSION = 'fffl_after_save_submission';

    /**
     * Filter: Export data columns
     *
     * @param array $columns Export columns
     * @param string $format Export format (csv, json, etc.)
     * @return array Modified columns
     */
    public const EXPORT_COLUMNS = 'fffl_export_columns';

    // =========================================================================
    // ANALYTICS & ATTRIBUTION HOOKS
    // =========================================================================

    /**
     * Action: UTM parameters captured from URL
     *
     * Fires when UTM parameters are captured from the URL.
     * Compatible with Peanut Suite integration.
     *
     * @param array $utm_data UTM parameter data (utm_source, utm_medium, utm_campaign, etc.)
     * @param string $visitor_id Visitor ID
     */
    public const UTM_CAPTURED = 'fffl_utm_captured';

    /**
     * Action: Form viewed by visitor
     *
     * Fires when a form is rendered/viewed.
     * Compatible with Peanut Suite integration.
     *
     * @param int $instance_id Instance ID
     * @param string $visitor_id Visitor ID
     * @param array $context Additional context (form_type, url, referrer, etc.)
     */
    public const FORM_VIEWED = 'fffl_form_viewed';

    /**
     * Action: Form submission completed successfully
     *
     * Fires when a form submission is completed.
     * Compatible with Peanut Suite integration.
     *
     * @param array $submission_data Full submission data including:
     *   - submission_id: int
     *   - instance_id: int
     *   - visitor_id: string
     *   - form_data: array
     *   - utm_data: array
     *   - status: string
     */
    public const FORM_COMPLETED = 'fffl_form_completed';

    /**
     * Action: Form step completed
     *
     * Fires when a user advances to the next form step.
     *
     * @param int $instance_id Instance ID
     * @param int $step Step number
     * @param string $visitor_id Visitor ID
     * @param array $step_data Data collected in this step
     */
    public const FORM_STEP_COMPLETED = 'fffl_form_step_completed';

    /**
     * Action: Visitor identified
     *
     * Fires when a visitor ID is created or retrieved.
     *
     * @param string $visitor_id Visitor ID
     * @param bool $is_new Whether this is a new visitor
     * @param array $device_info Device/browser information
     */
    public const VISITOR_IDENTIFIED = 'fffl_visitor_identified';

    /**
     * Action: Handoff to external enrollment
     *
     * Fires when a user is redirected to an external enrollment page.
     *
     * @param int $instance_id Instance ID
     * @param string $visitor_id Visitor ID
     * @param string $destination_url External URL
     * @param array $utm_data Attribution data passed along
     */
    public const HANDOFF_REDIRECT = 'fffl_handoff_redirect';

    /**
     * Action: External completion received
     *
     * Fires when a completion notification is received from an external system.
     *
     * @param array $completion_data Completion data
     * @param string $source Source type (webhook, redirect, import)
     */
    public const EXTERNAL_COMPLETION = 'fffl_external_completion';

    // =========================================================================
    // PEANUT SUITE INTEGRATION HOOKS
    // =========================================================================

    /**
     * Filter: Get visitor ID (integrates with Peanut Suite)
     *
     * Allows Peanut Suite to provide a shared visitor ID.
     *
     * @param string|null $visitor_id Current visitor ID
     * @return string Visitor ID (possibly from Peanut Suite)
     */
    public const GET_VISITOR_ID = 'fffl_get_visitor_id';

    /**
     * Filter: Sanitize input fields (integrates with Peanut Suite)
     *
     * Allows Peanut Suite security utilities to sanitize input.
     *
     * @param array $fields Input fields
     * @param string $context Context (form, api, etc.)
     * @return array Sanitized fields
     */
    public const SANITIZE_FIELDS = 'fffl_sanitize_fields';

    /**
     * Filter: Check rate limit (integrates with Peanut Suite)
     *
     * Allows Peanut Suite to handle rate limiting.
     *
     * @param bool $allowed Whether action is allowed
     * @param string $action Action identifier
     * @param int $limit Requests limit
     * @param int $window Time window in seconds
     * @return bool Whether action is allowed
     */
    public const CHECK_RATE_LIMIT = 'fffl_check_rate_limit';

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get all documented hooks
     *
     * @return array Hook names grouped by category
     */
    public static function get_all(): array {
        return [
            'Connectors' => [
                self::REGISTER_CONNECTORS,
                self::CONNECTORS_LOADED,
                self::CONNECTOR_REGISTERED,
                self::REGISTERED_CONNECTORS,
                self::DEFAULT_CONNECTOR,
            ],
            'Branding' => [
                self::BRANDING_SETTINGS,
                self::BRANDING_CSS,
            ],
            'Forms' => [
                self::FORM_CONFIG,
                self::FORM_DATA,
                self::BEFORE_STEP,
                self::AFTER_STEP,
                self::VALIDATE_STEP,
            ],
            'Enrollment' => [
                self::ENROLLMENT_DATA,
                self::ENROLLMENT_COMPLETED,
                self::ENROLLMENT_FAILED,
                self::ACCOUNT_VALIDATED,
            ],
            'Scheduling' => [
                self::SCHEDULE_SLOTS,
                self::APPOINTMENT_BOOKED,
                self::APPOINTMENT_CANCELLED,
                self::APPOINTMENT_RESCHEDULED,
            ],
            'Notifications' => [
                self::CONFIRMATION_EMAIL_CONTENT,
                self::EMAIL_HEADERS,
                self::SMS_MESSAGE,
                self::BEFORE_NOTIFICATION,
                self::AFTER_NOTIFICATION,
            ],
            'Webhooks' => [
                self::WEBHOOK_PAYLOAD,
                self::WEBHOOK_DELIVERED,
            ],
            'Admin' => [
                self::ADMIN_MENU_ITEMS,
                self::SETTINGS_SAVED,
                self::INSTANCE_SETTINGS,
            ],
            'Features' => [
                self::AVAILABLE_FEATURES,
                self::FEATURE_ENABLED,
                self::FEATURE_TOGGLED,
            ],
            'Security' => [
                self::FRAUD_RISK_SCORE,
                self::FRAUD_DETECTED,
                self::RATE_LIMIT,
            ],
            'Data' => [
                self::BEFORE_SAVE_SUBMISSION,
                self::AFTER_SAVE_SUBMISSION,
                self::EXPORT_COLUMNS,
            ],
            'Analytics' => [
                self::UTM_CAPTURED,
                self::FORM_VIEWED,
                self::FORM_COMPLETED,
                self::FORM_STEP_COMPLETED,
                self::VISITOR_IDENTIFIED,
                self::HANDOFF_REDIRECT,
                self::EXTERNAL_COMPLETION,
            ],
            'Peanut Suite' => [
                self::GET_VISITOR_ID,
                self::SANITIZE_FIELDS,
                self::CHECK_RATE_LIMIT,
            ],
        ];
    }

    /**
     * Get hooks by category
     *
     * @param string $category Category name
     * @return array Hook names
     */
    public static function get_category(string $category): array {
        $all = self::get_all();
        return $all[$category] ?? [];
    }
}
