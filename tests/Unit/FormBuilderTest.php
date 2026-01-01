<?php
/**
 * FormBuilder Class Unit Tests
 *
 * Tests for the FFFL\Builder\FormBuilder class including field types,
 * schema validation, and form building functionality.
 *
 * @package FormFlow_Lite\Tests\Unit
 */

namespace FFFL\Tests\Unit;

use FFFL\Tests\TestCase;
use FFFL\Builder\FormBuilder;

class FormBuilderTest extends TestCase
{
    private FormBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset singleton for testing
        $reflection = new \ReflectionClass(FormBuilder::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->builder = FormBuilder::instance();
    }

    // =========================================================================
    // Singleton Tests
    // =========================================================================

    public function testInstanceReturnsSameObject(): void
    {
        $instance1 = FormBuilder::instance();
        $instance2 = FormBuilder::instance();

        $this->assertSame($instance1, $instance2);
    }

    // =========================================================================
    // get_field_types() Tests
    // =========================================================================

    public function testGetFieldTypesReturnsArray(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertIsArray($types);
        $this->assertNotEmpty($types);
    }

    public function testGetFieldTypesContainsBasicTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('text', $types);
        $this->assertArrayHasKey('email', $types);
        $this->assertArrayHasKey('phone', $types);
        $this->assertArrayHasKey('number', $types);
        $this->assertArrayHasKey('textarea', $types);
    }

    public function testGetFieldTypesContainsSelectionTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('select', $types);
        $this->assertArrayHasKey('radio', $types);
        $this->assertArrayHasKey('checkbox', $types);
        $this->assertArrayHasKey('toggle', $types);
    }

    public function testGetFieldTypesContainsAdvancedTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('date', $types);
        $this->assertArrayHasKey('time', $types);
        $this->assertArrayHasKey('file', $types);
        $this->assertArrayHasKey('signature', $types);
    }

    public function testGetFieldTypesContainsAddressTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('address', $types);
        $this->assertArrayHasKey('address_street', $types);
        $this->assertArrayHasKey('address_city', $types);
        $this->assertArrayHasKey('address_state', $types);
        $this->assertArrayHasKey('address_zip', $types);
    }

    public function testGetFieldTypesContainsUtilityTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('account_number', $types);
        $this->assertArrayHasKey('device_type', $types);
        $this->assertArrayHasKey('program_selector', $types);
    }

    public function testGetFieldTypesContainsLayoutTypes(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('heading', $types);
        $this->assertArrayHasKey('paragraph', $types);
        $this->assertArrayHasKey('divider', $types);
        $this->assertArrayHasKey('spacer', $types);
        $this->assertArrayHasKey('columns', $types);
        $this->assertArrayHasKey('section', $types);
    }

    public function testFieldTypeHasRequiredProperties(): void
    {
        $types = $this->builder->get_field_types();

        foreach ($types as $type => $config) {
            $this->assertArrayHasKey('label', $config, "Field type '{$type}' missing 'label'");
            $this->assertArrayHasKey('icon', $config, "Field type '{$type}' missing 'icon'");
            $this->assertArrayHasKey('category', $config, "Field type '{$type}' missing 'category'");
            $this->assertArrayHasKey('settings', $config, "Field type '{$type}' missing 'settings'");
        }
    }

    // =========================================================================
    // get_field_types_by_category() Tests
    // =========================================================================

    public function testGetFieldTypesByCategoryReturnsArray(): void
    {
        $categories = $this->builder->get_field_types_by_category();

        $this->assertIsArray($categories);
        $this->assertNotEmpty($categories);
    }

    public function testGetFieldTypesByCategoryHasAllCategories(): void
    {
        $categories = $this->builder->get_field_types_by_category();

        $this->assertArrayHasKey('basic', $categories);
        $this->assertArrayHasKey('selection', $categories);
        $this->assertArrayHasKey('advanced', $categories);
        $this->assertArrayHasKey('address', $categories);
        $this->assertArrayHasKey('utility', $categories);
        $this->assertArrayHasKey('layout', $categories);
    }

    public function testCategoryHasLabelAndFields(): void
    {
        $categories = $this->builder->get_field_types_by_category();

        foreach ($categories as $key => $category) {
            $this->assertArrayHasKey('label', $category, "Category '{$key}' missing 'label'");
            $this->assertArrayHasKey('fields', $category, "Category '{$key}' missing 'fields'");
        }
    }

    public function testBasicCategoryContainsTextFields(): void
    {
        $categories = $this->builder->get_field_types_by_category();

        $this->assertArrayHasKey('text', $categories['basic']['fields']);
        $this->assertArrayHasKey('email', $categories['basic']['fields']);
        $this->assertArrayHasKey('phone', $categories['basic']['fields']);
    }

    // =========================================================================
    // register_field_type() Tests
    // =========================================================================

    public function testRegisterFieldType(): void
    {
        $this->builder->register_field_type('custom_field', [
            'label' => 'Custom Field',
            'icon' => 'dashicons-star-filled',
            'category' => 'basic',
            'settings' => [],
        ]);

        $types = $this->builder->get_field_types();

        $this->assertArrayHasKey('custom_field', $types);
        $this->assertEquals('Custom Field', $types['custom_field']['label']);
    }

    // =========================================================================
    // validate_schema() Tests
    // =========================================================================

    public function testValidateSchemaValidSchema(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'first_name',
                            'settings' => ['label' => 'First Name'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateSchemaEmptySteps(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testValidateSchemaFieldMissingType(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'name' => 'first_name',
                            'settings' => [],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testValidateSchemaFieldMissingName(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'text',
                            'settings' => [],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
    }

    public function testValidateSchemaLayoutFieldsCanMissName(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'heading',
                            'settings' => ['text' => 'Welcome'],
                        ],
                        [
                            'type' => 'divider',
                            'settings' => [],
                        ],
                        [
                            'type' => 'spacer',
                            'settings' => ['height' => 20],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertTrue($result['valid']);
    }

    public function testValidateSchemaDuplicateFieldNames(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'email',
                            'settings' => [],
                        ],
                        [
                            'type' => 'email',
                            'name' => 'email',
                            'settings' => [],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Duplicate', $result['errors'][0]);
    }

    public function testValidateSchemaInvalidFieldNameFormat(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => '123invalid',
                            'settings' => [],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
    }

    public function testValidateSchemaValidFieldNameFormat(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'valid_field_name123',
                            'settings' => [],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'another-valid-name',
                            'settings' => [],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertTrue($result['valid']);
    }

    // =========================================================================
    // get_default_schema() Tests
    // =========================================================================

    public function testGetDefaultSchema(): void
    {
        $schema = $this->builder->get_default_schema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('version', $schema);
        $this->assertArrayHasKey('steps', $schema);
        $this->assertArrayHasKey('settings', $schema);
    }

    public function testGetDefaultSchemaHasOneStep(): void
    {
        $schema = $this->builder->get_default_schema();

        $this->assertCount(1, $schema['steps']);
        $this->assertEquals('step_1', $schema['steps'][0]['id']);
    }

    public function testGetDefaultSchemaHasSettings(): void
    {
        $schema = $this->builder->get_default_schema();

        $this->assertArrayHasKey('submit_button_text', $schema['settings']);
        $this->assertArrayHasKey('success_message', $schema['settings']);
    }

    // =========================================================================
    // check_admin_permission() Tests
    // =========================================================================

    public function testCheckAdminPermissionWithCapability(): void
    {
        $this->setUserCapability('manage_options', true);

        $result = $this->builder->check_admin_permission();

        $this->assertTrue($result);
    }

    public function testCheckAdminPermissionWithoutCapability(): void
    {
        $this->setUserCapability('manage_options', false);

        $result = $this->builder->check_admin_permission();

        $this->assertFalse($result);
    }

    // =========================================================================
    // Field Settings Tests
    // =========================================================================

    public function testTextFieldHasExpectedSettings(): void
    {
        $types = $this->builder->get_field_types();
        $textField = $types['text'];

        $this->assertArrayHasKey('label', $textField['settings']);
        $this->assertArrayHasKey('placeholder', $textField['settings']);
        $this->assertArrayHasKey('required', $textField['settings']);
        $this->assertArrayHasKey('help_text', $textField['settings']);
        $this->assertArrayHasKey('max_length', $textField['settings']);
        $this->assertArrayHasKey('pattern', $textField['settings']);
    }

    public function testEmailFieldHasConfirmSetting(): void
    {
        $types = $this->builder->get_field_types();
        $emailField = $types['email'];

        $this->assertArrayHasKey('confirm', $emailField['settings']);
    }

    public function testSelectFieldHasOptionsSetting(): void
    {
        $types = $this->builder->get_field_types();
        $selectField = $types['select'];

        $this->assertArrayHasKey('options', $selectField['settings']);
        $this->assertArrayHasKey('searchable', $selectField['settings']);
    }

    public function testFileFieldHasUploadSettings(): void
    {
        $types = $this->builder->get_field_types();
        $fileField = $types['file'];

        $this->assertArrayHasKey('allowed_types', $fileField['settings']);
        $this->assertArrayHasKey('max_size', $fileField['settings']);
        $this->assertArrayHasKey('multiple', $fileField['settings']);
    }

    public function testContainerFieldsHaveIsContainer(): void
    {
        $types = $this->builder->get_field_types();

        $this->assertTrue($types['columns']['is_container'] ?? false);
        $this->assertTrue($types['section']['is_container'] ?? false);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testValidateSchemaWithNoStepsKey(): void
    {
        $schema = [
            'version' => '1.0',
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
    }

    public function testValidateSchemaWithNullSteps(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => null,
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertFalse($result['valid']);
    }

    public function testValidateSchemaWithEmptyFields(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Step 1',
                    'fields' => [],
                ],
            ],
        ];

        // Empty fields is valid - step can exist without fields
        $result = $this->builder->validate_schema($schema);

        $this->assertTrue($result['valid']);
    }

    public function testValidateSchemaWithMultipleSteps(): void
    {
        $schema = [
            'version' => '1.0',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Personal Info',
                    'fields' => [
                        ['type' => 'text', 'name' => 'first_name', 'settings' => []],
                        ['type' => 'text', 'name' => 'last_name', 'settings' => []],
                    ],
                ],
                [
                    'id' => 'step_2',
                    'title' => 'Contact Info',
                    'fields' => [
                        ['type' => 'email', 'name' => 'email', 'settings' => []],
                        ['type' => 'phone', 'name' => 'phone', 'settings' => []],
                    ],
                ],
            ],
        ];

        $result = $this->builder->validate_schema($schema);

        $this->assertTrue($result['valid']);
    }
}
