<?php
  // Only render the ajax code for tracking inventory if inventory tracking is enabled
  $setting = new Cart66Setting();
  $trackInventory = Cart66Setting::getValue('track_inventory');
  $id = Cart66Common::getButtonId($data['product']->id);
  $priceString = $data['price'];
  $noSymbol = str_replace(CART66_CURRENCY_SYMBOL, '', $priceString);
  $isNumeric = false;
  if(is_numeric(str_replace(",","",$noSymbol))){    
    $isNumeric = true;
  }
  
?>

<?php if($data['gravity_form_id'] && CART66_PRO && $data['showPrice'] != 'only'): ?>
  <?php echo do_shortcode("[gravityform id=" . $data['gravity_form_id'] . " ajax=false] "); ?>
<?php elseif($data['showPrice'] == 'only'): ?>
  
  <?php if($data['product']->isSubscription()): ?>
    
    <?php echo $data['product']->getPriceDescription(); ?>

  <?php else: ?>      
    <span class="Cart66Price<?php echo $isNumeric ? '' : ' Cart66PriceDescription'; ?>">
      <?php if($isNumeric): ?>
        <span class="Cart66PriceLabel"><?php _e( 'Price' , 'cart66' ); ?>: </span>
      <?php endif; ?>
      <?php echo Cart66Common::currency($noSymbol, true, true); ?>
    </span>
    
  <?php endif; ?>
  
<?php else: ?>
  
  <form id='cartButtonForm_<?php echo $id ?>' class="Cart66CartButton" method="post" action="<?php echo Cart66Common::getPageLink('store/cart'); ?>" <?php echo $data['style']; ?>>
    <input type='hidden' name='task' id="task_<?php echo $id ?>" value='addToCart' />
    <input type='hidden' name='cart66ItemId' value='<?php echo $data['product']->id; ?>' />
    <input type='hidden' name='product_url' value='<?php echo Cart66Common::getCurrentPageUrl(); ?>' />
    
    <?php if($data['showName'] == 'true'): ?> 
      <span class="Cart66ProductName"><?php echo $data['product']->name; ?></span>
    <?php endif; ?>
    
    <?php if($data['showPrice'] == 'yes' && $data['is_user_price'] != 1): ?>
      <?php
      $css = '';
      if(strpos($data['quantity'],'user') !== FALSE && $data['is_user_price'] != 1 && $data['subscription'] == 0) {
        $css = ' Cart66PriceBlock';
      }
      ?>
      <span class="Cart66Price<?php echo $isNumeric ? $css : ' Cart66PriceDescription'; ?>">
        <?php if($isNumeric): ?>
          <span class="Cart66PriceLabel"><?php _e( 'Price' , 'cart66' ); ?>: </span>
          <?php echo Cart66Common::currency($noSymbol, true, true); ?>
        <?php else: ?>
          <?php echo $priceString; ?>
        <?php endif; ?>
      </span>
    <?php endif; ?>
    
    <?php if($data['is_user_price'] == 1) : ?>
      <span class="Cart66UserPrice">
        <label for="Cart66UserPriceInput_<?php echo $id ?>"><?php echo (Cart66Setting::getValue('userPriceLabel')) ? Cart66Setting::getValue('userPriceLabel') : __( 'Enter an amount: ' ) ?> </label><?php echo Cart66Common::currencySymbol('before'); ?><input id="Cart66UserPriceInput_<?php echo $id ?>" name="item_user_price" value="<?php echo str_replace(CART66_CURRENCY_SYMBOL,"",$data['price']);?>" size="5" /><?php echo Cart66Common::currencySymbol('after'); ?>
      </span>
    <?php endif; ?>
    
    <?php 
      if(strpos($data['quantity'],'user') !== FALSE && $data['is_user_price'] != 1 && $data['subscription'] == 0): 
        $quantityString = explode(":",$data['quantity']);
        if(isset($quantityString[1])){
          $defaultQuantity = (is_numeric($quantityString[1])) ? $quantityString[1] : 1;
        }
        else{
          $defaultQuantity = "";
        }
        
    ?>
      <span class="Cart66UserQuantity">
       <label for="Cart66UserQuantityInput_<?php echo $id; ?>"><?php echo (Cart66Setting::getValue('userQuantityLabel')) ? Cart66Setting::getValue('userQuantityLabel') : __( 'Quantity: ' ) ?> </label>
       <input id="Cart66UserQuantityInput_<?php echo $id; ?>" name="item_quantity" value="<?php echo $defaultQuantity; ?>" size="4">
      </span> 
    <?php elseif(is_numeric($data['quantity']) && $data['is_user_price'] != 1): ?>
       <input type="hidden" name="item_quantity" class="Cart66ItemQuantityInput" value="<?php echo $data['quantity']; ?>">       
    <?php endif; ?>
      
      
    <?php if($data['product']->isAvailable()): ?>
      <?php echo $data['productOptions'] ?>
    
      <?php if($data['product']->recurring_interval > 0 && !CART66_PRO): ?>
          <span class='Cart66ProRequired'><a href='http://www.cart66.com'><?php _e( 'Cart66 Professional' , 'cart66' ); ?></a> <?php _e( 'is required to sell subscriptions' , 'cart66' ); ?></span>
      <?php else: ?>
        <?php if($data['addToCartPath']): ?> 
          <input type='image' value='<?php echo $data['buttonText'] ?>' src='<?php echo $data['addToCartPath'] ?>' class="purAddToCartImage ajax-button" name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>'/>
        <?php else: ?>
          <input type='submit' value='<?php echo $data['buttonText'] ?>' class='Cart66ButtonPrimary purAddToCart ajax-button' name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>' />
        <?php endif; ?>
      <?php endif; ?>
    
    <?php else: ?>
      <span class='Cart66OutOfStock'><?php echo Cart66Setting::getValue('label_out_of_stock') ? Cart66Setting::getValue('label_out_of_stock') : __( 'Out of stock' , 'cart66' ); ?></span>
    <?php endif; ?>
    
    <?php if($trackInventory): ?>
      <input type="hidden" name="action" value="check_inventory_on_add_to_cart" />
      <span id="stock_message_box_<?php echo $id ?>" class="Cart66Unavailable Cart66Error" style="display: none;">
        <h2><?php _e('We\'re Sorry','cart66'); ?></h2>
        <p id="stock_message_<?php echo $id ?>"></p>
        <input type="button" name="close" value="Ok" id="close" class="Cart66ButtonSecondary modalClose" />
      </span>
    <?php endif; ?>

  </form>
