<?php
/**
 * Branding Configuration
 *
 * Manages white-label branding settings for the plugin.
 * Allows customization of plugin name, colors, logos, and text.
 *
 * @package FormFlow
 * @since 2.0.0
 */

namespace FFFL;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Branding
 */
class Branding {

    /**
     * Singleton instance
     */
    private static ?Branding $instance = null;

    /**
     * Branding settings cache
     */
    private array $settings = [];

    /**
     * Default branding values
     */
    private const DEFAULTS = [
        // Plugin identity
        'plugin_name' => 'FormFlow Pro',
        'plugin_short_name' => 'FormFlow',
        'plugin_slug' => 'formflow-pro',
        'plugin_description' => 'Multi-step enrollment and scheduling forms with API integration',

        // Company/Developer info
        'company_name' => '',
        'company_url' => '',
        'support_email' => '',
        'support_url' => '',

        // Visual branding
        'primary_color' => '#4F46E5',
        'secondary_color' => '#10B981',
        'accent_color' => '#F59E0B',
        'logo_url' => '',
        'icon_url' => '',
        'favicon_url' => '',

        // Admin menu
        'menu_icon' => 'dashicons-feedback',
        'menu_position' => 30,

        // Text customization
        'form_title' => 'Enrollment Form',
        'form_subtitle' => 'Complete the form below to enroll',
        'success_title' => 'Thank You!',
        'success_message' => 'Your enrollment has been submitted successfully.',

        // Footer/Attribution
        'show_powered_by' => true,
        'powered_by_text' => 'Powered by FormFlow Pro',
        'powered_by_url' => '',

        // Email branding
        'email_from_name' => '',
        'email_logo_url' => '',
        'email_footer_text' => '',
    ];

    /**
     * Get singleton instance
     *
     * @return Branding
     */
    public static function instance(): Branding {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->load_settings();
    }

    /**
     * Load settings from database
     */
    private function load_settings(): void {
        $saved = get_option('fffl_branding', []);
        $this->settings = wp_parse_args($saved, self::DEFAULTS);

        /**
         * Filter: Modify branding settings
         *
         * @param array $settings Current branding settings
         * @return array Modified settings
         */
        $this->settings = apply_filters('fffl_branding_settings', $this->settings);
    }

