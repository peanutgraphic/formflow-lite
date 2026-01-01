<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment with WordPress function mocks
 * for unit testing FormFlow Lite without a full WordPress installation.
 *
 * @package FormFlow_Lite\Tests
 */

// Prevent direct access
if (php_sapi_name() !== 'cli') {
    exit;
}

// Define constants that WordPress would normally define
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
}

if (!defined('FFFL_PLUGIN_DIR')) {
    define('FFFL_PLUGIN_DIR', dirname(__DIR__) . '/');
}

if (!defined('FFFL_PLUGIN_URL')) {
    define('FFFL_PLUGIN_URL', 'https://example.com/wp-content/plugins/formflow-lite/');
}

if (!defined('FFFL_VERSION')) {
    define('FFFL_VERSION', '2.6.0');
}

if (!defined('FFFL_TABLE_INSTANCES')) {
    define('FFFL_TABLE_INSTANCES', 'fffl_instances');
}

if (!defined('FFFL_TABLE_SUBMISSIONS')) {
    define('FFFL_TABLE_SUBMISSIONS', 'fffl_submissions');
}

if (!defined('FFFL_TABLE_LOGS')) {
    define('FFFL_TABLE_LOGS', 'fffl_logs');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

// ============================================================================
// WordPress Core Function Mocks
// ============================================================================

/**
 * Mock sanitize_text_field
 */
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        if (is_object($str) || is_array($str)) {
            return '';
        }
        $str = (string) $str;
        $filtered = wp_check_invalid_utf8($str);
        $filtered = trim(preg_replace('/[\r\n\t ]+/', ' ', $filtered));
        return htmlspecialchars($filtered, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mock wp_check_invalid_utf8
 */
if (!function_exists('wp_check_invalid_utf8')) {
    function wp_check_invalid_utf8($string, $strip = false) {
        $string = (string) $string;
        if (0 === strlen($string)) {
            return '';
        }
        if (preg_match('/^./us', $string)) {
            return $string;
        }
        return '';
    }
}

/**
 * Mock sanitize_email
 */
if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }
}

/**
 * Mock sanitize_title
 */
if (!function_exists('sanitize_title')) {
    function sanitize_title($title, $fallback_title = '', $context = 'save') {
        $title = strip_tags($title);
        $title = preg_replace('/[^a-z0-9-_]/i', '-', $title);
        $title = preg_replace('/-+/', '-', $title);
        $title = strtolower(trim($title, '-'));
        return $title ?: $fallback_title;
    }
}

/**
 * Mock sanitize_html_class
 */
if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($classname, $fallback = '') {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $classname);
        return $sanitized ?: $fallback;
    }
}

/**
 * Mock esc_html
 */
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mock esc_attr
 */
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mock esc_url
 */
if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $_context = 'display') {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

/**
 * Mock esc_url_raw
 */
if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url, $protocols = null) {
        return esc_url($url, $protocols, 'db');
    }
}

/**
 * Mock esc_textarea
 */
if (!function_exists('esc_textarea')) {
    function esc_textarea($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Mock wp_kses_post
 */
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {
        return strip_tags($data, '<a><br><p><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><div><span>');
    }
}

/**
 * Mock __() translation function
 */
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

/**
 * Mock _e() translation echo function
 */
if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

/**
 * Mock esc_html__
 */
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return esc_html(__($text, $domain));
    }
}

/**
 * Mock esc_html_e
 */
if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo esc_html__($text, $domain);
    }
}

/**
 * Mock esc_attr__
 */
if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return esc_attr(__($text, $domain));
    }
}

/**
 * Mock esc_attr_e
 */
if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo esc_attr__($text, $domain);
    }
}

/**
 * Mock wp_salt
 */
if (!function_exists('wp_salt')) {
    function wp_salt($scheme = 'auth') {
        return 'test_salt_key_for_unit_testing_purposes_only_' . $scheme;
    }
}

/**
 * Mock wp_create_nonce
 */
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return md5('nonce_' . $action . '_' . time());
    }
}

/**
 * Mock wp_verify_nonce
 */
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true; // Always return true in tests
    }
}

/**
 * Mock wp_nonce_field
 */
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        $nonce = wp_create_nonce($action);
        $html = '<input type="hidden" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($nonce) . '" />';
        if ($echo) {
            echo $html;
        }
        return $html;
    }
}

