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

class Zkilleman_Checkout_Model_Source_Containers
{
    const EVENT_NAME = 'zkilleman_checkout_containers_additional';

    /**
     * Get available checkout step layout containers
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('zkilleman_checkout');
        $containers = new Varien_Object(array(
            'top'    => $helper->__('Top'),
            'left'   => $helper->__('Left'),
            'middle' => $helper->__('Middle'),
            'right'  => $helper->__('Right')
        ));
        Mage::dispatchEvent(
                    self::EVENT_NAME, array('containers' => $containers));
        $options = array();
        foreach ((array) $containers->getData() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
}
