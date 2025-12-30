/**
 * IntelliSource Forms - Validation Utilities
 *
 * Client-side validation helpers
 */

(function(window) {
    'use strict';

    var ISFValidation = {
        /**
         * Validate email format
         *
         * @param {string} email Email address to validate.
         * @return {boolean} True if valid.
         */
        isValidEmail: function(email) {
            if (!email) return false;
            var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return pattern.test(email);
        },

        /**
         * Validate phone number (10 digits)
         *
         * @param {string} phone Phone number to validate.
         * @return {boolean} True if valid.
         */
        isValidPhone: function(phone) {
            if (!phone) return false;
            var digits = phone.replace(/\D/g, '');
            return digits.length === 10;
        },

        /**
         * Validate ZIP code (5 digits)
         *
         * @param {string} zip ZIP code to validate.
         * @return {boolean} True if valid.
         */
        isValidZip: function(zip) {
            if (!zip) return false;
            return /^\d{5}$/.test(zip);
        },

        /**
         * Validate required field
         *
         * @param {string} value Field value.
         * @return {boolean} True if not empty.
         */
        isRequired: function(value) {
            return value !== null && value !== undefined && value.toString().trim() !== '';
        },

        /**
         * Validate minimum length
         *
         * @param {string} value Field value.
         * @param {number} minLength Minimum length.
         * @return {boolean} True if valid.
         */
        minLength: function(value, minLength) {
            if (!value) return false;
            return value.toString().trim().length >= minLength;
        },

        /**
         * Validate maximum length
         *
         * @param {string} value Field value.
         * @param {number} maxLength Maximum length.
         * @return {boolean} True if valid.
         */
        maxLength: function(value, maxLength) {
            if (!value) return true;
            return value.toString().trim().length <= maxLength;
        },

        /**
         * Validate that two values match
         *
         * @param {string} value1 First value.
         * @param {string} value2 Second value.
         * @return {boolean} True if they match.
         */
        matches: function(value1, value2) {
            return value1 === value2;
        },

        /**
         * Format phone number as (XXX) XXX-XXXX
         *
         * @param {string} phone Raw phone number.
         * @return {string} Formatted phone number.
         */
        formatPhone: function(phone) {
            if (!phone) return '';
            var digits = phone.replace(/\D/g, '');
            if (digits.length === 0) return '';

            var formatted = '';
            if (digits.length > 0) {
                formatted = '(' + digits.substring(0, 3);
            }
            if (digits.length >= 3) {
                formatted += ') ' + digits.substring(3, 6);
            }
            if (digits.length >= 6) {
                formatted += '-' + digits.substring(6, 10);
            }
            return formatted;
        },

        /**
         * Strip phone formatting to digits only
         *
         * @param {string} phone Formatted phone number.
         * @return {string} Digits only.
         */
        stripPhone: function(phone) {
            if (!phone) return '';
            return phone.replace(/\D/g, '');
        },

        /**
         * Validate a form and return errors
         *
         * @param {HTMLFormElement} form Form element.
         * @param {object} rules Validation rules.
         * @return {object} Errors object.
         */
        validateForm: function(form, rules) {
            var errors = {};
            var self = this;

            Object.keys(rules).forEach(function(fieldName) {
                var field = form.querySelector('[name="' + fieldName + '"]');
                if (!field) return;

                var value = field.value;
                var fieldRules = rules[fieldName];

                fieldRules.forEach(function(rule) {
                    // Skip if already has error
                    if (errors[fieldName]) return;

                    switch (rule.type) {
                        case 'required':
                            if (!self.isRequired(value)) {
                                errors[fieldName] = rule.message || 'This field is required.';
                            }
                            break;

                        case 'email':
                            if (value && !self.isValidEmail(value)) {
                                errors[fieldName] = rule.message || 'Please enter a valid email address.';
                            }
                            break;

                        case 'phone':
                            if (value && !self.isValidPhone(value)) {
                                errors[fieldName] = rule.message || 'Please enter a valid phone number.';
                            }
                            break;

                        case 'zip':
                            if (value && !self.isValidZip(value)) {
                                errors[fieldName] = rule.message || 'Please enter a valid ZIP code.';
                            }
                            break;

                        case 'minLength':
                            if (value && !self.minLength(value, rule.value)) {
                                errors[fieldName] = rule.message || 'Minimum length is ' + rule.value + ' characters.';
                            }
                            break;

                        case 'maxLength':
                            if (!self.maxLength(value, rule.value)) {
                                errors[fieldName] = rule.message || 'Maximum length is ' + rule.value + ' characters.';
                            }
                            break;

                        case 'matches':
                            var matchField = form.querySelector('[name="' + rule.field + '"]');
                            if (matchField && !self.matches(value, matchField.value)) {
                                errors[fieldName] = rule.message || 'Fields do not match.';
                            }
                            break;

                        case 'pattern':
                            if (value && !rule.pattern.test(value)) {
                                errors[fieldName] = rule.message || 'Invalid format.';
                            }
                            break;
                    }
                });
            });

            return errors;
        },

        /**
         * Display field errors
         *
         * @param {HTMLFormElement} form Form element.
         * @param {object} errors Errors object.
         */
        displayErrors: function(form, errors) {
            // Clear existing errors
            form.querySelectorAll('.ff-field-error').forEach(function(el) {
                el.remove();
            });
            form.querySelectorAll('.ff-input-error').forEach(function(el) {
                el.classList.remove('ff-input-error');
            });

            // Display new errors
            Object.keys(errors).forEach(function(fieldName) {
                var field = form.querySelector('[name="' + fieldName + '"]');
                if (!field) return;

                field.classList.add('ff-input-error');

                var errorEl = document.createElement('p');
                errorEl.className = 'ff-field-error';
                errorEl.textContent = errors[fieldName];

                var parent = field.closest('.ff-field');
                if (parent) {
                    parent.appendChild(errorEl);
                }
            });
        },

        /**
         * Clear all field errors
         *
         * @param {HTMLFormElement} form Form element.
         */
        clearErrors: function(form) {
            form.querySelectorAll('.ff-field-error').forEach(function(el) {
                el.remove();
            });
            form.querySelectorAll('.ff-input-error').forEach(function(el) {
                el.classList.remove('ff-input-error');
            });
        }
    };

    // Export to window
    window.ISFValidation = ISFValidation;

})(window);
