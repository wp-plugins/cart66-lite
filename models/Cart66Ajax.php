<?php
class Cart66Ajax {

  public static function resendEmailFromLog() {
    $log_id = $_POST['id'];
    $resendEmail = Cart66EmailLog::resendEmailFromLog($log_id);
    if($resendEmail) {
      $result[0] = 'Cart66Modal alert-message success';
      $result[1] = '<strong>Success</strong><br/>' . __('Email successfully resent', 'cart66') . ' <br />';
    }
    else {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Email was not resent Successfully', 'cart66') . '<br>';
    }
    echo json_encode($result);
    die();
  }

  public function forcePluginUpdate(){
    $output = false;
    update_option('_site_transient_update_plugins', '');
    update_option('_transient_update_plugins', '');
    delete_transient('_cart66_version_request');
    $output = true;
    echo $output;
    die();
  }

  public static function sendTestEmail() {
    $to = $_POST['email'];
    $status = $_POST['status'];
    if(!Cart66Common::isValidEmail($to)) {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = '<strong>Error</strong><br/>' . __('Please enter a valid email address', 'cart66') . '<br>';
    }
    else {
      if(isset($_GET['type']) && $_GET['type'] == 'reminder') {
        $sendEmail = Cart66MembershipReminders::sendTestReminderEmails($to, $_GET['id']);
      }
      else {
        $sendEmail = Cart66AdvancedNotifications::sendTestEmail($to, $status);
      }
      if($sendEmail) {
        $result[0] = 'Cart66Modal alert-message success';
        $result[1] = '<strong>Success</strong><br/>' . __('Email successfully sent to', 'cart66') . ' <br /><strong>' . $to . '</strong><br>';
      }
      else {
        $result[0] = 'Cart66Modal alert-message alert-error';
        $result[1] = '<strong>Error</strong><br/>' . __('Email not sent. There is an unknown error.', 'cart66') . '<br>';
      }
    }
    echo json_encode($result);
    die();
  }

  public static function ajaxReceipt() {
    if(isset($_GET['order_id'])) {
      $orderReceipt = new Cart66Order($_GET['order_id']);
      $printView = Cart66Common::getView('views/receipt_print_version.php', array('order' => $orderReceipt));
      $printView = str_replace("\n", '', $printView);
      $printView = str_replace("'", '"', $printView);
      echo $printView;
      die();
    }
  }

  public static function ajaxOrderLookUp() {
    $redirect = true;
    $order = new Cart66Order();
    $order->loadByOuid($_POST['ouid']);
    if(!Cart66Session::get('Cart66PendingOUID')) {
      Cart66Session::set('Cart66PendingOUID', $_POST['ouid']);
    }
    if(empty($order->id) || $order->status == 'checkout_pending') {
      $redirect = false;
    }
    echo json_encode($redirect);
    die();
  }

  public static function viewLoggedEmail() {
    if(isset($_POST['log_id'])) {
      $emailLog = new Cart66EmailLog($_POST['log_id']);
      echo nl2br(htmlentities($emailLog->headers . "\r\n" . $emailLog->body, ENT_COMPAT, 'UTF-8'));
      die();
    }
  }

  public static function checkPages(){
    $Cart66 = new Cart66();
    echo $Cart66->cart66_page_check(true);
    die();
  }

  public static function shortcodeProductsTable() {
    global $wpdb;
    $prices = array();
  	$types = array();
  	//$options='';
    $postId = intval(Cart66Common::postVal('id'));
    $product = new Cart66Product();
    $products = $product->getModels("where id=$postId", "order by name");
    $data = array();
    foreach($products as $p) {
      if($p->itemNumber==""){
        $type='id';
      }
      else{
        $type='item';
      }

  	  $types[] = htmlspecialchars($type);

  	  if(CART66_PRO && $p->isPayPalSubscription()) {
  	    $sub = new Cart66PayPalSubscription($p->id);
  	    $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
  	    $prices[] = htmlspecialchars($subPrice);
  	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
  	  }
  	  else {
  	    $prices[] = htmlspecialchars(strip_tags($p->getPriceDescription()));
  	  }


  	  //$options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
      $data[] = array('type' => $types, 'price' => $prices, 'item' => $p->itemNumber);
    }
    echo json_encode($data);
    die();
  }

