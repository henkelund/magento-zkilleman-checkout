<?php
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

class Zkilleman_Checkout_Model_Config
{
    const XML_PATH_CHECKOUT_ENABLED  = 'zkilleman_checkout/general/enabled';
    const XML_PATH_LOGIN_MODE        = 'zkilleman_checkout/general/login_mode';
    const XML_PATH_HIDE_SHIPPING     = 'zkilleman_checkout/general/hide_shipping';
    const XML_PATH_ESTIMATE_SHIPPING = 'zkilleman_checkout/general/estimate_shipping_methods';
    const XML_PATH_SHOW_REVIEW       = 'zkilleman_checkout/general/show_review';
    const XML_PATH_AUTO_CONTINUE     = 'zkilleman_checkout/general/auto_continue';
    const XML_PATH_STICKY_ADDRESSES  = 'zkilleman_checkout/general/sticky_addresses';
    const XML_PATH_CHECKOUT_LAYOUT   = 'zkilleman_checkout/layout/%s';

    // There's a const for this in Mage_Checkout_Helper_Data as well
    // but we keep our own copy for compatibility with older Magento versions
    const XML_PATH_CUSTOMER_MUST_BE_LOGGED = 'checkout/options/customer_must_be_logged';

    // Fallback if no code found in config
    const DEFAULT_CONTAINER_CODE    = 'right';

    /**
     * Is Zkilleman_Checkout enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CHECKOUT_ENABLED);
    }

    /**
     * Get login step display mode
     *
     * @return string
     */
    public function getLoginMode()
    {
        $mode = Mage::getStoreConfig(self::XML_PATH_LOGIN_MODE);

        if ($mode == 'hide' && $this->isLoginRequired()) {

            // We can't hide login, let's fall back on something else
            $modes = array_keys(
                        Mage::getModel('zkilleman_checkout/source_loginmodes')
                                ->toOptionArray());
            foreach ($modes as $newMode) {
                if ($newMode != $mode) {
                    return $newMode;
                }
            }
        }

        return $mode;
    }

    /**
     *
     * @return bool
     */
    public function isLoginHidden()
    {
        return $this->getLoginMode() == 'hide';
    }

    /**
     *
     * @return bool
     */
    public function isLoginRequired()
    {
        return !$this->isAllowedGuestCheckout() &&
                Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_MUST_BE_LOGGED);
    }

    /**
     *
     * @return bool
     */
    public function isAllowedGuestCheckout()
    {
        return Mage::helper('checkout')->isAllowedGuestCheckout(
                                Mage::getSingleton('checkout/session')->getQuote());
    }

    /**
     * Whether shipping step should be hidden on "use_same_as_billing"
     *
     * @return bool
     */
    public function isShippingHidden()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_HIDE_SHIPPING);
    }

    /**
     * Return the intended layout position for the given checkout step
     *
     * @param  string $stepCode
     * @return string
     */
    public function getStepContainerCode($stepCode)
    {
        if (!is_scalar($stepCode)) {
            return self::DEFAULT_CONTAINER_CODE;
        }
        $path = sprintf(self::XML_PATH_CHECKOUT_LAYOUT, $stepCode);
        $container = Mage::getStoreConfig($path);
        if (!strlen($container) > 0) {
            $container = self::DEFAULT_CONTAINER_CODE;
        }
        return $container;
    }

    /**
     * Whether shipping country should be guessed in order to collect shipping rates.
     *
     * @see    Zkilleman_Checkout_Model_Observer::beforeLayoutRender()
     * @param  Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function shouldEstimateShippingMethods(
                            Mage_Sales_Model_Quote $quote = null)
    {
        if (!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        return Mage::getStoreConfigFlag(
                    self::XML_PATH_ESTIMATE_SHIPPING) && !$quote->isVirtual();
    }

    /**
     * Whether checkout review step info block should be rendered
     *
     * @return boolean
     */
    public function shouldShowReviewBlock()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SHOW_REVIEW);
    }

    /**
     * Whether optionless steps should be bypassed
     *
     * @return boolean
     */
    public function isAutoContinueEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_AUTO_CONTINUE);
    }

    /**
     * Whether to pre populate address fields whith quote data
     *
     * @return boolean
     */
    public function isStickyAddressesEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_STICKY_ADDRESSES);
    }

    /**
     *
     * @return boolean
     */
    public function isAddressAutosaveEnabled()
    {
        return $this->isStickyAddressesEnabled() &&
                    count(
                            Mage::getSingleton('customer/session')
                                                ->getCustomer()->getAddresses()
                            ) == 0;
    }
}
