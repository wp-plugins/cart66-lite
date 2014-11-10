<?php
abstract class Cart66GatewayAbstract {

  protected $_errors;
  protected $_jqErrors;
  protected $_billing;
  protected $_shipping;
  protected $_payment;
  protected $_taxRate;

  /**
   * Return an array of available credit card types where the keys are the display values and the values 
   * are the submitted values.
   * 
   * @return array
   */
  public abstract function getCreditCardTypes();
  
  /**
   * Prepare the gateway to process a transaction.
   * Perhaps api credentials need to be set or other pre-sales setup
   * 
   * @param decimal Total amount to charge to credit card
   */
  public abstract function initCheckout($total);
  
  /**
   * Attempt to process a sales
   */
  public abstract function doSale();
  
  /**
   * Return a description explaining the results from an attempt to process a transaction through a gateway.
   * This information may be the reason for a transaction decline or a transaction failure
   */
  public abstract function getTransactionResponseDescription();
  
  public function __construct() {
    $this->_billing = array(
      'firstName' => '',
      'lastName' => '',
      'address' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => ''
    );
    $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
    if(is_array($custom_billing_fields)) {
      foreach($custom_billing_fields as $key => $billing_field) {
        $this->_billing[$key] = isset($billing_field['default']) ? $billing_field['default'] : '';
      }
    }
    
    $this->_payment = array(
      'cardType' => '',
      'cardNumber' => '',
      'cardExpirationMonth' => '',
      'cardExpirationYear' => '',
      'securityId' => '',
      'phone' => '',
      'email' => '',
      'password' => '',
      'password2' => '',
      'custom-field' => ''
    );
    $custom_payment_fields = apply_filters('cart66_after_payment_form', '');
    if(is_array($custom_payment_fields)) {
      foreach($custom_payment_fields as $key => $payment_field) {
        $this->_payment[$key] = isset($payment_field['default']) ? $payment_field['default'] : '';
      }
    }
    
    $this->_shipping = array(
      'firstName' => '',
      'lastName' => '',
      'address' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => ''
    );
    $custom_shipping_fields = apply_filters('cart66_after_shipping_form', '');
    if(is_array($custom_shipping_fields)) {
      foreach($custom_shipping_fields as $key => $shipping_field) {
        $this->_shipping[$key] = isset($shipping_field['default']) ? $shipping_field['default'] : '';
      }
    }
    
  }
  
  public function getErrors() {
    if(!is_array($this->_errors)) {
      $this->_errors = array();
    }
    return $this->_errors;
  }

  public function getJqErrors() {
    if(!is_array($this->_jqErrors)) {
      $this->_jqErrors = array();
    }
    return $this->_jqErrors;
  }

  public function clearErrors() {
    $this->_errors = array();
    $this->_jqErrors = array();
  }

  public function setBilling($b) {
    if(is_array($b)) {
      if(!(isset($b['state']) && !empty($b['state']))) {
        if(isset($b['state_text'])) {
          $b['state'] = trim($b['state_text']);
        }
      }
      unset($b['state_text']);

      $this->_billing = $b;
      $skip = array('address2', 'billing-state_text');
      $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
      if(is_array($custom_billing_fields)) {
        foreach($custom_billing_fields as $key => $billing_field) {
          if(!$billing_field['required']) {
            $skip[] = $billing_field['slug'];
          }
          if(isset($billing_field['validator']) && $billing_field['validator'] != '') {
            if(function_exists($billing_field['validator'])) {
              $skip[] = $billing_field['slug'];
              $data_to_validate = isset($b[$billing_field['slug']]) ? $b[$billing_field['slug']] : '';
              $validated = call_user_func($billing_field['validator'], $data_to_validate);
              if(!$validated['valid']) {
                foreach($validated['errors'] as $key => $error) {
                  $this->_errors['Billing ' . $billing_field['slug'] . $key] = $error;
                  $this->_jqErrors[] = 'billing-' . $billing_field['slug'];
                }
              }
            }
          }
        }
      }
      foreach($b as $key => $value) {
        if(!in_array($key, $skip)) {
          $value = trim($value);
          if($value == '') {
            $keyName = ucwords(preg_replace('/([A-Z])/', " $1", $key));
            $this->_errors['Billing ' . $keyName] = __('Billing ','cart66') . $keyName . __(' required','cart66');
            $this->_jqErrors[] = "billing-$key";
          }
        }
      }
    }
  } 

