<?php
/**
 * Peanut Suite Integration
 *
 * Provides integration between FormFlow and the Peanut Suite ecosystem.
 * Enables shared visitor tracking, UTM attribution, and security utilities.
 *
 * @package FormFlow
 * @since 2.7.0
 */

namespace FFFL;

if (!defined('ABSPATH')) {
    exit;
}

use FFFL\Database\Database;

/**
 * Peanut Suite Webhook Preset Configuration
 *
 * Handles automatic webhook setup for Peanut Suite integration.
 */
class PeanutWebhookPreset {

    /**
     * Default webhook name
     */
    public const WEBHOOK_NAME = 'Peanut Suite Integration';

    /**
     * All events to send to Peanut Suite
     */
    public const EVENTS = [
        'form.viewed',
        'form.step_completed',
        'account.validated',
        'enrollment.submitted',
        'enrollment.completed',
        'enrollment.failed',
        'appointment.scheduled',
    ];

    /**
     * Get the Peanut Suite internal webhook URL
     *
     * @return string REST API endpoint URL
     */
    public static function get_webhook_url(): string {
        return rest_url('peanut/v1/formflow/event');
    }

    /**
     * Generate a secure webhook secret
     *
     * @return string HMAC secret key
     */
    public static function generate_secret(): string {
        return wp_generate_password(32, true, true);
    }

    /**
     * Check if Peanut Suite webhook exists
     *
     * @param Database $db Database instance
     * @return array|null Existing webhook or null
     */
    public static function get_existing(Database $db): ?array {
        $webhooks = $db->get_webhooks(null, false);

        foreach ($webhooks as $webhook) {
            if ($webhook['name'] === self::WEBHOOK_NAME ||
                strpos($webhook['url'], 'peanut/v1/formflow') !== false) {
                return $webhook;
            }
        }

        return null;
    }

    /**
     * Create the Peanut Suite webhook
     *
     * @param Database $db Database instance
     * @return int|false Webhook ID or false on failure
     */
    public static function create(Database $db): int|false {
        $secret = self::generate_secret();

        // Store secret in options for Peanut Suite to retrieve
        update_option('fffl_peanut_webhook_secret', $secret);

        return $db->create_webhook([
            'instance_id' => null, // All instances
            'name' => self::WEBHOOK_NAME,
            'url' => self::get_webhook_url(),
            'events' => self::EVENTS,
            'secret' => $secret,
            'is_active' => 1,
        ]);
    }

    /**
     * Update existing webhook to ensure correct configuration
     *
     * @param Database $db Database instance
     * @param int $webhook_id Existing webhook ID
     * @return bool Success
     */
    public static function update(Database $db, int $webhook_id): bool {
        return $db->update_webhook($webhook_id, [
            'url' => self::get_webhook_url(),
            'events' => self::EVENTS,
            'is_active' => 1,
        ]);
    }
}

/**
 * Class PeanutIntegration
 *
 * Handles bidirectional integration with Peanut Suite plugins:
 * - Peanut Suite (core)
 * - Peanut Marketing Suite
 * - Peanut Shared utilities
 */
class PeanutIntegration {

    /**
     * Singleton instance
     */
    private static ?PeanutIntegration $instance = null;

    /**
     * Whether Peanut Suite is available
     */
    private bool $peanut_available = false;

    /**
     * Whether Peanut Shared is available
     */
    private bool $peanut_shared_available = false;

    /**
     * Get singleton instance
     */
    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->detect_peanut_suite();
        $this->register_hooks();

