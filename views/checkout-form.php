<?php
$account = false;
if(CART66_PRO) {
  $account = new Cart66Account();
  if($accountId = Cart66Common::isLoggedIn()) {
    $account = new Cart66Account($accountId);
    $name = $account->firstName . '&nbsp;' . $account->lastName;
    echo "<h3 class=\"loggedInAs\">You Are Logged In As $name</h3>";
    $logout = Cart66Common::appendQueryString('cart66-task=logout');
    echo "<p class=\"loggedInWrongMsg\">If you are not $name <a href='$logout'>Log out</a></p>";

    if(empty($b['firstName'])) {
      $b['firstName'] = $account->billingFirstName;
      $b['lastName'] = $account->billingLastName;
    }

    if(empty($p['email'])) {
      $p['email'] = $account->email;
    }
  }
}


// Show errors
if(count($errors)) {
  echo Cart66Common::showErrors($errors);
}
?>

<form action="" method='post' id="<?php echo $gatewayName ?>_form" class="phorm2<?php if(Cart66Session::get('Cart66Cart')->requireShipping() && $gatewayName != 'Cart66ManualGateway'): echo ' shipping'; endif; ?><?php if($lists = Cart66Setting::getValue('constantcontact_list_ids')): echo ' constantcontact'; endif; ?><?php if($lists = Cart66Setting::getValue('mailchimp_list_ids')): echo ' mailchimp'; endif; ?><?php if(Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() || Cart66Session::get('Cart66Cart')->hasMembershipProducts()): echo ' subscription'; endif; ?>">
  <input type="hidden" name="cart66-gateway-name" value="<?php echo $gatewayName ?>">
