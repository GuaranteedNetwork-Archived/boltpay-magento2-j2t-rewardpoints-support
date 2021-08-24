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

use Bolt\Boltpay\Helper\Discount;
use Bolt\Boltpay\Helper\Shared\CurrencyUtils;

class CartPlugin
{
    /**
     * @var \J2t\Rewardpoints\Helper\Data
     */
    private $rewardHelper;

    /**
     * @var \Bolt\J2tRewardpointsSupport\Helper\Data
     */
    private $supportHelper;

    /**
     * @var \Bolt\Boltpay\Helper\Bugsnag
     */
    private $bugsnagHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Discount
     */
    private $discountHelper;

    /**
     * @param \J2t\Rewardpoints\Helper\Data                     $rewardHelper
     * @param \Bolt\J2tRewardpointsSupport\Helper\Data          $supportHelper
     * @param \Bolt\Boltpay\Helper\Bugsnag                      $bugsnagHelper
     * @param Discount                                          $discountHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \J2t\Rewardpoints\Helper\Data                     $rewardHelper,
        \Bolt\J2tRewardpointsSupport\Helper\Data          $supportHelper,
        \Bolt\Boltpay\Helper\Bugsnag                      $bugsnagHelper,
        \Bolt\Boltpay\Helper\Discount                     $discountHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->rewardHelper = $rewardHelper;
        $this->supportHelper = $supportHelper;
        $this->bugsnagHelper = $bugsnagHelper;
        $this->priceCurrency = $priceCurrency;
        $this->discountHelper = $discountHelper;
    }

/**
 * @param \Bolt\Boltpay\Helper\Cart  $subject     cart helper that is performing the discount collection
 * @param array                      $result      containing discounts, the total amount and rounded amount
 *                                                difference
 * @param int                        $totalAmount original total amount, before disocunt collection
 * @param float                      $diff        orignal difference after rounding, before discount collection
 * @param bool                       $paymentOnly whether the current checkout process is payment only
 * @param \Magento\Quote\Model\Quote $quote       for which the discounts are being collected for
 *
 * @return array containing discounts, the total amount and rounded amount difference
 */
public function afterCollectDiscounts(\Bolt\Boltpay\Helper\Cart $subject, $result, $totalAmount, $diff, $paymentOnly, $quote)
{
    list ($discounts, $totalAmount, $diff) = $result;

    try {
        $pointsUsed = $quote->getRewardpointsQuantity();
        if ($pointsUsed > 0) {

            // J2t reward points can not be applied to shipping.
            // And if its setting Include Tax On Discounts is enabled,
            // the discount can be applied to tax.
            // But since Bolt checkout does not support such a behavior,
            // we have to exclude tax from the discount calculation.
            $storeId = $quote->getStoreId();
            if ($this->rewardHelper->getIncludeTax($storeId)) {
                $pointsUsed = $this->supportHelper->getMaxPointUsage($quote, $pointsUsed);
            }
            $pointsValue = $this->rewardHelper->getPointMoneyEquivalence($pointsUsed, true, $quote, $storeId);
            $discountAmount = abs($this->priceCurrency->convert($pointsValue));
            $currencyCode = $quote->getQuoteCurrencyCode();
            $roundedDiscountAmount = CurrencyUtils::toMinor($discountAmount, $currencyCode);
            $discountType = $this->discountHelper->getBoltDiscountType('by_fixed');
            $discounts[] = [
                'description'       => 'Reward Points',
                'amount'            => $roundedDiscountAmount,
                'reference'         => \Bolt\J2tRewardpointsSupport\Helper\Data::J2T_REWARD_POINTS,
                'discount_category' => Discount::BOLT_DISCOUNT_CATEGORY_STORE_CREDIT,
                // For v1/discounts.code.apply and v2/cart.update
                'discount_type'     => $discountType,
                // For v1/merchant/order
                'type'              => $discountType,
            ];
            $diff -= CurrencyUtils::toMinorWithoutRounding($discountAmount, $currencyCode) - $roundedDiscountAmount;
            $totalAmount -= $roundedDiscountAmount;
        }
    } catch (\Exception $e) {
        $this->bugsnagHelper->notifyException($e);
    } finally {
        return [$discounts, $totalAmount, $diff];
    }
}
}