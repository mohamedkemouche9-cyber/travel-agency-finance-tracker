/* ============================================================================
 * validate.js - client-side form validation.
 * ----------------------------------------------------------------------------
 * Gives the user instant feedback before the form is submitted. It mirrors
 * the server-side rules in app/Core/Validator.php - the server ALWAYS
 * re-validates, because client-side checks can be bypassed.
 * Applies to any <form data-validate>.
 * ========================================================================== */
(function () {
    'use strict';

    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    /** Rule check for a single field; returns an error string or ''. */
    function checkField(input) {
        var v = input.value.trim();

        if (input.hasAttribute('required') && v === '') {
            return 'This field is required.';
        }
        if (v === '') return '';

        if (input.type === 'email' && !EMAIL_RE.test(v)) {
            return 'Please enter a valid e-mail address.';
        }
        if (input.minLength > 0 && v.length < input.minLength) {
            return 'Must be at least ' + input.minLength + ' characters.';
        }
        if (input.pattern) {
            try {
                if (!(new RegExp('^(?:' + input.pattern + ')$')).test(v)) {
                    return input.id === 'code'
                        ? 'The code must be exactly 6 digits.'
                        : 'The format is not valid.';
                }
            } catch (e) { /* ignore a bad pattern */ }
        }
        if (input.type === 'number') {
            var num = parseFloat(v);
            if (isNaN(num)) return 'Please enter a number.';
            if (input.min !== '' && num < parseFloat(input.min)) {
                return 'Must be at least ' + input.min + '.';
            }
            if (input.max !== '' && num > parseFloat(input.max)) {
                return 'Must be at most ' + input.max + '.';
            }
        }
        if (input.type === 'date' && input.max && v > input.max) {
            return 'The date cannot be in the future.';
        }

        // Password strength (same rule as the server)
        if ((input.name === 'password' || input.name === 'new_password')
            && (v.length < 8 || !/[A-Za-z]/.test(v) || !/\d/.test(v))) {
            return 'At least 8 characters with letters and numbers.';
        }

        // Confirmation must match its partner field
        if (input.name === 'password_confirmation' || input.name === 'new_password_confirmation') {
            var partner = input.form.querySelector('[name="password"], [name="new_password"]');
            if (partner && v !== partner.value) {
                return 'The two passwords do not match.';
            }
        }

        return '';
    }

    /** Show or clear the error message under a field. */
    function render(input, error) {
        var group = input.closest('.form-group') || input.parentElement;
        if (!group) return;

        var span = group.querySelector('.field-error.js');
        if (error) {
            if (!span) {
                span = document.createElement('span');
                span.className = 'field-error js';
                group.appendChild(span);
            }
            span.textContent = error;
            group.classList.add('has-error');
        } else {
            if (span) span.remove();
            // keep server-side errors visible until the value changes
            if (!group.querySelector('.field-error:not(.js)')) {
                group.classList.remove('has-error');
            }
        }
    }

    /* -------------------- wire up every data-validate form ---------------- */
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var fields = form.querySelectorAll('input:not([type="hidden"]):not([type="radio"]), select');

        fields.forEach(function (input) {
            // validate when the user leaves the field...
            input.addEventListener('blur', function () {
                render(input, checkField(input));
            });
            // ...and clear the message as soon as they fix it
            input.addEventListener('input', function () {
                var group = input.closest('.form-group');
                if (group && group.classList.contains('has-error')) {
                    render(input, checkField(input));
                }
                var serverError = group && group.querySelector('.field-error:not(.js)');
                if (serverError) serverError.remove();
            });
        });

        form.addEventListener('submit', function (e) {
            var firstBad = null;
            fields.forEach(function (input) {
                var error = checkField(input);
                render(input, error);
                if (error && !firstBad) firstBad = input;
            });
            if (firstBad) {
                e.preventDefault();
                firstBad.focus();
            }
        });
    });

    /* ------------------------ password strength meter --------------------- */
    document.querySelectorAll('input[name="password"], input[name="new_password"]').forEach(function (input) {
        var meter = input.closest('.form-group');
        meter = meter && meter.querySelector('.pw-meter span');
        if (!meter) return;

        input.addEventListener('input', function () {
            var v = input.value;
            var score = 0;
            if (v.length >= 8)            score++;
            if (v.length >= 12)           score++;
            if (/[A-Za-z]/.test(v) && /\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v))   score++;

            var widths = ['0%', '25%', '50%', '75%', '100%'];
            var colors = ['#e2e8f0', '#ef4444', '#f59e0b', '#10b981', '#059669'];
            meter.style.width = widths[score];
            meter.style.background = colors[score];
        });
    });

    /* --------------- little hints under inputs (data-hint) ---------------- */
    document.querySelectorAll('[data-hint]').forEach(function (input) {
        var group = input.closest('.form-group');
        if (!group || group.querySelector('.js-hint')) return;
        var hint = document.createElement('span');
        hint.className = 'js-hint';
        hint.textContent = input.getAttribute('data-hint');
        group.appendChild(hint);
    });
})();
