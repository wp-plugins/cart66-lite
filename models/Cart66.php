<?php
class Cart66 {
  
  public function install() {
    global $wpdb;
    $prefix = Cart66Common::getTablePrefix();
    $sqlFile = CART66_PATH . '/sql/database.sql';
    $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
    $queries = explode(";\n", $sql);
    $wpdb->hide_errors();
    foreach($queries as $sql) {
      if(strlen($sql) > 5) {
        $wpdb->query($sql);
        Cart66Common::log("Running: $sql");
      }
    }
    require_once(CART66_PATH . "/create-pages.php");

    // Set the version number for this version of Cart66
    require_once(CART66_PATH . "/models/Cart66Setting.php");
    Cart66Setting::setValue('version', CART66_VERSION_NUMBER);
    
    // Look for hard coded order number
    if(CART66_PRO && CART66_ORDER_NUMBER !== false) {
      Cart66Setting::setValue('order_number', CART66_ORDER_NUMBER);
      $versionInfo = Cart66ProCommon::getVersionInfo();
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to register order number: " . 
        CART66_ORDER_NUMBER . print_r($versionInfo, true));
      if(!$versionInfo) {
        Cart66Setting::setValue('order_number', '');
      }
    }
  }
  
  public function init() {
    $this->loadCoreModels();
    $this->initCurrencySymbols();
    
    // Verify that upgrade has been run
    if(IS_ADMIN) {
      $dbVersion = Cart66Setting::getValue('version');
      if(version_compare(CART66_VERSION_NUMBER, $dbVersion)) {
        $this->install();
      }
    }
    
    // Set default admin page roles if there isn't any
    $pageRoles = Cart66Setting::getValue('admin_page_roles');
    if(empty($pageRoles)){
     $defaultPageRoles = array(
        'orders' => 'edit_pages',
        'products' => 'manage_options',
        'paypal-subscriptions' => 'manage_options',
        'inventory' => 'manage_options',
        'promotions' => 'manage_options',
        'shipping' => 'manage_options',
        'settings' => 'manage_options',
        'reports' => 'manage_options',
        'accounts' => 'manage_options'
     ); Cart66Setting::setValue('admin_page_roles',serialize($defaultPageRoles)); 
    }
    
    // Define debugging and testing info
    $cart66Logging = Cart66Setting::getValue('enable_logging') ? true : false;
    $sandbox = Cart66Setting::getValue('paypal_sandbox') ? true : false;
    define("CART66_DEBUG", $cart66Logging);
    define("SANDBOX", $sandbox);
    
    // Ajax actions
    if(CART66_PRO) {
      add_action('wp_ajax_check_inventory_on_add_to_cart', array('Cart66Ajax', 'checkInventoryOnAddToCart'));
      add_action('wp_ajax_nopriv_check_inventory_on_add_to_cart', array('Cart66Ajax', 'checkInventoryOnAddToCart'));
    }
    
    // Handle dynamic JS requests
    // See: http://ottopress.com/2010/dont-include-wp-load-please/ for why
    add_filter('query_vars', array($this, 'addAjaxTrigger'));
    add_action('template_redirect', array($this, 'ajaxTriggerCheck'));
    
    if(IS_ADMIN) {
      //add_action( 'admin_notices', 'cart66_data_collection' );
      add_action('admin_head', array( $this, 'registerBasicScripts'));

      if(strpos($_SERVER['QUERY_STRING'], 'page=cart66') !== false) {
        add_action('admin_head', array($this, 'registerAdminStyles'));
        add_action('admin_init', array($this, 'registerCustomScripts'));
      }
      
      add_action('admin_menu', array($this, 'buildAdminMenu'));
      // we dont use this button anymore
      //add_action('admin_init', array($this, 'addEditorButtons'));
      add_action('admin_init', array($this, 'forceDownload'));
      add_action('wp_ajax_save_settings', array('Cart66Ajax', 'saveSettings'));
      add_action('wp_ajax_promotionProductSearch', array('Cart66Ajax', 'promotionProductSearch'));
      add_action('wp_ajax_loadPromotionProducts', array('Cart66Ajax', 'loadPromotionProducts'));
      
      // Load Dialog Box in editor
      add_action('media_buttons', array('Cart66Dialog', 'cart66_dialog_box'), 11);
      add_action('admin_footer',  array('Cart66Dialog', 'add_shortcode_popup'));
      
      // Load Dashboard Widget
      $pageRoles = Cart66Setting::getValue('admin_page_roles');
      $pageRoles = unserialize($pageRoles);
      global $current_user;
      if(current_user_can($pageRoles['orders'])) {
        add_action('wp_dashboard_setup', array('Cart66Dashboard', 'cart66_add_dashboard_widgets' ));
      }
      
      if(CART66_PRO) {
        add_action('wp_ajax_update_gravity_product_quantity_field', array('Cart66Ajax', 'updateGravityProductQuantityField'));
      }
      
      
      if(class_exists('SpreedlySubscription') || true) {
        add_action('save_post', array($this, 'saveFeatureLevelMetaBoxData'));
        add_action('add_meta_boxes', array($this, 'addFeatureLevelMetaBox'));
      }
      
      
      //Plugin update actions
      if(CART66_PRO) {
        add_action('update_option__transient_update_plugins', array('Cart66ProCommon', 'checkUpdate'));             //used by WP 2.8
        add_filter('pre_set_site_transient_update_plugins', array('Cart66ProCommon', 'getUpdatePluginsOption'));    //used by WP 3.0
        add_action('install_plugins_pre_plugin-information', array('Cart66ProCommon', 'showChangelog'));
      }
    }
    else {
      $this->initShortcodes();
      $this->initCart();
      $order = new Cart66Order();
      add_action('wp_enqueue_scripts', array('Cart66', 'enqueueScripts'));

      if(CART66_PRO) {
        add_action('wp_head', array($this, 'checkInventoryOnCheckout'));
        add_action('wp_head', array($this, 'checkShippingMethodOnCheckout'));
        add_action('wp_head', array($this, 'checkZipOnCheckout'));
        add_action('wp_head', array($this, 'checkTermsOnCheckout'));
        add_action('template_redirect', array($this, 'protectSubscriptionPages'));
        add_filter('wp_list_pages_excludes', array($this, 'hideStorePages'));
        add_filter('wp_list_pages_excludes', array($this, 'hidePrivatePages'));
        add_filter('wp_nav_menu_items', array($this, 'filterPrivateMenuItems'), 10, 2);
      }
      
      add_action('wp_head', array($this, 'displayVersionInfo'));
      add_action('template_redirect', array($this, 'dontCacheMeBro'));
      add_action('shutdown', array('Cart66Session', 'touch'));
      
      add_action('wp_footer', array($order, 'updateViewed'));
      if(Cart66Setting::getValue('use_other_analytics_plugin') == 'no') {
        add_action('wp_footer', array($order, 'addTrackingCode'));
      }
    }
    
    
    
    function cart66_data_collection(){
         global $current_screen;
         
         echo '<div class="updated">';
         echo '<script type="text/javascript">
          (function($){
            $(document).ready(function(){
              $("#cart66SendSurvey").click(function(){
                $.get("http://cart66.com/survey/",function(data){
                  alert(data)
                })
              })
            })
          })(jQuery);
         </script>  ';
         echo '<H3>Cart66 Usage Survey</h3>';
         echo '<p>To improve our customer experience, Cart66 would love for you to participate in an anonymous usage survey. This data will be sent one time, and does not contain any personal or identification information.</p>';
         echo '<p>Here\'s what is being sent:<br><br>';
         echo Cart66Common::showReportData();
         echo '<p><a id="cart66SendSurvey" class="button" href="#">Send</a> &nbsp;&nbsp;&nbsp; <a class="button" href="#">No thanks</a></p>';
         echo '</div>';
    }
    
    
    
    // ================================================================
    // = Intercept query string cart66 tasks                          =
    // ================================================================
     
    // Logout the logged in user
    $isLoggedIn = Cart66Common::isLoggedIn();
    if(isset($_REQUEST['cart66-task']) && $_REQUEST['cart66-task'] == 'logout' && $isLoggedIn) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Intercepting Cart66 Logout task");
      $url = Cart66ProCommon::getLogoutUrl();
      Cart66Account::logout($url);
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'GET' &&  Cart66Common::getVal('task') == 'member_download') {
      if(Cart66Common::isLoggedIn()) {
        $path = $_GET['path'];
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempting a member download file request: $path");
        Cart66Common::downloadFile($path);
      }
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Common::getVal('task') == 'add-to-cart-anchor') {
      $options = null;
      if(isset($_GET['options'])) {
        $options = Cart66Common::getVal('options');
      }
      Cart66Session::get('Cart66Cart')->addItem(Cart66Common::getVal('cart66ItemId'), 1, $options);
    }
    
  }
  
  public function displayVersionInfo() {
    if(CART66_PRO) {
      echo '<meta name="Cart66Version" content="Professional ' . Cart66Setting::getValue('version') . '" />' . "\n";
    }
    else {
      echo '<meta name="Cart66Version" content="Lite ' . Cart66Setting::getValue('version') . '" />' . "\n";
    }
  }
  
  public function filterPrivateMenuItems($menuItems, $args=null) {
    $links = explode("</li>", $menuItems);
    $filteredMenuItems = '';
    
    if(Cart66Common::isLoggedIn()) {
      // User is logged in so hide the guest only pages
      $pageIds = Cart66AccessManager::getGuestOnlyPageIds();
    }
    else {
      // User is not logged in so hide the private pages
      $pageIds = Cart66AccessManager::getPrivatePageIds();
    }
    
    foreach($links as $link) {
      $addLink = true;
      $link = trim($link);
      
      if(empty($link)) {
        $addLink = false;
      }
      else {
        foreach($pageIds as $pageId) {
          $permalink = get_permalink($pageId);
          if(strpos($link, $permalink) !== false) {
            $addLink = false;
            break;
          }
        }
      }
         
      if($addLink) {
        $filteredMenuItems .= "$link</li>";
      }
    }
    
    return $filteredMenuItems;
  }
  
  public static function enqueueScripts() {
    $url = CART66_URL . '/cart66.css';
    wp_enqueue_style('cart66-css', $url, null, CART66_VERSION_NUMBER, 'all');

    if($css = Cart66Setting::getValue('styles_url')) {
      wp_enqueue_style('cart66-custom-css', $css, null, CART66_VERSION_NUMBER, 'all');
    }
    
    // Include the cart66 javascript library
    $path = CART66_URL . '/js/cart66-library.js';
    wp_enqueue_script('cart66-library', $path, array('jquery'), CART66_VERSION_NUMBER);
  }
  
  public function loadCoreModels() {
    require_once(CART66_PATH . "/models/Cart66BaseModelAbstract.php");
    require_once(CART66_PATH . "/models/Cart66ModelAbstract.php");
    require_once(CART66_PATH . "/models/Cart66Session.php");
    require_once(CART66_PATH . "/models/Cart66Setting.php");
    require_once(CART66_PATH . "/models/Cart66Admin.php");
    require_once(CART66_PATH . "/models/Cart66Ajax.php");
    require_once(CART66_PATH . "/models/Cart66Log.php");
    require_once(CART66_PATH . "/models/Cart66Product.php");
    require_once(CART66_PATH . "/models/Cart66CartItem.php");
    require_once(CART66_PATH . "/models/Cart66Cart.php");
    require_once(CART66_PATH . "/models/Cart66CartWidget.php");
    require_once(CART66_PATH . "/models/Cart66CheckoutThrottle.php");
    require_once(CART66_PATH . "/models/Cart66Exception.php");
    require_once(CART66_PATH . "/models/Cart66TaxRate.php");
    require_once(CART66_PATH . "/models/Cart66Order.php");
    require_once(CART66_PATH . "/models/Cart66Promotion.php");
    require_once(CART66_PATH . "/models/Cart66ShippingMethod.php");
    require_once(CART66_PATH . "/models/Cart66ShippingRate.php");
    require_once(CART66_PATH . "/models/Cart66ShippingRule.php");
    require_once(CART66_PATH . "/models/Cart66ShortcodeManager.php");
    require_once(CART66_PATH . "/models/Cart66ButtonManager.php");
    require_once(CART66_PATH . "/models/Cart66Dashboard.php");
    require_once(CART66_PATH . "/models/Cart66Dialog.php");
    require_once(CART66_PATH . "/models/Cart66Updater.php");
    require_once(CART66_PATH . "/gateways/Cart66GatewayAbstract.php");
    require_once(CART66_PATH . "/gateways/Cart66PayPalExpressCheckout.php");
    require_once(CART66_PATH . "/models/Cart66Updater.php");
    
    if(CART66_PRO) {
      require_once(CART66_PATH . "/pro/models/Cart66AccessManager.php");
      require_once(CART66_PATH . "/pro/models/Cart66AccountSubscription.php");
      require_once(CART66_PATH . "/pro/models/Cart66Account.php");
      require_once(CART66_PATH . "/pro/models/Cart66GravityReader.php");
      require_once(CART66_PATH . "/pro/models/Cart66LiveRate.php");
      require_once(CART66_PATH . "/pro/models/Cart66LiveRates.php");
      require_once(CART66_PATH . "/pro/gateways/Cart66PayPalPro.php");
      require_once(CART66_PATH . "/pro/models/Cart66PayPalRecurringPayment.php");
      require_once(CART66_PATH . "/pro/models/Cart66PayPalSubscription.php");
      require_once(CART66_PATH . "/pro/models/Cart66Ups.php");
      require_once(CART66_PATH . "/pro/models/Cart66Usps.php");
      require_once(CART66_PATH . "/pro/models/Cart66MailChimp.php");
      
      // Load Constant Contact classes
      if(Cart66Setting::getValue('constantcontact_username')) {
        require_once(CART66_PATH . "/pro/models/Cart66ConstantContact.php");
        //require_once(CART66_PATH . "/pro/models/Cart66ConstantContactWrapper.php");
      }
    }

    require_once(CART66_PATH . "/gateways/Cart66GatewayAbstract.php");
    
    self::loadSpreedlyModels();
    
    if(CART66_PRO && Cart66Setting::getValue('zendesk_token')) {
      require_once(CART66_PATH . "/pro/models/ZendeskRemoteAuth.php");
    }
  }
  
  public function loadSpreedlyModels() {
    $shortName = Cart66Setting::getValue('spreedly_shortname');
    $apiToken = Cart66Setting::getValue('spreedly_apitoken');
    if(CART66_PRO && $shortName && $apiToken) {
      require_once(CART66_PATH . "/pro/models/SpreedlyCommon.php");
      require_once(CART66_PATH . "/pro/models/SpreedlyCreditCard.php");
      require_once(CART66_PATH . "/pro/models/SpreedlyException.php");
      require_once(CART66_PATH . "/pro/models/SpreedlyInvoice.php");
      require_once(CART66_PATH . "/pro/models/SpreedlySubscriber.php");
      require_once(CART66_PATH . "/pro/models/SpreedlySubscription.php");
      require_once(CART66_PATH . "/pro/models/SpreedlyXmlObject.php");
      SpreedlyCommon::init($shortName, $apiToken);
    }
  }
  
  public function initCurrencySymbols() {
    $cs = Cart66Setting::getValue('CART66_CURRENCY_SYMBOL');
    $cs = $cs ? $cs : '$';
    $cst = Cart66Setting::getValue('CART66_CURRENCY_SYMBOL_text');
    $cst = $cst ? $cst : '$';
    $ccd = Cart66Setting::getValue('currency_code');
    $ccd = $ccd ? $ccd : 'USD';
    define("CART66_CURRENCY_SYMBOL", $cs);
    define("CART66_CURRENCY_SYMBOL_TEXT", $cst);
    define("CURRENCY_CODE", $ccd);
  }
  
  public function registerBasicScripts() {
    ?><script type="text/javascript">var wpurl = '<?php echo esc_js( home_url('/') ); ?>';</script><?php
    $dashboardCss = CART66_URL . '/admin/dashboard.css';
    echo "<link rel='stylesheet' type='text/css' href='$dashboardCss' />\n";
  }
  
  public function registerCustomScripts() {
    if(strpos($_SERVER['QUERY_STRING'], 'page=cart66') !== false) {
      $path = CART66_URL . '/js/ajax-setting-form.js';
      wp_enqueue_script('ajax-setting-form', $path);

      // Include jquery-multiselect, jquery-datepicker, jquery-timepicker-addon and jquery-ui
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-sortable');
      $path = CART66_URL . '/js/ui.multiselect.js';
      wp_enqueue_script('jquery-multiselect', $path, null, null, true);
      $path = CART66_URL . '/js/jquery-ui.core.datepicker.slider.js';
      wp_enqueue_script('jquery-datepicker', $path, null, null, true);
      $path = CART66_URL . '/js/ui.timepicker.addon.js';
      wp_enqueue_script('jquery-timepicker-addon', $path, null, null, true);
      $path = CART66_URL . '/js/jquery.tokeninput.js';
      wp_enqueue_script('jquery-tokeninput', $path, null, null, true);
 
      // Include the jquery table quicksearch library
      $path = CART66_URL . '/js/jquery.quicksearch.js';
      wp_enqueue_script('quicksearch', $path, array('jquery'));
      
    }
  }
  
  public function registerAdminStyles() {
    if(strpos($_SERVER['QUERY_STRING'], 'page=cart66') !== false) {
      $widgetCss = WPURL . '/wp-admin/css/widgets.css';
      echo "<link rel='stylesheet' type='text/css' href='$widgetCss' />\n";

    	$adminCss = CART66_URL . '/admin/admin-styles.css';
      echo "<link rel='stylesheet' type='text/css' href='$adminCss' />\n";

      $uiCss = CART66_URL . '/admin/jquery-ui-1.7.1.custom.css';
      echo "<link rel='stylesheet' type='text/css' href='$uiCss' />\n";
    }
  }
  
  public function dontCacheMeBro() {
    if(!IS_ADMIN) {
      global $post;
      $sendHeaders = false;
      if($disableCaching = Cart66Setting::getValue('disable_caching')) {
        if($disableCaching === '1') {
          $cartPage = get_page_by_path('store/cart');
          $checkoutPage = get_page_by_path('store/checkout');
          $cartPages = array($checkoutPage->ID, $cartPage->ID);
          if( isset( $post->ID ) && in_array($post->ID, $cartPages) ) {
            $sendHeaders = true;
            //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] set to send no cache headers for cart pages");
          }
          else {
            if(!isset($post->ID)) {
              Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The POST ID is not set");
            }
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not a cart page! Therefore need to set the headers to disable cache");
          }
        }
        elseif($disableCaching === '2') {
          $sendHeaders = true;
        }
      }
      
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Disable caching is: $disableCaching");
      
      if($sendHeaders) {
        // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Sending no cache headers");
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
      }
      
    }
  }

  /**
   * Put Cart66 in the admin menu
   */
  public function buildAdminMenu() {
    $icon = CART66_URL . '/images/cart66_logo_16.gif';
    $pageRoles = Cart66Setting::getValue('admin_page_roles');
    $pageRoles = unserialize($pageRoles);
    
    add_menu_page('Cart66', 'Cart66', $pageRoles['orders'], 'cart66_admin', null, $icon);
    add_submenu_page('cart66_admin', __('Orders', 'cart66'), __('Orders', 'cart66'), $pageRoles['orders'], 'cart66_admin', array('Cart66Admin', 'ordersPage'));
    add_submenu_page('cart66_admin', __('Products', 'cart66'), __('Products', 'cart66'), $pageRoles['products'], 'cart66-products', array('Cart66Admin', 'productsPage'));
    add_submenu_page('cart66_admin', __('PayPal Subscriptions', 'cart66'), __('PayPal Subscriptions', 'cart66'), $pageRoles['paypal-subscriptions'], 'cart66-paypal-subscriptions', array('Cart66Admin', 'paypalSubscriptions'));
    add_submenu_page('cart66_admin', __('Inventory', 'cart66'), __('Inventory', 'cart66'), $pageRoles['inventory'], 'cart66-inventory', array('Cart66Admin', 'inventoryPage'));
    add_submenu_page('cart66_admin', __('Promotions', 'cart66'), __('Promotions', 'cart66'), $pageRoles['promotions'], 'cart66-promotions', array('Cart66Admin', 'promotionsPage'));
    add_submenu_page('cart66_admin', __('Shipping', 'cart66'), __('Shipping', 'cart66'), $pageRoles['shipping'], 'cart66-shipping', array('Cart66Admin', 'shippingPage'));
    add_submenu_page('cart66_admin', __('Settings', 'cart66'), __('Settings', 'cart66'), $pageRoles['settings'], 'cart66-settings', array('Cart66Admin', 'settingsPage'));
    add_submenu_page('cart66_admin', __('Reports', 'cart66'), __('Reports', 'cart66'), $pageRoles['reports'], 'cart66-reports', array('Cart66Admin', 'reportsPage'));
    add_submenu_page('cart66_admin', __('Accounts', 'cart66'), __('Accounts', 'cart66'), $pageRoles['accounts'], 'cart66-accounts', array('Cart66Admin', 'accountsPage'));
  }
  

  /**
   * Check inventory levels when accessing the checkout page.
   * If inventory is insufficient place a warning message in Cart66Session::get('Cart66InventoryWarning')
   */
  public function checkInventoryOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        $inventoryMessage = Cart66Session::get('Cart66Cart')->checkCartInventory();
        if(!empty($inventoryMessage)) { Cart66Session::set('Cart66InventoryWarning', $inventoryMessage); }
      }
    }
  }
  
  public function checkShippingMethodOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      
      if(!Cart66Setting::getValue('use_live_rates')) {
        Cart66Session::drop('Cart66LiveRates');
      }
      
      if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        if(Cart66Session::get('Cart66LiveRates') && get_class(Cart66Session::get('Cart66LiveRates')) == 'Cart66LiveRates') {
          if(!Cart66Session::get('Cart66LiveRates')->hasValidShippingService()) {
            Cart66Session::set('Cart66ShippingWarning', true);
            $viewCartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($viewCartPage->ID);
            wp_redirect($viewCartLink);
            exit();
          }
        }
      }
    }
  }
  
  public function checkTermsOnCheckout() {
      if(Cart66Setting::getValue('require_terms') == 1) {
        global $post;
        $checkoutPage = get_page_by_path('store/checkout');
        $cartPage = get_page_by_path('store/cart');
        if( isset( $post->ID ) && $post->ID == $checkoutPage->ID || $post->ID == $cartPage->ID) {
          
          $sendBack = false;
         
          if($post->ID == $cartPage->ID && isset($_POST['terms_acceptance']) && $_POST['terms_acceptance'] == "I_Accept"){
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms are accepted, forwarding to checkout");
            Cart66Session::set("terms_acceptance","accepted",true);
            $link = get_permalink($checkoutPage->ID);
            $sendBack = true;
          }
          elseif($post->ID == $checkoutPage->ID && (!Cart66Session::get('terms_acceptance') || Cart66Session::get('terms_acceptance') != "accepted")) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms not accepted, send back to cart");
            $link = get_permalink($cartPage->ID);
            $sendBack = true;
          }
        
          if($sendBack) {
            wp_redirect($link);
            exit();
          }
          
        } // End if checkout or cart page
      } // End if require terms
  }
  
  public function checkZipOnCheckout() {
    if(CART66_PRO && $_SERVER['REQUEST_METHOD'] == 'GET') {
      if(Cart66Setting::getValue('use_live_rates') && Cart66Session::get('Cart66Cart')->requireShipping()) {
        global $post;
        $checkoutPage = get_page_by_path('store/checkout');
        if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
          $cartPage = get_page_by_path('store/cart');
          $link = get_permalink($cartPage->ID);
          $sendBack = false;
          
          if(!Cart66Session::get('cart66_shipping_zip')) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping zip in session");
            Cart66Session::set('Cart66ZipWarning', true);
            $sendBack = true;
          }
          elseif(!Cart66Session::get('cart66_shipping_country_code')) {
            Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping country code in session");
            Cart66Session::set('Cart66ShippingWarning', true);
            $sendBack = true;
          }
          
          if($sendBack) {
            wp_redirect($link);
            exit();
          }
          
        } // End if checkout page
      } // End if using live rates
    } // End if GET
  }
  
  /**
   *  Add Cart66 to the TinyMCE editor
   */
  public function addEditorButtons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
    return;

    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true') {
      add_filter('mce_external_plugins', array('Cart66', 'addTinymcePlugin'));
      add_filter('mce_buttons', array('Cart66','registerEditorButton'));
    }
  }

  public function registerEditorButton($buttons) {
    array_push($buttons, "|", "cart66");
    return $buttons;
  }

  public function addTinymcePlugin($plugin_array) {
    $plugin_array['cart66'] = CART66_URL . '/js/editor_plugin_src.js';
    return $plugin_array;
  }
  
  /**
   * Load the cart from the session or put a new cart in the session
   */
  public function initCart() {

    if(!Cart66Session::get('Cart66Cart')) {
      Cart66Session::set('Cart66Cart', new Cart66Cart());
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a new Cart66Cart OBJECT for the database session.");
    }

    if(isset($_POST['task'])) {
      if($_POST['task'] == 'addToCart') {
        Cart66Session::get('Cart66Cart')->addToCart();
      }
      elseif($_POST['task'] == 'updateCart') {
        Cart66Session::get('Cart66Cart')->updateCart();
      }
    }
    elseif(isset($_GET['task'])) {
      if($_GET['task']=='removeItem') {
        $itemIndex = Cart66Common::getVal('itemIndex');
        Cart66Session::get('Cart66Cart')->removeItem($itemIndex);
      }
    }
    elseif(isset($_POST['cart66-action'])) {
      $task = Cart66Common::postVal('cart66-action');
      if($task == 'authcheckout') {
        $inventoryMessage = Cart66Session::get('Cart66Cart')->checkCartInventory();
        if(!empty($inventoryMessage)) { Cart66Session::set('Cart66InventoryWarning', $inventoryMessage); }
      }
    }
    
  }
  
  public function initShortcodes() {
    $sc = new Cart66ShortcodeManager();
    add_shortcode('account_login',                array($sc, 'accountLogin'));
    add_shortcode('account_logout',               array($sc, 'accountLogout'));
    add_shortcode('account_logout_link',          array($sc, 'accountLogoutLink'));
    add_shortcode('account_info',                 array($sc, 'accountInfo'));
    add_shortcode('add_to_cart',                  array($sc, 'showCartButton'));
    add_shortcode('add_to_cart_anchor',           array($sc, 'showCartAnchor'));
    add_shortcode('cart',                         array($sc, 'showCart'));
    add_shortcode('cart66_download',              array($sc, 'downloadFile'));
    add_shortcode('cancel_paypal_subscription',   array($sc, 'cancelPayPalSubscription'));
    add_shortcode('checkout_authorizenet',        array($sc, 'authCheckout'));
    add_shortcode('checkout_manual',              array($sc, 'manualCheckout'));
    add_shortcode('checkout_payleap',             array($sc, 'payLeapCheckout'));
    add_shortcode('checkout_paypal',              array($sc, 'paypalCheckout'));
    add_shortcode('checkout_paypal_express',      array($sc, 'payPalExpressCheckout'));
    add_shortcode('checkout_paypal_pro',          array($sc, 'payPalProCheckout'));
    add_shortcode('clear_cart',                   array($sc, 'clearCart'));
    add_shortcode('hide_from',                    array($sc, 'hideFrom'));
    add_shortcode('post_sale',                    array($sc, 'postSale'));
    add_shortcode('shopping_cart',                array($sc, 'shoppingCart'));
    add_shortcode('show_to',                      array($sc, 'showTo'));
    add_shortcode('subscription_name',            array($sc, 'currentSubscriptionPlanName'));
    add_shortcode('subscription_feature_level',   array($sc, 'currentSubscriptionFeatureLevel'));
    add_shortcode('zendesk_login',                array($sc, 'zendeskRemoteLogin'));
    add_shortcode('terms_of_service',             array($sc, 'termsOfService'));
    add_shortcode('account_expiration',           array($sc, 'accountExpiration'));
    
    // System shortcodes
    add_shortcode('cart66_tests',                 array($sc, 'cart66Tests'));
    add_shortcode('express',                      array($sc, 'payPalExpress'));
    add_shortcode('ipn',                          array($sc, 'processIPN'));
    add_shortcode('receipt',                      array($sc, 'showReceipt'));
    add_shortcode('spreedly_listener',            array($sc, 'spreedlyListener'));
    add_shortcode('checkout_eway',                array($sc, 'ewayCheckout'));
    add_shortcode('checkout_mwarrior',            array($sc, 'mwarriorCheckout'));

    
    // Enable Gravity Forms hooks if Gravity Forms is available
    if(CART66_PRO && class_exists('RGForms')) {
      add_action("gform_post_submission", array($sc, 'gravityFormToCart'), 100, 1);
    }
    
  }
  
  /**
   * Adds a query var trigger for the dynamic JS dialog
   */
  public function addAjaxTrigger($vars) {
    $vars[] = 'cart66AjaxCartRequests';
    return $vars;
  }

  /**
   * Handles the query var trigger for the dyamic JS dialog
   */
  public function ajaxTriggerCheck() {
    if ( intval( get_query_var( 'cart66AjaxCartRequests' ) ) == 1 ) {
      Cart66Ajax::checkInventoryOnAddToCart();
      exit;
    }
  }

  /**
   * Register Cart66 cart sidebar widget
   */
  public function registerCartWidget() {
    register_widget('Cart66CartWidget');
  }
  
  public function addFeatureLevelMetaBox() {
    if(CART66_PRO) {
      add_meta_box('cart66_feature_level_meta', __('Feature Levels', 'cart66'), array($this, 'drawFeatureLevelMetaBox'), 'page', 'side', 'low');
      add_meta_box('cart66_feature_level_meta', __('Feature Levels', 'cart66'), array($this, 'drawFeatureLevelMetaBox'), 'post', 'side', 'low');
    }
  }  
  
  public function drawFeatureLevelMetaBox($post) {
    if(CART66_PRO) {
      $plans = array();
      $featureLevels = array();
      $data = array();
      
      // Load feature levels defined in Spreedly if available
      if(class_exists('SpreedlySubscription')) {
        $sub = new SpreedlySubscription();
        $subs = $sub->getSubscriptions();
        foreach($subs as $s) {
          // $plans[] = array('feature_level' => (string)$s->featureLevel, 'name' => (string)$s->name);
          $plans[(string)$s->name] = (string)$s->featureLevel;
          $featureLevels[] = (string)$s->featureLevel;
        }
      }

      // Load feature levels defined in PayPal subscriptions
      $sub = new Cart66PayPalSubscription();
      $subs = $sub->getSubscriptionPlans();
      foreach($subs as $s) {
        $plans[$s->name] = $s->featureLevel;
        $featureLevels[] = $s->featureLevel;
      }
      
      // Load feature levels defined in Membership products
      foreach(Cart66Product::getMembershipProducts() as $membership) {
        $plans[$membership->name] = $membership->featureLevel;
        $featureLevels[] = $membership->featureLevel;
      }

      // Put unique feature levels in alphabetical order
      if(count($featureLevels)) {
        $featureLevels = array_unique($featureLevels);
        sort($featureLevels);  

        $savedPlanCsv = get_post_meta($post->ID, '_cart66_subscription', true);
        $savedFeatureLevels = empty($savedPlanCsv) ? array() : explode(',', $savedPlanCsv);
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cart66 Saved Plans: $savedPlanCsv -- " . print_r($savedFeatureLevels, true));
        $data = array('featureLevels' => $featureLevels, 'plans' => $plans, 'saved_feature_levels' => $savedFeatureLevels);
      }
      $box = Cart66Common::getView('pro/views/feature-level-meta-box.php', $data);
      echo $box;
    }
  }
  
  /**
   * Convert selected plan ids into a CSV string.
   * If no plans are selected, the meta key is deleted for the post.
   */
  public function saveFeatureLevelMetaBoxData($postId) {
    $nonce = isset($_REQUEST['cart66_spreedly_meta_box_nonce']) ? $_REQUEST['cart66_spreedly_meta_box_nonce'] : '';
    if(wp_verify_nonce($nonce, 'spreedly_meta_box')) {
      $featureLevels = null;
      if(isset($_REQUEST['feature_levels']) && is_array($_REQUEST['feature_levels'])) {
        $featureLevels = implode(',', $_REQUEST['feature_levels']);
      }
      
      if(!empty($featureLevels)) {
        add_post_meta($postId, '_cart66_subscription', $featureLevels, true) or update_post_meta($postId, '_cart66_subscription', $featureLevels);
      }
      else {
        delete_post_meta($postId, '_cart66_subscription');
      }
    }
  }
  
  public function hideStorePages($excludes) {
    
    if(Cart66Setting::getValue('hide_system_pages') == 1) {
      $store = get_page_by_path('store');
      $excludes[] = $store->ID;

      $cart = get_page_by_path('store/cart');
      $excludes[] = $cart->ID;

      $checkout = get_page_by_path('store/checkout');
      $excludes[] = $checkout->ID;
    }

    $express = get_page_by_path('store/express');
    $excludes[] = $express->ID;

    $ipn = get_page_by_path('store/ipn');
    $excludes[] = $ipn->ID;

    $receipt = get_page_by_path('store/receipt');
    $excludes[] = $receipt->ID;
    
    $spreedly = get_page_by_path('store/spreedly');
    if ( isset( $spreedly->ID ) )
			$excludes[] = $spreedly->ID;
    
    if(is_array(get_option('exclude_pages'))){
  		$excludes = array_merge(get_option('exclude_pages'), $excludes );
  	}
  	sort($excludes);
    
  	return $excludes;
  }
  
  public function protectSubscriptionPages() {
    global $wp_query;
    $pid = isset( $wp_query->post->ID ) ? $wp_query->post->ID : NULL;

    // Keep visitors who are not logged in from seeing private pages
    Cart66AccessManager::verifyPageAccessRights($pid);

    // block subscription pages from non-subscribers
    $accountId = Cart66Common::isLoggedIn() ? Cart66Session::get('Cart66AccountId') : 0;
    $account = new Cart66Account($accountId);
    
    // Get a list of the required subscription ids
    $requiredFeatureLevels = Cart66AccessManager::getRequiredFeatureLevelsForPage($pid);
    if(count($requiredFeatureLevels)) {
      // Check to see if the logged in user has one of the required subscriptions
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] protectSubscriptionPages: Page access looking for " . $account->getFeatureLevel() . " in: " . print_r($requiredFeatureLevels, true));
      if(!in_array($account->getFeatureLevel(), $requiredFeatureLevels) || !$account->isActive()) {
        if($wp_query->post->post_type == 'post') {
          
          // Set message for when visitor is not logged in
          if(!$message = Cart66Setting::getValue('post_not_logged_in')) {
            $message = "You must be logged in to view this post.";
          }
          
          if(Cart66Common::isLoggedIn()) {
            
            // Set message for insuficient access rights
            if(!$message = Cart66Setting::getValue('post_access_denied')) {
              $message = "Your current subscription does not allow you to view this post.";
            }
            
          }
          $wp_query->post->post_content = $message;
          $wp_query->post->comment_status = 'closed';
        }
        else {
          wp_redirect(Cart66AccessManager::getDeniedLink());
        }
      }
    }

  }
  
  /**
   * Hide private pages and pages that require a subscription feature level the subscriber does not have
   */
  public function hidePrivatePages($excludes) {
    global $wpdb;
    $hidePrivate = true;
    $mySubItemNums = array();
    $activeAccount = false;
    $featureLevel = false;

    if(Cart66Common::isLoggedIn()) {
      $hidePrivate = false;
      $account = new Cart66Account(Cart66Session::get('Cart66AccountId'));
      
      if($account->isActive()) {
        $activeAccount = true;
        $featureLevel = $account->getFeatureLevel();
      }
      
      // Optionally add the logout link to the end of the navigation
      if(Cart66Setting::getValue('auto_logout_link')) {
        add_filter('wp_list_pages', array($this, 'appendLogoutLink'));
      }

      // Hide guest only pages
      $guestOnlyPageIds = Cart66AccessManager::getGuestOnlyPageIds();
      $excludes = array_merge($excludes, $guestOnlyPageIds);
    }

    // Hide pages requiring a feature level that the subscriber does not have
    $hiddenPages = Cart66AccessManager::hideSubscriptionPages($featureLevel, $activeAccount);
    if(count($hiddenPages)) {
      $excludes = array_merge($excludes, $hiddenPages);
    }

    if($hidePrivate) {
      // Build list of private page ids
      $privatePageIds = Cart66AccessManager::getPrivatePageIds();
      $excludes = array_merge($excludes, $privatePageIds);
    }

    // Merge private page ids with other excluded pages
    if(is_array(get_option('exclude_pages'))){
  		$excludes = array_merge(get_option('exclude_pages'), $excludes );
  	}

    sort($excludes);
    return $excludes;
  }
  
  public function appendLogoutLink($output) {
    $output .= "<li><a href='" . Cart66Common::appendQueryString('cart66-task=logout') . "'>Log out</a></li>";
    return $output;
  }
  
  /**
   * Force downloads for
   *   -- Cart66 reports (admin)
   *   -- Downloading the debuggin log file (admin)
   *   -- Downloading digital product files
   */
  public function forceDownload() {

    ob_end_clean();

    if($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('cart66-action') == 'export_csv') {
      require_once(CART66_PATH . "/models/Cart66Exporter.php");
      $start = str_replace(';', '', $_POST['start_date']);
      $end = str_replace(';', '', $_POST['end_date']);
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Date parameters for report: START $start and END $end");
      $report = Cart66Exporter::exportOrders($start, $end);

      header('Content-Type: application/csv'); 
      header('Content-Disposition: inline; filename="Cart66Report.csv"');
      echo $report;
      die();
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && Cart66Common::postVal('cart66-action') == 'download log file') {

      $logFilePath = Cart66Log::getLogFilePath();
      if(file_exists($logFilePath)) {
        $logData = file_get_contents($logFilePath);
        $cartSettings = Cart66Log::getCartSettings();

        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=Cart66LogFile.txt');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $cartSettings . "\n\n";
        echo $logData;
        die();
      }
    }
    
  }
  
}