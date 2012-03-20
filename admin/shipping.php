<?php
$rule = new Cart66ShippingRule();
$method = new Cart66ShippingMethod();
$rate = new Cart66ShippingRate();
$product = new Cart66Product();
$tab = 1;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['cart66-action'] == 'save rule') {
    $rule->setData($_POST['rule']);
    $rule->save();
    $rule->clear();
  }
  elseif($_POST['cart66-action'] == 'save shipping method') {
    $method->setData($_POST['shipping_method']);
    $method->save();
    $method->clear();
  }
  elseif($_POST['cart66-action'] == 'save product rate') {
    $rate->setData($_POST['rate']);
    $rate->save();
    $rate->clear();
  }
  elseif($_POST['cart66-action'] == 'save local pickup info') {
    foreach($_POST['local'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $tab = 6;
  }
  elseif($_POST['cart66-action'] == 'save ups account info') {
    foreach($_POST['ups'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['ups_methods'])) ? $_POST['ups_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'ups';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('ups', $codes);
    $tab = 2;
  }
  elseif($_POST['cart66-action'] == 'save usps account info') {
    foreach($_POST['ups'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    
    $methods = $_POST['usps_methods'];
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'usps';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('usps', $codes);
    $tab = 1;
  }
  elseif($_POST['cart66-action'] == 'save fedex account info') {
    foreach($_POST['fedex'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    
    $methods = (isset($_POST['fedex_methods'])) ? $_POST['fedex_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'fedex';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('fedex', $codes);
    
    $intlMethods = (isset($_POST['fedex_methods_intl'])) ? $_POST['fedex_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'fedex_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('fedex_intl', $intlCodes);
    
    $tab = 3;
  }
  elseif($_POST['cart66-action'] == 'save aupost account info') {
    foreach($_POST['aupost'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['aupost_methods'])) ? $_POST['aupost_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'aupost';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('aupost', $codes);
    
    $intlMethods = (isset($_POST['aupost_methods_intl'])) ? $_POST['aupost_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'aupost_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('aupost_intl', $intlCodes);
    
    $tab = 4;
  }
  elseif($_POST['cart66-action'] == 'save capost account info') {
    foreach($_POST['capost'] as $key => $value) {
      Cart66Setting::setValue($key, $value);
    }
    $methods = (isset($_POST['capost_methods'])) ? $_POST['capost_methods'] : false;
    $codes = array();
    if(is_array($methods)) {
      foreach($methods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'capost';
        $m->save();
        $codes[] = $code;
      }
    }
    else {
      $codes[] = -1;
    }
    $method->pruneCarrierMethods('capost', $codes);
    
    $intlMethods = (isset($_POST['capost_methods_intl'])) ? $_POST['capost_methods_intl'] : false;
    $intlCodes = array();
    if(is_array($intlMethods)) {
      foreach($intlMethods as $methodData) {
        list($code, $name) = explode('~', $methodData);
        $m = new Cart66ShippingMethod();
        $m->code = $code;
        $m->name = $name;
        $m->carrier = 'capost_intl';
        $m->save();
        $intlCodes[] = $code;
      }
    }
    else {
      $intlCodes[] = -1;
    }
    $method->pruneCarrierMethods('capost_intl', $intlCodes);
    
    $tab = 5;
  }
  elseif($_POST['cart66-action'] == 'enable live rates') {
    Cart66Setting::setValue('use_live_rates', 1);
  }
  elseif($_POST['cart66-action'] == 'disable live rates') {
    Cart66Setting::setValue('use_live_rates', '');
  }
  elseif($_POST['cart66-action'] == 'save rate tweak') {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving a rate tweak");
    $factor = Cart66Common::postVal('rate_tweak_factor');
    if(is_numeric($factor)) {
      Cart66Setting::setValue('rate_tweak_factor', $factor);
      Cart66Setting::setValue('rate_tweak_type', Cart66Common::postVal('rate_tweak_type'));
    }
    else {
      Cart66Setting::setValue('rate_tweak_factor', '');
      Cart66Setting::setValue('rate_tweak_type', '');
    }
    $tab = 7;
  }
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rule->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit_method' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $method->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit_rate' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rate->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rule->load($id);
  $rule->deleteMe();
  $rule->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete_method' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $method->load($id);
  $method->deleteMe();
  $method->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete_rate' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $rate->load($id);
  $rate->deleteMe();
  $rate->clear();
}
?>
<h2>Cart66 Shipping</h2>
<div class='wrap'>

  <?php if(CART66_PRO): ?>
  <div style="padding: 10px; 25px; width: 580px; background-color: #EEE; border: 1px solid #CCC; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
    <h3><?php _e( 'Live Shipping Rates' , 'cart66' ); ?></h3>
  
    <p><?php _e( 'Using live shipping rates overrides all other types of shipping settings.' , 'cart66' ); ?></p>
  
    <?php if(Cart66Setting::getValue('use_live_rates')): ?>
      <form action="" method="post">
        <p><?php _e( 'Live shipping rates are enabled.' , 'cart66' ); ?></p>
        <input type='hidden' name='cart66-action' value='disable live rates' />
        <input type="submit" name="submit" value="Disable Live Shipping Rates" id="submit" class="button-secondary" />
      </form>
    <?php else: ?>
      <form action="" method="post">
        <p><?php _e( 'Live shipping rates are not enabled.' , 'cart66' ); ?></p>
        <input type='hidden' name='cart66-action' value='enable live rates' />
        <input type="submit" name="submit" value="Enable Live Shipping Rates" id="submit" class="button-primary" />
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <?php if(CART66_PRO && Cart66Setting::getValue('use_live_rates')): ?>
    
    <div id="shipping_tabs" class="wrap">
      <div class="tabbed">
      	<div id="cart66-shipping-header">
      	  <ul class="tabs" id="sidemenu">
        	  <li class="sh1"><a class="sh1 tab" href="javascript:void(0)"><?php _e('USPS Shipping', 'cart66') ?></a></li>
        	  <li class="sh2"><a class="sh2 tab" href="javascript:void(0)"><?php _e('UPS Shipping', 'cart66') ?></a></li>
        	  <li class="sh3"><a class="sh3 tab" href="javascript:void(0)"><?php _e('FedEx Shipping' , 'cart66'); ?></a></li>
        	  <li class="sh4"><a class="sh4 tab" href="javascript:void(0)"><?php _e('Australia Post Shipping', 'cart66') ?></a></li>
        	  <li class="sh5"><a class="sh5 tab" href="javascript:void(0)"><?php _e('Canada Post Shipping', 'cart66') ?></a></li>
        	  <li class="sh6"><a class="sh6 tab" href="javascript:void(0)"><?php _e('Local Pickup', 'cart66') ?></a></li>
        	  <li class="sh7"><a class="sh7 tab" href="javascript:void(0)"><?php _e('Rate Tweaker', 'cart66') ?></a></li>
        	</ul>
      	</div>
      	<div class="loading">
      	  <h2 class="left"><?php _e('loading...', 'cart66') ?></h2>
      	</div>
      	<div class="sh1 pane">
      	  <h3 style="clear: both;"><?php _e( 'USPS Shipping Account Information' , 'cart66' ); ?></h3>
          <p><?php _e( 'If you intend to use United States Postal Service real-time shipping quotes please provide your USPS account information.<br/>This feature requires a <strong>production USPS account.</strong> A test account will not work.' , 'cart66' ); ?></p>
          <form action="" method='post'>
            <input type='hidden' name='cart66-action' value='save usps account info' />
            <ul>
              <li>
                <label class="med"><?php _e( 'Webtools username' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[usps_username]' id='usps_username' value='<?php echo Cart66Setting::getValue('usps_username'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[usps_ship_from_zip]' id='usps_ship_from_zip' value='<?php echo Cart66Setting::getValue('usps_ship_from_zip'); ?>' />
              </li>
              <li>
                <p><?php _e( 'Select the USPS shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
                <label class="med">&nbsp;</label> <a href="#" id="usps_clear_all">Clear All</a> | <a href="#" id="usps_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
              </li>
              <li>
                <?php
                  $services = Cart66ProCommon::getUspsServices();
                  $methods = $method->getServicesForCarrier('usps');
                  foreach($services as $name => $code) {
                    $checked = '';
                    if(in_array($code, $methods)) {
                      $checked = 'checked="checked"';
                    }
                    echo '<label class="med">&nbsp;</label>';
                    echo "<input type='checkbox' class='usps_shipping_options' name='usps_methods[]' value='$code~$name' $checked> $name<br/>";
                  }
                ?>
              </li>
              <li>
                <label class="med">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='Save' />
              </li>
            </ul>
          </form>
      	</div>
      	<div class="sh2 pane">
      	  <h3 style="clear: both;"><?php _e( 'UPS Shipping Account Information' , 'cart66' ); ?></h3>
          <p><?php _e( 'If you intend to use UPS real-time shipping quotes please provide your UPS account information.' , 'cart66' ); ?></p>
          <form action="" method='post'>
            <input type='hidden' name='cart66-action' value='save ups account info' />
            <ul>
              <li>
                <label class="med"><?php _e( 'Username' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[ups_username]' id='ups_username' value='<?php echo Cart66Setting::getValue('ups_username'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Password' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[ups_password]' id='ups_password' value='<?php echo Cart66Setting::getValue('ups_password'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'API Key' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[ups_apikey]' id='ups_apikey' value='<?php echo Cart66Setting::getValue('ups_apikey'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Account number' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[ups_account]' id='ups_account' value='<?php echo Cart66Setting::getValue('ups_account'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
                <input type='text' name='ups[ups_ship_from_zip]' id='ups_ship_from_zip' value='<?php echo Cart66Setting::getValue('ups_ship_from_zip'); ?>' />
              </li>        
              <li>
                <label class="med"><?php _e( 'Pickup Type' , 'cart66' ); ?>:</label>
                <select name='ups[ups_pickup_code]' id='ups_pickup_code'>
                  <option value="03">Drop Off</option>
                  <option value="01">Daily Pickup</option>
              <!--<option value="11">Suggested Retail Rates</option>
                  <option value="06">One Time Pickup</option> -->
                </select>
              </li>
              <li>
                <label class="med"><?php _e( 'Commercial Only' , 'cart66' ); ?>:</label>
                <input type="hidden" name='ups[ups_only_ship_commercial]' value='' />
                <input type='checkbox' name='ups[ups_only_ship_commercial]' id='ups_only_ship_commercial' value='1' <?php echo (Cart66Setting::getValue('ups_only_ship_commercial')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you only ship to commercial addresses' , 'cart66' ); ?>.
              </li>
              <li>
                <p><?php _e( 'Select the UPS shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
                <label class="med">&nbsp;</label> <a href="#" id="ups_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="ups_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
              </li>
              <li>
                <?php
                  $services = Cart66ProCommon::getUpsServices();
                  $methods = $method->getServicesForCarrier('ups');
                  foreach($services as $name => $code) {
                    $checked = '';
                    if(in_array($code, $methods)) {
                      $checked = 'checked="checked"';
                    }
                    echo '<label class="med">&nbsp;</label>';
                    echo "<input type='checkbox' class='ups_shipping_options' name='ups_methods[]' value='$code~$name' $checked> $name<br/>";
                  }
                ?>
              </li>
              <li>
                <label class="med">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
              </li>
            </ul>
          </form>
      	</div>
      	<div class="sh3 pane">
      	  <h3 style="clear: both;"><?php _e( 'FedEx Shipping Account Information' , 'cart66' ); ?></h3>
          <p><?php _e( "If you intend to use FedEx real-time shipping quotes please provide your FedEx account information. This feature requires a <strong>production FedEx</strong> account. A test account will not work." , 'cart66' ); ?></p>
          <form action="" method='post'>
            <input type='hidden' name='cart66-action' value='save fedex account info' />
            <ul>
              <li>
                <label class="med"><?php _e( 'Developer Key' , 'cart66' ); ?>:</label>
                <input type='text' name='fedex[fedex_developer_key]' id='fedex_developer_key' value='<?php echo Cart66Setting::getValue('fedex_developer_key'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Password' , 'cart66' ); ?>:</label>
                <input type='text' name='fedex[fedex_password]' id='fedex_password' value='<?php echo Cart66Setting::getValue('fedex_password'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Account Number' , 'cart66' ); ?>:</label>
                <input type='text' name='fedex[fedex_account_number]' id='fedex_account_number' value='<?php echo Cart66Setting::getValue('fedex_account_number'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Meter Number' , 'cart66' ); ?>:</label>
                <input type='text' name='fedex[fedex_meter_number]' id='fedex_meter_number' value='<?php echo Cart66Setting::getValue('fedex_meter_number'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
                <input type='text' name='fedex[fedex_ship_from_zip]' id='fedex_ship_from_zip' value='<?php echo Cart66Setting::getValue('fedex_ship_from_zip'); ?>' />
              </li>        
              <li>
                <label class="med"><?php _e( 'Pickup Type' , 'cart66' ); ?>:</label>
                <select name='fedex[fedex_pickup_code]' id='fedex_pickup_code'>
                  <option value="REGULAR_PICKUP"><?php _e( 'Regular Pickup' , 'cart66' ); ?></option>
                  <option value="REGULAR_COURIER"><?php _e( 'Regular Courier' , 'cart66' ); ?></option>
                  <option value="DROP_BOX"><?php _e( 'Drop Box' , 'cart66' ); ?></option>
                  <option value="STATION"><?php _e( 'Station' , 'cart66' ); ?></option>
                  <option value="BUSINESS_SERVICE_CENTER"><?php _e( 'Business Service Center' , 'cart66' ); ?></option>
                </select>
              </li>
              <li>
                <label class="med"><?php _e( 'Your Location' , 'cart66' ); ?>:</label>
                <select name='fedex[fedex_location_type]' id='fedex_location_type'>
                  <option value="commercial"><?php _e( 'Commercial' , 'cart66' ); ?></option>
                  <option value="residential"><?php _e( 'Residential' , 'cart66' ); ?></option>
                </select>
              </li>
              <li>
                <label class="med"><?php _e( 'Commercial Only' , 'cart66' ); ?>:</label>
                <input type="hidden" name='fedex[fedex_only_ship_commercial]' value='' />
                <input type='checkbox' name='fedex[fedex_only_ship_commercial]' id='fedex_only_ship_commercial' value='1' <?php echo (Cart66Setting::getValue('fedex_only_ship_commercial')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you only ship to commercial addresses' , 'cart66' ); ?>.
              </li>
              <li>
                <p><?php _e( 'Select the FedEx shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
                <label class="med">&nbsp;</label> <a href="#" id="fedex_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="fedex_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
              </li>
              <li>
                <?php
                  $homeCountryCode = 'US';
                  $setting = new Cart66Setting();
                  $home = Cart66Setting::getValue('home_country');
                  if($home) {
                    list($homeCountryCode, $name) = explode('~', $home);
                  }
                  if($homeCountryCode == 'US' || $homeCountryCode == 'CA') {
                    $services = Cart66ProCommon::getFedexServices();
                    $methods = $method->getServicesForCarrier('fedex');
                    foreach($services as $name => $code) {
                      $checked = '';
                      if(in_array($code, $methods)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<label class="med">&nbsp;</label>';
                      echo "<input type='checkbox' class='fedex_shipping_options' name='fedex_methods[]' value='$code~$name' $checked> $name<br/>";
                    }
                  }
                  $services = Cart66ProCommon::getFedexIntlServices();
                  $methods = $method->getServicesForCarrier('fedex_intl');
                  foreach($services as $name => $code) {
                    $checked = '';
                    if(in_array($code, $methods)) {
                      $checked = 'checked="checked"';
                    }
                    echo '<label class="med">&nbsp;</label>';
                    echo "<input type='checkbox' class='fedex_shipping_options' name='fedex_methods_intl[]' value='$code~$name' $checked> $name<br/>";
                  }
                ?>
              </li>
              <li>
                <label class="med">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
              </li>
            </ul>
          </form>
      	</div>
      	<div class="sh4 pane">
      	  <h3 style="clear: both;"><?php _e( 'Australia Post Shipping Account Information' , 'cart66' ); ?></h3>
          <p><?php _e( 'If you intend to use Australia Post real-time shipping quotes please provide your Australia Post account information.' , 'cart66' ); ?></p>
          <?php
          if($homeCountryCode !='AU'){
            echo '<h3>You must set Australia as your home country in order to use Australia Post Shipping Live Rates</h3>';
          } else {
          ?>
            <form action="" method='post'>
              <input type='hidden' name='cart66-action' value='save aupost account info' />
              <ul>
                <li>
                  <label class="med"><?php _e( 'Developer Key' , 'cart66' ); ?>:</label>
                  <input type='text' name='aupost[aupost_developer_key]' id='aupost_developer_key' value='<?php echo Cart66Setting::getValue('aupost_developer_key'); ?>' />
                </li>
                <li>
                  <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
                  <input type='text' name='aupost[aupost_ship_from_zip]' id='aupost_ship_from_zip' value='<?php echo Cart66Setting::getValue('aupost_ship_from_zip'); ?>' />
                </li>        
                <li>
                  <p><?php _e( 'Select the Australia Post shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
                  <label class="med">&nbsp;</label> <a href="#" id="aupost_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="aupost_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
                </li>
                <li>
                  <?php
                    $homeCountryCode = 'AU';
                    $setting = new Cart66Setting();
                    $home = Cart66Setting::getValue('home_country');
                    if($home) {
                      list($homeCountryCode, $name) = explode('~', $home);
                    }
                    $services = Cart66ProCommon::getAuPostServices();
                    $methods = $method->getServicesForCarrier('aupost');
                    foreach($services as $name => $code) {
                      $checked = '';
                      if(in_array($code, $methods)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<label class="med">&nbsp;</label>';
                      echo "<input type='checkbox' class='aupost_shipping_options' name='aupost_methods[]' value='$code~$name' $checked> $name<br/>";
                    }
                    $services = Cart66ProCommon::getAuPostIntlServices();
                    $methods = $method->getServicesForCarrier('aupost_intl');
                    foreach($services as $name => $code) {
                      $checked = '';
                      if(in_array($code, $methods)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<label class="med">&nbsp;</label>';
                      echo "<input type='checkbox' class='aupost_shipping_options' name='aupost_methods_intl[]' value='$code~$name' $checked> $name<br/>";
                    }
                  ?>
                </li>
                <li>
                  <label class="med">&nbsp;</label>
                  <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
                </li>
              </ul>
            </form>
          <?php } ?>
      	</div>
      	<div class="sh5 pane">
      	  <h3 style="clear: both;"><?php _e( 'Canada Post Shipping Account Information' , 'cart66' ); ?></h3>
          <p><?php _e( 'If you intend to use Canada Post real-time shipping quotes please provide your Canada Post account information.' , 'cart66' ); ?></p>
          <?php
          if($homeCountryCode !='CA'){
            echo '<h3>You must set Canada as your home country in order to use Canada Post Shipping Live Rates</h3>';
          } else {
          ?>
            <form action="" method='post'>
              <input type='hidden' name='cart66-action' value='save capost account info' />
              <ul>
                <li>
                  <label class="med"><?php _e( 'Merchant ID' , 'cart66' ); ?>:</label>
                  <input type='text' name='capost[capost_merchant_id]' id='capost_merchant_id' value='<?php echo Cart66Setting::getValue('capost_merchant_id'); ?>' />
                </li>
                <li>
                  <label class="med"><?php _e( 'Ship from zip' , 'cart66' ); ?>:</label>
                  <input type='text' name='capost[capost_ship_from_zip]' id='capost_ship_from_zip' value='<?php echo Cart66Setting::getValue('capost_ship_from_zip'); ?>' />
                </li>        
                <li>
                  <p><?php _e( 'Select the Canada Post shipping methods you would like to offer to your customers.' , 'cart66' ); ?></p>
                  <label class="med">&nbsp;</label> <a href="#" id="capost_clear_all"><?php _e( 'Clear All' , 'cart66' ); ?></a> | <a href="#" id="capost_select_all"><?php _e( 'Select All' , 'cart66' ); ?></a>
                </li>
                <li>
                  <?php
                    $homeCountryCode = 'CA';
                    $setting = new Cart66Setting();
                    $home = Cart66Setting::getValue('home_country');
                    if($home) {
                      list($homeCountryCode, $name) = explode('~', $home);
                    }
                    $services = Cart66ProCommon::getCaPostServices();
                    $methods = $method->getServicesForCarrier('capost');
                    foreach($services as $name => $code) {
                      $checked = '';
                      if(in_array($code, $methods)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<label class="med">&nbsp;</label>';
                      echo "<input type='checkbox' class='capost_shipping_options' name='capost_methods[]' value='$code~$name' $checked> $name<br/>";
                    }
                    $services = Cart66ProCommon::getCaPostIntlServices();
                    $methods = $method->getServicesForCarrier('capost_intl');
                    foreach($services as $name => $code) {
                      $checked = '';
                      if(in_array($code, $methods)) {
                        $checked = 'checked="checked"';
                      }
                      echo '<label class="med">&nbsp;</label>';
                      echo "<input type='checkbox' class='capost_shipping_options' name='capost_methods_intl[]' value='$code~$name' $checked> $name<br/>";
                    }
                  ?>
                </li>
                <li>
                  <label class="med">&nbsp;</label>
                  <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
                </li>
              </ul>
            </form>
          <?php } ?>
      	</div>
      	<div class="sh6 pane">
      	  <h3><?php _e( 'Local Pickup Option' , 'cart66' ); ?></h3>
          <p><?php _e( 'If you intend to use UPS real-time shipping quotes please provide your UPS account information.' , 'cart66' ); ?></p>
          <form action="" method='post'>
            <input type='hidden' name='cart66-action' value='save local pickup info' />
            <input type='hidden' name='local[shipping_local_pickup]' value='' />
            <input type='hidden' name='local[local_pickup_at_end]' value='' />
            <ul>
              <li>
                <label class="med"><?php _e( 'Enable' , 'cart66' ); ?>:</label>
                <input type='checkbox' name='local[shipping_local_pickup]' id='shipping_local_pickup' value='1' <?php echo (Cart66Setting::getValue('shipping_local_pickup')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to enable a local pickup or "in-store" option' , 'cart66' ); ?>.
              </li>
              <li>
                <label class="med"><?php _e( 'Push to End' , 'cart66' ); ?>:</label>
                <input type='checkbox' name='local[local_pickup_at_end]' id='local_pickup_at_end' value='1' <?php echo (Cart66Setting::getValue('local_pickup_at_end')) ? "checked='checked'" : ""; ?> /> <?php _e( 'Check this box if you want to put the local pickup option at the end of the live rates' , 'cart66' ); ?>.
              </li>
              <li>
                <label class="med"><?php _e( 'Label' , 'cart66' ); ?>:</label>
                <input type='text' name='local[shipping_local_pickup_label]' id='shipping_local_pickup_label' value='<?php echo Cart66Setting::getValue('shipping_local_pickup_label'); ?>' />
              </li>
              <li>
                <label class="med"><?php _e( 'Amount' , 'cart66' ); ?>:</label>
                <input type='text' name='local[shipping_local_pickup_amount]' id='shipping_local_pickup_amount' value='<?php echo Cart66Setting::getValue('shipping_local_pickup_amount'); ?>' />
              </li>
              <li>
                <label class="med">&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
              </li>
            </ul>
          </form>
      	</div>
      	<div class="sh7 pane">
      	  <h3><?php _e( 'Rate Tweaker' , 'cart66' ); ?></h3>

          <p style="border: 1px solid #CCC; background-color: #eee; padding: 5px; width: 590px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
            <strong><?php _e( 'Current Tweak Factor' , 'cart66' ); ?>:</strong> 
            <?php
              if(Cart66Setting::getValue('rate_tweak_factor')) {
                $type = Cart66Setting::getValue('rate_tweak_type');
                $factor = Cart66Setting::getValue('rate_tweak_factor');

                if($type == 'percentage') {
                  $direction = $factor > 0 ? 'increased' : 'decreased';
                  echo "All rates will be $direction by " . abs($factor) . '%';
                }
                else {
                  $direction = $factor > 0 ? 'added to' : 'subtracted from';
                  echo CART66_CURRENCY_SYMBOL . number_format(abs($factor), 2) . " will be $direction all rates";
                }

              }
              else {
                echo 'The calculated rates will not be tweaked.';
              }
            ?>
          </p>

          <form action="" method="post">
            <input type="hidden" name="cart66-action" value="save rate tweak" />
            <select name="rate_tweak_type" id="rate_tweak_type">
              <option value="percentage"><?php _e( 'Tweak by percentage' , 'cart66' ); ?></option>
              <option value="fixed"><?php _e( 'Tweak by fixed amount' , 'cart66' ); ?></option>
            </select>
            <span id="currency" style="display:none;">&nbsp;<?php echo CART66_CURRENCY_SYMBOL; ?></span>
            <input type="text" name="rate_tweak_factor" style="width: 5em;" />
            <span id="percentSign" style="display:none;">%</span>
            <input type='submit' name='submit' class="button-primary" style='width: 60px; margin-top: 10px; margin-left: 20px; margin-right: 20px;' value='Save' />
            <a id="whatIsRateTweaker" href="#" class='what_is'><?php _e( 'What is this?' , 'cart66' ); ?></a>
          </form>

          <div id="whatIsRateTweaker_answer" style="display: none; border: 1px solid #eee; background-color: #fff; padding: 0px 10px; width: 590px; margin-top: 10px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
            <h3><?php _e( 'How The Rate Tweaker Works' , 'cart66' ); ?></h3>
            <p><?php _e( 'The rate tweaker provides a way to adjust the live rate quotes by increasing or decreasing all of the calculated rates by a specified amount. You may choose to modify the rates by a percentage amount or by fixed amount. Enter a positive value to increase the calculated rates or negative value to reduce them. The rate tweaker will never reduce shipping rates below zero.' , 'cart66' ); ?></p>
            <p><?php _e( 'For example, if you want to increase all the calculated rates by 15% select "Tweak by percentage" and enter 15 in the text field then click "Save"' , 'cart66' ); ?></p>
            <p><?php _e( 'If you want to take $5.00 off all the shipping rates select "Tweak by fixed amount" and enter -5 in the text field then click "Save"' , 'cart66' ); ?></p>
            <p><?php _e( 'To stop using the rate tweaker, enter 0 and click "save"' , 'cart66' ); ?></p>
          </div>
      	</div>
      </div>
    </div>

  <?php else: ?>
  
    <h3 style="clear: both;"><?php _e( 'Shipping Methods' , 'cart66' ); ?></h3>
    <p style="width: 400px;"><?php _e( 'Create the shipping methods you will offer your customers. If no shipping
    price is defined for a product, the default rates entered here will be used
    to calculate shipping costs.' , 'cart66' ); ?></p> 

    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save shipping method' />
      <input type='hidden' name='shipping_method[id]' value='<?php echo $method->id ?>' />
    
      <ul>
        <li>
          <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
          <input type="text" name="shipping_method[name]" value="<?php echo $method->name ?>" />
          <span class="label_desc"><?php _e( 'ex. FedEx Ground' , 'cart66' ); ?></span>
        </li>
      
        <li>
          <label class="med"><?php _e( 'Default rate' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_rate]" value="<?php echo $method->default_rate ?>" style='width: 80px;'/>
          <span class="label_desc"><?php _e( 'Rate if only one item is ordered' , 'cart66' ); ?></span>
        </li>

        <li>
          <label class="med">Default bunde rate:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" name="shipping_method[default_bundle_rate]" value="<?php echo $method->default_bundle_rate ?>" style='width: 80px;'/>
          <span class="label_desc"><?php _e( 'Rate for each additional item' , 'cart66' ); ?></span>
        </li>

        <li>
          <label class="med">&nbsp;</label>
          <?php if($method->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='<?php _e( 'Save' , 'cart66' ); ?>' />
        </li>
      </ul>
    
    </form>
    
    <?php
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate');
    if(count($methods)):
    ?>
    <table class="widefat" style='width: 600px;'>
    <thead>
      <tr>
    		<th><?php _e( 'Shipping Method' , 'cart66' ); ?></th>
    		<th><?php _e( 'Default rate' , 'cart66' ); ?></th>
    		<th><?php _e( 'Default bundle rate' , 'cart66' ); ?></th>
        <th>&nbsp;</th>
    	</tr>
    </thead>
    <tbody>
      <?php foreach($methods as $m): ?>
        <tr>
          <td><?php echo $m->name ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($m->default_rate, 2); ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($m->default_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_method&id=<?php echo $m->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_method&id=<?php echo $m->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>

    <h3 style="clear: both;"><?php _e( 'Product Shipping Prices' , 'cart66' ); ?></h3>

    <p style="width: 400px;"><?php _e( 'The shipping prices you set up here override the default shipping prices for the shipping methods above.' , 'cart66' ); ?></p>
    
    <?php
      $products = Cart66Product::loadProductsOutsideOfClass();
      if(count($method->getModels()) && count($products)): ?>
      <form action="" method='post'>
        <input type="hidden" name="cart66-action" value="save product rate" />
        <input type="hidden" name="rate[id]" value="<?php echo $rate->id ?>" id="rate-id" />
        <ul>
          <li>
            <label class="med"><?php _e( 'Product' , 'cart66' ); ?>:</label>
            <select name='rate[product_id]'>
              <?php foreach($products as $p): ?>
                <option value="<?php echo $p->id; ?>" <?php echo ($p->id == $rate->product_id) ? 'selected="selected"' : '' ?>><?php echo $p->name ?> (<?php echo $p->item_number ?>)</option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
            <select name='rate[shipping_method_id]'>
              <?php foreach($method->getModels("where carrier = ''", 'order by name') as $m): ?>
                <option value="<?php echo $m->id; ?>" <?php echo ($m->id == $rate->shipping_method_id) ? 'selected="selected"' : '' ?>><?php echo $m->name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
          <li>
            <label class="med"><?php _e( 'Shipping rate' , 'cart66' ); ?>:</label>
            <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
            <input type="text" style="width: 80px;" name="rate[shipping_rate]" value="<?php echo $rate->shipping_rate ?>" />
          </li>
          <li>
            <label class="med">Shipping bundle rate:</label>
            <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
            <input type="text" style="width: 80px;" name="rate[shipping_bundle_rate]" value="<?php echo $rate->shipping_bundle_rate ?>" />
          </li>
          <li>
            <label class="med">&nbsp;</label>
            <?php if($rate->id > 0): ?>
              <a href='?page=cart66-shipping' class='button-secondary linkButton' style="">Cancel</a>
            <?php endif; ?>
            <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
          </li>
        </ul>
      </form>
    <?php else: ?>
      <p style="color: red;"><?php _e( 'You must enter at least one shipping method and at least one product for these setting to appear.' , 'cart66' ); ?></p>
    <?php endif; ?>

    <?php
    $rates = $rate->getModels(null, 'order by product_id, shipping_method_id');
    if(count($rates)):
    ?>
    <table class="widefat" style='width: auto;'>
    <thead>
      <tr>
    		<th><?php _e( 'Product' , 'cart66' ); ?></th>
    		<th><?php _e( 'Shipping method' , 'cart66' ); ?></th>
    		<th><?php _e( 'Rate' , 'cart66' ); ?></th>
    		<th><?php _e( 'Bundle rate' , 'cart66' ); ?></th>
        <th>&nbsp;</th>
    	</tr>
    </thead>
    <tbody>
      <?php foreach($rates as $r): ?>
        <?php 
          $product->load($r->product_id);
          $method->load($r->shipping_method_id);
        ?>
        <tr>
          <td><?php echo $product->item_number ?> <?php echo $product->name ?></td>
          <td><?php echo $method->name ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_rate, 2); ?></td>
          <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($r->shipping_bundle_rate, 2); ?></td>
          <td>
           <a href='?page=cart66-shipping&task=edit_rate&id=<?php echo $r->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
           <a class='delete' href='?page=cart66-shipping&task=delete_rate&id=<?php echo $r->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <?php endif; ?>
    <h3 style="clear: both;"><?php _e( 'Cart Price Shipping Rates' , 'cart66' ); ?></h3>

    <p style='width: 400px;'><?php _e( 'You can set the shipping cost based on the total cart value. For example, you 
      may want to offer free shipping on orders over $50. To do that set minimum cart amount to $50 and the
      shipping cost to $0.' , 'cart66' ); ?></p> 
    <p style='width: 400px;'><?php _e( 'You can also set up tiered shipping costs based on the cart amount. For example,
      if you want to charge $10 shipping on orders between $0 - $24.99 and $5 shipping on orders between $25 - $49.99
      and free shipping on orders $50 or more you would set that up with three shipping rules as follows.' , 'cart66' ); ?></p>
    
    <table style='width: 400px; margin-bottom: 20px;'>
      <tr>
        <th style='text-align: left;'><?php _e( 'Minimum cart amount' , 'cart66' ); ?></th>
        <th style='text-align: left;'><?php _e( 'Shipping cost' , 'cart66' ); ?></th>
      </tr>
      <tr>
        <td>$0</td>
        <td>$10</td>
      </tr>
      <tr>
        <td>$25</td>
        <td>$5</td>
      </tr>
      <tr>
        <td>$50</td>
        <td>$0</td>
      </tr>
    </table>

    <?php if(count($method->getModels())): ?>
    <form action="" method='post'>
      <input type='hidden' name='cart66-action' value='save rule' />
      <input type='hidden' name='rule[id]' value='<?php echo $rule->id ?>' />
      <ul>
        <li>
          <label for="rule-min_amount"><?php _e( 'Minimum cart amount' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type='text' name='rule[min_amount]' id='rule-min_amount' style='width: 80px;' value='<?php echo $rule->minAmount ?>' />
        </li>
        <li>
          <label class="med"><?php _e( 'Shipping method' , 'cart66' ); ?>:</label>
          <select name="rule[shipping_method_id]">
            <?php foreach($method->getModels(null, 'name') as $m): ?>
              <option value="<?php echo $m->id; ?>"><?php echo $m->name ?></option>
            <?php endforeach; ?>
          </select>
        </li>
        <li>
          <label class="med" for="rule-shipping_cost"><?php _e( 'Shipping cost' , 'cart66' ); ?>:</label>
          <span><?php echo CART66_CURRENCY_SYMBOL ?></span>
          <input type="text" id="rule-shipping_cost" name="rule[shipping_cost]" style='width: 80px;' value='<?php echo $rule->shippingCost ?>'>
        </li>
        <li>
          <label class="med">&nbsp;</label>
          <?php if($rule->id > 0): ?>
          <a href='?page=cart66-shipping' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
          <?php endif; ?>
          <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
        </li>
      </ul>
    </form>
    <?php else: ?>
      <p style='color: red;'><?php _e( 'You must have entered at least one shipping method before you can configure these settings.' , 'cart66' ); ?></p>
    <?php endif; ?>
  
    <?php
    $rules = $rule->getModels(null, 'order by min_amount');
    if(count($rules)):
    ?>
      <table class="widefat" style='width: auto;'>
        <thead>
        	<tr>
        		<th><?php _e( 'Minimum cart amount' , 'cart66' ); ?></th>
        		<th><?php _e( 'Shipping method' , 'cart66' ); ?></th>
        		<th><?php _e( 'Shipping cost' , 'cart66' ); ?></th>
        		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
        	</tr>
        </thead>
        <tbody>
          <?php foreach($rules as $rule): ?>
            <?php 
              $method = new Cart66ShippingMethod();
              $method->load($rule->shipping_method_id);
            ?>
           <tr>
             <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $rule->min_amount ?></td>
             <td><?php echo ($method->name) ? $method->name : "<span style='color:red;'>Please select a method</span>"; ?></td>
             <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $rule->shipping_cost ?></td>
             <td>
               <a href='?page=cart66-shipping&task=edit&id=<?php echo $rule->id ?>'><?php _e( 'Edit' , 'cart66' ); ?></a> | 
               <a class='delete' href='?page=cart66-shipping&task=delete&id=<?php echo $rule->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
             </td>
           </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    
  <?php endif; ?>

  
</div>

<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      
      $('div.sh<?php echo $tab; ?>').show();
  	  
  	  $('div.loading').hide();
  	  $('div.tabbed ul.tabs li.sh<?php echo $tab; ?> a').addClass('current');  	  
      // SHIPPING TABS
      $('div.tabbed ul li a.tab').click(function(){
  	    var thisClass = this.className.slice(0,3);
  	    $('div.pane').hide();
  	    $('div.' + thisClass).fadeIn(300);
  	    $('div.tabbed ul.tabs li a').removeClass('current');
  	    $('div.tabbed ul.tabs li a.' + thisClass).addClass('current');
  	  });
      
      $('#ups_clear_all').click(function() {
        $('.ups_shipping_options').attr('checked', false);
        return false;
      });

      $('#ups_select_all').click(function() {
        $('.ups_shipping_options').attr('checked', true);
        return false;
      });

      $('#usps_clear_all').click(function() {
        $('.usps_shipping_options').attr('checked', false);
        return false;
      });

      $('#usps_select_all').click(function() {
        $('.usps_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#ups_pickup_code').val("<?php echo Cart66Setting::getValue('ups_pickup_code'); ?>");
      $('#fedex_pickup_code').val("<?php echo Cart66Setting::getValue('fedex_pickup_code'); ?>");
      $('#fedex_location_type').val("<?php echo Cart66Setting::getValue('fedex_location_type'); ?>");
      
      $('#fedex_clear_all').click(function() {
        $('.fedex_shipping_options').attr('checked', false);
        return false;
      });

      $('#fedex_select_all').click(function() {
        $('.fedex_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#aupost_clear_all').click(function() {
        $('.aupost_shipping_options').attr('checked', false);
        return false;
      });

      $('#aupost_select_all').click(function() {
        $('.aupost_shipping_options').attr('checked', true);
        return false;
      });
      
      $('#capost_clear_all').click(function() {
        $('.capost_shipping_options').attr('checked', false);
        return false;
      });

      $('#capost_select_all').click(function() {
        $('.capost_shipping_options').attr('checked', true);
        return false;
      });
      
      setRateTweakerSymbol();

      $('#rate_tweak_type').change(function() {
        setRateTweakerSymbol();
      });
    })
    $('.what_is').click(function() {
      $('#' + $(this).attr('id') + '_answer').toggle('slow');
      return false;
    });

    $('.delete').click(function() {
      return confirm('Are you sure you want to delete this entry?');
    });

    function setRateTweakerSymbol() {
      if($('#rate_tweak_type').val() == 'percentage') {
        $('#percentSign').css('display', 'inline');
        $('#currency').css('display', 'none');
      }
      else {
        $('#currency').css('display', 'inline');
        $('#percentSign').css('display', 'none');
      }
    }
  })(jQuery);
</script>