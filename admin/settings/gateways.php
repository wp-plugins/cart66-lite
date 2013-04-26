<?php
$tab = 'gateway-gateway_settings';
?>
<div id="saveResult"></div>
<div id="cart66-inner-tabs">
  <ul class="subsubsub">
    <li><a href="#gateway-gateway_settings" class="gateway-gateway_settings"><?php _e('Gateway Settings', 'cart66'); ?></a> | </li>
    <li><a href="#gateway-mijireh_settings" class="gateway-mijireh_settings"><?php _e('Mijireh Checkout', 'cart66'); ?></a> | </li>
    <li><a href="#gateway-paypal_standard_settings" class="gateway-paypal_standard_settings"><?php _e('PayPal Standard', 'cart66'); ?></a> | </li>
    <li><a href="#gateway-paypal_express_pro_settings" class="gateway-paypal_express_pro_settings"><?php _e('PayPal Express', 'cart66'); ?><?php echo (CART66_PRO) ? __(' and PayPal Pro','cart66') : '' ?></a> | </li>
    <li><a href="#gateway-2checkout_settings" class="gateway-2checkout_settings"><?php _e('2Checkout', 'cart66'); ?></a> | </li>
    <li><a href="#gateway-other_gateways" class="gateway-other_gateways"><?php _e('Other Gateways', 'cart66'); ?></a></li>
  </ul>
  <br clear="all">
  <form id="gatewaySettingsForm" action="" method="post" class="ajaxSettingForm">
    <input type="hidden" name="action" value="save_settings" />
    <input type="hidden" name="_success" value="<?php _e('Your gateway settings have been saved', 'cart66'); ?>." />
    <div id="gateway-gateway_settings" class="pane">
      <h3><?php _e('Gateway Settings', 'cart66'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Accept Cards', 'cart66'); ?></th>
            <td>
              <?php if(CART66_PRO): ?>
                <?php
                  $cardTypes = Cart66Setting::getValue('auth_card_types');
                  if($cardTypes) {
                    $cardTypes = explode('~', $cardTypes);
                  }
                  else {
                    $cardTypes = array();
                  }
                ?>
                <input type="checkbox" name="auth_card_types[]" id="mastercard" value="mastercard" <?php echo in_array('mastercard', $cardTypes) ? 'checked="checked" ' : '' ?>>
                <label for="mastercard">Mastercard</label>
                <input type="checkbox" name="auth_card_types[]" id="visa" value="visa" <?php echo in_array('visa', $cardTypes) ? 'checked="checked" ' : '' ?>>
                <label for="visa">Visa</label>
                <input type="checkbox" name="auth_card_types[]" id="amex" value="amex" <?php echo in_array('amex', $cardTypes) ? 'checked="checked" ' : '' ?>>
                <label for="amex">American Express</label>
                <input type="checkbox" name="auth_card_types[]" id="discover" value="discover" <?php echo in_array('discover', $cardTypes) ? 'checked="checked" ' : '' ?>>
                <label for="discover">Discover</label>
                <p class="description"><?php _e('Select which credit cards you want to accept with Cart66. This does not apply to PayPal Express Checkout, PayPal Standard or Mijireh Checkout.', 'cart66'); ?></p>
              <?php else: ?>
                <p class="description"><?php _e( 'This feature is only available in', 'cart66'); ?> <a href="http://cart66.com"><?php _e('Cart66 Professional', 'cart66'); ?></a>.</p>
              <?php endif; ?>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Use SSL', 'cart66'); ?></th>
            <td>
              <?php if(CART66_PRO): ?>
                <?php
                  $force = Cart66Setting::getValue('auth_force_ssl');
                  if(!$force) { $force = 'no'; }
                ?>
                <input type="radio" name="auth_force_ssl" id="auth_force_ssl_yes" value="1" <?php echo Cart66Setting::getValue('auth_force_ssl') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="auth_force_ssl_yes"><?php _e( 'Yes' , 'cart66' ); ?></label>
                <input type="radio" name="auth_force_ssl" id="auth_force_ssl_no" value="0" <?php echo Cart66Setting::getValue('auth_force_ssl') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="auth_force_ssl_no"><?php _e( 'No' , 'cart66' ); ?></label>
                <p class="description"><?php _e( 'Be sure use an SSL certificate if you are using a payment gateway other than PayPal Website Payments Standard, PayPal Express Checkout or Mijireh Checkout.' , 'cart66' ); ?></p>
              <?php else: ?>
                <p class="description"><?php _e( 'This feature is only available in', 'cart66'); ?> <a href="http://cart66.com"><?php _e('Cart66 Professional', 'cart66'); ?></a>.</p>
              <?php endif; ?>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('PayPal Sandbox', 'cart66'); ?></th>
            <td>
              <input type="radio" name="paypal_sandbox" id="paypal_sandbox_yes" value="1" <?php echo Cart66Setting::getValue('paypal_sandbox') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="paypal_sandbox_yes"><?php _e( 'Yes' , 'cart66' ); ?></label>
              <input type="radio" name="paypal_sandbox" id="paypal_sandbox_no" value="0" <?php echo Cart66Setting::getValue('paypal_sandbox') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="paypal_sandbox_no"><?php _e( 'No' , 'cart66' ); ?></label>
              <p class="description"><?php _e( 'Send transactions to', 'cart66'); ?> <a href="https://developer.paypal.com"><?php _e('PayPal\'s developer sandbox', 'cart66'); ?></a>.</p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('PayPal Default Currency', 'cart66'); ?></th>
            <td>
              <select name="currency_code"  id="currency_code">
                <?php
                  $currencies = Cart66Common::getPayPalCurrencyCodes();
                  $current_lc = Cart66Setting::getValue('currency_code');
                  foreach($currencies as $name => $code) {
                    $selected = '';
                    if($code == $current_lc) {
                      $selected = 'selected="selected"';
                    } ?>
                    <option value="<?php echo $code ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
                  <?php }
                ?>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="gateway-mijireh_settings" class="pane">
      <a href="http://mijireh.com" target="_blank" style="float:right;"><img src="https://cart66.com/images/integrations/mijireh-checkout-large.png" align="left" alt="Mijireh Checkout"></a>
      <h3><?php _e('Mijireh Checkout Settings - Secure Credit Card Processing', 'cart66'); ?></h3>
      <?php if(!Cart66Setting::getValue('mijireh_access_key')): ?>
        <p class="description"><a href="http://www.mijireh.com"><?php _e('Secure credit card processing. Get started for FREE', 'cart66'); ?></a>.</p>
      <?php endif; ?>

      <p class="description"><?php _e('Accept credit cards with peace of mind using', 'cart66'); ?> <a href="http://www.mijireh.com">Mijreh</a>. <?php _e('You focus on the selling while Mijireh takes care of the security', 'cart66'); ?>.</p>
      <p class="description"><?php _e('Note: Mijireh checkout will not process recurring payments', 'cart66'); ?>.</p>
      <?php
        $has_mijireh = Cart66Setting::getValue('mijireh_store_id') || Cart66Setting::getValue('mijireh_access_key');
      ?>
      <?php if(!$has_mijireh): ?>
        <p class="description"><a href="http://mijireh.com"><?php _e('Get Mijireh Now', 'cart66'); ?></a></p>
      <?php endif; ?>
      </p>
      <?php
        $post = get_page_by_path('store/mijireh-secure-checkout');
        $slurp_page = 'post.php?post=' . $post->ID . '&action=edit';
      ?>
      <p class="description"><?php _e('To get setup with Mijireh', 'cart66'); ?>:</p>
      <p class="description">
        <ol>
          <li><?php _e('Enter your Mijireh Access Key', 'cart66'); ?></li>
          <li><?php _e('Make sure your store/checkout page has the [checkout_mijireh] shortcode on it', 'cart66'); ?></li>
          <li><?php _e('Go to the', 'cart66'); ?> <a href="<?php echo $slurp_page; ?>"><?php _e('Mijireh Secure Checkout', 'cart66'); ?></a> <?php _e('page and slurp it', 'cart66'); ?></li>
          <li><?php _e('Configure your gateways in Mijireh and go live', 'cart66'); ?></li>
        </ol>
      </p>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Access Key', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="mijireh_access_key" id="mijireh_access_key" value="<?php echo Cart66Setting::getValue('mijireh_access_key'); ?>" />
              <label for="mijireh_access_key"><span class="description"><?php _e('Enter your Mijireh access key', 'cart66'); ?></span></label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="gateway-paypal_standard_settings" class="pane">
      <a href="#" target="_blank" style="float:right;"><img src="https://cart66.com/images/integrations/paypal_logo.png" align="left" alt="PayPal"></a>
      <h3><?php _e('PayPal Standard Settings', 'cart66'); ?></h3>
      <p class="description"><?php _e('If you would like to use the PayPal Sandbox to test transactions, you will need to enable it in the Gateway Settings section.', 'cart66'); ?></p>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('PayPal Email', 'cart66'); ?></th>
            <td>
              <input type="text" name="paypal_email" id="paypal_email" value="<?php echo Cart66Setting::getValue('paypal_email'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Shopping URL', 'cart66'); ?></th>
            <td>
              <input type="text" name="shopping_url" id="shopping_url" value="<?php echo Cart66Setting::getValue('shopping_url'); ?>" />
              <p class="description"><?php _e( 'Used when buyers click \'Continue Shopping\' in the PayPal Cart.' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Return URL', 'cart66'); ?></th>
            <td>
              <input type="text" name="paypal_return_url" id="paypal_return_url" value="<?php echo Cart66Setting::getValue('paypal_return_url'); ?>" />
              <p class="description"><?php _e( 'Where buyers are sent after paying at PayPal.' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Notification URL', 'cart66'); ?></th>
            <td>
              <p>
              <?php
                $ipnPage = get_page_by_path('store/ipn');
                $ipnUrl = get_permalink($ipnPage->ID);
                echo $ipnUrl;
              ?>
              </p>
              <p class="description"><?php _e( 'Instant Payment Notification (IPN)' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Custom PayPal Standard Image', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="custom_paypal_standard_image" id="custom_paypal_standard_image" value="<?php echo Cart66Setting::getValue('custom_paypal_standard_image'); ?>" />
              <p class="description"><?php _e( 'Enter a URL to use a custom PayPal Image for the PayPal Standard checkout page.' , 'cart66' ); ?></p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="gateway-paypal_express_pro_settings" class="pane">
      <a href="#" target="_blank" style="float:right;"><img src="https://cart66.com/images/integrations/paypal_logo.png" align="left" alt="PayPal"></a>
      <h3><?php _e( 'PayPal API Settings for Express Checkout' , 'cart66' ); ?><?php echo (CART66_PRO) ? __(' and Website Payments Pro','cart66') : '' ?></h3>
      <p class="description"><?php _e('If you would like to use the PayPal Sandbox to test transactions, you will need to enable it in the Gateway Settings section.', 'cart66'); ?></p>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('API Username', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="paypalpro_api_username" id="paypalpro_api_username" value="<?php echo Cart66Setting::getValue('paypalpro_api_username'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('API Password', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="paypalpro_api_password" id="paypalpro_api_password" value="<?php echo Cart66Setting::getValue('paypalpro_api_password'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('API Signature', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="paypalpro_api_signature" id="paypalpro_api_signature" value="<?php echo Cart66Setting::getValue('paypalpro_api_signature'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Custom PayPal Express Image', 'cart66'); ?></th>
            <td>
              <input type="text" class="regular-text" name="custom_paypal_express_image" id="custom_paypal_express_image" value="<?php echo Cart66Setting::getValue('custom_paypal_express_image'); ?>" />
              <p class="description"><?php _e( 'Enter a URL to use a custom PayPal Image for the PayPal Express checkout page.' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('PayPal Account', 'cart66'); ?></th>
            <td>
              <input type="radio" name="express_force_paypal" id="express_force_paypal_yes" value="1" <?php echo Cart66Setting::getValue('express_force_paypal') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="express_force_paypal_yes"><?php _e( 'Don\'t Require Account' , 'cart66' ); ?></label>
              <input type="radio" name="express_force_paypal" id="express_force_paypal_no" value="0" <?php echo Cart66Setting::getValue('express_force_paypal') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="express_force_paypal_no"><?php _e( 'Require Account' , 'cart66' ); ?></label>
              <p class="description"><?php _e( 'Allow Express Checkout customers to checkout without a PayPal Account' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Disable Landing Page', 'cart66'); ?></th>
            <td>
              <input type="radio" name="disable_landing_page" id="disable_landing_page_yes" value="1" <?php echo Cart66Setting::getValue('disable_landing_page') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="disable_landing_page_yes"><?php _e( 'Yes' , 'cart66' ); ?></label>
              <input type="radio" name="disable_landing_page" id="disable_landing_page_no" value="0" <?php echo Cart66Setting::getValue('disable_landing_page') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="disable_landing_page_no"><?php _e( 'No' , 'cart66' ); ?></label>
              <p class="description"><?php _e( 'This allows you to send your customers directly to the login page instead of the signup page for PayPal Express' , 'cart66' ); ?></p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="gateway-2checkout_settings" class="pane">
      <a href="#" target="_blank" style="float:right;"><img src="https://cart66.com/images/integrations/2checkout-logo.png" align="left" alt="2Checkout"></a>
      <h3><?php _e('2Checkout Settings', 'cart66'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Account Number', 'cart66'); ?></th>
            <td>
              <input type="text" name="tco_account_number" id="tco_account_number" value="<?php echo Cart66Setting::getValue('tco_account_number'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Secret Word', 'cart66'); ?></th>
            <td>
              <input type="text" name="tco_secret_word" id="tco_secret_word" value="<?php echo Cart66Setting::getValue('tco_secret_word'); ?>" />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Demo Mode', 'cart66'); ?></th>
            <td>
              <input type="radio" name="tco_test_mode" value="1" id="tco_test_mode_yes"<?php echo Cart66Setting::getValue('tco_test_mode') == 1 ? ' checked="checked"' : ''; ?> />
              <label for="tco_test_mode_yes"><?php _e('Yes', 'cart66'); ?></label>
              <input type="radio" name="tco_test_mode" value="" id="tco_test_mode_no"<?php echo Cart66Setting::getValue('tco_test_mode') != 1 ? ' checked="checked"' : ''; ?> />
              <label for="tco_test_mode_No"><?php _e('No', 'cart66'); ?></label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="gateway-other_gateways" class="pane">
      <?php if(CART66_PRO): ?>
        <a href="#" target="_blank" class="authorize_logo"><img src="https://cart66.com/images/integrations/authorize-net-logo.png" align="left" alt="Authorize.net"></a>
        <a href="#" target="_blank" class="quantum_logo"><img src="https://cart66.com/images/integrations/quantum_logo.png" align="left" alt="Quantum Gateway"></a>
        <a href="#" target="_blank" class="eway_row"><img src="https://cart66.com/images/integrations/eWAY-logo.png" align="left" alt="eWay Payment Gateway (AU)"></a>
        <a href="#" target="_blank" class="mwarrior_row"><img src="https://cart66.com/images/integrations/merchant_warrior_logo.png" align="left" alt="Merchant Warrior Payment Gateway"></a>
        <a href="#" target="_blank" class="payleap_row"><img src="https://cart66.com/images/integrations/payleap_tagline.png" align="left" alt="PayLeap Payment Gateway"></a>
        <a href="#" target="_blank" class="stripe_row"><img src="https://cart66.com/images/integrations/stripelogo.png" align="left" alt="Stripe Payment Gateway"></a>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><?php _e('Gateway', 'cart66'); ?></th>
              <td>
                <select name="auth_url" id="auth_url">
                  <option id="authorize_url" value="https://secure.authorize.net/gateway/transact.dll">Authorize.net</option>
                  <option id="authorize_test_url" value="https://test.authorize.net/gateway/transact.dll">Authorize.net Test</option>
                  <option id="quantum_url" value="https://secure.quantumgateway.com/cgi/authnet_aim.php">Quantum Gateway</option>
                  <option id="eway_url" value="https://www.eway.com.au/gateway_cvn/xmlpayment.asp">eWay</option>
                  <option id="mwarrior_url" value="https://api.merchantwarrior.com/post/">Merchant Warrior</option>
                  <option id="payleap_url" value="https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard">PayLeap</option>
                  <option id="stripe_url" value="https://api.stripe.com/v1/charges">Stripe</option>
                  <option id="other_url" value="other"><?php _e( 'Other' , 'cart66' ); ?></option>
                </select>
              </td>
            </tr>
            <tr valign="top" class="authorize_row">
              <th scope="row"><?php _e('API Login ID', 'cart66'); ?></th>
              <td>
                <input type="text" name="auth_username" id="auth_username" class="regular-text" value="<?php echo Cart66Setting::getValue('auth_username'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="authorize_row">
              <th scope="row"><?php _e('Transaction Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="auth_trans_key" id="auth_trans_key" class="regular-text" value="<?php echo Cart66Setting::getValue('auth_trans_key'); ?>" />
                <p class="description authorize_help"><a href="http://cart66.com/system66/wp-content/uploads/authnet-api-login.jpg" target="_blank"><?php _e( 'Where can I find my Authorize.net API Login ID and Transaction Key?' , 'cart66' ); ?></a></p>
              </td>
            </tr>
            <tr valign="top" class="authorize_row">
              <th scope="row"><?php _e('Disable Line Item Submission', 'cart66'); ?></th>
              <td>
                <input type="radio" name="disable_authorizenet_items" id="disable_authorizenet_items_yes" value="1" <?php echo Cart66Setting::getValue('disable_authorizenet_items') == 1 ? 'checked="checked" ' : ''; ?>/>
                <label for="disable_authorizenet_items_yes"><?php _e('Yes', 'cart66'); ?></label>
                <input type="radio" name="disable_authorizenet_items" id="disable_authorizenet_items_no" value="0" <?php echo Cart66Setting::getValue('disable_authorizenet_items') != 1 ? 'checked="checked" ' : ''; ?>/>
                <label for="disable_authorizenet_items_no"><?php _e('No', 'cart66'); ?></label>
                <p class="description"><?php _e('Set this to yes to disable sending the item list to Authorize.net. This may be helpful if your product names include characters that Authorize.net does not support.', 'cart66'); ?></p>
              </td>
            </tr>
            <tr valign="top" class="emulation_url_row">
              <th scope="row"><?php _e('Emulation URL', 'cart66'); ?></th>
              <td>
                <input type="text" name="auth_url_other" id="auth_url_other" class="regular-text" value="<?php echo Cart66Setting::getValue('auth_url_other'); ?>" />
                <p class="description"><?php _e( 'Autorize.net AIM emulation URL' , 'cart66' ); ?></p>
              </td>
            </tr>
            <tr valign="top" class="eway_row">
              <th scope="row"><?php _e( 'eWay Customer ID' , 'cart66' ); ?></th>
              <td>
                <input type="text" name="eway_customer_id" id="eway_customer_id" class="regular-text" value="<?php echo Cart66Setting::getValue('eway_customer_id'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="eway_row">
              <th scope="row"><?php _e('eWay Sandbox', 'cart66'); ?></th>
              <td>
                <input type="radio" name="eway_sandbox" id="eway_sandbox_yes" value="1" <?php echo Cart66Setting::getValue('eway_sandbox') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="eway_sandbox_yes"><?php _e('Yes', 'cart66'); ?></label>
                <input type="radio" name="eway_sandbox" id="eway_sandbox_no" value="" <?php echo Cart66Setting::getValue('eway_sandbox') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="eway_sandbox_no"><?php _e('No', 'cart66'); ?></label>
              </td>
            </tr>
            <tr valign="top" class="eway_row">
              <th scope="row"><?php _e('eWay Sandbox Customer ID', 'cart66'); ?></th>
              <td>
                <input type="text" name="eway_sandbox_customer_id" id="eway_sandbox_customer_id" class="regular-text" value="<?php echo Cart66Setting::getValue('eway_sandbox_customer_id'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('Currency', 'cart66'); ?></th>
              <td>
                <select name="mwarrior_currency" id="mwarrior_currency">
                  <option value="AUD">AUD</option>
                  <option value="NZD">NZD</option>
                </select>
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('API Passphrase', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_api_passphrase" id="mwarrior_api_passphrase" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_api_passphrase'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('MerchantUUID', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_merchant_uuid" id="mwarrior_merchant_uuid" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_merchant_uuid'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('API key', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_api_key" id="mwarrior_api_key" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_api_key'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('Merchant Warrior Test Mode', 'cart66'); ?></th>
              <td>
                <input type="radio" name="mwarrior_test_mode" id="mwarrior_test_mode_yes" value="1" <?php echo Cart66Setting::getValue('mwarrior_test_mode') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="mwarrior_test_mode_yes"><?php _e('Yes', 'cart66'); ?></label>
                <input type="radio" name="mwarrior_test_mode" id="mwarrior_test_mode_no" value="" <?php echo Cart66Setting::getValue('mwarrior_test_mode') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="mwarrior_test_mode_no"><?php _e('No', 'cart66'); ?></label>
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('Test API Passphrase', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_test_api_passphrase" id="mwarrior_test_api_passphrase" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_test_api_passphrase'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('Test MerchantUUID', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_test_merchant_uuid" id="mwarrior_test_merchant_uuid" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_test_merchant_uuid'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="mwarrior_row">
              <th scope="row"><?php _e('Test API Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="mwarrior_test_api_key" id="mwarrior_test_api_key" class="regular-text" value="<?php echo Cart66Setting::getValue('mwarrior_test_api_key'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="payleap_row">
              <th scope="row"><?php _e('API Username', 'cart66'); ?></th>
              <td>
                <input type="text" name="payleap_api_username" id="payleap_api_username" class="regular-text" value="<?php echo Cart66Setting::getValue('payleap_api_username'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="payleap_row">
              <th scope="row"><?php _e('Transaction Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="payleap_transaction_key" id="payleap_transaction_key" class="regular-text" value="<?php echo Cart66Setting::getValue('payleap_transaction_key'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="payleap_row">
              <th scope="row"><?php _e('PayLeap Test Mode', 'cart66'); ?></th>
              <td>
                <input type="radio" name="payleap_test_mode" id="payleap_test_mode_yes" value="1" <?php echo Cart66Setting::getValue('payleap_test_mode') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="payleap_test_mode_yes"><?php _e('Yes', 'cart66'); ?></label>
                <input type="radio" name="payleap_test_mode" id="payleap_test_mode_no" value="" <?php echo Cart66Setting::getValue('payleap_test_mode') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="payleap_test_mode_no"><?php _e('No', 'cart66'); ?></label>
              </td>
            </tr>
            <tr valign="top" class="payleap_row">
              <th scope="row"><?php _e('Test API Username', 'cart66'); ?></th>
              <td>
                <input type="text" name="payleap_test_api_username" id="payleap_test_api_username" class="regular-text" value="<?php echo Cart66Setting::getValue('payleap_test_api_username'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="payleap_row">
              <th scope="row"><?php _e('Test Transaction Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="payleap_test_transaction_key" id="payleap_test_transaction_key" class="regular-text" value="<?php echo Cart66Setting::getValue('payleap_test_transaction_key'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="stripe_row">
              <th scope="row"><?php _e('Stripe API Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="stripe_api_key" id="stripe_api_key" class="regular-text" value="<?php echo Cart66Setting::getValue('stripe_api_key'); ?>" />
              </td>
            </tr>
            <tr valign="top" class="stripe_row">
              <th scope="row"><?php _e('Stripe Test Mode', 'cart66'); ?></th>
              <td>
                <input type="radio" name="stripe_test" id="stripe_test_yes" value="1" <?php echo Cart66Setting::getValue('stripe_test') == 1 ? 'checked="checked" ' : '' ?>/>
                <label for="stripe_test_yes"><?php _e('Yes', 'cart66'); ?></label>
                <input type="radio" name="stripe_test" id="stripe_test_no" value="" <?php echo Cart66Setting::getValue('stripe_test') != 1 ? 'checked="checked" ' : '' ?>/>
                <label for="stripe_test_no"><?php _e('No', 'cart66'); ?></label>
              </td>
            </tr>
            <tr valign="top" class="stripe_row">
              <th scope="row"><?php _e('Stripe Test API Key', 'cart66'); ?></th>
              <td>
                <input type="text" name="stripe_test_api_key" id="stripe_test_api_key" class="regular-text" value="<?php echo Cart66Setting::getValue('stripe_test_api_key'); ?>" />
              </td>
            </tr>
          </tbody>
        </table>
      <?php else: ?>
        <p class="description"><?php _e( 'These gateways are only available in', 'cart66'); ?> <a href="http://cart66.com"><?php _e('Cart66 Professional', 'cart66'); ?></a>.</p>
      <?php endif; ?>
    </div>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <?php submit_button(); ?>
          </th>
          <td></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $('#cart66-inner-tabs div.pane').hide();
      $('#cart66-inner-tabs div#<?php echo $tab; ?>').show();
      $('#cart66-inner-tabs ul li a.<?php echo $tab; ?>').addClass('current');
      
      $('#cart66-inner-tabs ul li a').click(function(){
        $('#cart66-inner-tabs ul li a').removeClass('current');
        $(this).addClass('current');
        var currentTab = $(this).attr('href');
        $('#cart66-inner-tabs div.pane').hide();
        $(currentTab).show();
        return false;
      });
      <?php if($authUrl = Cart66Setting::getValue('auth_url')): ?>
      $('#auth_url').val('<?php echo $authUrl; ?>').attr('selected', true);
      <?php endif; ?>
      setGatewayDisplay();
      $('#auth_url').change(function(){
        setGatewayDisplay();
      })
    })
  })(jQuery);
  $jq = jQuery.noConflict();
  function setGatewayDisplay() {
    $jq('.authorize_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'authorize_url' || $jq('#auth_url :selected').attr('id') == 'authorize_test_url' || $jq('#auth_url :selected').attr('id') == 'quantum_url' || $jq('#auth_url :selected').attr('id') == 'other_url') {
      $jq('.authorize_row').show();
    }
    $jq('.authorize_help').hide();
    $jq('.authorize_logo').hide();
    if($jq('#auth_url :selected').attr('id') == 'authorize_url' || $jq('#auth_url :selected').attr('id') == 'authorize_test_url') {
      $jq('.authorize_help').show();
      $jq('.authorize_logo').show();
    }
    $jq('.emulation_url_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'other_url') {
      $jq('.emulation_url_row').show();
    }
    $jq('.quantum_logo').hide();
    if($jq('#auth_url :selected').attr('id') == 'quantum_url') {
      $jq('.quantum_logo').show();
    }
    $jq('.eway_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'eway_url') {
      $jq('.eway_row').show();
    }
    $jq('.mwarrior_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'mwarrior_url') {
      $jq('.mwarrior_row').show();
    }
    $jq('.payleap_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'payleap_url') {
      $jq('.payleap_row').show();
    }
    $jq('.stripe_row').hide();
    if($jq('#auth_url :selected').attr('id') == 'stripe_url') {
      $jq('.stripe_row').show();
    }
  }
</script>