  public function setPayment($p) {
    if(is_array($p)) {
      // Remove all non-numeric characters from card number
      if(isset($p['cardNumber'])) {
        $cardNumber = $p['cardNumber'];
        $p['cardNumber'] = preg_replace('/\D/', '', $cardNumber);
      }

      $this->_payment = $p;
      $skip = array('email', 'phone', 'custom-field');
      $custom_payment_fields = apply_filters('cart66_after_payment_form', '');
      if(is_array($custom_payment_fields)) {
        foreach($custom_payment_fields as $key => $payment_field) {
          if(!$payment_field['required']) {
            $skip[] = $payment_field['slug'];
          }
          if(isset($payment_field['validator']) && $payment_field['validator'] != '') {
            if(function_exists($payment_field['validator'])) {
              $skip[] = $payment_field['slug'];
              $data_to_validate = isset($p[$payment_field['slug']]) ? $p[$payment_field['slug']] : '';
              $validated = call_user_func($payment_field['validator'], $data_to_validate);
              if(!$validated['valid']) {
                foreach($validated['errors'] as $key => $error) {
                  $this->_errors['Payment ' . $payment_field['slug'] . $key] = $error;
                  $this->_jqErrors[] = 'payment-' . $payment_field['slug'];
                }
              }
            }
          }
        }
      }
      foreach($p as $key => $value) {
        if(!in_array($key, $skip)) {
          $value = trim($value);
          if($value == '') {
            $keyName = ucwords(preg_replace('/([A-Z])/', " $1", $key));
            $this->_errors['Payment ' . $keyName] = __('Payment ','cart66') . $keyName . __(' required','cart66');
            $this->_jqErrors[] = "payment-$key";
          }
        }
      }
      foreach($p as $key => $value) {
        if($key == 'custom-field') {
          continue;
        }
        $value = trim($value);
        if($value == '') {
          $keyName = preg_replace('/([A-Z])/', " $1", $key);
          $this->_errors['Payment ' . $keyName] = __('Payment ','cart66') . $keyName . __(' required','cart66');
          $this->_jqErrors[] = "payment-$key";
        }
      }
      if(strlen($p['cardNumber']) > 0 && strlen($p['cardNumber']) < 13) {
        $this->_errors['Payment Card Number'] = __('Invalid credit card number','cart66');
        $this->_jqErrors[] = "payment-cardNumber";
      }
      
      if(!Cart66Common::isValidEmail($p['email'])) {
        $this->_errors['Email'] = __("Email address is not valid","cart66");
        $this->_jqErrors[] = 'payment-email';
      }
      
      if(Cart66Setting::getValue('checkout_custom_field_display') == 'required' && $p['custom-field'] == '') {
        $this->_errors['Custom Field'] = Cart66Setting::getValue('checkout_custom_field_error_label') ? Cart66Setting::getValue('checkout_custom_field_error_label') : __('The Special Instructions Field is required', 'cart66');
        $this->_jqErrors[] = 'checkout-custom-field-multi';
        $this->_jqErrors[] = 'checkout-custom-field-single';
      }

      // For subscription accounts
      if(isset($p['password'])) {
        if($p['password'] != $p['password2']) {
          $this->_errors['Password'] = __("Passwords do not match","cart66");
          $this->_jqErrors[] = 'payment-password';
        }
      }
    }
  }

