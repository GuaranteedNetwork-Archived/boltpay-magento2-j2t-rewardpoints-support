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

namespace Bolt\J2tRewardpointsSupport\Helper;

use Magento\Quote\Model\Quote;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const J2T_REWARD_POINTS = 'j2t_reward_points';

    /**
     * @var \J2t\Rewardpoints\Helper\Data
     */
    private $rewardHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \J2t\Rewardpoints\Helper\Data         $rewardHelper
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \J2t\Rewardpoints\Helper\Data $rewardHelper)
    {
        parent::__construct($context);
        $this->rewardHelper = $rewardHelper;
    }


    /**
     * Return the max amount of reward points can be applied to quote.
     *
     * @param Quote $quote
     * @param float $points
     */
    public function getMaxPointUsage($quote, $points)
    {
        if ($points > 0) {
            $customerPoints = $this->rewardHelper->getCurrentCustomerPoints(
                $quote->getCustomerId(),
                $quote->getStoreId()
            );
            $points = max($points, $customerPoints);
        }
        $points = $this->getMaxOrderUsage($quote, $points, true);
        return $points;
    }

    /**
     * Calculate the max amount of reward points can be applied to quote.
     *
     * @param Quote $quote
     * @param float $points
     * @param bool  $collectTotals
     *
     */
    public function getMaxOrderUsage($quote, $points, $collectTotals = false)
    {
        //check cart base subtotal
        $storeId = $quote->getStoreId();
        if ($percent = $this->rewardHelper->getMaxPercentUsage($storeId)) {
            //do collect totals
            $subtotalPrice = $quote->getShippingAddress()->getBaseSubtotal();

            if ($collectTotals && $subtotalPrice <= 0) {
                $quote->setByPassRewards(true);
                $quote->setTotalsCollectedFlag(false)->collectTotals();
                $quote->setByPassRewards(false);
                $subtotalPrice = $quote->getShippingAddress()->getBaseSubtotal();
            }

            if ($subtotalPrice <= 0) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $subtotalPrice += $item->getBasePrice() * $item->getQty();
                }
            }

            $baseSubtotalInPoints = $this->rewardHelper->getPointsProductPriceEquivalence(
                    $subtotalPrice,
                    $storeId
                ) * $percent / 100;
            $points = min($points, $baseSubtotalInPoints);
        }
        if ($maxPointUsage = $this->rewardHelper->getMaxPointUsage()) {
            $points = min($points, $maxPointUsage);
        }
        return $points;
    }
}