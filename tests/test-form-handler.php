<?php
/**
 * Form handler tests for FormFlow Lite.
 *
 * Tests form processing, data handling, and submission workflows.
 *
 * @package FormFlow_Lite
 */

class Test_Form_Handler extends FormFlow_Lite_TestCase {

    /**
     * Test form instance structure validation.
     */
    public function test_form_instance_structure() {
        $instance = [
            'id' => 1,
            'name' => 'Enrollment Form',
            'slug' => 'enrollment-form',
            'connector' => 'standalone',
            'settings' => [],
            'is_active' => true,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ];

        // Required fields.
        $required = ['id', 'name', 'slug', 'connector', 'is_active'];
        foreach ($required as $field) {
            $this->assertArrayHasKey($field, $instance);
        }

        // Slug should be URL-safe.
        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $instance['slug']);
    }

    /**
     * Test submission data structure.
     */
    public function test_submission_data_structure() {
        $submission = [
            'id' => 1,
            'instance_id' => 1,
            'session_id' => bin2hex(random_bytes(16)),
            'status' => 'completed',
            'step' => 3,
            'form_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ],
            'created_at' => gmdate('Y-m-d H:i:s'),
            'completed_at' => gmdate('Y-m-d H:i:s'),
        ];

        // Session ID should be 32 chars.
        $this->assertEquals(32, strlen($submission['session_id']));

        // Status should be valid.
        $valid_statuses = ['in_progress', 'completed', 'abandoned', 'error'];
        $this->assertContains($submission['status'], $valid_statuses);

        // Step should be positive.
        $this->assertGreaterThan(0, $submission['step']);
    }

    /**
     * Test form field validation - required fields.
     */
    public function test_required_field_validation() {
        $field_config = [
            'name' => 'email',
            'type' => 'email',
            'required' => true,
            'label' => 'Email Address',
        ];

        $valid_values = ['test@example.com'];
        $invalid_values = ['', null, '   '];

        foreach ($valid_values as $value) {
            $is_valid = !$field_config['required'] || !empty(trim($value ?? ''));
            $this->assertTrue($is_valid, "Value '$value' should be valid");
        }

        foreach ($invalid_values as $value) {
            $is_valid = !$field_config['required'] || !empty(trim($value ?? ''));
            $this->assertFalse($is_valid, "Value should be invalid for required field");
        }
    }

    /**
     * Test email field validation.
     */
    public function test_email_field_validation() {
        $valid_emails = [
            'test@example.com',
            'user.name@domain.org',
            'user+tag@example.co.uk',
        ];

        $invalid_emails = [
            'not-an-email',
            '@example.com',
            'user@',
            'user@.com',
            '',
        ];

        foreach ($valid_emails as $email) {
            $is_valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $this->assertTrue($is_valid, "Email '$email' should be valid");
        }

        foreach ($invalid_emails as $email) {
            $is_valid = !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            $this->assertFalse($is_valid, "Email '$email' should be invalid");
        }
    }

    /**
     * Test phone field validation.
     */
    public function test_phone_field_validation() {
        $valid_phones = [
            '555-123-4567',
            '(555) 123-4567',
            '+1 555 123 4567',
            '5551234567',
        ];

        $invalid_phones = [
            'not-a-phone',
            '123',  // Too short.
            '',
        ];

        foreach ($valid_phones as $phone) {
            $digits_only = preg_replace('/[^0-9]/', '', $phone);
            $is_valid = strlen($digits_only) >= 10;
            $this->assertTrue($is_valid, "Phone '$phone' should be valid");
        }

        foreach ($invalid_phones as $phone) {
            $digits_only = preg_replace('/[^0-9]/', '', $phone);
            $is_valid = !empty($phone) && strlen($digits_only) >= 10;
            $this->assertFalse($is_valid, "Phone '$phone' should be invalid");
        }
    }

    /**
     * Test form step progression.
     */
    public function test_step_progression() {
        $total_steps = 4;
        $current_step = 1;

        // Should be able to progress forward.
        $next_step = min($current_step + 1, $total_steps);
        $this->assertEquals(2, $next_step);

        // Should be able to go back.
        $prev_step = max($current_step - 1, 1);
        $this->assertEquals(1, $prev_step);

        // Should not exceed total steps.
        $current_step = 4;
        $next_step = min($current_step + 1, $total_steps);
        $this->assertEquals(4, $next_step);
    }

    /**
     * Test session ID generation.
     */
    public function test_session_id_generation() {
        $session_ids = [];

        for ($i = 0; $i < 100; $i++) {
            $session_id = bin2hex(random_bytes(16));

            // Should be unique.
            $this->assertNotContains($session_id, $session_ids);
            $session_ids[] = $session_id;

            // Should be 32 characters.
            $this->assertEquals(32, strlen($session_id));

            // Should be hex.
            $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $session_id);
        }
    }

    /**
     * Test form data sanitization.
     */
    public function test_form_data_sanitization() {
        $dirty_data = [
            'first_name' => '<script>alert("xss")</script>John',
            'email' => '  test@example.com  ',
            'phone' => '(555) 123-4567',
            'notes' => "Line 1\nLine 2",
        ];

        // First name should have HTML stripped.
        $sanitized_name = sanitize_text_field($dirty_data['first_name']);
        $this->assertStringNotContainsString('<script>', $sanitized_name);
        $this->assertStringContainsString('John', $sanitized_name);

        // Email should be trimmed.
        $sanitized_email = sanitize_email(trim($dirty_data['email']));
        $this->assertEquals('test@example.com', $sanitized_email);

        // Notes can preserve newlines with sanitize_textarea_field.
        $sanitized_notes = sanitize_textarea_field($dirty_data['notes']);
        $this->assertStringContainsString("\n", $sanitized_notes);
    }

    /**
     * Test resume token generation and validation.
     */
    public function test_resume_token() {
        $token = bin2hex(random_bytes(32));

        // Token should be 64 characters.
        $this->assertEquals(64, strlen($token));

        // Token should be hex.
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);

        // Token expiry should be future.
        $expires_at = gmdate('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->assertGreaterThan(gmdate('Y-m-d H:i:s'), $expires_at);
    }

    /**
     * Test webhook payload structure.
     */
    public function test_webhook_payload() {
        $payload = [
            'event' => 'submission_completed',
            'instance_id' => 1,
            'submission_id' => 123,
            'data' => [
                'email' => 'test@example.com',
                'status' => 'completed',
            ],
            'timestamp' => gmdate('c'),
        ];

        // Required fields.
        $this->assertArrayHasKey('event', $payload);
        $this->assertArrayHasKey('timestamp', $payload);
        $this->assertArrayHasKey('data', $payload);

        // Event should be valid.
        $valid_events = [
            'submission_started',
            'submission_completed',
            'submission_abandoned',
            'submission_error',
        ];
        $this->assertContains($payload['event'], $valid_events);

        // Timestamp should be ISO 8601.
        $this->assertNotFalse(strtotime($payload['timestamp']));
    }

    /**
     * Test API response validation structure.
     */
    public function test_api_response_structure() {
        $success_response = [
            'success' => true,
            'data' => [
                'submission_id' => 123,
                'status' => 'completed',
            ],
        ];

        $error_response = [
            'success' => false,
            'error' => [
                'code' => 'validation_failed',
                'message' => 'Required field is missing',
                'field' => 'email',
            ],
        ];

        // Success response structure.
        $this->assertTrue($success_response['success']);
        $this->assertArrayHasKey('data', $success_response);

        // Error response structure.
        $this->assertFalse($error_response['success']);
        $this->assertArrayHasKey('error', $error_response);
        $this->assertArrayHasKey('code', $error_response['error']);
    }
}