  public function setShipping($s, $billing_fields=false) {
    if(is_array($s)) {
      if(!(isset($s['state']) && !empty($s['state']))) {
        $s['state'] = trim($s['state_text']);
      }
      unset($s['state_text']);

      $this->_shipping = $s;
      $skip = array('address2', 'shipping-state_text');
      $custom_shipping_fields = apply_filters('cart66_after_shipping_form', '');
      if(is_array($custom_shipping_fields)) {
        foreach($custom_shipping_fields as $key => $shipping_field) {
          if(!$shipping_field['required']) {
            $skip[] = $shipping_field['slug'];
          }
          if(isset($shipping_field['validator']) && $shipping_field['validator'] != '') {
            if(function_exists($shipping_field['validator'])) {
              $skip[] = $shipping_field['slug'];
              $data_to_validate = isset($s[$shipping_field['slug']]) ? $s[$shipping_field['slug']] : '';
              $validated = call_user_func($shipping_field['validator'], $data_to_validate);
              if(!$validated['valid']) {
                foreach($validated['errors'] as $key => $error) {
                  $this->_errors['Shipping ' . $shipping_field['slug'] . $key] = $error;
                  $this->_jqErrors[] = 'shipping-' . $shipping_field['slug'];
                }
              }
            }
          }
        }
      }
      if($billing_fields) {
        $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
        if(is_array($custom_billing_fields)) {
          foreach($custom_billing_fields as $key => $billing_field) {
            if(!$billing_field['required']) {
              $skip[] = $billing_field['slug'];
            }
          }
        }
      }
      foreach($s as $key => $value) {
        if(!in_array($key, $skip)) {
          $value = trim($value);
          if($value == '') {
            $keyName = preg_replace('/([A-Z])/', " $1", $key);
            $this->_errors['Shipping ' . $keyName] = __('Shipping ','cart66') . $keyName . __(' required','cart66');
            $this->_jqErrors[] = "shipping-$key";
          }
        }
      }
    }
  }

  public function getShipping() {
    return count($this->_shipping) ? $this->_shipping : $this->_billing;
  }

  public function getBilling() {
    return $this->_billing;
  }

  public function getPayment() {
    return $this->_payment;
  }

  /**
   * Return and array with state and zip of shipping location
   * array('state' => 'XX', 'zip' => 'YYYYY');
   *
   * @return array
   */
  public function getTaxLocation() {
    $ship = $this->getShipping();
    $taxLocation = array (
      'state' => $ship['state'],
      'zip' => $ship['zip']
    );
    return $taxLocation;
  }
  
  /**
   * Return the last $lenght digits of the credit card number or false 
   * if the number is not set or is shorter than the requested length.
   * 
   * @param int (optional) The number of digits to return
   * @return int | false
   */
  public function getCardNumberTail($length=4) {
    $tail = false;
    if(isset($this->_payment['cardNumber']) && strlen($this->_payment['cardNumber']) >= $length) {
      $tail = substr($this->_payment['cardNumber'], -1 * $length);
    }
    return $tail;
  }

  /**
   * Return true if the order should be taxed
   *
   * @return boolean
   */
  public function isTaxed($isShippingTaxed=null) {
     $s = $this->getShipping();
     if(count($s)) {
       $taxRate = new Cart66TaxRate();
       $isTaxed = $taxRate->loadByZip($s['zip']);
       if($isTaxed == false) {
         $isTaxed = $taxRate->loadByState($s['state']);
       }

       $this->_taxRate = $taxRate;
       $taxShipping = $taxRate->tax_shipping;
     
       return ($isShippingTaxed==null) ? $isTaxed : $taxShipping;
     }
     else {
       throw new Exception(__('Unable to determine tax rate because shipping data is unavailable','cart66'));
     }
  }

  public function taxShipping() {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Checking for taxed shipping");
    if(!isset($this->_taxRate)) {
      $this->isTaxed();
    }
    $taxShipping = ($this->isTaxed('shipping') == 1) ? true : false;
    return $taxShipping;
  }
  
  public function getTaxRate() {
    return $this->_taxRate->rate;
  }

  public function getTaxAmount() {
    $tax = 0;
    if($this->isTaxed()) {
      $taxable = Cart66Session::get('Cart66Cart')->getTaxableAmount();
      if($this->taxShipping()) {
        $taxable += Cart66Session::get('Cart66Cart')->getShippingCost();
      }
      $tax = number_format($taxable * ($this->_taxRate->rate/100), 2, '.', '');
    }
    return $tax;
  }

