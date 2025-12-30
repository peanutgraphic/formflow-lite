<?php
/**
 * Breadcrumb Navigation Partial
 *
 * Renders a breadcrumb navigation for admin pages.
 *
 * @package FormFlow
 *
 * Usage:
 * <?php fffl_breadcrumbs(['Dashboard' => 'ff-dashboard', 'Data' => 'ff-data']); ?>
 * <?php fffl_breadcrumbs(['Dashboard' => 'ff-dashboard'], 'Current Page'); ?>
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render breadcrumb navigation
 *
 * @param array  $items   Array of label => page_slug pairs
 * @param string $current Optional current page label (not linked)
 * @param bool   $echo    Whether to echo or return output
 * @return string|void
 */
function fffl_breadcrumbs(array $items, string $current = '', bool $echo = true) {
    $html = '<nav class="ff-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'formflow-lite') . '">';
    $html .= '<span class="dashicons dashicons-admin-home"></span>';

    $count = count($items);
    $i = 0;

    foreach ($items as $label => $page_slug) {
        $i++;
        $url = admin_url('admin.php?page=' . $page_slug);
        $html .= sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html($label)
        );

        if ($i < $count || !empty($current)) {
            $html .= '<span class="ff-breadcrumb-sep">/</span>';
        }
    }

    if (!empty($current)) {
        $html .= sprintf(
            '<span class="ff-breadcrumb-current" aria-current="page">%s</span>',
            esc_html($current)
        );
    }

    $html .= '</nav>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Get the page title with icon
 *
 * @param string $title    Page title
 * @param string $icon     Dashicon name (without 'dashicons-' prefix)
 * @param bool   $echo     Whether to echo or return
 * @return string|void
 */
function fffl_page_header(string $title, string $icon = '', bool $echo = true) {
    $icon_html = '';
    if (!empty($icon)) {
        $icon_html = sprintf(
            '<span class="dashicons dashicons-%s ff-page-icon"></span>',
            esc_attr($icon)
        );
    }

    $html = sprintf(
        '<h1 class="ff-page-title">%s%s</h1>',
        $icon_html,
        esc_html($title)
    );

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
