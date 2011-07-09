=== Cart66 Lite :: Ecommerce Shopping Cart Plugin For Your Online Store ===
Contributors: reality66
Donate link: http://cart66.com
Tags: ecommerce, e-commerce, shopping, cart, store, paypal, sell, cart66, products, sales, shopping cart
Requires at least: 2.8
Tested up to: 3.2
Stable tag: 1.1.5

Sell digital products and/or physical products with the Cart66 WordPress ecommerce shopping cart plugin.

== Description ==

Cart66 is a simple to use yet powerful ecommerce plugin for WordPress.

* Sell digital products with Amazon S3 integration
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

[vimeo http://vimeo.com/23207481]

Some features in the video, such as the integration with Gravity Forms, are only available in Cart66 Professional.

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

= 1.1.6 =
* Updated: Enhancements for WordPress 3.2 compatibility
* Fixed: jQuery compatibility issues in WordPress 3.2
* Fixed: PayPal Subscriptions trial period toggle
* Fixed: Manual Checkout form validation
* Fixed: Express Checkout html typo

= 1.1.5 =
* NEW: Italian translation added
* NEW: cURL test added to debug options
* NEW: Debug problems highlighted in red
* NEW: State and zip code labels now change to "Province/Post Code" for non-US countries
* NEW: Amazon S3 bucket name validation
* NEW: Added validation for ship-to country list
* Updated: Receipt emails now get sent from checkout, not receipt page to prevent duplicate emails
* Updated: Zip code and country validation moved to wp_head
* Updated: Shipping/Billing state and zip code classes now have their own labels
* Fixed: Problem with the state drop-down when the home country was not the United States
* Fixed: Improved receipt url syntax for Express Checkout
* Fixed: Corrected misspelling in the login input css id

= 1.1.4 =
* NEW: Define user roles to control access to the Cart66 admin screens
* NEW: Support for translation files
* NEW: Italian translation files included (Thank you Roberto Lioniello!)
* Fixed: Cart66Account class not found error during checkout with $0.00 cart total or when using the Manual Checkout payment gateway.

= 1.1.3 =
* New: Added checks to prevent double charging a customer if they double click, or rapidly click, the complete order button during checkout
* New: Added feature to optionally send http headers to prevent pages from being cached by web browsers. This feature is found in the error logging and debugging box of the Cart66 Settings panel
* New: Added feature to override the price with your own text description of the price when defining products.
* New: Added Malaysian Ringgit to lis of available PayPal Currency options
* New: Added Pakistan to the country list
* Updated: Improved security for Amazon S3 account credentials
* Updated: Improved session management to handle situations when the server name and host name are different.
* Updated: Changed name of constant CURRENCY_SYMBOL to CART66_CURRENCY_SYMBOL to help prevent conflicts
* Updated: Optimized HTML and CSS to make it easier for theme developers to modify the Cart66 presentation elements
* Fixed problem where blank zip codes (such as for international order) could have sales tax charged under certain circumstances

= 1.1.2 =
* New: Added hooks for easier expansion of Cart66. The hooks are cart66_after_add_to_cart, cart66_after_update_cart, cart66_after_remove_item, and cart66_after_order_saved
* Fixed: Revised the syntax of certain callback function to comply with version of PHP prior to version 5.2.3. This resolves the "empty cart" issues some people were experiencing. 

= 1.1.1 =
* Fixed: Problem where database sessions would not persist for some international domains such as example.com.au
* Fixed: Problem where the Cart66 dialog box would occasionally show a 404 File Not Found error

= 1.1 =
* New: Amazon S3 integration. Sell digital products and delivery them through Amazon S3.
* New: Database backed session management. This resolves the unpleasant "incomplete object" errors caused when other plugins start a session before the Cart66 plugin is loaded. 
* New: Security updates
* New: Setting to keep Cart66 Database when uninstalling Cart66 Lite. Cart66 Pro can use the same database tables as Cart66 Lite so if you are upgrading to Cart66 Pro you can use all you Cart66 Lite settings.
* Updated: Cart66 date/time calculations now use the Wordpress timezone offset rather than PHP timezone.  

= 1.0.8 =
* Updated: Changed paths to use a new constant so that the plugins folder can be installed in custom locations
* Fixed: Resolved an issue with Gravity Forms where submitting a form would generate a class not found error.
* Fixed: Resolved several PHP Notice messages

= 1.0.7 =
* Security enhancements
* Added the CSS class Cart66ContinueShopping to the continue shopping links in the view cart screen
* Cart66 scripts are only included in admin pages pertaining to Cart66