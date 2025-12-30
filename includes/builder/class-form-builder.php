<?php
/**
 * Visual Form Builder
 *
 * Provides drag-and-drop form building capabilities with conditional logic.
 *
 * @package FormFlow
 * @since 2.6.0
 */

namespace FFFL\Builder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class FormBuilder
 *
 * Core form builder functionality.
 */
class FormBuilder {

    /**
     * Singleton instance
     */
    private static ?FormBuilder $instance = null;

    /**
     * Available field types
     */
    private array $field_types = [];

    /**
     * Get singleton instance
     */
    public static function instance(): FormBuilder {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        $this->register_default_field_types();
    }

    /**
     * Initialize builder hooks
     */
    public function init(): void {
        // Register REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_builder_assets']);

        // AJAX handlers
        add_action('wp_ajax_fffl_save_form_schema', [$this, 'ajax_save_form_schema']);
        add_action('wp_ajax_fffl_load_form_schema', [$this, 'ajax_load_form_schema']);
        add_action('wp_ajax_fffl_preview_form', [$this, 'ajax_preview_form']);
    }

    /**
     * Register default field types
     */
    private function register_default_field_types(): void {
        $this->field_types = [
            // Basic Fields
            'text' => [
                'label' => __('Text Input', 'formflow-lite'),
                'icon' => 'dashicons-editor-textcolor',
                'category' => 'basic',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'help_text' => ['type' => 'textarea', 'label' => __('Help Text', 'formflow-lite'), 'default' => ''],
                    'max_length' => ['type' => 'number', 'label' => __('Max Length', 'formflow-lite'), 'default' => ''],
                    'pattern' => ['type' => 'text', 'label' => __('Validation Pattern (regex)', 'formflow-lite'), 'default' => ''],
                ],
            ],
            'email' => [
                'label' => __('Email', 'formflow-lite'),
                'icon' => 'dashicons-email',
                'category' => 'basic',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Email Address', 'formflow-lite')],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => 'email@example.com'],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'confirm' => ['type' => 'checkbox', 'label' => __('Require Confirmation', 'formflow-lite'), 'default' => false],
                ],
            ],
            'phone' => [
                'label' => __('Phone Number', 'formflow-lite'),
                'icon' => 'dashicons-phone',
                'category' => 'basic',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Phone Number', 'formflow-lite')],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => '(555) 555-5555'],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'format' => ['type' => 'select', 'label' => __('Format', 'formflow-lite'), 'default' => 'us', 'options' => [
                        'us' => __('US Format', 'formflow-lite'),
                        'international' => __('International', 'formflow-lite'),
                    ]],
                ],
            ],
            'number' => [
                'label' => __('Number', 'formflow-lite'),
                'icon' => 'dashicons-calculator',
                'category' => 'basic',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'min' => ['type' => 'number', 'label' => __('Minimum Value', 'formflow-lite'), 'default' => ''],
                    'max' => ['type' => 'number', 'label' => __('Maximum Value', 'formflow-lite'), 'default' => ''],
                    'step' => ['type' => 'number', 'label' => __('Step', 'formflow-lite'), 'default' => '1'],
                ],
            ],
            'textarea' => [
                'label' => __('Text Area', 'formflow-lite'),
                'icon' => 'dashicons-editor-paragraph',
                'category' => 'basic',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'rows' => ['type' => 'number', 'label' => __('Rows', 'formflow-lite'), 'default' => 4],
                    'max_length' => ['type' => 'number', 'label' => __('Max Length', 'formflow-lite'), 'default' => ''],
                ],
            ],

            // Selection Fields
            'select' => [
                'label' => __('Dropdown', 'formflow-lite'),
                'icon' => 'dashicons-arrow-down-alt2',
                'category' => 'selection',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'options' => ['type' => 'options', 'label' => __('Options', 'formflow-lite'), 'default' => []],
                    'placeholder' => ['type' => 'text', 'label' => __('Placeholder', 'formflow-lite'), 'default' => __('Select an option', 'formflow-lite')],
                    'searchable' => ['type' => 'checkbox', 'label' => __('Searchable', 'formflow-lite'), 'default' => false],
                ],
            ],
            'radio' => [
                'label' => __('Radio Buttons', 'formflow-lite'),
                'icon' => 'dashicons-marker',
                'category' => 'selection',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'options' => ['type' => 'options', 'label' => __('Options', 'formflow-lite'), 'default' => []],
                    'layout' => ['type' => 'select', 'label' => __('Layout', 'formflow-lite'), 'default' => 'vertical', 'options' => [
                        'vertical' => __('Vertical', 'formflow-lite'),
                        'horizontal' => __('Horizontal', 'formflow-lite'),
                    ]],
                ],
            ],
            'checkbox' => [
                'label' => __('Checkboxes', 'formflow-lite'),
                'icon' => 'dashicons-yes',
                'category' => 'selection',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'options' => ['type' => 'options', 'label' => __('Options', 'formflow-lite'), 'default' => []],
                    'min_select' => ['type' => 'number', 'label' => __('Minimum Selections', 'formflow-lite'), 'default' => ''],
                    'max_select' => ['type' => 'number', 'label' => __('Maximum Selections', 'formflow-lite'), 'default' => ''],
                ],
            ],
            'toggle' => [
                'label' => __('Toggle Switch', 'formflow-lite'),
                'icon' => 'dashicons-controls-repeat',
                'category' => 'selection',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'default_value' => ['type' => 'checkbox', 'label' => __('Default On', 'formflow-lite'), 'default' => false],
                    'on_label' => ['type' => 'text', 'label' => __('On Label', 'formflow-lite'), 'default' => __('Yes', 'formflow-lite')],
                    'off_label' => ['type' => 'text', 'label' => __('Off Label', 'formflow-lite'), 'default' => __('No', 'formflow-lite')],
                ],
            ],

            // Advanced Fields
            'date' => [
                'label' => __('Date Picker', 'formflow-lite'),
                'icon' => 'dashicons-calendar-alt',
                'category' => 'advanced',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'min_date' => ['type' => 'text', 'label' => __('Min Date (YYYY-MM-DD or "today")', 'formflow-lite'), 'default' => ''],
                    'max_date' => ['type' => 'text', 'label' => __('Max Date', 'formflow-lite'), 'default' => ''],
                    'disable_weekends' => ['type' => 'checkbox', 'label' => __('Disable Weekends', 'formflow-lite'), 'default' => false],
                ],
            ],
            'time' => [
                'label' => __('Time Picker', 'formflow-lite'),
                'icon' => 'dashicons-clock',
                'category' => 'advanced',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'min_time' => ['type' => 'text', 'label' => __('Min Time (HH:MM)', 'formflow-lite'), 'default' => ''],
                    'max_time' => ['type' => 'text', 'label' => __('Max Time', 'formflow-lite'), 'default' => ''],
                    'interval' => ['type' => 'select', 'label' => __('Time Interval', 'formflow-lite'), 'default' => '30', 'options' => [
                        '15' => __('15 minutes', 'formflow-lite'),
                        '30' => __('30 minutes', 'formflow-lite'),
                        '60' => __('1 hour', 'formflow-lite'),
                    ]],
                ],
            ],
            'file' => [
                'label' => __('File Upload', 'formflow-lite'),
                'icon' => 'dashicons-upload',
                'category' => 'advanced',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => ''],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                    'allowed_types' => ['type' => 'text', 'label' => __('Allowed File Types', 'formflow-lite'), 'default' => 'jpg,jpeg,png,pdf'],
                    'max_size' => ['type' => 'number', 'label' => __('Max File Size (MB)', 'formflow-lite'), 'default' => 5],
                    'multiple' => ['type' => 'checkbox', 'label' => __('Allow Multiple Files', 'formflow-lite'), 'default' => false],
                ],
            ],
            'signature' => [
                'label' => __('Signature', 'formflow-lite'),
                'icon' => 'dashicons-admin-customizer',
                'category' => 'advanced',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Signature', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'width' => ['type' => 'number', 'label' => __('Width (px)', 'formflow-lite'), 'default' => 400],
                    'height' => ['type' => 'number', 'label' => __('Height (px)', 'formflow-lite'), 'default' => 150],
                ],
            ],

            // Address Fields
            'address' => [
                'label' => __('Address (Smart)', 'formflow-lite'),
                'icon' => 'dashicons-location',
                'category' => 'address',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Service Address', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'autocomplete' => ['type' => 'checkbox', 'label' => __('Enable Autocomplete', 'formflow-lite'), 'default' => true],
                    'validate_territory' => ['type' => 'checkbox', 'label' => __('Validate Service Territory', 'formflow-lite'), 'default' => true],
                    'include_unit' => ['type' => 'checkbox', 'label' => __('Include Unit/Apt Field', 'formflow-lite'), 'default' => true],
                ],
            ],
            'address_street' => [
                'label' => __('Street Address', 'formflow-lite'),
                'icon' => 'dashicons-location',
                'category' => 'address',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Street Address', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'autocomplete' => ['type' => 'checkbox', 'label' => __('Enable Autocomplete', 'formflow-lite'), 'default' => true],
                ],
            ],
            'address_city' => [
                'label' => __('City', 'formflow-lite'),
                'icon' => 'dashicons-location',
                'category' => 'address',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('City', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                ],
            ],
            'address_state' => [
                'label' => __('State', 'formflow-lite'),
                'icon' => 'dashicons-location',
                'category' => 'address',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('State', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'country' => ['type' => 'select', 'label' => __('Country', 'formflow-lite'), 'default' => 'US', 'options' => [
                        'US' => __('United States', 'formflow-lite'),
                        'CA' => __('Canada', 'formflow-lite'),
                    ]],
                ],
            ],
            'address_zip' => [
                'label' => __('ZIP Code', 'formflow-lite'),
                'icon' => 'dashicons-location',
                'category' => 'address',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('ZIP Code', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'validate_format' => ['type' => 'checkbox', 'label' => __('Validate Format', 'formflow-lite'), 'default' => true],
                ],
            ],

            // Utility-Specific Fields
            'account_number' => [
                'label' => __('Account Number', 'formflow-lite'),
                'icon' => 'dashicons-id',
                'category' => 'utility',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Account Number', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'help_text' => ['type' => 'textarea', 'label' => __('Help Text', 'formflow-lite'), 'default' => __('Find this on your utility bill', 'formflow-lite')],
                    'validate_api' => ['type' => 'checkbox', 'label' => __('Validate via API', 'formflow-lite'), 'default' => true],
                    'mask' => ['type' => 'text', 'label' => __('Input Mask', 'formflow-lite'), 'default' => ''],
                ],
            ],
            'meter_number' => [
                'label' => __('Meter Number', 'formflow-lite'),
                'icon' => 'dashicons-dashboard',
                'category' => 'utility',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Meter Number', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => false],
                ],
            ],
            'device_type' => [
                'label' => __('Device Type Selector', 'formflow-lite'),
                'icon' => 'dashicons-laptop',
                'category' => 'utility',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Select Your Device', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'device_options' => ['type' => 'select', 'label' => __('Device Category', 'formflow-lite'), 'default' => 'thermostat', 'options' => [
                        'thermostat' => __('Smart Thermostats', 'formflow-lite'),
                        'water_heater' => __('Water Heaters', 'formflow-lite'),
                        'ev_charger' => __('EV Chargers', 'formflow-lite'),
                        'pool_pump' => __('Pool Pumps', 'formflow-lite'),
                        'custom' => __('Custom List', 'formflow-lite'),
                    ]],
                ],
            ],
            'program_selector' => [
                'label' => __('Program Selector', 'formflow-lite'),
                'icon' => 'dashicons-clipboard',
                'category' => 'utility',
                'settings' => [
                    'label' => ['type' => 'text', 'label' => __('Label', 'formflow-lite'), 'default' => __('Select Programs', 'formflow-lite')],
                    'required' => ['type' => 'checkbox', 'label' => __('Required', 'formflow-lite'), 'default' => true],
                    'allow_multiple' => ['type' => 'checkbox', 'label' => __('Allow Multiple Selections', 'formflow-lite'), 'default' => true],
                    'show_descriptions' => ['type' => 'checkbox', 'label' => __('Show Program Descriptions', 'formflow-lite'), 'default' => true],
                    'show_incentives' => ['type' => 'checkbox', 'label' => __('Show Incentive Amounts', 'formflow-lite'), 'default' => true],
                ],
            ],

            // Layout Elements
            'heading' => [
                'label' => __('Heading', 'formflow-lite'),
                'icon' => 'dashicons-heading',
                'category' => 'layout',
                'settings' => [
                    'text' => ['type' => 'text', 'label' => __('Heading Text', 'formflow-lite'), 'default' => ''],
                    'level' => ['type' => 'select', 'label' => __('Heading Level', 'formflow-lite'), 'default' => 'h3', 'options' => [
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                    ]],
                    'alignment' => ['type' => 'select', 'label' => __('Alignment', 'formflow-lite'), 'default' => 'left', 'options' => [
                        'left' => __('Left', 'formflow-lite'),
                        'center' => __('Center', 'formflow-lite'),
                        'right' => __('Right', 'formflow-lite'),
                    ]],
                ],
            ],
            'paragraph' => [
                'label' => __('Paragraph', 'formflow-lite'),
                'icon' => 'dashicons-editor-paragraph',
                'category' => 'layout',
                'settings' => [
                    'content' => ['type' => 'wysiwyg', 'label' => __('Content', 'formflow-lite'), 'default' => ''],
                ],
            ],
            'divider' => [
                'label' => __('Divider', 'formflow-lite'),
                'icon' => 'dashicons-minus',
                'category' => 'layout',
                'settings' => [
                    'style' => ['type' => 'select', 'label' => __('Style', 'formflow-lite'), 'default' => 'solid', 'options' => [
                        'solid' => __('Solid', 'formflow-lite'),
                        'dashed' => __('Dashed', 'formflow-lite'),
                        'dotted' => __('Dotted', 'formflow-lite'),
                    ]],
                    'spacing' => ['type' => 'select', 'label' => __('Spacing', 'formflow-lite'), 'default' => 'medium', 'options' => [
                        'small' => __('Small', 'formflow-lite'),
                        'medium' => __('Medium', 'formflow-lite'),
                        'large' => __('Large', 'formflow-lite'),
                    ]],
                ],
            ],
            'spacer' => [
                'label' => __('Spacer', 'formflow-lite'),
                'icon' => 'dashicons-image-flip-vertical',
                'category' => 'layout',
                'settings' => [
                    'height' => ['type' => 'number', 'label' => __('Height (px)', 'formflow-lite'), 'default' => 20],
                ],
            ],
            'columns' => [
                'label' => __('Columns', 'formflow-lite'),
                'icon' => 'dashicons-columns',
                'category' => 'layout',
                'settings' => [
                    'column_count' => ['type' => 'select', 'label' => __('Columns', 'formflow-lite'), 'default' => '2', 'options' => [
                        '2' => __('2 Columns', 'formflow-lite'),
                        '3' => __('3 Columns', 'formflow-lite'),
                        '4' => __('4 Columns', 'formflow-lite'),
                    ]],
                    'gap' => ['type' => 'select', 'label' => __('Gap', 'formflow-lite'), 'default' => 'medium', 'options' => [
                        'small' => __('Small', 'formflow-lite'),
                        'medium' => __('Medium', 'formflow-lite'),
                        'large' => __('Large', 'formflow-lite'),
                    ]],
                ],
                'is_container' => true,
            ],
            'section' => [
                'label' => __('Section', 'formflow-lite'),
                'icon' => 'dashicons-editor-table',
                'category' => 'layout',
                'settings' => [
                    'title' => ['type' => 'text', 'label' => __('Section Title', 'formflow-lite'), 'default' => ''],
                    'collapsible' => ['type' => 'checkbox', 'label' => __('Collapsible', 'formflow-lite'), 'default' => false],
                    'collapsed_default' => ['type' => 'checkbox', 'label' => __('Collapsed by Default', 'formflow-lite'), 'default' => false],
                ],
                'is_container' => true,
            ],
        ];

        // Allow extensions to add custom field types
        $this->field_types = apply_filters('fffl_builder_field_types', $this->field_types);
    }

    /**
     * Get all field types
     */
    public function get_field_types(): array {
        return $this->field_types;
    }

    /**
     * Get field types by category
     */
    public function get_field_types_by_category(): array {
        $categories = [
            'basic' => ['label' => __('Basic Fields', 'formflow-lite'), 'fields' => []],
            'selection' => ['label' => __('Selection Fields', 'formflow-lite'), 'fields' => []],
            'advanced' => ['label' => __('Advanced Fields', 'formflow-lite'), 'fields' => []],
            'address' => ['label' => __('Address Fields', 'formflow-lite'), 'fields' => []],
            'utility' => ['label' => __('Utility Fields', 'formflow-lite'), 'fields' => []],
            'layout' => ['label' => __('Layout Elements', 'formflow-lite'), 'fields' => []],
        ];

        foreach ($this->field_types as $type => $config) {
            $category = $config['category'] ?? 'basic';
            if (isset($categories[$category])) {
                $categories[$category]['fields'][$type] = $config;
            }
        }

        return $categories;
    }

    /**
     * Register a custom field type
     */
    public function register_field_type(string $type, array $config): void {
        $this->field_types[$type] = $config;
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes(): void {
        register_rest_route('fffl/v1', '/builder/schema/(?P<instance_id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'rest_get_schema'],
                'permission_callback' => [$this, 'check_admin_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'rest_save_schema'],
                'permission_callback' => [$this, 'check_admin_permission'],
            ],
        ]);

        register_rest_route('fffl/v1', '/builder/field-types', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_field_types'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        register_rest_route('fffl/v1', '/builder/preview', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_preview_form'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);
    }

    /**
     * Check admin permission for REST API
     */
    public function check_admin_permission(): bool {
        return current_user_can('manage_options');
    }

    /**
     * REST: Get form schema
     */
    public function rest_get_schema(\WP_REST_Request $request): \WP_REST_Response {
        $instance_id = intval($request->get_param('instance_id'));

        global $wpdb;
        $table = $wpdb->prefix . 'fffl_instances';
        $instance = $wpdb->get_row($wpdb->prepare(
            "SELECT settings FROM {$table} WHERE id = %d",
            $instance_id
        ));

        if (!$instance) {
            return new \WP_REST_Response(['error' => 'Instance not found'], 404);
        }

        $settings = json_decode($instance->settings, true) ?: [];
        $schema = $settings['form_schema'] ?? $this->get_default_schema();

        return new \WP_REST_Response([
            'success' => true,
            'schema' => $schema,
        ]);
    }

    /**
     * REST: Save form schema
     */
    public function rest_save_schema(\WP_REST_Request $request): \WP_REST_Response {
        $instance_id = intval($request->get_param('instance_id'));
        $schema = $request->get_json_params();

        // Validate schema
        $validation = $this->validate_schema($schema);
        if (!$validation['valid']) {
            return new \WP_REST_Response([
                'success' => false,
                'errors' => $validation['errors'],
            ], 400);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fffl_instances';

        // Get current settings
        $instance = $wpdb->get_row($wpdb->prepare(
            "SELECT settings FROM {$table} WHERE id = %d",
            $instance_id
        ));

        if (!$instance) {
            return new \WP_REST_Response(['error' => 'Instance not found'], 404);
        }

        $settings = json_decode($instance->settings, true) ?: [];
        $settings['form_schema'] = $schema;

        // Save updated settings
        $wpdb->update(
            $table,
            ['settings' => wp_json_encode($settings)],
            ['id' => $instance_id]
        );

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Form schema saved successfully.', 'formflow-lite'),
        ]);
    }

    /**
     * REST: Get field types
     */
    public function rest_get_field_types(): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'field_types' => $this->get_field_types_by_category(),
        ]);
    }

    /**
     * REST: Preview form
     */
    public function rest_preview_form(\WP_REST_Request $request): \WP_REST_Response {
        $schema = $request->get_json_params();

        // Use service container if available, otherwise create directly
        try {
            $renderer = \FFFL\ServiceContainer::instance()->has('form_renderer')
                ? \FFFL\ServiceContainer::instance()->get('form_renderer')
                : new FormRenderer();
        } catch (\Throwable $e) {
            $renderer = new FormRenderer();
        }

        $html = $renderer->render_preview($schema);

        return new \WP_REST_Response([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Validate form schema
     *
     * Validates the schema structure and applies customizable validation rules.
     */
    public function validate_schema(array $schema): array {
        $errors = [];
        $warnings = [];

        // Size validation to prevent excessive schema sizes
        $schema_json = wp_json_encode($schema);
        $schema_size = strlen($schema_json);
        $max_size = apply_filters('fffl_schema_max_size', 1024 * 500); // 500KB default

        if ($schema_size > $max_size) {
            $errors[] = sprintf(
                __('Form schema exceeds maximum size (%s KB). Please reduce the number of fields.', 'formflow-lite'),
                round($max_size / 1024)
            );
        }

        // Step count validation
        $max_steps = apply_filters('fffl_schema_max_steps', 20);
        if (count($schema['steps'] ?? []) > $max_steps) {
            $errors[] = sprintf(
                __('Form exceeds maximum number of steps (%d).', 'formflow-lite'),
                $max_steps
            );
        }

        if (empty($schema['steps']) || !is_array($schema['steps'])) {
            $errors[] = __('Form must have at least one step.', 'formflow-lite');
        }

        $field_names = [];
        $nesting_depth = 0;
        $max_nesting = apply_filters('fffl_schema_max_nesting', 3);
        $max_fields_per_step = apply_filters('fffl_schema_max_fields_per_step', 50);

        foreach ($schema['steps'] ?? [] as $step_index => $step) {
            if (empty($step['fields']) || !is_array($step['fields'])) {
                continue;
            }

            // Field count per step
            if (count($step['fields']) > $max_fields_per_step) {
                $warnings[] = sprintf(
                    __('Step %d has many fields (%d). Consider splitting into multiple steps for better performance.', 'formflow-lite'),
                    $step_index + 1,
                    count($step['fields'])
                );
            }

            foreach ($step['fields'] as $field_index => $field) {
                if (empty($field['type'])) {
                    $errors[] = sprintf(
                        __('Field %d in step %d is missing a type.', 'formflow-lite'),
                        $field_index + 1,
                        $step_index + 1
                    );
                }

                $layout_fields = ['heading', 'paragraph', 'divider', 'spacer', 'columns', 'section'];
                if (empty($field['name']) && !in_array($field['type'] ?? '', $layout_fields)) {
                    $errors[] = sprintf(
                        __('Field %d in step %d is missing a name.', 'formflow-lite'),
                        $field_index + 1,
                        $step_index + 1
                    );
                }

                // Check for duplicate field names
                if (!empty($field['name'])) {
                    if (in_array($field['name'], $field_names)) {
                        $errors[] = sprintf(
                            __('Duplicate field name "%s" found. Field names must be unique.', 'formflow-lite'),
                            $field['name']
                        );
                    }
                    $field_names[] = $field['name'];
                }

                // Validate field name format
                if (!empty($field['name']) && !preg_match('/^[a-zA-Z][a-zA-Z0-9_\-]*$/', $field['name'])) {
                    $errors[] = sprintf(
                        __('Invalid field name "%s". Names must start with a letter and contain only letters, numbers, underscores, and hyphens.', 'formflow-lite'),
                        $field['name']
                    );
                }

                // Check for container nesting depth
                if (in_array($field['type'] ?? '', ['columns', 'section'])) {
                    $nesting_depth++;
                    if ($nesting_depth > $max_nesting) {
                        $errors[] = sprintf(
                            __('Container nesting depth exceeds maximum (%d levels).', 'formflow-lite'),
                            $max_nesting
                        );
                    }
                }
            }
        }

        /**
         * Filter schema validation result
         *
         * Allows custom validation rules to be added.
         *
         * @param array $result   Validation result with 'valid', 'errors', 'warnings' keys
         * @param array $schema   The form schema being validated
         */
        $result = apply_filters('fffl_schema_validation', [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ], $schema);

        return $result;
    }

    /**
     * Get default schema for new forms
     */
    public function get_default_schema(): array {
        return [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => __('Step 1', 'formflow-lite'),
                    'description' => '',
                    'fields' => [],
                ],
            ],
            'settings' => [
                'submit_button_text' => __('Submit', 'formflow-lite'),
                'success_message' => __('Thank you for your submission!', 'formflow-lite'),
            ],
        ];
    }

    /**
     * Enqueue builder assets
     */
    public function enqueue_builder_assets(string $hook): void {
        // Only load on form builder page
        if (strpos($hook, 'fffl-') === false) {
            return;
        }

        // Check if we're on the builder page
        if (!isset($_GET['action']) || $_GET['action'] !== 'builder') {
            return;
        }

        // React app for builder
        wp_enqueue_script(
            'fffl-form-builder',
            FFFL_PLUGIN_URL . 'admin/assets/js/form-builder.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            FFFL_VERSION,
            true
        );

        wp_enqueue_style(
            'fffl-form-builder',
            FFFL_PLUGIN_URL . 'admin/assets/css/form-builder.css',
            ['wp-components'],
            FFFL_VERSION
        );

        wp_localize_script('fffl-form-builder', 'FFBuilder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('fffl/v1/builder/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'field_types' => $this->get_field_types_by_category(),
            'instance_id' => intval($_GET['instance_id'] ?? 0),
            'strings' => [
                'save' => __('Save', 'formflow-lite'),
                'preview' => __('Preview', 'formflow-lite'),
                'undo' => __('Undo', 'formflow-lite'),
                'redo' => __('Redo', 'formflow-lite'),
                'add_step' => __('Add Step', 'formflow-lite'),
                'delete_step' => __('Delete Step', 'formflow-lite'),
                'step_settings' => __('Step Settings', 'formflow-lite'),
                'field_settings' => __('Field Settings', 'formflow-lite'),
                'conditional_logic' => __('Conditional Logic', 'formflow-lite'),
                'drag_field' => __('Drag a field here', 'formflow-lite'),
                'confirm_delete' => __('Are you sure you want to delete this?', 'formflow-lite'),
                'saved' => __('Changes saved!', 'formflow-lite'),
                'error_saving' => __('Error saving changes.', 'formflow-lite'),
            ],
        ]);
    }

    /**
     * AJAX: Save form schema
     */
    public function ajax_save_form_schema(): void {
        check_ajax_referer('fffl_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'formflow-lite')]);
        }

        $instance_id = intval($_POST['instance_id'] ?? 0);
        $schema = json_decode(stripslashes($_POST['schema'] ?? ''), true);

        if (!$instance_id || !$schema) {
            wp_send_json_error(['message' => __('Invalid data.', 'formflow-lite')]);
        }

        // Save schema...
        global $wpdb;
        $table = $wpdb->prefix . 'fffl_instances';

        $instance = $wpdb->get_row($wpdb->prepare(
            "SELECT settings FROM {$table} WHERE id = %d",
            $instance_id
        ));

        if (!$instance) {
            wp_send_json_error(['message' => __('Instance not found.', 'formflow-lite')]);
        }

        $settings = json_decode($instance->settings, true) ?: [];
        $settings['form_schema'] = $schema;

        $wpdb->update(
            $table,
            ['settings' => wp_json_encode($settings)],
            ['id' => $instance_id]
        );

        wp_send_json_success(['message' => __('Form saved successfully.', 'formflow-lite')]);
    }

    /**
     * AJAX: Load form schema
     */
    public function ajax_load_form_schema(): void {
        check_ajax_referer('fffl_builder_nonce', 'nonce');

        $instance_id = intval($_POST['instance_id'] ?? 0);

        if (!$instance_id) {
            wp_send_json_error(['message' => __('Invalid instance ID.', 'formflow-lite')]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fffl_instances';

        $instance = $wpdb->get_row($wpdb->prepare(
            "SELECT settings FROM {$table} WHERE id = %d",
            $instance_id
        ));

        if (!$instance) {
            wp_send_json_error(['message' => __('Instance not found.', 'formflow-lite')]);
        }

        $settings = json_decode($instance->settings, true) ?: [];
        $schema = $settings['form_schema'] ?? $this->get_default_schema();

        wp_send_json_success(['schema' => $schema]);
    }

    /**
     * AJAX: Preview form
     */
    public function ajax_preview_form(): void {
        check_ajax_referer('fffl_builder_nonce', 'nonce');

        $schema = json_decode(stripslashes($_POST['schema'] ?? ''), true);

        if (!$schema) {
            wp_send_json_error(['message' => __('Invalid schema.', 'formflow-lite')]);
        }

        // Use service container if available
        try {
            $renderer = \FFFL\ServiceContainer::instance()->has('form_renderer')
                ? \FFFL\ServiceContainer::instance()->get('form_renderer')
                : new FormRenderer();
        } catch (\Throwable $e) {
            $renderer = new FormRenderer();
        }

        $html = $renderer->render_preview($schema);

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Log an error message
     *
     * @param string $message Error message
     * @param array  $context Additional context
     */
    private function log_error(string $message, array $context = []): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[FormFlow FormBuilder] %s | Context: %s',
                $message,
                wp_json_encode($context)
            ));
        }

        /**
         * Fires when a form builder error occurs
         *
         * @param string $message Error message
         * @param array  $context Additional context
         */
        do_action('fffl_form_builder_error', $message, $context);
    }
}