  public static function ajaxTaxUpdate() {
    if(isset($_POST['state']) && isset($_POST['state_text']) && isset($_POST['zip']) && isset($_POST['gateway'])) {
      $gateway = Cart66Ajax::loadAjaxGateway($_POST['gateway']);
      $gateway->setShipping(array('state_text' => $_POST['state_text'], 'state' => $_POST['state'], 'zip' => $_POST['zip']));
      $s = $gateway->getShipping();
      if($s['state'] && $s['zip']){
        $id = 1;
        $taxLocation = $gateway->getTaxLocation();
        $tax = $gateway->getTaxAmount();
        $rate = $gateway->getTaxRate();
        $total = Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax;
        Cart66Session::set('Cart66Tax', $tax);
        Cart66Session::set('Cart66TaxRate', Cart66Common::tax($rate));
      }
      else {
        $id = 0;
        $tax = 0;
        $rate = 0;
        $total = Cart66Session::get('Cart66Cart')->getGrandTotal() + $tax;
        Cart66Session::set('Cart66Tax', $tax);
        Cart66Session::set('Cart66TaxRate', Cart66Common::tax($rate));
      }
      if(Cart66Session::get('Cart66Cart')->getTax('All Sales')) {
        $rate = $gateway->getTaxRate();
        Cart66Session::set('Cart66TaxRate', Cart66Common::tax($rate));
      }
    }
    $result = array(
      'id' => $id,
      'state' => $s['state'],
      'zip' => $s['zip'],
      'tax' => Cart66Common::currency($tax),
      'rate' => $rate == 0 ? '0.00%' : Cart66Common::tax($rate),
      'total' => Cart66Common::currency($total)
    );
    echo json_encode($result);
    die();
  }

