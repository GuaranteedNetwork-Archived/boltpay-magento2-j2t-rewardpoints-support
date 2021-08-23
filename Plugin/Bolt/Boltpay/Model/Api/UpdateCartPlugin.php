<?php
/**
 * Bolt magento2 plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Bolt
 * @package    Bolt_J2tRewardpointsSupport
 * @copyright  Copyright (c) 2017-2021 Bolt Financial, Inc (https://www.bolt.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Bolt\J2tRewardpointsSupport\Plugin\Bolt\Boltpay\Model\Api;

class UpdateCartPlugin
{
    /**
     * @param \Bolt\Boltpay\Model\Api\UpdateCart $subject
     * @param array                              $result
     * @param string                             $couponCode
     * @param \Magento\Quote\Model\Quote         $quote
     *
     * @return array[]|mixed
     */
    public function afterGetAppliedStoreCredit(\Bolt\Boltpay\Model\Api\UpdateCart $subject, $result, $couponCode, $quote)
    {
        if (
            !$result
            && $couponCode == \Bolt\J2tRewardpointsSupport\Helper\Data::J2T_REWARD_POINTS
            && $quote->getRewardpointsQuantity() > 0
        ) {
            $result = [
                [
                    'discount_category' => \Bolt\Boltpay\Helper\Discount::BOLT_DISCOUNT_CATEGORY_STORE_CREDIT,
                    'reference'         => $couponCode,
                ]
            ];
        }
        return $result;
    }
}