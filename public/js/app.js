/* ============================================================================
 * app.js - small UI behaviours shared by every page.
 * ----------------------------------------------------------------------------
 *  - mobile sidebar drawer (hamburger + backdrop)
 *  - dismissible flash messages (with auto-hide)
 *  - "Show/Hide" password toggles
 *  - confirmation modal for destructive forms (.js-confirm)
 *  - income/expense segmented control state
 *  - resend-code cooldown countdown
 * ========================================================================== */
(function () {
    'use strict';

    /* ------------------------- mobile sidebar ---------------------------- */
    var sidebar  = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var menuBtn  = document.getElementById('menuBtn');

    if (menuBtn && sidebar && backdrop) {
        menuBtn.addEventListener('click', function () {
            sidebar.classList.add('open');
            backdrop.classList.add('show');
        });
        backdrop.addEventListener('click', function () {
            sidebar.classList.remove('open');
            backdrop.classList.remove('show');
        });
    }

    /* ------------------------- flash messages ---------------------------- */
    document.querySelectorAll('.flash').forEach(function (flash) {
        var close = flash.querySelector('.flash-close');
        if (close) {
            close.addEventListener('click', function () { flash.remove(); });
        }
        // Auto-dismiss after 6 seconds (errors and demo verification codes
        // stay a little longer so they can be copied)
        var delay = flash.classList.contains('flash-error') ? 10000
                  : flash.classList.contains('flash-code')  ? 15000 : 6000;
        setTimeout(function () {
            flash.style.transition = 'opacity .4s';
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 400);
        }, delay);
    });

    /* ---------------------- show/hide password --------------------------- */
    document.querySelectorAll('[data-peek]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-peek'));
            if (!input) return;
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.textContent = showing ? 'Show' : 'Hide';
        });
    });

    /* -------------------- confirm modal for deletes ---------------------- */
    function openConfirm(message, onYes) {
        var backdropEl = document.createElement('div');
        backdropEl.className = 'modal-backdrop';
        backdropEl.innerHTML =
            '<div class="modal" role="dialog" aria-modal="true">' +
            '  <h4>Please confirm</h4>' +
            '  <p></p>' +
            '  <div class="modal-actions">' +
            '    <button type="button" class="btn btn-ghost" data-act="no">Cancel</button>' +
            '    <button type="button" class="btn btn-danger" data-act="yes">Yes, continue</button>' +
            '  </div>' +
            '</div>';
        backdropEl.querySelector('p').textContent = message;
        document.body.appendChild(backdropEl);

        backdropEl.addEventListener('click', function (e) {
            if (e.target === backdropEl || e.target.getAttribute('data-act') === 'no') {
                backdropEl.remove();
            } else if (e.target.getAttribute('data-act') === 'yes') {
                backdropEl.remove();
                onYes();
            }
        });
    }

    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.confirmed === '1') return;   // second pass: allow
            e.preventDefault();
            openConfirm(form.getAttribute('data-confirm') || 'Are you sure?', function () {
                form.dataset.confirmed = '1';
                form.submit();
            });
        });
    });

    /* ------------------- segmented income/expense ------------------------ */
    document.querySelectorAll('.segment').forEach(function (seg) {
        seg.addEventListener('change', function () {
            seg.querySelectorAll('.segment-opt').forEach(function (opt) {
                var radio = opt.querySelector('input[type="radio"]');
                opt.classList.toggle('on', !!(radio && radio.checked));
            });
        });
    });

    /* -------------------- inline category edit toggle -------------------- */
    document.querySelectorAll('[data-toggle-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id   = btn.getAttribute('data-toggle-edit');
            var form = document.querySelector('[data-edit-form="' + id + '"]');
            if (form) form.hidden = !form.hidden;
        });
    });

    /* -------------------- resend-code cooldown --------------------------- */
    var resendBtn = document.getElementById('resendBtn');
    if (resendBtn) {
        var left = parseInt(resendBtn.getAttribute('data-cooldown') || '0', 10);
        if (left > 0) {
            var base = 'Resend code';
            resendBtn.disabled = true;
            var tick = function () {
                if (left <= 0) {
                    resendBtn.disabled = false;
                    resendBtn.textContent = base;
                    return;
                }
                resendBtn.textContent = base + ' (' + left + 's)';
                left -= 1;
                setTimeout(tick, 1000);
            };
            tick();
        }
    }

    /* ------------- digits-only behaviour for the code input --------------- */
    document.querySelectorAll('.code-input').forEach(function (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
        });
    });
})();
