<?php
/**
 * Data Tab: Submissions
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
    <input type="hidden" name="tab" value="submissions">

    <select name="instance_id">
        <option value=""><?php esc_html_e('All Forms', 'formflow-lite'); ?></option>
        <?php foreach ($instances as $inst) : ?>
            <option value="<?php echo esc_attr($inst['id']); ?>"
                    <?php selected($filters['instance_id'], $inst['id']); ?>>
                <?php echo esc_html($inst['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="status">
        <option value=""><?php esc_html_e('All Statuses', 'formflow-lite'); ?></option>
        <option value="completed" <?php selected($filters['status'], 'completed'); ?>>
            <?php esc_html_e('Completed', 'formflow-lite'); ?>
        </option>
        <option value="in_progress" <?php selected($filters['status'], 'in_progress'); ?>>
            <?php esc_html_e('In Progress', 'formflow-lite'); ?>
        </option>
        <option value="failed" <?php selected($filters['status'], 'failed'); ?>>
            <?php esc_html_e('Failed', 'formflow-lite'); ?>
        </option>
        <option value="abandoned" <?php selected($filters['status'], 'abandoned'); ?>>
            <?php esc_html_e('Abandoned', 'formflow-lite'); ?>
        </option>
    </select>

    <input type="text" name="search" placeholder="<?php esc_attr_e('Search...', 'formflow-lite'); ?>"
           value="<?php echo esc_attr($filters['search']); ?>">

    <button type="submit" class="button"><?php esc_html_e('Filter', 'formflow-lite'); ?></button>

    <?php if (!empty(array_filter($filters))) : ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=fffl-data&tab=submissions')); ?>" class="button">
            <?php esc_html_e('Clear', 'formflow-lite'); ?>
        </a>
    <?php endif; ?>
</form>

<!-- Bulk Actions & Export -->
<div class="ff-bulk-actions-bar">
    <div class="ff-bulk-left">
        <select id="ff-bulk-action" class="ff-bulk-select">
            <option value=""><?php esc_html_e('Bulk Actions', 'formflow-lite'); ?></option>
            <option value="mark_test"><?php esc_html_e('Mark as Test Data', 'formflow-lite'); ?></option>
            <option value="mark_production"><?php esc_html_e('Mark as Production Data', 'formflow-lite'); ?></option>
            <option value="delete"><?php esc_html_e('Delete', 'formflow-lite'); ?></option>
        </select>
        <button type="button" id="ff-apply-bulk" class="button">
            <?php esc_html_e('Apply', 'formflow-lite'); ?>
        </button>
        <span id="ff-bulk-count" class="ff-bulk-count" style="display: none;">
            (<span class="count">0</span> <?php esc_html_e('selected', 'formflow-lite'); ?>)
        </span>
    </div>
    <div class="ff-bulk-right">
        <button type="button" id="ff-export-csv" class="button">
            <span class="dashicons dashicons-download"></span>
            <?php esc_html_e('Export CSV', 'formflow-lite'); ?>
        </button>
    </div>
</div>

<!-- Submissions Table -->
<table class="wp-list-table widefat fixed striped" id="ff-submissions-table">
    <thead>
        <tr>
            <th class="column-cb check-column" style="width:30px;"><input type="checkbox" id="ff-select-all"></th>
            <th class="column-id" style="width:50px;"><?php esc_html_e('ID', 'formflow-lite'); ?></th>
            <th class="column-form"><?php esc_html_e('Form', 'formflow-lite'); ?></th>
            <th class="column-account"><?php esc_html_e('Account', 'formflow-lite'); ?></th>
            <th class="column-customer"><?php esc_html_e('Customer', 'formflow-lite'); ?></th>
            <th class="column-device"><?php esc_html_e('Device', 'formflow-lite'); ?></th>
            <th class="column-status"><?php esc_html_e('Status', 'formflow-lite'); ?></th>
            <th class="column-step" style="width:60px;"><?php esc_html_e('Step', 'formflow-lite'); ?></th>
            <th class="column-date"><?php esc_html_e('Date', 'formflow-lite'); ?></th>
            <th class="column-actions" style="width:80px;"><?php esc_html_e('Actions', 'formflow-lite'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($items)) : ?>
            <tr>
                <td colspan="10"><?php esc_html_e('No submissions found.', 'formflow-lite'); ?></td>
            </tr>
        <?php else : ?>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <td class="column-cb check-column"><input type="checkbox" class="ff-row-cb" value="<?php echo esc_attr($item['id']); ?>"></td>
                    <td class="column-id"><?php echo esc_html($item['id']); ?></td>
                    <td class="column-form"><?php echo esc_html($item['instance_name'] ?? 'Unknown'); ?></td>
                    <td class="column-account">
                        <?php if ($item['account_number']) : ?>
                            <code><?php echo esc_html(\FFFL\Encryption::mask($item['account_number'], 0, 4)); ?></code>
                        <?php else : ?>
                            <span class="ff-na">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-customer"><?php echo esc_html($item['customer_name'] ?: '—'); ?></td>
                    <td class="column-device">
                        <?php if ($item['device_type']) : ?>
                            <?php echo esc_html(ucfirst($item['device_type'])); ?>
                        <?php else : ?>
                            <span class="ff-na">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-status">
                        <span class="ff-status ff-status-<?php echo esc_attr($item['status']); ?>">
                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $item['status']))); ?>
                        </span>
                        <?php if (!empty($item['is_test'])) : ?>
                            <span class="ff-status ff-status-test"><?php esc_html_e('Test', 'formflow-lite'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="column-step"><?php echo esc_html($item['step']); ?>/5</td>
                    <td class="column-date">
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']))); ?>
                    </td>
                    <td class="column-actions">
                        <button type="button" class="button button-small ff-view-submission"
                                data-id="<?php echo esc_attr($item['id']); ?>"
                                title="<?php esc_attr_e('View Details', 'formflow-lite'); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
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
