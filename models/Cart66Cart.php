<?php
class Cart66Cart {
  
  /**
   * An array of Cart66CartItem objects
   */
  private $_items;
  
  private $_promotion;
  private $_promoStatus;
  private $_shippingMethodId;
  private $_liveRates;
  
  public function __construct($items=null) {
    if(is_array($items)) {
      $this->_items = $items;
    }
    else {
      $this->_items = array();
    }
    $this->_promoStatus = 0;
    $this->_setDefaultShippingMethodId();
    
    if(CART66_PRO) {
      $this->_liveRates = new Cart66LiveRates();
    }
    
  }
  
  /**
   * Add an item to the shopping cart when an Add To Cart button is clicked.
   * Combine the product options, check inventory, and add the item to the shopping cart.
   * If the inventory check fails redirect the user back to the referring page.
   * This function assumes that a form post triggered the call.
   */
  public function addToCart() {
    $itemId = Cart66Common::postVal('cart66ItemId');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");

    $options = '';
    if(isset($_POST['options_1'])) {
      $options = Cart66Common::postVal('options_1');
    }
    if(isset($_POST['options_2'])) {
      $options .= '~' . Cart66Common::postVal('options_2');
    }
    
    if(isset($_POST['item_quantity'])) {
      $itemQuantity = ($_POST['item_quantity'] > 0) ? round($_POST['item_quantity'],0) : 1;
    }
    else{
      $itemQuantity = 1;
    }
    
    if(isset($_POST['item_user_price'])){
      $sanitizedPrice = preg_replace("/[^0-9\.]/","",$_POST['item_user_price']);
      Cart66Session::set("userPrice_$itemId",$sanitizedPrice);
    }

    if(Cart66Product::confirmInventory($itemId, $options)) {
      $this->addItem($itemId, $itemQuantity, $options);
    }
    else {
      Cart66Common::log("Item not added due to inventory failure");
      wp_redirect($_SERVER['HTTP_REFERER']);
    }
    //$this->_setAutoPromoFromPost();
    $this->_setPromoFromPost();
  }
  
  public function updateCart() {
    if(Cart66Common::postVal('updateCart') == 'Calculate Shipping') {
      Cart66Session::set('cart66_shipping_zip', Cart66Common::postVal('shipping_zip'));
      Cart66Session::set('cart66_shipping_country_code', Cart66Common::postVal('shipping_country_code'));
    }
    $this->_setShippingMethodFromPost();
    $this->_updateQuantitiesFromPost();
    $this->_setCustomFieldInfoFromPost();
    //$this->_setAutoPromoFromPost();
    $this->_setPromoFromPost();
    
    Cart66Session::touch();
    do_action('cart66_after_update_cart', $this);
  }
  
