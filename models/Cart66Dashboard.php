<?php

class Cart66Dashboard {
  
  // Create the function to initiate the Dashboard
  public static function cart66_add_dashboard_widgets() {
	  wp_add_dashboard_widget('cart66_recent_orders_widget', __('Cart66 Recent Orders', 'cart66'), array('Cart66Dashboard', 'cart66_recent_orders_widget'), array('Cart66Dashboard', 'cart66_recent_orders_setup'));
	  wp_add_dashboard_widget('cart66_statistics_widget', __('Cart66 Statistics', 'cart66'), array('Cart66Dashboard', 'cart66_statistics_widget'));	
	  global $wp_meta_boxes;
  	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
  	$cart66_recent_orders_widget_backup = array('cart66_recent_orders_widget' => $normal_dashboard['cart66_recent_orders_widget']);
  	$cart66_statistics_widget_backup = array('cart66_statistics_widget' => $normal_dashboard['cart66_statistics_widget']);
  	unset($normal_dashboard['cart66_recent_orders_widget']);
  	unset($normal_dashboard['cart66_statistics_widget']);
  	$sorted_dashboard = array_merge($cart66_recent_orders_widget_backup, $cart66_statistics_widget_backup, $normal_dashboard);
  	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
  	
  	if(CART66_PRO) {
  	  $updater = new Cart66ProCommon();
      $newVersion = $updater->getVersionInfo();
      $dismissVersion = Cart66Setting::getValue('dismiss_version');
      $currentVersion = Cart66Setting::getValue('version');
      if(version_compare($currentVersion, $newVersion['version'], '<') && version_compare($newVersion['version'], $dismissVersion, '>')){
        if(current_user_can('update_plugins')) {
          $plugins = get_plugin_updates();
        	if (!empty($plugins)) {
        		add_action('admin_footer', array('Cart66Dashboard', 'cart66_upgrade_message_jquery'));
        	}
        }
      }
  	}
  } 
  
