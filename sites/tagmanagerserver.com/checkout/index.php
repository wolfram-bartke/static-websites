<script src="https://js.stripe.com/v3/"></script>
<script>
var stripe = Stripe('pk_test_4np9mIfIFmd6JkWW4ImEad8700DFCA4xHY');
stripe.redirectToCheckout({
  lineItems: [
    // Replace with the ID of your price
    {price: 'price_HJ4a4Jnjqn25Gq', quantity: 1}
  ],
  mode: 'payment',
  successUrl: 'https://www.tagmanagerserver.com/checkout/success/index.php',
  cancelUrl: 'https://www.tagmanagerserver.com/checkout/index.php?checkout=fail',
  billingAddressCollection: 'required',
}).then(function (result) {
  // If `redirectToCheckout` fails due to a browser or network
  // error, display the localized error message to your customer
  // using `result.error.message`.
});
</script>
