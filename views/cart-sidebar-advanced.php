<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="Cart66WidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>  

  <?php if($data['numItems']): ?> 
      <p id="Cart66WidgetCartEmpty">
        <?php _e( 'You have' , 'cart66' ); ?> <?php echo $data['numItems']; ?> 
        <?php echo $data['numItems'] > 1 ? ' items' : ' item' ?> 
        (<?php echo CART66_CURRENCY_SYMBOL . number_format($data['cartWidget']->getSubTotal() - $data['cartWidget']->getDiscountAmount(), 2); ?>) <?php _e( 'in your shopping cart' , 'cart66' ); ?>.
      </p>
      <?php 
        $items = $data['items'];
        $product = new Cart66Product();
        $subtotal = Cart66Session::get('Cart66Cart')->getSubTotal();
        $shippingMethods = Cart66Session::get('Cart66Cart')->getShippingMethods();
        $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
 
        $tax = 0;
          if(isset($data['tax']) && $data['tax'] > 0) {
            $tax = $data['tax'];
          }
          else {
            // Check to see if all sales are taxed
            $tax = Cart66Session::get('Cart66Cart')->getTax('All Sales');
        }
      ?>
          <form id='Cart66WidgetCartForm' action="" method="post">
            <input type='hidden' name='task' value='updateCart' />
        <table id='Cart66AdvancedWidgetCartTable' class="Cart66AdvancedWidgetCartTable">
          <?php foreach($items as $itemIndex => $item): ?>
            <?php 
            $product->load($item->getProductId());
            $productPrice = $item->getProductPrice();
            $productSubtotal = $item->getProductPrice() * $item->getQuantity();
            ?>
            <tr>
              <td>
                <span class="Cart66ProductTitle"><?php echo $item->getFullDisplayName(); ?></span>
                <span class="Cart66QuanPrice">
                    <span class="Cart66ProductQuantity"><?php echo $item->getQuantity() ?></span> 
                    <span class="Cart66MetaSep">x</span> 
                    <span class="Cart66CurSymbol"><?php echo CART66_CURRENCY_SYMBOL ?></span>
                    <span class="Cart66ProductPrice"><?php echo number_format($productPrice, 2) ?></span>
                </span>
              </td>
              <td class="Cart66ProductSubtotalColumn">
                <span class="Cart66ProductSubtotal"><?php echo number_format($productSubtotal, 2) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        <tr class="Cart66SubtotalRow">
            <td colspan="2"><span class="Cart66CartSubTotalLabel"><?php _e( 'Subtotal' , 'cart66' ); ?></span><span class="Cart66MetaSep">: </span>
                <span class="Cart66CurSymbol"><?php echo CART66_CURRENCY_SYMBOL ?></span><span class="Cart66Subtotal"><?php echo number_format($subtotal, 2); ?></span>
            </td>
        </tr>
        
        <?php if(isset($data['shipping'] ) && $data['shipping'] == true): ?>
              <?php if(Cart66Session::get('Cart66Cart')->requireShipping()): ?>

                <?php if(CART66_PRO && Cart66Setting::getValue('use_live_rates')): ?>
                  <?php $zipStyle = "style=''"; ?>

                    <?php if(Cart66Session::get('cart66_shipping_zip')): ?>
                      <?php $zipStyle = "style='display: none;'"; ?>
                      <tr class="Cart66ShippingToRow">
                        <th colspan="2">
                          <?php _e( 'Shipping to' , 'cart66' ); ?> <?php echo Cart66Session::get('cart66_shipping_zip'); ?> 
                          <?php
                            if(Cart66Setting::getValue('international_sales')) {
                              echo Cart66Session::get('cart66_shipping_country_code');
                            }
                          ?>
                          (<a href="#" id="widget_change_shipping_zip_link"><?php _e( 'change' , 'cart66' ); ?></a>)
                          &nbsp;
                          <?php
                            $liveRates = Cart66Session::get('Cart66Cart')->getLiveRates();
                            $rates = $liveRates->getRates();
                            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                            $selectedRate = $liveRates->getSelected();
                            $shipping = Cart66Session::get('Cart66Cart')->getShippingCost();
                          ?>
                          <select name="live_rates" id="widget_live_rates">
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

                    <tr id="widget_set_shipping_zip_row" <?php echo $zipStyle; ?>>
                      <th colspan="2"><?php _e( 'Enter Your Zip Code' , 'cart66' ); ?>:
                        <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />

                        <?php if(Cart66Setting::getValue('international_sales')): ?>
                          <select name="shipping_country_code" class="Cart66CountrySelect">
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

                        <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="Cart66ButtonSecondaryWidget" />
                      </th>
                    </tr>

                <?php  else: ?>
                  <?php if(count($shippingMethods) > 1): ?>
                    <tr>
                      <th colspan="2"><?php _e( 'Shipping Method' , 'cart66' ); ?><span class="Cart66MetaSep">: </span> 
                        <select name='shipping_method_id' id='widget_shipping_method_id' class="Cart66ShippingMethodSelect">
                          <?php foreach($shippingMethods as $name => $id): ?>
                            <option value='<?php echo $id ?>' 
                            <?php echo ($id == Cart66Session::get('Cart66Cart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
                          <?php endforeach; ?>
                        </select>
                      </th>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>
            <?php endif; ?>
              
          <?php if($tax > 0): ?>
            <tr class="tax">
              <td colspan="2"><?php _e( 'Tax' , 'cart66' ); ?><span class="Cart66MetaSep">:</span>
              <span class="Cart66CurSymbol"><?php echo CART66_CURRENCY_SYMBOL ?></span><span class="Cart66TaxCost"><?php echo number_format($tax, 2); ?></span></td>
            </tr>
          <?php endif; ?>
        
        
      </table>
      </form>
      <div class="Cart66WidgetViewCartCheckout">
        <a class="Cart66WidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>View Cart</a> | <a class="Cart66WidgetViewCheckout" href='<?php echo get_permalink($data['checkoutPage']->ID) ?>'>Checkout</a>
      </div>
  <?php else: ?>
        <div class="Cart66WidgetViewCartCheckout">
            <p class="Cart66WidgetCartEmpty">You have <?php echo $data['numItems']; ?> <?php _e( 'items in your shopping cart' , 'cart66' ); ?>.
            <a class="Cart66WidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'View Cart' , 'cart66' ); ?></a>
            </p>
        </div>
  <?php endif; ?>

  <script type="text/javascript">
  /* <![CDATA[ */
    (function($){
      $(document).ready(function(){
        $('#widget_shipping_method_id').change(function() {
          $('#Cart66WidgetCartForm').submit();
        });

        $('#widget_live_rates').change(function() {
          $('#Cart66WidgetCartForm').submit();
        });

        $('#widget_change_shipping_zip_link').click(function() {
          $('#widget_set_shipping_zip_row').toggle();
          return false;
        });
      })
    })(jQuery);
  /* ]]> */
  </script>

<?php echo $data['afterWidget']; ?>