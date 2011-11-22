<?php
global $wpdb;

$product = new Cart66Product();

if(Cart66Setting::getValue('enable_google_analytics') == 1 && Cart66Setting::getValue('use_other_analytics_plugin') == 'no'): ?>
  <script type="text/javascript">
    /* <![CDATA[ */
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo Cart66Setting::getValue("google_analytics_wpid") ?>']);
    _gaq.push(['_trackPageview']);
  /* ]]> */
  </script>
<?php endif;

$order = false;
if(isset($_GET['ouid'])) {
  $order = new Cart66Order();
  $order->loadByOuid($_GET['ouid']);
  if(empty($order->id)) {
    echo "<h2>This order is no longer in the system</h2>";
    exit();
  }
}

// Process Affiliate Payments
// Begin processing affiliate information
if(Cart66Session::get('ap_id')) {
  $referrer = Cart66Session::get('ap_id');
}
elseif(isset($_COOKIE['ap_id'])) {
  $referrer = $_COOKIE['ap_id'];
}

if($order->viewed == 0){
  // only process affiliate logging if this is the first time the receipt is viewed
  if (!empty($referrer)) {
    Cart66Common::awardCommission($order->id, $referrer);
  }
  // End processing affiliate information

  // Begin iDevAffiliate Tracking
  if(CART66_PRO && $url = Cart66Setting::getValue('idevaff_url')) {
    require_once(CART66_PATH . "/pro/idevaffiliate-award.php");
  }
  // End iDevAffiliate Tracking
  if(isset($_COOKIE['ap_id']) && $_COOKIE['ap_id']) {
    setcookie('ap_id',$referrer, time() - 3600, "/");
    unset($_COOKIE['ap_id']);
  }
  Cart66Session::drop('app_id');
}



if(isset($_GET['duid'])) {
  $duid = $_GET['duid'];
  $product = new Cart66Product();
  if($product->loadByDuid($duid)) {
    $okToDownload = true;
    if($product->download_limit > 0) {
      // Check if download limit has been exceeded
      if($product->countDownloadsForDuid($duid) >= $product->download_limit) {
        $okToDownload = false;
      }
    }
    
    if($okToDownload) {
      $data = array(
        'duid' => $duid,
        'downloaded_on' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR']
      );
      $downloadsTable = Cart66Common::getTableName('downloads');
      $wpdb->insert($downloadsTable, $data, array('%s', '%s', '%s'));
      
      $setting = new Cart66Setting();
      
      if(!empty($product->s3Bucket) && !empty($product->s3File)) {
        require_once(CART66_PATH . '/models/Cart66AmazonS3.php');
        $link = Cart66AmazonS3::prepareS3Url($product->s3Bucket, $product->s3File, '1 minute');
        wp_redirect($link);
        exit();
      }
      else {
        $dir = Cart66Setting::getValue('product_folder');
        $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
        Cart66Common::downloadFile($path);
      }
      
    }
    else {
      _e("You have exceeded the maximum number of downloads for this product","cart66");
    }
    exit();
  }
}
?>

<?php  if($order !== false): ?>
<h2><?php _e( 'Order Number' , 'cart66' ); ?>: <?php echo $order->trans_id ?></h2>

<?php 
if(CART66_PRO) {
  $logInLink = Cart66AccessManager::getLogInLink();
  if(Cart66Session::get('Cart66LoginInfo') && $logInLink !== false) {
    echo '<h2>Your Account</h2>';
    echo "<p><a href=\"$logInLink\">Log into your account</a>.</p>";
  }
}
?>

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
      <p>
        <strong><?php _e( 'Billing Information' , 'cart66' ); ?></strong><br/>
      <?php echo $order->bill_first_name ?> <?php echo $order->bill_last_name ?><br/>
      <?php echo $order->bill_address ?><br/>
      <?php if(!empty($order->bill_address2)): ?>
        <?php echo $order->bill_address2 ?><br/>
      <?php endif; ?>

      <?php if(!empty($order->bill_city)): ?>
        <?php echo $order->bill_city ?> <?php echo $order->bill_state ?>, <?php echo $order->bill_zip ?><br/>
      <?php endif; ?>
      
      <?php if(!empty($order->bill_country)): ?>
        <?php echo $order->bill_country ?><br/>
      <?php endif; ?>
      </p>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign="top">
      <p><strong><?php _e( 'Contact Information' , 'cart66' ); ?></strong><br/>
      <?php if(!empty($order->phone)): ?>
        <?php _e( 'Phone' , 'cart66' ); ?>: <?php echo Cart66Common::formatPhone($order->phone) ?><br/>
      <?php endif; ?>
      <?php _e( 'Email' , 'cart66' ); ?>: <?php echo $order->email ?><br/>
      <?php _e( 'Date' , 'cart66' ); ?>: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
      </p>
    </td>
  </tr>
  <tr>
    <td>
      <?php if($order->shipping_method != 'None'): ?>
        <?php if($order->hasShippingInfo()): ?>
          
          <p><strong><?php _e( 'Shipping Information' , 'cart66' ); ?></strong><br/>
          <?php echo $order->ship_first_name ?> <?php echo $order->ship_last_name ?><br/>
          <?php echo $order->ship_address ?><br/>
      
          <?php if(!empty($order->ship_address2)): ?>
            <?php echo $order->ship_address2 ?><br/>
          <?php endif; ?>
      
          <?php if($order->ship_city != ''): ?>
            <?php echo $order->ship_city ?> <?php echo $order->ship_state ?>, <?php echo $order->ship_zip ?><br/>
          <?php endif; ?>
      
          <?php if(!empty($order->ship_country)): ?>
            <?php echo $order->ship_country ?><br/>
          <?php endif; ?>
      
        <?php endif; ?>
      
      <br/><em><?php _e( 'Delivery via' , 'cart66' ); ?>: <?php echo $order->shipping_method ?></em><br/>
      </p>
      <?php endif; ?>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>


