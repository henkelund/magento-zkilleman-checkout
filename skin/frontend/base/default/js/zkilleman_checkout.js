/**
 * Zkilleman_Checkout
 *
 * Copyright (C) 2012 Henrik Hedelund (henke.hedelund@gmail.com)
 *
 * This file is part of Zkilleman_Checkout.
 *
 * Zkilleman_Checkout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Zkilleman_Checkout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Zkilleman_Checkout. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Zkilleman
 * @package   Zkilleman_Checkout
 * @author    Henrik Hedelund <henke.hedelund@gmail.com>
 * @copyright 2012 Henrik Hedelund (henke.hedelund@gmail.com)
 * @license   http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @link      https://github.com/henkelund/magento-zkilleman-checkout
 */

(function(w) {

    if (!w.Zkilleman_Accordion && w.Accordion) {

        /**
         * This class extends and replaces the Accordion class defined in
         * /js/varien/accordion.js
         *
         */
        w.Zkilleman_Accordion = Class.create(w.Accordion, {

            setCallback: function(name, callback)
            {
                if (typeof this.callbacks != 'object') {
                    this.callbacks = {};
                }
                if (typeof callback == 'function') {
                    this.callbacks[name] = callback;
                }
            },

            getCallback: function(name)
            {
                if (typeof this.callbacks == 'object' &&
                        typeof this.callbacks[name] == 'function') {
                    return this.callbacks[name];
                } else {
                    return function() {};
                }
            },

            /**
             *
             * @param    mixed string|Element
             * @override Accordion::openSection
             * @see      /js/varien/accordion.js
             */
            openSection: function(section)
            {
                var section = $(section);

                // Check allow
                if (this.checkAllow && !Element.hasClassName(section, 'allow')){
                    return;
                }

                if(section.id != this.currentSection) {
                    this.closeExistingSection();
                    this.currentSection = section.id;
                    $(this.currentSection).addClassName('active');
                    this.getCallback('onToggle')(section.id, true);

                    if (this.disallowAccessToNextSections) {
                        var pastCurrentSection = false;
                        for (var i=0; i<this.sections.length; i++) {
                            if (pastCurrentSection ||
                                Element.hasClassName(this.sections[i], 'disallow')) {
                                Element.removeClassName(this.sections[i], 'allow');
                                Element.removeClassName(this.sections[i], 'disallow');
                            }
                            if (this.sections[i].id==section.id) {
                                pastCurrentSection = true;
                            }
                        }
                    }
                }
            },

            /**
             *
             * @param    mixed string|Element
             * @override Accordion::closeSection
             * @see      /js/varien/accordion.js
             */
            closeSection: function(section)
            {
                $(section).removeClassName('active');
                this.getCallback('onToggle')(section, false);
            }
        });
    }

    if (!w.Zkilleman_Checkout && w.Checkout) {

        /**
         * This class extends and replaces the Checkout class defined in
         * /skin/frontend/base/default/js/opcheckout.js
         *
         */
        w.Zkilleman_Checkout = Class.create(w.Checkout, {

            /**
             * This is like a second constructor for the checkout object.
             * We avoid overriding the initialize function for maintenance reasons.
             *
             */
            setup: function(options)
            {
                this.addOptions(options);
                this.accordion.setCallback(
                    'onToggle',
                    this.checkoutStepToggle.bind(this)
                );
                if (this.getOption('hide_shipping') == true &&
                        (shippingStep = $('opc-shipping'))) {
                    Element.hide(shippingStep);
                }
                if (activeStep = this.getOption('active_step')) {
                    accordion.openSection('opc-' + activeStep);
                }
                this._setupMethod();
                this._setupAddresses();
            },

            _setupMethod: function()
            {
                var loginMode        = this.getOption('login_mode')
                ,   guestAllowed     = this.getOption('guest_allowed')
                ,   loginStep        = $('opc-login')
                ,   loginGuest       = $('login:guest')
                ,   loginRegister    = $('login:register')
                ,   buttonsContainer = $('billing-buttons-container')
                ;

                if (loginMode != 'hide' || !loginStep) {
                    return false;
                }

                if (guestAllowed) {
                    // make sure all expected elements exist
                    if (!(loginGuest && loginRegister && buttonsContainer)) {
                        return false;
                    }

                    var methodsContainer = loginGuest.up('ul');
                    if (!methodsContainer) {
                        return false;
                    }
                }

                Element.hide(loginStep); // Hide before remove fixes IE7 glitch

                if (guestAllowed) {
                    // move methods from login to billing step
                    Element.remove(methodsContainer);
                    Element.addClassName(methodsContainer, 'checkout-methods');
                    buttonsContainer.insert({before: methodsContainer});

                    // force at least one method to be checked
                    if (!loginGuest.checked && !loginRegister.checked) {
                        loginGuest.checked = !(loginRegister.checked =
                                    (this.getOption('checkout_method') == 'register'));
                    }

                    // bind checkboxes to setMethod (toggles password fields & saves method)
                    var that = this;
                    [loginGuest, loginRegister].each(function(el) {
                        el.observe('click', that.setMethod.bind(that));
                    });
                }

                // save checked method, leave & remove login step
                this.setMethod();
                Element.remove(loginStep);

                return true;
            },

            _setupAddresses: function()
            {
                var type
                ,   key
                ,   field
                ,   updater
                ,   address
                ,   addresses = this.getOption('addresses')
                ;

                if (typeof addresses != 'object') {
                    return false;
                }

                for (type in addresses) {
                    address = addresses[type];
                    if (!address || typeof address != 'object') {
                        continue;
                    }
                    if (address.country_id && (field = $(type + ':country_id'))) {
                        Form.Element.setValue(field, address.country_id);
                        updater     = w[type + 'RegionUpdater'] || null;
                        if (typeof updater == 'object' &&
                                typeof updater.update == 'function') {
                            updater.update();
                        }
                    }
                    for (key in address) {
                        if (key == 'country_id') {
                            continue;
                        }
                        if (field = $(type + ':' + key)) {
                            Form.Element.setValue(field, address[key]);
                        }
                    }
                }
                return true;
            },

            _autoContinue: function(step)
            {
                var form   = $$('#' + step + ' form').first()
                ,   button = $$('#' + step + ' .buttons-set .button').first()
                ,   radios = []
                ;

                if (!form || !button || typeof button.onclick != 'function') {
                    return false;
                }

                radios = form.getInputs('radio');

                if (radios.size() != 1 ||
                        (form.getInputs().size() -
                            form.getInputs('hidden').size()) != 1) {
                    return false;
                }

                radios.first().checked = true;

                $(step).addClassName('disallow');

                window.setTimeout(button.onclick, 100);

                return true;
            },

            setOption: function(name, value)
            {
                if (typeof this.options != 'object') {
                    this.options = $H({});
                }
                this.options.set(name, value);
            },

            addOptions: function(options)
            {
                if (typeof options != 'object') {
                    return;
                }
                for (var key in options) {
                    this.setOption(key, options[key]);
                }
            },

            getOption: function(name)
            {
                if (typeof this.options == 'object') {
                    return this.options.get(name);
                }
                return null;
            },

            /**
             * Callback for accordion open/close toggle
             *
             */
            checkoutStepToggle: function(step, open)
            {
                if (!$(step)) { return; }

                var action = open ? Element.show : Element.hide;
                switch (step) {
                    case 'opc-login':
                        if (this.getOption('login_mode') == 'minimize') {
                            Element.select(step, '.a-item').each(function(el) {
                                action(el);
                            });
                        }
                        break;
                    case 'opc-billing':
                        if (this.getOption('hide_shipping') == true) {
                            var useForShipping = $('billing:use_for_shipping_yes');
                            useForShipping = useForShipping ? useForShipping.checked : false;
                            if (!open && useForShipping && (shippingStep = $('opc-shipping'))) {
                                action(shippingStep);
                            }
                        }
                        break;
                    case 'opc-shipping':
                        var sameAsBilling = $('shipping:same_as_billing');
                        sameAsBilling = sameAsBilling ? sameAsBilling.checked : false;
                        if (useForShippingYes = $('billing:use_for_shipping_yes')) {
                            useForShippingYes.checked = sameAsBilling;
                        }
                        if (useForShippingNo = $('billing:use_for_shipping_no')) {
                            useForShippingNo.checked = !sameAsBilling;
                        }
                        if (this.getOption('hide_shipping') == true) {
                            if (open || sameAsBilling) {
                                action(step);
                            }
                        }
                        break;
                    case 'opc-shipping_method':
                    case 'opc-payment':
                        if (open && this.getOption('auto_continue') == true) {
                            this._autoContinue(step);
                        }
                        break;
                }

                Zkilleman_Checkout.resizeStepOverlay(step);
            },

            /**
             *
             * @param    string
             * @override Checkout::reloadProgressBlock
             * @see      /skin/frontend/base/default/js/opcheckout.js
             */
            reloadProgressBlock: function(toStep)
            {
                // ignore progress request since we're not using the result
            }
        });

        /**
         * Static helper function used to set the height of inactive step overlays
         *
         */
        w.Zkilleman_Checkout.resizeStepOverlay = function(step)
        {
            if (!$(step)) {return;}

            var height = 0;
            if (!Element.hasClassName(step, 'active')) {
                var content = Element.select(step, '.a-item');
                height = Element.getHeight(content[0]);
            }
            Element.select(step, '.a-item .overlay').each(function(el) {
                Element.setStyle(el, {'height': height + 'px'});
            });
        };
    }

})(window);
