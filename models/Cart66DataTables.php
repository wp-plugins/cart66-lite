<?php
Class Cart66DataTables {
  
  public static function sortProductSearch($a, $b) {
    if(isset($_GET['iSortCol_0'])){
  		for($i=0; $i<intval($_GET['iSortingCols']); $i++){
  			if(isset($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])]) && $_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true"){
  			  if($_GET['iSortCol_' . $i] == 0) {
  			    $value = 'name';
  			  }
  			  elseif($_GET['iSortCol_' . $i] == 1) {
  			    $value = 'quantity';
  			  }
  			  else {
  			    $value = 'sales_amount';
  			  }
  			  if($_GET['sSortDir_0'] == 'asc') {
  			    $result = strnatcmp($a[$value], $b[$value]);
  			  }
  				else {
  				  $result = strnatcmp($b[$value], $a[$value]);
  				}
  			}
  		}
  	}
    return $result;
  }
  
  public function productsSearch() {
    $where = "";
  	if(isset($_GET['sSearch']) && $_GET['sSearch'] != ""){
  		$where = $_GET['sSearch'];
  	}
  	
    $products = self::productSalesForMonth();
    foreach($products as $k => $p) {
      if(!preg_match("/$where/i", $p['name']) && !preg_match("/$where/i", $p['quantity']) && !preg_match("/$where/i", $p['sales_amount'])) {
        unset($products[$k]);
      }
    }
    
    return $products;
  }
  
  public static function dashboardProductsTable() {
    
    $iFilteredTotal = self::productsSearch();
  	
  	$data = array();
  	$products = self::productSalesForMonth();
  	$productsSearch = self::productsSearch();
  	
  	uasort($productsSearch, array('Cart66DataTables', 'sortProductSearch'));
  	
  	if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1'){
  		$limit = array_slice($productsSearch, $_GET['iDisplayStart'], $_GET['iDisplayLength']);
  	}
  	
  	foreach($limit as $p) {
  	  $data[] = array(
    	  $p['name'],
    	  $p['quantity'],
    	  CART66_CURRENCY_SYMBOL . number_format($p['sales_amount'], 2)
    	);
  	}
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => count($products),
  	 'iTotalDisplayRecords' => count($productsSearch),
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function inventoryTable() {
    $columns = array(
      'id',
      'name',
      'options_1',
      'options_2'
    );
    
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
  	
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$product = new Cart66Product();
  	$products = $product->getModels($where, $order, $limit);
  	$save = false;
  	$ikeyList = array();
    foreach($products as $p) {
      $p->insertInventoryData();
      $combos = $p->getAllOptionCombinations();
      if(count($combos)) {
        foreach($combos as $c) {
          $k = $p->getInventoryKey($c);
          $ikeyList[] = $k;
          if($save) { $p->updateInventoryFromPost($k); }
          $data[] = array(
            $p->isInventoryTracked($k),
            $p->name,
            $c,
            $p->getInventoryCount($k),
            $k
          );
        }
      }
      else {
        $k = $p->getInventoryKey();
        $ikeyList[] = $k;
        if($save) { $p->updateInventoryFromPost($k); }
        $data[] = array(
          $p->isInventoryTracked($k),
          $p->name,
          $c='',
          $p->getInventoryCount($k),
          $k
        );
      }
    }
  
    if($save) { $p->pruneInventory($ikeyList); }
  	
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function accountsTable() {
    $columns = array(
      'a.id', 
      'a.first_name', 
      'a.last_name',
      'a.username', 
      'a.email',
      'a.notes',
      's.subscription_plan_name',
      's.feature_level',
      's.active_until',
    );
    $indexColumn = "DISTINCT a.id";
    $tableName = Cart66Common::getTableName('accounts') . ' as a, ' . Cart66Common::getTableName('account_subscriptions') . ' as s';
    
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 4,
        4 => 6,
        5 => 7,
        6 => 8,
        7 => 9
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    
    $where = self::dataTablesWhere($columns) == '' ? 'WHERE s.account_id = a.id' : self::dataTablesWhere($columns) . ' AND s.account_id = a.id ';
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName, 'WHERE s.account_id = a.id');
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	$data = array();
  	$account = new Cart66Account();
  	$accounts = $account->getModels($where, $order, $limit, $tableName, $indexColumn);
  	foreach($accounts as $a) {
      $planName = 'No Active Subscription';
      $featureLevel = 'No Access';
      $activeUntil = 'Expired';
      if($sub = $a->getCurrentAccountSubscription(true)) {
        $planName = $sub->subscriptionPlanName;
        $featureLevel = $sub->isActive() ? $sub->featureLevel : 'No Access - Expired';
        $activeUntil = $sub->isActive() ? date('m/d/Y', strtotime($sub->activeUntil)) : 'No Access';
        $activeUntil = ($sub->lifetime == 1) ? "Lifetime" : $activeUntil;
        $type = 'Manual';
        if($sub->isPayPalSubscription()) {
          $type = 'PayPal';
        }
        elseif($sub->isSpreedlySubscription()) {
          $type = 'Spreedly';
        }
      }
      else {
        $planName = 'No plan available';
        $featureLevel = 'No Feature Level';
        $activeUntil = 'No Access';
        $type = 'None';
      }
  	  
  	  $data[] = array(
  	    $a->id, 
  	    $a->first_name . ' ' . $a->last_name,
  	    $a->username,
  	    $a->email,
  	    $planName,
  	    $featureLevel,
  	    $activeUntil,
  	    $type,
  	    $a->notes
  	  );
  	}

  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function promotionsTable() {
    $columns = array(
      'id', 
      'name', 
      'code', 
      'amount', 
      'min_order', 
      'enable', 
      'effective_from', 
      'effective_to', 
      'redemptions', 
      'apply_to'
    );
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('promotions');
    if (isset($_GET['iSortCol_0'])){
      $sortingColumns = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 8,
        8 => 9
      );
      $_GET['iSortCol_0'] = $sortingColumns[$_GET['iSortCol_0']];
    }
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$promotion = new Cart66Promotion();
  	$promotions = $promotion->getModels($where, $order, $limit);
  	//Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . print_r($promotions));
  	foreach($promotions as $p) {
  	  $data[] = array(
  	    $p->id, 
  	    $p->name, 
  	    $p->getCodeAt(), 
  	    $p->getAmountDescription(), 
  	    $p->getMinOrderDescription(), 
  	    $p->enable == 1 ? 'Yes' : 'No',
  	    $p->effectiveDates(),
  	    ($p->redemptions < 1) ? __('Never', 'cart66') : ( ($p->redemptions == 1) ? $p->redemptions . ' time' : $p->redemptions . ' times'),
  	    ($p->apply_to == 'products') ? "Products" : ( ($p->apply_to == 'shipping') ? "Shipping" : "Cart Total" )
  	  );
  	}
  	
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function ordersTable() {
    $columns = array( 'id', 'trans_id', 'bill_first_name', 'bill_last_name', 'total', 'ordered_on', 'shipping_method', 'status', 'email' );
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('orders');

    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$orderBy = self::dataTablesOrder($columns);
    
    $iTotal = self::totalRows($indexColumn, $tableName);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$order = new Cart66Order();
  	$orders = $order->getOrderRows($where, $orderBy, $limit);
  	foreach($orders as $o) {
  	  $data[] = array(
  	    $o->id,
  	    $o->trans_id,
  	    $o->bill_first_name,
  	    $o->bill_last_name,
  	    $o->total,
  	    date('m/d/Y', strtotime($o->ordered_on)),
  	    $o->shipping_method,
  	    $o->status
  	  );
  	}
  	
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function productsTable() {
    $columns = array( 'id', 'item_number', 'name', 'price', 'taxable', 'shipped' );
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "where spreedly_subscription_id = 0 and is_paypal_subscription = 0";
    }
    else {
      $where .= " AND spreedly_subscription_id = 0 AND is_paypal_subscription = 0";
    }
    
    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$product = new Cart66Product();
  	$products = $product->getNonSubscriptionProducts($where, $order, $limit);
  	foreach($products as $p) {
  	  $gfTitles = self::gfData();
  	  if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$p->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $p->id,
  	    $p->item_number,
  	    $p->name . $gfTitles,
  	    CART66_CURRENCY_SYMBOL . $p->price,
  	    $p->taxable? ' Yes' : 'No',
  	    $p->shipped? ' Yes' : 'No'
  	  );
  	}
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function spreedlyTable() {
    $columns = array( 'id', 'item_number', 'name', 'price', 'taxable', 'shipped' );
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "where spreedly_subscription_id > 0";
    }
    else {
      $where .= " AND spreedly_subscription_id > 0";
    }
    
    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$spreedly = new Cart66Product();
  	$spreedlys = $spreedly->getSpreedlyProducts($where, $order, $limit);
  	foreach($spreedlys as $s) {
  	  $gfTitles = self::gfData();
  	  if($s->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$s->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$s->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $s->id,
  	    $s->item_number,
  	    $s->name . $gfTitles,
  	    $s->getPriceDescription(),
  	    $s->taxable? ' Yes' : 'No',
  	    $s->shipped? ' Yes' : 'No'
  	  );
  	}
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public static function paypalSubscriptionsTable() {
    $columns = array( 'id', 'item_number', 'name', 'feature_level', 'setup_fee', 'price', 'billing_cycles', 'offer_trial', 'start_recurring_number', 'start_recurring_unit' );
    $indexColumn = "id";
    $tableName = Cart66Common::getTableName('products');
    
    $where = self::dataTablesWhere($columns);
  	$limit = self::dataTablesLimit() == '' ? null : self::dataTablesLimit();
  	$order = self::dataTablesOrder($columns);
    
    if($where == null) {
      $where = "WHERE is_paypal_subscription>0";
    }
    else {
      $where .= " AND is_paypal_subscription>0";
    }

    $iTotal = self::totalRows($indexColumn, $tableName, $where);
    $iFilteredTotal = self::filteredRows($indexColumn, $tableName, $where);
  	
  	$data = array();
  	$subscription = new Cart66PayPalSubscription();
  	$subscriptions = $subscription->getModels($where, $order, $limit);
  	foreach($subscriptions as $s) {
  	  $gfTitles = self::gfData();
  	  if($s->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$s->gravityFormId])) {
         $gfTitles = '<br/><em>Linked To Gravity Form: ' . $gfTitles[$s->gravityFormId] . '</em>';
      }
      else {
        $gfTitles = '';
      }
  	  $data[] = array(
  	    $s->id,
  	    $s->item_number,
  	    $s->name . $gfTitles,
  	    $s->featureLevel,
  	    CART66_CURRENCY_SYMBOL . $s->setupFee,
  	    $s->getPriceDescription(false),
  	    $s->getBillingCycleDescription(),
  	    $s->getTrialPriceDescription(),
  	    $s->getStartRecurringDescription()
  	  );
  	}
  	Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . json_encode($data));
  	$array = array(
  	 'sEcho' => $_GET['sEcho'],
  	 'iTotalRecords' => $iTotal[0],
  	 'iTotalDisplayRecords' => $iFilteredTotal[0],
  	 'aaData' => $data
  	);
  	echo json_encode($array);
  	die();
  }
  
  public function gfData() {
    global $wpdb;
    $gfTitles = array();
    if(CART66_PRO && class_exists('RGFormsModel')) {
      require_once(CART66_PATH . "/pro/models/Cart66GravityReader.php");
      $forms = Cart66Common::getTableName('rg_form', '');
      $sql = "SELECT id, title from $forms where is_active=1 order by title";
      $results = $wpdb->get_results($sql);
      foreach($results as $r) {
        $gfTitles[$r->id] = $r->title;
      }
    }
    return $gfTitles;
  }
  
  public function totalRows($indexColumn, $tableName, $where=null) {
    global $wpdb;
    $sql = "
    	SELECT COUNT(" . $indexColumn . ")
    	FROM $tableName
    	$where
    ";
    $sql = $wpdb->get_results($sql, ARRAY_N);
    return $sql[0];
  }
  
  public function filteredRows($indexColumn, $tableName, $where) {
    global $wpdb;
    $sqlTotal = "
      SELECT COUNT(" . $indexColumn . ")
    	FROM $tableName
    	$where
    ";
    $sqlTotal = $wpdb->get_results($sqlTotal, ARRAY_N);
    return $sqlTotal;
  }
  
  public function dataTablesWhere($columns) {
    $where = "";
  	if($_GET['sSearch'] != ""){
  		$where = "WHERE (";
  		for ($i=0; $i<count($columns); $i++){
  			$where .= $columns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
  		}
  		$where = substr_replace($where, "", -3) . ')';
  	}

  	for($i=0; $i<count($columns); $i++){
  		if(isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != ''){
  			if($where == ""){
  				$where = "WHERE ";
  			}
  			else {
  				$where .= " AND ";
  			}
  			$where .= $columns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
  		}
  	}
    return $where;
  }
  
  public function dataTablesLimit() {
    $limit = "";
  	if(isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1'){
  		$limit = mysql_real_escape_string($_GET['iDisplayStart']) . ", " . mysql_real_escape_string($_GET['iDisplayLength']);
  	}
    return $limit;
  }
  
  public function dataTablesOrder($columns) {
    if(isset($_GET['iSortCol_0'])){
  		$order = "ORDER BY  ";
  		for($i=0; $i<intval($_GET['iSortingCols']); $i++){
  			if(isset($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])]) && $_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true"){
  				$order .= $columns[intval($_GET['iSortCol_' . $i])] . "
  				 	" . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", ";
  			}
  		}

  		$order = substr_replace($order, "", -2);
  		if($order == "ORDER BY"){
  			$order = "";
  		}
  	}
  	return $order;
  }
  
  public static function productSalesForMonth() {
    $data = array();

    foreach(self::getSalesForMonth() as $order_items) {
      if(isset($data[$order_items->product_id])) {
        $data[$order_items->product_id]['quantity'] = $data[$order_items->product_id]['quantity'] + $order_items->quantity;
        $data[$order_items->product_id]['sales_amount'] = ($order_items->product_price * $order_items->quantity) + $data[$order_items->product_id]['sales_amount'];
      }
      else {
        $data[$order_items->product_id] = array(
          'quantity' => $order_items->quantity, 
          'name' => $order_items->description,
          'sales_amount' => $order_items->product_price * $order_items->quantity
        );
      }
    }
    return $data;
  }

  public static function totalSalesForMonth($data) {
    $results = array();
    foreach($data as $d) {
      if(isset($results['total_sales']['total_amount'])) {
        $results['total_sales']['total_amount'] = $d['sales_amount'] + $results['total_sales']['total_amount'];
      }
      else {
        $results['total_sales']['total_amount'] = $d['sales_amount'];
      }
      if(isset($results['total_sales']['total_quantity'])) {
        $results['total_sales']['total_quantity'] = $d['quantity'] + $results['total_sales']['total_quantity'];
      }
      else {
        $results['total_sales']['total_quantity'] = $d['quantity'];
      }
    }
    return $results;
  }

  public static function getSalesForMonth() {
    $thisMonth = Cart66Common::localTs();
    $year =  date('Y', "$thisMonth");
    $month =  date('n', "$thisMonth");
    $orders = Cart66Common::getTableName('orders');
    $orderItems = Cart66Common::getTableName('order_items');
    $products = Cart66Common::getTableName('products');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));

    $sql = "SELECT 
        oi.id, 
        oi.description, 
        oi.product_id, 
        oi.product_price, 
        o.ordered_on,
        oi.quantity
      from 
        $products as p,
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = p.id and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
    ";
    global $wpdb;
    $query = $wpdb->prepare($sql);
    $results = $wpdb->get_results($query);
    return $results;
  }
  
}