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

use Magento\Quote\Model\Quote;

class DiscountPlugin
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(\Magento\Quote\Model\QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Remove J2t reward points from the quote.
     *
     * @param \Bolt\Boltpay\Helper\Discount $subject
     * @param void                          $result
     * @param string                        $couponCode
     * @param Quote                         $quote
     * @param int|string                    $websiteId
     * @param int|string                    $storeId
     */
    public function afterRemoveAppliedStoreCredit(\Bolt\Boltpay\Helper\Discount $subject, $result, $couponCode, $quote, $websiteId, $storeId)
    {
        if ($couponCode == \Bolt\J2tRewardpointsSupport\Helper\Data::J2T_REWARD_POINTS) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setRewardpointsQuantity(0)->collectTotals();
            $this->quoteRepository->save($quote);
        }
        return $result;
    }
}