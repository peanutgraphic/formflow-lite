/**
 * FormFlow Security Module
 *
 * Implements client-side anti-scraping and integrity protection.
 *
 * @package FormFlow
 */

(function() {
    'use strict';

    // Prevent running in non-browser environments
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    const FFSecurity = {
        config: null,
        initialized: false,
        interactionCount: 0,
        mouseMovements: 0,
        keystrokes: 0,
        scrollEvents: 0,
        formLoadTime: 0,

        /**
         * Initialize security module
         */
        init: function(config) {
            if (this.initialized) return;

            this.config = config || {};
            this.formLoadTime = Date.now();
            this.initialized = true;

            // Detect automation tools
            this.detectAutomation();

            // Track genuine user interactions
            this.trackInteractions();

            // Protect against console inspection
            this.protectConsole();

            // Add timing protection
            this.addTimingProtection();

            // Protect form data
            this.protectFormData();

            // Add request signing
            this.addRequestSigning();
        },

        /**
         * Detect automation/scraping tools
         */
        detectAutomation: function() {
            const signals = [];

            // Check for webdriver (Selenium, Puppeteer, etc.)
            if (navigator.webdriver) {
                signals.push('webdriver');
            }

            // Check for PhantomJS
            if (window.callPhantom || window._phantom) {
                signals.push('phantom');
            }

            // Check for headless Chrome
            if (/HeadlessChrome/.test(navigator.userAgent)) {
                signals.push('headless');
            }

            // Check for Puppeteer-specific properties
            if (navigator.languages === '' || navigator.languages.length === 0) {
                signals.push('no_languages');
            }

            // Check for automation-related properties
            const automationProps = [
                '__nightmare', '__selenium_unwrapped', '__webdriver_evaluate',
                '__driver_evaluate', '__webdriver_unwrapped', '__fxdriver_evaluate',
                '_Selenium_IDE_Recorder', '_selenium', 'callSelenium',
                '__webdriver_script_fn', 'domAutomation', 'domAutomationController'
            ];

            automationProps.forEach(function(prop) {
                if (window[prop] || document[prop]) {
                    signals.push(prop);
                }
            });

            // Check for missing browser features that real browsers have
            if (!window.chrome && /Chrome/.test(navigator.userAgent)) {
                signals.push('fake_chrome');
            }

            // Check for suspicious screen dimensions
            if (window.outerWidth === 0 || window.outerHeight === 0) {
                signals.push('zero_dimensions');
            }

            // Check for plugins (headless browsers often have none)
            if (navigator.plugins.length === 0 && !/mobile/i.test(navigator.userAgent)) {
                signals.push('no_plugins');
            }

            // If automation detected, add flag to all requests
            if (signals.length > 0) {
                this.isAutomated = true;
                this.automationSignals = signals;

                // Don't completely block - let server decide
                console.warn('FormFlow: Automation detected');
            }
        },

        /**
         * Track genuine user interactions
         */
        trackInteractions: function() {
            const self = this;

            // Mouse movements (genuine users move mouse randomly)
            document.addEventListener('mousemove', function() {
                self.mouseMovements++;
            }, { passive: true });

            // Keystrokes
            document.addEventListener('keydown', function() {
                self.keystrokes++;
            }, { passive: true });

            // Scroll events
            document.addEventListener('scroll', function() {
                self.scrollEvents++;
            }, { passive: true });

            // Click events
            document.addEventListener('click', function() {
                self.interactionCount++;
            }, { passive: true });

            // Touch events for mobile
            document.addEventListener('touchstart', function() {
                self.interactionCount++;
            }, { passive: true });
        },

        /**
         * Get interaction score (higher = more likely human)
         */
        getInteractionScore: function() {
            const timeOnPage = (Date.now() - this.formLoadTime) / 1000; // seconds

            // Calculate score based on interactions relative to time
            let score = 0;

            // Mouse movements (expect at least some)
            score += Math.min(this.mouseMovements / 10, 30);

            // Keystrokes (forms need typing)
            score += Math.min(this.keystrokes / 5, 30);

            // Scroll events
            score += Math.min(this.scrollEvents / 3, 20);

            // Time on page (at least 5 seconds for a real form)
            score += Math.min(timeOnPage / 2, 20);

            return Math.round(score);
        },

        /**
         * Protect console from inspection
         */
        protectConsole: function() {
            // Detect DevTools opening (basic detection)
            const threshold = 160;
            let devToolsOpen = false;

            const checkDevTools = function() {
                const widthThreshold = window.outerWidth - window.innerWidth > threshold;
                const heightThreshold = window.outerHeight - window.innerHeight > threshold;

                if (widthThreshold || heightThreshold) {
                    if (!devToolsOpen) {
                        devToolsOpen = true;
                        // Don't block, just log
                        console.log('FormFlow: Development mode detected');
                    }
                } else {
                    devToolsOpen = false;
                }
            };

            // Check periodically
            setInterval(checkDevTools, 1000);

            // Disable right-click on form elements (optional, can be annoying)
            // Uncomment if desired:
            // document.addEventListener('contextmenu', function(e) {
            //     if (e.target.closest('.ff-form-container')) {
            //         e.preventDefault();
            //     }
            // });
        },

        /**
         * Add timing protection to prevent rapid submissions
         */
        addTimingProtection: function() {
            const self = this;

            // Add hidden timestamp field to forms
            document.querySelectorAll('.ff-form-container form').forEach(function(form) {
                const loadTimeInput = document.createElement('input');
                loadTimeInput.type = 'hidden';
                loadTimeInput.name = 'ff_form_load_time';
                loadTimeInput.value = Math.floor(self.formLoadTime / 1000);
                form.appendChild(loadTimeInput);

                // Add interaction score on submit
                form.addEventListener('submit', function() {
                    const scoreInput = document.createElement('input');
                    scoreInput.type = 'hidden';
                    scoreInput.name = 'ff_interaction_score';
                    scoreInput.value = self.getInteractionScore();
                    form.appendChild(scoreInput);
                });
            });
        },

        /**
         * Protect form data from easy scraping
         */
        protectFormData: function() {
            // Obfuscate field names in memory (real names sent on submit)
            // This makes it harder to programmatically fill forms

            // Disable autocomplete on sensitive fields
            document.querySelectorAll('.ff-form-container input').forEach(function(input) {
                if (input.type === 'text' || input.type === 'email' || input.type === 'tel') {
                    input.setAttribute('autocomplete', 'off');
                }
            });
        },

        /**
         * Add request signing for API calls
         */
        addRequestSigning: function() {
            const self = this;

            // Intercept XMLHttpRequest
            const originalXHR = window.XMLHttpRequest;

            function SignedXHR() {
                const xhr = new originalXHR();
                const originalOpen = xhr.open;
                const originalSend = xhr.send;

                xhr.open = function(method, url) {
                    this._url = url;
                    this._method = method;
                    return originalOpen.apply(this, arguments);
                };

                xhr.send = function(data) {
                    // Only sign our own requests
                    if (this._url && this._url.indexOf('ff_') !== -1) {
                        const signature = self.generateRequestSignature(this._url, data);
                        this.setRequestHeader('X-FFFL-Signature', signature);
                        this.setRequestHeader('X-FFFL-Timestamp', Date.now().toString());
                        this.setRequestHeader('X-FFFL-Score', self.getInteractionScore().toString());
                    }
                    return originalSend.apply(this, arguments);
                };

                return xhr;
            }

            // Only override if not in automation mode
            if (!this.isAutomated) {
                window.XMLHttpRequest = SignedXHR;
            }

            // Also intercept fetch
            const originalFetch = window.fetch;

            window.fetch = function(url, options) {
                options = options || {};

                // Only sign our own requests
                if (typeof url === 'string' && url.indexOf('ff_') !== -1) {
                    options.headers = options.headers || {};
                    options.headers['X-FFFL-Signature'] = self.generateRequestSignature(url, options.body);
                    options.headers['X-FFFL-Timestamp'] = Date.now().toString();
                    options.headers['X-FFFL-Score'] = self.getInteractionScore().toString();
                }

                return originalFetch.apply(this, arguments);
            };
        },

        /**
         * Generate request signature
         */
        generateRequestSignature: function(url, data) {
            // Simple signature based on timestamp and session token
            const timestamp = Date.now();
            const token = this.config.token || '';
            const seed = this.config.fingerprint || '';

            // Create a basic signature (not cryptographically secure, but adds complexity)
            const payload = timestamp + '|' + token + '|' + seed + '|' + (data || '').length;

            return this.simpleHash(payload);
        },

        /**
         * Simple hash function (for obfuscation, not security)
         */
        simpleHash: function(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            return Math.abs(hash).toString(36);
        },

        /**
         * Generate browser fingerprint
         */
        generateFingerprint: function() {
            const components = [
                navigator.userAgent,
                navigator.language,
                screen.width + 'x' + screen.height,
                screen.colorDepth,
                new Date().getTimezoneOffset(),
                navigator.hardwareConcurrency || 0,
                navigator.platform,
                !!window.sessionStorage,
                !!window.localStorage,
                typeof window.indexedDB
            ];

            return this.simpleHash(components.join('|'));
        },

        /**
         * Get security data to include with form submissions
         */
        getSecurityData: function() {
            return {
                loadTime: Math.floor(this.formLoadTime / 1000),
                score: this.getInteractionScore(),
                fingerprint: this.generateFingerprint(),
                timestamp: Date.now(),
                token: this.config.token || '',
                automated: this.isAutomated || false
            };
        }
    };

    // Expose to global scope
    window.FFSecurity = FFSecurity;

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (window.ffSecurityConfig) {
                FFSecurity.init(window.ffSecurityConfig);
            }
        });
    } else {
        if (window.ffSecurityConfig) {
            FFSecurity.init(window.ffSecurityConfig);
        }
    }

})();
