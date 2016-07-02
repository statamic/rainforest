# Stripe

This tag acts as some syntactic sugar for [Stripe Checkout][checkout]. It simplifies your templates.

The server-side processing is up to you.

### Stripe.js
A shortcut to placing Stripe.js on your page. Put this at the bottom of your layout.

```
{{ stripe:js }}

Outputs:
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
```

### Stripe Checkout
A shortcut for generating a Stripe Checkout form.

Can be used as a single tag, or tag pair. When using as a tag pair, the content between the
tags will be injected inside the generated `<form>`. This is useful for adding hidden
fields that your server side processing would need.

```
{{ stripe:checkout action="/my-action" amount="500" description="T-shirt" }}
    <input type="hidden" name="foo" value="bar" />
{{ /stripe:checkout }}

Outputs:
<form action="/my-action" method="POST">
    <script
        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="pk_test_6pRNASCoBOKtIshFeQd4XMUh"
        data-amount="500"
        data-name="My Company"
        data-description="T-shirt"
        data-image="/logo.png"
        ...
    ></script>
    <input type="hidden" name="foo" value="bar" />
</form>
```

#### Parameters

The `action` parameter specifies where the form will POST to after receiving a token from Stripe.
The server-side logic is up to you to develop with your own addon.

Any configuration option listed [in the Stripe Checkout docs](https://stripe.com/docs/checkout#integration-simple-options) are available to you on the tag
without the `data-` prefix, and with underscores instead of dashes.

For example, where in the docs it says `data-zip-code="true"`, you'd use `zip_code="true"`.

#### Configuration

You may create a `site/addons/stripe.yaml` file containing any parameters that should be used by
all `{{ stripe:checkout }}` tags on your site.

A great example would be to include your key, company name, logo image, etc.

```
name: Statamic
image: http://site.com/logo.png
key: pk_test_123456789
```


[checkout]: https://stripe.com/checkout