<table id='viewCartTable' cellspacing="0" cellpadding="0">
  <tr>
    <th style='text-align: left;'><?php _e( 'Product' , 'cart66' ); ?></th>
    <th style='text-align: center;'><?php _e( 'Quantity' , 'cart66' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Price' , 'cart66' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Total' , 'cart66' ); ?></th>
  </tr>
  <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
    <script type="text/javascript">
      /* <![CDATA[ */
	    _gaq.push(['_addTrans',
	    
	      '<?php echo $order->trans_id; ?>',
	      '<?php echo get_bloginfo("name"); ?>',
	      '<?php echo number_format($order->total, 2, ".", ""); ?>',
	      '<?php echo number_format($order->tax, 2, ".", ""); ?>',
	      '<?php echo $order->shipping; ?>',
	      '<?php echo $order->ship_city; ?>',
	      '<?php echo $order->ship_state; ?>',
	      '<?php echo $order->ship_country; ?>'
	    ]);
	  /* ]]> */
    </script>  
  <?php endif;?>
  <?php foreach($order->getItems() as $item): ?>
    <?php 
      $product->load($item->product_id);
      $price = $item->product_price * $item->quantity;
    ?>
    <tr>
      <td>
        <?php echo nl2br($item->description) ?>
        <?php
          $product->load($item->product_id);
          if($product->isDigital()) {
            $receiptPage = get_page_by_path('store/receipt');
            $receiptPageLink = get_permalink($receiptPage);
            $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
            echo "<br/><a href='$receiptPageLink'>Download</a>";
          }
        ?>
        
      </td>
      <td style='text-align: center;'><?php echo $item->quantity ?></td>
      <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2) ?></td>
      <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
    </tr>
    <?php
      if(!empty($item->form_entry_ids)) {
        $entries = explode(',', $item->form_entry_ids);
        foreach($entries as $entryId) {
          if(class_exists('RGFormsModel')) {
            if(RGFormsModel::get_lead($entryId)) {
              echo "<tr><td colspan='4'><div class='Cart66GravityFormDisplay'>" . Cart66GravityReader::displayGravityForm($entryId) . "</div></td></tr>";
            }
          }
          else {
            echo "<tr><td colspan='5' style='color: #955;'>This order requires Gravity Forms in order to view all of the order information</td></tr>";
          }
        }
      }
    ?>
    <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
      <script type="text/javascript">
        /* <![CDATA[ */
        _gaq.push(['_addItem',
          '<?php echo $order->trans_id; ?>',
          '<?php echo $product->item_number; ?>',
          '<?php echo nl2br($item->description) ?>',
          '', // Item Category
          '<?php echo number_format($item->product_price, 2, ".", "") ?>',
          '<?php echo $item->quantity ?>'
        ]);
        /* ]]> */
      </script>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if(Cart66Setting::getValue('enable_google_analytics') == 1 && $order->viewed == 0): ?>
    <script type="text/javascript">
    /* <![CDATA[ */
  	  _gaq.push(['_trackTrans']);
    /* ]]> */
    </script>
  <?php endif; ?>
  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Subtotal' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $order->subtotal; ?></td>
  </tr>
  
  <?php if($order->shipping_method != 'None' && $order->shipping_method != 'Download'): ?>
  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Shipping' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $order->shipping; ?></td>
  </tr>
  <?php endif; ?>
  
  <?php if($order->discount_amount > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Discount' , 'cart66' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;">-<?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->discount_amount, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <?php if($order->tax > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Tax' , 'cart66' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <tr class="noBorder">
    <td colspan='2' style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Total' , 'cart66' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo CART66_CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
  </tr>
</table>

<p><a href='#' id="print_version"><?php _e( 'Printer Friendly Receipt' , 'cart66' ); ?></a></p>

<?php
  // Erase the shopping cart from the session at the end of viewing the receipt
  Cart66Session::drop('Cart66Cart');
  Cart66Session::drop('Cart66Tax');
?>
<?php else: ?>
  <p><?php _e( 'Receipt not available' , 'cart66' ); ?></p>
<?php endif; ?>


<?php
  if($order !== false) {
    $printView = Cart66Common::getView('views/receipt_print_version.php', array('order' => $order));
    $printView = str_replace("\n", '', $printView);
    $printView = str_replace("'", '"', $printView);
    ?>
    <script type="text/javascript">
    /* <![CDATA[ */
      (function($){
        $(document).ready(function(){
          $('#print_version').click(function() {
            myWindow = window.open('','Your_Receipt','resizable=yes,scrollbars=yes,width=550,height=700');
            myWindow.document.open("text/html","replace");
            myWindow.document.write(decodeURIComponent('<?php echo rawurlencode($printView); ?>' + ''));
            return false;
          });
        })
      })(jQuery);
    /* ]]> */
    </script> 
  <?php
  }
  ?>
  <?php 
  if(Cart66Setting::getValue('enable_google_analytics') == 1): ?>
    <?php
      $url = admin_url('admin-ajax.php');
      if(Cart66Common::isHttps()) {
        $url = preg_replace('/http[s]*:/', 'https:', $url);
      }
      else {
        $url = preg_replace('/http[s]*:/', 'http:', $url);
      }
    ?>
    <?php if(Cart66Setting::getValue('use_other_analytics_plugin') == 'no'): ?>
      <script type="text/javascript">
        /* <![CDATA[ */
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        /* ]]> */
      </script>
    <?php endif; ?>
  <?php endif; ?>