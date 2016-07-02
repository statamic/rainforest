<?php

namespace Statamic\Addons\Rainmaker;

use Exception;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Customer;
use Statamic\API\Content;
use Statamic\API\Request;
use Statamic\Extend\Listener;

class RainmakerListener extends Listener
{
    private $token;
    private $email;
    private $invoice;
    private $customer;
    private $charge;

    public $events = [
        'Rainmaker.process' => 'process'
    ];

    /**
     * Process the checkout request
     *
     * @return Response
     */
    public function process()
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $this->email = Request::get('stripeEmail');
        $this->token = Request::get('stripeToken');
        $this->invoice = Content::uuidRaw(Request::get('invoice'));
        $this->customer = $this->getCustomer();

        try {
            $this->charge();
        } catch (Exception $e) {
            return response('Error: ' . $e->getMessage(), 500);
        }

        $this->updateInvoice();

        return back();
    }

    /**
     * Perform the charge
     *
     * @return void
     */
    private function charge()
    {
        $this->charge = Charge::create([
            'customer' => $this->customer->id,
            'description' => $this->invoice->get('title'),
            'amount' => $this->invoice->get('price').'00',
            'currency' => 'usd',
            'metadata' => [
                'invoice_id' => $this->invoice->id(),
                'invoice_slug' => $this->invoice->slug(),
            ]
        ]);
    }

    /**
     * Get the Stripe Customer
     *
     * @return Customer
     */
    private function getCustomer()
    {
        // Look up a customer with the submitted email
        $customer = collect(Customer::all()['data'])->filter(function ($customer) {
            return $customer->email === $this->email;
        })->first();

        // If the customer already exists, we'll need to update the card
        // on file with the one that was submitted in the Checkout form.
        // Otherwise, we'll create a new customer from scratch.
        if ($customer) {
            $customer->source = $this->token;
            $customer->save();
        } else {
            $customer = $this->createNewCustomer();
        }

        return $customer;
    }

    /**
     * Create a new Customer
     *
     * @return Customer
     */
    private function createNewCustomer()
    {
        return Customer::create(array(
            'email' => $this->email,
            'source'  => $this->token
        ));
    }

    /**
     * Update the invoice
     *
     * @return void
     */
    private function updateInvoice()
    {
        $this->invoice->set('paid', true);
        $this->invoice->set('stripe_charge_id', $this->charge->id);
        $this->invoice->set('stripe_customer_id', $this->charge->customer);
        $this->invoice->save();
    }
}