  // Create the Dashboard Widget
  public static function cart66_recent_orders_widget() {
	  // Display whatever it is you want to show
	  global $wpdb;
	  $dashboardOrderLimit = (Cart66Setting::getValue('dashboard_order_limit')) ? Cart66Setting::getValue('dashboard_order_limit') : 10;
	  
    $order = new Cart66Order();
    $orderRows = $order->getOrderRows(null, 'order by ordered_on DESC', $dashboardOrderLimit);

  ?>
	
	  <table id="dashboardTable" cellspacing="0" cellpadding="0">
	  <?php if (count($orderRows) == 0) : ?>
      <tr>
        <td colspan="6" class="left noOrders">
          <h1><?php _e( 'You have no orders yet... Start Selling!' , 'cart66' ); ?></h1>
          <p>&nbsp;</p>
          <p>
            <a href="admin.php?page=cart66-settings" class="links"><?php _e( 'Cart66 Settings' , 'cart66' ); ?></a><br />
            <a href="admin.php?page=cart66-products" class="links"><?php _e( 'Cart66 Products' , 'cart66' ); ?></a><br />
            <a href="admin.php?page=cart66-inventory" class="links"><?php _e( 'Cart66 Inventory' , 'cart66' ); ?></a><br />
            <a href="admin.php?page=cart66-shipping" class="links"><?php _e( 'Cart66 Shipping' , 'cart66' ); ?></a>
          </p>
          <p>&nbsp;</p>
        </td>
      </tr>
    <?php else : ?> 
      <thead>
    	  <tr>
    	    <th colspan="2" class="left"><?php _e( 'Order Details' , 'cart66' ); ?></th>
    		  <?php if(Cart66Setting::getValue('dashboard_display_order_number')) : ?>
    		      <th class="left"><?php _e( 'Order Number' , 'cart66' ); ?></th>
    		  <?php endif; ?>
    		  
    		  <?php if(Cart66Setting::getValue('dashboard_display_delivery')) : ?>
            <th class="left"><?php _e( 'Delivery' , 'cart66' ); ?></th>
  		    <?php endif; ?>
    		  
    		  <?php if(Cart66Setting::getValue('dashboard_display_status')) : ?>
    		    <th class="center"><?php _e( 'Status' , 'cart66' ); ?></th>
   		    <?php endif; ?>
   		    
    		  <th class="right"><?php _e( 'Order Total' , 'cart66' ); ?></th>
    	  </tr>
      </thead>
    <?php endif; ?>
    <?php
      $row_num = 1;
      $displayed_total = 0;
      $colSpan = false;
      if(Cart66Setting::getValue('dashboard_display_status') || Cart66Setting::getValue('dashboard_display_delivery') || Cart66Setting::getValue('dashboard_display_order_number')) {
        $colSpan = true;
      }
    ?>
      <tbody>
      <?php
      foreach($orderRows as $order):
        $proper_currency = $order->total;
        $total = number_format($proper_currency, 2, '.', ',');
        ?>
        <tr class="orderDetails" onclick="window.location.href='?page=cart66_admin&task=view&id=<?php echo $order->id; ?>';">
          <td class="right orderRow numberTD">
            <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id; ?>'>
              <h2><?php echo $row_num; ?></h2>
            </a>
          </td>
          <td class="orderRow">
            <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id; ?>'>
              <p>
                <strong><span class="links"><?php echo $order->bill_first_name . ' ' . $order->bill_last_name; ?></span></strong>
                <br />
                <span class="orderDate"><?php echo Cart66Common::getElapsedTime($order->ordered_on); ?></span>
              </p>
            </a>
          </td>
        
          <?php if(Cart66Setting::getValue('dashboard_display_order_number')) : ?>
    		    <td class="left orderNumber orderRow">
    		      <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id ?>'>
                <p><?php echo $order->trans_id; ?></p>
              </a>
            </td>
    		  <?php endif; ?>
  		  
          <?php if(Cart66Setting::getValue('dashboard_display_delivery')) : ?>
    		    <td class="orderRow">
    		      <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id ?>'>
                <p><?php echo $order->shipping_method; ?></p>
              </a>
            </td>
    		  <?php endif; ?>
  		  
          <?php if(Cart66Setting::getValue('dashboard_display_status')) : ?>
    		    <td class="center orderRow">
    		      <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id ?>'>
                <p><?php echo $order->status; ?></p>
              </a>
            </td>
    		  <?php endif; ?>
  		  
          <td class="right orderRow middle">
            <a class="orderLinks" href='?page=cart66_admin&task=view&id=<?php echo $order->id ?>'>
              <p><strong><?php echo CART66_CURRENCY_SYMBOL . $total; ?></strong></p>
            </a>
          </td>
        </tr>
      <?php
        $row_num++;
        $displayed_total += $order->total;
      endforeach;
    
      ?>
      </tbody>  
      <tfoot>
         <tr>
           <td colspan="<?php echo $colSpan ? '3' : '2'; ?>" class="left orderTotal middle">
             <p><span class="recentOrderTotal"><a class="links" href='admin.php?page=cart66_admin'><?php _e('View All Orders', 'cart66' ); ?></a><?php
             if(CART66_PRO) { ?> - <a class="links" href='admin.php?page=cart66-reports'><?php _e('View Reports', 'cart66' ); ?></a>
             <?php } ?></span></p>
           </td>
           <td colspan="4" class="right orderTotal middle">
             <p><span class="recentOrderTotal"><?php _e('Recent Orders Total', 'cart66' ); ?>:</span> <strong><?php echo CART66_CURRENCY_SYMBOL . number_format($displayed_total, 2); ?></strong></p>
           </td>
         </tr>
      </tfoot>
    </table>
	  <?php
	  if(CART66_PRO) {
	    $updater = new Cart66ProCommon();
      $newVersion = $updater->getVersionInfo();
      $dismissVersion = Cart66Setting::getValue('dismiss_version');
      $currentVersion = Cart66Setting::getValue('version');
      if(version_compare($currentVersion, $newVersion['version'], '<') && version_compare($newVersion['version'], $dismissVersion, '>')){
        if(current_user_can('update_plugins')) {
          $plugins = get_plugin_updates();
        	if (!empty($plugins)) {
        		self::cart66_upgrade_message($currentVersion, $newVersion);
        	}
        }
      }
	  }
  }
  
