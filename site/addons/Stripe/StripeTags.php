<?php

namespace Statamic\Addons\Stripe;

use Statamic\Extend\Tags;

class StripeTags extends Tags
{
    public function js()
    {
        return '<script type="text/javascript" src="https://js.stripe.com/v2/"></script>';
    }

    public function checkout()
    {
        // Get the content to go between the tags. If it's a tag pair,
        // we'll parse the contents, otherwise just do nothing.
        $content = ($this->content === false) ? '' : $this->parse([]);

        return '
            <form action="' . $this->get('action', '/charge') . '" method="POST">
            '. csrf_field() .'
                <script
                    src="https://checkout.stripe.com/checkout.js"
                    class="stripe-button"
                    data-label="' . $this->get('label') . '"
                    data-key="'. $this->get('key') .'"
                    data-image="'. $this->get('image') .'"
                    data-name="'. $this->get('name') .'"
                    data-description="'. $this->get('description') .'"
                    data-amount="'. $this->get('amount') .'"
                    data-locale="auto"
                    data-zip-code="'. bool_str($this->getBool('zip_code')) .'"
                    data-billing-address="'. bool_str($this->getBool('billing_address', false)) .'"
                    data-currency="'. $this->get('currency', 'USD') .'"
                    data-panel-label="'. $this->get('panel_label') .'"
                    data-shipping-address="'. bool_str($this->getBool('shipping_address', false)) .'"
                    data-email="'. $this->get('email') .'"
                    data-allow-remember-me="'. bool_str($this->getBool('allow_remember_me', true)) .'"
                    data-bitcoin="'. bool_str($this->getBool('bitcoin', false)) .'"
                    data-alipay="'. bool_str($this->getBool('alipay', false)) .'"
                    data-alipay-reusable="'. bool_str($this->getBool('alipay_reusable', false)) .'"
                ></script>
                '. $content .'
            </form>';
    }
}
