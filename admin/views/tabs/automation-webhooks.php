<?php
/**
 * Automation Tab: Webhooks
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="fffl-webhooks-intro">
    <p><?php esc_html_e('Webhooks allow you to receive real-time notifications when enrollment events occur. Configure endpoints to integrate with CRM systems, email services, or custom applications.', 'formflow-lite'); ?></p>
    <button type="button" class="button button-primary" id="ff-add-webhook">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add New Webhook', 'formflow-lite'); ?>
    </button>
</div>

<!-- Webhook List -->
<div class="ff-card">
    <div class="ff-card-header">
        <h2><?php esc_html_e('Configured Webhooks', 'formflow-lite'); ?></h2>
        <div class="ff-card-actions">
            <select id="ff-webhook-filter-instance">
                <option value=""><?php esc_html_e('All Form Instances', 'formflow-lite'); ?></option>
                <?php foreach ($instances as $inst): ?>
                    <option value="<?php echo esc_attr($inst['id']); ?>">
                        <?php echo esc_html($inst['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped fffl-webhooks-table">
        <thead>
            <tr>
                <th class="column-status" style="width: 60px;"><?php esc_html_e('Status', 'formflow-lite'); ?></th>
                <th class="column-name"><?php esc_html_e('Name', 'formflow-lite'); ?></th>
                <th class="column-url"><?php esc_html_e('URL', 'formflow-lite'); ?></th>
                <th class="column-instance"><?php esc_html_e('Instance', 'formflow-lite'); ?></th>
                <th class="column-events"><?php esc_html_e('Events', 'formflow-lite'); ?></th>
                <th class="column-stats" style="width: 120px;"><?php esc_html_e('Stats', 'formflow-lite'); ?></th>
                <th class="column-actions" style="width: 120px;"><?php esc_html_e('Actions', 'formflow-lite'); ?></th>
            </tr>
        </thead>
        <tbody id="fffl-webhooks-list">
            <?php if (empty($webhooks)): ?>
                <tr class="ff-no-webhooks">
                    <td colspan="7">
                        <div class="ff-empty-state">
                            <span class="dashicons dashicons-rest-api"></span>
                            <p><?php esc_html_e('No webhooks configured yet.', 'formflow-lite'); ?></p>
                            <button type="button" class="button button-primary" id="ff-add-webhook-empty">
                                <?php esc_html_e('Add Your First Webhook', 'formflow-lite'); ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($webhooks as $webhook): ?>
                    <tr data-webhook-id="<?php echo esc_attr($webhook['id']); ?>">
                        <td class="column-status">
                            <span class="ff-status-indicator ff-status-<?php echo $webhook['is_active'] ? 'active' : 'inactive'; ?>"></span>
                        </td>
                        <td class="column-name">
                            <strong><?php echo esc_html($webhook['name']); ?></strong>
                            <?php if (!empty($webhook['secret'])): ?>
                                <span class="dashicons dashicons-lock" title="<?php esc_attr_e('Signature enabled', 'formflow-lite'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-url">
                            <code><?php echo esc_html($webhook['url']); ?></code>
                        </td>
                        <td class="column-instance">
                            <?php
                            if ($webhook['instance_id']) {
                                $inst_name = 'Unknown';
                                foreach ($instances as $inst) {
                                    if ($inst['id'] == $webhook['instance_id']) {
                                        $inst_name = $inst['name'];
                                        break;
                                    }
                                }
                                echo esc_html($inst_name);
                            } else {
                                echo '<em>' . esc_html__('All instances', 'formflow-lite') . '</em>';
                            }
                            ?>
                        </td>
                        <td class="column-events">
                            <?php
                            $event_labels = \FFFL\WebhookHandler::get_available_events();
                            $events = $webhook['events'] ?? [];
                            $event_names = [];
                            foreach ($events as $event) {
                                $event_names[] = $event_labels[$event] ?? $event;
                            }
                            echo esc_html(implode(', ', $event_names));
                            ?>
                        </td>
                        <td class="column-stats">
                            <span class="ff-webhook-stat" title="<?php esc_attr_e('Successful deliveries', 'formflow-lite'); ?>">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html($webhook['success_count'] ?? 0); ?>
                            </span>
                            <span class="ff-webhook-stat ff-stat-failures" title="<?php esc_attr_e('Failed deliveries', 'formflow-lite'); ?>">
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php echo esc_html($webhook['failure_count'] ?? 0); ?>
                            </span>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small fffl-test-webhook" data-id="<?php echo esc_attr($webhook['id']); ?>" title="<?php esc_attr_e('Test webhook', 'formflow-lite'); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="button button-small ff-edit-webhook" data-id="<?php echo esc_attr($webhook['id']); ?>" title="<?php esc_attr_e('Edit', 'formflow-lite'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small ff-delete-webhook" data-id="<?php echo esc_attr($webhook['id']); ?>" title="<?php esc_attr_e('Delete', 'formflow-lite'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Event Documentation -->
<div class="ff-card ff-webhook-docs">
    <div class="ff-card-header">
        <h2><?php esc_html_e('Available Events', 'formflow-lite'); ?></h2>
    </div>
    <div class="ff-card-body">
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 200px;"><?php esc_html_e('Event', 'formflow-lite'); ?></th>
                    <th><?php esc_html_e('Description', 'formflow-lite'); ?></th>
                    <th style="width: 300px;"><?php esc_html_e('Payload Fields', 'formflow-lite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>enrollment.completed</code></td>
                    <td><?php esc_html_e('Fired when an enrollment is successfully submitted to the API.', 'formflow-lite'); ?></td>
                    <td><code>submission_id, account_number, customer_name, device_type, confirmation_number</code></td>
                </tr>
                <tr>
                    <td><code>enrollment.failed</code></td>
                    <td><?php esc_html_e('Fired when an enrollment fails after all retries are exhausted.', 'formflow-lite'); ?></td>
                    <td><code>submission_id, account_number, customer_name, error, retry_count</code></td>
                </tr>
                <tr>
                    <td><code>appointment.scheduled</code></td>
                    <td><?php esc_html_e('Fired when an installation appointment is successfully booked.', 'formflow-lite'); ?></td>
                    <td><code>submission_id, account_number, customer_name, schedule_date, schedule_time</code></td>
                </tr>
                <tr>
                    <td><code>account.validated</code></td>
                    <td><?php esc_html_e('Fired when a customer account is validated against the API.', 'formflow-lite'); ?></td>
                    <td><code>account_number, customer_name, premise_address, is_valid</code></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Webhook Modal -->
<div id="ff-webhook-modal" class="ff-modal" style="display: none;">
    <div class="ff-modal-content">
        <div class="ff-modal-header">
            <h2 id="ff-webhook-modal-title"><?php esc_html_e('Add Webhook', 'formflow-lite'); ?></h2>
            <button type="button" class="ff-modal-close">&times;</button>
        </div>
        <form id="ff-webhook-form">
            <input type="hidden" name="webhook_id" id="ff-webhook-id" value="">

            <div class="ff-modal-body">
                <div class="ff-form-row">
                    <label for="ff-webhook-name"><?php esc_html_e('Name', 'formflow-lite'); ?> <span class="required">*</span></label>
                    <input type="text" id="ff-webhook-name" name="name" required placeholder="<?php esc_attr_e('e.g., CRM Integration', 'formflow-lite'); ?>">
                </div>

                <div class="ff-form-row">
                    <label for="ff-webhook-url"><?php esc_html_e('Endpoint URL', 'formflow-lite'); ?> <span class="required">*</span></label>
                    <input type="url" id="ff-webhook-url" name="url" required placeholder="https://example.com/webhook">
                    <p class="description"><?php esc_html_e('The URL where webhook payloads will be sent.', 'formflow-lite'); ?></p>
                </div>

                <div class="ff-form-row">
                    <label for="ff-webhook-instance"><?php esc_html_e('Form Instance', 'formflow-lite'); ?></label>
                    <select id="ff-webhook-instance" name="instance_id">
                        <option value=""><?php esc_html_e('All instances (global)', 'formflow-lite'); ?></option>
                        <?php foreach ($instances as $inst): ?>
                            <option value="<?php echo esc_attr($inst['id']); ?>">
                                <?php echo esc_html($inst['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ff-form-row">
                    <label><?php esc_html_e('Events', 'formflow-lite'); ?> <span class="required">*</span></label>
                    <div class="ff-checkbox-group">
                        <?php foreach (\FFFL\WebhookHandler::get_available_events() as $event => $label): ?>
                            <label class="ff-checkbox-label">
                                <input type="checkbox" name="events[]" value="<?php echo esc_attr($event); ?>">
                                <?php echo esc_html($label); ?>
                                <code><?php echo esc_html($event); ?></code>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="ff-form-row">
                    <label for="ff-webhook-secret"><?php esc_html_e('Secret Key', 'formflow-lite'); ?></label>
                    <div class="ff-input-group">
                        <input type="text" id="ff-webhook-secret" name="secret" placeholder="<?php esc_attr_e('Optional signing secret', 'formflow-lite'); ?>">
                        <button type="button" class="button" id="ff-generate-secret"><?php esc_html_e('Generate', 'formflow-lite'); ?></button>
                    </div>
                </div>

                <div class="ff-form-row">
                    <label class="ff-checkbox-label">
                        <input type="checkbox" name="is_active" id="ff-webhook-active" value="1" checked>
                        <?php esc_html_e('Active', 'formflow-lite'); ?>
                    </label>
                </div>
            </div>

            <div class="ff-modal-footer">
                <button type="button" class="button ff-modal-cancel"><?php esc_html_e('Cancel', 'formflow-lite'); ?></button>
                <button type="submit" class="button button-primary" id="ff-webhook-save"><?php esc_html_e('Save Webhook', 'formflow-lite'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var $modal = $('#ff-webhook-modal');
    var $form = $('#ff-webhook-form');

    $('#ff-add-webhook, #ff-add-webhook-empty').on('click', function() {
        openModal();
    });

    $(document).on('click', '.ff-edit-webhook', function() {
        loadWebhook($(this).data('id'));
    });

    $(document).on('click', '.ff-delete-webhook', function() {
        if (confirm('<?php echo esc_js(__('Are you sure you want to delete this webhook?', 'formflow-lite')); ?>')) {
            deleteWebhook($(this).data('id'));
        }
    });

    $(document).on('click', '.fffl-test-webhook', function() {
        var $btn = $(this);
        $btn.find('.dashicons').addClass('ff-spin');
        testWebhook($btn.data('id'), function() {
            $btn.find('.dashicons').removeClass('ff-spin');
        });
    });

    $('.ff-modal-close, .ff-modal-cancel').on('click', closeModal);
    $modal.on('click', function(e) { if (e.target === this) closeModal(); });

    $('#ff-generate-secret').on('click', function() {
        $('#ff-webhook-secret').val(generateSecret(32));
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        saveWebhook();
    });

    function openModal(webhook) {
        $form[0].reset();
        $('#ff-webhook-id').val('');
        $('#ff-webhook-modal-title').text('<?php echo esc_js(__('Add Webhook', 'formflow-lite')); ?>');

        if (webhook) {
            $('#ff-webhook-id').val(webhook.id);
            $('#ff-webhook-name').val(webhook.name);
            $('#ff-webhook-url').val(webhook.url);
            $('#ff-webhook-instance').val(webhook.instance_id || '');
            $('#ff-webhook-secret').val(webhook.secret || '');
            $('#ff-webhook-active').prop('checked', webhook.is_active);
            $('#ff-webhook-modal-title').text('<?php echo esc_js(__('Edit Webhook', 'formflow-lite')); ?>');
            $('input[name="events[]"]').prop('checked', false);
            if (webhook.events) {
                webhook.events.forEach(function(event) {
                    $('input[name="events[]"][value="' + event + '"]').prop('checked', true);
                });
            }
        }
        $modal.fadeIn(200);
    }

    function closeModal() { $modal.fadeOut(200); }

    function loadWebhook(webhookId) {
        $.post(fffl_admin.ajax_url, {
            action: 'fffl_get_webhook',
            nonce: fffl_admin.nonce,
            webhook_id: webhookId
        }, function(response) {
            if (response.success) openModal(response.data.webhook);
            else alert(response.data.message || '<?php echo esc_js(__('Failed to load webhook.', 'formflow-lite')); ?>');
        });
    }

    function saveWebhook() {
        var $saveBtn = $('#ff-webhook-save');
        $saveBtn.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'formflow-lite')); ?>');

        var events = [];
        $('input[name="events[]"]:checked').each(function() { events.push($(this).val()); });

        $.post(fffl_admin.ajax_url, {
            action: 'fffl_save_webhook',
            nonce: fffl_admin.nonce,
            webhook_id: $('#ff-webhook-id').val(),
            name: $('#ff-webhook-name').val(),
            url: $('#ff-webhook-url').val(),
            instance_id: $('#ff-webhook-instance').val(),
            events: events,
            secret: $('#ff-webhook-secret').val(),
            is_active: $('#ff-webhook-active').is(':checked') ? 1 : 0
        }, function(response) {
            $saveBtn.prop('disabled', false).text('<?php echo esc_js(__('Save Webhook', 'formflow-lite')); ?>');
            if (response.success) { closeModal(); location.reload(); }
            else alert(response.data.message || '<?php echo esc_js(__('Failed to save webhook.', 'formflow-lite')); ?>');
        });
    }

    function deleteWebhook(webhookId) {
        $.post(fffl_admin.ajax_url, {
            action: 'fffl_delete_webhook',
            nonce: fffl_admin.nonce,
            webhook_id: webhookId
        }, function(response) {
            if (response.success) {
                $('tr[data-webhook-id="' + webhookId + '"]').fadeOut(300, function() { $(this).remove(); });
            } else alert(response.data.message || '<?php echo esc_js(__('Failed to delete webhook.', 'formflow-lite')); ?>');
        });
    }

    function testWebhook(webhookId, callback) {
        $.post(fffl_admin.ajax_url, {
            action: 'fffl_test_webhook',
            nonce: fffl_admin.nonce,
            webhook_id: webhookId
        }, function(response) {
            callback();
            if (response.success) alert('<?php echo esc_js(__('Test webhook sent! Status: ', 'formflow-lite')); ?>' + response.data.status_code);
            else alert('<?php echo esc_js(__('Test failed: ', 'formflow-lite')); ?>' + (response.data.message || response.data.error));
        });
    }

    function generateSecret(length) {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var secret = '';
        for (var i = 0; i < length; i++) secret += chars.charAt(Math.floor(Math.random() * chars.length));
        return secret;
    }
});
</script>
