<?php
  if(!isset($s)) {
    $s = array(
      'firstName' => '',
      'lastName' => '',
      'address' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => '',
      'phone' => ''
    );
  }
  
  if(!isset($s['phone'])) {
    $s['phone'] = '';
  }
  
  if(!isset($shippingCountryCode)) {
    $shippingCountryCode = 'US';
  }
  
  if(empty($b['country'])){
     $b['country'] = Cart66Common::getHomeCountryCode();
  }
  
  $cart = Cart66Session::get('Cart66Cart');
  if($cart->requireShipping() || $cart->hasTaxableProducts()): ?>

    <form action="" method='post' id="mijireh_shipping_form" class="phorm2
      <?php 
        // Apply CSS classes for mailing lists
        if($lists = Cart66Setting::getValue('constantcontact_list_ids')) {
          echo ' constantcontact';
        }
        elseif($lists = Cart66Setting::getValue('mailchimp_list_ids')) {
          echo ' mailchimp';
        }
    
        // Apply CSS class for subscription products
        if(Cart66Session::get('Cart66Cart')->hasSubscriptionProducts() || Cart66Session::get('Cart66Cart')->hasMembershipProducts()) { 
          echo ' subscription'; 
        }
      ?>">
      <input type="hidden" class="ajax-tax-cart" name="ajax-tax-cart" value="<?php echo Cart66Session::get('Cart66Cart')->hasTaxableProducts() ? 'true' : 'false'; ?>" />
      <input type="hidden" name="cart66-gateway-name" value="<?php echo $gatewayName ?>" id="cart66-gateway-name" />
      <?php
        $url = Cart66Common::appendWurlQueryString('cart66AjaxCartRequests');
        if(Cart66Common::isHttps()) {
          $url = preg_replace('/http[s]*:/', 'https:', $url);
        }
        else {
          $url = preg_replace('/http[s]*:/', 'http:', $url);
        }
      ?>
      <input type="hidden" name="confirm_url" value="<?php echo $url; ?>" id="confirm-url" />
      <?php if($cart->requireShipping()): ?>
        <h2><?php _e( 'Shipping Address' , 'cart66' ); ?></h2>
      <?php else: ?>
        <h2><?php _e( 'Your Address' , 'cart66' ); ?></h2>
      <?php endif; ?>
      
      <?php if(CART66_PRO && Cart66Setting::getValue('checkout_custom_field_display') && Cart66Setting::getValue('checkout_custom_field_display') != 'disabled'): ?>
        <div class="checkout-custom-field">
          <?php if(Cart66Setting::getValue('checkout_custom_field_label')): ?>
            <p><?php echo Cart66Setting::getValue('checkout_custom_field_label'); ?></p>
          <?php else: ?>
            <p><?php _e('Enter any special instructions you have for this order:', 'cart66'); ?></p>
          <?php endif; ?>
          <?php if(Cart66Setting::getValue('checkout_custom_field') == 'multi' || !Cart66Setting::getValue('checkout_custom_field')): ?>
            <textarea id="checkout-custom-field-multi" name="payment[custom-field]"><?php Cart66Common::showValue($p['custom-field']); ?></textarea>
          <?php elseif(Cart66Setting::getValue('checkout_custom_field') == 'single'): ?>
            <input type="text" id="checkout-custom-field-single" name="payment[custom-field]" value="<?php Cart66Common::showValue($p['custom-field']); ?>" />
          <?php endif; ?>
        </div>
      <?php endif; ?>

      
      <ul id="mijireh_shippingAddress" class="shippingAddress shortLabels" style="float:left;">
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
      </ul>
  
      <ul class="shippingAddress shortLabels" style='float: left;'>
        <li>
          <label for="shipping-city"><?php _e( 'City' , 'cart66' ); ?>:</label>
          <input type="text" id="shipping-city" name="shipping[city]" value="<?php Cart66Common::showValue($s['city']); ?>">
        </li>

        <li>
          <label for="shipping-state_text" class="short shipping-state_label"><?php _e( 'State' , 'cart66' ); ?>:</label>
          <input type="text" name="shipping[state_text]" value="<?php Cart66Common::showValue($s['state']); ?>" id="shipping-state_text" class="ajax-tax state_text_field" />
          <select id="shipping-state" class="ajax-tax shipping_countries required" title="State shipping address" name="shipping[state]">
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
          <label for="shipping-zip" class="shipping-zip_label"><?php _e( 'Zip code' , 'cart66' ); ?>:</label>
          <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php Cart66Common::showValue($s['zip']); ?>" class="ajax-tax">
        </li>

        <li>
          <label for="shipping-country" class="short"><?php _e( 'Country' , 'cart66' ); ?>:</label>
          <select title="country" id="shipping-country" name="shipping[country]">
            <?php foreach(Cart66Common::getShippingCountries() as $code => $country_name): ?>
              <?php
              $disabled = false;
              if(is_array($country_name)) {
                $disabled = isset($country_name['disabled']) ? $country_name['disabled'] : 'true';
                $country_name = $country_name['country'];
              }
              if($disabled == 'true') {
                $disabled = 'disabled';
              }
              ?>
              <option value="<?php echo $code ?>" <?php if($code == $shippingCountryCode  && !$disabled) { echo 'selected="selected"'; } ?> <?php echo $disabled; ?>><?php echo $country_name ?></option>
            <?php endforeach; ?>
          </select>
        </li>
        <?php if(Cart66Session::get('Cart66ShippingCountryCode') && Cart66Setting::getValue('international_sales')): ?>
          <li class="limited-countries-label-shipping summary-message cart66-align-center">
            <p><?php _e('Available countries may be limited based', 'cart66'); ?><br /><?php _e('on your selected shipping method', 'cart66'); ?></p>
          </li>
        <?php endif; ?>
        <li>
          <label for="payment-phone"><?php _e( 'Phone' , 'cart66' ); ?>:</label>
          <input type="text" id="payment-phone" name="payment[phone]" value="<?php Cart66Common::showValue($p['phone']); ?>">
        </li>
        
        <li>
          <label for="Cart66CheckoutButton" class="short">&nbsp;</label>
          <?php
          $cartImgPath = Cart66Setting::getValue('cart_images_url');
          if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
            $cartImgPath .= '/';
          }
          if($cartImgPath) {
            $continueImg = $cartImgPath . 'continue.png';
          }
          ?>
          <?php if($cartImgPath && Cart66Common::urlIsLIve($continueImg)): ?>
            <input class="Cart66CheckoutButton" type="image" src='<?php echo $continueImg ?>' value="<?php _e( 'Continue' , 'cart66' ); ?>" name="Complete Order"/>
          <?php else: ?>
            <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary Cart66CompleteOrderButton Cart66ContinueButton" type="submit"  value="<?php _e( 'Continue' , 'cart66' ); ?>" name="Complete Order"/>
          <?php endif; ?>
        </li>
    
      </ul>
  
    </form>
<?php else: ?>
  <?php
    // TODO: Handle account generation for membership stuff
    $total = Cart66Session::get('Cart66Cart')->getGrandTotal();
    $gateway = new Cart66Mijireh();
    $gateway->initCheckout($total);
  ?>
<?php endif; ?>