    /**
     * Get a branding setting
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not set
     * @return mixed
     */
    public function get(string $key, $default = null) {
        if ($default === null && isset(self::DEFAULTS[$key])) {
            $default = self::DEFAULTS[$key];
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Get all branding settings
     *
     * @return array
     */
    public function get_all(): array {
        return $this->settings;
    }

    /**
     * Set a branding setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return self
     */
    public function set(string $key, $value): self {
        $this->settings[$key] = $value;
        return $this;
    }

    /**
     * Update multiple settings
     *
     * @param array $settings Settings to update
     * @return self
     */
    public function update(array $settings): self {
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                $this->settings[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Save settings to database
     *
     * @return bool
     */
    public function save(): bool {
        return update_option('fffl_branding', $this->settings);
    }

    /**
     * Reset to defaults
     *
     * @param string|null $key Specific key to reset, or null for all
     * @return self
     */
    public function reset(?string $key = null): self {
        if ($key !== null) {
            $this->settings[$key] = self::DEFAULTS[$key] ?? '';
        } else {
            $this->settings = self::DEFAULTS;
        }
        return $this;
    }

    /**
     * Get plugin name
     *
     * @return string
     */
    public function get_plugin_name(): string {
        return $this->get('plugin_name');
    }

    /**
     * Get plugin short name (for compact displays)
     *
     * @return string
     */
    public function get_short_name(): string {
        return $this->get('plugin_short_name');
    }

    /**
     * Get admin menu icon
     *
     * @return string
     */
    public function get_menu_icon(): string {
        $icon = $this->get('icon_url');
        if (!empty($icon)) {
            return $icon;
        }
        return $this->get('menu_icon');
    }

    /**
     * Get CSS for custom colors
     *
     * @return string
     */
    public function get_custom_css(): string {
        $primary = $this->get('primary_color');
        $secondary = $this->get('secondary_color');
        $accent = $this->get('accent_color');

        return "
            :root {
                --ff-primary-color: {$primary};
                --ff-secondary-color: {$secondary};
                --ff-accent-color: {$accent};
            }
            .ff-btn-primary { background-color: {$primary}; }
            .ff-btn-primary:hover { background-color: color-mix(in srgb, {$primary} 85%, black); }
            .ff-progress-bar .ff-progress-fill { background-color: {$primary}; }
            .ff-step.active .ff-step-number { background-color: {$primary}; }
            .ff-link { color: {$primary}; }
        ";
    }

    /**
     * Get email header HTML
     *
     * @return string
     */
    public function get_email_header(): string {
        $logo = $this->get('email_logo_url') ?: $this->get('logo_url');
        $name = $this->get('plugin_name');
        $primary = $this->get('primary_color');

        $html = '<div style="background-color: ' . esc_attr($primary) . '; padding: 20px; text-align: center;">';

        if (!empty($logo)) {
            $html .= '<img src="' . esc_url($logo) . '" alt="' . esc_attr($name) . '" style="max-height: 60px; width: auto;">';
        } else {
            $html .= '<h1 style="color: #ffffff; margin: 0; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">' . esc_html($name) . '</h1>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get email footer HTML
     *
     * @return string
     */
    public function get_email_footer(): string {
        $footer_text = $this->get('email_footer_text');
        $company = $this->get('company_name');
        $powered_by = $this->get('show_powered_by');

        $html = '<div style="background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666;">';

        if (!empty($footer_text)) {
            $html .= '<p>' . wp_kses_post($footer_text) . '</p>';
        }

        if (!empty($company)) {
            $html .= '<p>&copy; ' . date('Y') . ' ' . esc_html($company) . '</p>';
        }

        if ($powered_by && $this->get('powered_by_text')) {
            $powered_url = $this->get('powered_by_url');
            $powered_text = $this->get('powered_by_text');

            if (!empty($powered_url)) {
                $html .= '<p><a href="' . esc_url($powered_url) . '" style="color: #999;">' . esc_html($powered_text) . '</a></p>';
            } else {
                $html .= '<p style="color: #999;">' . esc_html($powered_text) . '</p>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get powered by HTML for forms
     *
     * @return string
     */
    public function get_powered_by_html(): string {
        if (!$this->get('show_powered_by')) {
            return '';
        }

        $text = $this->get('powered_by_text');
        $url = $this->get('powered_by_url');

        if (empty($text)) {
            return '';
        }

        $html = '<div class="ff-powered-by">';

        if (!empty($url)) {
            $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">';
            $html .= esc_html($text);
            $html .= '</a>';
        } else {
            $html .= esc_html($text);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get branding field definitions for admin settings
     *
     * @return array
     */
    public static function get_field_definitions(): array {
        return [
            'identity' => [
                'label' => __('Plugin Identity', 'formflow-lite'),
                'fields' => [
                    'plugin_name' => [
                        'label' => __('Plugin Name', 'formflow-lite'),
                        'type' => 'text',
                        'description' => __('The main name displayed in the admin menu and throughout the plugin', 'formflow-lite'),
                    ],
                    'plugin_short_name' => [
                        'label' => __('Short Name', 'formflow-lite'),
                        'type' => 'text',
                        'description' => __('Abbreviated name for compact displays', 'formflow-lite'),
                    ],
                    'plugin_description' => [
                        'label' => __('Description', 'formflow-lite'),
                        'type' => 'textarea',
                        'description' => __('Brief description of what the plugin does', 'formflow-lite'),
                    ],
                ],
            ],
            'company' => [
                'label' => __('Company Information', 'formflow-lite'),
                'fields' => [
                    'company_name' => [
                        'label' => __('Company Name', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'company_url' => [
                        'label' => __('Company Website', 'formflow-lite'),
                        'type' => 'url',
                    ],
                    'support_email' => [
                        'label' => __('Support Email', 'formflow-lite'),
                        'type' => 'email',
                    ],
                    'support_url' => [
                        'label' => __('Support URL', 'formflow-lite'),
                        'type' => 'url',
                    ],
                ],
            ],
            'visual' => [
                'label' => __('Visual Branding', 'formflow-lite'),
                'fields' => [
                    'primary_color' => [
                        'label' => __('Primary Color', 'formflow-lite'),
                        'type' => 'color',
                        'description' => __('Main brand color for buttons and accents', 'formflow-lite'),
                    ],
                    'secondary_color' => [
                        'label' => __('Secondary Color', 'formflow-lite'),
                        'type' => 'color',
                    ],
                    'accent_color' => [
                        'label' => __('Accent Color', 'formflow-lite'),
                        'type' => 'color',
                    ],
                    'logo_url' => [
                        'label' => __('Logo URL', 'formflow-lite'),
                        'type' => 'url',
                        'description' => __('Full URL to your logo image', 'formflow-lite'),
                    ],
                    'menu_icon' => [
                        'label' => __('Admin Menu Icon', 'formflow-lite'),
                        'type' => 'text',
                        'description' => __('Dashicon class (e.g., dashicons-feedback) or image URL', 'formflow-lite'),
                    ],
                ],
            ],
            'text' => [
                'label' => __('Text Customization', 'formflow-lite'),
                'fields' => [
                    'form_title' => [
                        'label' => __('Default Form Title', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'form_subtitle' => [
                        'label' => __('Default Form Subtitle', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'success_title' => [
                        'label' => __('Success Page Title', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'success_message' => [
                        'label' => __('Success Message', 'formflow-lite'),
                        'type' => 'textarea',
                    ],
                ],
            ],
            'attribution' => [
                'label' => __('Attribution', 'formflow-lite'),
                'fields' => [
                    'show_powered_by' => [
                        'label' => __('Show "Powered By"', 'formflow-lite'),
                        'type' => 'checkbox',
                    ],
                    'powered_by_text' => [
                        'label' => __('Powered By Text', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'powered_by_url' => [
                        'label' => __('Powered By URL', 'formflow-lite'),
                        'type' => 'url',
                    ],
                ],
            ],
            'email' => [
                'label' => __('Email Branding', 'formflow-lite'),
                'fields' => [
                    'email_from_name' => [
                        'label' => __('Email From Name', 'formflow-lite'),
                        'type' => 'text',
                    ],
                    'email_logo_url' => [
                        'label' => __('Email Logo URL', 'formflow-lite'),
                        'type' => 'url',
                    ],
                    'email_footer_text' => [
                        'label' => __('Email Footer Text', 'formflow-lite'),
                        'type' => 'textarea',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get default values
     *
     * @return array
     */
    public static function get_defaults(): array {
        return self::DEFAULTS;
    }
}

/**
 * Helper function to get branding instance
 *
 * @return Branding
 */
function fffl_branding(): Branding {
    return Branding::instance();
}
