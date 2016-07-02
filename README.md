## Installing

- Setup a fresh Statamic install
- Unzip the Rainforest package
- Replace the contents of `site` with those from the package

**Integrating into an existing site**

If you're integrating Rainforest into an existing site, cherrypick these instructions and take care not to overwrite settings or content files you might need. The following steps are are necessary at a minimum.

- Move the addons from `addons/` into `/site/addons`
- Move `theme/rainforest` into `site/themes/`
- Move the contents of `content` into `site/content`
- Move the contents of `settings` into `site/settings`

## Configuring Stripe

If you don't have a Stripe account, you can [create one](https://dashboard.stripe.com/register). Once logged in, you can get your keys in the [API Keys](https://dashboard.stripe.com/account/apikeys) section of your account.

Copy `.env.example` to `.env` and set the values of the two settings to match your API keys.

```
STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
```

## Theme customization

As long as you like the basic theme, Rainforest is very close to drop-and-go ready. You'll want to edit the Globals file to set your `company` name, and update the logo in `site/themes/rainforest/img/logo.png` but that's pretty much it. If you want to redesign everything, go right ahead. The `gulpfile` necessary to compile the SASS and is included. Do you thing!

## Clients

Clients are necessary to help you group invoices. Clients are simply Users without any permissions. If you set their `email`, you'll be able to send pre-populated emails from your dashboard with a link to their invoice.

Rainforest includes two dummy clients so you can see how everything works. You'll probably want to delete them when you're done exploring.

## Invoices

Invoices are entries inside the `invoices` collection. All the fields necessary are wired up with a matching fieldset, so you can simply use the Control Panel to set an invoice's `price`, `client`, and even track `hours`.

If you set a `price`, it will override the hourly calculation.

Once an invoice is paid with Stripe, it will automatically be marked as `PAID`.

Rainforest includes four dummy invoices so you can see how everything works. You'll probably want to delete them along with the dummy clients when you're done exploring.

## Admin User

You can create your own user(s) of course, but if you want to just jump right in, you can simply set a password for the admin user (found in `site/users/admin.yaml`).