<?php endif; ?>

<?php if($data['ajax'] == 'yes' || $data['ajax'] == 'true'): ?>
  <?php echo Cart66Common::getView('views/ajax-cart-button-message.php', array('id' => $id, 'productName' => $data['product']->name));?>
<?php endif; ?>

<?php if(Cart66Common::cart66UserCan('products') && Cart66Setting::getValue('enable_edit_product_links')): ?>
  <div class='cart66_edit_product_link'>
    <?php if($data['subscription'] == 0): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=cart66-products&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Product' , 'cart66' ); ?></a>
    <?php elseif($data['subscription'] == 1): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=cart66-paypal-subscriptions&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Subscription' , 'cart66' ); ?></a>
    <?php elseif($data['subscription'] == 2): ?>
      <a href='<?php echo admin_url(); ?>admin.php?page=cart66-products&amp;task=edit&amp;id=<?php echo $id ?>'><?php _e( 'Edit this Subscription' , 'cart66' ); ?></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if($trackInventory): ?>
  <?php if(is_user_logged_in()): ?>
    <div class="Cart66AjaxWarning"><?php _e('Inventory tracking will not work because your site has javascript errors.', 'cart66'); ?> 
      <a href="http://www.cart66.com/jquery-errors/"><?php _e('Possible solutions', 'cart66'); ?></a></div>
  <?php endif; ?>
<?php endif; ?>
<?php
$url = Cart66Common::appendWurlQueryString('cart66AjaxCartRequests');
if(Cart66Common::isHttps()) {
  $url = preg_replace('/http[s]*:/', 'https:', $url);
}
else {
  $url = preg_replace('/http[s]*:/', 'http:', $url);
}
$product_name = str_replace("'", "\'", $data["product"]->name);
$product = array(
  'id' => $id,
  'name' => $product_name,
  'ajax' => $data['ajax'],
  'returnUrl' => Cart66Common::getCurrentPageUrl(),
  'addingText' => __('Adding...' , 'cart66')
);
$localized_data = array(
  'youHave' => __('You have', 'cart66'),
  'inYourShoppingCart' => __('in your shopping cart', 'cart66'),
  'trackInventory' => $trackInventory,
  'ajaxurl' => $url,
);
$localized_data['products'][$id] = $product;

global $wp_scripts;
$data = $wp_scripts->get_data('cart66-library', 'data');
if(empty($data)) {
  wp_localize_script('cart66-library', 'C66', $localized_data);
}
else {
  if(!is_array($data)) {
    $data = json_decode(str_replace('var C66 = ', '', substr($data, 0, -1)), true);
  }
  foreach($data['products'] as $product_id => $product) {
    $localized_data['products'][$product_id] = $product;
  }
  $wp_scripts->add_data('cart66-library', 'data', '');
  wp_localize_script('cart66-library', 'C66', $localized_data);
}
apply_filters('cart66_filter_after_add_to_cart_button', true);

