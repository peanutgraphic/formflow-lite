<?php
/**
 * Base Test Case
 *
 * Provides common setup and utilities for all FormFlow Lite tests.
 *
 * @package FormFlow_Lite\Tests
 */

namespace FFFL\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case class
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Reset all mock data before each test
        reset_mock_data();

        // Set default options
        $this->setDefaultOptions();
    }

    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        // Clean up after each test
        reset_mock_data();

        parent::tearDown();
    }

    /**
     * Set default WordPress options for tests
     */
    protected function setDefaultOptions(): void
    {
        set_mock_option('fffl_settings', [
            'rate_limit_requests' => 120,
            'rate_limit_window' => 60,
            'disable_rate_limit' => false,
        ]);
    }

    /**
     * Set a mock option value
     *
     * @param string $option Option name
     * @param mixed  $value  Option value
     */
    protected function setOption(string $option, $value): void
    {
        set_mock_option($option, $value);
    }

    /**
     * Set mock wpdb result
     *
     * @param string $method  The wpdb method (get_row, get_results, get_var)
     * @param mixed  $result  The result to return
     */
    protected function setDbResult(string $method, $result): void
    {
        set_mock_wpdb_result($method, $result);
    }

    /**
     * Set mock insert ID
     *
     * @param int $id The insert ID to use
     */
    protected function setInsertId(int $id): void
    {
        global $mock_wpdb_insert_id;
        $mock_wpdb_insert_id = $id;
    }

    /**
     * Get all sent mock emails
     *
     * @return array
     */
    protected function getSentEmails(): array
    {
        return get_mock_emails_sent();
    }

    /**
     * Get the last mock JSON response
     *
     * @return array|null
     */
    protected function getJsonResponse(): ?array
    {
        return get_mock_json_response();
    }

    /**
     * Assert JSON response was successful
     *
     * @param string $message Optional message
     */
    protected function assertJsonSuccess(string $message = ''): void
    {
        $response = $this->getJsonResponse();
        $this->assertNotNull($response, $message ?: 'Expected JSON response to be set');
        $this->assertTrue($response['success'] ?? false, $message ?: 'Expected JSON response to indicate success');
    }

    /**
     * Assert JSON response was an error
     *
     * @param string $message Optional message
     */
    protected function assertJsonError(string $message = ''): void
    {
        $response = $this->getJsonResponse();
        $this->assertNotNull($response, $message ?: 'Expected JSON response to be set');
        $this->assertFalse($response['success'] ?? true, $message ?: 'Expected JSON response to indicate error');
    }

    /**
     * Set current user capability
     *
     * @param string $capability The capability
     * @param bool   $has        Whether user has capability
     */
    protected function setUserCapability(string $capability, bool $has): void
    {
        global $mock_current_user_capabilities;
        if (!isset($mock_current_user_capabilities)) {
            $mock_current_user_capabilities = [];
        }
        $mock_current_user_capabilities[$capability] = $has;
    }

    /**
     * Set whether SSL is active
     *
     * @param bool $ssl SSL status
     */
    protected function setSsl(bool $ssl): void
    {
        global $mock_is_ssl;
        $mock_is_ssl = $ssl;
    }

    /**
     * Create a mock form instance
     *
     * @param array $overrides Values to override defaults
     * @return array
     */
    protected function createMockInstance(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'name' => 'Test Form',
            'slug' => 'test-form',
            'utility' => 'test-utility',
            'form_type' => 'enrollment',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => 'test_password',
            'support_email_from' => 'support@example.com',
            'support_email_to' => 'admin@example.com',
            'settings' => [
                'demo_mode' => false,
                'content' => [
                    'program_name' => 'Test Program',
                ],
            ],
            'is_active' => true,
            'test_mode' => false,
            'created_at' => '2024-01-01 00:00:00',
        ], $overrides);
    }

    /**
     * Create mock form data
     *
     * @param array $overrides Values to override defaults
     * @return array
     */
    protected function createMockFormData(array $overrides = []): array
    {
        return array_merge([
            'has_ac' => true,
            'device_type' => 'thermostat',
            'utility_no' => '1234567890',
            'zip' => '20001',
            'zip_confirm' => '20001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'email_confirm' => 'john.doe@example.com',
            'phone' => '2025551234',
            'phone_type' => 'mobile',
            'street' => '123 Main St',
            'city' => 'Washington',
            'state' => 'DC',
            'schedule_date' => date('Y-m-d', strtotime('+7 days')),
            'schedule_time' => 'AM',
            'agree_terms' => true,
            'agree_adult' => true,
        ], $overrides);
    }

    /**
     * Create mock submission
     *
     * @param array $overrides Values to override defaults
     * @return array
     */
    protected function createMockSubmission(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'instance_id' => 1,
            'session_id' => 'test_session_123',
            'account_number' => '1234567890',
            'customer_name' => 'John Doe',
            'device_type' => 'thermostat',
            'form_data' => $this->createMockFormData(),
            'api_response' => null,
            'status' => 'in_progress',
            'step' => 1,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'PHPUnit Test',
            'created_at' => '2024-01-01 00:00:00',
            'completed_at' => null,
        ], $overrides);
    }

    /**
     * Assert that a string contains HTML elements
     *
     * @param string $needle   The string to search for
     * @param string $haystack The HTML to search in
     * @param string $message  Optional message
     */
    protected function assertHtmlContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString(
            $needle,
            $haystack,
            $message ?: "Expected HTML to contain: {$needle}"
        );
    }

    /**
     * Assert that a string does not contain HTML elements
     *
     * @param string $needle   The string to search for
     * @param string $haystack The HTML to search in
     * @param string $message  Optional message
     */
    protected function assertHtmlNotContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringNotContainsString(
            $needle,
            $haystack,
            $message ?: "Expected HTML to NOT contain: {$needle}"
        );
    }

    /**
     * Create a mock $_SERVER array
     *
     * @param array $overrides Values to override defaults
     * @return array
     */
    protected function createMockServer(array $overrides = []): array
    {
        return array_merge([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/wp-admin/admin-ajax.php',
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'PHPUnit Test Agent',
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_REFERER' => 'https://example.com/form/',
            'HTTPS' => 'on',
        ], $overrides);
    }

    /**
     * Set $_SERVER values for testing
     *
     * @param array $values Server values to set
     */
    protected function setServerValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Set $_POST values for testing
     *
     * @param array $values POST values to set
     */
    protected function setPostValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $_POST[$key] = $value;
        }
    }

    /**
     * Set $_GET values for testing
     *
     * @param array $values GET values to set
     */
    protected function setGetValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $_GET[$key] = $value;
        }
    }

    /**
     * Clear $_POST values
     */
    protected function clearPost(): void
    {
        $_POST = [];
    }

    /**
     * Clear $_GET values
     */
    protected function clearGet(): void
    {
        $_GET = [];
    }

    /**
     * Clear $_SERVER values (restore to defaults)
     */
    protected function clearServer(): void
    {
        $_SERVER = $this->createMockServer();
    }
}