  /**
   * Store order in database after successful transaction is processed
   */
  public function saveOrder($total, $tax, $transId, $status, $accountId=0) {
    $address = $this->getShipping();
    $b = $this->getBilling();
    $p = $this->getPayment();

    $orderInfo['ship_first_name'] = $address['firstName'];
    $orderInfo['ship_last_name'] = $address['lastName'];
    $orderInfo['ship_address'] = $address['address'];
    $orderInfo['ship_address2'] = $address['address2'];
    $orderInfo['ship_city'] = $address['city'];
    $orderInfo['ship_state'] = $address['state'];
    $orderInfo['ship_zip'] = $address['zip'];
    $orderInfo['ship_country'] = Cart66Common::getCountryName($address['country']);

    $orderInfo['bill_first_name'] = $b['firstName'];
    $orderInfo['bill_last_name'] = $b['lastName'];
    $orderInfo['bill_address'] = $b['address'];
    $orderInfo['bill_address2'] = $b['address2'];
    $orderInfo['bill_city'] = $b['city'];
    $orderInfo['bill_state'] = $b['state'];
    $orderInfo['bill_zip'] = $b['zip'];
    $orderInfo['bill_country'] = Cart66Common::getCountryName($b['country']);

    $orderInfo['phone'] = preg_replace("/[^0-9]/", "", $p['phone']);
    $orderInfo['email'] = $p['email'];
    $orderInfo['custom_field'] = $p['custom-field'];
    $orderInfo['coupon'] = Cart66Common::getPromoMessage();
    $orderInfo['tax'] = $tax;
    $orderInfo['shipping'] = Cart66Session::get('Cart66Cart')->getShippingCost();
    $orderInfo['subtotal'] = Cart66Session::get('Cart66Cart')->getSubTotal();
    $orderInfo['total'] = preg_replace("/[^0-9\.]/", "", $total);
    $orderInfo['trans_id'] = $transId;
    $orderInfo['status'] = $status;
    $orderInfo['ordered_on'] = date('Y-m-d H:i:s', Cart66Common::localTs());
    $orderInfo['shipping_method'] = Cart66Session::get('Cart66Cart')->getShippingMethodName();
    $orderInfo['account_id'] = $accountId;
    
    $additional_fields = array();
    $custom_payment_fields = apply_filters('cart66_after_payment_form', '');
    if(is_array($custom_payment_fields)) {
      foreach($custom_payment_fields as $key => $payment_field) {
        if(isset($p[$payment_field['slug']])) {
          $additional_fields[$payment_field['section']][$payment_field['slug']] = array('label' => $payment_field['label'], 'value' => $p[$payment_field['slug']]);
        }
      }
    }
    $custom_billing_fields = apply_filters('cart66_after_billing_form', '');
    if(is_array($custom_billing_fields)) {
      foreach($custom_billing_fields as $key => $billing_field) {
        if(isset($b[$billing_field['slug']])) {
          $additional_fields[$billing_field['section']][$billing_field['slug']] = array('label' => $billing_field['label'], 'value' => $b[$billing_field['slug']]);
        }
      }
    }
    $custom_shipping_fields = apply_filters('cart66_after_shipping_form', '');
    if(is_array($custom_shipping_fields)) {
      foreach($custom_shipping_fields as $key => $shipping_field) {
        if(isset($address[$shipping_field['slug']])) {
          $additional_fields[$shipping_field['section']][$shipping_field['slug']] = array('label' => $shipping_field['label'], 'value' => $address[$shipping_field['slug']]);
        }
      }
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] additional fields: " . print_r($additional_fields, true));
    if(!empty($additional_fields)) {
      $orderInfo['additional_fields'] = serialize($additional_fields);
    }
    
    $orderId = Cart66Session::get('Cart66Cart')->storeOrder($orderInfo);
    return $orderId;
  }
  
  /**
   * Make sure there is at least one product in the cart.
   * Return true if the cart is valid, otherwise false.
   * 
   * @return boolean
   */
  public function validateCartForCheckout() {
    $isValid = true;
    $itemCount = Cart66Session::get('Cart66Cart')->countItems();
    if($itemCount < 1) {
      $this->_errors['Invalid Cart'] = __('There must be at least one item in the cart.','cart66');
      $isValid = false;
    }
    return $isValid;
  }
  
  
}
