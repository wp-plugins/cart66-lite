<?php
class Cart66Notifications {
  
  protected $_order;
  
  public function __construct($id=null) {
    $this->_order = new Cart66Order($id);
  }
  
  /**
   * Configure mail for use with either standard wp_mail or when using the WP Mail SMTP plugin
   */
  public static function mail($to, $subject, $msg, $headers=null) {
    //Disable mail headers if the WP Mail SMTP plugin is in use.
    //if(function_exists('wp_mail_smtp_activate')) { $headers = null; }
    return wp_mail($to, $subject, $msg, $headers);
  }
  
  /**
   * Send email receipt and copies thereof.
   * Return true if all the emails that were supposed to be sent got sent.
   * Note that just because the email was sent does not mean the recipient received it.
   * All sorts of things can go awry after the email leaves the server before it is in the
   * recipient's inbox. 
   * 
   * @param int $orderId
   * @return bool
   */
  public function sendEmailReceipts() {
    $isSent = false;
    $msg = $this->getEmailReceiptMessage($this->_order);
    $to = $this->_order->email;
    $subject = Cart66Setting::getValue('receipt_subject');
    
    $headers = 'From: '. Cart66Setting::getValue('receipt_from_name') .' <' . Cart66Setting::getValue('receipt_from_address') . '>' . "\r\n\\";
    $msgIntro = Cart66Setting::getValue('receipt_intro');
    
    if($this->_order) {
      $isSent = $this->mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        Cart66Common::log("Mail not sent to: $to");
      }

      $others = Cart66Setting::getValue('receipt_copy');
      if($others) {
        $list = explode(',', $others);
        $msg = "THIS IS A COPY OF THE RECEIPT\n\n$msg";
        foreach($list as $e) {
          $e = trim($e);
          $isSent = $this->mail($e, $subject, $msg, $headers);
          if(!$isSent) {
            Cart66Common::log("Mail not sent to: $e");
          }
          else {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Receipt also mailed to: $e");
          }
        }
      } 
    }
    return $isSent;
  }
  
  public function getEmailReceiptMessage($order, $html=null, $test=null) {
    if(CART66_PRO) {
      $msg = Cart66Common::getView('pro/views/emails/default-email-receipt.php', array($order, $html, $test));
    }
    else {
      $msg = $this->defaultPlainEmailMessage($order);
    }
    return $msg;
  }
  
  public function defaultPlainEmailMessage($order) {
    $msg = __("ORDER NUMBER","cart66") . ": " . $order->trans_id . "\n\n";

    $hasDigital = false;
    $product = new Cart66Product();
    foreach($order->getItems() as $item) {
      $product->load($item->product_id);
      if($hasDigital == false) {
        $hasDigital = $product->isDigital();
      }
      $price = $item->product_price * $item->quantity;
      // $msg .= "Item: " . $item->item_number . ' ' . $item->description . "\n";
      $msg .= __("Item","cart66") . ": " . $item->description . "\n";
      if($hasDigital) {
        $receiptPage = get_page_by_path('store/receipt');
        $receiptPageLink = get_permalink($receiptPage);
        $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
        $msg .= "\n" . $receiptPageLink . "\n";
      }
      if($item->quantity > 1) {
        $msg .= __("Quantity","cart66") . ": " . $item->quantity . "\n";
      }
      $msg .= __("Item Price","cart66") . ": " . Cart66Common::currency($item->product_price, false) . "\n";
      $msg .= __("Item Total","cart66") . ": " . Cart66Common::currency($item->product_price * $item->quantity, false) . "\n\n";

      if($product->isGravityProduct()) {
        $msg .= Cart66GravityReader::displayGravityForm($item->form_entry_ids, true);
      }
    }

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= __("Shipping","cart66") . ": " . Cart66Common::currency($order->shipping, false) . "\n";
    }

    if(!empty($order->coupon) && $order->coupon != 'none') {
      $msg .= __("Coupon","cart66") . ": " . $order->coupon . "\n";
    }

    if($order->tax > 0) {
      $msg .= __("Tax","cart66") . ": " . Cart66Common::currency($order->tax, false) . "\n";
    }

    $msg .= "\n" . __("TOTAL","cart66") . ": " . Cart66Common::currency($order->total, false) . "\n";

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "\n\n" . __("SHIPPING INFORMATION","cart66") . "\n\n";

      $msg .= $order->ship_first_name . ' ' . $order->ship_last_name . "\n";
      $msg .= $order->ship_address . "\n";
      if(!empty($order->ship_address2)) {
        $msg .= $order->ship_address2 . "\n";
      }
      $msg .= $order->ship_city . ' ' . $order->ship_state . ' ' . $order->ship_zip . "\n" . $order->ship_country . "\n";

      $msg .= "\n" . __("Delivery via","cart66") . ": " . $order->shipping_method . "\n";
    }


    $msg .= "\n\n" . __("BILLING INFORMATION","cart66") . "\n\n";

    $msg .= $order->bill_first_name . ' ' . $order->bill_last_name . "\n";
    $msg .= $order->bill_address . "\n";
    if(!empty($order->bill_address2)) {
      $msg .= $order->bill_address2 . "\n";
    }
    $msg .= $order->bill_city . ' ' . $order->bill_state;
    $msg .= $order->bill_zip != null ? ', ' : ' ';
    $msg .= $order->bill_zip . "\n" . $order->bill_country . "\n";

    if(!empty($order->phone)) {
      $phone = Cart66Common::formatPhone($order->phone);
      $msg .= "\n" . __("Phone","cart66") . ": $phone\n";
    }

    if(!empty($order->email)) {
      $msg .= __("Email","cart66") . ': ' . $order->email . "\n";
    }

    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    if(strstr($link,"?")){
      $link .= '&ouid=' . $order->ouid;
    }
    else{
      $link .= '?ouid=' . $order->ouid;
    }

    if($hasDigital) {
      $msg .= "\n" . __('DOWNLOAD LINK','cart66') . "\n" . __('Click the link below to download your order.','cart66') . "\n$link";
    }
    else {
      $msg .= "\n" . __('VIEW RECEIPT ONLINE','cart66') . "\n" . __('Click the link below to view your receipt online.','cart66') . "\n$link";
    }

    $msgIntro = Cart66Setting::getValue('receipt_intro') && !Cart66Setting::getValue('enable_advanced_notifications') ? Cart66Setting::getValue('receipt_intro') : '';
    $msgIntro .= Cart66Setting::getValue('receipt_message_intro') && Cart66Setting::getValue('enable_advanced_notifications') ? Cart66Setting::getValue('receipt_plain_email') : '';

    $msg = $msgIntro . " \n----------------------------------\n\n" . $msg;
    return $msg;
  }
  
}