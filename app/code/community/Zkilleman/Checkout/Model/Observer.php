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

class Zkilleman_Checkout_Model_Observer
{
    const EVENT_NAME_SHIPPING_COUNTRY = 'zkilleman_checkout_shipping_country';

    /**
     *
     * @return Zkilleman_Checkout_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('zkilleman_checkout/config');
    }

    /**
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return $this->_getConfig()->isEnabled();
    }

    /**
     * Remove Zkilleman_Checkout layout if disabled in config
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterGetLayoutUpdates(Varien_Event_Observer $observer)
    {
        if (!$this->_isEnabled()) {
            $updates = $observer->getUpdates();
            if (isset($updates->zkilleman_checkout)) {
                unset($updates->zkilleman_checkout);
            }
        }
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeLayoutRender(Varien_Event_Observer $observer)
    {
        if (!$this->_isEnabled()) {
            return;
        }

        if ($this->_getConfig()->shouldEstimateShippingMethods()) {
            $this->estimateShippingMethods();
        }

        if (!$this->_getConfig()->shouldShowReviewBlock() &&
                ($block = Mage::app()->getLayout()
                                            ->getBlock('checkout.onepage.review'))) {
            $block->unsetChild('info');
        }
    }

    /**
     * If country id is not yet set in the quotes shipping address, set it to the
     * stores default to estimate shipping rates
     *
     */
    public function estimateShippingMethods()
    {
        $address = Mage::getSingleton('checkout/session')
                            ->getQuote()->getShippingAddress();
        /* @var $address Mage_Sales_Model_Quote_Address */

        if ($address &&
                (!$address->getCountryId() ||
                  $address->getShippingRatesCollection()->count() == 0)) {
            $country = new Varien_Object(array(
                            'country_id' => Mage::helper('core')
                                                ->getDefaultCountry()));

            // Give observer a chance to do an ip lookup for a better estimate
            Mage::dispatchEvent(
                    self::EVENT_NAME_SHIPPING_COUNTRY, array('country' => $country));

            $address->setCountryId($country->getCountryId())
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->save();
        }
    }
}