  // Create the settings for the Dashboard Widget
  public static function cart66_recent_orders_setup() {
    if( $_SERVER['REQUEST_METHOD']=="POST" && isset( $_POST['widget_id'] ) && 'cart66_recent_orders_widget' == $_POST['widget_id'] ) {
      $dashboardOrderLimit = Cart66Common::postVal('dashboard_order_limit');                     
      Cart66Setting::setValue('dashboard_order_limit', $dashboardOrderLimit);
      
      $dashboard_display_status = Cart66Common::postVal('dashboard_display_status');               
      Cart66Setting::setValue('dashboard_display_status', $dashboard_display_status);
      
      
      $order_number_column = Cart66Common::postVal('dashboard_display_order_number');   
      Cart66Setting::setValue('dashboard_display_order_number', $order_number_column);
      
      $dashboard_display_delivery = Cart66Common::postVal('dashboard_display_delivery');           
      Cart66Setting::setValue('dashboard_display_delivery', $dashboard_display_delivery);
      
	  }
	  
	  $dashboardOrderLimit = (Cart66Setting::getValue('dashboard_order_limit')) ? Cart66Setting::getValue('dashboard_order_limit') : 10;
    ?>
    <div class="optionsDiv">
      <p>
        <label for="dashboardOrderLimit"><?php _e('How many recent orders would you like to display?', 'cart66' ); ?>
          <input type='text' name='dashboard_order_limit' id='dashboard_order_limit' style='width: 50px;' value="<?php echo $dashboardOrderLimit; ?>" />
		    </label>
	    </p>
	    <p>
        <label for="dashboard_display_status"><input type="checkbox" name='dashboard_display_status' id='dashboard_display_status' value="1" <?php echo (Cart66Setting::getValue('dashboard_display_status') == 1) ? 'checked="checked"' : ''; ?> />
          <?php _e('Display Status Column', 'cart66' ); ?>  
		    </label>
	    </p>
	    <p>
        <label for="dashboard_display_order_number"><input type="checkbox" name='dashboard_display_order_number' id='dashboard_display_order_number' value="1" <?php echo (Cart66Setting::getValue('dashboard_display_order_number') == 1) ? 'checked="checked"' : ''; ?> />
          <?php _e('Display Order Number Column', 'cart66' ); ?> 
		    </label>
	    </p>
	    <p>
        <label for="dashboard_display_delivery"><input type="checkbox" name='dashboard_display_delivery' id='dashboard_display_delivery' value="1" <?php echo (Cart66Setting::getValue('dashboard_display_delivery') == 1) ? 'checked="checked"' : ''; ?> />
          <?php _e('Display Delivery Column', 'cart66' ); ?> 
		    </label>
	    </p>
	  </div>
    <?php
  }

