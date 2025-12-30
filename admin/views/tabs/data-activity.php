<?php
/**
 * Data Tab: Activity Logs
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Filters -->
<form method="get" class="ff-log-filters">
    <input type="hidden" name="page" value="fffl-data">
    <input type="hidden" name="tab" value="activity">

    <select name="instance_id">
        <option value=""><?php esc_html_e('All Forms', 'formflow-lite'); ?></option>
        <?php foreach ($instances as $inst) : ?>
            <option value="<?php echo esc_attr($inst['id']); ?>"
                    <?php selected($filters['instance_id'], $inst['id']); ?>>
                <?php echo esc_html($inst['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="type">
        <option value=""><?php esc_html_e('All Types', 'formflow-lite'); ?></option>
        <option value="info" <?php selected($filters['type'], 'info'); ?>>
            <?php esc_html_e('Info', 'formflow-lite'); ?>
        </option>
        <option value="warning" <?php selected($filters['type'], 'warning'); ?>>
            <?php esc_html_e('Warning', 'formflow-lite'); ?>
        </option>
        <option value="error" <?php selected($filters['type'], 'error'); ?>>
            <?php esc_html_e('Error', 'formflow-lite'); ?>
        </option>
        <option value="api_call" <?php selected($filters['type'], 'api_call'); ?>>
            <?php esc_html_e('API Call', 'formflow-lite'); ?>
        </option>
        <option value="security" <?php selected($filters['type'], 'security'); ?>>
            <?php esc_html_e('Security', 'formflow-lite'); ?>
        </option>
    </select>

    <input type="text" name="search" placeholder="<?php esc_attr_e('Search...', 'formflow-lite'); ?>"
           value="<?php echo esc_attr($filters['search']); ?>">

    <button type="submit" class="button"><?php esc_html_e('Filter', 'formflow-lite'); ?></button>

    <?php if (!empty(array_filter($filters))) : ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-data&tab=activity')); ?>" class="button">
            <?php esc_html_e('Clear', 'formflow-lite'); ?>
        </a>
    <?php endif; ?>
</form>

<!-- Bulk Actions -->
<div class="ff-bulk-actions-bar">
    <div class="ff-bulk-left">
        <select id="ff-bulk-action" class="ff-bulk-select">
            <option value=""><?php esc_html_e('Bulk Actions', 'formflow-lite'); ?></option>
            <option value="delete"><?php esc_html_e('Delete', 'formflow-lite'); ?></option>
        </select>
        <button type="button" id="ff-apply-bulk" class="button">
            <?php esc_html_e('Apply', 'formflow-lite'); ?>
        </button>
        <span id="ff-bulk-count" class="ff-bulk-count" style="display: none;">
            (<span class="count">0</span> <?php esc_html_e('selected', 'formflow-lite'); ?>)
        </span>
    </div>
</div>

<!-- Activity Logs Table -->
<table class="wp-list-table widefat fixed striped" id="fffl-logs-table">
    <thead>
        <tr>
            <th class="column-cb check-column" style="width:30px;"><input type="checkbox" id="ff-select-all"></th>
            <th class="column-id" style="width:60px;"><?php esc_html_e('ID', 'formflow-lite'); ?></th>
            <th class="column-type" style="width:100px;"><?php esc_html_e('Type', 'formflow-lite'); ?></th>
            <th class="column-form"><?php esc_html_e('Form', 'formflow-lite'); ?></th>
            <th class="column-message"><?php esc_html_e('Message', 'formflow-lite'); ?></th>
            <th class="column-date" style="width:160px;"><?php esc_html_e('Date', 'formflow-lite'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($items)) : ?>
            <tr>
                <td colspan="6"><?php esc_html_e('No activity logs found.', 'formflow-lite'); ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <td class="column-cb check-column"><input type="checkbox" class="ff-row-cb" value="<?php echo esc_attr($item['id']); ?>"></td>
                    <td class="column-id"><?php echo esc_html($item['id']); ?></td>
                    <td class="column-type">
                        <span class="ff-log-type ff-log-type-<?php echo esc_attr($item['log_type']); ?>">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $item['log_type']))); ?>
                        </span>
                    </td>
                    <td class="column-form"><?php echo esc_html($item['instance_name'] ?? '—'); ?></td>
                    <td class="column-message">
                        <?php echo esc_html($item['message']); ?>
                        <?php if (!empty($item['details'])) : ?>
                            <a href="#" class="ff-view-details" data-details="<?php echo esc_attr(json_encode($item['details'])); ?>">
                                <?php esc_html_e('[details]', 'formflow-lite'); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="column-date">
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']))); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">
                <?php printf(esc_html__('%d items', 'formflow-lite'), $total_items); ?>
            </span>
            <span class="pagination-links">
                <?php if ($page > 1) : ?>
                    <a class="first-page button" href="<?php echo esc_url(add_query_arg('paged', 1)); ?>">
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $page - 1)); ?>">
                        <span aria-hidden="true">‹</span>
                    </a>
                <?php endif; ?>

                <span class="paging-input">
                    <?php printf(esc_html__('%1$d of %2$d', 'formflow-lite'), $page, $total_pages); ?>
                </span>

                <?php if ($page < $total_pages) : ?>
                    <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $page + 1)); ?>">
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page button" href="<?php echo esc_url(add_query_arg('paged', $total_pages)); ?>">
                        <span aria-hidden="true">»</span>
                    </a>
                <?php endif; ?>
            </span>
        </div>
    </div>
<?php endif; ?>
