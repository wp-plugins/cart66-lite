<?php
class Cart66Exporter {
  
  public static function exportOrders($startDate, $endDate) {
    global $wpdb;
    $start = date('Y-m-d 00:00:00', strtotime($startDate));
    $end = date('Y-m-d 00:00:00', strtotime($endDate . ' + 1 day'));
    
    $orders = Cart66Common::getTableName('orders');
    $items = Cart66Common::getTableName('order_items');
    
    $orderHeaders = array(
      'id' => __('Order ID', 'cart66'),
      'trans_id' => __('Order Number', 'cart66'),
      'ordered_on' => __('Date', 'cart66'),
      'bill_first_name' => __('Billing First Name', 'cart66'),
      'bill_last_name' => __('Billing Last Name', 'cart66'),
      'bill_address' => __('Billing Address', 'cart66'),
      'bill_address2' => __('Billing Address 2', 'cart66'),
      'bill_city' => __('Billing City', 'cart66'),
      'bill_state' => __('Billing State', 'cart66'),
      'bill_country' => __('Billing Country', 'cart66'),
      'bill_zip' => __('Billing Zip Code', 'cart66'),
      'ship_first_name' => __('Shipping First Name', 'cart66'),
      'ship_last_name' => __('Shipping Last Name', 'cart66'),
      'ship_address' => __('Shipping Address', 'cart66'),
      'ship_address2' => __('Shipping Address 2', 'cart66'),
      'ship_city' => __('Shipping City', 'cart66'),
      'ship_state' => __('Shipping State', 'cart66'),
      'ship_country' => __('Shipping Country', 'cart66'),
      'ship_zip' => __('Shipping Zip Code', 'cart66'),
      'phone' => __('Phone', 'cart66'),
      'email' => __('Email', 'cart66'),
      'coupon' => __('Coupon', 'cart66'),
      'discount_amount' => __('Discount Amount', 'cart66'),
      'shipping' => __('Shipping Cost', 'cart66'),
      'subtotal' => __('Subtotal', 'cart66'),
      'tax' => __('Tax', 'cart66'),
      'total' => __('Total', 'cart66'),
      'ip' => __('IP Address', 'cart66'),
      'shipping_method' => __('Delivery Method', 'cart66'),
      'status' => __('Order Status', 'cart66')
    );
    
    $orderColHeaders = implode(',', $orderHeaders);
    $orderColSql = implode(',', array_keys($orderHeaders));
    $out  = $orderColHeaders . ",Form Data,Item Number,Description,Quantity,Product Price,Form ID\n";
    
    $sql = "SELECT $orderColSql from $orders where ordered_on >= %s AND ordered_on < %s AND status != %s order by ordered_on";
    $sql = $wpdb->prepare($sql, $start, $end, 'checkout_pending');
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL: $sql");
    $selectedOrders = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($selectedOrders as $o) {
      $itemRowPrefix = '"' . $o['id'] . '","' . $o['trans_id'] . '",' . str_repeat(',', count($o)-3);
      $orderId = $o['id'];
      $sql = "SELECT form_entry_ids, item_number, description, quantity, product_price FROM $items where order_id = $orderId";
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item query: $sql");
      $selectedItems = $wpdb->get_results($sql, ARRAY_A);
      $out .= '"' . implode('","', $o) . '"';
      $printItemRowPrefix = false;
      if(!empty($selectedItems)) {
        foreach($selectedItems as $i) {
          if($printItemRowPrefix) {
            $out .= $itemRowPrefix;
          }

          if($i['form_entry_ids'] && CART66_PRO){
            $i['form_id'] = $i['form_entry_ids'];
            $GReader = new Cart66GravityReader();
            $i['form_entry_ids'] = $GReader->displayGravityForm($i['form_entry_ids'],true);
            $i['form_entry_ids'] = str_replace("\"","''",$i['form_entry_ids']);
          }

          $i['description'] = str_replace(","," -",$i['description']);

          $out .= ',"' . implode('","', $i) . '"';
          $out .= "\n";
          $printItemRowPrefix = true;
        }
      }
      else {
        $out .= "\n";
      }
      
    }
    
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Report\n$out");
    return $out;
  }
  
}