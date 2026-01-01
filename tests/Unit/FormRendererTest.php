<?php
/**
 * FormRenderer Class Unit Tests
 *
 * Tests for the FFFL\Builder\FormRenderer class including field rendering,
 * form output, and accessibility features.
 *
 * @package FormFlow_Lite\Tests\Unit
 */

namespace FFFL\Tests\Unit;

use FFFL\Tests\TestCase;
use FFFL\Builder\FormRenderer;

class FormRendererTest extends TestCase
{
    private FormRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new FormRenderer();
    }

    // =========================================================================
    // render() Tests
    // =========================================================================

    public function testRenderEmptySchemaReturnsEmptyString(): void
    {
        $schema = ['steps' => []];

        $result = $this->renderer->render($schema);

        $this->assertEquals('', $result);
    }

    public function testRenderBasicForm(): void
    {
        $schema = [
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Test Step',
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

        $html = $this->renderer->render($schema);

        $this->assertStringContainsString('ff-form-container', $html);
        $this->assertStringContainsString('ff-form', $html);
        $this->assertStringContainsString('First Name', $html);
    }

    public function testRenderIncludesProgressBar(): void
    {
        $schema = [
            'steps' => [
                ['id' => 'step_1', 'title' => 'Step 1', 'fields' => []],
                ['id' => 'step_2', 'title' => 'Step 2', 'fields' => []],
            ],
        ];

        $html = $this->renderer->render($schema);

        $this->assertStringContainsString('ff-progress-container', $html);
        $this->assertStringContainsString('ff-progress-bar', $html);
    }

    public function testRenderIncludesFormActions(): void
    {
        $schema = [
            'steps' => [
                ['id' => 'step_1', 'title' => 'Step 1', 'fields' => []],
            ],
            'settings' => [
                'submit_button_text' => 'Submit Form',
                'prev_button_text' => 'Go Back',
                'next_button_text' => 'Continue',
            ],
        ];

        $html = $this->renderer->render($schema);

        $this->assertStringContainsString('ff-btn-submit', $html);
        $this->assertStringContainsString('ff-btn-prev', $html);
        $this->assertStringContainsString('ff-btn-next', $html);
        $this->assertStringContainsString('Submit Form', $html);
    }

    public function testRenderIncludesNonceField(): void
    {
        $schema = [
            'steps' => [
                ['id' => 'step_1', 'title' => 'Step 1', 'fields' => []],
            ],
        ];

        $html = $this->renderer->render($schema);

        $this->assertStringContainsString('fffl_nonce', $html);
    }

    public function testRenderIncludesInstanceId(): void
    {
        $schema = [
            'steps' => [
                ['id' => 'step_1', 'title' => 'Step 1', 'fields' => []],
            ],
        ];

        $instance = ['id' => 42];

        $html = $this->renderer->render($schema, $instance);

        $this->assertStringContainsString('instance_id', $html);
        $this->assertStringContainsString('42', $html);
    }

    // =========================================================================
    // render_preview() Tests
    // =========================================================================

    public function testRenderPreview(): void
    {
        $schema = [
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Preview Test',
                    'fields' => [],
                ],
            ],
        ];

        $html = $this->renderer->render_preview($schema);

        $this->assertStringContainsString('ff-form-container', $html);
    }

    // =========================================================================
    // render_field() Tests - Text Input
    // =========================================================================

    public function testRenderTextInput(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'first_name',
            'settings' => [
                'label' => 'First Name',
                'placeholder' => 'Enter your name',
                'required' => true,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('name="first_name"', $html);
        $this->assertStringContainsString('First Name', $html);
        $this->assertStringContainsString('placeholder="Enter your name"', $html);
        $this->assertStringContainsString('required', $html);
    }

    public function testRenderTextInputWithHelpText(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'username',
            'settings' => [
                'label' => 'Username',
                'help_text' => 'Choose a unique username',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-help-text', $html);
        $this->assertStringContainsString('Choose a unique username', $html);
        $this->assertStringContainsString('aria-describedby', $html);
    }

    public function testRenderTextInputWithMaxLength(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'short_text',
            'settings' => [
                'label' => 'Short Text',
                'max_length' => 50,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('maxlength="50"', $html);
    }

    public function testRenderTextInputWithPattern(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'code',
            'settings' => [
                'label' => 'Code',
                'pattern' => '[A-Z]{3}-[0-9]{4}',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('pattern="[A-Z]{3}-[0-9]{4}"', $html);
    }

    // =========================================================================
    // render_field() Tests - Email
    // =========================================================================

    public function testRenderEmailInput(): void
    {
        $field = [
            'type' => 'email',
            'name' => 'email',
            'settings' => [
                'label' => 'Email Address',
                'required' => true,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="email"', $html);
        $this->assertStringContainsString('name="email"', $html);
    }

    // =========================================================================
    // render_field() Tests - Phone
    // =========================================================================

    public function testRenderPhoneInput(): void
    {
        $field = [
            'type' => 'phone',
            'name' => 'phone',
            'settings' => [
                'label' => 'Phone Number',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="tel"', $html);
        $this->assertStringContainsString('name="phone"', $html);
    }

    // =========================================================================
    // render_field() Tests - Number
    // =========================================================================

    public function testRenderNumberInput(): void
    {
        $field = [
            'type' => 'number',
            'name' => 'quantity',
            'settings' => [
                'label' => 'Quantity',
                'min' => 1,
                'max' => 100,
                'step' => 1,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="number"', $html);
        $this->assertStringContainsString('min="1"', $html);
        $this->assertStringContainsString('max="100"', $html);
        $this->assertStringContainsString('step="1"', $html);
    }

    // =========================================================================
    // render_field() Tests - Textarea
    // =========================================================================

    public function testRenderTextarea(): void
    {
        $field = [
            'type' => 'textarea',
            'name' => 'comments',
            'settings' => [
                'label' => 'Comments',
                'rows' => 5,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="comments"', $html);
        $this->assertStringContainsString('rows="5"', $html);
    }

    public function testRenderTextareaWithValue(): void
    {
        $field = [
            'type' => 'textarea',
            'name' => 'description',
            'settings' => [
                'label' => 'Description',
            ],
        ];

        $form_data = ['description' => 'Existing text content'];

        $html = $this->renderer->render_field($field, $form_data);

        $this->assertStringContainsString('Existing text content', $html);
    }

    // =========================================================================
    // render_field() Tests - Select
    // =========================================================================

    public function testRenderSelect(): void
    {
        $field = [
            'type' => 'select',
            'name' => 'country',
            'settings' => [
                'label' => 'Country',
                'options' => [
                    ['value' => 'us', 'label' => 'United States'],
                    ['value' => 'ca', 'label' => 'Canada'],
                    ['value' => 'mx', 'label' => 'Mexico'],
                ],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="country"', $html);
        $this->assertStringContainsString('United States', $html);
        $this->assertStringContainsString('value="us"', $html);
    }

    public function testRenderSelectWithPlaceholder(): void
    {
        $field = [
            'type' => 'select',
            'name' => 'state',
            'settings' => [
                'label' => 'State',
                'placeholder' => 'Choose a state',
                'options' => [],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('Choose a state', $html);
    }

    public function testRenderSelectWithSelectedValue(): void
    {
        $field = [
            'type' => 'select',
            'name' => 'color',
            'settings' => [
                'label' => 'Color',
                'options' => [
                    ['value' => 'red', 'label' => 'Red'],
                    ['value' => 'blue', 'label' => 'Blue'],
                ],
            ],
        ];

        $form_data = ['color' => 'blue'];

        $html = $this->renderer->render_field($field, $form_data);

        $this->assertStringContainsString('selected', $html);
    }

    // =========================================================================
    // render_field() Tests - Radio
    // =========================================================================

    public function testRenderRadio(): void
    {
        $field = [
            'type' => 'radio',
            'name' => 'gender',
            'settings' => [
                'label' => 'Gender',
                'options' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                    ['value' => 'other', 'label' => 'Other'],
                ],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('name="gender"', $html);
        $this->assertStringContainsString('Male', $html);
        $this->assertStringContainsString('Female', $html);
    }

    public function testRenderRadioWithLayout(): void
    {
        $field = [
            'type' => 'radio',
            'name' => 'choice',
            'settings' => [
                'label' => 'Choice',
                'layout' => 'horizontal',
                'options' => [
                    ['value' => 'a', 'label' => 'A'],
                    ['value' => 'b', 'label' => 'B'],
                ],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-layout-horizontal', $html);
    }

    // =========================================================================
    // render_field() Tests - Checkbox
    // =========================================================================

    public function testRenderCheckbox(): void
    {
        $field = [
            'type' => 'checkbox',
            'name' => 'interests',
            'settings' => [
                'label' => 'Interests',
                'options' => [
                    ['value' => 'sports', 'label' => 'Sports'],
                    ['value' => 'music', 'label' => 'Music'],
                    ['value' => 'art', 'label' => 'Art'],
                ],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('name="interests[]"', $html);
        $this->assertStringContainsString('Sports', $html);
    }

    // =========================================================================
    // render_field() Tests - Toggle
    // =========================================================================

    public function testRenderToggle(): void
    {
        $field = [
            'type' => 'toggle',
            'name' => 'subscribe',
            'settings' => [
                'label' => 'Subscribe to newsletter',
                'on_label' => 'Yes',
                'off_label' => 'No',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-toggle', $html);
        $this->assertStringContainsString('Subscribe to newsletter', $html);
    }

    // =========================================================================
    // render_field() Tests - Date
    // =========================================================================

    public function testRenderDate(): void
    {
        $field = [
            'type' => 'date',
            'name' => 'birth_date',
            'settings' => [
                'label' => 'Birth Date',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="date"', $html);
        $this->assertStringContainsString('name="birth_date"', $html);
    }

    public function testRenderDateWithMinMax(): void
    {
        $field = [
            'type' => 'date',
            'name' => 'appointment',
            'settings' => [
                'label' => 'Appointment Date',
                'min_date' => '2024-01-01',
                'max_date' => '2024-12-31',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('min="2024-01-01"', $html);
        $this->assertStringContainsString('max="2024-12-31"', $html);
    }

    // =========================================================================
    // render_field() Tests - Time
    // =========================================================================

    public function testRenderTime(): void
    {
        $field = [
            'type' => 'time',
            'name' => 'meeting_time',
            'settings' => [
                'label' => 'Meeting Time',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="time"', $html);
        $this->assertStringContainsString('name="meeting_time"', $html);
    }

    // =========================================================================
    // render_field() Tests - File
    // =========================================================================

    public function testRenderFile(): void
    {
        $field = [
            'type' => 'file',
            'name' => 'document',
            'settings' => [
                'label' => 'Upload Document',
                'allowed_types' => 'pdf,doc,docx',
                'max_size' => 10,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('accept=".pdf,.doc,.docx"', $html);
        $this->assertStringContainsString('data-max-size="10"', $html);
    }

    public function testRenderFileMultiple(): void
    {
        $field = [
            'type' => 'file',
            'name' => 'images',
            'settings' => [
                'label' => 'Upload Images',
                'multiple' => true,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('multiple', $html);
        $this->assertStringContainsString('name="images[]"', $html);
    }

    // =========================================================================
    // render_field() Tests - Signature
    // =========================================================================

    public function testRenderSignature(): void
    {
        $field = [
            'type' => 'signature',
            'name' => 'signature',
            'settings' => [
                'label' => 'Your Signature',
                'width' => 400,
                'height' => 150,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<canvas', $html);
        $this->assertStringContainsString('ff-signature-canvas', $html);
        $this->assertStringContainsString('width="400"', $html);
        $this->assertStringContainsString('height="150"', $html);
    }

    // =========================================================================
    // render_field() Tests - Address
    // =========================================================================

    public function testRenderAddress(): void
    {
        $field = [
            'type' => 'address',
            'name' => 'service_address',
            'settings' => [
                'label' => 'Service Address',
                'include_unit' => true,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-address-fieldset', $html);
        $this->assertStringContainsString('ff-address-street', $html);
        $this->assertStringContainsString('ff-address-city', $html);
        $this->assertStringContainsString('ff-address-state', $html);
        $this->assertStringContainsString('ff-address-zip', $html);
    }

    // =========================================================================
    // render_field() Tests - Layout Elements
    // =========================================================================

    public function testRenderHeading(): void
    {
        $field = [
            'type' => 'heading',
            'settings' => [
                'text' => 'Section Title',
                'level' => 'h2',
                'alignment' => 'center',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<h2', $html);
        $this->assertStringContainsString('Section Title', $html);
        $this->assertStringContainsString('text-align: center', $html);
    }

    public function testRenderParagraph(): void
    {
        $field = [
            'type' => 'paragraph',
            'settings' => [
                'content' => 'This is some explanatory text.',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-paragraph', $html);
        $this->assertStringContainsString('This is some explanatory text.', $html);
    }

    public function testRenderDivider(): void
    {
        $field = [
            'type' => 'divider',
            'settings' => [
                'style' => 'dashed',
                'spacing' => 'large',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<hr', $html);
        $this->assertStringContainsString('ff-divider', $html);
        $this->assertStringContainsString('border-style: dashed', $html);
    }

    public function testRenderSpacer(): void
    {
        $field = [
            'type' => 'spacer',
            'settings' => [
                'height' => 30,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('ff-spacer', $html);
        $this->assertStringContainsString('height: 30px', $html);
    }

    // =========================================================================
    // Accessibility Tests
    // =========================================================================

    public function testFieldHasLabelAssociation(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'test_field',
            'settings' => [
                'label' => 'Test Label',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('for="fffl_test_field"', $html);
        $this->assertStringContainsString('id="fffl_test_field"', $html);
    }

    public function testRequiredFieldHasAriaRequired(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'required_field',
            'settings' => [
                'label' => 'Required Field',
                'required' => true,
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('aria-required="true"', $html);
    }

    public function testFieldWithHelpTextHasAriaDescribedby(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'help_field',
            'settings' => [
                'label' => 'Field',
                'help_text' => 'Some help text',
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('aria-describedby', $html);
    }

    public function testFieldsetUsesLegend(): void
    {
        $field = [
            'type' => 'radio',
            'name' => 'radio_group',
            'settings' => [
                'label' => 'Radio Group',
                'options' => [['value' => 'a', 'label' => 'A']],
            ],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('<fieldset', $html);
        $this->assertStringContainsString('<legend', $html);
    }

    public function testProgressBarHasAriaAttributes(): void
    {
        $schema = [
            'steps' => [
                ['id' => 'step_1', 'title' => 'Step 1', 'fields' => []],
            ],
        ];

        $html = $this->renderer->render($schema);

        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('aria-valuenow', $html);
        $this->assertStringContainsString('aria-valuemin', $html);
        $this->assertStringContainsString('aria-valuemax', $html);
    }

    // =========================================================================
    // Conditional Visibility Tests
    // =========================================================================

    public function testFieldCanBeHidden(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'hidden_field',
            'settings' => ['label' => 'Hidden'],
        ];

        $visibility = [
            'hidden_fields' => ['hidden_field'],
        ];

        $html = $this->renderer->render_field($field, [], $visibility);

        $this->assertStringContainsString('ff-conditional-hidden', $html);
        $this->assertStringContainsString('display: none', $html);
    }

    public function testFieldCanBeDisabled(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'disabled_field',
            'settings' => ['label' => 'Disabled'],
        ];

        $visibility = [
            'disabled_fields' => ['disabled_field'],
        ];

        $html = $this->renderer->render_field($field, [], $visibility);

        $this->assertStringContainsString('disabled', $html);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testRenderFieldWithEmptySettings(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'simple_field',
            'settings' => [],
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('name="simple_field"', $html);
    }

    public function testRenderFieldWithMissingSettings(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'no_settings',
        ];

        $html = $this->renderer->render_field($field);

        $this->assertStringContainsString('type="text"', $html);
    }

    public function testRenderUnknownFieldType(): void
    {
        $field = [
            'type' => 'unknown_custom_type',
            'name' => 'custom',
            'settings' => [],
        ];

        // Should not throw an error
        $html = $this->renderer->render_field($field);

        $this->assertIsString($html);
    }

    public function testRenderFieldPreservesFormData(): void
    {
        $field = [
            'type' => 'text',
            'name' => 'preserved',
            'settings' => ['label' => 'Preserved'],
        ];

        $form_data = ['preserved' => 'Existing Value'];

        $html = $this->renderer->render_field($field, $form_data);

        $this->assertStringContainsString('value="Existing Value"', $html);
    }
}
