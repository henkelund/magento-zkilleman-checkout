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
                            if (pastCurrentSection) {
                                Element.removeClassName(this.sections[i], 'allow')
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
                        Element.select(step, '.a-item').each(function(el) {
                            action(el);
                        });
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
