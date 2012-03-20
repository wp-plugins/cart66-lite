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
    
      <input type="hidden" name="cart66-gateway-name" value="<?php echo $gatewayName ?>"/>
    
      <?php if($cart->requireShipping()): ?>
        <h2><?php _e( 'Shipping Address' , 'cart66' ); ?></h2>
      <?php else: ?>
        <h2><?php _e( 'Your Address' , 'cart66' ); ?></h2>
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
          <input type="text" name="shipping[state_text]" value="<?php Cart66Common::showValue($s['state']); ?>" id="shipping-state_text" class="state_text_field" />
          <select id="shipping-state" class="shipping_countries required" title="State shipping address" name="shipping[state]">
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
      
        <li>
          <label for="payment-phone"><?php _e( 'Phone' , 'cart66' ); ?>:</label>
          <input type="text" id="payment-phone" name="payment[phone]" value="<?php Cart66Common::showValue($p['phone']); ?>">
        </li>
        
        <li>
          <label for="Cart66CheckoutButton" class="short">&nbsp;</label>
          <input id="Cart66CheckoutButton" class="Cart66ButtonPrimary Cart66CompleteOrderButton" 
            type="submit"  value="<?php _e( 'Continue' , 'cart66' ); ?>" name="Complete Order"/>
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