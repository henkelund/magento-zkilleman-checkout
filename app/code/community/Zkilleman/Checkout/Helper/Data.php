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

class Zkilleman_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EVENT_NAME_OPTIONS = 'zkilleman_checkout_options_additional';

    /**
     *
     * @return Zkilleman_Checkout_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('zkilleman_checkout/config');
    }

    /**
     * Build step layout
     *
     * @param  array $steps
     * @param  Mage_Core_Block_Abstract $block
     * @return array
     * @throws Exception
     */
    public function containSteps(
                                    array                    $steps,
                                    Mage_Core_Block_Abstract $block = null)
    {
        $containers     = array();
        $config         = $this->_getConfig();
        $containerCodes =
            Mage::getSingleton('zkilleman_checkout/source_containers')
                        ->toOptionArray();

        foreach (array_keys($containerCodes) as $code) {
            $containers[$code] = array();
        }

        $i = 0;
        $shippingContainer = null;
        $billingContainer  = null;
        foreach ($steps as $stepCode => $stepInfo) {
            $containerCode = $config->getStepContainerCode($stepCode);
            if (isset($containers[$containerCode])) {
                $visible = ($block == null) ? true :
                                ($block->getChild($stepCode) &&
                                    $block->getChild($stepCode)->isShow());
                if ($visible) {
                    $stepInfo['counter'] = ++$i;
                    $containers[$containerCode][$stepCode] = $stepInfo;
                    switch ($stepCode) {
                        case 'shipping':
                            $shippingContainer = $containerCode;
                            --$i;
                            break;
                        case 'billing':
                            $billingContainer = $containerCode;
                            break;
                        case 'login':
                            if ($config->isLoginHidden()) {
                                --$i;
                            }
                            break;
                    }
                }
            } else {
                throw new Exception(sprintf(
                        'No container "%s" exists for step "%s"',
                        $containerCode, $stepCode));
            }
        }

        if ($shippingContainer && $billingContainer) {
            $counter  = $containers[$billingContainer]['billing']['counter'];
            $counter .= 'b';
            $containers[$shippingContainer]['shipping']['counter'] = $counter;
        }

        return $containers;
    }

    /**
     *
     * @param  array $additional
     * @return array
     */
    public function getCheckoutOptions(
                                        Mage_Sales_Model_Quote $quote,
                                        $additional = array())
    {
        $config  = $this->_getConfig();
        $options = new Varien_Object(array_merge(array(
            'hide_shipping' => $config->isShippingHidden(),
            'login_mode'    => $config->getLoginMode(),
            'guest_allowed' => $config->isAllowedGuestCheckout(),
            'auto_continue' => $config->isAutoContinueEnabled()
        ), $additional));
        if ($config->isStickyAddressesEnabled()) {
            $options['addresses'] = array(
                'billing'  => $this->getAddressFormData($quote->getBillingAddress()),
                'shipping' => $this->getAddressFormData($quote->getShippingAddress())
            );
        }
        Mage::dispatchEvent(
                    self::EVENT_NAME_OPTIONS, array('options' => $options));
        return (array) $options->getData();
    }

    /**
     *
     * @param  array $additional
     * @return string
     */
    public function getCheckoutOptionsJson(
                                            Mage_Sales_Model_Quote $quote,
                                            $additional = array())
    {
        return Mage::helper('core')->jsonEncode(
                    $this->getCheckoutOptions($quote, $additional));
    }

    /**
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return string
     */
    public function getAddressFormData(Mage_Sales_Model_Quote_Address $address)
    {
        if (!$address) {
            return null;
        }
        $helper = Mage::helper('customer/address');
        $data   = array();
        $keys   = array(
            'firstname' , 'lastname'   ,
            'company'   , 'email'      ,
            'street'    , 'city'       ,
            'region'    , 'region_id'  ,
            'postcode'  , 'country_id' ,
            'telephone' , 'fax'
        );
        foreach ($keys as $key) {
            if ($address->hasData($key) &&
                    is_scalar($value = $address->getData($key))) {
                $data[$key] = (string) $value;
            }
        }

        if (isset($data['street'])) {
            $lines = array_pad(
                            explode("\n", $data['street']),
                            max(1, $helper->getStreetLines()),
                            '');
            for ($i = 0; $i < count($lines); ++$i) {
                $data[sprintf('street%d', $i + 1)] = $lines[$i];
            }
            unset($data['street']);
        }
        return count($data) > 0 ? $data : null;
    }
}
