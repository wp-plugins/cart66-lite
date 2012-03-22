<?php
$supportedGateways = array (
  'Cart66AuthorizeNet',
  'Cart66PayPalPro',
  'Cart66ManualGateway',
  'Cart66Eway',
  'Cart66MerchantWarrior',
  'Cart66PayLeap',
  'Cart66Mijireh',
  'Cart66Stripe'
);

$errors = array();
$createAccount = false;
$gateway = $data['gateway']; // Object instance inherited from Cart66GatewayAbstract 

if($_SERVER['REQUEST_METHOD'] == "POST") {
  $cart = Cart66Session::get('Cart66Cart');
  
  $account = false;
  if($cart->hasMembershipProducts() || $cart->hasSpreedlySubscriptions()) {
    // Set up a new Cart66Account and start by pre-populating the data or load the logged in account
    if($accountId = Cart66Common::isLoggedIn()) {
      $account = new Cart66Account($accountId);
    }
    else {
      $account = new Cart66Account();
      if(isset($_POST['account'])) {
        $acctData = Cart66Common::postVal('account');
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Account Data: " . print_r($acctData, true));
        $account->firstName = $acctData['first_name'];
        $account->lastName = $acctData['last_name'];
        $account->email = $acctData['email'];
        $account->username = $acctData['username'];
        $account->password = md5($acctData['password']);
        $errors = $account->validate();
        $jqErrors = $account->getJqErrors();
        if($acctData['password'] != $acctData['password2']) {
          $errors[] = __("Passwords do not match","cart66");
          $jqErrors[] = 'account-password';
          $jqErrors[] = 'account-password2';
        }
        if(count($errors) == 0) { $createAccount = true; }
        else {
          if(count($errors)) {
            try {
              throw new Cart66Exception(__('Your order could not be processed for the following reasons:', 'cart66'), 66500);
            }
            catch(Cart66Exception $e) {
              $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), $errors);
              echo Cart66Common::getView('views/error-messages.php', $exception);
            }
          }
        }
        
         // An account should be created and the account data is valid
      }
    }
  }
  
  $gatewayName = Cart66Common::postVal('cart66-gateway-name');
  
  if(in_array($gatewayName, $supportedGateways)) {
      
    $gateway->validateCartForCheckout();
    
    $gateway->setBilling(Cart66Common::postVal('billing'));
    $gateway->setPayment(Cart66Common::postVal('payment'));
    
    // If shipping data is set, pass it to the gateway, it could be mijireh which does not have a "same as billing" checkbox
    if(isset($_POST['shipping'])) {
      $gateway->setShipping(Cart66Common::postVal('shipping'));
    }
    
    if(isset($_POST['sameAsBilling'])) {
      $gateway->setShipping(Cart66Common::postVal('billing'));
    }
    
    $s = $gateway->getShipping();
    if($s['state'] && $s['zip']){
      $taxLocation = $gateway->getTaxLocation();
      $tax = $gateway->getTaxAmount();
      Cart66Session::set('Cart66Tax',$tax);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Tax PreCalculated: $".$tax);
    }

    if(count($errors) == 0) {
      $errors = $gateway->getErrors();     // Error info for server side error code
      if(count($errors)) {
        try {
          throw new Cart66Exception(__('Your order could not be processed for the following reasons:', 'cart66'), 66500);
        }
        catch(Cart66Exception $e) {
          $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), $errors);
          echo Cart66Common::getView('views/error-messages.php', $exception);
        }
      }
      $jqErrors = $gateway->getJqErrors(); // Error info for client side error code
    }
    
    if(count($errors) == 0 || 1) {
      // Calculate final billing amounts
      $taxLocation = $gateway->getTaxLocation();
      $tax = $gateway->getTaxAmount();
      $total = Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax;
      $subscriptionAmt = Cart66Session::get('Cart66Cart')->getSubscriptionAmount();
      $oneTimeTotal = $total - $subscriptionAmt;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Tax: $tax | Total: $total | Subscription Amount: $subscriptionAmt | One Time Total: $oneTimeTotal");

      // Throttle checkout attempts
      if(!Cart66Session::get('Cart66CheckoutThrottle')) {
        Cart66Session::set('Cart66CheckoutThrottle', Cart66CheckoutThrottle::getInstance(), true);
      }

      if(!Cart66Session::get('Cart66CheckoutThrottle')->isReady($gateway->getCardNumberTail(), $oneTimeTotal)) {
        try {
          throw new Cart66Exception(__('Your order could not be processed for the following reasons:', 'cart66'), 66500);
        }
        catch(Cart66Exception $e) {
          $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), "You must wait " . Cart66Session::get('Cart66CheckoutThrottle')->getTimeRemaining() . " more seconds before trying to checkout again.");
          echo Cart66Common::getView('views/error-messages.php', $exception);
        }
      }
    }
    
    
    // Charge credit card for one time transaction using Authorize.net API
    if(count($errors) == 0 && !Cart66Session::get('Cart66InventoryWarning')) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] start working on charging the credit card");
      
      // =============================
      // = Start Spreedly Processing =
      // =============================
      
      if(Cart66Session::get('Cart66Cart')->hasSpreedlySubscriptions()) {
        
        $accountErrors = $account->validate();
        if(count($accountErrors) == 0) {
          $account->save(); // Save account data locally which will create an account id and/or update local values
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account data validated and saved for account id: " . $account->id);
          
          try {
            $spreedlyCard = new SpreedlyCreditCard();
            $spreedlyCard->hydrateFromCheckout();
            $subscriptionId = Cart66Session::get('Cart66Cart')->getSpreedlySubscriptionId();
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to create a new spreedly account subscription: Account ID: $account->id | Subscription ID: $subscriptionId");
            $accountSubscription = new Cart66AccountSubscription();
            $accountSubscription->createSpreedlySubscription($account->id, $subscriptionId, $spreedlyCard);
          }
          catch(SpreedlyException $e) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to checkout: " . $e->getCode() . ' ' . $e->getMessage());
            $errors['spreedly failed'] = $e->getMessage();
            $accountSubscription->refresh();
            if(empty($accountSubscription->subscriberToken)) {
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to delete local account after spreedly failure: " . print_r($account->getData(), true));
              $account->deleteMe();
            }
            else {
              // Set the subscriber token in the session for repeat attempts to create the subscription
              Cart66Session::set('Cart66SubscriberToken', $account->subscriberToken);
            }
          }
          
        }
        else {
          $errors = $account->getErrors();
          if(count($errors)) {
            try {
              throw new Cart66Exception(__('Your order could not be processed for the following reasons:', 'cart66'), 66500);
            }
            catch(Cart66Exception $e) {
              $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), $errors);
              echo Cart66Common::getView('views/error-messages.php', $exception);
            }
          }
          $jqErrors = $account->getJqErrors();
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed. " . print_r($errors, true));
        }
      }
      
      // ===========================
      // = End Spreedly Processing =
      // ===========================
       
      
       
      if(count($errors) == 0) {
        
        // Look for constant contact opt-in
        if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66ConstantContactOptIn.php"); }
        
        // Look for mailchimp opt-in
        if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66MailChimpOptIn.php"); }
        
        $gatewayName = get_class($gateway);
        $gateway->initCheckout($oneTimeTotal);
        if($oneTimeTotal > 0 || $gatewayName == 'Cart66ManualGateway') {
          $transactionId = $gateway->doSale();
        }
        else {
          // Do not attempt to charge $0.00 transactions to live gateways
          $transactionId = $transId = 'MT-' . Cart66Common::getRandString();
        }
        
        if($transactionId) {
          // Set order status based on Cart66 settings
          $statusOptions = Cart66Common::getOrderStatusOptions();
          $status = $statusOptions[0];
          
          // Check for account creation
          $accountId = 0;
          if($createAccount) { $account->save(); }
          if($mp = Cart66Session::get('Cart66Cart')->getMembershipProduct()) { 
            $account->attachMembershipProduct($mp, $account->firstName, $account->lastName);
            $accountId = $account->id;
          }

          // Save the order locally
          $orderId = $gateway->saveOrder($total, $tax, $transactionId, $status, $accountId);

          
          Cart66Session::drop('Cart66SubscriberToken');
          Cart66Session::set('order_id', $orderId);
          $receiptLink = Cart66Common::getPageLink('store/receipt');
          $newOrder = new Cart66Order($orderId);
          
          // Send email receipts
          Cart66Common::sendEmailReceipts($orderId);
          
          // Send buyer to receipt page
          $receiptVars = strpos($receiptLink, '?') ? '&' : '?';
          $receiptVars .= "ouid=" . $newOrder->ouid;
          header("Location: " . $receiptLink . $receiptVars);
        }
        else {
          // Attempt to discover reason for transaction failure
          
          try {
            throw new Cart66Exception(__('Your order could not be completed for the following reasons:', 'cart66'), 66500);
          }
          catch(Cart66Exception $e) {
            $gatewayResponse = $gateway->getTransactionResponseDescription();
            $exception = Cart66Exception::exceptionMessages($e->getCode(), $e->getMessage(), array('Error Number: ' . $gatewayResponse['errorcode'], strtolower($gatewayResponse['errormessage'])));
            echo Cart66Common::getView('views/error-messages.php', $exception);
          }
          
          //$errors['Could Not Process Transaction'] = $gateway->getTransactionResponseDescription();
        }
      }
      
    }
    
  } // End if supported gateway 
} // End if POST


