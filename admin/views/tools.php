<?php
/**
 * Combined Tools View - Lite Version
 *
 * Displays Settings and Diagnostics in a tabbed interface.
 *
 * @package FormFlow Lite
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include breadcrumbs partial
require_once FFFL_PLUGIN_DIR . 'admin/views/partials/breadcrumbs.php';

// Base URL for tabs
$base_url = admin_url('admin.php?page=fffl-tools');

// Default to settings tab for lite version
if (empty($tab) || !in_array($tab, ['settings', 'diagnostics'])) {
    $tab = 'settings';
}
?>

<div class="wrap ff-admin-wrap">
    <?php fffl_breadcrumbs(['Dashboard' => 'fffl-dashboard'], __('Tools & Settings', 'formflow-lite')); ?>

    <h1><?php esc_html_e('Tools & Settings', 'formflow-lite'); ?></h1>

    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg('tab', 'settings', $base_url)); ?>"
           class="nav-tab <?php echo ($tab === 'settings') ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php esc_html_e('Settings', 'formflow-lite'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'diagnostics', $base_url)); ?>"
           class="nav-tab <?php echo ($tab === 'diagnostics') ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-heart"></span>
            <?php esc_html_e('Diagnostics', 'formflow-lite'); ?>
        </a>
    </nav>

    <div class="ff-tab-content">
        <?php if ($tab === 'settings') : ?>
            <!-- Settings Tab -->
            <?php include FFFL_PLUGIN_DIR . 'admin/views/tabs/tools-settings.php'; ?>

        <?php elseif ($tab === 'diagnostics') : ?>
            <!-- Diagnostics Tab -->
            <?php include FFFL_PLUGIN_DIR . 'admin/views/tabs/tools-diagnostics.php'; ?>

        <?php endif; ?>
    </div>
</div>
