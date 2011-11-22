<?php
  global $wpdb;
  $order = new Cart66Order();
  $orderRows = $order->getOrderRows();
  $search = null;
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['cart66-task']) && $_POST['cart66-task'] == 'search orders') {
      $search = $wpdb->escape($_POST['search']) . '%';
      $where = "WHERE ship_last_name LIKE '$search' OR bill_last_name LIKE '$search' OR email LIKE '$search' or trans_id LIKE '$search'";
      $orderRows = $order->getOrderRows($where);
    }
  }
  else {
    if(isset($_GET['status'])) {
      $status = $wpdb->escape($_GET['status']);
      $orderRows = $order->getOrderRows("WHERE status='$status'");
    }
  }
  
  
?>
<h2>Cart66 Orders</h2>

<div class='wrap'>
  <form class='phorm' action="" method="post">
    <input type='hidden' name='cart66-task' value='search orders'/>
    <input type='text' name='search'>
    <input type='submit' class='button-secondary' value='Search' style='width: auto;'>
    <br/>
    <p style="float: left; color: #999; font-size: 11px; margin-top: 0;"><?php _e( 'Search by last name, email, or order number' , 'cart66' ); ?></p>
  </form>
  
  <?php
    $setting = new Cart66Setting();
    $stats = trim(Cart66Setting::getValue('status_options'));
    if(strlen($stats) >= 1 ) {
      $stats = explode(',', $stats);
  ?>
      <p style="float: left; clear: both; margin-top:0; padding-top: 0;"><?php _e( 'Filter Orders' , 'cart66' ); ?>:
        <?php
          foreach($stats as $s) {
            $s = trim($s);
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order status query: WHERE status='$s'");
            $tmpRows = $order->getOrderRows("WHERE status='$s'");
            $n = count($tmpRows);
            if($n > 0) {
              $url = Cart66Common::replaceQueryString("page=cart66_admin&status=$s");
              echo "<a href=\"$url\">$s (" . count($tmpRows) . ")</a> &nbsp;|&nbsp; ";
            }
            else {
              echo "$s (0) &nbsp;|&nbsp;";
            }
          }
        ?>
        <a href="?page=cart66_admin">All (<?php echo count($order->getOrderRows()) ?>)</a>
      </p>
  <?php
    }
    else {
      echo "<p style=\"float: left; clear: both; color: #999; font-size: 11px; both; margin-top:0; padding-top: 0;\">" .
        __("You should consider setting order status options such as new and complete on the 
        <a href='?page=cart66-settings'>Cart66 Settings page</a>.","cart66") . "</p>";
    }
  
  ?>
  
  <?php if(isset($search)): ?>
    <p style='float:left; clear: both;'><strong><?php _e( 'Search String' , 'cart66' ); ?>:</strong> <?php echo Cart66Common::postVal('search'); ?></p>
  <?php endif; ?>
</div>

<table class="widefat Cart66HighlightTable" style="width: auto;">
<thead>
  <tr>
    <th colspan="8"><?php _e( 'Search' , 'cart66' ); ?>: <input type="text" name="Cart66AccountSearchField" value="" id="Cart66AccountSearchField" /></th>
  </tr>
	<tr>
	  <th><?php _e( 'Order Number' , 'cart66' ); ?></th>
		<th><?php _e( 'Name' , 'cart66' ); ?></th>
		<th><?php _e( 'Amount' , 'cart66' ); ?></th>
		<th><?php _e( 'Date' , 'cart66' ); ?></th>
    <th><?php _e( 'Delivery' , 'cart66' ); ?></th>
		<th><?php _e( 'Status' , 'cart66' ); ?></th>
		<th><?php _e( 'Actions' , 'cart66' ); ?></th>
	</tr>
</thead>
<?php

foreach($orderRows as $row) {
  ?>
  <tr>
    <td><?php echo $row->trans_id ?></td>
    <td><?php echo $row->bill_first_name ?> <?php echo $row->bill_last_name?></td>
    <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $row->total ?></td>
    <td><?php echo date('m/d/Y', strtotime($row->ordered_on)) ?></td>
    <td><?php echo $row->shipping_method ?></td>
    <td><?php echo $row->status ?></td>
    <td>
      <a href='?page=cart66_admin&task=view&id=<?php echo $row->id ?>'><?php _e( 'View' , 'cart66' ); ?></a> | 
      <a class='delete' href='?page=cart66_admin&task=delete&id=<?php echo $row->id ?>'><?php _e( 'Delete' , 'cart66' ); ?></a>
    </td>
    
  </tr>
  <?php
}
?>
</table>

<script type="text/javascript">
  (function($){
    $('.delete').click(function() {
      return confirm('Are you sure you want to delete this item?');
    });

    $('#Cart66AccountSearchField').quicksearch('table tbody tr');
  })(jQuery);
</script> 
