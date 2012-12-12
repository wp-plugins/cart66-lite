<?php
class Cart66ManualGateway extends Cart66GatewayAbstract {

  /**
   * @var decimal
   * The total price to charge the customer. Shipping, tax, etc. all included.
   */
  protected $_total;
  
  public function setPayment($p) {
    $this->_payment = $p;
    if($p['email'] == '') {
      $this->_errors['Email address'] = __('Email address is required','cart66');
      $this->_jqErrors[] = "payment-email";
    }

    if($p['phone'] == '') {
      $this->_errors['Phone'] = __('Phone number is required','cart66');
      $this->_jqErrors[] = "payment-phone";
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
    
  }
  
   public function getCreditCardTypes() {
     $noCards = array();
     return $noCards;
   }
   
   public function initCheckout($total) {
     $this->_total = $total;
   }
   
   public function getTransactionResponseDescription() {
     return 'Manual transaction processed: ' . $this->_total;
   }
   
   public function doSale() {
     $transId = 'MT-' . Cart66Common::getRandString();
     return $transId;
   }

}
