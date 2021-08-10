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

namespace Bolt\J2tRewardpointsSupport\Plugin\Bolt\Boltpay\Block\Js;

class JsPlugin
{

    /**
     * Get Additional Javascript to invalidate BoltCart.
     *
     * @param \Bolt\Boltpay\Block\Js $subject
     * @param string                 $result
     *
     * @return string
     */
    public function afterGetAdditionalJavascript(\Bolt\Boltpay\Block\Js $subject, $result)
    {
        return $result . /** @lang JavaScript */ <<<'JavaScript'

var selectorsForInvalidate = ["#discount-point-form .applyPointsBtn","#discount-point-form .cancelPoints"];
for (var i = 0; i < selectorsForInvalidate.length; i++) {
    var button = document.querySelector(selectorsForInvalidate[i]);
    if (button) {
        button.addEventListener("click", function() {
            if (localStorage) {
                localStorage.setItem("bolt_cart_is_invalid", "true");
            }
        }, false);
    }
}
JavaScript;
    }
}