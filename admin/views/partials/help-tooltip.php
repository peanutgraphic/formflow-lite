<?php
/**
 * Help Tooltip Partial
 *
 * Renders a help icon with tooltip on hover.
 *
 * @package FormFlow
 *
 * Usage:
 * <?php fffl_help_tooltip('Your helpful text here'); ?>
 * <?php fffl_help_tooltip('Text', 'right'); // Position: 'top' (default) or 'right' ?>
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a help tooltip icon
 *
 * @param string $text     The tooltip text to display
 * @param string $position Position of tooltip: 'top' (default) or 'right'
 * @param bool   $echo     Whether to echo or return the output
 * @return string|void
 */
function fffl_help_tooltip(string $text, string $position = 'top', bool $echo = true) {
    $class = 'ff-help-tip';
    if ($position === 'right') {
        $class .= ' ff-tooltip-right';
    }

    $html = sprintf(
        '<span class="%s">
            <span class="dashicons dashicons-editor-help"></span>
            <span class="ff-tooltip">%s</span>
        </span>',
        esc_attr($class),
        esc_html($text)
    );

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Render a label with help tooltip
 *
 * @param string $for      The ID of the form field
 * @param string $label    The label text
 * @param string $tooltip  The tooltip text
 * @param bool   $required Whether the field is required
 * @param bool   $echo     Whether to echo or return
 * @return string|void
 */
function fffl_label_with_help(string $for, string $label, string $tooltip, bool $required = false, bool $echo = true) {
    $required_html = $required ? ' <span class="required">*</span>' : '';

    $html = sprintf(
        '<div class="ff-label-with-help">
            <label for="%s">%s%s</label>
            %s
        </div>',
        esc_attr($for),
        esc_html($label),
        $required_html,
        fffl_help_tooltip($tooltip, 'top', false)
    );

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