  public function addItem($id, $qty=1, $optionInfo='', $formEntryId=0) {
    $optionInfo = $this->_processOptionInfo($optionInfo);
    $product = new Cart66Product($id);
    
    if($product->id > 0) {
      
      $newItem = new Cart66CartItem($product->id, $qty, $optionInfo->options, $optionInfo->priceDiff);
      
      if( ($product->isSubscription() || $product->isMembershipProduct()) && ($this->hasSubscriptionProducts() || $this->hasMembershipProducts() )) {
        // Make sure only one subscription can be added to the cart. Spreedly only allows one subscription per subscriber.
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to remove membership item");
        $this->removeMembershipProductItem();
      }
      
      if($product->isGravityProduct()) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is a Gravity Product: $formEntryId");
        if($formEntryId > 0) {
          $newItem->addFormEntryId($formEntryId);
          $this->_items[] = $newItem;
        }
      }
      else {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is NOT a Gravity Product");
        $isNew = true;
        $newItem->setQuantity($qty);
        foreach($this->_items as $item) {
          if($item->isEqual($newItem)) {
            $isNew = false;
            $newQuantity = $item->getQuantity() + $qty;
            $item->setQuantity($newQuantity);
            if($formEntryId > 0) {
              $item->addFormEntryId($formEntryId);
            }
            break;
          }
        }
        if($isNew) {
          $this->_items[] = $newItem;
        }
      }

      $this->_setPromoFromPost();
      Cart66Session::touch();
      do_action('cart66_after_add_to_cart', $product, $qty);
    }
    
  }
  
  public function removeItem($itemIndex) {
    if(isset($this->_items[$itemIndex])) {
      $product = $this->_items[$itemIndex]->getProduct();
      $this->_items[$itemIndex]->detachAllForms();
      if(count($this->_items) <= 1) {
        $this->_items = array();
        Cart66Session::drop('Cart66Tax',true);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Reset the cart items array");
      }
      else {
        unset($this->_items[$itemIndex]);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Did not reset the cart items array because the cart contains more than just a membership item");
      }
      $this->_setPromoFromPost();
      Cart66Session::touch();
      do_action('cart66_after_remove_item', $this, $product);
    }
  }
  
  public function removeItemByProductId($productId) {
    foreach($this->_items as $index => $item) {
      if($item->getProductId() == $productId) {
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing item at index: $index");
        $this->removeItem($index);
      }
    }
  }
  
  public function removeMembershipProductItem() {
    foreach($this->_items as $item) {
      if($item->isMembershipProduct() || $item->isSubscription()) {
        $productId = $item->getProductId();
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing membership item with product id: $productId");
        $this->removeItemByProductId($productId);
      }
    }
  }
  
  public function setPriceDifference($amt) {
    if(is_numeric($amt)) {
      $this->_priceDifference = $amt;
    }
  }
  
  public function setItemQuantity($itemIndex, $qty) {
    if(is_numeric($qty)) {
      if(isset($this->_items[$itemIndex])) {
        if($qty == 0) {
          unset($this->_items[$itemIndex]);
        }
        else {
          $this->_items[$itemIndex]->setQuantity($qty);
        }
      }
    }
  }
  
  public function setCustomFieldInfo($itemIndex, $info) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->setCustomFieldInfo($info);
    }
  }
  
  /**
   * Return the number of items in the shopping cart.
   * This count includes multiples of the same product so the returned value is the sum 
   * of all the item quantities for all the items in the cart.
   * 
   * @return int
   */
  public function countItems() {
    $count = 0;
    foreach($this->_items as $item) {
      $count += $item->getQuantity();
    }
    return $count;
  }
  
  public function getItems() {
    return $this->_items;
  }
  
  public function getItem($itemIndex) {
    $item = false;
    if(isset($this->_items[$itemIndex])) {
      $item = $this->_items[$itemIndex];
    }
    return $item;
  }
  
  public function setItems($items) {
    if(is_array($items)) {
      $this->_items = $items;
    }
  }
  
  public function getSubTotal() {
    $total = 0;
    foreach($this->_items as $item) {
      $total += $item->getProductPrice() * $item->getQuantity();
    }
    return $total;
  }
  
  public function getSubscriptionAmount() {
    $amount = 0;
    if($subId = $this->getSpreedlySubscriptionId()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($subId);
      if(!$subscription->hasFreeTrial()) {
        $amount = (float) $subscription->amount;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Subscription amount for subscription id: $subId = " . $subscription->amount);
      }
    }
    return $amount;
  }
  
  /**
   * Return the subtotal without including any of the subscription prices
   * 
   * @return float
   */
  public function getNonSubscriptionAmount() {
    $total = 0;
    foreach($this->_items as $item) {
      if(!$item->isSubscription()) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
      else {
        // item is subscription
        $total += $item->getBaseProductPrice();
      }
    }
    return $total;
  }
  
  public function getTaxableAmount() {
    $total = 0;
    $p = new Cart66Product();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if($p->taxable == 1) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    $discount = $this->getDiscountAmount();
    if($discount > $total) {
      $total = 0;
    }
    else {
      $total = $total - $discount;
    }
    return $total;
  }
  
  public function getTax($state='All Sales', $zip=null) {
    $tax = 0;
    $taxRate = new Cart66TaxRate();
    
    $isTaxed = $taxRate->loadByZip($zip);
    if($isTaxed == false) {
      $isTaxed = $taxRate->loadByState($state);
    }
    
    if($isTaxed) {
      $taxable = $this->getTaxableAmount();
      if($taxRate->tax_shipping == 1) {
        $taxable += $this->getShippingCost();
      }
      $tax = number_format($taxable * ($taxRate->rate/100), 2, '.', '');
    }
    
    return $tax;
  }
  
  /**
   * Return an array of the shipping methods where the keys are names and the values are ids
   * 
   * @return array of shipping names and ids
   */
  public function getShippingMethods() {
    $method = new Cart66ShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $ship = array();
    foreach($methods as $m) {
      $ship[$m->name] = $m->id;
    }
    return $ship;
  }

  public function getCartWeight() {
    $weight = 0;
    foreach($this->_items as $item) {
      $weight += $item->getWeight()  * $item->getQuantity();
    }
    return $weight;
  }
  
  public function getShippingCost($methodId=null) {
    $setting = new Cart66Setting();
    $shipping = null;
    if(!$this->requireShipping()) { 
      $shipping = 0; 
    }
    // Check to see if Live Rates are enabled and available
    elseif(Cart66Session::get('Cart66LiveRates') && get_class(Cart66Session::get('Cart66LiveRates')) == 'Cart66LiveRates' && Cart66Setting::getValue('use_live_rates')) {
      $liveRate = Cart66Session::get('Cart66LiveRates')->getSelected();
      if(is_numeric($liveRate->rate)) {
        $r = $liveRate->rate;
        return number_format($r, 2, '.', '');
      }
    }
    // Live Rates are not in use
    else {
      if($methodId > 0) {
        $this->_shippingMethodId = $methodId;
      }
      
      if($this->_shippingMethodId < 1) {
        $this->_setDefaultShippingMethodId();
      }
      else {
        // make sure shipping method exists otherwise reset to the default shipping method
        $method = new Cart66ShippingMethod();
        if(!$method->load($this->_shippingMethodId) || !empty($method->code)) {
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Resetting the default shipping method id");
          $this->_setDefaultShippingMethodId();
        }
      }
      
      $methodId = $this->_shippingMethodId;

      // Check for shipping rules first
      $shipping = 0;
      $isRuleSet = false;
      $rule = new Cart66ShippingRule();
      if(isset($methodId) && is_numeric($methodId)) {
        $rules = $rule->getModels("where shipping_method_id = $methodId", 'order by min_amount desc');
        if(count($rules)) {
          $cartTotal = $this->getSubTotal();
          foreach($rules as $rule) {
            if($cartTotal > $rule->minAmount) {
              $shipping = $rule->shippingCost;
              $isRuleSet = true; 
              break;
            }
          }
        }
      }
      
      
      if(!$isRuleSet) {
        $product = new Cart66Product();
        $shipping = 0;
        $highestShipping = 0;
        $bundleShipping = 0;
        $highestId = 0;
        foreach($this->_items as $item) {
          $product->load($item->getProductId());
          
          if($highestId < 1) {
            $highestId = $product->id;
          }
          
          if($product->isShipped()) {
            $shippingPrice = $product->getShippingPrice($methodId);
            $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
            if($shippingPrice > $highestShipping) {
              $highestShipping = $shippingPrice;
              $highestId = $product->id;
            }
            $bundleShipping += $bundleShippingPrice * $item->getQuantity();
          }
        }

        if($highestId > 0) {
          $product->load($highestId);
          $shippingPrice = $product->getShippingPrice($methodId);
          $bundleShippingPrice = $product->getBundleShippingPrice($methodId);
          $shipping = $shippingPrice + ($bundleShipping - $bundleShippingPrice);
        }
      }
    }
    
   
    $shipping = ($shipping < 0) ? 0 : $shipping;
    return number_format($shipping, 2, '.', '');
  }
  
  public function applyPromotion($code,$auto=false) {
    $code = strtoupper($code);
    $promotion = new Cart66Promotion();
    
    if($promotion->validateCustomerPromotion($code)) {  
      $this->clearPromotion();
      Cart66Session::set('Cart66Promotion',$promotion);
      Cart66Session::set('Cart66PromotionCode',$code);
    } 
    else {      
      $this->clearPromotion();
      if($auto==false){        
        Cart66Session::set('Cart66PromotionErrors',$promotion->getErrors());
      }    
      
    }
    
  }
  
  public function clearPromotion() {
    Cart66Session::drop('Cart66PromotionErrors',true);
    Cart66Session::drop('Cart66Promotion',true);
    Cart66Session::drop('Cart66PromotionCode',true);
  }
  
  public function getPromotion() {
    $promotion = false;
    if($sessionPromo = Cart66Session::get('Cart66Promotion')) {
      $promotion = $sessionPromo;
    }
    return $promotion;
  }
  
  // Get the products in the cart and returns the ID's of each product
  public function getProductsAndIds() {
    $product = new Cart66Product();
    $products = array();
    foreach($this->_items as $item) { //needs to be changed because _items is a private cart function
      $product->load($item->getProductId());
      $products[] = $product->id;
    }
    return $products;
  }
  
  
  public function getDiscountAmount() {
    $discount = 0;
    if(Cart66Session::get('Cart66Promotion')) {
      $discount = number_format(Cart66Session::get('Cart66Promotion')->getDiscountAmount(Cart66Session::get('Cart66Cart')), 2, '.', '');
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
    return $discount;
  }
  
  /**
   * Return the entire cart total including shipping costs and discounts.
   * An optional paramater can be provided to specify whether or not subscription items 
   * are included in the total.
   * 
   * @param boolean $includeSubscriptions
   * @return float 
   */
  public function getGrandTotal($includeSubscriptions=true) {
    if($includeSubscriptions) {
       $subTotal = $this->getSubTotal();
    }
    else{
       $subTotal = $this->getNonSubscriptionAmount();
    }
    
    // discounts apply to the total by default
    $total = $subTotal + $this->getShippingCost();
    
    if($this->getDiscountAmount() > 0){
      // discount is in use
      $promotion = Cart66Session::get('Cart66Promotion');
      if($promotion && $promotion->apply_to == "shipping"){
        $shippingWithDiscount = $this->getShippingCost() - $this->getDiscountAmount();
        $shippingWithDiscount = ($shippingWithDiscount < 0) ? 0 : $shippingWithDiscount;
        $total = $this->getNonSubscriptionAmount() + $shippingWithDiscount;
      }
      if($promotion && $promotion->apply_to == "total"){
        // nothing special to do here right now
        $total = $total - $promotion->getDiscountAmount();
      }
      if($promotion && $promotion->apply_to == "products"){
        $total = ($subTotal - $promotion->getDiscountAmount()) + $this->getShippingCost();
      }
      
        
    }
      
    
    $total = ($total < 0) ? 0 : $total;
    return $total; 
  }
  
  public function getFinalDiscountTotal(){
     $finalDiscountTotal = 0;
     $subTotal = $this->getSubTotal();
 
     $promotion = Cart66Session::get('Cart66Promotion');
     if($promotion && $promotion->apply_to == "shipping"){
       $finalDiscountTotal = $promotion->stayPositive($this->getShippingCost());
     }
     if($promotion && $promotion->apply_to == "total"){
       $finalDiscountTotal = $promotion->stayPositive($subTotal + $this->getShippingCost());
     }
     if($promotion && $promotion->apply_to == "products"){
       $finalDiscountTotal = $promotion->stayPositive($subTotal);
     }
     
     
     return $finalDiscountTotal;
   }
  
  public function storeOrder($orderInfo) {
    $order = new Cart66Order();
    $orderInfo['trans_id'] = (empty($orderInfo['trans_id'])) ? 'MT-' . Cart66Common::getRandString() : $orderInfo['trans_id'];
    $orderInfo['ip'] = $_SERVER['REMOTE_ADDR'];
    if(Cart66Session::get('Cart66Promotion')){
       $orderInfo['discount_amount'] = Cart66Session::get('Cart66Promotion')->getDiscountAmount(Cart66Session::get('Cart66Cart'));
    }
    else{
      $orderInfo['discount_amount'] = 0;
    }
    $order->setInfo($orderInfo);
    $order->setItems($this->getItems());
    $orderId = $order->save();
    //update the number of redemptions for the promotion code.
    if(Cart66Session::get('Cart66Promotion')) {
      Cart66Session::get('Cart66Promotion')->updateRedemptions();
    }
    $orderInfo['id'] = $orderId;
    do_action('cart66_after_order_saved', $orderInfo);
    
    return $orderId;
  }
  
  /**
   * Return true if all products are digital
   */
  public function isAllDigital() {
    $allDigital = true;
    foreach($this->getItems() as $item) {
      if(!$item->isDigital()) {
        $allDigital = false;
        break;
      }
    }
    return $allDigital;
  }
  
  public function hasMembershipProducts() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSubscriptionProducts() {
    foreach($this->getItems() as $item) {
      if($item->isSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return true if the cart only contains PayPal subscriptions
   */
  public function hasPayPalSubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSpreedlySubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return the spreedly subscription id for the subscription product in the cart 
   * or false if there are no spreedly subscriptions in the cart. With Spreedly 
   * subscriptions, there may be only one subscription product in the cart.
   */
  public function getSpreedlySubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return $item->getSpreedlySubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the CartItem that holds the membership product.
   * If there is no membership product in the cart, return false.
   * 
   * @return Cart66CartItem
   */
  public function getMembershipProductItem() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the membership product in the cart. 
   * Only one membership or subscription type item may be in the cart at any given time. 
   * Note that this function returns the actual Cart66Product not the Cart66CartItem.
   * If there is no membership product in the cart, return false.
   * 
   * @return Cart66Product
   */
  public function getMembershipProduct() {
    $product = false;
    if($item = $this->getMembershipProductItem()) {
      $product = new Cart66Product($item->getProductId());
    }
    return $product;
  }
  
  public function getPayPalSubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item->getPayPalSubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the Cart66CartItem with the PayPal subscription
   * 
   * @return Cart66CartItem
   */
  public function getPayPalSubscriptionItem() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the index in the cart of the PayPal subscription item.
   * This number is used to know the location of the item in the cart
   * when creating the payment profile with PayPal.
   * 
   * @return int
   */
  public function getPayPalSubscriptionIndex() {
    $index = 0;
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $index;
      }
      $index++;
    }
    return false;
  }
  
  /**
   * Return false if none of the items in the cart are shipped
   */
  public function requireShipping() {
    $ship = false;
    foreach($this->getItems() as $item) {
      if($item->isShipped()) {
        $ship = true;
        break;
      }
    }
    return $ship;
  }
  
  public function requirePayment() {
    $requirePayment = true;
    if($this->getGrandTotal() < 0.01) {
      // Look for free trial subscriptions that require billing
      if($subId = $this->getSpreedlySubscriptionId()) {
        $sub = new SpreedlySubscription($subId);
        if('free_trial' == strtolower((string)$sub->planType)) {
          $requirePayment = false;
        }
      }
    }
    return $requirePayment;
  }

  public function setShippingMethod($id) {
    $method = new Cart66ShippingMethod();
    if($method->load($id)) {
      $this->_shippingMethodId = $id;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set shipping method id to: $id");
    }
  }

  public function getShippingMethodId() {
    if($this->_shippingMethodId < 1) {
      $this->_setDefaultShippingMethodId();
    }
    return $this->_shippingMethodId;
  }

  public function getShippingMethodName() {
    // Look for live rates
    if(Cart66Session::get('Cart66LiveRates')) {
      $rate = Cart66Session::get('Cart66LiveRates')->getSelected();
      return $rate->service;
    }
    // Not using live rates
    else {
      if($this->isAllDigital()) {
        return 'Download';
      }
      elseif(!$this->requireShipping()) {
        return 'None';
      }
      else {
        if($this->_shippingMethodId < 1) {
          $this->_setDefaultShippingMethodId();
        }
        $method = new Cart66ShippingMethod($this->_shippingMethodId);
        return $method->name;
      }
    }
    
  }
  
  public function detachFormEntry($entryId) {
    foreach($this->_items as $index => $item) {
      $entries = $item->getFormEntryIds();
      if(in_array($entryId, $entries)) {
        $item->detachFormEntry($entryId);
        $qty = $item->getQuantity();
        if($qty == 0) {
          $this->removeItem($index);
        }
      }
    }
  }
  
  public function checkCartInventory() {
    $alert = '';
    foreach($this->_items as $itemIndex => $item) {
      if(!Cart66Product::confirmInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity())) {
        Cart66Common::log("Unable to confirm inventory when checking cart.");
        $qtyAvailable = Cart66Product::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
        if($qtyAvailable > 0) {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            we only have <strong>$qtyAvailable in stock</strong>.</p>";
        }
        else {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            it is <strong>out of stock</strong>.</p>";
        }
        
        if($qtyAvailable > 0) {
          $item->setQuantity($qtyAvailable);
        }
        else {
          $this->removeItem($itemIndex);
        }
        
      }
    }
    
    if(!empty($alert)) {
      $alert = "<div class='Cart66Unavailable'><h1>Inventory Restriction</h1> $alert <p>Your cart has been updated based on our available inventory.</p>";
      $alert .= '<input type="button" name="close" value="Ok" class="Cart66ButtonSecondary modalClose" /></div>';
    }
    
    return $alert;
  }
  
  public function getLiveRates() {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Call to getLiveRates");
    if(!CART66_PRO) { return false; }
    
    $weight = Cart66Session::get('Cart66Cart')->getCartWeight();
    $zip = Cart66Session::get('cart66_shipping_zip') ? Cart66Session::get('cart66_shipping_zip') : false;
    $countryCode = Cart66Session::get('cart66_shipping_country_code') ? Cart66Session::get('cart66_shipping_country_code') : Cart66Common::getHomeCountryCode();
    
    // Make sure _liveRates is a Cart66LiveRates object
    if(get_class($this->_liveRates) != 'Cart66LiveRates') {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] WARNING: \$this->_liveRates is not a Cart66LiveRates object so we're making it one now.");
      $this->_liveRates = new Cart66LiveRates();
    }
    
    // Return the live rates from the session if the zip, country code, and cart weight are the same
    if(Cart66Session::get('Cart66LiveRates') && get_class($this->_liveRates) == 'Cart66LiveRates') {
      $cartWeight = $this->getCartWeight();
      $this->_liveRates = Cart66Session::get('Cart66LiveRates');
      
      $liveWeight = $this->_liveRates->weight;
      $liveZip = $this->_liveRates->toZip;
      $liveCountry = $this->_liveRates->getToCountryCode();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] 
        $liveWeight == $weight
        $liveZip == $zip
        $liveCountry == $countryCode
      ");
      
      if($this->_liveRates->weight == $weight && $this->_liveRates->toZip == $zip && $this->_liveRates->getToCountryCode() == $countryCode) {
        Cart66Common::log("Using Live Rates from the session: " . $this->_liveRates->getSelected()->getService());
        return Cart66Session::get('Cart66LiveRates'); 
      }
    }

    if($this->getCartWeight() > 0 && Cart66Session::get('cart66_shipping_zip') && Cart66Session::get('cart66_shipping_country_code')) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Clearing current live shipping rates and recalculating new rates.");
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = $weight;
      $this->_liveRates->toZip = $zip;
      $method = new Cart66ShippingMethod();
      
      // Get USPS shipping rates
      if(Cart66Setting::getValue('usps_username')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = ($countryCode == 'US') ? $this->getUspsRates() : $this->getUspsIntlRates($countryCode);
        $uspsServices = $method->getServicesForCarrier('usps');
        foreach($rates as $name => $price) {
          $price = number_format($price, 2, '.', '');
          if(in_array($name, $uspsServices)) {
            $this->_liveRates->addRate('USPS', 'USPS ' . $name, $price);
          }
        }
      }

      // Get UPS Live Shipping Rates
      if(Cart66Setting::getValue('ups_apikey')) {
        $rates = $this->getUpsRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('UPS', $name, $price);
        }
      }
      
    }
    else {
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = 0;
      $this->_liveRates->toZip = $zip;
      $this->_liveRates->setToCountryCode($countryCode);
      $this->_liveRates->addRate('SYSTEM', 'Free Shipping', '0.00');
    }
    
    Cart66Session::set('Cart66LiveRates', $this->_liveRates);
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Dump live rates: " . print_r($this->_liveRates, true));
    return $this->_liveRates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUspsRates() {
    $usps = new Cart66Usps();
    $weight = $this->getCartWeight();
    $fromZip = Cart66Setting::getValue('usps_ship_from_zip');
    $toZip = Cart66Session::get('cart66_shipping_zip');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS: $fromZip > $toZip > $weight");
    $rates = $usps->getRates($fromZip, $toZip, $weight);
    return $rates;
  }
  
  public function getUspsIntlRates($countryCode) {
    $usps = new Cart66Usps();
    $weight = $this->getCartWeight();
    $value = $this->getSubTotal();
    $zipOrigin = Cart66Setting::getValue('usps_ship_from_zip');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS Intl: $zipOrigin > $countryCode > $value > $weight");
    $rates = $usps->getIntlRates($zipOrigin, $countryCode, $value, $weight);
    return $rates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUpsRates() {
    $ups = new Cart66Ups();
    $weight = Cart66Session::get('Cart66Cart')->getCartWeight();
    $zip = Cart66Session::get('cart66_shipping_zip');
    $countryCode = Cart66Session::get('cart66_shipping_country_code');
    $rates = $ups->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  protected function _setDefaultShippingMethodId() {
    // Set default shipping method to the cheapest method
    $method = new Cart66ShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    if(is_array($methods) && count($methods) && get_class($methods[0]) == 'Cart66ShippingMethod') {
      $this->_shippingMethodId = $methods[0]->id;
    }
  }
  
  protected function _setShippingMethodFromPost() {
    // Not using live rates
    if(isset($_POST['shipping_method_id'])) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not using live shipping rates");
      $shippingMethodId = $_POST['shipping_method_id'];
      $this->setShippingMethod($shippingMethodId);
    }
    // Using live rates
    elseif(isset($_POST['live_rates'])) {
      if(Cart66Session::get('Cart66LiveRates')) {
        Cart66Session::get('Cart66LiveRates')->setSelected($_POST['live_rates']);
        // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This LIVE RATE is now set: " . Cart66Session::get('Cart66LiveRates')->getSelected()->getService());
        // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using live shipping rates to set shipping method from post: " . $_POST['live_rates']);
      }
    }
  }
  
  protected function _updateQuantitiesFromPost() {
    $qtys = Cart66Common::postVal('quantity');
    if(is_array($qtys)) {
      foreach($qtys as $itemIndex => $qty) {
        $item = $this->getItem($itemIndex);
        if(!is_null($item) && get_class($item) == 'Cart66CartItem') {
          
          if($qty == 0){
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Customer specified quantity of 0 - remove item.");
            $this->removeItem($itemIndex);
          }
          
          if(Cart66Product::confirmInventory($item->getProductId(), $item->getOptionInfo(), $qty)) {
            $this->setItemQuantity($itemIndex, $qty);
          }
          else {
            $qtyAvailable = Cart66Product::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
            $this->setItemQuantity($itemIndex, $qtyAvailable);
            if(!Cart66Session::get('Cart66InventoryWarning')) { Cart66Session::set('Cart66InventoryWarning', ''); }
            $inventoryWarning = Cart66Session::get('Cart66InventoryWarning');
            $inventoryWarning .= '<p>The quantity for ' . $item->getFullDisplayName() . " could not be changed to $qty because we only have $qtyAvailable in stock.</p>";
            Cart66Session::set('Cart66InventoryWarning', $inventoryWarning);
            Cart66Common::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($qty) for product id: " . $item->getProductId());
          }
        }
      }
    }
  }
  
  protected function _setCustomFieldInfoFromPost() {
    // Set custom values for individual products in the cart
    $custom = Cart66Common::postVal('customFieldInfo');
    if(is_array($custom)) {
      foreach($custom as $itemIndex => $info) {
        $this->setCustomFieldInfo($itemIndex, $info);
      }
    }
  }
  
  protected function _setPromoFromPost() {
    if(isset($_POST['couponCode']) && $_POST['couponCode'] != '') {
      $couponCode = Cart66Common::postVal('couponCode');
      $this->applyPromotion($couponCode);
    }
    else{
      if(Cart66Session::get('Cart66Promotion')){
        $currentPromotionCode = Cart66Session::get('Cart66PromotionCode');
        $this->applyPromotion($currentPromotionCode);
      }
      else {
        $this->clearPromotion();
        $this->_setAutoPromoFromPost();
      }
    }
    
  }
  
  //applies coupon codes that are set to Auto Apply
  protected function _setAutoPromoFromPost() {    
    $promotion = new Cart66Promotion();
    $promotions = $promotion->getAutoApplyPromotions();
    foreach($promotions as $promo){
      if($promo->validateCustomerPromotion($promo->getCodeAt())) {  
        $this->applyPromotion($promo->getCodeAt(), true);
      }
    }
  }
   
  /**
   * Return a stdClass object with the price difference and a CSV list of options.
   *   $optionResult->priceDiff
   *   $optionResult->options
   * @return object
   */
  protected function _processOptionInfo($optionInfo) {
    $optionInfo = trim($optionInfo);
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with option: $opt");
      if(preg_match('/\+\s*\$/', $opt)) {
        $opt = preg_replace('/\+\s*\$/', '+$', $opt);
        list($opt, $pd) = explode('+$', $opt);
        $optionList[] = trim($opt);
        $priceDiff += $pd;
      }
      elseif(preg_match('/-\s*\$/', $opt)) {
        $opt = preg_replace('/-\s*\$/', '-$', $opt);
        list($opt, $pd) = explode('-$', $opt);
        $optionList[] = trim($opt);
        $pd = trim($pd);
        $priceDiff -= $pd;
      }
      else {
        $optionList[] = trim($opt);
      }
    }
    $optionResult = new stdClass();
    $optionResult->priceDiff = $priceDiff;
    $optionResult->options = implode(', ', $optionList);
    return $optionResult;
  }
  
}
