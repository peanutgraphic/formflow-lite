<?php
/**
 * Form Renderer
 *
 * Renders forms from schema to HTML.
 *
 * @package FormFlow
 * @since 2.6.0
 */

namespace FFFL\Builder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class FormRenderer
 *
 * Converts form schema to HTML output.
 */
class FormRenderer {

    /**
     * Render a complete form from schema
     */
    public function render(array $schema, array $instance = [], array $form_data = []): string {
        $steps = $schema['steps'] ?? [];
        $settings = $schema['settings'] ?? [];

        if (empty($steps)) {
            return '';
        }

        // Process conditional logic
        $conditional = new ConditionalLogic();
        $visibility = $conditional->process_schema($schema, $form_data);

        ob_start();
        ?>
        <div class="ff-form-container ff-builder-form" data-instance="<?php echo esc_attr($instance['id'] ?? 0); ?>">
            <?php $this->render_progress_bar($steps, 1); ?>

            <form class="ff-form" method="post" novalidate>
                <?php wp_nonce_field('fffl_form_submit', 'fffl_nonce'); ?>
                <input type="hidden" name="instance_id" value="<?php echo esc_attr($instance['id'] ?? 0); ?>">
                <input type="hidden" name="current_step" value="1">

                <?php foreach ($steps as $index => $step) : ?>
                    <?php
                    $step_id = $step['id'] ?? "step_{$index}";
                    $is_visible = in_array($step_id, $visibility['visible_steps']);
                    $is_first = $index === 0;
                    ?>
                    <div class="ff-step <?php echo esc_attr($is_first ? 'active' : ''); ?> <?php echo esc_attr(!$is_visible ? 'ff-conditional-hidden' : ''); ?>"
                         data-step="<?php echo intval($index + 1); ?>"
                         data-step-id="<?php echo esc_attr($step_id); ?>">

                        <?php if (!empty($step['title'])) : ?>
                            <h2 class="ff-step-title"><?php echo esc_html($step['title']); ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($step['description'])) : ?>
                            <p class="ff-step-description"><?php echo esc_html($step['description']); ?></p>
                        <?php endif; ?>

                        <div class="ff-step-fields">
                            <?php foreach ($step['fields'] ?? [] as $field) : ?>
                                <?php echo $this->render_field($field, $form_data, $visibility); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="ff-form-actions">
                    <button type="button" class="ff-btn ff-btn-secondary ff-btn-prev" style="display: none;">
                        <?php echo esc_html($settings['prev_button_text'] ?? __('Previous', 'formflow-lite')); ?>
                    </button>
                    <button type="button" class="ff-btn ff-btn-primary ff-btn-next">
                        <?php echo esc_html($settings['next_button_text'] ?? __('Next', 'formflow-lite')); ?>
                    </button>
                    <button type="submit" class="ff-btn ff-btn-primary ff-btn-submit" style="display: none;">
                        <?php echo esc_html($settings['submit_button_text'] ?? __('Submit', 'formflow-lite')); ?>
                    </button>
                </div>
            </form>
        </div>

        <?php
        // Add conditional logic script
        $script = $conditional->generate_client_script($schema);
        if ($script) {
            echo '<script>' . $script . '</script>';
        }

