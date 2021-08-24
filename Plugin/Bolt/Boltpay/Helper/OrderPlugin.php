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

namespace Bolt\J2tRewardpointsSupport\Plugin\Bolt\Boltpay\Helper;

class OrderPlugin
{
    /**
     * @var \J2t\Rewardpoints\Helper\Data
     */
    private $rewardHelper;

    /**
     * @var \Bolt\Boltpay\Helper\Bugsnag
     */
    private $bugsnagHelper;

    /**
     * @var \Bolt\J2tRewardpointsSupport\Helper\Data
     */
    private $supportHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param \J2t\Rewardpoints\Helper\Data                     $rewardHelper
     * @param \Bolt\J2tRewardpointsSupport\Helper\Data          $supportHelper
     * @param \Bolt\Boltpay\Helper\Bugsnag                      $bugsnagHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \J2t\Rewardpoints\Helper\Data                     $rewardHelper,
        \Bolt\J2tRewardpointsSupport\Helper\Data          $supportHelper,
        \Bolt\Boltpay\Helper\Bugsnag                      $bugsnagHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->rewardHelper = $rewardHelper;
        $this->bugsnagHelper = $bugsnagHelper;
        $this->supportHelper = $supportHelper;
        $this->priceCurrency = $priceCurrency;
    }

/**
 * @param \Bolt\Boltpay\Helper\Order $subject order helper responsible for preparing th quote for order creation
 * @param \Magento\Quote\Model\Quote $immutableQuote source of truth for order creation
 * @param \stdClass                  $transaction Bolt order transaction object
 *
 * @return array altered parameters to make quote incude rewardpoints if eligible
 */
public function beforePrepareQuote(\Bolt\Boltpay\Helper\Order $subject, $immutableQuote, $transaction)
{
    try {
        $pointsUsed = $immutableQuote->getRewardpointsQuantity();
        if ($pointsUsed > 0) {
            // J2t reward points can not be applied to shipping.
            // And if its setting Include Tax On Discounts is enabled,
            // the discount can be applied to tax.
            // But since Bolt checkout does not support such a behavior,
            // we have to exclude tax from the discount calculation.
            $storeId = $immutableQuote->getStoreId();
            if ($this->rewardHelper->getIncludeTax($storeId)) {
                $maxPointUsage = $this->supportHelper->getMaxPointUsage($immutableQuote, $pointsUsed);
                $pointsValue = $this->rewardHelper->getPointMoneyEquivalence(
                    $maxPointUsage,
                    true,
                    $immutableQuote,
                    $storeId
                );
                $immutableQuote->setRewardpointsQuantity($maxPointUsage);
                $immutableQuote->setBaseRewardpoints($pointsValue);
                $immutableQuote->setRewardpoints($this->priceCurrency->convert($pointsValue));
                $immutableQuote->save();
            }
        }
    } catch (\Exception $e) {
        $this->bugsnagHelper->notifyException($e);
    } finally {
        return [$immutableQuote, $transaction];
    }
}
}