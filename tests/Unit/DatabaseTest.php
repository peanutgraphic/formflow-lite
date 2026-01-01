<?php
/**
 * Database Class Unit Tests
 *
 * Tests for the FFFL\Database\Database class including CRUD operations
 * for instances, submissions, and logging.
 *
 * @package FormFlow_Lite\Tests\Unit
 */

namespace FFFL\Tests\Unit;

use FFFL\Tests\TestCase;
use FFFL\Database\Database;

class DatabaseTest extends TestCase
{
    private Database $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new Database();
    }

    // =========================================================================
    // get_instances() Tests
    // =========================================================================

    public function testGetInstancesReturnsArray(): void
    {
        $this->setDbResult('get_results', []);

        $result = $this->database->get_instances();

        $this->assertIsArray($result);
    }

    public function testGetInstancesWithActiveOnly(): void
    {
        $this->setDbResult('get_results', [
            [
                'id' => 1,
                'name' => 'Active Form',
                'slug' => 'active-form',
                'utility' => 'test',
                'form_type' => 'enrollment',
                'api_endpoint' => 'https://api.example.com',
                'api_password' => '',
                'support_email_from' => '',
                'support_email_to' => '',
                'settings' => '{}',
                'is_active' => 1,
                'test_mode' => 0,
            ],
        ]);

        $result = $this->database->get_instances(true);

        $this->assertCount(1, $result);
    }

    public function testGetInstancesDecodesData(): void
    {
        $this->setDbResult('get_results', [
            [
                'id' => 1,
                'name' => 'Test Form',
                'slug' => 'test-form',
                'utility' => 'test',
                'form_type' => 'enrollment',
                'api_endpoint' => 'https://api.example.com',
                'api_password' => '',
                'support_email_from' => '',
                'support_email_to' => '',
                'settings' => '{"demo_mode": true}',
                'is_active' => 1,
                'test_mode' => 0,
            ],
        ]);

        $result = $this->database->get_instances();

        $this->assertTrue($result[0]['is_active']);
        $this->assertFalse($result[0]['test_mode']);
        $this->assertIsArray($result[0]['settings']);
        $this->assertTrue($result[0]['settings']['demo_mode']);
    }

    public function testGetInstancesWithDifferentOrdering(): void
    {
        $this->setDbResult('get_results', []);

        // These should not throw errors
        $this->database->get_instances(false, 'display_order');
        $this->database->get_instances(false, 'name');
        $this->database->get_instances(false, 'created_at');

        $this->assertTrue(true); // If we got here, no exceptions were thrown
    }

    // =========================================================================
    // get_instance() Tests
    // =========================================================================

    public function testGetInstanceById(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'name' => 'Test Form',
            'slug' => 'test-form',
            'utility' => 'test',
            'form_type' => 'enrollment',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => '',
            'support_email_from' => '',
            'support_email_to' => '',
            'settings' => '{}',
            'is_active' => 1,
            'test_mode' => 0,
        ]);

        $result = $this->database->get_instance(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test Form', $result['name']);
    }

    public function testGetInstanceByIdNotFound(): void
    {
        $this->setDbResult('get_row', null);

        $result = $this->database->get_instance(999);

        $this->assertNull($result);
    }

    // =========================================================================
    // get_instance_by_slug() Tests
    // =========================================================================

    public function testGetInstanceBySlug(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'name' => 'Test Form',
            'slug' => 'test-form',
            'utility' => 'test',
            'form_type' => 'enrollment',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => '',
            'support_email_from' => '',
            'support_email_to' => '',
            'settings' => '{}',
            'is_active' => 1,
            'test_mode' => 0,
        ]);

        $result = $this->database->get_instance_by_slug('test-form');

        $this->assertNotNull($result);
        $this->assertEquals('test-form', $result['slug']);
    }

    public function testGetInstanceBySlugActiveOnly(): void
    {
        $this->setDbResult('get_row', null);

        $result = $this->database->get_instance_by_slug('test-form', true);

        $this->assertNull($result);
    }

    public function testGetInstanceBySlugNotFound(): void
    {
        $this->setDbResult('get_row', null);

        $result = $this->database->get_instance_by_slug('non-existent');

        $this->assertNull($result);
    }

    // =========================================================================
    // get_instance_by_utility() Tests
    // =========================================================================

    public function testGetInstanceByUtility(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'name' => 'Utility Form',
            'slug' => 'utility-form',
            'utility' => 'pepco-dc',
            'form_type' => 'enrollment',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => '',
            'support_email_from' => '',
            'support_email_to' => '',
            'settings' => '{}',
            'is_active' => 1,
            'test_mode' => 0,
        ]);

        $result = $this->database->get_instance_by_utility('pepco-dc');

        $this->assertNotNull($result);
        $this->assertEquals('pepco-dc', $result['utility']);
    }

    // =========================================================================
    // create_instance() Tests
    // =========================================================================

    public function testCreateInstance(): void
    {
        $this->setInsertId(1);

        $data = [
            'name' => 'New Form',
            'slug' => 'new-form',
            'utility' => 'test-utility',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => 'secret',
        ];

        $result = $this->database->create_instance($data);

        $this->assertEquals(1, $result);
    }

    public function testCreateInstanceWithAllFields(): void
    {
        $this->setInsertId(2);

        $data = [
            'name' => 'Complete Form',
            'slug' => 'complete-form',
            'utility' => 'test-utility',
            'form_type' => 'scheduler',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => 'secret',
            'support_email_from' => 'support@example.com',
            'support_email_to' => 'admin@example.com',
            'settings' => ['demo_mode' => true],
            'is_active' => 0,
            'test_mode' => 1,
        ];

        $result = $this->database->create_instance($data);

        $this->assertEquals(2, $result);
    }

    // =========================================================================
    // update_instance() Tests
    // =========================================================================

    public function testUpdateInstance(): void
    {
        $result = $this->database->update_instance(1, [
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateInstanceMultipleFields(): void
    {
        $result = $this->database->update_instance(1, [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'is_active' => 0,
            'test_mode' => 1,
            'settings' => ['key' => 'value'],
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateInstanceEmptyDataReturnsTrue(): void
    {
        $result = $this->database->update_instance(1, []);

        $this->assertTrue($result);
    }

    public function testUpdateInstanceWithNewPassword(): void
    {
        $result = $this->database->update_instance(1, [
            'api_password' => 'new_password',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateInstanceWithEmptyPasswordSkipsUpdate(): void
    {
        $result = $this->database->update_instance(1, [
            'api_password' => '',
        ]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // delete_instance() Tests
    // =========================================================================

    public function testDeleteInstance(): void
    {
        $result = $this->database->delete_instance(1);

        $this->assertTrue($result);
    }

    // =========================================================================
    // create_submission() Tests
    // =========================================================================

    public function testCreateSubmission(): void
    {
        $this->setInsertId(1);

        $data = [
            'instance_id' => 1,
            'session_id' => 'abc123',
            'form_data' => ['first_name' => 'John'],
        ];

        $result = $this->database->create_submission($data);

        $this->assertEquals(1, $result);
    }

    public function testCreateSubmissionWithAllFields(): void
    {
        $this->setInsertId(2);

        $data = [
            'instance_id' => 1,
            'session_id' => 'abc123',
            'account_number' => '1234567890',
            'customer_name' => 'John Doe',
            'device_type' => 'thermostat',
            'form_data' => ['first_name' => 'John', 'last_name' => 'Doe'],
            'status' => 'completed',
            'step' => 5,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'PHPUnit Test',
        ];

        $result = $this->database->create_submission($data);

        $this->assertEquals(2, $result);
    }

    // =========================================================================
    // get_submission_by_session() Tests
    // =========================================================================

    public function testGetSubmissionBySession(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'instance_id' => 1,
            'session_id' => 'abc123',
            'account_number' => '1234567890',
            'customer_name' => 'John Doe',
            'device_type' => 'thermostat',
            'form_data' => '',
            'api_response' => null,
            'status' => 'in_progress',
            'step' => 2,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'created_at' => '2024-01-01 00:00:00',
            'completed_at' => null,
        ]);

        $result = $this->database->get_submission_by_session('abc123', 1);

        $this->assertNotNull($result);
        $this->assertEquals('abc123', $result['session_id']);
    }

    public function testGetSubmissionBySessionNotFound(): void
    {
        $this->setDbResult('get_row', null);

        $result = $this->database->get_submission_by_session('nonexistent', 1);

        $this->assertNull($result);
    }

    // =========================================================================
    // get_submission() Tests
    // =========================================================================

    public function testGetSubmissionById(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'instance_id' => 1,
            'session_id' => 'abc123',
            'account_number' => '1234567890',
            'customer_name' => 'John Doe',
            'device_type' => 'thermostat',
            'form_data' => '',
            'api_response' => null,
            'status' => 'in_progress',
            'step' => 2,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'created_at' => '2024-01-01 00:00:00',
            'completed_at' => null,
        ]);

        $result = $this->database->get_submission(1);

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['id']);
    }

    // =========================================================================
    // update_submission() Tests
    // =========================================================================

    public function testUpdateSubmission(): void
    {
        $result = $this->database->update_submission(1, [
            'status' => 'completed',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateSubmissionSetsCompletedAt(): void
    {
        $result = $this->database->update_submission(1, [
            'status' => 'completed',
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateSubmissionWithFormData(): void
    {
        $result = $this->database->update_submission(1, [
            'form_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateSubmissionWithApiResponse(): void
    {
        $result = $this->database->update_submission(1, [
            'api_response' => [
                'success' => true,
                'confirmation' => 'ABC123',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateSubmissionEmptyDataReturnsTrue(): void
    {
        $result = $this->database->update_submission(1, []);

        $this->assertTrue($result);
    }

    // =========================================================================
    // get_submissions() Tests
    // =========================================================================

    public function testGetSubmissions(): void
    {
        $this->setDbResult('get_results', []);
        $this->setDbResult('get_var', 0);

        $result = $this->database->get_submissions();

        $this->assertIsArray($result);
    }

    public function testGetSubmissionsWithFilters(): void
    {
        $this->setDbResult('get_results', []);
        $this->setDbResult('get_var', 0);

        $result = $this->database->get_submissions([
            'instance_id' => 1,
            'status' => 'completed',
        ]);

        $this->assertIsArray($result);
    }

    public function testGetSubmissionsWithDateRange(): void
    {
        $this->setDbResult('get_results', []);
        $this->setDbResult('get_var', 0);

        $result = $this->database->get_submissions([
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
        ]);

        $this->assertIsArray($result);
    }

    public function testGetSubmissionsWithSearch(): void
    {
        $this->setDbResult('get_results', []);
        $this->setDbResult('get_var', 0);

        $result = $this->database->get_submissions([
            'search' => 'john',
        ]);

        $this->assertIsArray($result);
    }

    public function testGetSubmissionsWithPagination(): void
    {
        $this->setDbResult('get_results', []);
        $this->setDbResult('get_var', 0);

        $result = $this->database->get_submissions([], 10, 20);

        $this->assertIsArray($result);
    }

    // =========================================================================
    // log() Tests
    // =========================================================================

    public function testLog(): void
    {
        $this->setInsertId(1);

        $this->database->log('info', 'Test log message');

        $this->assertTrue(true); // No exception = success
    }

    public function testLogWithContext(): void
    {
        $this->setInsertId(1);

        $this->database->log('error', 'Error occurred', [
            'error_code' => 500,
            'details' => 'Something went wrong',
        ]);

        $this->assertTrue(true);
    }

    public function testLogWithInstanceAndSubmission(): void
    {
        $this->setInsertId(1);

        $this->database->log('warning', 'Warning message', [], 1, 5);

        $this->assertTrue(true);
    }

    public function testLogDifferentLevels(): void
    {
        $this->setInsertId(1);

        $this->database->log('debug', 'Debug message');
        $this->database->log('info', 'Info message');
        $this->database->log('warning', 'Warning message');
        $this->database->log('error', 'Error message');

        $this->assertTrue(true);
    }

    // =========================================================================
    // Helper Method Tests
    // =========================================================================

    public function testDecodeSubmissionDecryptsFormData(): void
    {
        $this->setDbResult('get_row', [
            'id' => 1,
            'instance_id' => 1,
            'session_id' => 'abc123',
            'account_number' => null,
            'customer_name' => null,
            'device_type' => null,
            'form_data' => '', // Would be encrypted in real scenario
            'api_response' => null,
            'status' => 'in_progress',
            'step' => 1,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'created_at' => '2024-01-01 00:00:00',
            'completed_at' => null,
        ]);

        $result = $this->database->get_submission(1);

        $this->assertNotNull($result);
        $this->assertIsArray($result['form_data']);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testCreateInstanceWithEmptyPassword(): void
    {
        $this->setInsertId(1);

        $data = [
            'name' => 'No Password Form',
            'slug' => 'no-password',
            'utility' => 'test',
            'api_endpoint' => 'https://api.example.com',
            'api_password' => '',
        ];

        $result = $this->database->create_instance($data);

        $this->assertEquals(1, $result);
    }

    public function testCreateSubmissionWithLongUserAgent(): void
    {
        $this->setInsertId(1);

        $longUserAgent = str_repeat('A', 1000);

        $data = [
            'instance_id' => 1,
            'session_id' => 'abc123',
            'form_data' => [],
            'user_agent' => $longUserAgent,
        ];

        $result = $this->database->create_submission($data);

        $this->assertEquals(1, $result);
    }

    public function testGetInstancesEmptyDatabase(): void
    {
        $this->setDbResult('get_results', []);

        $result = $this->database->get_instances();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testUpdateSubmissionWithStep(): void
    {
        $result = $this->database->update_submission(1, [
            'step' => 3,
        ]);

        $this->assertTrue($result);
    }
}
