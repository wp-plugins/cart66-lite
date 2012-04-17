<?php
$service_name = null;

// Look for newsletter opt-in info

if($lists = Cart66Setting::getValue('constantcontact_list_ids')) {
  $service_name = 'constantcontact';
  if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66ConstantContactOptIn.php"); }
}
elseif($lists = Cart66Setting::getValue('mailchimp_list_ids')) {
  $service_name = 'mailchimp';
  if(CART66_PRO) { include(CART66_PATH . "/pro/Cart66MailChimpOptIn.php"); }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  $url = $_SERVER['REQUEST_URI'];
  $count = 0;
  $url = str_replace('newsletter=1', 'newsletter=2', $url, $count);
  if($count == 1) {
    wp_redirect($url);
    exit;
  }
}

if(isset($_REQUEST['newsletter'])):
  if($_REQUEST['newsletter'] == 1): 
?>
  <form action="" method='post' id="newsletter_form" class="phorm2">
    <?php if($lists): ?>
      <ul id="<?php echo $service_name; ?>">
        <li>
          <?php
            if(!$optInMessage = Cart66Setting::getValue($service_name . '_opt_in_message')) {
              $optInMessage = 'Yes, I would like to subscribe to:';
            }
            echo "<p>$optInMessage</p>";
            $lists = explode('~', $lists);
            echo '<ul class="Cart66NewsletterList">';
            foreach($lists as $list) {
              list($id, $name) = explode('::', $list);
              echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"" . $service_name . "_subscribe_ids[]\" value=\"$id\" /> $name</li>";
            }
            echo '</ul>';
          ?>
        </li>
      </ul>
    <?php endif; ?>
  
    <ul>
      <li>
        <label for="<?php echo $service_name; ?>_first_name"><?php _e( 'First name:' , 'cart66' ); ?></label>
        <input type="text" id="<?php echo $service_name; ?>_first_name" name="<?php echo $service_name; ?>_first_name" value="<?php echo $order->bill_first_name; ?>">
      </li>
      <li>
        <label for="<?php echo $service_name; ?>_last_name"><?php _e( 'Last name:' , 'cart66' ); ?></label>
        <input type="text" id="<?php echo $service_name; ?>_last_name" name="<?php echo $service_name; ?>_last_name" value="<?php echo $order->bill_last_name; ?>">
      </li>
      <li>
        <label for="<?php echo $service_name; ?>_email"><?php _e( 'Email:' , 'cart66' ); ?></label>
        <input type="text" id="<?php echo $service_name; ?>_email" name="<?php echo $service_name; ?>_email" value="<?php echo $order->email; ?>">
      </li>
      <li>
        <label for="<?php echo $service_name; ?>_submit">&nbsp;</label>
        <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary Cart66NewsletterButton" type="submit"  
          value="<?php _e( 'Sign Up' , 'cart66' ); ?>" name="Sign Up"/>
      </li>
    </ul>
  </form>
  <?php elseif($_REQUEST['newsletter'] == 2): ?>
    <p><?php _e('Thank you for subscribing!', 'cart66'); ?>
  <?php endif; ?>
<?php endif; ?>