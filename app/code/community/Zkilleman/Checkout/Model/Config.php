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
    const XML_PATH_CHECKOUT_ENABLED = 'zkilleman_checkout/general/enabled';
    const XML_PATH_CHECKOUT_LAYOUT  = 'zkilleman_checkout/layout/%s';
    
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
}
