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
        $config         = Mage::getSingleton('zkilleman_checkout/config');
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
                    if ($stepCode == 'shipping') {
                        $shippingContainer = $containerCode;
                        --$i;
                    }
                    if ($stepCode == 'billing') {
                        $billingContainer = $containerCode;
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
}
