<?php
class Cart66Order extends Cart66ModelAbstract {
  
  protected $_orderInfo = array();
  protected $_items = array();
  
  public function __construct($id=null) {
    $this->_tableName = Cart66Common::getTableName('orders');
    parent::__construct($id);
  }
  
  public function loadByOuid($ouid) {
    $sql = $this->_db->prepare("SELECT id from $this->_tableName where ouid=%s", $ouid);
    $id = $this->_db->get_var($sql);
    $this->load($id);
  }
  
  public function setInfo(array $info) {
    $this->_orderInfo = $info;
  }
  
  public function setItems(array $items) {
    $this->_items = $items;
  }
  
  public function save() {
    $this->_orderInfo['ouid'] = md5($this->_orderInfo['trans_id'] . $this->_orderInfo['bill_address']);
    Cart66Common::log('order.php:' . __LINE__ . ' - Saving Order Information (Items: ' . count($this->_items). '): ' . print_r($this->_orderInfo, true));
    $this->_db->insert($this->_tableName, $this->_orderInfo);
    $this->id = $this->_db->insert_id;
    $key = $this->_orderInfo['trans_id'] . '-' . $this->id . '-';
    foreach($this->_items as $item) {
      
      // Deduct from inventory
      Cart66Product::decrementInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity());
      
      $data = array(
        'order_id' => $this->id,
        'product_id' => $item->getProductId(),
        'product_price' => $item->getProductPrice(),
        'item_number' => $item->getItemNumber(),
        'description' => $item->getFullDisplayName(),
        'quantity' => $item->getQuantity(),
        'duid' => md5($key . $item->getProductId())
      );
      
      $formEntryIds = '';
      $fIds = $item->getFormEntryIds();
      if(is_array($fIds) && count($fIds)) {
        $formEntryIds = implode(',', $fIds);
      }
      $data['form_entry_ids'] = $formEntryIds;
      
      if($item->getCustomFieldInfo()) {
        $data['description'] .= "\n" . $item->getCustomFieldDesc() . ":\n" . $item->getCustomFieldInfo();
      }
      
      $orderItems = Cart66Common::getTableName('order_items');
      $this->_db->insert($orderItems, $data);
      $orderItemId = $this->_db->insert_id;
      Cart66Common::log("Saved order item ($orderItemId): " . $data['description'] . "\nSQL: " . $this->_db->last_query);
    }
    return $this->id;
  }
  
  public function getOrderRows($where=null, $limit=null) {
    if(!empty($where)) {
      $sql = "SELECT * from $this->_tableName $where order by ordered_on desc";
    }
    else {
      $sql = "SELECT * from $this->_tableName order by ordered_on desc";
    }
    
    if(!empty($limit)){
      $sql = $sql . " LIMIT $limit";
    }
    
    $orders = $this->_db->get_results($sql);
    return $orders;
  }
  
  public function getItems() {
    $orderItems = Cart66Common::getTableName('order_items');
    $sql = "SELECT * from $orderItems where order_id = $this->id order by product_price desc";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  
  public function updateStatus($status) {
    if($this->id > 0) {
      $data['status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  
  public function updateViewed() {
    global $post;
    $receiptPage = get_page_by_path('store/receipt');
    if( isset( $post->ID ) && $post->ID == $receiptPage->ID) {
      $order = new Cart66Order();
      if(isset($_GET['ouid'])) {
        $order->loadByOuid($_GET['ouid']);
        $data['viewed'] = '1';
        if($order->viewed == 0) {
          $this->_db->update($this->_tableName, $data, array('id' => $order->id), array('%s'));
        }
      }
    }
    return false;
  }
  
  public function addTrackingCode() {
    if(Cart66Setting::getValue('enable_google_analytics') && is_home()) {
      echo '<script type="text/javascript">
        /* <![CDATA[ */
        var _gaq = _gaq || [];
        _gaq.push([\'_setAccount\', \'' . Cart66Setting::getValue('google_analytics_wpid') . '\']);
        _gaq.push([\'_trackPageview\']);

        (function() {
          var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
          ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
          var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
        })();
      /* ]]> */
      </script>hello world';
    }
    return false;
  }
  
  public function deleteMe() {
    if($this->id > 0) {
      
      // Delete attached Gravity Forms if they exist
      $items = $this->getItems();
      foreach($items as $item) {
        if(!empty($item->form_entry_ids)) {
          $entryIds = explode(',', $item->form_entry_ids);
          if(is_array($entryIds)) {
            foreach($entryIds as $entryId) {
              RGFormsModel::delete_lead($entryId);
            }
          } 
        }
      }
      
      // Delete order items
      $orderItems = Cart66Common::getTableName('order_items');
      $sql = "DELETE from $orderItems where order_id = $this->id";
      $this->_db->query($sql);
      
      // Delete the order
      $sql = "DELETE from $this->_tableName where id = $this->id";
      $this->_db->query($sql);
    }
  }
  
  public function hasShippingInfo() {
    return strlen(trim($this->ship_first_name) . trim($this->ship_last_name) . trim($this->ship_address)) > 0;
  }
}