<div id="ccInfo">
	<div id="billingInfo">
        <ul id="billingAddress" class="shortLabels" >
          <?php if($gatewayName == 'Cart66ManualGateway'): ?>
            <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>
              <li><h2><?php _e( 'Shipping Address' , 'cart66' ); ?></h2></li>
            <?php else: ?>
              <li><h2><?php _e( 'Order Information' , 'cart66' ); ?></h2></li>
            <?php endif; ?>
          <?php else: ?>
            <li><h2><?php _e( 'Billing Address' , 'cart66' ); ?></h2></li>
          <?php endif; ?>

          <li>
            <label for="billing-firstName"><?php _e( 'First name' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-firstName" name="billing[firstName]" value="<?php Cart66Common::showValue($b['firstName']); ?>">
          </li>

          <li>
            <label for="billing-lastName"><?php _e( 'Last name' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-lastName" name="billing[lastName]" value="<?php Cart66Common::showValue($b['lastName']); ?>">
          </li>

          <li>
            <label for="billing-address"><?php _e( 'Address' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-address" name="billing[address]" value="<?php Cart66Common::showValue($b['address']); ?>">
          </li>

          <li>
            <label for="billing-address2" id="billing-address2-label" class="hidden"><?php _e( 'Address 2' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-address2" name="billing[address2]" value="<?php Cart66Common::showValue($b['address2']); ?>">
          </li>

          <li>
            <label for="billing-city"><?php _e( 'City' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-city" name="billing[city]" value="<?php Cart66Common::showValue($b['city']); ?>">
          </li>

          <li><label for="billing-state_text" class="short"><?php _e( 'State' , 'cart66' ); ?>:</label>
            <input type="text" name="billing[state_text]" value="<?php Cart66Common::showValue($b['state']); ?>" id="billing-state_text" class="state_text_field" />
            <select id="billing-state" class="required" title="State billing address" name="billing[state]">
              <option value="0">&nbsp;</option>
              <?php
                Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Country code on checkout form: $billingCountryCode");
                $zone = Cart66Common::getZones($billingCountryCode);
                foreach($zone as $code => $name) {
                  $selected = ($b['state'] == $code) ? 'selected="selected"' : '';
                  echo '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
                }
              ?>
            </select>
          </li>

          <li>
            <label for="billing-zip"><?php _e( 'Zip code' , 'cart66' ); ?>:</label>
            <input type="text" id="billing-zip" name="billing[zip]" value="<?php Cart66Common::showValue($b['zip']); ?>">
          </li>

          <li>
            <label for="billing-country" class="short"><?php _e( 'Country' , 'cart66' ); ?>:</label>
            <select title="country" id="billing-country" name="billing[country]" class="billing_countries">
              <?php foreach(Cart66Common::getCountries() as $code => $name): ?>
                <option value="<?php echo $code ?>" <?php if($code == $billingCountryCode) { echo 'selected="selected"'; } ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
	</div><!-- #billingInfo -->
   
    <?php if(Cart66Session::get('Cart66Cart')->requireShipping() && $gatewayName != 'Cart66ManualGateway'): ?>
	<div id="shippingInfo">
        <ul id="shippingAddressCheckbox">
          <li><h2><?php _e( 'Shipping Address' , 'cart66' ); ?></h2></li>
    
          <li>
            <label for="sameAsBilling"><?php _e( 'Same as billing address' , 'cart66' ); ?>:</label>
            <input type='checkbox' class='sameAsBilling' id='sameAsBilling' name='sameAsBilling' value='1'>
          </li>
        </ul>

        <ul id="shippingAddress" class="shippingAddress shortLabels">

          <li>
            <label for="shipping-firstName"><?php _e( 'First name' , 'cart66' ); ?>:</label>
            <input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php Cart66Common::showValue($s['firstName']); ?>">
          </li>

          <li>
            <label for="shipping-lastName"><?php _e( 'Last name' , 'cart66' ); ?>:</label>
            <input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php Cart66Common::showValue($s['lastName']); ?>">
          </li>

          <li>
            <label for="shipping-address"><?php _e( 'Address' , 'cart66' ); ?>:</label>
            <input type="text" id="shipping-address" name="shipping[address]" value="<?php Cart66Common::showValue($s['address']); ?>">
          </li>

          <li>
            <label for="shipping-address2">&nbsp;</label>
            <input type="text" id="shipping-address2" name="shipping[address2]" value="<?php Cart66Common::showValue($s['address2']); ?>">
          </li>

          <li>
            <label for="shipping-city"><?php _e( 'City' , 'cart66' ); ?>:</label>
            <input type="text" id="shipping-city" name="shipping[city]" value="<?php Cart66Common::showValue($s['city']); ?>">
          </li>

          <li>
            <label for="shipping-state_text" class="short"><?php _e( 'State' , 'cart66' ); ?>:</label>
            <input type="text" name="shipping[state_text]" value="<?php Cart66Common::showValue($s['state']); ?>" id="shipping-state_text" class="state_text_field" />
            <select id="shipping-state" class="shipping_countries required" title="State shipping address" name="shipping[state]">
              <option value="0">&nbsp;</option>
              <option value="0">&nbsp;</option>
              <?php
                $zone = Cart66Common::getZones($shippingCountryCode);
                foreach($zone as $code => $name) {
                  $selected = ($s['state'] == $code) ? 'selected="selected"' : '';
                  echo '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
                }
              ?>
            </select>
          </li>

          <li>
            <label for="shipping-zip"><?php _e( 'Zip code' , 'cart66' ); ?>:</label>
            <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php Cart66Common::showValue($s['zip']); ?>">
          </li>

          <li>
            <label for="shipping-country" class="short"><?php _e( 'Country' , 'cart66' ); ?>:</label>
            <select title="country" id="shipping-country" name="shipping[country]">
              <?php foreach(Cart66Common::getCountries() as $code => $name): ?>
                <option value="<?php echo $code ?>" <?php if($code == $shippingCountryCode) { echo 'selected="selected"'; } ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
     </div> <!--shippingInfo-->
	
        <?php else: ?>
          <input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />
        <?php endif; ?>
<div id="paymentInfo">
        <ul id="contactPaymentInfo" class="shortLabels">
          <?php if($gatewayName == 'Cart66ManualGateway'): ?>
            <li><h2><?php _e( 'Contact Information' , 'cart66' ); ?></h2></li>
          <?php else: ?>
            <li><h2><?php _e( 'Payment Information' , 'cart66' ); ?></h2></li>
          <?php endif; ?>
        
          <?php if($gatewayName != 'Cart66ManualGateway'): ?>
          <li>
            <label for="payment-cardType">Card Type:</label>
            <select id="payment-cardType" name="payment[cardType]">
              <?php foreach($data['gateway']->getCreditCardTypes() as $name => $value): ?>
                <option value="<?php echo $value ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        
          <li>
            <label for="payment-cardNumber"><?php _e( 'Card Number' , 'cart66' ); ?>:</label>
            <input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php Cart66Common::showValue($p['cardNumber']); ?>">
          </li>
        
          <li>
            <label for="payment-cardExpirationMonth"><?php _e( 'Expiration' , 'cart66' ); ?>:</label>
            <select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
              <option value=''></option>
              <?php 
                for($i=1; $i<=12; $i++){
                  $val = $i;
                  if(strlen($val) == 1) {
                    $val = '0' . $i;
                  }
                  $selected = '';
                  if(isset($p['cardExpirationMonth']) && $val == $p['cardExpirationMonth']) {
                    $selected = 'selected="selected"';
                  }
                  echo "<option value='$val' $selected>$val</option>\n";
                } 
              ?>
            </select> / <select id="payment-cardExpirationYear" name="payment[cardExpirationYear]">
              <option value=''></option>
              <?php
                $year = date('Y', Cart66Common::localTs());
                for($i=$year; $i<=$year+12; $i++) {
                  $selected = '';
                  if(isset($p['cardExpirationYear']) && $i == $p['cardExpirationYear']) {
                    $selected = 'selected="selected"';
                  }
                  echo "<option value='$i' $selected>$i</option>\n";
                } 
              ?>
            </select>
          
          </li>
          
          <li>
            <label for="payment-securityId"><?php _e( 'Security ID' , 'cart66' ); ?>:</label>
            <input type="text" id="payment-securityId" name="payment[securityId]" value="<?php Cart66Common::showValue($p['securityId']); ?>">
            <p class="description"><?php _e( 'Security code on back of card' , 'cart66' ); ?></p>
          </li>

          <?php endif; ?>
          <li>
            <label for="payment-phone"><?php _e( 'Phone' , 'cart66' ); ?>:</label>
            <input type="text" id="payment-phone" name="payment[phone]" value="<?php Cart66Common::showValue($p['phone']); ?>">
          </li>
          
          <li>
            <label for="payment-email"><?php _e( 'Email' , 'cart66' ); ?>:</label>
            <input type="text" id="payment-email" name="payment[email]" value="<?php Cart66Common::showValue($p['email']); ?>">
          </li>
          </ul>

	</div><!-- #paymentInfo -->
</div><!-- #ccInfo -->
         <?php if($lists = Cart66Setting::getValue('constantcontact_list_ids')): ?>
	<ul id="constantContact">
            <li>
              <?php
                if(!$optInMessage = Cart66Setting::getValue('constantcontact_opt_in_message')) {
                  $optInMessage = 'Yes, I would like to subscribe to:';
                }
                echo "<p>$optInMessage</p>";
                $lists = explode('~', $lists);
                echo '<ul class="Cart66NewsletterList">';
                foreach($lists as $list) {
                  list($id, $name) = explode('::', $list);
                  echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"constantcontact_subscribe_ids[]\" value=\"$id\" /> $name</li>";
                }
                echo '</ul>';
              ?>
            </li>
          <?php endif; ?>
          
          <?php if($lists = Cart66Setting::getValue('mailchimp_list_ids')): ?>
            <li>
              <?php
                if(!$optInMessage = Cart66Setting::getValue('mailchimp_opt_in_message')) {
                  $optInMessage = 'Yes, I would like to subscribe to:';
                }
                echo "<p>$optInMessage</p>";
                $lists = explode('~', $lists);
                echo '<ul class="Cart66NewsletterList MailChimpList">';
                foreach($lists as $list) {
                  list($id, $name) = explode('::', $list);
                  echo "<li><input class=\"Cart66CheckboxList\" type=\"checkbox\" name=\"mailchimp_subscribe_ids[]\" value=\"$id\" /> $name</li>";
                }
                echo '</ul>';
              
              
              
                if(isset($_POST['mailchimp_subscribe_ids']) && !empty($_POST['mailchimp_subscribe_ids'])){
                    ?>
                    <script type="text/javascript" charset="utf-8">
                      jQuery(document).ready(function($){
                        <?php
                        foreach($_POST['mailchimp_subscribe_ids'] as $id) {
                          ?>
                          $(".MailChimpList input[value=<?php echo $id; ?>]").attr('checked','true');
                        <?php 
                        }

                        ?>
                      })
                    </script>
              <?php
                }
              ?>
            </li>
		</ul>
          <?php endif; ?>

			
			<?php if(!Cart66Common::isLoggedIn()): ?>
	          <?php if(Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() || Cart66Session::get('Cart66Cart')->hasMembershipProducts()): ?>
	            <?php echo Cart66Common::getView('pro/views/account-form.php', array('account' => $account, 'embed' => false)); ?>
	          <?php endif; ?>
	        <?php endif; ?>
			
          <div id="Cart66CheckoutButtonDiv">
            <label for="Cart66CheckoutButton" class="hidden"><?php _e( 'Checkout' , 'cart66' ); ?></label>
            <?php
              $cartImgPath = Cart66Setting::getValue('cart_images_url');
              if($cartImgPath) {
                if(strpos(strrev($cartImgPath), '/') !== 0) {
                  $cartImgPath .= '/';
                }
                $completeImgPath = $cartImgPath . 'complete-order.png';
              }
            ?>
            <?php if($cartImgPath): ?>
              <input id="Cart66CheckoutButton" type="image" src='<?php echo $completeImgPath ?>' value="Complete Order" name="Complete Order"/>
            <?php else: ?>
              <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary" type="submit"  value="Complete Order" name="Complete Order"/>
            <?php endif; ?>

            <p class="description"><?php _e( 'Your receipt will be on the next page and also immediately emailed to you. <strong>We respect your privacy!</strong>' , 'cart66' ); ?></p>
          </div>
</form>