/**
 * Mock check_ajax_referer
 */
if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_arg = false, $die = true) {
        return true;
    }
}

/**
 * Mock wp_generate_password
 */
if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length = 12, $special_chars = true, $extra_special_chars = false) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}

/**
 * Mock current_user_can
 */
if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        global $mock_current_user_capabilities;
        if (!isset($mock_current_user_capabilities)) {
            $mock_current_user_capabilities = ['manage_options' => true];
        }
        return $mock_current_user_capabilities[$capability] ?? false;
    }
}

/**
 * Mock get_option
 */
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        global $mock_options;
        if (!isset($mock_options)) {
            $mock_options = [];
        }
        return $mock_options[$option] ?? $default;
    }
}

/**
 * Mock update_option
 */
if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        global $mock_options;
        if (!isset($mock_options)) {
            $mock_options = [];
        }
        $mock_options[$option] = $value;
        return true;
    }
}

/**
 * Mock delete_option
 */
if (!function_exists('delete_option')) {
    function delete_option($option) {
        global $mock_options;
        if (!isset($mock_options)) {
            $mock_options = [];
        }
        unset($mock_options[$option]);
        return true;
    }
}

/**
 * Mock get_transient
 */
if (!function_exists('get_transient')) {
    function get_transient($transient) {
        global $mock_transients;
        if (!isset($mock_transients)) {
            $mock_transients = [];
        }
        if (!isset($mock_transients[$transient])) {
            return false;
        }
        $data = $mock_transients[$transient];
        if ($data['expiration'] < time()) {
            unset($mock_transients[$transient]);
            return false;
        }
        return $data['value'];
    }
}

/**
 * Mock set_transient
 */
if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        global $mock_transients;
        if (!isset($mock_transients)) {
            $mock_transients = [];
        }
        $mock_transients[$transient] = [
            'value' => $value,
            'expiration' => $expiration > 0 ? time() + $expiration : PHP_INT_MAX
        ];
        return true;
    }
}

/**
 * Mock delete_transient
 */
if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        global $mock_transients;
        if (!isset($mock_transients)) {
            $mock_transients = [];
        }
        unset($mock_transients[$transient]);
        return true;
    }
}

/**
 * Mock current_time
 */
if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        switch ($type) {
            case 'mysql':
                return gmdate('Y-m-d H:i:s');
            case 'timestamp':
                return time();
            default:
                return gmdate($type);
        }
    }
}

/**
 * Mock is_ssl
 */
if (!function_exists('is_ssl')) {
    function is_ssl() {
        global $mock_is_ssl;
        return $mock_is_ssl ?? true;
    }
}

/**
 * Mock is_email
 */
if (!function_exists('is_email')) {
    function is_email($email, $deprecated = false) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * Mock wp_mail
 */
if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = []) {
        global $mock_emails_sent;
        if (!isset($mock_emails_sent)) {
            $mock_emails_sent = [];
        }
        $mock_emails_sent[] = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
            'attachments' => $attachments
        ];
        return true;
    }
}

/**
 * Mock wp_json_encode
 */
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

/**
 * Mock wp_send_json_success
 */
if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null, $status_code = null) {
        global $mock_json_response;
        $mock_json_response = [
            'success' => true,
            'data' => $data
        ];
        // Don't exit in tests
    }
}

/**
 * Mock wp_send_json_error
 */
if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null) {
        global $mock_json_response;
        $mock_json_response = [
            'success' => false,
            'data' => $data
        ];
        // Don't exit in tests
    }
}

/**
 * Mock add_action
 */
if (!function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        global $mock_actions;
        if (!isset($mock_actions)) {
            $mock_actions = [];
        }
        if (!isset($mock_actions[$hook_name])) {
            $mock_actions[$hook_name] = [];
        }
        $mock_actions[$hook_name][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        ];
        return true;
    }
}

/**
 * Mock do_action
 */
if (!function_exists('do_action')) {
    function do_action($hook_name, ...$args) {
        global $mock_actions;
        if (!isset($mock_actions[$hook_name])) {
            return;
        }
        foreach ($mock_actions[$hook_name] as $action) {
            call_user_func_array($action['callback'], array_slice($args, 0, $action['accepted_args']));
        }
    }
}

/**
 * Mock add_filter
 */
