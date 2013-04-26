<?php
class Cart662Checkout extends Cart66GatewayAbstract {
  
  protected $_purchase_data;
  public $secret;
  public $gatewayUrl;
  public $fields = array();
  
  public function getCreditCardTypes() {
    // 2Checkout does not use credit cards
    return array();
  }
  
  public function setPayment($p) {
    $this->_payment['phone'] = isset($p['phone']) ? $p['phone'] : '';
    $this->_payment['email'] = isset($p['email']) ? $p['email'] : '';
    
    // For subscription accounts
    if(isset($p['password'])) {
      if($p['password'] != $p['password2']) {
        $this->_errors['Password'] = __('Passwords do not match', 'cart66');
        $this->_jqErrors[] = 'payment-password';
      }
    }
  }
  
  public function initCheckout($total, $override=false) {
    if((isset($_POST['cart66-task']) && $_POST['cart66-task'] == '2checkout') || ($override)) {
      $pendingOrderId = $this->storePendingOrder();
      $order = new Cart66Order($pendingOrderId);
      Cart66Session::set('Cart66PendingOUID', $order->ouid);
      $redirect = $this->get_redirect_url();

      if(Cart66Setting::getValue('tco_test_mode')) {
        $redirect .= '&demo=Y';
      }
      
      // Start affiliate program integration
      $aff = '';
      if(Cart66Session::get('ap_id')) {
        $aff .= Cart66Session::get('ap_id');
      }
      elseif(isset($_COOKIE['ap_id'])) {
        $aff .= $_COOKIE['ap_id'];
      }
      // End affilitate program integration
      
      $redirect .= '&custom=' . $order->ouid . '|' . $aff . '|';
      // Redirect to 2Checkout
      
      //print_r($redirect);
      wp_redirect($redirect);
      exit;
    }
  }
  
  public function doSale() {
    // 2Checkout has a multi-step sale process and is implemented apart from this function
    return false;
  }
  
  public function getTransactionResponseDescription() {
    // 2Checkout handles errors in a way that is implemented without this function.
    return '';
  }
  
  public function __construct() {
    $this->gatewayUrl = 'https://www.2checkout.com/checkout/purchase';
  }
  
  public function addField($field, $value) {
    $this->fields[$field] = $value;
  }
  
  public function removeField($field) {
    unset($this->fields[$field]);
  }

  public function setSecret($word) {
    if(!empty($word)) {
      $this->secret = $word;
    }
  }
  
  public function purchase_data($data) {
    $this->_purchase_data = $data;
  }
  
  public function get_redirect_url() {
    // Specify your 2CheckOut vendor id
    $this->addField('sid', Cart66Setting::getValue('tco_account_number'));

    // Specify the order information
    $items = Cart66Session::get('Cart66Cart')->getItems();
    $number = 0;
    $item_amount = array();
    foreach($items as $i) {
      $product = new Cart66Product($i->getProductId());
      $this->addField('li_' . $number . '_type', 'product');
      $this->addField('li_' . $number . '_name', $product->name);
      $this->addField('li_' . $number . '_price', number_format($i->getProductPrice(), 2, '.', ''));
      $this->addField('li_' . $number . '_product_id', $i->getItemNumber());
      $this->addField('li_' . $number . '_quantity', $i->getQuantity());
      $this->addField('li_' . $number . '_tangible', 'N');
      $item_amount[] = number_format($i->getProductPrice(), 2, '.', '');
      $number++;
    }
    
    $item_amount = array_sum($item_amount);
    $total_amount = number_format(Cart66Session::get('Cart66Cart')->getGrandTotal() + Cart66Session::get('Cart66Tax'), 2, '.', '');
    
    // Discounts
    $promotion = Cart66Session::get('Cart66Promotion');
    if($promotion) {
      $this->addField('li_' . $number . '_type', 'coupon');
      $this->addField('li_' . $number . '_name', $promotion->name);
      $this->addField('li_' . $number . '_price', Cart66Session::get('Cart66Cart')->getDiscountAmount());
      $this->addField('li_' . $number . '_product_id', __('Discount', 'cart66') . '(' . Cart66Session::get('Cart66PromotionCode') . ')');
      $this->addField('li_' . $number . '_quantity', 1);
      $this->addField('li_' . $number . '_tangible', 'N');
      $number++;
    }
    
    // Shipping
    $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
    if(CART66_PRO && Cart66Setting::getValue('use_live_rates')) {
      $selectedRate = Cart66Session::get('Cart66LiveRates')->getSelected();
      $shippingMethod = $selectedRate->service;
    }
    else {
      $method = new Cart66ShippingMethod(Cart66Session::get('Cart66Cart')->getShippingMethodId());
      $shippingMethod = $method->name;
    }
    if($shipping > 0) {
      $this->addField('li_' . $number . '_type', 'product');
      $this->addField('li_' . $number . '_product_id', __('Shipping', 'cart66'));
      $this->addField('li_' . $number . '_name', $shippingMethod);
      $this->addField('li_' . $number . '_price', $shipping);
      $this->addField('li_' . $number . '_quantity', 1);
      $this->addField('li_' . $number . '_tangible', 'N');
      $number++;
      // Shipping Fields
      if(strlen($this->_shipping['address']) > 3) {
        $this->addField('ship_name', $this->_shipping['firstName'] . ' ' . $this->_shipping['lastName']);
        $this->addField('ship_street_address', $this->_shipping['address']);
        $this->addField('ship_street_address2', $this->_shipping['address2']);
        $this->addField('ship_city', $this->_shipping['city']);
        $this->addField('ship_state', $this->_shipping['state']);
        $this->addField('ship_zip', $this->_shipping['zip']);
        $this->addField('ship_country', $this->_shipping['country']);
        $this->addField('phone', $this->_payment['phone']);
      }
    }
    
    // Tax
    $tax = Cart66Session::get('Cart66Tax');
    if($tax > 0) {
      $this->addField('li_' . $number . '_type', 'tax');
      $this->addField('li_' . $number . '_product_id', __('Tax', 'cart66'));
      $this->addField('li_' . $number . '_name', Cart66Session::get('Cart66TaxRate'));
      $this->addField('li_' . $number . '_price', $tax);
      $this->addField('li_' . $number . '_quantity', 1);
      $this->addField('li_' . $number . '_tangible', 'N');
      $number++;
    }
    
    // Default Fields
    $this->addField('mode', '2CO');
    $this->addField('return_url', Cart66Setting::getValue('shopping_url') );
    $this->addField('pay_method', 'CC');
    $this->addField('x_receipt_link_url', add_query_arg('listener', '2CO', Cart66Common::getPageLink('store/receipt')));
    $this->addField('tco_currency', 'USD');
    
    $redirect_url = $this->gatewayUrl . '?' . http_build_query($this->fields, '', '&');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $redirect_url");
    
    return $redirect_url;
    
  }
  
