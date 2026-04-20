(function ($) {
    'use strict';

    var PHONE_SELECTOR = 'input[name="telephone"], input[name="payment_address[telephone]"]';
    var SUBMIT_SELECTORS = [
        'button[type="submit"]',
        'input[type="submit"]',
        '#button-register',
        '#button-guest',
        '#button-order',
        '#button-confirm',
        '[data-aw-otp-trigger]'
    ].join(', ');

    var config = window.AW_OTP_CONFIG || null;

    if (!config) {
        return;
    }

    var i18n = config.i18n || {};
    var $modal = null;
    var state = {
        $form: null,
        $phone: null,
        $submit: null,
        phone: '',
        scope: '',
        timer: null,
        throttleUntil: 0,
        inFlight: false
    };

    function detectScope($form, $phone) {
        var action = $form.attr('action') || '';
        var formId = $form.attr('id') || '';
        var url = window.location.pathname + window.location.search;

        if ($form.closest('.aw-easy-checkout, #aw-easy-checkout').length || $form.find('#button-order').length) {
            return config.protect.checkout_easy ? 'checkout_easy' : null;
        }

        if ($phone.attr('name') === 'payment_address[telephone]' || $form.find('#button-guest').length) {
            return config.protect.checkout_std ? 'checkout_std' : null;
        }

        var isRegisterByUrl = action.indexOf('account/register') !== -1
            || action.indexOf('create-account') !== -1
            || formId.indexOf('register') !== -1
            || url.indexOf('account/register') !== -1
            || url.indexOf('route=account/register') !== -1
            || url.indexOf('create-account') !== -1;

        var isRegisterBySignature = $phone.attr('name') === 'telephone'
            && $form.find('input[name="password"]').length
            && $form.find('input[name="confirm"]').length;

        if (isRegisterByUrl || isRegisterBySignature) {
            return config.protect.register ? 'register' : null;
        }

        return null;
    }

    function findContainer($phone) {
        var $form = $phone.closest('form');
        if ($form.length) {
            return $form;
        }
        return $phone.closest('.panel-collapse, [id*="checkout"], [id*="register"], .aw-easy-checkout');
    }

    function init() {
        $(PHONE_SELECTOR).each(function () {
            var $phone = $(this);
            var $form = findContainer($phone);
            if (!$form.length) {
                return;
            }

            var scope = detectScope($form, $phone);
            if (!scope) {
                return;
            }

            var $btn = $form.find(SUBMIT_SELECTORS).first();
            if (!$btn.length || $btn.data('aw-otp-bound')) {
                return;
            }

            $form.data('aw-otp-hooked', true);
            $btn.data('aw-otp-bound', true);

            $btn[0].addEventListener('click', function (e) {
                intercept(e, $form, $phone, $btn, scope);
            }, true);

            if ($form.is('form')) {
                $form[0].addEventListener('submit', function (e) {
                    intercept(e, $form, $phone, $btn, scope);
                }, true);
            }
        });
    }

    function intercept(e, $form, $phone, $btn, scope) {
        var phone = $.trim($phone.val());

        if (!phone || $form.data('aw-otp-verified') === phone) {
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();

        openModal($form, $phone, $btn, scope, phone);
    }

    function ensureModal() {
        if ($modal) {
            return $modal;
        }

        $modal = $('#aw-otp-modal');
        if (!$modal.length) {
            $modal = null;
            return null;
        }

        $modal.find('#aw-otp-verify').on('click', verifyCode);
        $modal.find('#aw-otp-resend').on('click', requestCode);

        $modal.find('#aw-otp-code-input').on('keydown', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                verifyCode();
            }
        });

        $modal.on('hidden.bs.modal', function () {
            stopTimer();
            state.$form = null;
            state.$phone = null;
            state.$submit = null;
            state.phone = '';
            state.scope = '';
        });

        return $modal;
    }

    function openModal($form, $phone, $btn, scope, phone) {
        var $m = ensureModal();
        if (!$m) {
            return;
        }

        state.$form = $form;
        state.$phone = $phone;
        state.$submit = $btn;
        state.phone = phone;
        state.scope = scope;

        $m.find('#aw-otp-phone-display').text(phone);
        $m.find('#aw-otp-code-input').val('');
        $m.find('#aw-otp-resend').prop('disabled', false).show();
        setMessage('', '');
        $m.modal('show');

        $m.one('shown.bs.modal', function () {
            $m.find('#aw-otp-code-input').focus();
        });

        requestCode();
    }

    function setMessage(text, kind) {
        var $el = $('#aw-otp-message');
        if (!$el.length) {
            return;
        }
        $el.text(text || '').removeClass('is-error is-success');
        if (kind === 'error') {
            $el.addClass('is-error');
        } else if (kind === 'success') {
            $el.addClass('is-success');
        }
    }

    function startThrottleTimer(seconds) {
        stopTimer();

        if (!seconds || seconds <= 0) {
            enableResend();
            return;
        }

        var $btn = $('#aw-otp-resend');
        var $timer = $('#aw-otp-resend-timer');
        if (!$btn.length || !$timer.length) {
            return;
        }

        state.throttleUntil = Date.now() + seconds * 1000;
        $btn.prop('disabled', true);
        $timer.text(seconds);

        state.timer = setInterval(function () {
            var left = Math.max(0, Math.round((state.throttleUntil - Date.now()) / 1000));
            $timer.text(left);
            if (left <= 0) {
                enableResend();
            }
        }, 500);
    }

    function stopTimer() {
        if (state.timer) {
            clearInterval(state.timer);
            state.timer = null;
        }
    }

    function enableResend() {
        stopTimer();
        $('#aw-otp-resend').prop('disabled', false).show();
        $('#aw-otp-resend-timer').text('0');
    }

    function disableResend() {
        stopTimer();
        $('#aw-otp-resend').prop('disabled', true).hide();
        $('#aw-otp-resend-timer').text('');
    }

    function requestCode() {
        if (state.inFlight) {
            return;
        }

        if (!state.phone) {
            setMessage(i18n.invalidPhone || 'Invalid phone', 'error');
            return;
        }

        state.inFlight = true;

        $.ajax({
            url: config.requestUrl,
            type: 'POST',
            dataType: 'json',
            data: { phone: state.phone },
            success: function (resp) {
                state.inFlight = false;
                resp = resp || {};

                if (resp.success) {
                    setMessage(i18n.codeSent || 'Code sent', 'success');
                    var throttle = config.throttle || 30;
                    if (resp.throttle_until) {
                        var diff = Math.max(0, resp.throttle_until - Math.floor(Date.now() / 1000));
                        if (diff > 0) {
                            throttle = diff;
                        }
                    }
                    if (resp.resends_left <= 0) {
                        disableResend();
                    } else {
                        startThrottleTimer(throttle);
                    }
                } else {
                    setMessage(resp.error || i18n.gateway || 'Error', 'error');
                    if (resp.lockout) {
                        disableResend();
                    } else if (resp.retry_after) {
                        startThrottleTimer(resp.retry_after);
                    }
                }
            },
            error: function () {
                state.inFlight = false;
                setMessage(i18n.gateway || 'Network error', 'error');
            }
        });
    }

    function verifyCode() {
        var code = ($('#aw-otp-code-input').val() || '').replace(/\D/g, '');

        if (code.length !== 6) {
            setMessage(i18n.invalidCode || 'Invalid code', 'error');
            return;
        }

        $.ajax({
            url: config.verifyUrl,
            type: 'POST',
            dataType: 'json',
            data: { phone: state.phone, code: code },
            success: function (resp) {
                resp = resp || {};

                if (resp.success && resp.token) {
                    onVerifySuccess(resp.token);
                    return;
                }

                var msg = resp.error || i18n.invalidCode || 'Invalid code';
                if (typeof resp.attempts_left !== 'undefined') {
                    msg += ' (' + resp.attempts_left + ')';
                }
                setMessage(msg, 'error');
            },
            error: function () {
                setMessage(i18n.gateway || 'Network error', 'error');
            }
        });
    }

    function onVerifySuccess(token) {
        var $form = state.$form;
        var $phone = state.$phone;
        var $btn = state.$submit;

        if (!$form || !$phone) {
            return;
        }

        $form.data('aw-otp-verified', state.phone);

        var $target = $phone.closest('.panel-body, fieldset, form');
        if (!$target.length) {
            $target = $form;
        }

        var $hidden = $target.find('input[name="aw_otp_token"]');
        if (!$hidden.length) {
            $hidden = $('<input>', { type: 'hidden', name: 'aw_otp_token' }).appendTo($target);
        }
        $hidden.val(token);

        setMessage('', 'success');

        if ($modal) {
            $modal.modal('hide');
        }

        setTimeout(function () {
            if ($btn && $btn.length) {
                $btn[0].click();
            } else if ($form.is('form')) {
                $form[0].submit();
            }
        }, 120);
    }

    var initTimer = null;
    function scheduleInit() {
        if (initTimer) {
            return;
        }
        initTimer = setTimeout(function () {
            initTimer = null;
            init();
        }, 200);
    }

    $(init);

    if (typeof MutationObserver !== 'undefined') {
        $(function () {
            new MutationObserver(scheduleInit).observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }
})(jQuery);