// Show inventory warning if there is one
if(Cart66Session::get('Cart66InventoryWarning')) {
  echo Cart66Session::get('Cart66InventoryWarning');
  Cart66Session::drop('Cart66InventoryWarning');
}


// Build checkout form action URL
$checkoutPage = get_page_by_path('store/checkout');
$ssl = Cart66Setting::getValue('auth_force_ssl');
$url = get_permalink($checkoutPage->ID);
if(Cart66Common::isHttps()) {
  $url = str_replace('http:', 'https:', $url);
}

// Determine which gateway is in use
$gatewayName = get_class($data['gateway']);

// Make it easier to get to payment, billing, and shipping data
$p = $gateway->getPayment();
$b = $gateway->getBilling();
$s = $gateway->getShipping();

$billingCountryCode =  (isset($b['country']) && !empty($b['country'])) ? $b['country'] : Cart66Common::getHomeCountryCode();
$shippingCountryCode = (isset($s['country']) && !empty($s['country'])) ? $s['country'] : Cart66Common::getHomeCountryCode();

// Include the HTML markup for the checkout form
$userViewFile = get_stylesheet_directory() . "/cart66-templates/views/checkout-form.php";
$checkoutFormFile = CART66_PATH . 'views/checkout-form.php';

if($gatewayName == 'Cart66Mijireh') {
  $checkoutFormFile = CART66_PATH . 'views/mijireh/shipping_address.php';
}

if(file_exists($userViewFile) && filesize($userViewFile)>10 && CART66_PRO && Cart66Common::isRegistered()) {
	$checkoutFormFile = $userViewFile;
}

Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using Checkout Form File :: $checkoutFormFile");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  include_once($checkoutFormFile);
}
else {
  include($checkoutFormFile);
}

// Include the client side javascript validation                 
include_once(CART66_PATH . '/views/client/checkout.php'); 