  public function storePendingOrder() {
    $orderInfo = array();
    $orderInfo['bill_address'] = '';
    $orderInfo['coupon'] = Cart66Common::getPromoMessage();
    $orderInfo['shipping'] = Cart66Session::get('Cart66Cart')->getShippingCost();
    $orderInfo['trans_id'] = '';
    $orderInfo['status'] = 'checkout_pending';
    $orderInfo['ordered_on'] = date('Y-m-d H:i:s', Cart66Common::localTs());
    $orderInfo['shipping_method'] = Cart66Session::get('Cart66Cart')->getShippingMethodName();
    $orderInfo['account_id'] = 0;
    $orderInfo['total'] = Cart66Session::get('Cart66Cart')->getGrandTotal() + Cart66Session::get('Cart66Tax');
    $orderInfo['tax'] = Cart66Session::get('Cart66Tax');
    $orderInfo['ship_first_name'] = $this->_shipping['firstName'];
    $orderInfo['ship_last_name'] = $this->_shipping['lastName'];
    $orderInfo['ship_address'] = $this->_shipping['address'];
    $orderInfo['ship_address2'] = $this->_shipping['address2'];
    $orderInfo['ship_city'] = $this->_shipping['city'];
    $orderInfo['ship_state'] = $this->_shipping['state'];
    $orderInfo['ship_zip'] = $this->_shipping['zip'];
    $orderInfo['ship_country'] = $this->_shipping['country'];
    $orderId = Cart66Session::get('Cart66Cart')->storeOrder($orderInfo);
    return $orderId;
  }
  
  public function saveOrder() {
    global $wpdb;
    // NEW Parse custom value
    $referrer = false;
    $ouid = $_POST['custom'];
    if(strpos($ouid, '|') !== false) {
      list($ouid, $referrer) = explode('|', $ouid);
    }
    $order = new Cart66Order();
    $order->loadByOuid($ouid);
    
    if($order->id > 0 && $order->status == 'checkout_pending' && $_POST['total'] == $order->total) {
      $statusOptions = Cart66Common::getOrderStatusOptions();
      $status = $statusOptions[0];
      
      $data = array(
        'bill_first_name' => $_POST['first_name'],
        'bill_last_name' => $_POST['last_name'],
        'bill_address' => $_POST['street_address'],
        'bill_address2' => $_POST['street_address2'],
        'bill_city' => $_POST['city'],
        'bill_state' => $_POST['state'],
        'bill_zip' => $_POST['zip'],
        'bill_country' => $_POST['country'],
        'email' => $_POST['email'],
        //'tax' => $pp['tax'],
        'trans_id' => $_POST['order_number'],
        'ordered_on' => date('Y-m-d H:i:s', Cart66Common::localTs()),
        'status' => $status
      );


      // Verify the first items in the IPN are for products managed by Cart66. It could be an IPN from some other type of transaction.
      $productsTable = Cart66Common::getTableName('products');
      $orderItemsTable = Cart66Common::getTableName('order_items');
      $sql = "SELECT id from $productsTable where item_number = '" . $_POST['li_0_product_id'] . "'";
      $productId = $wpdb->get_var($sql);
      if(!$productId) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to throw an exception, this is not an IPN that should be managed by cart66 because the item number does not match up");
        throw new Exception("This is not an IPN that should be managed by Cart66");
      }
      $order->setData($data);
      $order->save();
      $orderId = $order->id;
      
      // Handle email receipts
      if(CART66_PRO && Cart66Setting::getValue('enable_advanced_notifications') ==1) {
        $notify = new Cart66AdvancedNotifications($orderId);
        $notify->sendAdvancedEmailReceipts();
      }
      else {
        $notify = new Cart66Notifications($orderId);
        $notify->sendEmailReceipts();
      }
      
      // Process affiliate reward if necessary
      if($referrer && CART66_PRO) {
        Cart66Common::awardCommission($order->id, $referrer);
        // End processing affiliate information
        if(isset($_COOKIE['ap_id']) && $_COOKIE['ap_id']) {
          setcookie('ap_id',$referrer, time() - 3600, "/");
          unset($_COOKIE['ap_id']);
        }
        Cart66Session::drop('app_id');
      }
      if(CART66_PRO) {
        // Begin iDevAffiliate Tracking
        if(CART66_PRO && $url = Cart66Setting::getValue('idevaff_url')) {
          require_once(CART66_PATH . "/pro/idevaffiliate-award.php");
        }
        // End iDevAffiliate Tracking
      }
      
      wp_redirect(remove_query_arg('listener', Cart66Common::getCurrentPageUrl()));
      exit;
    }
  }
  
}