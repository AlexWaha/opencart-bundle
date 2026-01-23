/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

(function() {
    'use strict';

    window.awEcommerceTracking = window.awEcommerceTracking || {};

    const config = window.awEcommerceTrackingConfig || {
        enabled: false,
        debug: false,
        currency: 'USD',
        trackAddToCart: true,
        trackRemoveFromCart: true,
        trackSelectItem: true,
        trackWishlist: true,
        trackCoupon: true
    };

    if (!config.enabled) {
        return;
    }

    const log = function(message, data) {
        if (config.debug) {
            console.log('alexwaha.com - E-commerce Tracking:', message, data);
        }
    };

    const pushEvent = function(eventData) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push(eventData);
        log('Event pushed', eventData);
    };

    const getProductIdFromUrl = function(url) {
        const match = url.match(/product_id=(\d+)/);
        if (match) return match[1];
        return null;
    };

    if (config.trackSelectItem) {
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href*="product_id="], a[href*="/product/"]');
            if (!link || !window.awProductsData) return;

            const productId = getProductIdFromUrl(link.href);
            if (!productId) return;

            const productData = window.awProductsData[productId];
            if (!productData) return;

            const eventData = {
                event: 'select_item',
                ecommerce: {
                    item_list_name: productData.list_name || '',
                    item_list_id: productData.list_id || '',
                    items: [{
                        item_id: String(productData.id || productData.product_id),
                        item_name: productData.name,
                        price: parseFloat(productData.price) || 0,
                        item_brand: productData.brand || productData.manufacturer || '',
                        item_category: productData.category || '',
                        index: parseInt(productData.index) || 0
                    }]
                }
            };

            pushEvent(eventData);
        });
    }

    if (config.trackAddToCart) {
        const hookCartAdd = function() {
            if (!window.cart || !window.cart.add || window.cart._awHooked) {
                return false;
            }

            const originalCartAdd = window.cart.add;
            window.cart._awHooked = true;

            window.cart.add = function(productId, quantity) {
                const productData = window.awProductsData ? window.awProductsData[productId] : null;

                if (productData) {
                    const eventData = {
                        event: 'add_to_cart',
                        ecommerce: {
                            currency: config.currency,
                            value: (parseFloat(productData.price) || 0) * (parseInt(quantity) || 1),
                            items: [{
                                item_id: String(productData.id || productData.product_id),
                                item_name: productData.name,
                                price: parseFloat(productData.price) || 0,
                                quantity: parseInt(quantity) || 1,
                                item_brand: productData.brand || productData.manufacturer || '',
                                item_category: productData.category || ''
                            }]
                        }
                    };

                    pushEvent(eventData);
                } else {
                    log('No product data found for productId: ' + productId);
                }

                return originalCartAdd.apply(this, arguments);
            };

            return true;
        };

        if (!hookCartAdd()) {
            let attempts = 0;
            const interval = setInterval(function() {
                if (hookCartAdd() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }

        document.addEventListener('click', function(e) {
            const buttonCart = e.target.closest('#button-cart');
            if (!buttonCart) return;

            let productId = null;
            const productInput = document.querySelector('input[name="product_id"]');
            if (productInput) {
                productId = productInput.value;
            } else {
                const urlParams = new URLSearchParams(window.location.search);
                productId = urlParams.get('product_id');
            }

            if (!productId) return;

            let productData = window.awProductsData ? window.awProductsData[productId] : null;
            if (!productData) return;

            const qtyInput = document.querySelector('input[name="quantity"]');
            const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            const eventData = {
                event: 'add_to_cart',
                ecommerce: {
                    currency: config.currency,
                    value: (parseFloat(productData.price) || 0) * quantity,
                    items: [{
                        item_id: String(productData.id || productData.product_id),
                        item_name: productData.name,
                        price: parseFloat(productData.price) || 0,
                        quantity: quantity,
                        item_brand: productData.brand || productData.manufacturer || '',
                        item_category: productData.category || ''
                    }]
                }
            };

            pushEvent(eventData);
        }, true);
    }

    if (config.trackAddToCart || config.trackRemoveFromCart) {
        const storeCartQuantities = function() {
            const quantities = {};
            document.querySelectorAll('input[name^="quantity"]').forEach(function(input) {
                const key = input.name.match(/\[([^\]]+)\]/);
                if (key) {
                    quantities[key[1]] = parseInt(input.value) || 0;
                }
            });
            document.querySelectorAll('input[data-key]').forEach(function(input) {
                quantities[input.dataset.key] = parseInt(input.value) || 0;
            });
            window._awCartQuantities = quantities;
        };

        const trackQuantityChange = function(key, oldQty, newQty, productData) {
            const delta = newQty - oldQty;
            if (delta === 0 || !productData) return;

            const eventName = delta > 0 ? 'add_to_cart' : 'remove_from_cart';
            const quantity = Math.abs(delta);

            if (delta > 0 && !config.trackAddToCart) return;
            if (delta < 0 && !config.trackRemoveFromCart) return;

            const eventData = {
                event: eventName,
                ecommerce: {
                    currency: config.currency,
                    value: (parseFloat(productData.price) || 0) * quantity,
                    items: [{
                        item_id: String(productData.id || productData.product_id),
                        item_name: productData.name,
                        price: parseFloat(productData.price) || 0,
                        quantity: quantity,
                        item_brand: productData.brand || productData.manufacturer || '',
                        item_category: productData.category || ''
                    }]
                }
            };

            pushEvent(eventData);
        };

        const hookCartUpdate = function() {
            if (!window.cart || !window.cart.update || window.cart._awUpdateHooked) {
                return false;
            }

            const originalCartUpdate = window.cart.update;
            window.cart._awUpdateHooked = true;

            window.cart.update = function(key, quantity) {
                const oldQty = (window._awCartQuantities && window._awCartQuantities[key]) || 0;
                const newQty = parseInt(quantity) || 0;

                let productData = window.awProductsData ? window.awProductsData[key] : null;

                if (productData && oldQty !== newQty) {
                    trackQuantityChange(key, oldQty, newQty, productData);
                }

                if (window._awCartQuantities) {
                    window._awCartQuantities[key] = newQty;
                }

                return originalCartUpdate.apply(this, arguments);
            };

            return true;
        };

        storeCartQuantities();

        if (!hookCartUpdate()) {
            let attempts = 0;
            const interval = setInterval(function() {
                storeCartQuantities();
                if (hookCartUpdate() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form || !form.action || !form.action.includes('cart')) return;

            if (!form.action.includes('cart/edit') && !form.querySelector('input[name^="quantity"]')) return;

            const inputs = form.querySelectorAll('input[name^="quantity"]');
            inputs.forEach(function(input) {
                const key = input.name.match(/\[([^\]]+)\]/);
                if (!key) return;

                const cartKey = key[1];
                const oldQty = (window._awCartQuantities && window._awCartQuantities[cartKey]) || 0;
                const newQty = parseInt(input.value) || 0;

                if (oldQty !== newQty) {
                    const productData = window.awProductsData ? window.awProductsData[cartKey] : null;
                    if (productData) {
                        trackQuantityChange(cartKey, oldQty, newQty, productData);
                    }
                }
            });
        }, true);

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-action="plus"], [data-action="minus"], .btn-plus, .btn-minus, .qty-plus, .qty-minus');
            if (!btn) return;

            const container = btn.closest('.input-group, .qty-group, .quantity-control, [class*="quantity"]');
            if (!container) return;

            const input = container.querySelector('input[type="text"], input[type="number"]');
            if (!input) return;

            const key = input.dataset.key || (input.name && input.name.match(/\[([^\]]+)\]/) ? input.name.match(/\[([^\]]+)\]/)[1] : null);
            if (!key) return;

            const oldQty = (window._awCartQuantities && window._awCartQuantities[key]) || parseInt(input.value) || 0;

            setTimeout(function() {
                const newQty = parseInt(input.value) || 0;
                if (oldQty !== newQty) {
                    const productData = window.awProductsData ? window.awProductsData[key] : null;
                    if (productData) {
                        trackQuantityChange(key, oldQty, newQty, productData);
                        if (window._awCartQuantities) {
                            window._awCartQuantities[key] = newQty;
                        }
                    }
                }
            }, 50);
        }, true);

        document.addEventListener('change', function(e) {
            const input = e.target;
            if (!input || input.type !== 'text' && input.type !== 'number') return;

            const key = input.dataset.key || (input.name && input.name.match(/quantity\[([^\]]+)\]/) ? input.name.match(/quantity\[([^\]]+)\]/)[1] : null);
            if (!key) return;

            const oldQty = (window._awCartQuantities && window._awCartQuantities[key]) || 0;
            const newQty = parseInt(input.value) || 0;

            if (oldQty !== newQty) {
                const productData = window.awProductsData ? window.awProductsData[key] : null;
                if (productData) {
                    trackQuantityChange(key, oldQty, newQty, productData);
                    if (window._awCartQuantities) {
                        window._awCartQuantities[key] = newQty;
                    }
                }
            }
        }, true);
    }

    if (config.trackRemoveFromCart) {
        const hookCartRemove = function() {
            if (!window.cart || !window.cart.remove || window.cart._awRemoveHooked) {
                return false;
            }

            const originalCartRemove = window.cart.remove;
            window.cart._awRemoveHooked = true;

            window.cart.remove = function(key) {
                let productData = null;
                let quantity = 1;

                if (window.awProductsData && window.awProductsData[key]) {
                    productData = window.awProductsData[key];
                    quantity = parseInt(productData.quantity) || 1;
                }

                if (!productData) {
                    const removeBtn = document.querySelector('button[onclick*="cart.remove(\'' + key + '\')"], button[onclick*="cart.remove(' + key + ')"]');
                    if (removeBtn) {
                        const row = removeBtn.closest('tr, [class*="cart-item"], [class*="product"]');
                        if (row) {
                            const productLink = row.querySelector('a[href*="product_id="], a[href*="/product/"]');
                            if (productLink) {
                                const match = productLink.href.match(/product_id=(\d+)/);
                                if (match && window.awProductsData && window.awProductsData[match[1]]) {
                                    productData = window.awProductsData[match[1]];
                                }
                            }
                            const qtyInput = row.querySelector('input[name*="quantity"]');
                            if (qtyInput) {
                                quantity = parseInt(qtyInput.value) || 1;
                            }
                        }
                    }
                }

                if (productData) {
                    const eventData = {
                        event: 'remove_from_cart',
                        ecommerce: {
                            currency: config.currency,
                            value: (parseFloat(productData.price) || 0) * quantity,
                            items: [{
                                item_id: String(productData.id || productData.product_id),
                                item_name: productData.name,
                                price: parseFloat(productData.price) || 0,
                                quantity: quantity,
                                item_brand: productData.brand || productData.manufacturer || '',
                                item_category: productData.category || ''
                            }]
                        }
                    };

                    pushEvent(eventData);
                } else {
                    log('No product data found for cart.remove key: ' + key);
                }

                return originalCartRemove.apply(this, arguments);
            };

            return true;
        };

        if (!hookCartRemove()) {
            let attempts = 0;
            const interval = setInterval(function() {
                if (hookCartRemove() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }

        document.addEventListener('click', function(e) {
            const button = e.target.closest('[data-action="remove"][data-key]');
            if (!button) return;

            const key = button.dataset.key;
            if (!key) return;

            let productData = null;
            let quantity = 1;

            let row = button.closest('.cart-item, .product-row, .shopping-cart-item');
            if (!row) {
                let parent = button.parentElement;
                while (parent && parent !== document.body) {
                    if (parent.querySelector('a[href*="product"], a[href]:not([href^="#"]):not([href^="javascript"])')) {
                        row = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }

            if (row) {
                const productLink = row.querySelector('a[href*="product"], a[href]:not([href^="#"]):not([href^="javascript"])');
                if (productLink && window.awProductsData) {
                    const href = productLink.href;
                    for (const [pid, pdata] of Object.entries(window.awProductsData)) {
                        if (href.includes('product_id=' + pid)) {
                            productData = pdata;
                            break;
                        }
                        if (pdata.name) {
                            const nameClean = pdata.name.toLowerCase().replace(/[^a-z0-9а-яё]/gi, '');
                            if (nameClean && href.toLowerCase().includes(nameClean)) {
                                productData = pdata;
                                break;
                            }
                        }
                    }
                }
                let qtyInput = row.querySelector('input[data-key="' + key + '"]');
                if (!qtyInput) {
                    qtyInput = row.querySelector('input[type="text"], input[type="number"]');
                }
                if (qtyInput) {
                    quantity = parseInt(qtyInput.value) || 1;
                }
            }

            if (productData) {
                const eventData = {
                    event: 'remove_from_cart',
                    ecommerce: {
                        currency: config.currency,
                        value: (parseFloat(productData.price) || 0) * quantity,
                        items: [{
                            item_id: String(productData.id || productData.product_id),
                            item_name: productData.name,
                            price: parseFloat(productData.price) || 0,
                            quantity: quantity,
                            item_brand: productData.brand || productData.manufacturer || '',
                            item_category: productData.category || ''
                        }]
                    }
                };

                pushEvent(eventData);
            } else {
                log('No product data found for AW Easy Checkout remove, key: ' + key);
            }
        }, true);
    }

    if (config.trackWishlist) {
        const hookWishlistAdd = function() {
            if (!window.wishlist || !window.wishlist.add || window.wishlist._awHooked) {
                return false;
            }

            const originalWishlistAdd = window.wishlist.add;
            window.wishlist._awHooked = true;

            window.wishlist.add = function(productId) {
                const productData = window.awProductsData ? window.awProductsData[productId] : null;

                if (productData) {
                    const eventData = {
                        event: 'add_to_wishlist',
                        ecommerce: {
                            currency: config.currency,
                            value: parseFloat(productData.price) || 0,
                            items: [{
                                item_id: String(productData.id || productData.product_id),
                                item_name: productData.name,
                                price: parseFloat(productData.price) || 0,
                                item_brand: productData.brand || productData.manufacturer || '',
                                item_category: productData.category || ''
                            }]
                        }
                    };

                    pushEvent(eventData);
                } else {
                    log('No product data found for wishlist productId: ' + productId);
                }

                return originalWishlistAdd.apply(this, arguments);
            };

            return true;
        };

        if (!hookWishlistAdd()) {
            let attempts = 0;
            const interval = setInterval(function() {
                if (hookWishlistAdd() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }
    }

    if (config.trackCoupon) {
        const getDiscountValue = function(couponCode, isVoucher) {
            const tables = document.querySelectorAll('table, .totals, .order-total, #totals');
            let discountValue = 0;

            for (const table of tables) {
                const rows = table.querySelectorAll('tr, .total-row');
                for (const row of rows) {
                    const text = row.textContent || '';
                    if (text.includes(couponCode)) {
                        const match = text.match(/-?\s*[\d\s,.]+/g);
                        if (match) {
                            for (const m of match) {
                                const val = Math.abs(parseFloat(m.replace(/[^\d.-]/g, '').replace(',', '.')));
                                if (val > 0 && val < 100000) {
                                    discountValue = val;
                                    break;
                                }
                            }
                        }
                        if (discountValue > 0) break;
                    }
                }
                if (discountValue > 0) break;
            }

            if (discountValue === 0) {
                const allElements = document.querySelectorAll('td, .total-value, .discount-value');
                for (const el of allElements) {
                    const text = el.textContent || '';
                    if (text.includes('-') && /[\d.,]+/.test(text)) {
                        const val = Math.abs(parseFloat(text.replace(/[^\d.-]/g, '').replace(',', '.')));
                        if (val > 0 && val < 100000) {
                            discountValue = val;
                            break;
                        }
                    }
                }
            }

            return discountValue;
        };

        const trackCouponEvent = function(couponCode, eventType, discountValue) {
            if (!couponCode) return;

            if (typeof discountValue === 'undefined' || discountValue === null) {
                setTimeout(function() {
                    const discount = getDiscountValue(couponCode, eventType === 'add_voucher');
                    pushCouponEvent(couponCode, eventType, discount);
                }, 500);
            } else {
                pushCouponEvent(couponCode, eventType, discountValue);
            }
        };

        const pushCouponEvent = function(couponCode, eventType, discountValue) {
            const eventData = {
                event: eventType || 'add_coupon',
                ecommerce: {
                    currency: config.currency,
                    value: discountValue || 0,
                    coupon: couponCode
                }
            };

            pushEvent(eventData);
        };

        const setupAjaxIntercept = function() {
            if (!window.jQuery || window._awCouponAjaxHooked) return false;

            window._awCouponAjaxHooked = true;

            const originalAjax = jQuery.ajax;

            jQuery.ajax = function(url, options) {
                if (typeof url === 'object') {
                    options = url;
                    url = options.url;
                }

                options = options || {};
                const requestUrl = url || options.url || '';

                const isCouponRequest = requestUrl.includes('cart/coupon') ||
                                        requestUrl.includes('total/coupon') ||
                                        requestUrl.includes('coupon_voucher') ||
                                        requestUrl.includes('simplecheckout_cart/coupon');
                const isVoucherRequest = requestUrl.includes('cart/voucher') ||
                                         requestUrl.includes('total/voucher') ||
                                         requestUrl.includes('simplecheckout_cart/voucher');
                const isCartEditRequest = requestUrl.includes('cart/edit') ||
                                          requestUrl.includes('cart/update');

                if (isCartEditRequest && (config.trackAddToCart || config.trackRemoveFromCart)) {
                    let quantities = {};
                    if (options.data) {
                        if (typeof options.data === 'string') {
                            const params = new URLSearchParams(options.data);
                            params.forEach(function(value, key) {
                                const match = key.match(/quantity\[([^\]]+)\]/);
                                if (match) {
                                    quantities[match[1]] = parseInt(value) || 0;
                                }
                            });
                        } else if (typeof options.data === 'object') {
                            for (const [key, value] of Object.entries(options.data)) {
                                const match = key.match(/quantity\[([^\]]+)\]/);
                                if (match) {
                                    quantities[match[1]] = parseInt(value) || 0;
                                }
                            }
                        }
                    }

                    for (const [cartKey, newQty] of Object.entries(quantities)) {
                        const oldQty = (window._awCartQuantities && window._awCartQuantities[cartKey]) || 0;
                        if (oldQty !== newQty) {
                            const productData = window.awProductsData ? window.awProductsData[cartKey] : null;
                            if (productData) {
                                const delta = newQty - oldQty;
                                const eventName = delta > 0 ? 'add_to_cart' : 'remove_from_cart';
                                const quantity = Math.abs(delta);

                                if ((delta > 0 && config.trackAddToCart) || (delta < 0 && config.trackRemoveFromCart)) {
                                    pushEvent({
                                        event: eventName,
                                        ecommerce: {
                                            currency: config.currency,
                                            value: (parseFloat(productData.price) || 0) * quantity,
                                            items: [{
                                                item_id: String(productData.id || productData.product_id),
                                                item_name: productData.name,
                                                price: parseFloat(productData.price) || 0,
                                                quantity: quantity,
                                                item_brand: productData.brand || productData.manufacturer || '',
                                                item_category: productData.category || ''
                                            }]
                                        }
                                    });
                                }
                            }
                            if (window._awCartQuantities) {
                                window._awCartQuantities[cartKey] = newQty;
                            }
                        }
                    }
                }

                if (isCouponRequest || isVoucherRequest) {
                    const originalSuccess = options.success;
                    options.success = function(data, textStatus, jqXHR) {
                        let isSuccess = false;
                        let couponCode = '';

                        if (options.data) {
                            if (typeof options.data === 'string') {
                                const params = new URLSearchParams(options.data);
                                couponCode = params.get('coupon') || params.get('voucher') || '';
                            } else if (typeof options.data === 'object') {
                                couponCode = options.data.coupon || options.data.voucher || '';
                            }
                        }

                        if (typeof data === 'object') {
                            isSuccess = !!(data.success || data.redirect || (!data.error && !data.warning));
                        } else if (typeof data === 'string') {
                            try {
                                const parsed = JSON.parse(data);
                                isSuccess = !!(parsed.success || parsed.redirect || (!parsed.error && !parsed.warning));
                            } catch (e) {
                                isSuccess = false;
                            }
                        }

                        if (isSuccess && couponCode) {
                            const eventType = isVoucherRequest ? 'add_voucher' : 'add_coupon';
                            const discountValue = (typeof data === 'object' && data.discount) ? parseFloat(data.discount) : null;
                            trackCouponEvent(couponCode, eventType, discountValue);
                        }

                        if (originalSuccess) {
                            return originalSuccess.apply(this, arguments);
                        }
                    };
                }

                return originalAjax.call(this, typeof url === 'object' ? options : url, typeof url === 'object' ? undefined : options);
            };

            return true;
        };

        if (!setupAjaxIntercept()) {
            let attempts = 0;
            const interval = setInterval(function() {
                if (setupAjaxIntercept() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }

        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form) return;

            const action = form.action || '';
            const isCouponForm = action.includes('cart/coupon') || form.querySelector('input[name="coupon"]');
            const isVoucherForm = action.includes('cart/voucher') || form.querySelector('input[name="voucher"]');

            if (!isCouponForm && !isVoucherForm) return;

            const input = form.querySelector('input[name="coupon"], input[name="voucher"]');
            if (!input || !input.value.trim()) return;

            log('Coupon/voucher form submitted', input.value);
        }, true);

        document.addEventListener('click', function(e) {
            const button = e.target.closest('[data-action="coupon"], [data-action="voucher"], .btn-coupon, .btn-voucher');
            if (!button) return;

            const container = button.closest('.coupon-block, .voucher-block, .input-group, [class*="coupon"], [class*="voucher"]');
            if (!container) return;

            const input = container.querySelector('input[type="text"]');
            if (!input || !input.value.trim()) return;

            window._awPendingCoupon = {
                code: input.value.trim(),
                type: button.dataset.action === 'voucher' || button.classList.contains('btn-voucher') ? 'voucher' : 'coupon'
            };

            log('AW Easy Checkout coupon/voucher button clicked', window._awPendingCoupon);
        }, true);

        document.addEventListener('click', function(e) {
            const button = e.target.closest('#button-coupon, #button-voucher, [id*="coupon-button"], [id*="voucher-button"]');
            if (!button) return;

            let input = null;
            const formGroup = button.closest('.form-group, .input-group, [class*="coupon"], [class*="voucher"]');
            if (formGroup) {
                input = formGroup.querySelector('input[type="text"]');
            }

            if (!input) {
                input = document.querySelector('#input-coupon, #input-voucher, input[name="coupon"], input[name="voucher"]');
            }

            if (!input || !input.value.trim()) return;

            window._awPendingCoupon = {
                code: input.value.trim(),
                type: button.id.includes('voucher') ? 'voucher' : 'coupon'
            };

            log('Simple Checkout coupon/voucher button clicked', window._awPendingCoupon);
        }, true);

        document.addEventListener('awCouponApplied', function(e) {
            if (e.detail && e.detail.code) {
                trackCouponEvent(e.detail.code, e.detail.type === 'voucher' ? 'add_voucher' : 'add_coupon');
            }
        });

        window.awEcommerceTracking = window.awEcommerceTracking || {};
        window.awEcommerceTracking.trackCoupon = function(code, type) {
            trackCouponEvent(code, type === 'voucher' ? 'add_voucher' : 'add_coupon');
        };
    }

    if (config.trackShippingInfo || config.trackPaymentInfo) {
        const setupCheckoutAjaxIntercept = function() {
            if (!window.jQuery || window._awCheckoutAjaxHooked) return false;

            window._awCheckoutAjaxHooked = true;

            jQuery(document).ajaxComplete(function(event, xhr, settings) {
                if (!settings.url) return;

                const url = settings.url;

                if (config.trackShippingInfo && url.includes('shipping_method')) {
                    if (settings.data && settings.data.includes('shipping_method')) {
                        try {
                            const params = new URLSearchParams(settings.data);
                            const shippingMethod = params.get('shipping_method') || '';

                            if (shippingMethod) {
                                const shippingTier = shippingMethod.split('.')[0] || shippingMethod;

                                const items = [];
                                if (window.awProductsData) {
                                    for (const [id, product] of Object.entries(window.awProductsData)) {
                                        items.push({
                                            item_id: String(product.id || product.product_id),
                                            item_name: product.name,
                                            price: parseFloat(product.price) || 0,
                                            quantity: parseInt(product.quantity) || 1,
                                            item_brand: product.brand || product.manufacturer || '',
                                            item_category: product.category || ''
                                        });
                                    }
                                }

                                const eventData = {
                                    event: 'add_shipping_info',
                                    ecommerce: {
                                        currency: config.currency,
                                        shipping_tier: shippingTier,
                                        items: items
                                    }
                                };

                                pushEvent(eventData);
                            }
                        } catch (e) {
                            log('Error parsing shipping method', e);
                        }
                    }
                }

                if (config.trackPaymentInfo && url.includes('payment_method')) {
                    if (settings.data && settings.data.includes('payment_method')) {
                        try {
                            const params = new URLSearchParams(settings.data);
                            const paymentMethod = params.get('payment_method') || '';

                            if (paymentMethod) {
                                const items = [];
                                if (window.awProductsData) {
                                    for (const [id, product] of Object.entries(window.awProductsData)) {
                                        items.push({
                                            item_id: String(product.id || product.product_id),
                                            item_name: product.name,
                                            price: parseFloat(product.price) || 0,
                                            quantity: parseInt(product.quantity) || 1,
                                            item_brand: product.brand || product.manufacturer || '',
                                            item_category: product.category || ''
                                        });
                                    }
                                }

                                const eventData = {
                                    event: 'add_payment_info',
                                    ecommerce: {
                                        currency: config.currency,
                                        payment_type: paymentMethod,
                                        items: items
                                    }
                                };

                                pushEvent(eventData);
                            }
                        } catch (e) {
                            log('Error parsing payment method', e);
                        }
                    }
                }
            });

            return true;
        };

        if (!setupCheckoutAjaxIntercept()) {
            let attempts = 0;
            const interval = setInterval(function() {
                if (setupCheckoutAjaxIntercept() || ++attempts > 50) {
                    clearInterval(interval);
                }
            }, 100);
        }
    }

    window.awEcommerceTracking = {
        push: pushEvent,
        config: config,

        trackAddToCart: function(product, quantity) {
            if (!config.trackAddToCart) return;

            quantity = parseInt(quantity) || 1;

            pushEvent({
                event: 'add_to_cart',
                ecommerce: {
                    currency: config.currency,
                    value: (parseFloat(product.price) || 0) * quantity,
                    items: [{
                        item_id: String(product.id || product.product_id),
                        item_name: product.name,
                        price: parseFloat(product.price) || 0,
                        quantity: quantity,
                        item_brand: product.brand || product.manufacturer || '',
                        item_category: product.category || '',
                        item_variant: product.variant || ''
                    }]
                }
            });
        },

        trackRemoveFromCart: function(product, quantity) {
            if (!config.trackRemoveFromCart) return;

            quantity = parseInt(quantity) || 1;

            pushEvent({
                event: 'remove_from_cart',
                ecommerce: {
                    currency: config.currency,
                    value: (parseFloat(product.price) || 0) * quantity,
                    items: [{
                        item_id: String(product.id || product.product_id),
                        item_name: product.name,
                        price: parseFloat(product.price) || 0,
                        quantity: quantity
                    }]
                }
            });
        },

        trackSelectItem: function(product, listName, listId, index) {
            if (!config.trackSelectItem) return;

            pushEvent({
                event: 'select_item',
                ecommerce: {
                    item_list_name: listName || '',
                    item_list_id: listId || '',
                    items: [{
                        item_id: String(product.id || product.product_id),
                        item_name: product.name,
                        price: parseFloat(product.price) || 0,
                        item_brand: product.brand || product.manufacturer || '',
                        item_category: product.category || '',
                        index: parseInt(index) || 0
                    }]
                }
            });
        }
    };

    log('Initialized', config);
})();