        // Auto-configure Peanut webhook on admin init
        if ($this->peanut_available) {
            add_action('admin_init', [$this, 'maybe_configure_webhook']);
        }
    }

    /**
     * Auto-configure Peanut Suite webhook if not already set up
     *
     * This runs once when Peanut Suite is detected and creates
     * the internal webhook for seamless data sharing.
     */
    public function maybe_configure_webhook(): void {
        // Only run once per version
        $configured_version = get_option('fffl_peanut_webhook_version', '');
        if ($configured_version === FFFL_VERSION) {
            return;
        }

        $db = new Database();
        $existing = PeanutWebhookPreset::get_existing($db);

        if ($existing) {
            // Update existing webhook to ensure correct configuration
            PeanutWebhookPreset::update($db, (int)$existing['id']);
        } else {
            // Create new Peanut Suite webhook
            PeanutWebhookPreset::create($db);
        }

        // Mark as configured for this version
        update_option('fffl_peanut_webhook_version', FFFL_VERSION);

        // Log the auto-configuration
        $db->log(
            'info',
            'Peanut Suite webhook auto-configured',
            ['version' => FFFL_VERSION]
        );
    }

    /**
     * Detect Peanut Suite availability
     */
    private function detect_peanut_suite(): void {
        // Check for Peanut Shared (visitor tracking, security)
        $this->peanut_shared_available = class_exists('Peanut_Visitor') || class_exists('\\Peanut\\Shared\\Visitor');

        // Check for Peanut Suite core
        $this->peanut_available = defined('PEANUT_SUITE_VERSION') || function_exists('peanut_suite');
    }

    /**
     * Register integration hooks
     */
    private function register_hooks(): void {
        // Only register if Peanut Suite is available
        if ($this->peanut_shared_available || $this->peanut_available) {
            // Filter to get shared visitor ID
            add_filter(Hooks::GET_VISITOR_ID, [$this, 'get_peanut_visitor_id'], 10, 1);

            // Filter to use Peanut security utilities
            add_filter(Hooks::SANITIZE_FIELDS, [$this, 'peanut_sanitize_fields'], 10, 2);
            add_filter(Hooks::CHECK_RATE_LIMIT, [$this, 'peanut_check_rate_limit'], 10, 4);

            // Forward FormFlow events to Peanut Suite
            add_action(Hooks::UTM_CAPTURED, [$this, 'forward_utm_captured'], 10, 2);
            add_action(Hooks::FORM_COMPLETED, [$this, 'forward_form_completed'], 10, 1);
            add_action(Hooks::FORM_VIEWED, [$this, 'forward_form_viewed'], 10, 3);
        }
    }

    /**
     * Check if Peanut Suite is available
     */
    public function is_available(): bool {
        return $this->peanut_available || $this->peanut_shared_available;
    }

    /**
     * Get visitor ID from Peanut Suite if available
     *
     * @param string|null $visitor_id Current visitor ID
     * @return string Visitor ID
     */
    public function get_peanut_visitor_id(?string $visitor_id): string {
        // If we already have a visitor ID, check if we should use Peanut's instead
        if ($this->peanut_shared_available) {
            // Try Peanut_Visitor class (peanut-shared plugin)
            if (class_exists('Peanut_Visitor')) {
                $peanut_id = \Peanut_Visitor::get_or_create_id();
                if ($peanut_id) {
                    return $peanut_id;
                }
            }

            // Try namespaced version
            if (class_exists('\\Peanut\\Shared\\Visitor')) {
                $peanut_id = \Peanut\Shared\Visitor::get_or_create_id();
                if ($peanut_id) {
                    return $peanut_id;
                }
            }
        }

        // Fall back to provided visitor ID
        return $visitor_id ?? '';
    }

    /**
     * Use Peanut Security utilities for field sanitization
     *
     * @param array $fields Input fields
     * @param string $context Context (form, api, etc.)
     * @return array Sanitized fields
     */
    public function peanut_sanitize_fields(array $fields, string $context): array {
        if ($this->peanut_shared_available) {
            // Try Peanut_Security class
            if (class_exists('Peanut_Security') && method_exists('Peanut_Security', 'sanitize_fields')) {
                return \Peanut_Security::sanitize_fields($fields);
            }

            // Try namespaced version
            if (class_exists('\\Peanut\\Shared\\Security') && method_exists('\\Peanut\\Shared\\Security', 'sanitize_fields')) {
                return \Peanut\Shared\Security::sanitize_fields($fields);
            }
        }

        // Fall back to original fields
        return $fields;
    }

    /**
     * Use Peanut Security utilities for rate limiting
     *
     * @param bool $allowed Whether action is allowed
     * @param string $action Action identifier
     * @param int $limit Requests limit
     * @param int $window Time window in seconds
     * @return bool Whether action is allowed
     */
    public function peanut_check_rate_limit(bool $allowed, string $action, int $limit, int $window): bool {
        if (!$allowed) {
            return false; // Already blocked
        }

        if ($this->peanut_shared_available) {
            // Try Peanut_Security class
            if (class_exists('Peanut_Security') && method_exists('Peanut_Security', 'check_rate_limit')) {
                return \Peanut_Security::check_rate_limit($action, $limit, $window);
            }

            // Try namespaced version
            if (class_exists('\\Peanut\\Shared\\Security') && method_exists('\\Peanut\\Shared\\Security', 'check_rate_limit')) {
                return \Peanut\Shared\Security::check_rate_limit($action, $limit, $window);
            }
        }

        return $allowed;
    }

    /**
     * Forward UTM captured event to Peanut Suite
     *
     * @param array $utm_data UTM parameters
     * @param string $visitor_id Visitor ID
     */
    public function forward_utm_captured(array $utm_data, string $visitor_id): void {
        if ($this->peanut_available) {
            /**
             * Fire Peanut Suite compatible action
             *
             * @param array $utm_data UTM parameters
             * @param string $visitor_id Visitor ID
             */
            do_action('peanut_utm_captured', $utm_data, $visitor_id);
        }
    }

    /**
     * Forward form completed event to Peanut Suite
     *
     * @param array $submission_data Full submission data
     */
    public function forward_form_completed(array $submission_data): void {
        if ($this->peanut_available) {
            // Build Peanut-compatible conversion data
            $conversion_data = [
                'source' => 'formflow-lite',
                'form_id' => $submission_data['instance_id'] ?? 0,
                'submission_id' => $submission_data['submission_id'] ?? 0,
                'visitor_id' => $submission_data['visitor_id'] ?? '',
                'email' => $submission_data['form_data']['email'] ?? '',
                'first_name' => $submission_data['form_data']['first_name'] ?? '',
                'last_name' => $submission_data['form_data']['last_name'] ?? '',
                'phone' => $submission_data['form_data']['phone'] ?? '',
                'utm_source' => $submission_data['utm_data']['utm_source'] ?? '',
                'utm_medium' => $submission_data['utm_data']['utm_medium'] ?? '',
                'utm_campaign' => $submission_data['utm_data']['utm_campaign'] ?? '',
                'conversion_type' => 'enrollment',
                'value' => $submission_data['form_data']['value'] ?? null,
                'metadata' => [
                    'instance_slug' => $submission_data['instance_slug'] ?? '',
                    'form_type' => $submission_data['form_type'] ?? 'enrollment',
                    'account_number' => $submission_data['form_data']['account_number'] ?? '',
                ],
            ];

            /**
             * Fire Peanut Suite compatible conversion action
             *
             * @param array $conversion_data Conversion data
             */
            do_action('peanut_conversion', $conversion_data);
        }
    }

    /**
     * Forward form viewed event to Peanut Suite
     *
     * @param int $instance_id Instance ID
     * @param string $visitor_id Visitor ID
     * @param array $context Additional context
     */
    public function forward_form_viewed(int $instance_id, string $visitor_id, array $context): void {
        if ($this->peanut_available) {
            /**
             * Fire Peanut Suite compatible form view action
             *
             * @param int $instance_id Instance ID
             * @param string $visitor_id Visitor ID
             */
            do_action('peanut_form_view', $instance_id, $visitor_id);
        }
    }

    /**
     * Get integration status for diagnostics
     *
     * @return array Status information
     */
    public function get_status(): array {
        return [
            'peanut_suite_available' => $this->peanut_available,
            'peanut_shared_available' => $this->peanut_shared_available,
            'visitor_class' => class_exists('Peanut_Visitor') ? 'Peanut_Visitor' : (class_exists('\\Peanut\\Shared\\Visitor') ? 'Peanut\\Shared\\Visitor' : 'none'),
            'security_class' => class_exists('Peanut_Security') ? 'Peanut_Security' : (class_exists('\\Peanut\\Shared\\Security') ? 'Peanut\\Shared\\Security' : 'none'),
            'hooks_registered' => $this->peanut_available || $this->peanut_shared_available,
        ];
    }
}
