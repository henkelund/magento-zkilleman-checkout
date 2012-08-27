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

class Zkilleman_Checkout_AddressController extends Mage_Core_Controller_Front_Action
{
    public function saveAction()
    {
        $helper = Mage::helper('core');
        $this->getResponse()
                ->clearHeaders()
                ->setHttpResponseCode(200)
                ->setHeader('Content-type', 'application/json')
                ->setBody($helper->jsonEncode(true));

        $billingData  = (array) $this->getRequest()->getPost('billing', array());
        $shippingData = (array) $this->getRequest()->getPost('shipping', array());
        $quote        = Mage::getSingleton('checkout/session')->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if (!$quote) {
            return;
        }
        if ($billingAddress = $quote->getBillingAddress()) {
            /* @var $billingAddress Mage_Sales_Model_Quote_Address */
            if (isset($billingData['street']) && is_array($billingData['street'])) {
                $billingData['street'] = implode("\n", $billingData['street']);
            }
            $billingAddress->addData($billingData)->save();
        }
        if ($shippingAddress = $quote->getBillingAddress()) {
            /* @var $shippingAddress Mage_Sales_Model_Quote_Address */
            if (isset($shippingData['street']) && is_array($shippingData['street'])) {
                $shippingData['street'] = implode("\n", $shippingData['street']);
            }
            $shippingAddress->addData($shippingData)->save();
        }
    }
}
