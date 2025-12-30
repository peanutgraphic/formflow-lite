<?php
/**
 * Features Settings Partial
 *
 * Per-instance feature toggles and configuration.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current feature settings
$features = \FFFL\FeatureManager::get_features($instance ?? []);
$available_features = \FFFL\FeatureManager::get_available_features();
$features_by_category = \FFFL\FeatureManager::get_features_by_category();
?>

<div class="ff-card ff-features-card">
    <h2><?php esc_html_e('Features', 'formflow-lite'); ?></h2>
    <p class="description"><?php esc_html_e('Enable or disable features for this form instance. Click "Configure" to adjust feature-specific settings.', 'formflow-lite'); ?></p>

    <?php foreach ($features_by_category as $category_key => $category) : ?>
        <div class="ff-feature-category">
            <h3><?php echo esc_html($category['label']); ?></h3>

            <div class="ff-feature-list">
                <?php foreach ($category['features'] as $feature_key => $feature_meta) : ?>
                    <?php
                    $feature_settings = $features[$feature_key] ?? [];
                    $is_enabled = !empty($feature_settings['enabled']);
                    $requires_config = !empty($feature_meta['requires_config']);
                    ?>
                    <div class="ff-feature-item <?php echo $is_enabled ? 'ff-feature-enabled' : ''; ?>">
                        <div class="ff-feature-toggle">
                            <label class="ff-toggle">
                                <input type="checkbox"
                                       name="settings[features][<?php echo esc_attr($feature_key); ?>][enabled]"
                                       value="1"
                                       <?php checked($is_enabled); ?>
                                       class="ff-feature-checkbox"
                                       data-feature="<?php echo esc_attr($feature_key); ?>">
                                <span class="ff-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="ff-feature-info">
                            <div class="ff-feature-name">
                                <span class="dashicons dashicons-<?php echo esc_attr($feature_meta['icon']); ?>"></span>
                                <?php echo esc_html($feature_meta['name']); ?>
                            </div>
                            <div class="ff-feature-description">
                                <?php echo esc_html($feature_meta['description']); ?>
                            </div>
                        </div>

                        <div class="ff-feature-actions">
                            <button type="button" class="button ff-configure-feature"
                                    data-feature="<?php echo esc_attr($feature_key); ?>"
                                    <?php echo !$is_enabled ? 'disabled' : ''; ?>>
                                <?php esc_html_e('Configure', 'formflow-lite'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Configuration Panel (hidden by default) -->
                    <div class="ff-feature-config" id="ff-config-<?php echo esc_attr($feature_key); ?>" style="display: none;">
                        <?php include __DIR__ . "/feature-config-{$feature_key}.php"; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
