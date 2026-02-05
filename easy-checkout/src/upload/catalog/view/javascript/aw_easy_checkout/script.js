(function($) {
  /**
   * @external Maska
   */
  /**
   * @external datetimepicker
   */

  window.AwEasyCheckout = function (options) {
    const self = this;
    self.controllerPath = 'index.php?route=extension/aw_easy_checkout/';
    self.$elem = $('#aw-easy-checkout');
    self.options = options;
    self.$checkoutFields = $('input[type=\'text\'], textarea, input[type=\'checkbox\'], input[type=\'radio\'], select', self.$elem);
    self.clickSelectors = '.cart-list .btn, [id^="button-"]';
    self.reloadInProgress = false;
    self.saveInProgress = false;
    self.saveFieldsTimeout = null;

    self.SELECTORS = {
      FORM_DATA: 'input[type=\'text\']:visible, input[type=\'password\']:visible, input[type=\'hidden\'], input[type=\'checkbox\']:checked, .checkout-totals input[type=\'checkbox\']:checked, input[type=\'radio\']:checked, textarea:visible, select:visible',
      SAVE_FIELDS_DATA: 'input[type=\'text\']:not([id*=\'input_item_quantity_\']):visible, input[type=\'hidden\'], input[type=\'checkbox\']:checked, input[type=\'radio\']:checked, textarea:visible, select:visible',
      SHIPPING_COUNTRY: 'select[name=\'shipping_country_id\']',
      PAYMENT_COUNTRY: 'select[name=\'payment_country_id\']',
      SHIPPING_ZONE: 'select[name=\'shipping_zone_id\']',
      PAYMENT_ZONE: 'select[name=\'payment_zone_id\']',
      PHONE_INPUT: '#input-telephone',
      TELEPHONE_INPUT: 'input[name=\'telephone\']',
      COUPON_INPUT: 'input[name=\'coupon\']',
      VOUCHER_INPUT: 'input[name=\'voucher\']',
      REWARD_INPUT: 'input[name=\'reward\']',
      COUPON_ALERT: '.cart-coupon .alert',
      VOUCHER_ALERT: '.cart-voucher .alert',
      COUPON_ALERT_CLONE: '.cart-coupon .coupon-voucher-alert',
      VOUCHER_ALERT_CLONE: '.cart-voucher .coupon-voucher-alert',
      CART_COUPON: '.cart-coupon',
      CART_VOUCHER: '.cart-voucher',
      BLOCK_SHIPPING_ADDRESS: '.block_shipping_address',
      BLOCK_PAYMENT_ADDRESS: '.block_payment_address',
      BLOCK_SHIPPING_METHOD: '.block_shipping_method',
      BLOCK_PAYMENT_METHOD: '.block_payment_method',
      BLOCK_CUSTOMER: '.block_customer',
      BLOCK_CART: '.block_cart',
      BLOCK_COUPON: '.block_coupon',
      BLOCK_VOUCHER: '.block_voucher',
      FREE_SHIPPING_INFO: '.free-shipping-info',
      FREE_SHIPPING_LEFT: '.free-shipping-left',
      FREE_SHIPPING_BAR_FILL: '.free-shipping-bar-fill',
      FREE_SHIPPING_PROGRESS_BAR: '.free-shipping-progress-bar',
      CART_WEIGHT: '.cart-weight',
      CART_LIST: '.cart-list',
      PANEL_GROUP: '.panel-group',
      TABLE_TOTAL: '.table_total',
      CHECKOUT_TOTALS: '.checkout-totals',
      CHECKOUT_ADDRESS_INFO: '.checkout-address-info',
      CHECKOUT_CART_QUANTITY: '.checkout-cart-quantity',
      CHECKOUT_COL_FIX_RIGHT: '.checkout-col-fix-right',
      PAYMENT_ADDRESS_SAME_AS_SHIPPING: '#payment-address-same-as-shipping',
      CUSTOMER_ADDRESS_SELECT: '#customer-address-select',
      BUTTON_ORDER: '#button-order',
      BUTTON_CONFIRM: '#button-confirm',
      DATE_PICKER: '.date',
      TIME_PICKER: '.time',
      DATETIME_PICKER: '.datetime'
    };

    self.init = function () {
      self.response();
      self.initSelect2();
      self.authorization();
      self.attachEventHandlers();
      self.saveFields();
    };

    self.attachEventHandlers = function () {
      const $shippingCountry = $(self.SELECTORS.SHIPPING_COUNTRY, self.$elem);
      const $paymentCountry = $(self.SELECTORS.PAYMENT_COUNTRY, self.$elem);

      if ($shippingCountry.length > 0) {
        $shippingCountry.trigger('change');
      }

      if ($paymentCountry.length > 0) {
        $paymentCountry.trigger('change');
      }

      self.$elem.on('click', self.clickSelectors, function (e) {
        e.preventDefault();
        const $target = $(this);

        if ($target.hasClass('btn') && $target.closest('.cart-list').length > 0) {
          const action = $target.data('action');
          if (action === 'minus' || action === 'plus') {
            self.plusMinusQty($target, action);
          } else if (action === 'remove') {
            self.removeProduct($target.data('key'));
          } else if (action === 'remove-voucher') {
            self.removeVoucher($target.data('key'));
          }
        } else if ($target.attr('id') && $target.attr('id').startsWith('button-')) {
          const buttonId = $target.attr('id');
          if (buttonId === 'button-coupon') {
            self.handleCouponButtonClick();
          } else if (buttonId === 'button-reward') {
            self.handleRewardButtonClick();
          } else if (buttonId === 'button-voucher') {
            self.handleVoucherButtonClick();
          } else if (buttonId === 'button-order') {
            self.validateForm();
          }
        }
      });

      $(document).on('click', '.btn-remove-coupon', function (e) {
        e.preventDefault();
        self.removeCoupon();
      });

      $(document).on('click', '.btn-remove-voucher', function (e) {
        e.preventDefault();
        self.removeVoucherCode();
      });

      $(document).on('change', self.$checkoutFields, function (e) {
        const $target = $(e.target);

        const skipFields = [
          'shipping_country_id', 'shipping_zone_id', 'shipping_method', 'shipping_city', 'shipping_address_1', 'shipping_address_2', 'shipping_postcode', 'shipping_company',
          'payment_country_id', 'payment_zone_id', 'payment_method', 'payment_city', 'payment_address_1', 'payment_address_2', 'payment_postcode', 'payment_company'
        ];

        if (skipFields.includes($target.attr('name'))) {
          return;
        }

        setTimeout(function () {
          self.saveFields();
        }, 200);
      });

      $(document).on('change', '#input-customer-group', function (e) {
        e.preventDefault();
        self.customerUpdate();
        self.paymentUpdate();
        self.shippingAddressUpdate();
      });

      $(document).on('change', 'input[name=\'register\']', function () {
        self.toggleRegisterForm($(this));
      });

      $(document).on('change', '.checkout-address input, .checkout-address select, .checkout-address textarea, .checkout-address input[type="radio"], input[name=\'shipping_method\'], input[name=\'payment_method\']', function (e) {
        if (this.name === 'payment_address_same_as_shipping') {
          return;
        }
        e.preventDefault();
        self.saveFields();
        if (this.name === 'shipping_country_id' || this.name === 'payment_country_id') {
          if (this.name === 'shipping_country_id') {
            $(self.SELECTORS.SHIPPING_ZONE, self.$elem).val('');
            self.getShippingZones(this.value);

            $(self.SELECTORS.TELEPHONE_INPUT, self.$elem).val('');

            const $selectedOption = $(this).find('option:selected');
            const countryIso = $selectedOption.data('iso') || $selectedOption.attr('data-iso');
            self.updatePhoneMaskByCountry(countryIso);
          } else if (this.name === 'payment_country_id') {
            $(self.SELECTORS.PAYMENT_ZONE, self.$elem).val('');
            self.getPaymentZones(this.value);
          }
        } else if (this.name === 'payment_method') {
          $('#aw-payment-validation-block').remove();
          $(self.SELECTORS.BUTTON_ORDER, self.$elem).show();

          setTimeout(function () {
            self.reload();
          }, 100);
        } else if (this.name === 'shipping_city' || this.name === 'shipping_address_1') {
          setTimeout(function () {
            self.shippingUpdate();
            self.updateTotals();
          }, 100);
        } else if (this.name === 'payment_city' || this.name === 'payment_address_1') {
          setTimeout(function () {
            self.reload();
          }, 100);
        } else {
          setTimeout(function () {
            self.reload();
          }, 100);
        }
      });

      let inputTimeout;
      $(document).on('input', '.cart-item-price-quantity .form-control', function () {
        const input = this;
        clearTimeout(inputTimeout);
        inputTimeout = setTimeout(function () {
          self.validateQty(input);
        }, 600);
      });

      self.initMaskPhone();
      self.initDateTimePicker();
      self.initPaymentAddressToggle();
      self.initCustomerAddressSelector();
      self.initFieldErrorRemoval();
    };

    self.validateForm = function () {
      const $validationData = $('input[type=\'text\']:visible, input[type=\'date\']:visible, input[type=\'datetime-local\']:visible, input[type=\'time\']:visible, input[type=\'password\']:visible, input[type=\'hidden\'], input[type=\'checkbox\']:checked, .checkout-totals input[type=\'checkbox\']:checked, input[type=\'radio\']:checked, textarea:visible, select:visible', self.$elem);
      let data = $validationData.serialize();
      data += '&_shipping_method=' + $('input[name=\'shipping_method\']:checked', self.$elem).prop('title') +
        '&_payment_method=' + $('input[name=\'payment_method\']:checked', self.$elem).prop('title');

      $.ajax({
        url: self.controllerPath + 'validation',
        type: 'post',
        data: data,
        dataType: 'json',
        beforeSend: function () {
          $('.alert-danger').remove();
          self.loading(true);
        },
        success: function (json) {
          $('.alert:not(.alert-danger),.text-error,.text-danger').remove();
          $('.form-group').removeClass('has-error');

          if (json['error']) {
            self.loading(false);

            for (let i in json['error']) {
              const fieldId = i.includes('custom_field') ? '#input-' + i.replaceAll('_', '-') : '#input-' + i;
              let $field = $(fieldId);

              if (!$field.length) {
                $field = $('[name="' + i + '"]');
              }

              if ($field.length) {
                if ($field.closest('.form-group').length) {
                  $field.closest('.form-group').addClass('has-error');
                  if (i === 'agree') {
                    $field.parent().after('<div class="text-danger">' + json['error'][i] + '</div>');
                  } else {
                    const $inputGroup = $field.closest('.input-group');
                    if ($inputGroup.length) {
                      $inputGroup.after('<div class="text-danger">' + json['error'][i] + '</div>');
                    } else {
                      $field.after('<div class="text-danger">' + json['error'][i] + '</div>');
                    }
                  }
                }
              }
            }

            const $errorElement = $('.form-group.has-error, .checkout-agree.has-error').first();
            if ($errorElement.length > 0) {
              $('html, body').animate({
                scrollTop: $errorElement.offset().top - 120,
              }, 'slow');
            }
          }

          if (json['warning']) {
            const alertHtml = '<div class="alert alert-danger validation-alert"><i class="aw-icon aw-icon-error"></i> ' +
              json['warning'] +
              '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>';
            self.$elem.prepend(alertHtml);
          }

          const paymentHtml = (json['success'] && json['success']['payment']) || json['payment'];

          if (paymentHtml) {
            $(self.SELECTORS.BUTTON_ORDER, self.$elem).hide();

            $('#aw-payment-validation-block').remove();

            const wrappedHtml = '<div id="aw-payment-validation-block">' + paymentHtml + '</div>';
            self.$elem.after(wrappedHtml);

            const $validationBlock = $('#aw-payment-validation-block');
            const $buttonConfirm = $validationBlock.find(self.SELECTORS.BUTTON_CONFIRM);

            if ($buttonConfirm.length) {
              $buttonConfirm.on('click', function () {
                $(document).ajaxComplete(function () {
                  setTimeout(function () {
                    self.loading(false);
                  }, 300);
                });
              });

              const $selectedPayment = $('input[name="payment_method"]:checked', self.$elem);
              const autoConfirmEnabled = $selectedPayment.data('auto-confirm') == 1;

              if (autoConfirmEnabled) {
                $buttonConfirm.trigger('click');
              } else {
                const headerHeight = $('header').outerHeight() || 0;
                $('html, body').animate({
                  scrollTop: $validationBlock.offset().top - headerHeight - 50,
                }, 250);

                self.loading(false);
              }
            } else {
              self.loading(false);
            }
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
          self.loading(false);
        },
      });
    };

    self.reload = function () {
      if (self.reloadInProgress) {
        return;
      }
      self.reloadInProgress = true;

      const savedValues = {};
      $(self.SELECTORS.SAVE_FIELDS_DATA, self.$elem).each(function() {
        const $field = $(this);
        const name = $field.attr('name');
        if (name) {
          if ($field.is(':checkbox') || $field.is(':radio')) {
            savedValues[name] = $field.is(':checked');
          } else {
            savedValues[name] = $field.val();
          }
        }
      });

      const data = $(self.SELECTORS.FORM_DATA, self.$elem).serialize();
      $.ajax({
        url: self.controllerPath + 'reload',
        type: 'post',
        data: data,
        dataType: 'json',
        cache: false,
        global: false,
        beforeSend: function () {
          $('.alert-danger').not('.coupon-voucher-alert').remove();
          self.loading(true);
        },
        complete: function () {
          self.loading(false);
          self.reloadInProgress = false;

          $(self.SELECTORS.SAVE_FIELDS_DATA, self.$elem).each(function() {
            const $field = $(this);
            const name = $field.attr('name');

            if ($field.is(self.SELECTORS.PAYMENT_ADDRESS_SAME_AS_SHIPPING)) {
              return;
            }

            if (name && savedValues.hasOwnProperty(name)) {
              if ($field.is(':checkbox') || $field.is(':radio')) {
                $field.prop('checked', savedValues[name]);
              } else if (savedValues[name] !== undefined && savedValues[name] !== null) {
                $field.val(savedValues[name]);
              }
            }
          });
        },
        success: function (json) {
          if (json['redirect']) {
            location = json['redirect'];
          } else {
            for (let key in json) {
              switch (key) {
                case 'shipping_method':
                  const $blockShippingMethod = $(self.SELECTORS.BLOCK_SHIPPING_METHOD, self.$elem);
                  if (json['shipping_method']) {
                    $blockShippingMethod.html(json['shipping_method']);
                  } else {
                    $blockShippingMethod.html('');
                  }
                  break;
                case 'shipping_address':
                  const $blockShippingAddress = $(self.SELECTORS.BLOCK_SHIPPING_ADDRESS, self.$elem);
                  if ($(json['shipping_address']).find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).children().length > 0) {
                    const $checkboxContainer = $blockShippingAddress.find(self.SELECTORS.PAYMENT_ADDRESS_SAME_AS_SHIPPING).closest('.form-group');
                    const checkboxHtml = $checkboxContainer.length ? $checkboxContainer[0].outerHTML : '';

                    $blockShippingAddress.html(json['shipping_address']);
                    $blockShippingAddress.show();

                    if (checkboxHtml && !$blockShippingAddress.find(self.SELECTORS.PAYMENT_ADDRESS_SAME_AS_SHIPPING).length) {
                      $blockShippingAddress.find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).append(checkboxHtml);
                    }
                  } else {
                    $blockShippingAddress.find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).empty();
                    $blockShippingAddress.hide();
                  }
                  if (typeof self.initSelect2 == 'function') {
                    self.initSelect2();
                  }
                  if (typeof self.initPaymentAddressToggle == 'function') {
                    self.initPaymentAddressToggle();
                  }
                  if (typeof self.initCustomerAddressSelector == 'function') {
                    self.initCustomerAddressSelector();
                  }
                  break;
                case 'payment_address':
                  const $blockPaymentAddress = $(self.SELECTORS.BLOCK_PAYMENT_ADDRESS, self.$elem);
                  const $paymentCheckbox = $(self.SELECTORS.PAYMENT_ADDRESS_SAME_AS_SHIPPING, self.$elem);
                  const isPaymentSameAsShipping = $paymentCheckbox.length && $paymentCheckbox.is(':checked');

                  if ($(json['payment_address']).find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).children().length > 0) {
                    $blockPaymentAddress.html(json['payment_address']);
                    if (isPaymentSameAsShipping) {
                      $blockPaymentAddress.slideUp(300);
                    } else {
                      $blockPaymentAddress.slideDown(300);
                    }
                  } else {
                    $blockPaymentAddress.find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).empty();
                    $blockPaymentAddress.slideUp(300);
                  }
                  if (typeof self.initSelect2 == 'function') {
                    self.initSelect2();
                  }
                  break;
                case 'payment_method':
                  if (json['payment_method'] !== '') {
                    $(self.SELECTORS.BLOCK_PAYMENT_METHOD, self.$elem).html(json['payment_method']);
                  }
                  break;
                case 'customer':
                  $(self.SELECTORS.BLOCK_CUSTOMER, self.$elem).html(json['customer']);
                  self.initMaskPhone();
                  break;
                case 'cart':
                  $(self.SELECTORS.BLOCK_CART, self.$elem).html(json['cart']);
                  if (self.options.load_script && self.options.load_script.trim()) {
                    new Function(self.options.load_script)();
                  }
                  break;
                case 'coupon':
                  const $couponAlertClone = $(self.SELECTORS.COUPON_ALERT_CLONE, self.$elem).clone();
                  $(self.SELECTORS.BLOCK_COUPON, self.$elem).html(json['coupon']);
                  if ($couponAlertClone.length) {
                    $(self.SELECTORS.CART_COUPON, self.$elem).prepend($couponAlertClone);
                  }
                  break;
                case 'voucher':
                  const $voucherAlertClone = $(self.SELECTORS.VOUCHER_ALERT_CLONE, self.$elem).clone();
                  $(self.SELECTORS.BLOCK_VOUCHER, self.$elem).html(json['voucher']);
                  if ($voucherAlertClone.length) {
                    $(self.SELECTORS.CART_VOUCHER, self.$elem).prepend($voucherAlertClone);
                  }
                  break;
                case 'totals':
                  const $cartWeight = $(self.SELECTORS.CART_WEIGHT, self.$elem);
                  if ($cartWeight.length) {
                    $cartWeight.html($(json['totals']).find(self.SELECTORS.CART_WEIGHT).html());
                  }

                  const free_ship_left_html = $(json['totals']).find(self.SELECTORS.FREE_SHIPPING_LEFT).html();
                  let fsPercentageMatch = null;

                  if (free_ship_left_html) {
                    fsPercentageMatch = $(json['totals']).find('.free-shipping-inner').attr('data-fsl-width') || null;
                  } else {
                    $(self.SELECTORS.FREE_SHIPPING_LEFT, self.$elem).remove();
                  }

                  if (fsPercentageMatch) {
                    const $freeShippingLeft = $(self.SELECTORS.FREE_SHIPPING_LEFT, self.$elem);
                    if ($freeShippingLeft.length) {
                      const targetWidth = parseFloat(fsPercentageMatch);
                      const $freeShippingBarFill = $(self.SELECTORS.FREE_SHIPPING_BAR_FILL, self.$elem);
                      const $freeShippingInfo = $(self.SELECTORS.FREE_SHIPPING_INFO, self.$elem);
                      const $freeShippingProgressBar = $(self.SELECTORS.FREE_SHIPPING_PROGRESS_BAR, self.$elem);

                      $freeShippingBarFill.css({ width: targetWidth + '%' });
                      $freeShippingInfo.html($(free_ship_left_html).find(self.SELECTORS.FREE_SHIPPING_INFO).html());

                      if (targetWidth === 100) {
                        $freeShippingProgressBar.addClass('hidden');
                        $freeShippingInfo.addClass('active-free-shipping');
                      } else {
                        $freeShippingProgressBar.removeClass('hidden');
                        $freeShippingInfo.removeClass('active-free-shipping');
                      }

                    } else if (free_ship_left_html) {
                      const $checkoutTotals = $(self.SELECTORS.CHECKOUT_TOTALS, self.$elem);
                      $checkoutTotals.prepend('<div class="free-shipping-left">' + free_ship_left_html +
                        '</div>');
                    }
                  }

                  $(self.SELECTORS.TABLE_TOTAL, self.$elem).html($(json['totals']).find(self.SELECTORS.TABLE_TOTAL).html());
                  break;
                case 'errors':
                  if (json['errors'] && Object.keys(json['errors']).length > 0) {
                    $('.alert-danger').remove();

                    $.each(json['errors'], function (errorKey, error) {
                      let html = '<div class="alert alert-danger ' + errorKey + '">';
                      html += '<i class="aw-icon aw-icon-error"></i>' + error +
                        '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button>';
                      html += '</div>';

                      self.$elem.before(html);
                    });
                  } else {
                    const $alertDanger = $('.alert-danger');
                    if ($alertDanger.length) {
                      $alertDanger.remove();
                    }
                  }

                  break;
              }
            }

            self.initDateTimePicker();
          }
        },
      });
    };

    self.shippingUpdate = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem).serialize();

      $.ajax({
        url: self.controllerPath + 'shipping_method',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        beforeSend: function () {
          self.loading(true);
        },
        complete: function () {
          self.loading(false);
        },
        success: function (html) {
          const $blockShippingMethod = $(self.SELECTORS.BLOCK_SHIPPING_METHOD, self.$elem);
          if (html.length) {
            $blockShippingMethod.html(html);
          } else {
            $blockShippingMethod.html('');
          }
        },
      });
    };

    self.paymentUpdate = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem).serialize();

      $.ajax({
        url: self.controllerPath + 'payment_method',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        success: function (html) {
          $(self.SELECTORS.BLOCK_PAYMENT_METHOD, self.$elem).html(html);
        },
      });
    };

    self.updateCart = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem);

      $.ajax({
        url: self.controllerPath + 'cart',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        success: function (html) {
          const $html = $(html);
          $(self.SELECTORS.TABLE_TOTAL, self.$elem).html($html.find(self.SELECTORS.TABLE_TOTAL).html());
          $(self.SELECTORS.CART_LIST, self.$elem).html($html.find(self.SELECTORS.CART_LIST).html());
          $(self.SELECTORS.PANEL_GROUP, self.$elem).html($html.find(self.SELECTORS.PANEL_GROUP).html());
          if (self.options.load_script && self.options.load_script.trim()) {
            new Function(self.options.load_script)();
          }
        },
      });
    };

    self.updateTotals = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem);

      $.ajax({
        url: self.controllerPath + 'cart/getTotals',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        success: function (html) {
          const $html = $(html);
          const $cartWeight = $(self.SELECTORS.CART_WEIGHT, self.$elem);
          if ($cartWeight.length) {
            $cartWeight.html($html.find(self.SELECTORS.CART_WEIGHT).html());
          }
          $(self.SELECTORS.TABLE_TOTAL, self.$elem).html($html.find(self.SELECTORS.TABLE_TOTAL).html());
        },
      });
    };

    self.shippingAddressUpdate = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem).serialize();

      $.ajax({
        url: self.controllerPath + 'shipping_address',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        complete: function () {
          self.loading(false);
        },
        success: function (html) {
          const $blockShippingAddress = $(self.SELECTORS.BLOCK_SHIPPING_ADDRESS, self.$elem);
          if ($(html).find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).children().length > 0) {
            $blockShippingAddress.html(html);
            $blockShippingAddress.removeClass('hidden');
          } else {
            $blockShippingAddress.addClass('hidden');
          }
          self.initDateTimePicker();
        },
      }).done(function () {
        if (typeof self.initSelect2 == 'function') {
          self.initSelect2();
        }
        if (typeof self.initCustomerAddressSelector == 'function') {
          self.initCustomerAddressSelector();
        }
      });
    };

    self.customerUpdate = function () {
      const data = $(self.SELECTORS.FORM_DATA, self.$elem).serialize();
      $.ajax({
        url: self.controllerPath + 'customer',
        type: 'post',
        data: data,
        dataType: 'html',
        cache: false,
        beforeSend: function () {
          self.loading(true);
        },
        complete: function () {
          self.loading(false);
        },
        success: function (data) {
          $(self.SELECTORS.BLOCK_CUSTOMER, self.$elem).html(data);
          self.initMaskPhone();
          self.initDateTimePicker();
        },
      });
    };

    self.buildZoneOptionsHtml = function (zones, activeZoneId, placeholderText) {
      let html = '<option value="">' + placeholderText + '</option>';

      if (zones && zones !== '') {
        for (let i = 0; i < zones.length; i++) {
          html += '<option value="' + zones[i]['zone_id'] + '"';

          if (zones[i]['zone_id'] === activeZoneId) {
            html += ' selected="selected"';
          }

          html += '>' + zones[i]['name'] + '</option>';
        }
      }

      return html;
    };

    self.getShippingZones = function (value) {
      $.ajax({
        url: self.controllerPath + 'country&country_id=' + value + '&address_type=shipping',
        dataType: 'json',
        success: function (json) {
          const placeholderText = (self.options.lang && self.options.lang.text_select) || 'Please Select';
          const html = self.buildZoneOptionsHtml(json['zone'], json['active_zone_id'], placeholderText);
          $(self.SELECTORS.SHIPPING_ZONE, self.$elem).html(html);
          self.shippingUpdate();
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        },
      });
    };

    self.getPaymentZones = function (value) {
      $.ajax({
        url: self.controllerPath + 'country&country_id=' + value + '&address_type=payment',
        dataType: 'json',
        success: function (json) {
          const placeholderText = (self.options.lang && self.options.lang.text_select) || 'Please Select';
          const html = self.buildZoneOptionsHtml(json['zone'], json['active_zone_id'], placeholderText);
          $(self.SELECTORS.PAYMENT_ZONE, self.$elem).html(html);
          self.reload();
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        },
      });
    };

    self.initMaskPhone = function () {
      const $phoneInput = $(self.SELECTORS.PHONE_INPUT, self.$elem);

      if ($phoneInput.length) {
        const mask = self.options.tel_mask || '';

        if (mask.length) {
          $phoneInput.attr('placeholder', mask);

          self.phoneMaska = new Maska.MaskInput($phoneInput[0], {
            mask: mask
          });
        }
      }
    };

    self.updatePhoneMaskByCountry = function (countryIso) {
      const $phoneInput = $(self.SELECTORS.PHONE_INPUT, self.$elem);
      if (!$phoneInput.length) return;

      if (self.phoneMaska) {
        self.phoneMaska.destroy();
        self.phoneMaska = null;
      }

      const defaultMask = self.options.tel_mask || '+### (###) ###-##-##';

      if (!self.options.country_masks || !countryIso) {
        $phoneInput.attr('placeholder', defaultMask);
        self.phoneMaska = new Maska.MaskInput($phoneInput[0], {
          mask: defaultMask
        });
        return;
      }

      const countryData = self.options.country_masks[countryIso.toUpperCase()];
      if (countryData && countryData.mask) {
        $phoneInput.attr('placeholder', countryData.mask);
        self.phoneMaska = new Maska.MaskInput($phoneInput[0], {
          mask: countryData.mask
        });
      } else {
        $phoneInput.attr('placeholder', defaultMask);
        self.phoneMaska = new Maska.MaskInput($phoneInput[0], {
          mask: defaultMask
        });
      }
    };

    self.initDateTimePicker = function () {
      const $datePickers = $(self.SELECTORS.DATE_PICKER, self.$elem);
      if ($datePickers.length) {
        $datePickers.each(function () {
          const $datePicker = $(this);

          if ($datePicker.data('DateTimePicker')) {
            $datePicker.data('DateTimePicker').destroy();
          }

          $datePicker.off('dp.change');

          $datePicker.datetimepicker({
            pickTime: false,
            minDate: new Date(),
          });

          $datePicker.on('dp.change', function(e) {
            const $input = $(this).find('input');
            if (e.date) {
              const formattedValue = e.date.format('YYYY-MM-DD');
              $input.val(formattedValue);
              $input.attr('value', formattedValue);
            } else {
              $input.val('');
              $input.attr('value', '');
            }
          });
        });
      }

      const $timePickers = $(self.SELECTORS.TIME_PICKER, self.$elem);
      if ($timePickers.length) {
        $timePickers.each(function () {
          const $timePicker = $(this);

          if ($timePicker.data('DateTimePicker')) {
            $timePicker.data('DateTimePicker').destroy();
          }

          $timePicker.off('dp.change');

          $timePicker.datetimepicker({
            pickDate: false,
          });

          $timePicker.on('dp.change', function(e) {
            const $input = $(this).find('input');
            if (e.date) {
              const formattedValue = e.date.format('HH:mm');
              $input.val(formattedValue);
              $input.attr('value', formattedValue);
            } else {
              $input.val('');
              $input.attr('value', '');
            }
          });
        });
      }

      const $datetimePickers = $(self.SELECTORS.DATETIME_PICKER, self.$elem);
      if ($datetimePickers.length) {
        $datetimePickers.each(function () {
          const $datetimePicker = $(this);

          if ($datetimePicker.data('DateTimePicker')) {
            $datetimePicker.data('DateTimePicker').destroy();
          }

          $datetimePicker.off('dp.change');

          $datetimePicker.datetimepicker({
            pickDate: true,
            pickTime: true,
          });

          $datetimePicker.on('dp.change', function(e) {
            const $input = $(this).find('input');
            if (e.date) {
              const formattedValue = e.date.format('YYYY-MM-DD HH:mm');
              $input.val(formattedValue);
              $input.attr('value', formattedValue);
            } else {
              $input.val('');
              $input.attr('value', '');
            }
          });
        });
      }
    };

    self.handleCouponButtonClick = function () {
      const $couponInput = $(self.SELECTORS.COUPON_INPUT, self.$elem);
      const couponValue = $couponInput.val().trim();
      const $couponContainer = $(self.SELECTORS.CART_COUPON, self.$elem);

      if (couponValue === '') {
        $(self.SELECTORS.COUPON_ALERT, self.$elem).remove();
        const errorText = (self.options.lang && self.options.lang.text_coupon_required) || 'Please enter coupon code';
        $couponContainer.prepend('<div class="alert alert-danger coupon-voucher-alert"><i class="aw-icon aw-icon-error"></i> ' + errorText + '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
        return;
      }

      $.ajax({
        url: 'index.php?route=extension/total/coupon/coupon',
        type: 'post',
        data: 'coupon=' + encodeURIComponent(couponValue),
        dataType: 'json',
        beforeSend: function () {
          $couponInput.attr('disabled', 'disabled');
        },
        complete: function () {
          $couponInput.removeAttr('disabled');
        },
        success: function (json) {
          $(self.SELECTORS.COUPON_ALERT, self.$elem).remove();

          if (json['error']) {
            $couponContainer.prepend('<div class="alert alert-danger coupon-voucher-alert"><i class="aw-icon aw-icon-error"></i> ' +
              json['error'] +
              '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
          } else if (json['success'] || json['redirect']) {
            const successMessage = json['success'] || (self.options.lang && self.options.lang.text_coupon_success) || 'Coupon successfully applied';
            $couponContainer.prepend('<div class="alert alert-success coupon-voucher-alert"><i class="aw-icon aw-icon-success"></i> ' +
              successMessage +
              '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
            self.reload();
          }
        },
      });
    };

    self.handleRewardButtonClick = function () {
      const $rewardInput = $(self.SELECTORS.REWARD_INPUT, self.$elem);

      $.ajax({
        url: 'index.php?route=extension/total/reward/reward',
        type: 'post',
        data: 'reward=' + encodeURIComponent($rewardInput.val()),
        dataType: 'json',
        beforeSend: function () {
          $rewardInput.attr('disabled', 'disabled');
        },
        complete: function () {
          $rewardInput.removeAttr('disabled');
        },
        success: function (json) {
          $('.alert').remove();

          if (json['error']) {
            $('body').append('<div class="alert alert-danger"><i class="aw-icon aw-icon-error"></i> <div class="text-modal-block">' +
              json['error'] + '</div><button type="button" class="close" data-dismiss="alert"></button></div>');
          }
          if (json['success']) {
            $('body').append('<div class="alert alert-success"><i class="aw-icon aw-icon-success"></i><div class="text-modal-block">' +
              json['success'] + '</div><button type="button" class="close" data-dismiss="alert"></button></div>');
          }
          self.reload();
        },
      });
    };

    self.handleVoucherButtonClick = function () {
      const $voucherInput = $(self.SELECTORS.VOUCHER_INPUT, self.$elem);
      const voucherValue = $voucherInput.val().trim();
      const $voucherContainer = $(self.SELECTORS.CART_VOUCHER, self.$elem);

      if (voucherValue === '') {
        $(self.SELECTORS.VOUCHER_ALERT, self.$elem).remove();
        const errorText = (self.options.lang && self.options.lang.text_voucher_required) || 'Please enter voucher code';
        $voucherContainer.prepend('<div class="alert alert-danger coupon-voucher-alert"><i class="aw-icon aw-icon-error"></i> ' + errorText + '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
        return;
      }

      $.ajax({
        url: 'index.php?route=extension/total/voucher/voucher',
        type: 'post',
        data: 'voucher=' + encodeURIComponent(voucherValue),
        dataType: 'json',
        beforeSend: function () {
          $voucherInput.attr('disabled', 'disabled');
        },
        complete: function () {
          $voucherInput.removeAttr('disabled');
        },
        success: function (json) {
          $(self.SELECTORS.VOUCHER_ALERT, self.$elem).remove();

          if (json['error']) {
            $voucherContainer.prepend('<div class="alert alert-danger coupon-voucher-alert"><i class="aw-icon aw-icon-error"></i> ' +
              json['error'] +
              '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
          } else if (json['success'] || json['redirect']) {
            const successMessage = json['success'] || (self.options.lang && self.options.lang.text_voucher_success) || 'Voucher successfully applied';
            $voucherContainer.prepend('<div class="alert alert-success coupon-voucher-alert"><i class="aw-icon aw-icon-success"></i> ' +
              successMessage +
              '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');
            self.reload();
          }
        },
      });
    };

    self.removeCoupon = function () {
      const $couponInput = $(self.SELECTORS.COUPON_INPUT, self.$elem);
      $couponInput.val('');

      $.ajax({
        url: self.controllerPath + 'coupon_voucher/removeCoupon',
        type: 'post',
        dataType: 'json',
        success: function () {
          $(self.SELECTORS.COUPON_ALERT, self.$elem).remove();
          self.reload();
        },
      });
    };

    self.removeVoucherCode = function () {
      const $voucherInput = $(self.SELECTORS.VOUCHER_INPUT, self.$elem);
      $voucherInput.val('');

      $.ajax({
        url: self.controllerPath + 'coupon_voucher/removeVoucher',
        type: 'post',
        dataType: 'json',
        success: function () {
          $(self.SELECTORS.VOUCHER_ALERT, self.$elem).remove();
          self.reload();
        },
      });
    };

    self.plusMinusQty = function (elem, action) {
      let $parent = elem.closest(self.SELECTORS.CHECKOUT_CART_QUANTITY);

      let key = $parent.find('input').data('key');
      let minimum = parseFloat($parent.find('input').data('minimum'));
      minimum = minimum < 1 ? 1 : minimum;
      let quantity = parseFloat($parent.find('input').val().replace(/\D/g, ''));

      if (quantity === 0) {
        quantity = minimum;
      } else if (action === 'plus') {
        quantity += minimum;
      } else if (action === 'minus') {
        if (quantity <= minimum) {
          quantity = minimum;
        } else {
          quantity -= minimum;
        }
      }

      $parent.find('input').val(quantity);
      self.updateQty(key, quantity, minimum);
    };

    self.ajaxCartRequest = function (url, data) {
      $.ajax({
        url: self.controllerPath + url,
        type: 'post',
        data: data,
        dataType: 'json',
        global: false,
        beforeSend: function () {
          self.loading(true);
        },
        complete: function () {
          self.loading(false);
        },
        success: function () {
          self.reload();
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        },
      });
    };

    self.updateQty = function (key, quantity, minimum = 1) {
      if (quantity >= minimum) {
        self.ajaxCartRequest('cart/editCart', 'quantity[' + key + ']=' + quantity);
      }
    };

    self.validateQty = function (elem) {
      let count;
      const $input = $(elem);

      let minimum = $input.data('minimum');
      let value = $input.val().trim();
      let key = $input.data('key');

      if (/^0/.test(value)) {
        $input.val(minimum);
      } else {
        count = value.replace(/\D/g, '');
        if (count === '') count = minimum;
        if (count === '0') count = minimum;
        if (count < minimum) count = minimum;
        $input.val(count);
      }

      self.updateQty(key, count, minimum);
    };

    self.removeProduct = function (key) {
      self.ajaxCartRequest('cart/removeFromCart', 'key=' + key);
    };

    self.removeVoucher = function (key) {
      self.ajaxCartRequest('cart/removeFromCart', 'key=' + key);
    };

    self.saveFields = function (immediate = false) {
      if (self.saveFieldsTimeout) {
        clearTimeout(self.saveFieldsTimeout);
        self.saveFieldsTimeout = null;
      }

      if (self.saveInProgress || self.reloadInProgress) {
        return;
      }

      const performSave = function() {
        self.saveInProgress = true;

        $.ajax({
          url: self.controllerPath + 'reload/save',
          type: 'post',
          data: $(self.SELECTORS.SAVE_FIELDS_DATA, self.$elem),
          dataType: 'json',
          cache: false,
          global: false,
          complete: function () {
            self.saveInProgress = false;
          },
          error: function (xhr, ajaxOptions, thrownError) {
            self.saveInProgress = false;
          }
        });
      };

      if (immediate) {
        performSave();
      } else {
        self.saveFieldsTimeout = setTimeout(performSave, 100);
      }
    };

    self.initSelect2 = function () {
      $('.block_shipping_address, .block_payment_address', self.$elem).find('select[data-type=select2]').each(function () {
        $(this).select2();
      });
    };

    self.authorization = function () {
      $(document).on('click', '.login', function (e) {
        e.preventDefault();
        $.ajax({
          type: 'get',
          url: self.controllerPath + 'main/login',
          beforeSend: function () {
            self.loading(true);
          },
          complete: function () {
            self.loading(false);
          },
          success: function (data) {
            $('html body').append('<div id="login-form-popup" class="modal fade" role="dialog">' + data + '</div>');
            $('#login-form-popup').modal('show');
            self.validateLogin();
            $(document).on('hide.bs.modal', '#login-form-popup.modal.fade', function () {
              $('#login-form-popup').remove();
            });
          },
        });
      });
    };

    self.validateLogin = function () {
      $(document).on('click', '#button-login-popup', function (e) {
        e.preventDefault();
        $.ajax({
          url: self.controllerPath + 'validation/validateLogin',
          type: 'post',
          data: $('#aw-checkout-login input'),
          dataType: 'json',
          beforeSend: function () {
            self.loading(true);
          },
          complete: function () {
            self.loading(false);
          },
          success: function (json) {
            const $loginErrorContainer = $('#login-error-container');
            const $textDanger = $('.text-danger');
            const $inputEmailPopup = $('#input-email-popup');
            const $inputPasswordPopup = $('#input-password-popup');

            $loginErrorContainer.empty();
            $textDanger.remove();
            $inputEmailPopup.removeClass('error');
            $inputPasswordPopup.removeClass('error');

            if (json['isLogged']) {
              window.location.href = 'index.php?route=account/account';
            }

            if (json['error']) {
              let errorHtml = '';

              if (json['error']['warning']) {
                errorHtml += '<div class="alert alert-danger">' +
                  '<i class="aw-icon aw-icon-error"></i> ' + json['error']['warning'] +
                  '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>';
              }

              if (json['error']['email']) {
                $inputEmailPopup.addClass('error').after('<div class="text-danger">' + json['error']['email'] +
                  '</div>');
              }

              if (json['error']['password']) {
                $inputPasswordPopup.addClass('error').after('<div class="text-danger">' +
                  json['error']['password'] + '</div>');
              }

              if (errorHtml) {
                $loginErrorContainer.html(errorHtml);
              }
            }

            if (json['success']) {
              $('#login-form-popup').modal('hide');
              self.$elem.prepend('<div class="alert alert-success"><i class="aw-icon aw-icon-success"></i> ' +
                json['success'] +
                '<button type="button" class="close" data-dismiss="alert"><i class="aw-icon aw-icon-close"></i></button></div>');

              setTimeout(function () {
                location.reload();
              }, 3000);
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
          },
        });
      });
    };

    self.addTopCartRight = function () {
      if (self.viewport().width > 991) {
        if ($('header').hasClass('fix-header')) {
          const headerHeight = $('header').outerHeight() || 0;
          $(self.SELECTORS.CHECKOUT_COL_FIX_RIGHT).css('top', headerHeight + 30);
        } else {
          $(self.SELECTORS.CHECKOUT_COL_FIX_RIGHT).css('top', 30);
        }
      } else {
        $(self.SELECTORS.CHECKOUT_COL_FIX_RIGHT).css('top', 0);
      }
    };

    self.response = function () {
      let base = this,
        smallDelay,
        lastWindowWidth;

      lastWindowWidth = $(window).width();
      self.addTopCartRight();
      base.resizer = function () {
        if ($(window).width() !== lastWindowWidth) {

          window.clearTimeout(smallDelay);
          smallDelay = window.setTimeout(function () {
            lastWindowWidth = $(window).width();
            self.addTopCartRight();
          }, 200);
        }
      };
      $(window).resize(base.resizer);
    };

    self.loading = function (action) {
      const $loading = $('.aw-loading');
      if (action) {
        if (!$loading.length) {
          $('body').append('<div class="aw-loading"><div class="aw-loading-spinner"></div></div>');
        }
        $loading.show();
      } else {
        $loading.hide();
      }
    };

    self.toggleRegisterForm = function ($checkbox) {
      const $form = $('#register-form');

      if (!$checkbox.length || !$form.length) {
        return;
      }

      $form.removeClass('hidden').css('display', '');

      if ($checkbox.is(':checked')) {
        $form.show();
      } else {
        $form.hide();
      }

      $checkbox.off('change').on('change', function () {
        if ($(this).is(':checked')) {
          $form.slideDown(300);
        } else {
          $form.slideUp(300);
        }
      });
    };

    self.initPaymentAddressToggle = function () {
      const $checkbox = $(self.SELECTORS.PAYMENT_ADDRESS_SAME_AS_SHIPPING, self.$elem);
      const $paymentAddressBlock = $(self.SELECTORS.BLOCK_PAYMENT_ADDRESS, self.$elem);

      if ($checkbox.length && $paymentAddressBlock.length) {
        $paymentAddressBlock.removeClass('hidden').css('display', '');

        if ($checkbox.is(':checked')) {
          $paymentAddressBlock.hide();
        } else {
          $paymentAddressBlock.show();
        }

        $checkbox.off('change').on('change', function () {
          if ($(this).is(':checked')) {
            $paymentAddressBlock.slideUp(300, function() {
              self.saveFields(true);
            });
          } else {
            const isEmpty = $paymentAddressBlock.find(self.SELECTORS.CHECKOUT_ADDRESS_INFO).children().length === 0;
            if (isEmpty) {
              $paymentAddressBlock.show();
              self.saveFields(true);
              setTimeout(function() {
                self.reload();
              }, 100);
            } else {
              $paymentAddressBlock.slideDown(300, function() {
                self.saveFields(true);
              });
            }
          }
        });
      }
    };

    self.initCustomerAddressSelector = function () {
      const $addressSelector = $(self.SELECTORS.CUSTOMER_ADDRESS_SELECT, self.$elem);

      if ($addressSelector.length) {
        $addressSelector.off('change').on('change', function () {
          const addressId = $(this).val();

          if (addressId) {
            $.ajax({
              url: self.controllerPath + 'reload/changeAddress',
              type: 'post',
              data: 'address_id=' + addressId,
              dataType: 'json',
              beforeSend: function () {
                self.loading(true);
              },
              complete: function () {
                self.loading(false);
              },
              success: function (json) {
                if (json['success']) {
                  self.reload();
                }
              },
              error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
              }
            });
          }
        });
      }
    };

    self.viewport = function () {
      let e = window, a = 'inner';
      if (!('innerWidth' in window)) {
        a = 'client';
        e = document.documentElement || document.body;
      }
      return {
        width: e[a + 'Width'],
        height: e[a + 'Height'],
      };
    };

    self.initFieldErrorRemoval = function () {
      $(document).on('input change', 'input, select, textarea', self.$elem, function () {
        const $field = $(this);
        const $formGroup = $field.closest('.form-group');

        if ($formGroup.hasClass('has-error')) {
          $formGroup.removeClass('has-error');

          const $inputGroup = $field.closest('.input-group');
          if ($inputGroup.length) {
            $inputGroup.next('.text-danger').remove();
          } else {
            $field.next('.text-danger').remove();
            $field.siblings('.text-danger').remove();
          }
        }
      });

      $(document).on('change', 'input[type="checkbox"], input[type="radio"]', self.$elem, function () {
        const $field = $(this);
        const $formGroup = $field.closest('.form-group');

        if ($formGroup.hasClass('has-error')) {
          $formGroup.removeClass('has-error');
          $field.parent().next('.text-danger').remove();
        }
      });
    };
  };
})(jQuery);