  public static function loadAjaxGateway($gateway) {
    switch($gateway) {
      case 'Cart66ManualGateway':
        require_once(CART66_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart662Checkout':
        require_once(CART66_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66AuthorizeNet':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Eway':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Mijireh':
        require_once(CART66_PATH . "/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66MWarrior':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66PayLeap':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66PayPalPro':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      case 'Cart66Stripe':
        require_once(CART66_PATH . "/pro/gateways/$gateway.php");
        $gateway = new $gateway();
        break;
      default:
        break;
    }
    return $gateway;
  }

  public static function ajaxCartElements($args="") {

    $items = Cart66Session::get('Cart66Cart')->getItems();
    $product = new Cart66Product();
    $products = array();
    foreach($items as $itemIndex => $item) {
      $product->load($item->getProductId());
      $products[] = array(
        'productName' => $item->getFullDisplayName(),
        'productQuantity' => $item->getQuantity(),
        'productPrice' => Cart66Common::currency($item->getProductPrice()),
        'productSubtotal' => Cart66Common::currency($item->getProductPrice() * $item->getQuantity())
      );
    }

    $summary = array(
      'items' => ' ' . _n('item', 'items', Cart66CartWidget::countItems(), 'cart66'),
      'amount' => Cart66Common::currency(Cart66CartWidget::getSubTotal()),
      'count' => Cart66CartWidget::countItems()
    );

    $array = array(
      'summary' => $summary,
      'products' => $products,
      'subtotal' => Cart66Common::currency(Cart66Session::get('Cart66Cart')->getSubTotal()),
      'shipping' => Cart66Session::get('Cart66Cart')->requireShipping() ? 1 : 0,
      'shippingAmount' => Cart66Common::currency(Cart66Session::get('Cart66Cart')->getShippingCost())
    );
    echo json_encode($array);
    die();
  }

  public static function ajaxAddToCart() {
    $message = Cart66Session::get('Cart66Cart')->addToCart(true);
    if(!is_array($message)) {
      $message = array(
        'msgId' => -2,
        'msgHeader' => __('Error', 'cart66'),
        'msg' => '<p>' . __('An error occurred while trying to add a product to the cart. Please contact the site administrator.', 'cart66') . '</p>'
      );
    }
    echo json_encode($message);
    die();
  }

  public static function promotionProductSearch() {
    global $wpdb;
    $search = Cart66Common::getVal('q');
    $product = new Cart66Product();
    $tableName = Cart66Common::getTableName('products');
    $search_sql = $wpdb->prepare( "SELECT id, name from $tableName WHERE name LIKE %s ORDER BY id ASC LIMIT 10", '%' . $wpdb->esc_like($search) . '%');
    $products = $wpdb->get_results($search_sql);
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
    if(!Cart66Common::cart66UserCan('settings')){
      die();
    }
    $error = '';
    foreach($_REQUEST as $key => $value) {
      if($key[0] != '_' && $key != 'action' && $key != 'submit' && $key) {
        if(is_array($value) && $key != 'admin_page_roles') {
          $value = array_filter($value, 'strlen');
          if(empty($value)) {
            $value = '';
          }
          else {
            $value = implode('~', $value);
          }
        }
        if($key == 'status_options') {
          $value = str_replace('&', '', Cart66Common::deepTagClean($value));
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
            $error = '<span>' . $e->getMessage() . '</span>';
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught Cart66 exception: " . $e->getMessage());
          }
        }
        elseif($key == 'constantcontact_list_ids') {

        }
        elseif($key == 'admin_page_roles') {
          $value = serialize($value);
          Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Admin Page Roles: " . print_r($value,true));
        }
        elseif($key == 'currency_decimals' && $value == 0) {
          $value = 'no_decimal';
        }

        Cart66Setting::setValue($key, trim(stripslashes($value)));

        if(CART66_PRO && $key == 'order_number') {
          $versionInfo = get_transient('_cart66_version_request');
          if(!$versionInfo) {
            $versionInfo = Cart66ProCommon::getVersionInfo();
            set_transient('_cart66_version_request', $versionInfo, 43200);
          }
          if(!$versionInfo) {
            Cart66Setting::setValue('order_number', '');
            $error = '<span>' . __( 'Invalid Order Number' , 'cart66' ) . '</span>';
          }
        }
      }
    }

    if($error) {
      $result[0] = 'Cart66Modal alert-message alert-error';
      $result[1] = "<strong>" . __("Warning","cart66") . "</strong><br/>$error";
    }
    else {
      $result[0] = 'Cart66Modal alert-message success';
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

  public static function checkInventoryOnAddToCart() {
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
        $out .= '<tr><td colspan="2"><strong>' . __('Currently In Stock', 'cart66') . '</strong></td></tr>';
        foreach($counts as $name => $qty) {
          $out .= '<tr>';
          $out .= "<td>$name</td><td>$qty</td>";
          $out .= '</tr>';
        }
        $out .= '</table>';
      }
      $soldOutLabel = Cart66Setting::getValue('label_out_of_stock') ? strtolower(Cart66Setting::getValue('label_out_of_stock')) : __('out of stock', 'cart66');
      $result[1] = $p->name . " " . $optionsMsg . " is $soldOutLabel $out";
    }

    $result = json_encode($result);
    echo $result;
    die();
  }

  public static function pageSlurp() {
    require_once(CART66_PATH . "/models/Pest.php");
    require_once(CART66_PATH . "/models/PestJSON.php");

    $page_id = Cart66Common::postVal('page_id');
    $page = get_page($page_id);
    $slurp_url = get_permalink($page->ID);
    $html = false;
    $job_id = $slurp_url;

    if(wp_update_post(array('ID' => $page->ID, 'post_status' => 'publish'))) {
      $access_key = Cart66Setting::getValue('mijireh_access_key');
      $rest = new PestJSON(MIJIREH_CHECKOUT);
      $rest->setupAuth($access_key, '');
      $data = array(
        'url' => $slurp_url,
        'page_id' => $page->ID,
        'return_url' => add_query_arg('task', 'mijireh_page_slurp', $slurp_url)
      );

      try {
        $response = $rest->post('/api/1/slurps', $data);
        $job_id = $response['job_id'];
      }
      catch(Pest_Unauthorized $e) {
        header('Bad Request', true, 400);
        die();
      }
    }
    else {
      $job_id = 'did not update post successfully';
    }

    echo $job_id;
    die;
  }

  public static function dismissMijirehNotice() {
    Cart66Setting::setValue('mijireh_notice', 1);
  }

}