        return ob_get_clean();
    }

    /**
     * Render preview (for builder)
     */
    public function render_preview(array $schema): string {
        return $this->render($schema, [], []);
    }

    /**
     * Render progress bar
     */
    private function render_progress_bar(array $steps, int $current_step): void {
        $total_steps = count($steps);
        $progress_percent = (($current_step - 1) / max($total_steps - 1, 1)) * 100;
        ?>
        <div class="ff-progress-container">
            <div class="ff-progress-bar" role="progressbar" aria-valuenow="<?php echo intval($progress_percent); ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="ff-progress-fill" style="width: <?php echo intval($progress_percent); ?>%;"></div>
            </div>
            <div class="ff-progress-steps" role="navigation" aria-label="<?php esc_attr_e('Form progress', 'formflow-lite'); ?>">
                <?php foreach ($steps as $index => $step) : ?>
                    <?php
                    $step_num = $index + 1;
                    $is_active = $step_num === $current_step;
                    $is_completed = $step_num < $current_step;
                    ?>
                    <div class="ff-progress-step <?php echo esc_attr($is_active ? 'active' : ''); ?> <?php echo esc_attr($is_completed ? 'completed' : ''); ?>"
                         data-step="<?php echo intval($step_num); ?>"
                         aria-current="<?php echo esc_attr($is_active ? 'step' : 'false'); ?>">
                        <span class="ff-step-number"><?php echo intval($step_num); ?></span>
                        <span class="ff-step-label"><?php echo esc_html($step['title'] ?? "Step {$step_num}"); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single field
     */
    public function render_field(array $field, array $form_data = [], array $visibility = []): string {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['settings']['label'] ?? $field['label'] ?? '';
        $required = $field['settings']['required'] ?? $field['required'] ?? false;
        $value = $form_data[$name] ?? $field['settings']['default_value'] ?? '';
        $help_text = $field['settings']['help_text'] ?? '';
        $placeholder = $field['settings']['placeholder'] ?? '';

        // Check visibility
        $is_hidden = $name && in_array($name, $visibility['hidden_fields'] ?? []);
        $is_disabled = $name && in_array($name, $visibility['disabled_fields'] ?? []);

        // Dynamic required based on conditional logic
        if (in_array($name, $visibility['required_fields'] ?? [])) {
            $required = true;
        } elseif (in_array($name, $visibility['optional_fields'] ?? [])) {
            $required = false;
        }

        // Set value from conditional logic
        if (isset($visibility['field_values'][$name])) {
            $value = $visibility['field_values'][$name];
        }

        // Generate unique ID
        $id = 'fffl_' . ($name ?: uniqid('field_'));

        // Wrapper classes
        $wrapper_classes = ['ff-field-wrapper', "ff-field-type-{$type}"];
        if ($required) {
            $wrapper_classes[] = 'ff-required';
        }
        if ($is_hidden) {
            $wrapper_classes[] = 'ff-conditional-hidden';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"
             data-field="<?php echo esc_attr($name); ?>"
             <?php echo $is_hidden ? 'style="display: none;"' : ''; ?>>

            <?php
            // Render field based on type
            switch ($type) {
                case 'text':
                case 'email':
                case 'phone':
                case 'number':
                    $this->render_input_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'textarea':
                    $this->render_textarea_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'select':
                    $this->render_select_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'radio':
                    $this->render_radio_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'checkbox':
                    $this->render_checkbox_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'toggle':
                    $this->render_toggle_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'date':
                    $this->render_date_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'time':
                    $this->render_time_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'file':
                    $this->render_file_field($field, $id, $name, $is_disabled);
                    break;

                case 'signature':
                    $this->render_signature_field($field, $id, $name, $is_disabled);
                    break;

                case 'address':
                    $this->render_address_field($field, $id, $name, $form_data, $is_disabled);
                    break;

                case 'account_number':
                    $this->render_account_number_field($field, $id, $name, $value, $is_disabled);
                    break;

                case 'program_selector':
                    $this->render_program_selector($field, $id, $name, $value, $is_disabled);
                    break;

                case 'heading':
                    $this->render_heading($field);
                    break;

                case 'paragraph':
                    $this->render_paragraph($field);
                    break;

                case 'divider':
                    $this->render_divider($field);
                    break;

                case 'spacer':
                    $this->render_spacer($field);
                    break;

                case 'columns':
                    $this->render_columns($field, $form_data, $visibility);
                    break;

                case 'section':
                    $this->render_section($field, $form_data, $visibility);
                    break;

                default:
                    // Allow custom field type rendering
                    do_action("fffl_render_field_type_{$type}", $field, $id, $name, $value, $is_disabled);
                    break;
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render standard input field (text, email, phone, number)
     */
    private function render_input_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $type = $field['type'] ?? 'text';
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $placeholder = $settings['placeholder'] ?? '';
        $help_text = $settings['help_text'] ?? '';
        $max_length = $settings['max_length'] ?? '';
        $pattern = $settings['pattern'] ?? '';
        $min = $settings['min'] ?? '';
        $max = $settings['max'] ?? '';
        $step = $settings['step'] ?? '';

        // Map type for HTML input
        $input_type = $type;
        if ($type === 'phone') {
            $input_type = 'tel';
        }
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <input type="<?php echo esc_attr($input_type); ?>"
               id="<?php echo esc_attr($id); ?>"
               name="<?php echo esc_attr($name); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="ff-input"
               <?php echo $placeholder ? 'placeholder="' . esc_attr($placeholder) . '"' : ''; ?>
               <?php echo $required ? 'required aria-required="true"' : ''; ?>
               <?php echo $disabled ? 'disabled' : ''; ?>
               <?php echo $max_length ? 'maxlength="' . esc_attr($max_length) . '"' : ''; ?>
               <?php echo $pattern ? 'pattern="' . esc_attr($pattern) . '"' : ''; ?>
               <?php echo $type === 'number' && $min !== '' ? 'min="' . esc_attr($min) . '"' : ''; ?>
               <?php echo $type === 'number' && $max !== '' ? 'max="' . esc_attr($max) . '"' : ''; ?>
               <?php echo $type === 'number' && $step ? 'step="' . esc_attr($step) . '"' : ''; ?>
               <?php echo $help_text ? 'aria-describedby="' . esc_attr($id) . '_help"' : ''; ?>>

        <?php if ($help_text) : ?>
            <p id="<?php echo esc_attr($id); ?>_help" class="ff-help-text"><?php echo esc_html($help_text); ?></p>
        <?php endif; ?>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render textarea field
     */
    private function render_textarea_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $placeholder = $settings['placeholder'] ?? '';
        $help_text = $settings['help_text'] ?? '';
        $rows = $settings['rows'] ?? 4;
        $max_length = $settings['max_length'] ?? '';
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <textarea id="<?php echo esc_attr($id); ?>"
                  name="<?php echo esc_attr($name); ?>"
                  class="ff-textarea"
                  rows="<?php echo esc_attr($rows); ?>"
                  <?php echo $placeholder ? 'placeholder="' . esc_attr($placeholder) . '"' : ''; ?>
                  <?php echo $required ? 'required aria-required="true"' : ''; ?>
                  <?php echo $disabled ? 'disabled' : ''; ?>
                  <?php echo $max_length ? 'maxlength="' . esc_attr($max_length) . '"' : ''; ?>
                  <?php echo $help_text ? 'aria-describedby="' . esc_attr($id) . '_help"' : ''; ?>><?php echo esc_textarea($value); ?></textarea>

        <?php if ($help_text) : ?>
            <p id="<?php echo esc_attr($id); ?>_help" class="ff-help-text"><?php echo esc_html($help_text); ?></p>
        <?php endif; ?>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render select/dropdown field
     */
    private function render_select_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $placeholder = $settings['placeholder'] ?? __('Select an option', 'formflow-lite');
        $options = $settings['options'] ?? [];
        $searchable = $settings['searchable'] ?? false;
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <select id="<?php echo esc_attr($id); ?>"
                name="<?php echo esc_attr($name); ?>"
                class="ff-select <?php echo $searchable ? 'ff-searchable' : ''; ?>"
                <?php echo $required ? 'required aria-required="true"' : ''; ?>
                <?php echo $disabled ? 'disabled' : ''; ?>>
            <option value=""><?php echo esc_html($placeholder); ?></option>
            <?php foreach ($options as $option) : ?>
                <?php
                $opt_value = is_array($option) ? ($option['value'] ?? '') : $option;
                $opt_label = is_array($option) ? ($option['label'] ?? $opt_value) : $option;
                ?>
                <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($value, $opt_value); ?>>
                    <?php echo esc_html($opt_label); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render radio button group
     */
    private function render_radio_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $options = $settings['options'] ?? [];
        $layout = $settings['layout'] ?? 'vertical';
        ?>
        <fieldset class="ff-fieldset">
            <?php if ($label) : ?>
                <legend class="ff-legend">
                    <?php echo esc_html($label); ?>
                    <?php if ($required) : ?>
                        <span class="ff-required-indicator" aria-hidden="true">*</span>
                    <?php endif; ?>
                </legend>
            <?php endif; ?>

            <div class="ff-radio-group ff-layout-<?php echo esc_attr($layout); ?>">
                <?php foreach ($options as $index => $option) : ?>
                    <?php
                    $opt_value = is_array($option) ? ($option['value'] ?? '') : $option;
                    $opt_label = is_array($option) ? ($option['label'] ?? $opt_value) : $option;
                    $opt_id = $id . '_' . $index;
                    ?>
                    <label class="ff-radio-label" for="<?php echo esc_attr($opt_id); ?>">
                        <input type="radio"
                               id="<?php echo esc_attr($opt_id); ?>"
                               name="<?php echo esc_attr($name); ?>"
                               value="<?php echo esc_attr($opt_value); ?>"
                               class="ff-radio"
                               <?php checked($value, $opt_value); ?>
                               <?php echo $required && $index === 0 ? 'required' : ''; ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>>
                        <span class="ff-radio-text"><?php echo esc_html($opt_label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render checkbox group
     */
    private function render_checkbox_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $options = $settings['options'] ?? [];

        $selected = is_array($value) ? $value : [$value];
        ?>
        <fieldset class="ff-fieldset">
            <?php if ($label) : ?>
                <legend class="ff-legend">
                    <?php echo esc_html($label); ?>
                    <?php if ($required) : ?>
                        <span class="ff-required-indicator" aria-hidden="true">*</span>
                    <?php endif; ?>
                </legend>
            <?php endif; ?>

            <div class="ff-checkbox-group">
                <?php foreach ($options as $index => $option) : ?>
                    <?php
                    $opt_value = is_array($option) ? ($option['value'] ?? '') : $option;
                    $opt_label = is_array($option) ? ($option['label'] ?? $opt_value) : $option;
                    $opt_id = $id . '_' . $index;
                    ?>
                    <label class="ff-checkbox-label" for="<?php echo esc_attr($opt_id); ?>">
                        <input type="checkbox"
                               id="<?php echo esc_attr($opt_id); ?>"
                               name="<?php echo esc_attr($name); ?>[]"
                               value="<?php echo esc_attr($opt_value); ?>"
                               class="ff-checkbox"
                               <?php checked(in_array($opt_value, $selected)); ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>>
                        <span class="ff-checkbox-text"><?php echo esc_html($opt_label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render toggle switch
     */
    private function render_toggle_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $label = $settings['label'] ?? '';
        $on_label = $settings['on_label'] ?? __('Yes', 'formflow-lite');
        $off_label = $settings['off_label'] ?? __('No', 'formflow-lite');
        $is_on = $value === true || $value === '1' || $value === 'yes' || $value === 'on';
        ?>
        <div class="ff-toggle-wrapper">
            <?php if ($label) : ?>
                <span class="ff-label"><?php echo esc_html($label); ?></span>
            <?php endif; ?>

            <label class="ff-toggle" for="<?php echo esc_attr($id); ?>">
                <input type="checkbox"
                       id="<?php echo esc_attr($id); ?>"
                       name="<?php echo esc_attr($name); ?>"
                       value="1"
                       class="ff-toggle-input"
                       <?php checked($is_on); ?>
                       <?php echo $disabled ? 'disabled' : ''; ?>>
                <span class="ff-toggle-slider"></span>
                <span class="ff-toggle-labels">
                    <span class="ff-toggle-on"><?php echo esc_html($on_label); ?></span>
                    <span class="ff-toggle-off"><?php echo esc_html($off_label); ?></span>
                </span>
            </label>
        </div>
        <?php
    }

    /**
     * Render date picker
     */
    private function render_date_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $min_date = $settings['min_date'] ?? '';
        $max_date = $settings['max_date'] ?? '';

        // Handle "today" placeholder
        if ($min_date === 'today') {
            $min_date = date('Y-m-d');
        }
        if ($max_date === 'today') {
            $max_date = date('Y-m-d');
        }
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <input type="date"
               id="<?php echo esc_attr($id); ?>"
               name="<?php echo esc_attr($name); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="ff-input ff-date-input"
               <?php echo $required ? 'required aria-required="true"' : ''; ?>
               <?php echo $disabled ? 'disabled' : ''; ?>
               <?php echo $min_date ? 'min="' . esc_attr($min_date) . '"' : ''; ?>
               <?php echo $max_date ? 'max="' . esc_attr($max_date) . '"' : ''; ?>>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render time picker
     */
    private function render_time_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $min_time = $settings['min_time'] ?? '';
        $max_time = $settings['max_time'] ?? '';
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <input type="time"
               id="<?php echo esc_attr($id); ?>"
               name="<?php echo esc_attr($name); ?>"
               value="<?php echo esc_attr($value); ?>"
               class="ff-input ff-time-input"
               <?php echo $required ? 'required aria-required="true"' : ''; ?>
               <?php echo $disabled ? 'disabled' : ''; ?>
               <?php echo $min_time ? 'min="' . esc_attr($min_time) . '"' : ''; ?>
               <?php echo $max_time ? 'max="' . esc_attr($max_time) . '"' : ''; ?>>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render file upload field
     */
    private function render_file_field(array $field, string $id, string $name, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? false;
        $label = $settings['label'] ?? '';
        $allowed_types = $settings['allowed_types'] ?? 'jpg,jpeg,png,pdf';
        $max_size = $settings['max_size'] ?? 5;
        $multiple = $settings['multiple'] ?? false;

        $accept = implode(',', array_map(function($ext) {
            return '.' . trim($ext);
        }, explode(',', $allowed_types)));
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="ff-file-upload">
            <input type="file"
                   id="<?php echo esc_attr($id); ?>"
                   name="<?php echo esc_attr($name); ?><?php echo $multiple ? '[]' : ''; ?>"
                   class="ff-file-input"
                   accept="<?php echo esc_attr($accept); ?>"
                   <?php echo $required ? 'required aria-required="true"' : ''; ?>
                   <?php echo $disabled ? 'disabled' : ''; ?>
                   <?php echo $multiple ? 'multiple' : ''; ?>
                   data-max-size="<?php echo esc_attr($max_size); ?>">

            <div class="ff-file-dropzone">
                <span class="dashicons dashicons-upload"></span>
                <span class="ff-file-text"><?php esc_html_e('Drag files here or click to browse', 'formflow-lite'); ?></span>
                <span class="ff-file-types"><?php echo esc_html(sprintf(__('Allowed: %s (Max: %dMB)', 'formflow-lite'), strtoupper($allowed_types), $max_size)); ?></span>
            </div>

            <div class="ff-file-list"></div>
        </div>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render signature field
     */
    private function render_signature_field(array $field, string $id, string $name, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? true;
        $label = $settings['label'] ?? __('Signature', 'formflow-lite');
        $width = $settings['width'] ?? 400;
        $height = $settings['height'] ?? 150;
        ?>
        <?php if ($label) : ?>
            <label class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="ff-signature-wrapper">
            <canvas id="<?php echo esc_attr($id); ?>_canvas"
                    class="ff-signature-canvas"
                    width="<?php echo esc_attr($width); ?>"
                    height="<?php echo esc_attr($height); ?>"
                    <?php echo $disabled ? 'data-disabled="true"' : ''; ?>></canvas>
            <input type="hidden" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" <?php echo $required ? 'required' : ''; ?>>
            <button type="button" class="ff-btn ff-btn-link ff-signature-clear"><?php esc_html_e('Clear', 'formflow-lite'); ?></button>
        </div>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render smart address field with autocomplete
     */
    private function render_address_field(array $field, string $id, string $name, array $form_data, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? true;
        $label = $settings['label'] ?? __('Service Address', 'formflow-lite');
        $autocomplete = $settings['autocomplete'] ?? true;
        $validate_territory = $settings['validate_territory'] ?? true;
        $include_unit = $settings['include_unit'] ?? true;
        ?>
        <fieldset class="ff-fieldset ff-address-fieldset">
            <?php if ($label) : ?>
                <legend class="ff-legend">
                    <?php echo esc_html($label); ?>
                    <?php if ($required) : ?>
                        <span class="ff-required-indicator" aria-hidden="true">*</span>
                    <?php endif; ?>
                </legend>
            <?php endif; ?>

            <div class="ff-address-fields">
                <div class="ff-field-row">
                    <div class="ff-field-col <?php echo $include_unit ? 'ff-col-8' : 'ff-col-12'; ?>">
                        <label for="<?php echo esc_attr($id); ?>_street" class="ff-label ff-label-sm"><?php esc_html_e('Street Address', 'formflow-lite'); ?></label>
                        <input type="text"
                               id="<?php echo esc_attr($id); ?>_street"
                               name="<?php echo esc_attr($name); ?>[street]"
                               value="<?php echo esc_attr($form_data[$name]['street'] ?? ''); ?>"
                               class="ff-input ff-address-street <?php echo $autocomplete ? 'ff-address-autocomplete' : ''; ?>"
                               placeholder="<?php esc_attr_e('123 Main Street', 'formflow-lite'); ?>"
                               <?php echo $required ? 'required aria-required="true"' : ''; ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>
                               autocomplete="street-address"
                               data-validate-territory="<?php echo $validate_territory ? 'true' : 'false'; ?>">
                    </div>

                    <?php if ($include_unit) : ?>
                    <div class="ff-field-col ff-col-4">
                        <label for="<?php echo esc_attr($id); ?>_unit" class="ff-label ff-label-sm"><?php esc_html_e('Unit/Apt', 'formflow-lite'); ?></label>
                        <input type="text"
                               id="<?php echo esc_attr($id); ?>_unit"
                               name="<?php echo esc_attr($name); ?>[unit]"
                               value="<?php echo esc_attr($form_data[$name]['unit'] ?? ''); ?>"
                               class="ff-input"
                               placeholder="<?php esc_attr_e('Apt 4B', 'formflow-lite'); ?>"
                               <?php echo $disabled ? 'disabled' : ''; ?>
                               autocomplete="address-line2">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="ff-field-row">
                    <div class="ff-field-col ff-col-5">
                        <label for="<?php echo esc_attr($id); ?>_city" class="ff-label ff-label-sm"><?php esc_html_e('City', 'formflow-lite'); ?></label>
                        <input type="text"
                               id="<?php echo esc_attr($id); ?>_city"
                               name="<?php echo esc_attr($name); ?>[city]"
                               value="<?php echo esc_attr($form_data[$name]['city'] ?? ''); ?>"
                               class="ff-input ff-address-city"
                               <?php echo $required ? 'required' : ''; ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>
                               autocomplete="address-level2">
                    </div>
                    <div class="ff-field-col ff-col-3">
                        <label for="<?php echo esc_attr($id); ?>_state" class="ff-label ff-label-sm"><?php esc_html_e('State', 'formflow-lite'); ?></label>
                        <select id="<?php echo esc_attr($id); ?>_state"
                                name="<?php echo esc_attr($name); ?>[state]"
                                class="ff-select ff-address-state"
                                <?php echo $required ? 'required' : ''; ?>
                                <?php echo $disabled ? 'disabled' : ''; ?>
                                autocomplete="address-level1">
                            <option value=""><?php esc_html_e('Select', 'formflow-lite'); ?></option>
                            <?php echo $this->get_us_states_options($form_data[$name]['state'] ?? ''); ?>
                        </select>
                    </div>
                    <div class="ff-field-col ff-col-4">
                        <label for="<?php echo esc_attr($id); ?>_zip" class="ff-label ff-label-sm"><?php esc_html_e('ZIP Code', 'formflow-lite'); ?></label>
                        <input type="text"
                               id="<?php echo esc_attr($id); ?>_zip"
                               name="<?php echo esc_attr($name); ?>[zip]"
                               value="<?php echo esc_attr($form_data[$name]['zip'] ?? ''); ?>"
                               class="ff-input ff-address-zip"
                               pattern="[0-9]{5}(-[0-9]{4})?"
                               <?php echo $required ? 'required' : ''; ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>
                               autocomplete="postal-code">
                    </div>
                </div>

                <!-- Hidden fields for geocoding data -->
                <input type="hidden" name="<?php echo esc_attr($name); ?>[lat]" class="ff-address-lat" value="<?php echo esc_attr($form_data[$name]['lat'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($name); ?>[lng]" class="ff-address-lng" value="<?php echo esc_attr($form_data[$name]['lng'] ?? ''); ?>">
                <input type="hidden" name="<?php echo esc_attr($name); ?>[place_id]" class="ff-address-place-id" value="<?php echo esc_attr($form_data[$name]['place_id'] ?? ''); ?>">
            </div>

            <div class="ff-territory-status" style="display: none;">
                <span class="ff-territory-checking"><?php esc_html_e('Checking service area...', 'formflow-lite'); ?></span>
                <span class="ff-territory-valid" style="display: none; color: #28a745;">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Address is in service area', 'formflow-lite'); ?>
                </span>
                <span class="ff-territory-invalid" style="display: none; color: #dc3545;">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e('Address is outside service area', 'formflow-lite'); ?>
                </span>
            </div>
        </fieldset>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Get US states as select options
     */
    private function get_us_states_options(string $selected = ''): string {
        $states = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii',
            'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
            'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine',
            'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
            'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
            'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
            'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
            'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
            'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
            'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
            'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        ];

        $html = '';
        foreach ($states as $code => $name) {
            $sel = $selected === $code ? ' selected' : '';
            $html .= '<option value="' . esc_attr($code) . '"' . $sel . '>' . esc_html($name) . '</option>';
        }

        return $html;
    }

    /**
     * Render account number field with API validation
     */
    private function render_account_number_field(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? true;
        $label = $settings['label'] ?? __('Account Number', 'formflow-lite');
        $help_text = $settings['help_text'] ?? __('Find this on your utility bill', 'formflow-lite');
        $validate_api = $settings['validate_api'] ?? true;
        $mask = $settings['mask'] ?? '';
        ?>
        <?php if ($label) : ?>
            <label for="<?php echo esc_attr($id); ?>" class="ff-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="ff-required-indicator" aria-hidden="true">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="ff-account-input-wrapper">
            <input type="text"
                   id="<?php echo esc_attr($id); ?>"
                   name="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($value); ?>"
                   class="ff-input ff-account-input"
                   <?php echo $required ? 'required aria-required="true"' : ''; ?>
                   <?php echo $disabled ? 'disabled' : ''; ?>
                   <?php echo $mask ? 'data-mask="' . esc_attr($mask) . '"' : ''; ?>
                   data-validate-api="<?php echo $validate_api ? 'true' : 'false'; ?>"
                   autocomplete="off">

            <span class="ff-account-status">
                <span class="ff-status-checking" style="display: none;">
                    <span class="ff-spinner"></span>
                </span>
                <span class="ff-status-valid" style="display: none;">
                    <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                </span>
                <span class="ff-status-invalid" style="display: none;">
                    <span class="dashicons dashicons-warning" style="color: #dc3545;"></span>
                </span>
            </span>
        </div>

        <?php if ($help_text) : ?>
            <p class="ff-help-text"><?php echo esc_html($help_text); ?></p>
        <?php endif; ?>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render program selector for multi-program enrollment
     */
    private function render_program_selector(array $field, string $id, string $name, $value, bool $disabled): void {
        $settings = $field['settings'] ?? [];
        $required = $settings['required'] ?? true;
        $label = $settings['label'] ?? __('Select Programs', 'formflow-lite');
        $allow_multiple = $settings['allow_multiple'] ?? true;
        $show_descriptions = $settings['show_descriptions'] ?? true;
        $show_incentives = $settings['show_incentives'] ?? true;

        // Get available programs (would normally come from database)
        $programs = apply_filters('fffl_available_programs', [
            [
                'id' => 'smart_thermostat',
                'name' => __('Smart Thermostat Program', 'formflow-lite'),
                'description' => __('Earn rewards by allowing brief AC adjustments during peak demand.', 'formflow-lite'),
                'incentive' => '$75 annual credit',
                'icon' => 'dashicons-superhero',
            ],
            [
                'id' => 'peak_time_rebates',
                'name' => __('Peak Time Rebates', 'formflow-lite'),
                'description' => __('Reduce energy during peak events and earn bill credits.', 'formflow-lite'),
                'incentive' => 'Up to $2/kWh saved',
                'icon' => 'dashicons-clock',
            ],
            [
                'id' => 'ev_charging',
                'name' => __('EV Managed Charging', 'formflow-lite'),
                'description' => __('Optimize your EV charging to save money and support the grid.', 'formflow-lite'),
                'incentive' => '$50 monthly credit',
                'icon' => 'dashicons-car',
            ],
        ]);

        $selected = is_array($value) ? $value : [$value];
        ?>
        <fieldset class="ff-fieldset ff-program-selector">
            <?php if ($label) : ?>
                <legend class="ff-legend">
                    <?php echo esc_html($label); ?>
                    <?php if ($required) : ?>
                        <span class="ff-required-indicator" aria-hidden="true">*</span>
                    <?php endif; ?>
                </legend>
            <?php endif; ?>

            <div class="ff-program-grid">
                <?php foreach ($programs as $program) : ?>
                    <?php $prog_id = $id . '_' . $program['id']; ?>
                    <label class="ff-program-card <?php echo in_array($program['id'], $selected) ? 'selected' : ''; ?>"
                           for="<?php echo esc_attr($prog_id); ?>">
                        <input type="<?php echo $allow_multiple ? 'checkbox' : 'radio'; ?>"
                               id="<?php echo esc_attr($prog_id); ?>"
                               name="<?php echo esc_attr($name); ?><?php echo $allow_multiple ? '[]' : ''; ?>"
                               value="<?php echo esc_attr($program['id']); ?>"
                               class="ff-program-input"
                               <?php checked(in_array($program['id'], $selected)); ?>
                               <?php echo $disabled ? 'disabled' : ''; ?>>

                        <div class="ff-program-card-content">
                            <?php if (!empty($program['icon'])) : ?>
                                <span class="ff-program-icon dashicons <?php echo esc_attr($program['icon']); ?>"></span>
                            <?php endif; ?>

                            <span class="ff-program-name"><?php echo esc_html($program['name']); ?></span>

                            <?php if ($show_descriptions && !empty($program['description'])) : ?>
                                <span class="ff-program-description"><?php echo esc_html($program['description']); ?></span>
                            <?php endif; ?>

                            <?php if ($show_incentives && !empty($program['incentive'])) : ?>
                                <span class="ff-program-incentive">
                                    <span class="dashicons dashicons-awards"></span>
                                    <?php echo esc_html($program['incentive']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <span class="ff-program-checkmark">
                            <span class="dashicons dashicons-yes"></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="ff-field-error" role="alert"></div>
        <?php
    }

    /**
     * Render heading element
     */
    private function render_heading(array $field): void {
        $settings = $field['settings'] ?? [];
        $text = $settings['text'] ?? '';
        $level = $settings['level'] ?? 'h3';
        $alignment = $settings['alignment'] ?? 'left';

        if (empty($text)) {
            return;
        }

        $allowed_levels = ['h2', 'h3', 'h4', 'h5', 'h6'];
        $level = in_array($level, $allowed_levels) ? $level : 'h3';

        printf(
            '<%1$s class="ff-heading" style="text-align: %2$s;">%3$s</%1$s>',
            $level,
            esc_attr($alignment),
            esc_html($text)
        );
    }

    /**
     * Render paragraph element
     */
    private function render_paragraph(array $field): void {
        $settings = $field['settings'] ?? [];
        $content = $settings['content'] ?? '';

        if (empty($content)) {
            return;
        }

        echo '<div class="ff-paragraph">' . wp_kses_post($content) . '</div>';
    }

    /**
     * Render divider element
     */
    private function render_divider(array $field): void {
        $settings = $field['settings'] ?? [];
        $style = $settings['style'] ?? 'solid';
        $spacing = $settings['spacing'] ?? 'medium';

        $spacing_map = ['small' => '10px', 'medium' => '20px', 'large' => '40px'];
        $margin = $spacing_map[$spacing] ?? '20px';

        echo '<hr class="ff-divider" style="border-style: ' . esc_attr($style) . '; margin: ' . esc_attr($margin) . ' 0;">';
    }

    /**
     * Render spacer element
     */
    private function render_spacer(array $field): void {
        $settings = $field['settings'] ?? [];
        $height = intval($settings['height'] ?? 20);

        echo '<div class="ff-spacer" style="height: ' . esc_attr($height) . 'px;"></div>';
    }

    /**
     * Render columns container
     */
    private function render_columns(array $field, array $form_data, array $visibility): void {
        $settings = $field['settings'] ?? [];
        $column_count = intval($settings['column_count'] ?? 2);
        $gap = $settings['gap'] ?? 'medium';
        $children = $field['children'] ?? [];

        $gap_map = ['small' => '10px', 'medium' => '20px', 'large' => '30px'];
        $gap_value = $gap_map[$gap] ?? '20px';

        echo '<div class="ff-columns" style="display: grid; grid-template-columns: repeat(' . $column_count . ', 1fr); gap: ' . esc_attr($gap_value) . ';">';

        foreach ($children as $child) {
            echo '<div class="ff-column">';
            echo $this->render_field($child, $form_data, $visibility);
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render section container
     */
    private function render_section(array $field, array $form_data, array $visibility): void {
        $settings = $field['settings'] ?? [];
        $title = $settings['title'] ?? '';
        $collapsible = $settings['collapsible'] ?? false;
        $collapsed_default = $settings['collapsed_default'] ?? false;
        $children = $field['children'] ?? [];

        $section_classes = ['ff-section'];
        if ($collapsible) {
            $section_classes[] = 'ff-collapsible';
        }
        if ($collapsed_default) {
            $section_classes[] = 'ff-collapsed';
        }
        ?>
        <div class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
            <?php if ($title) : ?>
                <div class="ff-section-header" <?php echo $collapsible ? 'role="button" tabindex="0"' : ''; ?>>
                    <h4 class="ff-section-title"><?php echo esc_html($title); ?></h4>
                    <?php if ($collapsible) : ?>
                        <span class="ff-section-toggle dashicons dashicons-arrow-down-alt2"></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="ff-section-content">
                <?php foreach ($children as $child) : ?>
                    <?php echo $this->render_field($child, $form_data, $visibility); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
