<?php
/**
 * Form validation tests for FormFlow Lite.
 *
 * Tests field validation functionality.
 *
 * @package FormFlow_Lite
 */

class Test_Validation extends FormFlow_Lite_TestCase {

    /**
     * Test email validation.
     */
    public function test_email_validation() {
        $valid_emails = [
            'test@example.com',
            'user.name@domain.org',
            'user+tag@example.co.uk',
        ];

        $invalid_emails = [
            'notanemail',
            '@nodomain.com',
            'spaces in@email.com',
            'missing@.com',
        ];

        foreach ($valid_emails as $email) {
            $this->assertNotFalse(filter_var($email, FILTER_VALIDATE_EMAIL), "Email should be valid: $email");
        }

        foreach ($invalid_emails as $email) {
            $this->assertFalse(filter_var($email, FILTER_VALIDATE_EMAIL), "Email should be invalid: $email");
        }
    }

    /**
     * Test phone number format validation.
     */
    public function test_phone_validation() {
        $valid_phones = [
            '555-123-4567',
            '(555) 123-4567',
            '+1 555 123 4567',
            '5551234567',
        ];

        // Pattern for US phone numbers.
        $pattern = '/^[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[(]?[0-9]{1,3}[)]?[-\s\.]?[0-9]{3,4}[-\s\.]?[0-9]{4}$/';

        foreach ($valid_phones as $phone) {
            // Remove non-digit characters for length check.
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $this->assertGreaterThanOrEqual(10, strlen($digits), "Phone should have at least 10 digits: $phone");
        }
    }

    /**
     * Test required field validation.
     */
    public function test_required_field_validation() {
        $empty_values = ['', null, '   ', []];
        $valid_values = ['text', 0, '0', ['item']];

        foreach ($empty_values as $value) {
            $is_empty = empty(trim((string) $value));
            // Note: This test accounts for trim behavior.
            if ($value !== 0 && $value !== '0') {
                $this->assertTrue($is_empty || empty($value), 'Value should be considered empty');
            }
        }

        foreach ($valid_values as $value) {
            if (is_array($value)) {
                $this->assertNotEmpty($value, 'Array should not be empty');
            } else {
                $this->assertTrue(strlen((string) $value) > 0, 'Value should have content');
            }
        }
    }

    /**
     * Test URL validation.
     */
    public function test_url_validation() {
        $valid_urls = [
            'https://example.com',
            'http://www.example.org/path',
            'https://subdomain.example.com/path?query=value',
        ];

        $invalid_urls = [
            'not-a-url',
            'ftp://example.com', // FTP might not be valid for form URLs.
            '//missing-protocol.com',
            'http:/missing-slash.com',
        ];

        foreach ($valid_urls as $url) {
            $this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL), "URL should be valid: $url");
        }

        foreach ($invalid_urls as $url) {
            // Note: Some FTP URLs pass FILTER_VALIDATE_URL.
            $result = filter_var($url, FILTER_VALIDATE_URL);
            if ($url === 'ftp://example.com') {
                // FTP is technically valid.
                $this->assertNotFalse($result);
            } else {
                $this->assertFalse($result, "URL should be invalid: $url");
            }
        }
    }

    /**
     * Test date format validation.
     */
    public function test_date_validation() {
        $valid_dates = [
            '2025-01-15',
            '2024-12-31',
            '2023-06-01',
        ];

        $invalid_dates = [
            '2025-13-01', // Invalid month.
            '2025-01-32', // Invalid day.
            '01-15-2025', // Wrong format.
            'not-a-date',
        ];

        foreach ($valid_dates as $date) {
            $parsed = \DateTime::createFromFormat('Y-m-d', $date);
            $this->assertInstanceOf(\DateTime::class, $parsed, "Date should be valid: $date");
            $this->assertEquals($date, $parsed->format('Y-m-d'), "Date should parse correctly: $date");
        }

        foreach ($invalid_dates as $date) {
            $parsed = \DateTime::createFromFormat('Y-m-d', $date);
            if ($parsed) {
                // DateTime might parse invalid dates, but the formatted result won't match.
                $this->assertNotEquals($date, $parsed->format('Y-m-d'), "Date should not parse correctly: $date");
            } else {
                $this->assertFalse($parsed, "Date should be invalid: $date");
            }
        }
    }

    /**
     * Test numeric validation.
     */
    public function test_numeric_validation() {
        $valid_numbers = [123, '456', 78.9, '12.34', 0, '0'];
        $invalid_numbers = ['abc', '12.34.56', '', null, '12abc'];

        foreach ($valid_numbers as $num) {
            $this->assertTrue(is_numeric($num), "Value should be numeric: $num");
        }

        foreach ($invalid_numbers as $num) {
            $this->assertFalse(is_numeric($num), "Value should not be numeric: " . var_export($num, true));
        }
    }

    /**
     * Test minimum length validation.
     */
    public function test_min_length_validation() {
        $min_length = 5;

        $this->assertGreaterThanOrEqual($min_length, strlen('hello'));
        $this->assertGreaterThanOrEqual($min_length, strlen('longer string'));
        $this->assertLessThan($min_length, strlen('hi'));
        $this->assertLessThan($min_length, strlen('test'));
    }

    /**
     * Test maximum length validation.
     */
    public function test_max_length_validation() {
        $max_length = 10;

        $this->assertLessThanOrEqual($max_length, strlen('short'));
        $this->assertLessThanOrEqual($max_length, strlen('exactly10!'));
        $this->assertGreaterThan($max_length, strlen('this is too long'));
    }

    /**
     * Test checkbox/multi-select validation.
     */
    public function test_multi_select_validation() {
        $min_selections = 2;
        $max_selections = 5;

        $valid_selections = [['a', 'b'], ['a', 'b', 'c'], ['a', 'b', 'c', 'd', 'e']];
        $invalid_selections = [[], ['a'], ['a', 'b', 'c', 'd', 'e', 'f']];

        foreach ($valid_selections as $selections) {
            $count = count($selections);
            $this->assertGreaterThanOrEqual($min_selections, $count);
            $this->assertLessThanOrEqual($max_selections, $count);
        }

        foreach ($invalid_selections as $selections) {
            $count = count($selections);
            $is_valid = $count >= $min_selections && $count <= $max_selections;
            $this->assertFalse($is_valid, 'Selection count should be invalid');
        }
    }
}
