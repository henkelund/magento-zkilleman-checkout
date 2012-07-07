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
    
    if (!w.Zkilleman_Accordion) {
        w.Zkilleman_Accordion = Class.create(Accordion, {
            
            initialize: function(elem, clickableEntity, checkAllow, callbacks) {
                this.container = $(elem);
                this.checkAllow = checkAllow || false;
                this.disallowAccessToNextSections = false;
                this.sections = $$('#' + elem + ' .section');
                this.currentSection = false;
                var headers = $$('#' + elem + ' .section ' + clickableEntity);
                headers.each(function(header) {
                    Event.observe(header,'click',this.sectionClicked.bindAsEventListener(this));
                }.bind(this));
                this.callbacks = callbacks || {};
            },

            openSection: function(section) {
                var section = $(section);

                // Check allow
                if (this.checkAllow && !Element.hasClassName(section, 'allow')){
                    return;
                }

                if(section.id != this.currentSection) {
                    this.closeExistingSection();
                    this.currentSection = section.id;
                    $(this.currentSection).addClassName('active');
                    if (typeof this.callbacks.onToggle == 'function') {
                        this.callbacks.onToggle(section.id, true);
                    }

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

            closeSection: function(section) {
                $(section).removeClassName('active');
                if (typeof this.callbacks.onToggle == 'function') {
                    this.callbacks.onToggle(section, false);
                }
            }
        });
    }

})(window);
