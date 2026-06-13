/**
 * GDPR Consent Module (Cookie Consent + Google Consent Mode v2)
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @license GPLv3
 */
(() => {
    'use strict';

    const config = window.AwGdprConsent ?? {
        cookieName: 'aw_gdpr_consent',
        cookieDays: 365,
        signals: {
            necessary: ['security_storage', 'functionality_storage'],
            preferences: ['personalization_storage'],
            statistics: ['analytics_storage'],
            marketing: ['ad_storage', 'ad_user_data', 'ad_personalization'],
        },
    };

    const setCookie = (name, value) => {
        const date = new Date();
        date.setTime(date.getTime() + config.cookieDays * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/; SameSite=Lax`;
    };

    const getCookie = (name) => {
        const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
        return match ? decodeURIComponent(match[1]) : null;
    };

    const hasConsent = () => getCookie(config.cookieName) !== null;

    const pushUpdate = (signalState) => {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function gtag() { window.dataLayer.push(arguments); };
        window.gtag('consent', 'update', signalState);
    };

    const apply = (grantedCategories) => {
        const signalState = {};

        Object.entries(config.signals).forEach(([category, signals]) => {
            const granted = grantedCategories.includes(category) ? 'granted' : 'denied';
            signals.forEach((signal) => {
                signalState[signal] = granted;
                setCookie(signal, granted);
            });
        });

        setCookie(config.cookieName, grantedCategories.join('|') || 'necessary');
        pushUpdate(signalState);
    };

    const init = () => {
        const root = document.getElementById('aw-gdpr-consent');

        if (!root) {
            return;
        }

        const categories = [...root.querySelectorAll('[data-aw-gdpr-cat]')].map((input) => input.dataset.awGdprCat);

        const showBar = () => root.classList.add('is-open');
        const hideBar = () => root.classList.remove('is-open');

        const restore = () => {
            const saved = getCookie(config.cookieName);

            if (!saved) {
                return;
            }

            const granted = saved.split('|');

            categories.forEach((category) => {
                const input = root.querySelector(`[data-aw-gdpr-cat="${category}"]`);

                if (input && !input.disabled) {
                    input.checked = granted.includes(category);
                }
            });
        };

        const collect = () => [
            ...categories.filter((category) => {
                const input = root.querySelector(`[data-aw-gdpr-cat="${category}"]`);
                return input.disabled || input.checked;
            }),
        ];

        const finish = (grantedCategories) => {
            apply(grantedCategories);
            hideBar();
        };

        const handlers = {
            accept: () => finish([...categories]),
            selection: () => finish(collect()),
            deny: () => finish(categories.filter((category) => {
                const input = root.querySelector(`[data-aw-gdpr-cat="${category}"]`);
                return input.disabled;
            })),
            reopen: showBar,
        };

        root.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-aw-gdpr]');

            if (!trigger || !root.contains(trigger)) {
                return;
            }

            const action = handlers[trigger.dataset.awGdpr];

            if (action) {
                event.preventDefault();
                action();
            }
        });

        restore();

        if (!hasConsent()) {
            showBar();
        }

        root.classList.add('is-ready');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
