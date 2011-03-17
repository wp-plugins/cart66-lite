=== Plugin Name ===
Contributors: reality66
Donate link: http://cart66.com
Tags: ecommerce, e-commerce, shopping, cart, store, paypal, sell, cart66, products, sales
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 1.0.5

Sell digital products and/or physical products with the Cart66 WordPress ecommerce shopping cart plugin.

== Description ==

Cart66 is a simple to use yet powerful ecommerce plugin for WordPress.

* Sell digital products
* Sell physical products
* Sell services
* Manage orders
* Promotions
* Tax by state (United States and Canada), zip code, or all orders
* Multiple shipping options
* Custom fields for products
* Product variations that can optionally change the price of the product
* Place products on any page or post
* Sell internationally
* Set your currency
* Customizable email receipts
* Affiliate platform integration

Checkout using

* PayPal Website Payments Standard
* PayPal Express Checkout
* Manual Checkout (you collect payments on your own, with an invoice or over the phone for example)

Only community support is provided. For additional features and professional support please consider [Cart66 Professional](http://cart66.com "Cart66 WordPress Ecommerce Plugin").

== Installation ==
[Download complete documentation for Cart66 Lite.](http://cart66.com/lite "Cart66 Lite")

To  Install Cart66 Lite

1. Download the cart66-lite.zip file 
2. Extract the zip file so that you have a folder called cart66
3. Upload the 'cart66' folder to the `/wp-content/plugins/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Configure your settings in the Cart66 Settings panel
6. Be sure to configure your store/checkout page with the appropriate shortcode for the payment gateway you intend to use. PayPal Express Checkout is the default.

To Manually Upgrade Cart66 Lite

1. Deactivate Cart66 Lite through the 'Plugins' menu in WordPress
2. Delete the cart66 directory from the `/wp-content/plugins/` directory. WARNING: Do not delete the plugin using the link in the WordPress plugins menu because that will delete the cart66 database as well.
3. Download the new cart66-lite.zip file
4. Extract the zip file so that you have a folder called cart66
5. Upload the 'cart66' folder to the `/wp-content/plugins/` directory
6. Activate the plugin through the 'Plugins' menu in WordPress

To Uninstall Cart66 Lite

1. Deactivate Cart66 Lite thorugh the 'Plugins' menu in WordPress
2. Click the "delete" link to delete the Cart66 Lite plugin. This will remove all of the Cart66 Lite files from your plugins directory and will delete the database tables used by Cart66.

When Cart66 Lite is installed it will create a page called "store" and a few sub-pages under the "store" page. If you no longer want those pages, you can delete them through the WordPress pages panel.

It is important not to move or rename the store pages, otherwise Cart66 will not function properly. For example, if you move or rename the slug for the store/cart page, Cart66 will not know where to send folks after they add a product to their shopping cart.

== Frequently Asked Questions ==

= Why do I get an error that says: The script tried to execute a method or access a property of an incomplete object =

This error occurs when other plugins you have installed, or your theme, starts a session before Cart66 Lite has been loaded. The solution is to look in your other plugins for a line that says `session_start()` and then comment out that line by placing two slashes at the front of the line like: `// session_start();`

= How can I get support? =

We are not able to provide anything other than community based support for Cart66 Lite. Please consider upgrading to [Cart66 Professional](http://cart66.com "Cart66 WordPress Ecommerce Plugin") for support.

== Screenshots ==
1. Cart66 Product Creation Form
2. Cart66 Promotion Management
3. Cart66 Product Management

== Changelog ==

= 1.0.7 =
* Security enhancements
* Added the CSS class Cart66ContinueShopping to the continue shopping links in the view cart screen
* Cart66 scripts are only included in admin pages pertaining to Cart66
