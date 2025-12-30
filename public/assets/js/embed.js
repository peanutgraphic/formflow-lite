/**
 * IntelliSource Forms - Embeddable Widget
 *
 * Lightweight script for embedding forms on external websites.
 * Supports both iframe and inline rendering modes.
 *
 * Usage:
 * <div id="ff-form-123" data-ff-token="abc123"></div>
 * <script src="https://yoursite.com/wp-content/plugins/intellisource-forms/public/assets/js/embed.js" async></script>
 *
 * @package IntelliSourceForms
 * @since 2.1.0
 */

(function() {
    'use strict';

    // Configuration
    var FF_EMBED = {
        version: '2.1.0',
        initialized: false,
        instances: {},
        baseUrl: null,
        defaultOptions: {
            mode: 'iframe', // 'iframe' or 'inline'
            height: 'auto',
            minHeight: 400,
            maxHeight: 2000,
            theme: 'light',
            locale: 'en',
            onReady: null,
            onSubmit: null,
            onError: null,
            onStepChange: null
        }
    };

    /**
     * Initialize the embed system
     */
    function init() {
        if (FF_EMBED.initialized) return;
        FF_EMBED.initialized = true;

        // Detect base URL from script tag
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].src || '';
            if (src.indexOf('embed.js') !== -1) {
                FF_EMBED.baseUrl = src.replace(/\/public\/assets\/js\/embed\.js.*$/, '');
                break;
            }
        }

        // Find and initialize all embed containers
        var containers = document.querySelectorAll('[data-ff-token]');
        for (var j = 0; j < containers.length; j++) {
            initContainer(containers[j]);
        }

        // Listen for dynamic containers
        observeDOM();
    }

    /**
     * Initialize a single embed container
     */
    function initContainer(container) {
        var token = container.getAttribute('data-ff-token');
        if (!token || FF_EMBED.instances[token]) return;

        var options = parseOptions(container);
        var instance = {
            token: token,
            container: container,
            options: options,
            iframe: null,
            loaded: false
        };

        FF_EMBED.instances[token] = instance;

        if (options.mode === 'iframe') {
            createIframe(instance);
        } else {
            createInlineForm(instance);
        }
    }

    /**
     * Parse options from data attributes
     */
    function parseOptions(container) {
        var options = Object.assign({}, FF_EMBED.defaultOptions);

        // Parse data attributes
        var attrs = container.attributes;
        for (var i = 0; i < attrs.length; i++) {
            var name = attrs[i].name;
            var value = attrs[i].value;

            if (name.indexOf('data-ff-') === 0) {
                var key = name.replace('data-ff-', '').replace(/-([a-z])/g, function(g) {
                    return g[1].toUpperCase();
                });
                if (key !== 'token') {
                    options[key] = value;
                }
            }
        }

        return options;
    }

    /**
     * Create iframe embed
     */
    function createIframe(instance) {
        var container = instance.container;
        var options = instance.options;

        // Create wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'ff-embed-wrapper';
        wrapper.style.cssText = 'position: relative; width: 100%; overflow: hidden;';

        // Create loading indicator
        var loader = document.createElement('div');
        loader.className = 'ff-embed-loader';
        loader.innerHTML = '<div class="ff-spinner"></div><span>Loading form...</span>';
        loader.style.cssText = 'display: flex; align-items: center; justify-content: center; padding: 40px; color: #666;';
        wrapper.appendChild(loader);

        // Create iframe
        var iframe = document.createElement('iframe');
        iframe.style.cssText = 'width: 100%; border: none; display: none; min-height: ' + options.minHeight + 'px;';
        iframe.setAttribute('allowfullscreen', 'true');
        iframe.setAttribute('loading', 'lazy');

        // Build iframe URL
        var iframeSrc = FF_EMBED.baseUrl + '/?ff_embed=1&token=' + encodeURIComponent(instance.token);
        if (options.locale) {
            iframeSrc += '&locale=' + encodeURIComponent(options.locale);
        }
        if (options.theme) {
            iframeSrc += '&theme=' + encodeURIComponent(options.theme);
        }

        iframe.src = iframeSrc;
        instance.iframe = iframe;

        // Handle load event
        iframe.onload = function() {
            instance.loaded = true;
            loader.style.display = 'none';
            iframe.style.display = 'block';

            if (typeof options.onReady === 'function') {
                options.onReady(instance);
            }
        };

        wrapper.appendChild(iframe);
        container.appendChild(wrapper);

        // Add styles
        addEmbedStyles();

        // Listen for messages from iframe
        window.addEventListener('message', function(event) {
            handleIframeMessage(event, instance);
        });
    }

    /**
     * Handle messages from iframe
     */
    function handleIframeMessage(event, instance) {
        var data = event.data;
        if (!data || typeof data !== 'object') return;

        switch (data.type) {
            case 'ff-resize':
                if (instance.iframe && data.height) {
                    var height = Math.min(
                        Math.max(data.height, instance.options.minHeight),
                        instance.options.maxHeight
                    );
                    instance.iframe.style.height = height + 'px';
                }
                break;

            case 'ff-submit':
                if (typeof instance.options.onSubmit === 'function') {
                    instance.options.onSubmit(data.data, instance);
                }
                break;

            case 'ff-error':
                if (typeof instance.options.onError === 'function') {
                    instance.options.onError(data.error, instance);
                }
                break;

            case 'ff-step':
                if (typeof instance.options.onStepChange === 'function') {
                    instance.options.onStepChange(data.step, instance);
                }
                break;
        }
    }

    /**
     * Create inline form (fetches config and renders)
     */
    function createInlineForm(instance) {
        var container = instance.container;
        var options = instance.options;

        // Show loading
        container.innerHTML = '<div class="ff-inline-loader">Loading form...</div>';

        // Fetch configuration
        fetch(FF_EMBED.baseUrl + '/wp-json/fffl/v1/embed/config/' + instance.token)
            .then(function(response) {
                if (!response.ok) throw new Error('Failed to load form configuration');
                return response.json();
            })
            .then(function(config) {
                instance.config = config;
                renderInlineForm(instance);
            })
            .catch(function(error) {
                container.innerHTML = '<div class="ff-embed-error">Failed to load form: ' + error.message + '</div>';
                if (typeof options.onError === 'function') {
                    options.onError(error, instance);
                }
            });
    }

    /**
     * Render inline form
     */
    function renderInlineForm(instance) {
        var container = instance.container;
        var config = instance.config;

        // Build form HTML
        var html = '<div class="ff-inline-form" data-instance="' + config.instance_id + '">';

        // Header
        if (config.branding.logo_url) {
            html += '<div class="ff-inline-header">';
            html += '<img src="' + escapeHtml(config.branding.logo_url) + '" alt="" class="ff-inline-logo">';
            html += '</div>';
        }

        // Form title
        html += '<h2 class="ff-inline-title">' + escapeHtml(config.branding.form_title || config.name) + '</h2>';

        // Progress bar
        html += '<div class="ff-progress">';
        html += '<div class="ff-progress-bar"><div class="ff-progress-fill" style="width: 20%"></div></div>';
        html += '<div class="ff-progress-steps">';
        for (var i = 1; i <= 5; i++) {
            html += '<div class="ff-progress-step' + (i === 1 ? ' active' : '') + '">' + i + '</div>';
        }
        html += '</div></div>';

        // Form container
        html += '<div class="ff-inline-content" id="ff-step-content"></div>';

        // Navigation
        html += '<div class="ff-inline-nav">';
        html += '<button type="button" class="ff-btn ff-btn-back" style="display:none">Back</button>';
        html += '<button type="button" class="ff-btn ff-btn-next ff-btn-primary">Continue</button>';
        html += '</div>';

        // Powered by
        if (config.branding.powered_by) {
            html += '<div class="ff-powered-by">' + escapeHtml(config.branding.powered_by) + '</div>';
        }

        html += '</div>';

        container.innerHTML = html;

        // Apply branding colors
        var style = document.createElement('style');
        style.textContent = '.ff-inline-form { --ff-primary: ' + config.branding.primary_color + '; }';
        container.appendChild(style);

        // Initialize form logic
        initInlineFormLogic(instance);

        // Trigger ready callback
        if (typeof instance.options.onReady === 'function') {
            instance.options.onReady(instance);
        }
    }

    /**
     * Initialize inline form logic
     */
    function initInlineFormLogic(instance) {
        var container = instance.container;
        var config = instance.config;
        var currentStep = 1;
        var formData = {};

        var contentEl = container.querySelector('#ff-step-content');
        var backBtn = container.querySelector('.ff-btn-back');
        var nextBtn = container.querySelector('.ff-btn-next');

        // Load first step
        loadStep(1);

        // Navigation handlers
        backBtn.addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                loadStep(currentStep);
            }
        });

        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                collectStepData(currentStep);
                if (currentStep < 5) {
                    currentStep++;
                    loadStep(currentStep);
                } else {
                    submitForm();
                }
            }
        });

        function loadStep(step) {
            currentStep = step;
            updateProgress(step);
            backBtn.style.display = step > 1 ? 'inline-block' : 'none';
            nextBtn.textContent = step === 5 ? 'Submit' : 'Continue';

            // Render step content
            contentEl.innerHTML = getStepContent(step);

            // Trigger step change callback
            if (typeof instance.options.onStepChange === 'function') {
                instance.options.onStepChange(step, instance);
            }
        }

        function updateProgress(step) {
            var fill = container.querySelector('.ff-progress-fill');
            var steps = container.querySelectorAll('.ff-progress-step');

            fill.style.width = (step * 20) + '%';

            steps.forEach(function(el, i) {
                el.classList.toggle('active', i < step);
                el.classList.toggle('current', i === step - 1);
            });
        }

        function getStepContent(step) {
            // Simplified step content - in production, this would be fetched from server
            var steps = {
                1: '<div class="ff-step-content">' +
                   '<h3>Program Selection</h3>' +
                   '<div class="ff-field"><label>Device Type</label>' +
                   '<select name="device_type" class="ff-input">' +
                   '<option value="thermostat">Smart Thermostat</option>' +
                   '<option value="dcu">Outdoor Cycling Switch</option>' +
                   '</select></div></div>',

                2: '<div class="ff-step-content">' +
                   '<h3>Account Validation</h3>' +
                   '<div class="ff-field"><label>Account Number</label>' +
                   '<input type="text" name="account_number" class="ff-input" required></div>' +
                   '<div class="ff-field"><label>ZIP Code</label>' +
                   '<input type="text" name="zip" class="ff-input" maxlength="5" required></div></div>',

                3: '<div class="ff-step-content">' +
                   '<h3>Your Information</h3>' +
                   '<div class="ff-field-row">' +
                   '<div class="ff-field"><label>First Name</label><input type="text" name="first_name" class="ff-input" required></div>' +
                   '<div class="ff-field"><label>Last Name</label><input type="text" name="last_name" class="ff-input" required></div>' +
                   '</div>' +
                   '<div class="ff-field"><label>Email</label><input type="email" name="email" class="ff-input" required></div>' +
                   '<div class="ff-field"><label>Phone</label><input type="tel" name="phone" class="ff-input" required></div></div>',

                4: '<div class="ff-step-content">' +
                   '<h3>Schedule Installation</h3>' +
                   '<div class="ff-field"><label>Preferred Date</label>' +
                   '<input type="date" name="schedule_date" class="ff-input"></div>' +
                   '<div class="ff-field"><label>Preferred Time</label>' +
                   '<select name="schedule_time" class="ff-input">' +
                   '<option value="AM">Morning (8am-12pm)</option>' +
                   '<option value="PM">Afternoon (12pm-5pm)</option>' +
                   '</select></div></div>',

                5: '<div class="ff-step-content">' +
                   '<h3>Confirm & Submit</h3>' +
                   '<div class="ff-summary" id="ff-summary"></div>' +
                   '<div class="ff-field"><label>' +
                   '<input type="checkbox" name="terms" required> I agree to the terms and conditions' +
                   '</label></div></div>'
            };

            return steps[step] || '';
        }

        function validateStep(step) {
            var inputs = contentEl.querySelectorAll('[required]');
            var valid = true;

            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('ff-error');
                    valid = false;
                } else {
                    input.classList.remove('ff-error');
                }
            });

            return valid;
        }

        function collectStepData(step) {
            var inputs = contentEl.querySelectorAll('input, select, textarea');
            inputs.forEach(function(input) {
                var name = input.name;
                if (name) {
                    if (input.type === 'checkbox') {
                        formData[name] = input.checked;
                    } else {
                        formData[name] = input.value;
                    }
                }
            });
        }

        function submitForm() {
            nextBtn.disabled = true;
            nextBtn.textContent = 'Submitting...';

            fetch(config.endpoints.submit, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Embed-Token': instance.token,
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify(formData)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(result) {
                if (result.success) {
                    contentEl.innerHTML = '<div class="ff-success">' +
                        '<h3>Thank You!</h3>' +
                        '<p>Your enrollment has been submitted successfully.</p>' +
                        (result.confirmation_number ? '<p>Confirmation: ' + result.confirmation_number + '</p>' : '') +
                        '</div>';
                    backBtn.style.display = 'none';
                    nextBtn.style.display = 'none';

                    if (typeof instance.options.onSubmit === 'function') {
                        instance.options.onSubmit(result, instance);
                    }
                } else {
                    throw new Error(result.message || 'Submission failed');
                }
            })
            .catch(function(error) {
                nextBtn.disabled = false;
                nextBtn.textContent = 'Submit';
                alert('Error: ' + error.message);

                if (typeof instance.options.onError === 'function') {
                    instance.options.onError(error, instance);
                }
            });
        }
    }

    /**
     * Add embed styles
     */
    function addEmbedStyles() {
        if (document.getElementById('ff-embed-styles')) return;

        var style = document.createElement('style');
        style.id = 'ff-embed-styles';
        style.textContent = [
            '.ff-embed-wrapper { background: #f9f9f9; border-radius: 8px; }',
            '.ff-embed-loader { display: flex; flex-direction: column; align-items: center; gap: 10px; }',
            '.ff-spinner { width: 30px; height: 30px; border: 3px solid #e0e0e0; border-top-color: #4F46E5; border-radius: 50%; animation: ff-spin 1s linear infinite; }',
            '@keyframes ff-spin { to { transform: rotate(360deg); } }',
            '.ff-inline-form { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }',
            '.ff-inline-header { text-align: center; margin-bottom: 20px; }',
            '.ff-inline-logo { max-height: 50px; }',
            '.ff-inline-title { text-align: center; margin: 0 0 20px; font-size: 24px; color: #333; }',
            '.ff-progress { margin-bottom: 30px; }',
            '.ff-progress-bar { height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden; }',
            '.ff-progress-fill { height: 100%; background: var(--ff-primary, #4F46E5); transition: width 0.3s; }',
            '.ff-progress-steps { display: flex; justify-content: space-between; margin-top: 10px; }',
            '.ff-progress-step { width: 28px; height: 28px; border-radius: 50%; background: #e0e0e0; color: #666; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; }',
            '.ff-progress-step.active { background: var(--ff-primary, #4F46E5); color: #fff; }',
            '.ff-field { margin-bottom: 20px; }',
            '.ff-field label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }',
            '.ff-field-row { display: flex; gap: 15px; }',
            '.ff-field-row .ff-field { flex: 1; }',
            '.ff-input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; transition: border-color 0.2s; }',
            '.ff-input:focus { outline: none; border-color: var(--ff-primary, #4F46E5); }',
            '.ff-input.ff-error { border-color: #dc3545; }',
            '.ff-inline-nav { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }',
            '.ff-btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background 0.2s; }',
            '.ff-btn-back { background: #f0f0f0; color: #666; }',
            '.ff-btn-back:hover { background: #e0e0e0; }',
            '.ff-btn-primary { background: var(--ff-primary, #4F46E5); color: #fff; }',
            '.ff-btn-primary:hover { opacity: 0.9; }',
            '.ff-btn:disabled { opacity: 0.6; cursor: not-allowed; }',
            '.ff-powered-by { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }',
            '.ff-success { text-align: center; padding: 40px 20px; }',
            '.ff-success h3 { color: #28a745; margin-bottom: 10px; }',
            '.ff-embed-error { padding: 20px; text-align: center; color: #dc3545; }'
        ].join('\n');

        document.head.appendChild(style);
    }

    /**
     * Observe DOM for dynamically added containers
     */
    function observeDOM() {
        if (!window.MutationObserver) return;

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.hasAttribute('data-ff-token')) {
                            initContainer(node);
                        }
                        var nested = node.querySelectorAll('[data-ff-token]');
                        nested.forEach(initContainer);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Escape HTML
     */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Public API
     */
    window.FFEmbed = {
        version: FF_EMBED.version,
        init: init,
        getInstance: function(token) {
            return FF_EMBED.instances[token] || null;
        },
        getInstances: function() {
            return Object.assign({}, FF_EMBED.instances);
        },
        destroy: function(token) {
            var instance = FF_EMBED.instances[token];
            if (instance) {
                instance.container.innerHTML = '';
                delete FF_EMBED.instances[token];
            }
        }
    };

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
