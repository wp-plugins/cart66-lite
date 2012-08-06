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
    $msg = Cart66Common::getView('pro/views/emails/default-email-receipt.php', array($order, $html, $test));
    return $msg;
  }
  
}