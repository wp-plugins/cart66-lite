<?php
$settingsOk = true;
$username = Cart66Setting::getValue('paypalpro_api_username');
$password = Cart66Setting::getValue('paypalpro_api_password');
$signature = Cart66Setting::getValue('paypalpro_api_signature');
if(!($username && $password && $signature)) {
  $settingsOk = false;
  ?>
  <div class='Cart66Error'>
    <p><strong><?php _e( 'PayPal Express Checkout Is Not Configured' , 'cart66' ); ?></strong></p>
    <p><?php _e( 'In order to use PayPal Express Checkout you must enter your PayPal API username, password and signature in the Cart66 Settings Panel' , 'cart66' ); ?></p>
  </div>
  <?php
}

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['cart66-action']) && $_POST['cart66-action'] == 'paypalexpresscheckout') {
  // Set up the PayPal object
  $pp = new Cart66PayPalExpressCheckout();
  
  // Calculate total amount to charge customer
  $total = Cart66Session::get('Cart66Cart')->getGrandTotal(false);
  $total = number_format($total, 2, '.', '');
  
  // Calculate total cost of all items in cart, not including tax and shipping
  $itemTotal = Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount() - Cart66Session::get('Cart66Cart')->getDiscountAmount();
  $itemTotal = number_format($itemTotal, 2, '.', '');
  
  // Calculate shipping costs
  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
  
  // Calculate IPN URL
  $ipnPage = get_page_by_path('store/ipn');
  $ipnUrl = get_permalink($ipnPage->ID);

  // Set shipping as an item if the item total is $0.00, otherwise PayPal will fail
  if($itemTotal == 0 && $shipping > 0) {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Setting shipping to be an item because the item total would otherwise be $0.00");
    $itemTotal = $shipping;
    $itemData = array(
      'NAME' => 'Shipping',
      'AMT' => $shipping,
      'NUMBER' => 'SHIPPING',
      'QTY' => 1
    );
    $pp->addItem($itemData);
    $shipping = 0;
  }
  else {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not making shipping part of the item list. Item Total: $itemTotal");
  }
  
  // Set payment information
  $payment = array(
    'AMT' => $total,
    'CURRENCYCODE' => CURRENCY_CODE,
    'ITEMAMT' => $itemTotal,
    'SHIPPINGAMT' => $shipping,
    'NOTIFYURL' => $ipnUrl
  );
  $pp->setPaymentDetails($payment);
  
  // Add cart items to PayPal
  $pp->populatePayPalCartItems();
  
  // Set Express Checkout URLs
  $returnPage = get_page_by_path('store/express');
  $returnUrl = get_permalink($returnPage->ID);
  $cancelPage = get_page_by_path('store/checkout');
  $cancelUrl = get_permalink($cancelPage->ID);
  $ecUrls = array(
    'RETURNURL' => $returnUrl,
    'CANCELURL' => $cancelUrl
  );
  $pp->setEcUrls($ecUrls);
  
  $response = $pp->SetExpressCheckout();
  $ack = strtoupper($response['ACK']);
  if('SUCCESS' == $ack || 'SUCCESSWITHWARNING' == $ack) {
    Cart66Session::set('PayPalProToken', $response['TOKEN']);
    $expressCheckoutUrl = $pp->getExpressCheckoutUrl($response['TOKEN']);
  	header("Location: $expressCheckoutUrl");
  	exit;
  }
  elseif(empty($ack)) {
      echo '<pre>Failed to connect via curl to PayPal. The most likely cause is that your PHP installation failed to verify that the CA cert is OK</pre>';
  }
  else {
    echo "<pre>PayPal Response: $ack\n";
    print_r($response);
    echo "</pre>";
  }
}
?>

<?php if($settingsOk): ?>
<form action="" method='post' id="paypalexpresscheckout">
  <input type='hidden' name='cart66-action' value='paypalexpresscheckout'>
  <input type="image" id='PayPalExpressCheckoutButton' src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" value="PayPal Express Checkout" name="PayPal Express Checkout" />
</form>
<?php endif; ?>