 <?php 
Cart66Session::get('Cart66Cart')->resetPromotionStatus();
$items = Cart66Session::get('Cart66Cart')->getItems();
$shippingMethods = Cart66Session::get('Cart66Cart')->getShippingMethods();
$shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
$promotion = Cart66Session::get('Cart66Cart')->getPromotion();
$product = new Cart66Product();
$subtotal = Cart66Session::get('Cart66Cart')->getSubTotal();
$discountAmount = Cart66Session::get('Cart66Cart')->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new Cart66Setting();

// Try to return buyers to the last page they were on when the click to continue shopping

if(!Cart66Session::get('Cart66LastPage')) {
  // If the last page is not set, use the store url
  $lastPage = Cart66Setting::getValue('store_url') ? Cart66Setting::getValue('store_url') : get_bloginfo('url');
  Cart66Session::set('Cart66LastPage', $lastPage);
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
if(isset($data['tax']) && $data['tax'] > 0) {
  $tax = $data['tax'];
}
else {
  // Check to see if all sales are taxed
  $tax = Cart66Session::get('Cart66Cart')->getTax('All Sales');
}

$cartImgPath = Cart66Setting::getValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
}

if(count($items)): ?>

<?php if(Cart66Session::get('Cart66InventoryWarning') && $fullMode): ?>
  <div class="Cart66Unavailable">
    <h1><?php _e( 'Inventory Restriction' , 'cart66' ); ?></h1>
    <?php 
      echo Cart66Session::get('Cart66InventoryWarning');
      Cart66Session::drop('Cart66InventoryWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>


<?php if(Cart66Session::get('Cart66ZipWarning')): ?>
  <div id="Cart66ZipWarning" class="Cart66Unavailable">
    <h2><?php _e( 'Please Provide Your Zip Code' , 'cart66' ); ?></h2>
    <p><?php _e( 'Before you can checkout, please provide the zip code for where we will be shipping your order and click' , 'cart66' ); ?> "<?php _e( 'Calculate Shipping' , 'cart66' ); ?>".</p>
    <?php 
      Cart66Session::drop('Cart66ZipWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php elseif(Cart66Session::get('Cart66ShippingWarning')): ?>
  <div id="Cart66ShippingWarning" class="Cart66Unavailable">
    <h2><?php _e( 'No Shipping Service Selected' , 'cart66' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not selected a shipping method. If there are no shipping services available, we may not be able to ship to your location.' , 'cart66' ); ?></p>
    <?php Cart66Session::drop('Cart66ShippingWarning'); ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php if(Cart66Session::get('Cart66SubscriptionWarning')): ?>
  <div id="Cart66SubscriptionWarning" class="Cart66Unavailable">
    <h2><?php _e( 'Too Many Subscriptions' , 'cart66' ); ?></h2>
    <p><?php _e( 'Only one subscription may be purchased at a time.' , 'cart66' ); ?></p>
    <?php 
      Cart66Session::drop('Cart66SubscriptionWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php 
  if($accountId = Cart66Common::isLoggedIn()) {
    $account = new Cart66Account($accountId);
    if($sub = $account->getCurrentAccountSubscription()) {
      if($sub->isPayPalSubscription()) {
        ?>
        <p id="Cart66SubscriptionChangeNote"><?php _e( 'Your current subscription will be canceled when you purchase your new subscription.' , 'cart66' ); ?></p>
        <?php
      }
    }
  } 
?>

<form id='Cart66CartForm' action="" method="post">
  <input type='hidden' name='task' value='updateCart'>
  <table id='viewCartTable'>
    <tr>
      <th><?php _e('Product','cart66') ?></th>
      <th colspan="1"><?php _e( 'Quantity' , 'cart66' ); ?></th>
      <th>&nbsp;</th>
      <th><?php _e( 'Item Price' , 'cart66' ); ?></th>
      <th><?php _e( 'Item Total' , 'cart66' ); ?></th>
    </tr>
  
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> >
          <?php #echo $item->getItemNumber(); ?>
          <?php echo $item->getFullDisplayName(); ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = CART66_URL . '/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> colspan="2">
          
          <?php if($item->isSubscription() || $item->isMembershipProduct()): ?>
            <span class="subscriptionOrMembership"><?php echo $item->getQuantity() ?></span>
          <?php else: ?>
            <input type='text' name='quantity[<?php echo $itemIndex ?>]' value='<?php echo $item->getQuantity() ?>' class="itemQuantity"/>
          <?php endif; ?>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href='<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>' title='Remove item from cart'><img src='<?php echo $removeItemImg ?>' alt="Remove Item" /></a>
          
        </td>
        <?php else: ?>
          <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> colspan="2"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>><?php echo $item->getProductPriceDescription(); ?></td>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($price, 2) ?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="5">
            <a href='#' class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>"><?php _e( 'Show Details' , 'cart66' ); ?> <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>
    <?php endforeach; ?>
  
    <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>
      
      
      <?php if(CART66_PRO && Cart66Setting::getValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(Cart66Session::get('cart66_shipping_zip')): ?>
            <?php $zipStyle = "style='display: none;'"; ?>
            <tr id="shipping_to_row">
              <th colspan="5" class="alignRight">
                <?php _e( 'Shipping to' , 'cart66' ); ?> <?php echo Cart66Session::get('cart66_shipping_zip'); ?> 
                <?php
                  if(Cart66Setting::getValue('international_sales')) {
                    echo Cart66Session::get('cart66_shipping_country_code');
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link">change</a>)
                &nbsp;
                <?php
                  $liveRates = Cart66Session::get('Cart66Cart')->getLiveRates();
                  $rates = $liveRates->getRates();
                  Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                  $selectedRate = $liveRates->getSelected();
                  $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
                ?>
                <select name="live_rates" id="live_rates">
                  <?php foreach($rates as $rate): ?>
                    <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                      <?php 
                        if($rate->rate !== false) {
                          echo "$rate->service: \$$rate->rate";
                        }
                        else {
                          echo "$rate->service";
                        }
                      ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </th>
            </tr>
          <?php endif; ?>
        
          <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
            <th colspan="5" class="alignRight"><?php _e( 'Enter Your Zip Code' , 'cart66' ); ?>:
              <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />
              
              <?php if(Cart66Setting::getValue('international_sales')): ?>
                <select name="shipping_country_code">
                  <?php
                    $customCountries = Cart66Common::getCustomCountries();
                    foreach($customCountries as $code => $name) {
                      echo "<option value='$code'>$name</option>\n";
                    }
                  ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="shipping_country_code" value="<?php echo Cart66Common::getHomeCountryCode(); ?>" id="shipping_country_code">
              <?php endif; ?>
              
              <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="Cart66ButtonSecondary" />
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="5" class='alignRight'>
              <?php
                $liveRates = Cart66Session::get('Cart66Cart')->getLiveRates();
                if($liveRates && Cart66Session::get('cart66_shipping_zip') && Cart66Session::get('cart66_shipping_country_code')) {
                  $selectedRate = $liveRates->getSelected();
                  echo __("Shipping to", "cart66") . " " . Cart66Session::get('cart66_shipping_zip') . " " . __("via","cart66") . " " . $selectedRate->service;
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
        <tr>
          <th colspan='5' class="alignRight"><?php _e( 'Shipping Method' , 'cart66' ); ?>: &nbsp;
            <select name='shipping_method_id' id='shipping_method_id'>
              <?php foreach($shippingMethods as $name => $id): ?>
              <option value='<?php echo $id ?>' 
               <?php echo ($id == Cart66Session::get('Cart66Cart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>
        <?php elseif(!$fullMode): ?>
        <tr>
          <th colspan='5' class="alignRight"><?php _e( 'Shipping Method' , 'cart66' ); ?>: 
            <?php 
              $method = new Cart66ShippingMethod(Cart66Session::get('Cart66Cart')->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>

    <tr class="subtotal">
      <?php if($fullMode): ?>
      <td>&nbsp;</td>
      <td colspan='2'>
        <input type='submit' name='updateCart' value='<?php _e( 'Update Total' , 'cart66' ); ?>' class="Cart66ButtonSecondary" />
      </td>
      <?php else: ?>
        <td colspan='3'>&nbsp;</td>
      <?php endif; ?>
      <td class="alignRight strong" colspan="1"><?php _e( 'Subtotal' , 'cart66' ); ?>:</td>
      <td class='strong' colspan="1"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($subtotal, 2); ?></td>
    </tr>
    
    <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>
    <tr class="shipping">
      <td colspan='1'>&nbsp;</td>
      <td colspan="2">&nbsp;</td>
      <td colspan="1" class="alignRight strong"><?php _e( 'Shipping' , 'cart66' ); ?>:</td>
      <td colspan="1" class="strong"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $shipping ?></td>
    </tr>
    <?php endif; ?>
    
    <?php if($promotion): ?>
      <tr class="coupon">
        <td colspan='2'>&nbsp;</td>
        <td colspan="2" class="alignRight strong"><?php _e( 'Coupon' , 'cart66' ); ?>:</td>
        <td colspan="1" class="strong"><?php echo $promotion->getAmountDescription(); ?></td>
      </tr>
    <?php endif; ?>
    
    
    <?php if($tax > 0): ?>
      <tr class="tax">
        <td colspan='3'>&nbsp;</td>
        <td colspan="1" class="alignRight strong"><?php _e( 'Tax' , 'cart66' ); ?>:</td>
        <td colspan="1" class="strong"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($tax, 2); ?></td>
      </tr>
    <?php endif; ?>
    
      <tr class="total">
        <?php if(Cart66Session::get('Cart66Cart')->getNonSubscriptionAmount() > 0): ?>
        <td class="alignRight" colspan='3'>
          <?php if($fullMode && Cart66Common::activePromotions()): ?>
            <p class="haveCoupon"><?php _e( 'Do you have a coupon?' , 'cart66' ); ?>
            <div id="couponCode"><input type='text' name='couponCode' value='' /></div>
            <div id="updateCart"><input type='submit' name='updateCart' value='<?php _e( 'Apply Coupon' , 'cart66' ); ?>' class="Cart66ButtonSecondary" /></div></p>
            <?php if(Cart66Session::get('Cart66Cart')->getPromoStatus() < 0): ?>
              <div class="promoMessage"><?php echo Cart66Session::get('Cart66Cart')->getPromoMessage(); ?></div>
            <?php endif; ?>
          <?php endif; ?>&nbsp;
        </td>
        <?php else: ?>
          <td colspan='3'>&nbsp;</td>
        <?php endif; ?>
        <td colspan="1" class="alignRight strong"><?php _e( 'Total' , 'cart66' ); ?>:</td>
        <td colspan="1" class="strong">
          <?php 
            echo CART66_CURRENCY_SYMBOL;
            echo number_format(Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax, 2);
          ?>
        </td>
      </tr>
  </table>
</form>

  <?php if($fullMode): ?>
  <div id="viewCartNav">
	<div id="continueShopping">
        <?php if($cartImgPath): ?>
          <a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66CartContinueShopping" ><img src='<?php echo $continueShoppingImg ?>' /></a>
        <?php else: ?>
          <a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66ButtonSecondary Cart66CartContinueShopping" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'cart66' ); ?></a>
        <?php endif; ?>
	</div>
	<div id="checkoutShopping">
        <?php
          $checkoutImg = false;
          if($cartImgPath) {
            $checkoutImg = $cartImgPath . 'checkout.png';
          }
        ?>

        <?php if($checkoutImg): ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>'><img src='<?php echo $checkoutImg ?>' /></a>
        <?php else: ?>
          <a id="Cart66CheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>' class="Cart66ButtonPrimary" title="Continue to Checkout"><?php _e( 'Checkout' , 'cart66' ); ?></a>
        <?php endif; ?>
		</div>
	</div>
  <?php endif; ?>
<?php else: ?>
  <div id="emptyCartMsg">
  <h3>Your Cart Is Empty</h3>
  <?php if($cartImgPath): ?>
    <p><a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' title="Continue Shopping" class="Cart66CartContinueShopping"><img alt="Continue Shopping" class="continueShoppingImg" src='<?php echo $continueShoppingImg ?>' /></a>
  <?php else: ?>
    <p><a href='<?php echo Cart66Session::get('Cart66LastPage'); ?>' class="Cart66ButtonSecondary" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'cart66' ); ?></a>
  <?php endif; ?>
  </div>
  <?php
    Cart66Session::get('Cart66Cart')->clearPromotion();
  ?>
<?php endif; ?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    $jq('#shipping_method_id').change(function() {
      $jq('#Cart66CartForm').submit();
    });
    
    $jq('#live_rates').change(function() {
      $jq('#Cart66CartForm').submit();
    });
    
    $jq('.showEntriesLink').click(function() {
      var panel = $jq(this).attr('rel');
      $jq('#' + panel).toggle();
      return false;
    });
    
    $jq('#change_shipping_zip_link').click(function() {
      $jq('#set_shipping_zip_row').toggle();
      return false;
    });
  });
  
//]]>  
</script>
