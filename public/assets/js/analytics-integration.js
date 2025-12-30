/**
 * FormFlow Lite - Analytics Integration
 *
 * Pushes form events to Google Tag Manager dataLayer.
 * Integrates with GA4 and Microsoft Clarity for tracking.
 */

(function($, window) {
    'use strict';

    // Ensure dataLayer exists
    window.dataLayer = window.dataLayer || [];

    var FFAnalytics = {

        // Configuration (set from PHP)
        config: {
            enabled: true,
            gtmEnabled: false,
            gtmContainerId: '',
            ga4MeasurementId: '',
            clarityProjectId: '',
            visitorId: '',
            instanceSlug: '',
            instanceId: 0,
            debug: false
        },

        // Track if form has been started
        formStarted: false,

        /**
         * Initialize analytics
         */
        init: function(config) {
            if (config) {
                this.config = $.extend({}, this.config, config);
            }

            if (!this.config.enabled) {
                return;
            }

            this.bindEvents();

            // Track initial page view if form is present
            if ($('.ff-form-container').length) {
                this.trackFormView();
            }

            if (this.config.debug) {
                console.log('ISF Analytics initialized', this.config);
            }
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // Track form start (first field interaction)
            $(document).on('focus', '.ff-step-form input, .ff-step-form select, .ff-step-form textarea', function() {
                if (!self.formStarted) {
                    self.formStarted = true;
                    self.trackFormStart();
                }
            });

            // Track step changes
            $(document).on('ff:stepLoaded', function(e, data) {
                self.trackFormStep(data.step, data.stepName || 'step_' + data.step);
            });

            // Track form completion
            $(document).on('ff:completed', function(e, data) {
                self.trackFormComplete(data);
            });

            // Track handoff clicks
            $(document).on('click', '.ff-handoff-button, [data-ff-handoff]', function(e) {
                var $btn = $(this);
                self.trackHandoff({
                    destination: $btn.data('destination') || $btn.attr('href'),
                    buttonText: $btn.text()
                });
            });

            // Track validation errors
            $(document).on('ff:validationError', function(e, data) {
                self.trackValidationError(data);
            });
        },

        /**
         * Push event to dataLayer
         */
        pushEvent: function(eventName, eventData) {
            if (!this.config.enabled) {
                return;
            }

            var data = $.extend({
                event: eventName,
                ff_visitor_id: this.config.visitorId,
                ff_instance: this.config.instanceSlug,
                ff_instance_id: this.config.instanceId,
                ff_timestamp: new Date().toISOString()
            }, eventData);

            // Push to dataLayer
            window.dataLayer.push(data);

            if (this.config.debug) {
                console.log('ISF Analytics Event:', eventName, data);
            }

            // Also send to server for touch recording
            this.recordTouch(eventName, eventData);
        },

        /**
         * Track form view
         */
        trackFormView: function() {
            this.pushEvent('ff_form_view', {
                ff_event_category: 'form',
                ff_event_action: 'view'
            });
        },

        /**
         * Track form start
         */
        trackFormStart: function() {
            this.pushEvent('ff_form_start', {
                ff_event_category: 'form',
                ff_event_action: 'start'
            });
        },

        /**
         * Track form step transition
         */
        trackFormStep: function(step, stepName) {
            this.pushEvent('ff_form_step', {
                ff_event_category: 'form',
                ff_event_action: 'step',
                ff_step: step,
                ff_step_name: stepName
            });
        },

        /**
         * Track form completion
         */
        trackFormComplete: function(data) {
            data = data || {};

            this.pushEvent('ff_form_complete', {
                ff_event_category: 'form',
                ff_event_action: 'complete',
                ff_device_type: data.deviceType || '',
                ff_appointment_scheduled: data.appointmentScheduled || false
            });

            // GA4 conversion event
            if (this.config.ga4MeasurementId) {
                this.pushEvent('conversion', {
                    send_to: this.config.ga4MeasurementId,
                    value: 1,
                    currency: 'USD'
                });
            }
        },

        /**
         * Track handoff to external system
         */
        trackHandoff: function(data) {
            data = data || {};

            this.pushEvent('ff_handoff', {
                ff_event_category: 'handoff',
                ff_event_action: 'click',
                ff_destination: data.destination || '',
                ff_button_text: data.buttonText || ''
            });
        },

        /**
         * Track validation error
         */
        trackValidationError: function(data) {
            data = data || {};

            this.pushEvent('ff_validation_error', {
                ff_event_category: 'form',
                ff_event_action: 'error',
                ff_field_name: data.fieldName || '',
                ff_error_message: data.errorMessage || '',
                ff_step: data.step || 0
            });
        },

        /**
         * Track custom event
         */
        trackCustomEvent: function(eventName, data) {
            this.pushEvent('ff_' + eventName, $.extend({
                ff_event_category: 'custom',
                ff_event_action: eventName
            }, data));
        },

        /**
         * Record touch on server
         */
        recordTouch: function(eventName, data) {
            // Only record certain events as touches
            var touchEvents = ['ff_form_view', 'ff_form_start', 'ff_form_complete', 'ff_handoff'];

            if (touchEvents.indexOf(eventName) === -1) {
                return;
            }

            // Map event names to touch types
            var touchTypeMap = {
                'ff_form_view': 'form_view',
                'ff_form_start': 'form_start',
                'ff_form_complete': 'form_complete',
                'ff_handoff': 'handoff'
            };

            var touchType = touchTypeMap[eventName];
            if (!touchType) {
                return;
            }

            // Send to server via AJAX
            if (typeof fffl_frontend !== 'undefined' && fffl_frontend.ajax_url) {
                $.ajax({
                    url: fffl_frontend.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fffl_record_touch',
                        nonce: fffl_frontend.nonce,
                        touch_type: touchType,
                        instance_id: this.config.instanceId,
                        extra_data: JSON.stringify(data)
                    },
                    // Fire and forget - don't wait for response
                    timeout: 5000
                });
            }
        },

        /**
         * Get current visitor ID
         */
        getVisitorId: function() {
            return this.config.visitorId;
        },

        /**
         * Set visitor ID (if updated)
         */
        setVisitorId: function(visitorId) {
            this.config.visitorId = visitorId;
        },

        /**
         * Initialize Microsoft Clarity
         */
        initClarity: function() {
            if (!this.config.clarityProjectId) {
                return;
            }

            // Clarity script loader
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", this.config.clarityProjectId);

            // Set custom dimensions
            if (window.clarity) {
                window.clarity('set', 'ff_visitor_id', this.config.visitorId);
                window.clarity('set', 'ff_instance', this.config.instanceSlug);
            }
        },

        /**
         * Get attribution data from URL
         */
        getUrlAttribution: function() {
            var params = new URLSearchParams(window.location.search);
            var attribution = {};

            var trackParams = [
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'gclid', 'fbclid', 'msclkid', 'promo'
            ];

            trackParams.forEach(function(param) {
                if (params.has(param)) {
                    attribution[param] = params.get(param);
                }
            });

            return attribution;
        },

        /**
         * Expose attribution to dataLayer
         */
        exposeAttribution: function() {
            var attribution = this.getUrlAttribution();

            if (Object.keys(attribution).length > 0) {
                window.dataLayer.push({
                    event: 'ff_attribution_captured',
                    ff_attribution: attribution
                });
            }
        }
    };

    // Export to window
    window.FFAnalytics = FFAnalytics;

    // Auto-initialize when DOM ready
    $(document).ready(function() {
        // Config is injected by PHP via wp_localize_script
        if (typeof ff_analytics_config !== 'undefined') {
            FFAnalytics.init(ff_analytics_config);

            // Initialize Clarity if configured
            if (ff_analytics_config.clarityProjectId) {
                FFAnalytics.initClarity();
            }

            // Expose attribution from URL
            FFAnalytics.exposeAttribution();
        } else {
            // Basic initialization without config
            FFAnalytics.init({
                enabled: true,
                debug: false
            });
        }
    });

})(jQuery, window);
