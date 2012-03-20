<h2>Cart66 Inventory Tracking</h2>

<?php
// Get a list of all products
$product = new Cart66Product();
$products = $product->getModels('where id>0', 'order by name', '1');
$save = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-task'] == 'save-inventory-form') {
  $save = true;
  
  $product->updateInventoryFromPost($_REQUEST);
  
  ?>
  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $("#Cart66SuccessBox").show().delay(1000).fadeOut('slow'); 
      })
    })(jQuery);
  </script> 
  <div id='Cart66SuccessBox' style='width: 300px;'><p class='Cart66Success'><?php _e( 'Inventory updated' , 'cart66' ); ?></p></div>
  <?php
}

$setting = new Cart66Setting();
$track = Cart66Setting::getValue('track_inventory');
$wpurl = get_bloginfo('wpurl');

if(CART66_PRO) {
  require_once(CART66_PATH . "/pro/admin/inventory.php");
}
else {
    echo '<p class="description">' . __("Account functionality is only available in <a href='http://cart66.com'>Cart66 Professional</a>","cart66") . '</p>';
}