  public static function cart66_statistics_widget() {
      
    function totalFromRange($start,$end){
      global $wpdb;
      $tableName = Cart66Common::getTableName('orders');
    	$sql = "SELECT sum(total) from $tableName where ordered_on > '$start' AND ordered_on < '$end'";
    	$result = mysql_query($sql);
    	if (!$result) {
    	    die('TotalFromRange(): Could not query:' . mysql_error() . " " . $sql);
    	}
    	if(mysql_num_rows($result)>0){
    		$output = mysql_result($result,0);
    	}
    	else{
    		$output = "N/A";
    	}
    	return $output;
    }

    // TODAY
    $yday = date('Y-m-d 00:00:00', strtotime('yesterday'));
    $dayStart = date('Y-m-d 00:00:00', strtotime('today'));
    $dayEnd = date('Y-m-d 00:00:00', strtotime('tomorrow'));
    $mdayStart = date('Y-m-01 00:00:00', strtotime('today'));
    $mdayEnd = date('Y-m-01 00:00:00', strtotime('next month'));

    $today_total =	totalFromRange($dayStart,$dayEnd);
    $yesterday_total = totalFromRange($yday,$dayStart);
    $month_total = totalFromRange($mdayStart,$mdayEnd);
    $total_product_sales = Cart66DataTables::productsSearch();
    $total_sales_amount = Cart66DataTables::totalSalesForMonth($total_product_sales);
    $daily_avg = ($month_total-$today_total)/date('j',strtotime('yesterday'));    //number_format($month_total/date("j"),2);
    $total_days = date('t',strtotime('now'));
    $est_month = ($total_days * $daily_avg);
    ?>
    <div class="tabbed">
    	<ul class="tabs">
    	  <li class="t1"><a class="t1 tab" href="javascript:void(0)"><?php _e('Summary', 'cart66') ?></a></li>
    	  <li class="t2"><a class="t2 tab" href="javascript:void(0)"><?php _e('Today/Yesterday', 'cart66') ?></a></li>
    	  <li class="t3"><a class="t3 tab" href="javascript:void(0)"><?php echo date("F, Y",strtotime("now"))?></a></li>
    	  <li class="t4"><a class="t4 tab" href="javascript:void(0)"><?php _e('Daily Average', 'cart66') ?></a></li>
    	  <li class="t5"><a class="t5 tab" href="javascript:void(0)"><?php _e('Estimate', 'cart66') ?></a></li>
    	</ul>
    	<div class="loading">
    	  <h2 class="center"><?php _e('loading...', 'cart66') ?></h2>
    	</div>
    	<div class="t1 pane">
    	  <table id="statSummary" cellspacing="0" cellpadding="0">
    	  <tfoot>
  	    <tr>
  	      <td>
  	       <?php _e('Last Updated', 'cart66') ?>:
  	      </td>
  	      <td class="right">
  	        <?php echo date('D, M d, Y g:i:s A', Cart66Common::localTs()); ?>
  	      </td>
  	    </tr>
  	    </tfoot>
    	    <tbody>
    	    <tr class="t4 tab summaryDetails">
    	      <td>
    	       <?php echo date('F'); _e(' Daily Average', 'cart66') ?>:
    	      </td>
    	      <td class="right">
    	       <a class="t4 tab" href="javascript:void(0)"><?php echo CART66_CURRENCY_SYMBOL . number_format($daily_avg,2) ?></a>
    	      </td>
    	    </tr>
    	    <tr class="t2 tab summaryDetails">
    	      <td>
    	        <?php _e('Today\'s Total', 'cart66') ?>:
    	      </td>
    	      <td class="right">
    	        <a class="t2 tab" href="javascript:void(0)"><?php echo CART66_CURRENCY_SYMBOL . number_format($today_total,2); ?></a>
    	      </td>
    	    </tr>
    	    <tr class="t2 tab summaryDetails">
    	      <td>
    	        <?php _e('Yesterday\'s Total', 'cart66') ?>:
    	      </td>
    	      <td class="right">
    	        <a class="t2 tab" href="javascript:void(0)"><?php echo CART66_CURRENCY_SYMBOL . number_format($yesterday_total,2);?></a>
    	      </td>
    	    </tr>
    	    <tr class="t3 tab summaryDetails">
    	      <td>
    	        <?php echo date("F",strtotime("now"))?> <?php _e('Total', 'cart66') ?>:
    	      </td>
    	      <td class="right">
    	       <a class="t3 tab" href="javascript:void(0)"><?php echo CART66_CURRENCY_SYMBOL . number_format($month_total,2); ?></a>
    	      </td>
    	    </tr>
    	    <tr class="t5 tab summaryDetails">
    	      <td>
    	       <?php _e('Estimated', 'cart66') ?> <?php echo date("F",strtotime('now'))?> <?php _e('Total', 'cart66') ?>:
    	      </td>
    	      <td class="right">
    	       <a class="t5 tab" href="javascript:void(0)"><?php echo CART66_CURRENCY_SYMBOL . number_format($est_month,2); ?></a>
    	      </td>
    	    </tr>
    	    </tbody>
    	  </table>
    	</div>
    	<div class="t2 pane">
        <table id="dayStats" cellpadding="0" cellspacing="0">
          <tr class="summaryDetails dayStats">
            <td>
              <?php _e('Today\'s Total', 'cart66') ?>: <strong><?php echo CART66_CURRENCY_SYMBOL . number_format($today_total,2); ?></strong>
            </td>
            <td class="right">
              <?php _e('Yesterday\'s Total', 'cart66') ?>: <strong><?php echo CART66_CURRENCY_SYMBOL . number_format($yesterday_total,2);?></strong>
            </td>
          </tr>
          <tr>
            <td class="wideOrders" colspan="2">
              <table width="100%" id="todaysOrders" cellpadding="0" cellspacing="0">
              <thead>
                <tr>
                  <th colspan="2" class="left"><?php _e('Today\'s Order Details', 'cart66') ?></th>
                  <th class="right"><?php _e('Order Total', 'cart66') ?></th>
                </tr>
                </thead>
          	  <?php 
        			$Orders = new Cart66Order();
        			$todaysOrders = $Orders->getOrderRows(" WHERE ordered_on > '$dayStart' AND ordered_on < '$dayEnd' AND id>0", "order by ordered_on DESC");
        			
        			if($todaysOrders):
        			  $i=1; ?>
        			  <tbody>
        			    <?php foreach($todaysOrders as $order): ?>
          			    <tr>
          					  <td class="rowNumber">
          					    <h2><?php echo $i ?></h2>
          					  </td>
          					  <td class="orderInformation">
          					    <p><?php echo $order->bill_first_name . " " . $order->bill_last_name ?><br>
          					      <span class='orderDate'><?php echo Cart66Common::getElapsedTime($order->ordered_on); ?></span>
          					    </p>
          					  </td>
          					  <td class='right'>
          					    <?php echo CART66_CURRENCY_SYMBOL . number_format($order->total,2) ?>
          					  </td>
          					  <?php $i++; ?>
          					</tr>
          				<?php endforeach;?>
        				</tbody>
        				<?php else: ?>
        				<tfoot>
        				<tr>
        				  <td colspan='3'>
        				    <h2 class="noOrders"><?php _e('No orders yet today', 'cart66') ?></h2>
        				  </td>
        				</tr>
        				</tfoot>
        			<?php endif; ?>
        			</table>
            </td>
          </tr>
        </table>	  
      </div>
      <div class="t3 pane">
        <table id="productTable" cellpadding="0" cellspacing="0">
          <tr>
            <thead>
            	<tr>
          			<th class="left"><?php _e('Product Name', 'cart66'); ?></th>
          			<th class="center"><?php _e('Sales', 'cart66'); ?></th>
          			<th class="right"><?php _e('Income', 'cart66'); ?></th>
            	</tr>
            </thead>
            <tfoot>
              <tr>
                <th class="left"><strong><?php echo date("F, Y",strtotime('now')); ?></strong></th>
                <?php
                if(isset($total_sales_amount['total_sales'])) {
                  $totalSales = $total_sales_amount['total_sales']['total_quantity'];
                  $totalAmount = $total_sales_amount['total_sales']['total_amount'];
                }
                else {
                  $totalSales = '0';
                  $totalAmount = '0';
                }
                ?>
                <th class="center"><strong><?php echo $totalSales; ?></strong></th>
                <th class="right"><strong><?php echo CART66_CURRENCY_SYMBOL . number_format($totalAmount,2); ?></strong></th>
              </tr>
            </tfoot>
          </tr>
        </table>
      </div>
      <div class="t4 pane">
      	<div>
      	  <table id="dailyAverage" cellpadding="0" cellspacing="0">
      		    <?php
      		    $column = 0;
      		    for($i=6; $i>0; $i--){
    			      $tmonth_start = date('Y-m-01 00:00:00',strtotime("$i months ago"));
    			      $tmonth_end = date('Y-m-01 00:00:00',strtotime(($i-1)." months ago"));
    			      $tmonth_total = totalFromRange($tmonth_start,$tmonth_end);
    				    $tmonth_days = date('t',strtotime("$i months ago"));
    				    ?>
  					    <?php if($tmonth_total!=""){ ?>
              <thead>
                <tr>
                  <th class="left" colspan="2">
                  <?php echo date('F, Y',strtotime("$i months ago")); ?>
						      </th>
						    </tr>
					    </thead>
					    <tbody>
					      <tr>
  						    <td><?php _e('Total Income', 'cart66') ?>:</td>
  						    <td class="right"><strong><?php echo CART66_CURRENCY_SYMBOL . number_format($tmonth_total,2); ?></strong></td>
  						  </tr>
  						  <tr>
  						    <td><?php _e('Daily Average', 'cart66') ?>:</td>
  						    <td class="right"><strong><?php echo CART66_CURRENCY_SYMBOL . number_format($tmonth_total/$tmonth_days,2);?></strong></td>
  					    </tr>
  					    </tbody>
  				      <?php
    				      }
      			    }
    			    $ystart = date("Y-01-01 00:00:00");
    			    $yend = date("Y-01-01 00:00:00",strtotime("next year"));
    			    $year_total = totalFromRange($ystart,$yend);
    			    $day_of_year = date('z');
    			    ?>
    			    <thead>
    			      <tr>
    			        <th class="left" colspan="2">YTD - <?php echo date('Y'); ?></th>
    			      </tr>
    			    </thead>
    			    <tbody>
        			  <tr>
        			    <td><?php _e('Total Income', 'cart66') ?>:</td>
        			    <td class="right"><strong><?php echo CART66_CURRENCY_SYMBOL . number_format($year_total,2); ?></strong></td>
        			  </tr>
        			  <tr>
        			      <td><?php _e('Daily Average', 'cart66') ?>:</td>
        			      <td class="right"><strong><?php echo CART66_CURRENCY_SYMBOL . number_format($year_total/$day_of_year,2);?></strong></td>
        			  </tr>
        			</tbody>
    			</table>
      	</div>
      </div>
      <div class="t5 pane">
    	  <table id="estimatedSummary" cellspacing="0" cellpadding="0">
    	    <tbody>
    	      <tr>
    	        <td>
  	            <?php _e('Today', 'cart66') ?>:
    	        </td>
    	        <td class="right">
    	          <?php echo date('F j',strtotime('now'));?>
    	        </td>
    	      </tr>
    	      <tr>
    	        <td>
    	          <?php _e('Total Days in', 'cart66') ?> <?php echo date("F",strtotime('now'))?>:
    	        </td>
    	        <td class="right">
    	          <?php echo $total_days; ?>
    	        </td>
    	      </tr>
    	      <tr>
    	        <td>
    	          <?php _e('Remaining Days in', 'cart66') ?> <?php echo date("F",strtotime('now'))?>:
    	        </td>
    	        <td class="right">
    	          <?php echo $total_days-date('j',strtotime('now')); ?>
    	        </td>
    	      </tr>
    	      <tr>
    	        <td>
    	          <?php _e('Estimated Remaining Income', 'cart66') ?>:
    	        </td>
    	        <td class="right">
    	          <?php echo CART66_CURRENCY_SYMBOL . number_format(($total_days-date('j',strtotime('now'))) * $daily_avg,2); ?>
    	        </td>
    	      </tr>
    	    </tbody>
    	  </table>
    	</div>
    </div>
    <script type="text/javascript">
      (function($){
        $(document).ready(function(){
          
          /* API method to get paging information */
          $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
          {
              return {
                  "iStart":         oSettings._iDisplayStart,
                  "iEnd":           oSettings.fnDisplayEnd(),
                  "iLength":        oSettings._iDisplayLength,
                  "iTotal":         oSettings.fnRecordsTotal(),
                  "iFilteredTotal": oSettings.fnRecordsDisplay(),
                  "iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
                  "iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
              };
          }

          /* Bootstrap style pagination control */
          $.extend( $.fn.dataTableExt.oPagination, {
              "bootstrap": {
                  "fnInit": function( oSettings, nPaging, fnDraw ) {
                      var oLang = oSettings.oLanguage.oPaginate;
                      var fnClickHandler = function ( e ) {
                          e.preventDefault();
                          if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                              fnDraw( oSettings );
                          }
                      };

                      $(nPaging).addClass('pagination').append(
                          '<ul>'+
                              '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
                              '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
                          '</ul>'
                      );
                      var els = $('a', nPaging);
                      $(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
                      $(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
                  },

                  "fnUpdate": function ( oSettings, fnDraw ) {
                      var iListLength = 5;
                      var oPaging = oSettings.oInstance.fnPagingInfo();
                      var an = oSettings.aanFeatures.p;
                      var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

                      if ( oPaging.iTotalPages < iListLength) {
                          iStart = 1;
                          iEnd = oPaging.iTotalPages;
                      }
                      else if ( oPaging.iPage <= iHalf ) {
                          iStart = 1;
                          iEnd = iListLength;
                      } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
                          iStart = oPaging.iTotalPages - iListLength + 1;
                          iEnd = oPaging.iTotalPages;
                      } else {
                          iStart = oPaging.iPage - iHalf + 1;
                          iEnd = iStart + iListLength - 1;
                      }

                      for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                          // Remove the middle elements
                          $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                          // Add the new list items and their event handlers
                          for ( j=iStart ; j<=iEnd ; j++ ) {
                              sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
                              $('<li '+sClass+'><a href="#">'+j+'</a></li>')
                                  .insertBefore( $('li:last', an[i])[0] )
                                  .bind('click', function (e) {
                                      e.preventDefault();
                                      oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
                                      fnDraw( oSettings );
                                  } );
                          }

                          // Add / remove disabled classes from the static elements
                          if ( oPaging.iPage === 0 ) {
                              $('li:first', an[i]).addClass('disabled');
                          } else {
                              $('li:first', an[i]).removeClass('disabled');
                          }

                          if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
                              $('li:last', an[i]).addClass('disabled');
                          } else {
                              $('li:last', an[i]).removeClass('disabled');
                          }
                      }
                  }
              }
          } );
          
          
          $('#productTable').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "bPagination": true,
            "bLengthChange": false,
            "iDisplayLength": 6,
            "sPaginationType": "bootstrap",
    				"sAjaxSource": ajaxurl + "?action=dashboard_products_table",
    				"aoColumns": [
              { "sClass": "left" },
              { "sClass": "center" },
              { "sClass": "right" },
            ],
            "oLanguage": { "sZeroRecords": "<?php _e('No matching products found', 'cart66'); ?>" }
          });
        })
      })(jQuery);
    </script> 
    <script type="text/javascript">
      (function($){
        $(document).ready(function() {
          // setting the tabs in the sidebar hide and show, setting the current tab
      	  $('div.pane').hide();
      	  $('div.t1').show();
      	  $('div.loading').hide();
      	  $('div.tabbed ul.tabs li.t1 a').addClass('tab-current');
          // SIDEBAR TABS
          $('div.tabbed ul li a, div.t1 a, div.t1 tr.summaryDetails').click(function(){
            if($(this).hasClass('tab')) {
              var thisClass = this.className.slice(0,2);
        	    $('div.pane').hide();
        	    $('div.' + thisClass).fadeIn(300);
        	    $('div.tabbed ul.tabs li a').removeClass('tab-current');
        	    $('div.tabbed ul.tabs li a.' + thisClass).addClass('tab-current');
            }
      	  });
        });
      })(jQuery);
    </script><?php
  }
  
  public static function cart66_statistics_setup() {
    
  }
  
  public static function cart66_upgrade_message($currentVersion, $newVersion){
    $cart66_plugin_url = "cart66/cart66.php";
    $cart66_upgrade_url = wp_nonce_url('update.php?action=upgrade-plugin&amp;plugin=' . urlencode($cart66_plugin_url), 'upgrade-plugin_' . $cart66_plugin_url);
    ?>
      <div class='updated' id='cart66_upgrade_message'>
        <p class="left">
          <img src="<?php echo CART66_URL ?>/images/cart66_upgrade.png" height="30" />
          <strong><?php _e('There is a new version of Cart66 available', 'cart66'); ?>!</strong> 
          <?php _e('You are currently running Cart66', 'cart66'); ?> 
          <?php echo $currentVersion; ?><br />
          <strong><?php _e('The latest version of Cart66 is', 'cart66'); ?> <?php echo $newVersion['version']; ?>.</strong>
          &nbsp;<a href="plugin-install.php?tab=plugin-information&plugin=cart66&TB_iframe=true&width=640&height=810" class="thickbox" title="Cart66"><?php _e('View Details', 'cart66'); ?></a> 
          <?php _e('or', 'cart66'); ?> 
          <a href="<?php echo $cart66_upgrade_url; ?>"><?php _e('Upgrade Automatically', 'cart66'); ?></a></p>
        <p><a href="javascript:void(0);" class="dismiss" onclick="dismissMessage();"><?php _e("Dismiss", "cart66"); ?></a></p>
        <br clear="all" />
      </div>
    <?php
  }
  
  public static function cart66_upgrade_message_jquery() {
    $updater = new Cart66Updater();
    $newVersion = $updater->newVersion();
    ?>
      <script type="text/javascript">
        (function($){
          $(document).ready(function(){
            $("#cart66_upgrade_message").fadeIn(900);
          })
        })(jQuery);
        $jq = jQuery.noConflict();
        function dismissMessage(){
          $jq.post(ajaxurl,{'action':'save_settings','dismiss_version':'<?php echo $newVersion["version"] ?>'},function(){
            $jq("#cart66_upgrade_message").fadeOut(900);
          })
        }
      </script> 
    <?php 
  }
  
}