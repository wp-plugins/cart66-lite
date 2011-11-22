<?php
/*
Plugin Name: Cart66 Lite
Plugin URI: http://www.cart66.com
Description: Wordpress Shopping Cart
Version: 1.3.0
Author: Reality 66
Author URI: http://www.Reality66.com
Text Domain: cart66
Domain Path: /languages/

------------------------------------------------------------------------
Cart66 WordPress Ecommerce Plugin
Copyright 2011  Reality 66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!class_exists('Cart66')) {
  ob_start();
  define("CART66_PATH", plugin_dir_path( __FILE__ ) ); // e.g. /var/www/example.com/wordpress/wp-content/plugins/cart66
  // define("CART66_URL", rtrim(plugin_dir_url( __FILE__ ), '/') ); // e.g. http://example.com/wordpress/wp-content/plugins/cart66
  define('CART66_URL', plugins_url() . '/' . basename(dirname(__FILE__)));

  require_once(CART66_PATH. "/models/Cart66CartWidget.php");
  require_once(CART66_PATH. "/models/Cart66.php");
  require_once(CART66_PATH. "/models/Cart66Common.php");
  
  define("CART66_ORDER_NUMBER", false);
  define("CART66_PRO", false);
  define('CART66_VERSION_NUMBER', '1.3.0');
  define("WPCURL", Cart66Common::getWpContentUrl());
  define("WPURL", Cart66Common::getWpUrl());

  
  if(CART66_PRO) {
    require_once(CART66_PATH. "/pro/models/Cart66ProCommon.php");
  }

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined("IS_ADMIN")) {
    define("IS_ADMIN",  is_admin());
  }

  /* Uncomment this block of code for load time debugging
  $filename = CART66_PATH . "/log.txt"; 
  if(file_exists($filename) && is_writable($filename)) {
    file_put_contents($filename, "\n\n\n================= Loading Cart66 Main File [" . date('m/d/Y g:i:s a') . "] " . 
      $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI'] . " =================\n\n", FILE_APPEND);
  }
  */
  $cart66 = new Cart66();
  load_plugin_textdomain( 'cart66', false, '/' . basename(dirname(__FILE__)) . '/languages/' );
  
  // Register activation hook to install Cart66 database tables and system code
  register_activation_hook(__FILE__, array($cart66, 'install'));
  
  // Check for WordPress 3.1 auto-upgrades
  if(function_exists('register_update_hook')) {
    register_update_hook(__FILE__, array($cart66, 'install'));
  }

  add_action('init',  array($cart66, 'init'));
  add_action('widgets_init', array($cart66, 'registerCartWidget'));
}

/**
 * Prevent the link rel="next" content from showing up in the wordpress header 
 * because it can potentially prefetch a page with a [clearcart] shortcode
 */
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');