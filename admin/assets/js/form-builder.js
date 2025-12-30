/**
 * FormFlow Visual Form Builder
 * Drag-and-drop form builder with conditional logic support
 */

(function($) {
    'use strict';

    // FormBuilder namespace
    window.FFFormBuilder = window.FFFormBuilder || {};

    /**
     * Main Form Builder Class
     */
    class FormBuilder {
        constructor(options = {}) {
            this.container = $(options.container || '#fffl-form-builder');
            this.instanceId = options.instanceId || 0;
            this.schema = options.schema || this.getDefaultSchema();
            this.fieldTypes = options.fieldTypes || {};
            this.currentStep = 0;
            this.selectedField = null;
            this.isDirty = false;
            this.history = [];
            this.historyIndex = -1;
            this.maxHistory = 50;

            // Bind methods
            this.init = this.init.bind(this);
            this.render = this.render.bind(this);
            this.save = this.save.bind(this);

            // Initialize if container exists
            if (this.container.length) {
                this.init();
            }
        }

        /**
         * Get default empty schema
         */
        getDefaultSchema() {
            return {
                version: '1.0',
                settings: {
                    title: 'New Form',
                    description: '',
                    submit_text: 'Submit',
                    success_message: 'Thank you for your submission.',
                    show_progress: true,
                    enable_autosave: false,
                    conditional_logic: true
                },
                steps: [{
                    id: 'step_1',
                    title: 'Step 1',
                    description: '',
                    fields: []
                }],
                conditions: []
            };
        }

        /**
         * Initialize the form builder
         */
        init() {
            this.buildUI();
            this.bindEvents();
            this.initDragDrop();
            this.render();
            this.saveHistory();

            // Mark as initialized
            this.container.addClass('ff-builder-initialized');
        }

        /**
         * Build the main UI structure
         */
        buildUI() {
            const html = `
                <div class="ff-builder-wrapper">
                    <!-- Toolbar -->
                    <div class="ff-builder-toolbar">
                        <div class="ff-toolbar-left">
                            <button type="button" class="ff-btn ff-btn-icon ff-btn-undo" title="Undo (Ctrl+Z)" disabled>
                                <span class="dashicons dashicons-undo"></span>
                            </button>
                            <button type="button" class="ff-btn ff-btn-icon ff-btn-redo" title="Redo (Ctrl+Y)" disabled>
                                <span class="dashicons dashicons-redo"></span>
                            </button>
                            <span class="ff-toolbar-divider"></span>
                            <button type="button" class="ff-btn ff-btn-icon ff-btn-preview" title="Preview Form">
                                <span class="dashicons dashicons-visibility"></span>
                                <span class="ff-btn-label">Preview</span>
                            </button>
                        </div>
                        <div class="ff-toolbar-center">
                            <input type="text" class="ff-form-title" value="${this.escapeHtml(this.schema.settings.title)}" placeholder="Form Title">
                        </div>
                        <div class="ff-toolbar-right">
                            <span class="ff-save-status"></span>
                            <button type="button" class="ff-btn ff-btn-primary ff-btn-save">
                                <span class="dashicons dashicons-saved"></span>
                                <span class="ff-btn-label">Save Form</span>
                            </button>
                        </div>
                    </div>

                    <!-- Main Content Area -->
                    <div class="ff-builder-main">
                        <!-- Field Palette (Left Sidebar) -->
                        <div class="ff-builder-palette">
                            <div class="ff-palette-search">
                                <input type="text" placeholder="Search fields..." class="ff-field-search">
                                <span class="dashicons dashicons-search"></span>
                            </div>
                            <div class="ff-palette-fields"></div>
                        </div>

                        <!-- Canvas (Center) -->
                        <div class="ff-builder-canvas">
                            <div class="ff-canvas-header">
                                <div class="ff-steps-nav"></div>
                                <button type="button" class="ff-btn ff-btn-sm ff-btn-add-step">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                    Add Step
                                </button>
                            </div>
                            <div class="ff-canvas-body">
                                <div class="ff-step-content"></div>
                            </div>
                        </div>

                        <!-- Properties Panel (Right Sidebar) -->
                        <div class="ff-builder-properties">
                            <div class="ff-properties-header">
                                <h3>Properties</h3>
                                <button type="button" class="ff-btn ff-btn-icon ff-btn-close-props" title="Close">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                            <div class="ff-properties-content">
                                <div class="ff-no-selection">
                                    <span class="dashicons dashicons-forms"></span>
                                    <p>Select a field to edit its properties</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conditional Logic Modal -->
                    <div class="ff-modal ff-modal-conditions" style="display:none;">
                        <div class="ff-modal-overlay"></div>
                        <div class="ff-modal-content">
                            <div class="ff-modal-header">
                                <h3>Conditional Logic</h3>
                                <button type="button" class="ff-modal-close">&times;</button>
                            </div>
                            <div class="ff-modal-body"></div>
                            <div class="ff-modal-footer">
                                <button type="button" class="ff-btn ff-btn-secondary ff-modal-cancel">Cancel</button>
                                <button type="button" class="ff-btn ff-btn-primary ff-modal-save">Save Conditions</button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Modal -->
                    <div class="ff-modal ff-modal-preview" style="display:none;">
                        <div class="ff-modal-overlay"></div>
                        <div class="ff-modal-content ff-modal-lg">
                            <div class="ff-modal-header">
                                <h3>Form Preview</h3>
                                <div class="ff-preview-device-toggle">
                                    <button type="button" class="ff-btn ff-btn-icon active" data-device="desktop" title="Desktop">
                                        <span class="dashicons dashicons-desktop"></span>
                                    </button>
                                    <button type="button" class="ff-btn ff-btn-icon" data-device="tablet" title="Tablet">
                                        <span class="dashicons dashicons-tablet"></span>
                                    </button>
                                    <button type="button" class="ff-btn ff-btn-icon" data-device="mobile" title="Mobile">
                                        <span class="dashicons dashicons-smartphone"></span>
                                    </button>
                                </div>
                                <button type="button" class="ff-modal-close">&times;</button>
                            </div>
                            <div class="ff-modal-body">
                                <div class="ff-preview-frame"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            this.container.html(html);
            this.buildFieldPalette();
        }

        /**
         * Build the field palette with categorized field types
         */
        buildFieldPalette() {
            const $palette = this.container.find('.ff-palette-fields');
            const categories = this.getFieldCategories();

            let html = '';

            for (const [catKey, category] of Object.entries(categories)) {
                html += `
                    <div class="ff-palette-category" data-category="${catKey}">
                        <div class="ff-category-header">
                            <span class="dashicons ${category.icon}"></span>
                            <span class="ff-category-title">${this.escapeHtml(category.label)}</span>
                            <span class="dashicons dashicons-arrow-down-alt2 ff-category-toggle"></span>
                        </div>
                        <div class="ff-category-fields">
                `;

                for (const field of category.fields) {
                    html += `
                        <div class="ff-palette-field" data-type="${field.type}" draggable="true">
                            <span class="dashicons ${field.icon}"></span>
                            <span class="ff-field-label">${this.escapeHtml(field.label)}</span>
                        </div>
                    `;
                }

                html += `
                        </div>
                    </div>
                `;
            }

            $palette.html(html);
        }

        /**
         * Get field categories with their field types
         */
        getFieldCategories() {
            return {
                basic: {
                    label: 'Basic Fields',
                    icon: 'dashicons-edit',
                    fields: [
                        { type: 'text', label: 'Text', icon: 'dashicons-editor-textcolor' },
                        { type: 'textarea', label: 'Textarea', icon: 'dashicons-text' },
                        { type: 'email', label: 'Email', icon: 'dashicons-email' },
                        { type: 'phone', label: 'Phone', icon: 'dashicons-phone' },
                        { type: 'number', label: 'Number', icon: 'dashicons-calculator' },
                        { type: 'password', label: 'Password', icon: 'dashicons-lock' },
                        { type: 'hidden', label: 'Hidden', icon: 'dashicons-hidden' }
                    ]
                },
                selection: {
                    label: 'Selection Fields',
                    icon: 'dashicons-list-view',
                    fields: [
                        { type: 'select', label: 'Dropdown', icon: 'dashicons-arrow-down-alt' },
                        { type: 'radio', label: 'Radio Buttons', icon: 'dashicons-marker' },
                        { type: 'checkbox', label: 'Checkboxes', icon: 'dashicons-yes-alt' },
                        { type: 'checkbox_single', label: 'Single Checkbox', icon: 'dashicons-yes' }
                    ]
                },
                advanced: {
                    label: 'Advanced Fields',
                    icon: 'dashicons-admin-tools',
                    fields: [
                        { type: 'date', label: 'Date Picker', icon: 'dashicons-calendar-alt' },
                        { type: 'time', label: 'Time Picker', icon: 'dashicons-clock' },
                        { type: 'datetime', label: 'Date & Time', icon: 'dashicons-calendar' },
                        { type: 'file', label: 'File Upload', icon: 'dashicons-upload' },
                        { type: 'signature', label: 'Signature', icon: 'dashicons-art' },
                        { type: 'rating', label: 'Rating', icon: 'dashicons-star-filled' },
                        { type: 'slider', label: 'Slider', icon: 'dashicons-leftright' }
                    ]
                },
                address: {
                    label: 'Address Fields',
                    icon: 'dashicons-location',
                    fields: [
                        { type: 'address', label: 'Full Address', icon: 'dashicons-location-alt' },
                        { type: 'street', label: 'Street', icon: 'dashicons-admin-home' },
                        { type: 'city', label: 'City', icon: 'dashicons-building' },
                        { type: 'state', label: 'State', icon: 'dashicons-flag' },
                        { type: 'zip', label: 'ZIP Code', icon: 'dashicons-location' },
                        { type: 'country', label: 'Country', icon: 'dashicons-admin-site' }
                    ]
                },
                utility: {
                    label: 'Utility Fields',
                    icon: 'dashicons-lightbulb',
                    fields: [
                        { type: 'account_number', label: 'Account Number', icon: 'dashicons-id-alt' },
                        { type: 'meter_number', label: 'Meter Number', icon: 'dashicons-dashboard' },
                        { type: 'program_selector', label: 'Program Selector', icon: 'dashicons-networking' },
                        { type: 'service_address', label: 'Service Address', icon: 'dashicons-location' },
                        { type: 'appointment_picker', label: 'Appointment Picker', icon: 'dashicons-calendar' }
                    ]
                },
                layout: {
                    label: 'Layout Elements',
                    icon: 'dashicons-layout',
                    fields: [
                        { type: 'heading', label: 'Heading', icon: 'dashicons-heading' },
                        { type: 'paragraph', label: 'Paragraph', icon: 'dashicons-editor-paragraph' },
                        { type: 'divider', label: 'Divider', icon: 'dashicons-minus' },
                        { type: 'columns', label: 'Columns', icon: 'dashicons-columns' },
                        { type: 'section', label: 'Section', icon: 'dashicons-align-wide' },
                        { type: 'html', label: 'HTML Block', icon: 'dashicons-html' }
                    ]
                }
            };
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const self = this;

            // Toolbar events
            this.container.on('click', '.ff-btn-save', () => this.save());
            this.container.on('click', '.ff-btn-undo', () => this.undo());
            this.container.on('click', '.ff-btn-redo', () => this.redo());
            this.container.on('click', '.ff-btn-preview', () => this.showPreview());
            this.container.on('input', '.ff-form-title', (e) => {
                this.schema.settings.title = $(e.target).val();
                this.markDirty();
            });

            // Step navigation
            this.container.on('click', '.ff-step-tab', (e) => {
                const index = $(e.currentTarget).data('step-index');
                this.switchStep(index);
            });
            this.container.on('click', '.ff-btn-add-step', () => this.addStep());
            this.container.on('click', '.ff-step-delete', (e) => {
                e.stopPropagation();
                const index = $(e.currentTarget).closest('.ff-step-tab').data('step-index');
                this.deleteStep(index);
            });

            // Field palette
            this.container.on('click', '.ff-category-header', (e) => {
                $(e.currentTarget).closest('.ff-palette-category').toggleClass('collapsed');
            });
            this.container.on('input', '.ff-field-search', (e) => {
                this.filterFields($(e.target).val());
            });

            // Canvas field events
            this.container.on('click', '.ff-canvas-field', (e) => {
                e.stopPropagation();
                this.selectField($(e.currentTarget).data('field-id'));
            });
            this.container.on('click', '.ff-field-delete', (e) => {
                e.stopPropagation();
                const fieldId = $(e.currentTarget).closest('.ff-canvas-field').data('field-id');
                this.deleteField(fieldId);
            });
            this.container.on('click', '.ff-field-duplicate', (e) => {
                e.stopPropagation();
                const fieldId = $(e.currentTarget).closest('.ff-canvas-field').data('field-id');
                this.duplicateField(fieldId);
            });
            this.container.on('click', '.ff-field-conditions', (e) => {
                e.stopPropagation();
                const fieldId = $(e.currentTarget).closest('.ff-canvas-field').data('field-id');
                this.openConditionsModal(fieldId);
            });

            // Deselect field when clicking canvas background
            this.container.on('click', '.ff-canvas-body', (e) => {
                if ($(e.target).hasClass('ff-canvas-body') || $(e.target).hasClass('ff-step-content')) {
                    this.deselectField();
                }
            });

            // Properties panel events
            this.container.on('click', '.ff-btn-close-props', () => this.deselectField());
            this.container.on('input change', '.ff-properties-content input, .ff-properties-content select, .ff-properties-content textarea', (e) => {
                this.updateFieldProperty(e);
            });

            // Modal events
            this.container.on('click', '.ff-modal-overlay, .ff-modal-close, .ff-modal-cancel', () => {
                this.closeModals();
            });
            this.container.on('click', '.ff-modal-save', (e) => {
                const $modal = $(e.target).closest('.ff-modal');
                if ($modal.hasClass('ff-modal-conditions')) {
                    this.saveConditions();
                }
            });

            // Preview device toggle
            this.container.on('click', '.ff-preview-device-toggle .ff-btn', (e) => {
                const device = $(e.currentTarget).data('device');
                this.container.find('.ff-preview-device-toggle .ff-btn').removeClass('active');
                $(e.currentTarget).addClass('active');
                this.container.find('.ff-preview-frame')
                    .removeClass('device-desktop device-tablet device-mobile')
                    .addClass('device-' + device);
            });

            // Keyboard shortcuts
            $(document).on('keydown', (e) => {
                if (!this.container.is(':visible')) return;

                // Ctrl+S / Cmd+S - Save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }
                // Ctrl+Z / Cmd+Z - Undo
                if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') {
                    e.preventDefault();
                    this.undo();
                }
                // Ctrl+Shift+Z / Cmd+Shift+Z or Ctrl+Y - Redo
                if (((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'z') ||
                    ((e.ctrlKey || e.metaKey) && e.key === 'y')) {
                    e.preventDefault();
                    this.redo();
                }
                // Delete - Delete selected field
                if (e.key === 'Delete' && this.selectedField) {
                    e.preventDefault();
                    this.deleteField(this.selectedField);
                }
                // Escape - Deselect/close modals
                if (e.key === 'Escape') {
                    this.closeModals();
                    this.deselectField();
                }
            });

            // Warn before leaving with unsaved changes
            $(window).on('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        }

        /**
         * Initialize drag and drop functionality
         */
        initDragDrop() {
            const self = this;

            // Make palette fields draggable
            this.container.find('.ff-palette-field').each(function() {
                $(this).on('dragstart', function(e) {
                    const type = $(this).data('type');
                    e.originalEvent.dataTransfer.setData('field-type', type);
                    e.originalEvent.dataTransfer.effectAllowed = 'copy';
                    $(this).addClass('dragging');
                });

                $(this).on('dragend', function() {
                    $(this).removeClass('dragging');
                    self.container.find('.ff-drop-indicator').remove();
                });
            });

            // Make canvas a drop zone
            this.container.find('.ff-step-content').on('dragover', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'copy';

                // Show drop indicator
                const $fields = $(this).find('.ff-canvas-field');
                const mouseY = e.originalEvent.clientY;
                let insertBefore = null;

                $fields.each(function() {
                    const rect = this.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;
                    if (mouseY < midY && !insertBefore) {
                        insertBefore = this;
                    }
                });

                self.container.find('.ff-drop-indicator').remove();
                const $indicator = $('<div class="ff-drop-indicator"></div>');

                if (insertBefore) {
                    $(insertBefore).before($indicator);
                } else {
                    $(this).append($indicator);
                }
            });

            this.container.find('.ff-step-content').on('dragleave', function(e) {
                if (!$(e.relatedTarget).closest('.ff-step-content').length) {
                    self.container.find('.ff-drop-indicator').remove();
                }
            });

            this.container.find('.ff-step-content').on('drop', function(e) {
                e.preventDefault();
                self.container.find('.ff-drop-indicator').remove();

                const type = e.originalEvent.dataTransfer.getData('field-type');
                if (!type) return;

                // Find insert position
                const $fields = $(this).find('.ff-canvas-field');
                const mouseY = e.originalEvent.clientY;
                let insertIndex = $fields.length;

                $fields.each(function(index) {
                    const rect = this.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;
                    if (mouseY < midY && insertIndex === $fields.length) {
                        insertIndex = index;
                    }
                });

                self.addField(type, insertIndex);
            });

            // Make canvas fields sortable
            this.container.find('.ff-step-content').sortable({
                handle: '.ff-field-drag-handle',
                placeholder: 'ff-field-placeholder',
                tolerance: 'pointer',
                cursor: 'grabbing',
                opacity: 0.8,
                items: '.ff-canvas-field',
                update: function(event, ui) {
                    self.reorderFields();
                }
            });
        }

        /**
         * Render the form builder
         */
        render() {
            this.renderStepNav();
            this.renderStepContent();
            this.updateUndoRedoButtons();
        }

        /**
         * Render step navigation tabs
         */
        renderStepNav() {
            const $nav = this.container.find('.ff-steps-nav');
            let html = '';

            this.schema.steps.forEach((step, index) => {
                const isActive = index === this.currentStep;
                html += `
                    <div class="ff-step-tab ${isActive ? 'active' : ''}" data-step-index="${index}">
                        <span class="ff-step-number">${index + 1}</span>
                        <span class="ff-step-title">${this.escapeHtml(step.title || 'Step ' + (index + 1))}</span>
                        ${this.schema.steps.length > 1 ? '<button type="button" class="ff-step-delete" title="Delete Step">&times;</button>' : ''}
                    </div>
                `;
            });

            $nav.html(html);
        }

        /**
         * Render the current step's fields
         */
        renderStepContent() {
            const $content = this.container.find('.ff-step-content');
            const step = this.schema.steps[this.currentStep];

            if (!step) {
                $content.html('<div class="ff-empty-step">No step selected</div>');
                return;
            }

            if (!step.fields || step.fields.length === 0) {
                $content.html(`
                    <div class="ff-empty-step">
                        <span class="dashicons dashicons-welcome-add-page"></span>
                        <p>Drag fields here to build your form</p>
                    </div>
                `);
                return;
            }

            let html = '';
            step.fields.forEach((field) => {
                html += this.renderCanvasField(field);
            });

            $content.html(html);

            // Reinitialize sortable
            $content.sortable('refresh');

            // Restore selection if applicable
            if (this.selectedField) {
                this.container.find(`.ff-canvas-field[data-field-id="${this.selectedField}"]`).addClass('selected');
            }
        }

        /**
         * Render a single field on the canvas
         */
        renderCanvasField(field) {
            const fieldConfig = this.getFieldConfig(field.type);
            const hasConditions = this.fieldHasConditions(field.id);
            const isSelected = field.id === this.selectedField;

            return `
                <div class="ff-canvas-field ${isSelected ? 'selected' : ''} ${hasConditions ? 'has-conditions' : ''}"
                     data-field-id="${field.id}"
                     data-field-type="${field.type}">
                    <div class="ff-field-header">
                        <span class="ff-field-drag-handle dashicons dashicons-move"></span>
                        <span class="ff-field-type-icon dashicons ${fieldConfig.icon}"></span>
                        <span class="ff-field-type-label">${this.escapeHtml(fieldConfig.label)}</span>
                        <div class="ff-field-actions">
                            <button type="button" class="ff-field-conditions" title="Conditional Logic">
                                <span class="dashicons dashicons-randomize"></span>
                            </button>
                            <button type="button" class="ff-field-duplicate" title="Duplicate">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            <button type="button" class="ff-field-delete" title="Delete">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="ff-field-preview">
                        ${this.renderFieldPreview(field)}
                    </div>
                    ${hasConditions ? '<div class="ff-field-conditions-indicator"><span class="dashicons dashicons-randomize"></span> Has conditions</div>' : ''}
                </div>
            `;
        }

        /**
         * Render a field preview for the canvas
         */
        renderFieldPreview(field) {
            const label = field.label || 'Untitled Field';
            const required = field.required ? '<span class="ff-required">*</span>' : '';

            let preview = `<label class="ff-preview-label">${this.escapeHtml(label)}${required}</label>`;

            switch (field.type) {
                case 'text':
                case 'email':
                case 'phone':
                case 'number':
                case 'password':
                case 'account_number':
                case 'meter_number':
                case 'zip':
                    preview += `<input type="text" class="ff-preview-input" placeholder="${this.escapeHtml(field.placeholder || '')}" disabled>`;
                    break;

                case 'textarea':
                    preview += `<textarea class="ff-preview-textarea" placeholder="${this.escapeHtml(field.placeholder || '')}" disabled></textarea>`;
                    break;

                case 'select':
                case 'state':
                case 'country':
                    preview += `<select class="ff-preview-select" disabled>
                        <option>${this.escapeHtml(field.placeholder || 'Select...')}</option>
                    </select>`;
                    break;

                case 'radio':
                    const radioOptions = field.options || [{ label: 'Option 1' }, { label: 'Option 2' }];
                    preview += '<div class="ff-preview-options">';
                    radioOptions.slice(0, 3).forEach((opt, i) => {
                        preview += `<label class="ff-preview-radio"><input type="radio" disabled ${i === 0 ? 'checked' : ''}> ${this.escapeHtml(opt.label || opt)}</label>`;
                    });
                    if (radioOptions.length > 3) preview += `<span class="ff-more-options">+${radioOptions.length - 3} more</span>`;
                    preview += '</div>';
                    break;

                case 'checkbox':
                    const checkOptions = field.options || [{ label: 'Option 1' }, { label: 'Option 2' }];
                    preview += '<div class="ff-preview-options">';
                    checkOptions.slice(0, 3).forEach((opt) => {
                        preview += `<label class="ff-preview-checkbox"><input type="checkbox" disabled> ${this.escapeHtml(opt.label || opt)}</label>`;
                    });
                    if (checkOptions.length > 3) preview += `<span class="ff-more-options">+${checkOptions.length - 3} more</span>`;
                    preview += '</div>';
                    break;

                case 'checkbox_single':
                    preview += `<label class="ff-preview-checkbox"><input type="checkbox" disabled> ${this.escapeHtml(field.checkbox_label || 'I agree')}</label>`;
                    break;

                case 'date':
                    preview += `<input type="text" class="ff-preview-input ff-preview-date" placeholder="MM/DD/YYYY" disabled><span class="dashicons dashicons-calendar-alt"></span>`;
                    break;

                case 'time':
                    preview += `<input type="text" class="ff-preview-input" placeholder="HH:MM" disabled><span class="dashicons dashicons-clock"></span>`;
                    break;

                case 'datetime':
                    preview += `<input type="text" class="ff-preview-input" placeholder="MM/DD/YYYY HH:MM" disabled><span class="dashicons dashicons-calendar"></span>`;
                    break;

                case 'file':
                    preview += `<div class="ff-preview-file"><span class="dashicons dashicons-upload"></span> Drop files or click to upload</div>`;
                    break;

                case 'signature':
                    preview += `<div class="ff-preview-signature"><span class="dashicons dashicons-art"></span> Click to sign</div>`;
                    break;

                case 'rating':
                    preview += `<div class="ff-preview-rating">`;
                    for (let i = 0; i < 5; i++) {
                        preview += `<span class="dashicons dashicons-star-empty"></span>`;
                    }
                    preview += `</div>`;
                    break;

                case 'slider':
                    preview += `<input type="range" class="ff-preview-slider" disabled>`;
                    break;

                case 'address':
                case 'service_address':
                    preview = `<label class="ff-preview-label">${this.escapeHtml(label)}${required}</label>`;
                    preview += `<div class="ff-preview-address">
                        <input type="text" class="ff-preview-input" placeholder="Street Address" disabled>
                        <div class="ff-preview-address-row">
                            <input type="text" placeholder="City" disabled>
                            <input type="text" placeholder="State" disabled>
                            <input type="text" placeholder="ZIP" disabled>
                        </div>
                    </div>`;
                    break;

                case 'program_selector':
                    preview += `<div class="ff-preview-programs">
                        <div class="ff-preview-program"><input type="checkbox" disabled> Program 1</div>
                        <div class="ff-preview-program"><input type="checkbox" disabled> Program 2</div>
                    </div>`;
                    break;

                case 'appointment_picker':
                    preview += `<div class="ff-preview-appointment">
                        <span class="dashicons dashicons-calendar"></span>
                        Select available date and time
                    </div>`;
                    break;

                case 'heading':
                    const headingLevel = field.heading_level || 'h2';
                    preview = `<${headingLevel} class="ff-preview-heading">${this.escapeHtml(field.content || 'Heading')}</${headingLevel}>`;
                    break;

                case 'paragraph':
                    preview = `<p class="ff-preview-paragraph">${this.escapeHtml(field.content || 'Paragraph text...')}</p>`;
                    break;

                case 'divider':
                    preview = `<hr class="ff-preview-divider">`;
                    break;

                case 'columns':
                    const colCount = field.columns || 2;
                    preview = `<div class="ff-preview-columns" style="--columns: ${colCount}">`;
                    for (let i = 0; i < colCount; i++) {
                        preview += `<div class="ff-preview-column">Column ${i + 1}</div>`;
                    }
                    preview += `</div>`;
                    break;

                case 'section':
                    preview = `<div class="ff-preview-section">
                        <div class="ff-preview-section-title">${this.escapeHtml(field.section_title || 'Section')}</div>
                        <div class="ff-preview-section-content">Section content area</div>
                    </div>`;
                    break;

                case 'html':
                    preview = `<div class="ff-preview-html"><span class="dashicons dashicons-html"></span> Custom HTML Block</div>`;
                    break;

                case 'hidden':
                    preview = `<div class="ff-preview-hidden"><span class="dashicons dashicons-hidden"></span> Hidden field: ${this.escapeHtml(field.name || 'field_name')}</div>`;
                    break;

                default:
                    preview += `<input type="text" class="ff-preview-input" disabled>`;
            }

            if (field.description) {
                preview += `<p class="ff-preview-description">${this.escapeHtml(field.description)}</p>`;
            }

            return preview;
        }

        /**
         * Get field configuration by type
         */
        getFieldConfig(type) {
            const categories = this.getFieldCategories();
            for (const category of Object.values(categories)) {
                const field = category.fields.find(f => f.type === type);
                if (field) return field;
            }
            return { type, label: type, icon: 'dashicons-admin-generic' };
        }

        /**
         * Add a new field to the current step
         */
        addField(type, insertIndex = null) {
            const step = this.schema.steps[this.currentStep];
            if (!step.fields) step.fields = [];

            const field = {
                id: this.generateId(),
                type: type,
                name: type + '_' + Date.now(),
                label: this.getFieldConfig(type).label,
                required: false,
                placeholder: '',
                description: ''
            };

            // Add type-specific defaults
            this.addFieldDefaults(field);

            if (insertIndex !== null && insertIndex < step.fields.length) {
                step.fields.splice(insertIndex, 0, field);
            } else {
                step.fields.push(field);
            }

            this.markDirty();
            this.saveHistory();
            this.renderStepContent();
            this.selectField(field.id);
        }

        /**
         * Add type-specific default values to a field
         */
        addFieldDefaults(field) {
            switch (field.type) {
                case 'select':
                case 'radio':
                case 'checkbox':
                    field.options = [
                        { value: 'option_1', label: 'Option 1' },
                        { value: 'option_2', label: 'Option 2' },
                        { value: 'option_3', label: 'Option 3' }
                    ];
                    break;

                case 'checkbox_single':
                    field.checkbox_label = 'I agree to the terms and conditions';
                    break;

                case 'rating':
                    field.max_rating = 5;
                    break;

                case 'slider':
                    field.min = 0;
                    field.max = 100;
                    field.step = 1;
                    break;

                case 'heading':
                    field.heading_level = 'h2';
                    field.content = 'Section Heading';
                    break;

                case 'paragraph':
                    field.content = 'Enter your text here...';
                    break;

                case 'columns':
                    field.columns = 2;
                    break;

                case 'section':
                    field.section_title = 'Section Title';
                    break;

                case 'program_selector':
                    field.allow_multiple = true;
                    field.show_descriptions = true;
                    break;
            }
        }

        /**
         * Delete a field
         */
        deleteField(fieldId) {
            const step = this.schema.steps[this.currentStep];
            const index = step.fields.findIndex(f => f.id === fieldId);

            if (index === -1) return;

            step.fields.splice(index, 1);

            // Remove any conditions referencing this field
            this.removeFieldConditions(fieldId);

            if (this.selectedField === fieldId) {
                this.deselectField();
            }

            this.markDirty();
            this.saveHistory();
            this.renderStepContent();
        }

        /**
         * Duplicate a field
         */
        duplicateField(fieldId) {
            const step = this.schema.steps[this.currentStep];
            const index = step.fields.findIndex(f => f.id === fieldId);

            if (index === -1) return;

            const original = step.fields[index];
            const duplicate = JSON.parse(JSON.stringify(original));
            duplicate.id = this.generateId();
            duplicate.name = original.name + '_copy';
            duplicate.label = original.label + ' (Copy)';

            step.fields.splice(index + 1, 0, duplicate);

            this.markDirty();
            this.saveHistory();
            this.renderStepContent();
            this.selectField(duplicate.id);
        }

        /**
         * Reorder fields after drag-drop
         */
        reorderFields() {
            const step = this.schema.steps[this.currentStep];
            const newOrder = [];

            this.container.find('.ff-step-content .ff-canvas-field').each(function() {
                const id = $(this).data('field-id');
                const field = step.fields.find(f => f.id === id);
                if (field) newOrder.push(field);
            });

            step.fields = newOrder;
            this.markDirty();
            this.saveHistory();
        }

        /**
         * Select a field for editing
         */
        selectField(fieldId) {
            this.selectedField = fieldId;

            // Update visual selection
            this.container.find('.ff-canvas-field').removeClass('selected');
            this.container.find(`.ff-canvas-field[data-field-id="${fieldId}"]`).addClass('selected');

            // Show properties panel
            this.renderPropertiesPanel(fieldId);
            this.container.find('.ff-builder-properties').addClass('active');
        }

        /**
         * Deselect the current field
         */
        deselectField() {
            this.selectedField = null;
            this.container.find('.ff-canvas-field').removeClass('selected');
            this.container.find('.ff-builder-properties').removeClass('active');
            this.container.find('.ff-properties-content').html(`
                <div class="ff-no-selection">
                    <span class="dashicons dashicons-forms"></span>
                    <p>Select a field to edit its properties</p>
                </div>
            `);
        }

        /**
         * Render properties panel for selected field
         */
        renderPropertiesPanel(fieldId) {
            const step = this.schema.steps[this.currentStep];
            const field = step.fields.find(f => f.id === fieldId);

            if (!field) return;

            const $content = this.container.find('.ff-properties-content');
            const fieldConfig = this.getFieldConfig(field.type);

            let html = `
                <div class="ff-properties-field">
                    <div class="ff-prop-header">
                        <span class="dashicons ${fieldConfig.icon}"></span>
                        <span>${this.escapeHtml(fieldConfig.label)}</span>
                    </div>

                    <div class="ff-prop-tabs">
                        <button type="button" class="ff-prop-tab active" data-tab="general">General</button>
                        <button type="button" class="ff-prop-tab" data-tab="validation">Validation</button>
                        <button type="button" class="ff-prop-tab" data-tab="advanced">Advanced</button>
                    </div>

                    <div class="ff-prop-tab-content" data-tab="general">
                        ${this.renderGeneralProperties(field)}
                    </div>

                    <div class="ff-prop-tab-content" data-tab="validation" style="display:none;">
                        ${this.renderValidationProperties(field)}
                    </div>

                    <div class="ff-prop-tab-content" data-tab="advanced" style="display:none;">
                        ${this.renderAdvancedProperties(field)}
                    </div>
                </div>
            `;

            $content.html(html);

            // Tab switching
            $content.find('.ff-prop-tab').on('click', function() {
                const tab = $(this).data('tab');
                $content.find('.ff-prop-tab').removeClass('active');
                $(this).addClass('active');
                $content.find('.ff-prop-tab-content').hide();
                $content.find(`.ff-prop-tab-content[data-tab="${tab}"]`).show();
            });

            // Initialize options editor if needed
            if (['select', 'radio', 'checkbox'].includes(field.type)) {
                this.initOptionsEditor(field);
            }
        }

        /**
         * Render general properties
         */
        renderGeneralProperties(field) {
            const isLayoutField = ['heading', 'paragraph', 'divider', 'columns', 'section', 'html'].includes(field.type);

            let html = `
                <div class="ff-prop-group">
                    <label class="ff-prop-label">Field Name</label>
                    <input type="text" class="ff-prop-input" data-prop="name" value="${this.escapeHtml(field.name || '')}">
                    <p class="ff-prop-help">Used for form submission (no spaces)</p>
                </div>
            `;

            if (!isLayoutField) {
                html += `
                    <div class="ff-prop-group">
                        <label class="ff-prop-label">Label</label>
                        <input type="text" class="ff-prop-input" data-prop="label" value="${this.escapeHtml(field.label || '')}">
                    </div>

                    <div class="ff-prop-group">
                        <label class="ff-prop-label">Placeholder</label>
                        <input type="text" class="ff-prop-input" data-prop="placeholder" value="${this.escapeHtml(field.placeholder || '')}">
                    </div>

                    <div class="ff-prop-group">
                        <label class="ff-prop-label">Description</label>
                        <textarea class="ff-prop-textarea" data-prop="description">${this.escapeHtml(field.description || '')}</textarea>
                    </div>

                    <div class="ff-prop-group">
                        <label class="ff-prop-checkbox">
                            <input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}>
                            Required field
                        </label>
                    </div>
                `;
            }

            // Type-specific properties
            html += this.renderTypeSpecificProperties(field);

            return html;
        }

        /**
         * Render type-specific properties
         */
        renderTypeSpecificProperties(field) {
            let html = '';

            switch (field.type) {
                case 'select':
                case 'radio':
                case 'checkbox':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Options</label>
                            <div class="ff-options-editor" data-field-id="${field.id}">
                                <div class="ff-options-list"></div>
                                <button type="button" class="ff-btn ff-btn-sm ff-btn-add-option">
                                    <span class="dashicons dashicons-plus"></span> Add Option
                                </button>
                            </div>
                        </div>
                    `;
                    break;

                case 'checkbox_single':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Checkbox Label</label>
                            <input type="text" class="ff-prop-input" data-prop="checkbox_label" value="${this.escapeHtml(field.checkbox_label || '')}">
                        </div>
                    `;
                    break;

                case 'number':
                case 'slider':
                    html = `
                        <div class="ff-prop-row">
                            <div class="ff-prop-group ff-prop-half">
                                <label class="ff-prop-label">Min</label>
                                <input type="number" class="ff-prop-input" data-prop="min" value="${field.min || ''}">
                            </div>
                            <div class="ff-prop-group ff-prop-half">
                                <label class="ff-prop-label">Max</label>
                                <input type="number" class="ff-prop-input" data-prop="max" value="${field.max || ''}">
                            </div>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Step</label>
                            <input type="number" class="ff-prop-input" data-prop="step" value="${field.step || 1}">
                        </div>
                    `;
                    break;

                case 'rating':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Max Rating</label>
                            <select class="ff-prop-select" data-prop="max_rating">
                                <option value="5" ${field.max_rating == 5 ? 'selected' : ''}>5 Stars</option>
                                <option value="10" ${field.max_rating == 10 ? 'selected' : ''}>10 Stars</option>
                            </select>
                        </div>
                    `;
                    break;

                case 'file':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Allowed File Types</label>
                            <input type="text" class="ff-prop-input" data-prop="allowed_types" value="${this.escapeHtml(field.allowed_types || 'jpg,png,pdf')}" placeholder="jpg,png,pdf">
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Max File Size (MB)</label>
                            <input type="number" class="ff-prop-input" data-prop="max_size" value="${field.max_size || 5}">
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="multiple" ${field.multiple ? 'checked' : ''}>
                                Allow multiple files
                            </label>
                        </div>
                    `;
                    break;

                case 'heading':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Heading Level</label>
                            <select class="ff-prop-select" data-prop="heading_level">
                                <option value="h1" ${field.heading_level === 'h1' ? 'selected' : ''}>H1</option>
                                <option value="h2" ${field.heading_level === 'h2' ? 'selected' : ''}>H2</option>
                                <option value="h3" ${field.heading_level === 'h3' ? 'selected' : ''}>H3</option>
                                <option value="h4" ${field.heading_level === 'h4' ? 'selected' : ''}>H4</option>
                            </select>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Heading Text</label>
                            <input type="text" class="ff-prop-input" data-prop="content" value="${this.escapeHtml(field.content || '')}">
                        </div>
                    `;
                    break;

                case 'paragraph':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Content</label>
                            <textarea class="ff-prop-textarea" data-prop="content" rows="5">${this.escapeHtml(field.content || '')}</textarea>
                        </div>
                    `;
                    break;

                case 'columns':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Number of Columns</label>
                            <select class="ff-prop-select" data-prop="columns">
                                <option value="2" ${field.columns == 2 ? 'selected' : ''}>2 Columns</option>
                                <option value="3" ${field.columns == 3 ? 'selected' : ''}>3 Columns</option>
                                <option value="4" ${field.columns == 4 ? 'selected' : ''}>4 Columns</option>
                            </select>
                        </div>
                    `;
                    break;

                case 'section':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Section Title</label>
                            <input type="text" class="ff-prop-input" data-prop="section_title" value="${this.escapeHtml(field.section_title || '')}">
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="collapsible" ${field.collapsible ? 'checked' : ''}>
                                Collapsible section
                            </label>
                        </div>
                    `;
                    break;

                case 'html':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">HTML Content</label>
                            <textarea class="ff-prop-textarea ff-prop-code" data-prop="html_content" rows="8">${this.escapeHtml(field.html_content || '')}</textarea>
                            <p class="ff-prop-help">Enter valid HTML. Scripts are not allowed.</p>
                        </div>
                    `;
                    break;

                case 'hidden':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Default Value</label>
                            <input type="text" class="ff-prop-input" data-prop="default_value" value="${this.escapeHtml(field.default_value || '')}">
                        </div>
                    `;
                    break;

                case 'date':
                case 'datetime':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-label">Date Format</label>
                            <select class="ff-prop-select" data-prop="date_format">
                                <option value="MM/DD/YYYY" ${field.date_format === 'MM/DD/YYYY' ? 'selected' : ''}>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" ${field.date_format === 'DD/MM/YYYY' ? 'selected' : ''}>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD" ${field.date_format === 'YYYY-MM-DD' ? 'selected' : ''}>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="disable_past" ${field.disable_past ? 'checked' : ''}>
                                Disable past dates
                            </label>
                        </div>
                    `;
                    break;

                case 'program_selector':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="allow_multiple" ${field.allow_multiple ? 'checked' : ''}>
                                Allow multiple program selection
                            </label>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="show_descriptions" ${field.show_descriptions ? 'checked' : ''}>
                                Show program descriptions
                            </label>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="show_cross_sell" ${field.show_cross_sell ? 'checked' : ''}>
                                Enable cross-sell recommendations
                            </label>
                        </div>
                    `;
                    break;

                case 'address':
                case 'service_address':
                    html = `
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="enable_autocomplete" ${field.enable_autocomplete !== false ? 'checked' : ''}>
                                Enable address autocomplete
                            </label>
                        </div>
                        <div class="ff-prop-group">
                            <label class="ff-prop-checkbox">
                                <input type="checkbox" data-prop="validate_territory" ${field.validate_territory ? 'checked' : ''}>
                                Validate service territory
                            </label>
                        </div>
                    `;
                    break;
            }

            return html;
        }

        /**
         * Render validation properties
         */
        renderValidationProperties(field) {
            const isLayoutField = ['heading', 'paragraph', 'divider', 'columns', 'section', 'html'].includes(field.type);

            if (isLayoutField) {
                return '<p class="ff-prop-notice">Layout elements do not have validation options.</p>';
            }

            let html = `
                <div class="ff-prop-group">
                    <label class="ff-prop-checkbox">
                        <input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}>
                        Required field
                    </label>
                </div>
            `;

            // Text-based fields
            if (['text', 'textarea', 'email', 'phone', 'password'].includes(field.type)) {
                html += `
                    <div class="ff-prop-row">
                        <div class="ff-prop-group ff-prop-half">
                            <label class="ff-prop-label">Min Length</label>
                            <input type="number" class="ff-prop-input" data-prop="min_length" value="${field.min_length || ''}" min="0">
                        </div>
                        <div class="ff-prop-group ff-prop-half">
                            <label class="ff-prop-label">Max Length</label>
                            <input type="number" class="ff-prop-input" data-prop="max_length" value="${field.max_length || ''}" min="0">
                        </div>
                    </div>
                `;
            }

            // Custom validation pattern
            if (['text', 'phone', 'account_number', 'meter_number'].includes(field.type)) {
                html += `
                    <div class="ff-prop-group">
                        <label class="ff-prop-label">Validation Pattern (Regex)</label>
                        <input type="text" class="ff-prop-input" data-prop="pattern" value="${this.escapeHtml(field.pattern || '')}" placeholder="e.g., ^[0-9]{10}$">
                    </div>
                `;
            }

            // Custom error message
            html += `
                <div class="ff-prop-group">
                    <label class="ff-prop-label">Custom Error Message</label>
                    <input type="text" class="ff-prop-input" data-prop="error_message" value="${this.escapeHtml(field.error_message || '')}" placeholder="This field is required">
                </div>
            `;

            return html;
        }

        /**
         * Render advanced properties
         */
        renderAdvancedProperties(field) {
            return `
                <div class="ff-prop-group">
                    <label class="ff-prop-label">CSS Classes</label>
                    <input type="text" class="ff-prop-input" data-prop="css_class" value="${this.escapeHtml(field.css_class || '')}" placeholder="custom-class another-class">
                </div>

                <div class="ff-prop-group">
                    <label class="ff-prop-label">Field ID</label>
                    <input type="text" class="ff-prop-input" data-prop="custom_id" value="${this.escapeHtml(field.custom_id || '')}" placeholder="Leave empty for auto-generated">
                </div>

                <div class="ff-prop-group">
                    <label class="ff-prop-label">Default Value</label>
                    <input type="text" class="ff-prop-input" data-prop="default_value" value="${this.escapeHtml(field.default_value || '')}">
                </div>

                <div class="ff-prop-group">
                    <label class="ff-prop-checkbox">
                        <input type="checkbox" data-prop="readonly" ${field.readonly ? 'checked' : ''}>
                        Read only
                    </label>
                </div>

                <div class="ff-prop-group">
                    <label class="ff-prop-checkbox">
                        <input type="checkbox" data-prop="disabled" ${field.disabled ? 'checked' : ''}>
                        Disabled
                    </label>
                </div>

                <div class="ff-prop-group">
                    <label class="ff-prop-label">Conditional Logic</label>
                    <button type="button" class="ff-btn ff-btn-secondary ff-btn-edit-conditions" data-field-id="${field.id}">
                        <span class="dashicons dashicons-randomize"></span>
                        ${this.fieldHasConditions(field.id) ? 'Edit Conditions' : 'Add Conditions'}
                    </button>
                </div>
            `;
        }

        /**
         * Initialize options editor for select/radio/checkbox fields
         */
        initOptionsEditor(field) {
            const $editor = this.container.find(`.ff-options-editor[data-field-id="${field.id}"]`);
            const $list = $editor.find('.ff-options-list');

            // Render existing options
            this.renderOptions($list, field);

            // Make options sortable
            $list.sortable({
                handle: '.ff-option-drag',
                placeholder: 'ff-option-placeholder',
                update: () => this.updateOptionsFromEditor($list, field)
            });

            // Add option button
            $editor.find('.ff-btn-add-option').on('click', () => {
                const newOption = {
                    value: 'option_' + (field.options.length + 1),
                    label: 'Option ' + (field.options.length + 1)
                };
                field.options.push(newOption);
                this.renderOptions($list, field);
                this.markDirty();
                this.renderStepContent();
            });
        }

        /**
         * Render options list
         */
        renderOptions($list, field) {
            let html = '';

            (field.options || []).forEach((opt, index) => {
                html += `
                    <div class="ff-option-item" data-index="${index}">
                        <span class="ff-option-drag dashicons dashicons-menu"></span>
                        <input type="text" class="ff-option-label" value="${this.escapeHtml(opt.label || '')}" placeholder="Label">
                        <input type="text" class="ff-option-value" value="${this.escapeHtml(opt.value || '')}" placeholder="Value">
                        <button type="button" class="ff-option-remove" title="Remove">&times;</button>
                    </div>
                `;
            });

            $list.html(html);

            // Bind events
            const self = this;
            $list.find('.ff-option-label, .ff-option-value').on('input', function() {
                self.updateOptionsFromEditor($list, field);
            });

            $list.find('.ff-option-remove').on('click', function() {
                const index = $(this).closest('.ff-option-item').data('index');
                field.options.splice(index, 1);
                self.renderOptions($list, field);
                self.markDirty();
                self.renderStepContent();
            });
        }

        /**
         * Update options from editor inputs
         */
        updateOptionsFromEditor($list, field) {
            field.options = [];

            $list.find('.ff-option-item').each(function() {
                field.options.push({
                    label: $(this).find('.ff-option-label').val(),
                    value: $(this).find('.ff-option-value').val()
                });
            });

            this.markDirty();
            this.renderStepContent();
        }

        /**
         * Update a field property from the properties panel
         */
        updateFieldProperty(e) {
            if (!this.selectedField) return;

            const $input = $(e.target);
            const prop = $input.data('prop');
            if (!prop) return;

            const step = this.schema.steps[this.currentStep];
            const field = step.fields.find(f => f.id === this.selectedField);
            if (!field) return;

            // Get value based on input type
            let value;
            if ($input.attr('type') === 'checkbox') {
                value = $input.is(':checked');
            } else if ($input.attr('type') === 'number') {
                value = $input.val() ? parseFloat($input.val()) : null;
            } else {
                value = $input.val();
            }

            field[prop] = value;

            // Update field name to be slug-friendly
            if (prop === 'name') {
                field.name = value.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
                $input.val(field.name);
            }

            this.markDirty();
            this.renderStepContent();
        }

        /**
         * Switch to a different step
         */
        switchStep(index) {
            if (index < 0 || index >= this.schema.steps.length) return;

            this.currentStep = index;
            this.deselectField();
            this.render();
        }

        /**
         * Add a new step
         */
        addStep() {
            const newStep = {
                id: 'step_' + this.generateId(),
                title: 'Step ' + (this.schema.steps.length + 1),
                description: '',
                fields: []
            };

            this.schema.steps.push(newStep);
            this.currentStep = this.schema.steps.length - 1;

            this.markDirty();
            this.saveHistory();
            this.render();
        }

        /**
         * Delete a step
         */
        deleteStep(index) {
            if (this.schema.steps.length <= 1) {
                alert('You must have at least one step.');
                return;
            }

            if (!confirm('Are you sure you want to delete this step and all its fields?')) {
                return;
            }

            // Remove conditions for all fields in this step
            const step = this.schema.steps[index];
            step.fields.forEach(field => this.removeFieldConditions(field.id));

            this.schema.steps.splice(index, 1);

            if (this.currentStep >= this.schema.steps.length) {
                this.currentStep = this.schema.steps.length - 1;
            }

            this.markDirty();
            this.saveHistory();
            this.render();
        }

        /**
         * Filter fields in palette by search term
         */
        filterFields(search) {
            const term = search.toLowerCase().trim();

            this.container.find('.ff-palette-field').each(function() {
                const label = $(this).find('.ff-field-label').text().toLowerCase();
                const type = $(this).data('type').toLowerCase();
                const visible = !term || label.includes(term) || type.includes(term);
                $(this).toggle(visible);
            });

            // Show/hide empty categories
            this.container.find('.ff-palette-category').each(function() {
                const hasVisible = $(this).find('.ff-palette-field:visible').length > 0;
                $(this).toggle(hasVisible);
            });
        }

        /**
         * Check if a field has conditions
         */
        fieldHasConditions(fieldId) {
            return (this.schema.conditions || []).some(c =>
                c.target_field === fieldId ||
                (c.conditions || []).some(cond => cond.field === fieldId)
            );
        }

        /**
         * Remove conditions for a field
         */
        removeFieldConditions(fieldId) {
            if (!this.schema.conditions) return;

            // Remove conditions targeting this field
            this.schema.conditions = this.schema.conditions.filter(c => c.target_field !== fieldId);

            // Remove this field from other conditions
            this.schema.conditions.forEach(rule => {
                if (rule.conditions) {
                    rule.conditions = rule.conditions.filter(c => c.field !== fieldId);
                }
            });

            // Clean up empty rules
            this.schema.conditions = this.schema.conditions.filter(c =>
                c.conditions && c.conditions.length > 0
            );
        }

        /**
         * Open conditions modal for a field
         */
        openConditionsModal(fieldId) {
            const $modal = this.container.find('.ff-modal-conditions');
            const $body = $modal.find('.ff-modal-body');

            // Get all fields for dropdown
            const allFields = [];
            this.schema.steps.forEach(step => {
                (step.fields || []).forEach(f => {
                    if (f.id !== fieldId) {
                        allFields.push({ id: f.id, label: f.label || f.name, type: f.type });
                    }
                });
            });

            // Get existing conditions for this field
            const existingRule = (this.schema.conditions || []).find(c => c.target_field === fieldId);
            const conditions = existingRule ? existingRule.conditions : [];

            let html = `
                <div class="ff-conditions-builder" data-target-field="${fieldId}">
                    <p class="ff-conditions-intro">
                        <strong>Show this field when:</strong>
                    </p>

                    <div class="ff-conditions-list">
                        ${conditions.length ? '' : '<div class="ff-condition-empty">No conditions added yet.</div>'}
                    </div>

                    <button type="button" class="ff-btn ff-btn-sm ff-btn-add-condition">
                        <span class="dashicons dashicons-plus"></span> Add Condition
                    </button>

                    ${conditions.length > 1 ? `
                        <div class="ff-conditions-logic">
                            <label>Match:</label>
                            <select class="ff-condition-logic-select">
                                <option value="all" ${existingRule?.logic === 'all' ? 'selected' : ''}>All conditions (AND)</option>
                                <option value="any" ${existingRule?.logic === 'any' ? 'selected' : ''}>Any condition (OR)</option>
                            </select>
                        </div>
                    ` : ''}

                    <div class="ff-conditions-action">
                        <label>Action when conditions are met:</label>
                        <select class="ff-condition-action-select">
                            <option value="show" ${!existingRule || existingRule.action === 'show' ? 'selected' : ''}>Show field</option>
                            <option value="hide" ${existingRule?.action === 'hide' ? 'selected' : ''}>Hide field</option>
                            <option value="enable" ${existingRule?.action === 'enable' ? 'selected' : ''}>Enable field</option>
                            <option value="disable" ${existingRule?.action === 'disable' ? 'selected' : ''}>Disable field</option>
                            <option value="require" ${existingRule?.action === 'require' ? 'selected' : ''}>Make required</option>
                        </select>
                    </div>
                </div>
            `;

            $body.html(html);

            // Render existing conditions
            const $list = $body.find('.ff-conditions-list');
            conditions.forEach((cond, index) => {
                $list.append(this.renderConditionRow(cond, index, allFields));
            });

            if (conditions.length) {
                $list.find('.ff-condition-empty').remove();
            }

            // Bind add condition
            $body.find('.ff-btn-add-condition').on('click', () => {
                $list.find('.ff-condition-empty').remove();
                const index = $list.find('.ff-condition-row').length;
                $list.append(this.renderConditionRow({}, index, allFields));
            });

            // Bind remove condition
            $body.on('click', '.ff-condition-remove', function() {
                $(this).closest('.ff-condition-row').remove();
                if ($list.find('.ff-condition-row').length === 0) {
                    $list.html('<div class="ff-condition-empty">No conditions added yet.</div>');
                }
            });

            // Update operator options when field changes
            $body.on('change', '.ff-condition-field', function() {
                const $row = $(this).closest('.ff-condition-row');
                const selectedField = allFields.find(f => f.id === $(this).val());
                if (selectedField) {
                    $row.find('.ff-condition-operator').html(
                        this.getOperatorOptions(selectedField.type)
                    );
                }
            }.bind(this));

            $modal.show();
        }

        /**
         * Render a condition row
         */
        renderConditionRow(condition, index, allFields) {
            const fieldOptions = allFields.map(f =>
                `<option value="${f.id}" ${condition.field === f.id ? 'selected' : ''}>${this.escapeHtml(f.label)}</option>`
            ).join('');

            const selectedField = allFields.find(f => f.id === condition.field);
            const operatorOptions = this.getOperatorOptions(selectedField?.type);

            return `
                <div class="ff-condition-row" data-index="${index}">
                    <select class="ff-condition-field">
                        <option value="">Select field...</option>
                        ${fieldOptions}
                    </select>
                    <select class="ff-condition-operator">
                        ${operatorOptions}
                    </select>
                    <input type="text" class="ff-condition-value" value="${this.escapeHtml(condition.value || '')}" placeholder="Value">
                    <button type="button" class="ff-condition-remove" title="Remove">&times;</button>
                </div>
            `;
        }

        /**
         * Get operator options based on field type
         */
        getOperatorOptions(fieldType) {
            const operators = [
                { value: 'equals', label: 'Equals' },
                { value: 'not_equals', label: 'Does not equal' },
                { value: 'contains', label: 'Contains' },
                { value: 'not_contains', label: 'Does not contain' },
                { value: 'is_empty', label: 'Is empty' },
                { value: 'is_not_empty', label: 'Is not empty' }
            ];

            // Add numeric operators for appropriate types
            if (['number', 'slider', 'rating'].includes(fieldType)) {
                operators.push(
                    { value: 'greater_than', label: 'Greater than' },
                    { value: 'less_than', label: 'Less than' },
                    { value: 'greater_equal', label: 'Greater or equal' },
                    { value: 'less_equal', label: 'Less or equal' }
                );
            }

            // Add checkbox operator
            if (['checkbox_single', 'checkbox'].includes(fieldType)) {
                operators.unshift({ value: 'is_checked', label: 'Is checked' });
            }

            return operators.map(op =>
                `<option value="${op.value}">${op.label}</option>`
            ).join('');
        }

        /**
         * Save conditions from modal
         */
        saveConditions() {
            const $builder = this.container.find('.ff-conditions-builder');
            const targetField = $builder.data('target-field');

            // Gather conditions
            const conditions = [];
            $builder.find('.ff-condition-row').each(function() {
                const field = $(this).find('.ff-condition-field').val();
                const operator = $(this).find('.ff-condition-operator').val();
                const value = $(this).find('.ff-condition-value').val();

                if (field) {
                    conditions.push({ field, operator, value });
                }
            });

            // Initialize conditions array if needed
            if (!this.schema.conditions) {
                this.schema.conditions = [];
            }

            // Remove existing rule for this field
            this.schema.conditions = this.schema.conditions.filter(c => c.target_field !== targetField);

            // Add new rule if there are conditions
            if (conditions.length > 0) {
                this.schema.conditions.push({
                    target_field: targetField,
                    action: $builder.find('.ff-condition-action-select').val() || 'show',
                    logic: $builder.find('.ff-condition-logic-select').val() || 'all',
                    conditions: conditions
                });
            }

            this.closeModals();
            this.markDirty();
            this.saveHistory();
            this.renderStepContent();
        }

        /**
         * Show preview modal
         */
        showPreview() {
            const $modal = this.container.find('.ff-modal-preview');
            const $frame = $modal.find('.ff-preview-frame');

            // Load preview via AJAX
            $frame.html('<div class="ff-preview-loading"><span class="dashicons dashicons-update ff-spin"></span> Loading preview...</div>');
            $modal.show();

            $.ajax({
                url: fffl_builder.ajax_url,
                type: 'POST',
                data: {
                    action: 'fffl_builder_preview',
                    nonce: fffl_builder.nonce,
                    schema: JSON.stringify(this.schema)
                },
                success: (response) => {
                    if (response.success) {
                        $frame.html(response.data.html);
                    } else {
                        $frame.html('<div class="ff-preview-error">Failed to load preview</div>');
                    }
                },
                error: () => {
                    $frame.html('<div class="ff-preview-error">Failed to load preview</div>');
                }
            });
        }

        /**
         * Close all modals
         */
        closeModals() {
            this.container.find('.ff-modal').hide();
        }

        /**
         * Save the form schema
         */
        save() {
            const $status = this.container.find('.ff-save-status');
            const $btn = this.container.find('.ff-btn-save');

            $status.text('Saving...').addClass('saving');
            $btn.prop('disabled', true);

            $.ajax({
                url: fffl_builder.ajax_url,
                type: 'POST',
                data: {
                    action: 'fffl_builder_save',
                    nonce: fffl_builder.nonce,
                    instance_id: this.instanceId,
                    schema: JSON.stringify(this.schema)
                },
                success: (response) => {
                    if (response.success) {
                        $status.text('Saved').removeClass('saving').addClass('saved');
                        this.isDirty = false;

                        setTimeout(() => {
                            $status.text('').removeClass('saved');
                        }, 2000);
                    } else {
                        $status.text('Save failed').removeClass('saving').addClass('error');
                        alert(response.data.message || 'Failed to save form');
                    }
                },
                error: () => {
                    $status.text('Save failed').removeClass('saving').addClass('error');
                    alert('Failed to save form. Please try again.');
                },
                complete: () => {
                    $btn.prop('disabled', false);
                }
            });
        }

        /**
         * Mark the form as dirty (unsaved changes)
         */
        markDirty() {
            this.isDirty = true;
            this.container.find('.ff-save-status').text('Unsaved changes').addClass('dirty');
        }

        /**
         * Save current state to history for undo/redo
         */
        saveHistory() {
            // Remove any redo states
            this.history = this.history.slice(0, this.historyIndex + 1);

            // Add current state
            this.history.push(JSON.stringify(this.schema));

            // Limit history size
            if (this.history.length > this.maxHistory) {
                this.history.shift();
            }

            this.historyIndex = this.history.length - 1;
            this.updateUndoRedoButtons();
        }

        /**
         * Undo last change
         */
        undo() {
            if (this.historyIndex <= 0) return;

            this.historyIndex--;
            this.schema = JSON.parse(this.history[this.historyIndex]);
            this.markDirty();
            this.render();
            this.updateUndoRedoButtons();
        }

        /**
         * Redo last undone change
         */
        redo() {
            if (this.historyIndex >= this.history.length - 1) return;

            this.historyIndex++;
            this.schema = JSON.parse(this.history[this.historyIndex]);
            this.markDirty();
            this.render();
            this.updateUndoRedoButtons();
        }

        /**
         * Update undo/redo button states
         */
        updateUndoRedoButtons() {
            this.container.find('.ff-btn-undo').prop('disabled', this.historyIndex <= 0);
            this.container.find('.ff-btn-redo').prop('disabled', this.historyIndex >= this.history.length - 1);
        }

        /**
         * Generate a unique ID
         */
        generateId() {
            return 'f' + Math.random().toString(36).substr(2, 9);
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(str) {
            if (str === null || str === undefined) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }

    // Export FormBuilder class
    window.FFFormBuilder.FormBuilder = FormBuilder;

    // Auto-initialize on page load if container exists
    $(document).ready(function() {
        if ($('#fffl-form-builder').length && typeof fffl_builder !== 'undefined') {
            window.FFFormBuilder.instance = new FormBuilder({
                container: '#fffl-form-builder',
                instanceId: fffl_builder.instance_id || 0,
                schema: fffl_builder.schema || null,
                fieldTypes: fffl_builder.field_types || {}
            });
        }
    });

})(jQuery);