if (!function_exists('add_filter')) {
    function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        global $mock_filters;
        if (!isset($mock_filters)) {
            $mock_filters = [];
        }
        if (!isset($mock_filters[$hook_name])) {
            $mock_filters[$hook_name] = [];
        }
        $mock_filters[$hook_name][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];
        return true;
    }
}

/**
 * Mock apply_filters
 */
if (!function_exists('apply_filters')) {
    function apply_filters($hook_name, $value, ...$args) {
        global $mock_filters;
        if (!isset($mock_filters[$hook_name])) {
            return $value;
        }
        ksort($mock_filters[$hook_name]);
        foreach ($mock_filters[$hook_name] as $priority => $filters) {
            foreach ($filters as $filter) {
                $all_args = array_merge([$value], $args);
                $value = call_user_func_array($filter['callback'], array_slice($all_args, 0, $filter['accepted_args']));
            }
        }
        return $value;
    }
}

/**
 * Mock selected()
 */
if (!function_exists('selected')) {
    function selected($selected, $current = true, $echo = true) {
        $result = ($selected == $current) ? ' selected="selected"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

/**
 * Mock checked()
 */
if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        $result = ($checked == $current) ? ' checked="checked"' : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

/**
 * Mock admin_url
 */
if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'https://example.com/wp-admin/' . ltrim($path, '/');
    }
}

/**
 * Mock home_url
 */
if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null) {
        return 'https://example.com/' . ltrim($path, '/');
    }
}

/**
 * Mock rest_url
 */
if (!function_exists('rest_url')) {
    function rest_url($path = '', $scheme = 'rest') {
        return 'https://example.com/wp-json/' . ltrim($path, '/');
    }
}

/**
 * Mock get_permalink
 */
if (!function_exists('get_permalink')) {
    function get_permalink($post = 0, $leavename = false) {
        return 'https://example.com/page/' . $post . '/';
    }
}

/**
 * Mock add_query_arg
 */
if (!function_exists('add_query_arg')) {
    function add_query_arg($args, $url = '') {
        if (is_array($args)) {
            return $url . '?' . http_build_query($args);
        }
        return $url . '?' . $args;
    }
}

/**
 * Mock wp_enqueue_script
 */
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        global $mock_enqueued_scripts;
        if (!isset($mock_enqueued_scripts)) {
            $mock_enqueued_scripts = [];
        }
        $mock_enqueued_scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer
        ];
        return true;
    }
}

/**
 * Mock wp_enqueue_style
 */
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        global $mock_enqueued_styles;
        if (!isset($mock_enqueued_styles)) {
            $mock_enqueued_styles = [];
        }
        $mock_enqueued_styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media
        ];
        return true;
    }
}

/**
 * Mock wp_register_script
 */
if (!function_exists('wp_register_script')) {
    function wp_register_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        return true;
    }
}

/**
 * Mock wp_register_style
 */
if (!function_exists('wp_register_style')) {
    function wp_register_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        return true;
    }
}

/**
 * Mock wp_localize_script
 */
if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        global $mock_localized_scripts;
        if (!isset($mock_localized_scripts)) {
            $mock_localized_scripts = [];
        }
        $mock_localized_scripts[$handle] = [$object_name => $l10n];
        return true;
    }
}

/**
 * Mock shortcode_atts
 */
if (!function_exists('shortcode_atts')) {
    function shortcode_atts($pairs, $atts, $shortcode = '') {
        $atts = (array) $atts;
        $out = [];
        foreach ($pairs as $name => $default) {
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            } else {
                $out[$name] = $default;
            }
        }
        return $out;
    }
}

/**
 * Mock register_rest_route
 */
if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = [], $override = false) {
        global $mock_rest_routes;
        if (!isset($mock_rest_routes)) {
            $mock_rest_routes = [];
        }
        $mock_rest_routes[$namespace][$route] = $args;
        return true;
    }
}

/**
 * Mock WP_REST_Request class
 */
if (!class_exists('WP_REST_Request')) {
    class WP_REST_Request {
        private $params = [];
        private $json_params = [];

        public function set_param($key, $value) {
            $this->params[$key] = $value;
        }

        public function get_param($key) {
            return $this->params[$key] ?? null;
        }

        public function get_params() {
            return $this->params;
        }

        public function get_json_params() {
            return $this->json_params;
        }

        public function set_json_params($params) {
            $this->json_params = $params;
        }
    }
}

