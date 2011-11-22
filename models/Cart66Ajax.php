<?php
class Cart66Ajax {
  
  public static function promotionProductSearch() {
    global $wpdb;
    $search = Cart66Common::getVal('q');
    $product = new Cart66Product();
    $tableName = Cart66Common::getTableName('products');
    $products = $wpdb->get_results("SELECT id, name from $tableName WHERE name LIKE '%%%$search%%' ORDER BY id ASC LIMIT 10");
    $data = array();
    foreach($products as $p) {
      $data[] = array('id' => $p->id, 'name' => $p->name);
    }
    echo json_encode($data);
    die();
  }
  
  public static function loadPromotionProducts() {
    $productId = Cart66Common::postVal('productId');
    $product = new Cart66Product();
    $ids = explode(',', $productId);
    $selected = array();
    foreach($ids as $id) {
      $product->load($id);
      $selected[] = array('id' => $id, 'name' => $product->name);
    }
    echo json_encode($selected);
    die();
  }
  
  public static function saveSettings() {
    $error = '';
    foreach($_REQUEST as $key => $value) {
      if($key[0] != '_' && $key != 'action' && $key != 'submit') {
        if(is_array($value) && $key != 'admin_page_roles') {
          $value = implode('~', $value);
        }

        if($key == 'home_country') {
          $hc = Cart66Setting::getValue('home_country');
          if($hc != $value) {
            $method = new Cart66ShippingMethod();
            $method->clearAllLiveRates();
          }
        }
        elseif($key == 'countries') {
          if(strpos($value, '~') === false) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] country list value: $value");
            $value = '';
          }
          if(empty($value) && !empty($_REQUEST['international_sales'])){
            $error = "Please select at least one country to ship to.";
          }
        }
        elseif($key == 'enable_logging' && $value == '1') {
          try {
            Cart66Log::createLogFile();
          }
          catch(Cart66Exception $e) {
            $error = '<span style="color: red;">' . $e->getMessage() . '</span>';
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught Cart66 exception: " . $e->getMessage());
          }
        }
        elseif($key == 'constantcontact_list_ids') {
          
        }
        elseif($key == 'admin_page_roles') {
          $value = serialize($value);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Admin Page Roles: " . print_r($value,true));
        }

        Cart66Setting::setValue($key, trim(stripslashes($value)));

        if(CART66_PRO && $key == 'order_number') {
          $versionInfo = Cart66ProCommon::getVersionInfo();
          if(!$versionInfo) {
            Cart66Setting::setValue('order_number', '');
            $error = '<span style="color: red;">' . __( 'Invalid Order Number' , 'cart66' ) . '</span>';
          }
        }
      }
    }

    if($error) {
      $result[0] = 'Cart66ErrorModal';
      $result[1] = "<strong style='color: red;'>" . __("Warning","cart66") . "</strong><br/>$error";
    }
    else {
      $result[0] = 'Cart66SuccessModal';
      $result[1] = '<strong>Success</strong><br/>' . $_REQUEST['_success'] . '<br>'; 
    }

    $out = json_encode($result);
    echo $out;
    die();
  }
  
  public static function updateGravityProductQuantityField() {
    $formId = Cart66Common::getVal('formId');
    $gr = new Cart66GravityReader($formId);
    $fields = $gr->getStandardFields();
    header('Content-type: application/json');
    echo json_encode($fields);
    die();
  }
  
  function checkInventoryOnAddToCart() {
    $result = array(true);
    $itemId = Cart66Common::postVal('cart66ItemId');
    $options = '';
    $optionsMsg = '';

    $opt1 = Cart66Common::postVal('options_1');
    $opt2 = Cart66Common::postVal('options_2');

    if(!empty($opt1)) {
      $options = $opt1;
      $optionsMsg = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt1));
    }
    if(!empty($opt2)) {
      $options .= '~' . $opt2;
      $optionsMsg .= ', ' . trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt2));
    }

    $scrubbedOptions = Cart66Product::scrubVaritationsForIkey($options);
    if(!Cart66Product::confirmInventory($itemId, $scrubbedOptions)) {
      $result[0] = false;
      $p = new Cart66Product($itemId);

      $counts = $p->getInventoryNamesAndCounts();
      $out = '';

      if(count($counts)) {
        $out = '<table class="inventoryCountTableModal">';
        $out .= '<tr><td colspan="2"><strong>Currently In Stock</strong></td></tr>';
        foreach($counts as $name => $qty) {
          $out .= '<tr>';
          $out .= "<td>$name</td><td>$qty</td>";
          $out .= '</tr>';
        }
        $out .= '</table>';
      }

      $result[1] = $p->name . " " . $optionsMsg . " is&nbsp;out&nbsp;of&nbsp;stock $out";
    }

    $result = json_encode($result);
    echo $result;
    die();
  }
  
}