/**
 * Mock WP_REST_Response class
 */
if (!class_exists('WP_REST_Response')) {
    class WP_REST_Response {
        private $data;
        private $status;

        public function __construct($data = null, $status = 200) {
            $this->data = $data;
            $this->status = $status;
        }

        public function get_data() {
            return $this->data;
        }

        public function get_status() {
            return $this->status;
        }
    }
}

/**
 * Mock wpdb class
 */
if (!class_exists('wpdb')) {
    class wpdb {
        public $prefix = 'wp_';
        public $insert_id = 0;
        public $last_error = '';
        public $posts = 'wp_posts';

        private $mock_data = [];
        private $mock_results = [];

        public function prepare($query, ...$args) {
            $query = str_replace('%s', "'%s'", $query);
            $query = str_replace('%d', '%d', $query);
            return vsprintf($query, $args);
        }

        public function get_row($query, $output = OBJECT) {
            // Return mock data if set
            global $mock_wpdb_results;
            if (isset($mock_wpdb_results['get_row'])) {
                return array_shift($mock_wpdb_results['get_row']);
            }
            return null;
        }

        public function get_results($query, $output = OBJECT) {
            global $mock_wpdb_results;
            if (isset($mock_wpdb_results['get_results'])) {
                return array_shift($mock_wpdb_results['get_results']);
            }
            return [];
        }

        public function get_var($query, $x = 0, $y = 0) {
            global $mock_wpdb_results;
            if (isset($mock_wpdb_results['get_var'])) {
                return array_shift($mock_wpdb_results['get_var']);
            }
            return null;
        }

        public function insert($table, $data, $format = null) {
            global $mock_wpdb_insert_id;
            $this->insert_id = $mock_wpdb_insert_id ?? rand(1, 1000);
            return 1;
        }

        public function update($table, $data, $where, $format = null, $where_format = null) {
            return 1;
        }

        public function delete($table, $where, $where_format = null) {
            return 1;
        }

        public function query($query) {
            return true;
        }

        public function esc_like($text) {
            return addcslashes($text, '_%\\');
        }
    }
}

// Create global $wpdb instance
global $wpdb;
$wpdb = new wpdb();

// ============================================================================
// Helper Functions for Tests
// ============================================================================

/**
 * Reset all mock data
 */
function reset_mock_data() {
    global $mock_options, $mock_transients, $mock_actions, $mock_filters,
           $mock_emails_sent, $mock_json_response, $mock_wpdb_results,
           $mock_wpdb_insert_id, $mock_enqueued_scripts, $mock_enqueued_styles,
           $mock_localized_scripts, $mock_rest_routes, $mock_current_user_capabilities,
           $mock_is_ssl;

    $mock_options = [];
    $mock_transients = [];
    $mock_actions = [];
    $mock_filters = [];
    $mock_emails_sent = [];
    $mock_json_response = null;
    $mock_wpdb_results = [];
    $mock_wpdb_insert_id = null;
    $mock_enqueued_scripts = [];
    $mock_enqueued_styles = [];
    $mock_localized_scripts = [];
    $mock_rest_routes = [];
    $mock_current_user_capabilities = ['manage_options' => true];
    $mock_is_ssl = true;
}

/**
 * Set mock option value
 */
function set_mock_option($option, $value) {
    global $mock_options;
    if (!isset($mock_options)) {
        $mock_options = [];
    }
    $mock_options[$option] = $value;
}

/**
 * Set mock wpdb result
 */
function set_mock_wpdb_result($method, $result) {
    global $mock_wpdb_results;
    if (!isset($mock_wpdb_results)) {
        $mock_wpdb_results = [];
    }
    if (!isset($mock_wpdb_results[$method])) {
        $mock_wpdb_results[$method] = [];
    }
    $mock_wpdb_results[$method][] = $result;
}

/**
 * Get sent emails
 */
function get_mock_emails_sent() {
    global $mock_emails_sent;
    return $mock_emails_sent ?? [];
}

/**
 * Get JSON response
 */
function get_mock_json_response() {
    global $mock_json_response;
    return $mock_json_response;
}

// Initialize mock data
reset_mock_data();

// Load Composer autoloader if available
$composer_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}
