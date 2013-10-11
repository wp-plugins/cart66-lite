=== Cart66 Lite :: WordPress Ecommerce ===
Contributors: reality66
Donate link: http://cart66.com/
Tags: ecommerce, e-commerce, shopping, cart, store, paypal, sell, cart66, products, sales, shopping cart
Requires at least: 2.8.2
Tested up to: 3.6.1
Stable tag: 1.5.1.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell digital products and/or physical products with Cart66. The easiest to use WordPress ecommerce shopping cart plugin.

== Description ==

Cart66 is a simple to use yet powerful ecommerce plugin for WordPress.

* [Process credit cards securely](http://www.mijireh.com "Secure credit card processing") using your payment gateway with [Mijireh](http://www.mijireh.com "Secure credit card processing")
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

* [Mijireh Checkout - Secure credit card processing](http://www.mijireh.com "Secure credit card processing")
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
4. [Secure credit card processing with Mijireh](http://www.mijireh.com)

== Changelog ==

= 1.5.1.15 =

* FIXED: Cross Site Scripting vulnerability and XSS vulnerability
* FIXED: Issue with tax not calculating on PayPal Express

= 1.5.1.14 =

* NEW: Filter to disable Cart66 email receipts
* UPDATED: Datatables integration to allow more friendly searches in orders and accounts
* UPDATED: PayPal to include default language
* UPDATED: htmlentities() function to force UTF-8 Standard for compatibility between PHP 5.4 – 5.5
* UPDATED: Most date formats to use WordPress date format and time format from the main site settings
* UPDATED: Cookies to include HTTPONLY parameter to fix PCI compliance issues
* FIXED: PHP 5 error messages
* FIXED: Issue with crossover request from Cart6 Cloud
* FIXED: Issue with function that checks to see if custom mail plugins are installed
* FIXED: Localization issues
* FIXED: Removed unnecessary html tags
* FIXED: Issue with user defined price not allowing “0″
* FIXED: Issue with coupon code making the grand total a negative number
* FIXED: Possible race condition between setting page to public and starting the slurp for Mijireh
* FIXED: Fixed slurper not working correctly
* FIXED: Issue with empty countries box when no countries are selected for international sales
* FIXED: Issue with add to cart button staying disabled after inventory fail
* FIXED: Misplaced paragraph and list tags in cart view
* FIXED: Invalid character issue with Order Statuses
* FIXED: Issues with international countries calculating taxes
* FIXED: 2Checkout issue not sending shipping details when shipping is $0

= 1.5.1.8 = 

* NEW: Add to cart button is disabled after clicking on it with custom add to cart button
* NEW: Added new wp_mail plugin exception for WP SES
* UPDATED: "Change" link in cart to use double quotes instead of single quotes for attributes
* UPDATED: Javascript to work better with data tables integration
* UPDATED: Custom complete order button to include CSS classes
* UPDATED: Mijireh integration to work with new slurp technology
* FIXED: Fatal error with daily pending order pruner
* FIXED: Yellow label displaying on checkout page when country shipping validation is off
* FIXED: PayPal Express settings not working
* FIXED: Dialog box "enable ajax by default" option
* FIXED: Fatal error when getting live rates in 2checkout
* FIXED: User defined price not working when set to 0
* FIXED: Auto shipping promotions not applying
* FIXED: Incorrect taxed shipping amount
* FIXED: Rare occurance of an incorrect grand total
* FIXED: Removed invalid shipping validation options

= 1.5.1.2 =

* UPDATED: JavaScript for add to cart ajax grabbing product options
* UPDATED: Subtotal in sidebar widget reflects actual subtotal, not discounted subtotal
* FIXED: Missing 8th argument error when adding product to cart with no options

= 1.5.1.1 =

* UPDATED: Form elements for the products, promotions and shipping pages
* UPDATED: Add to cart success and error messages
* UPDATED: Localization strings
* FIXED: Add to cart amounts displaying $0 for amounts over 999.99.
* FIXED: Display of main settings tab
* FIXED: Ajax add to cart issues where quantities were being multiplied
* FIXED: Undefined constant error if DOING_AJAX is not defined
* FIXED: PHP Warning trying to get class of non-object

= 1.5.1 =

* NEW: 2Checkout gateway integration
* NEW: Override Mijireh Checkout Continue button
* NEW: Auto-minifying of views to reduce the risk of wpautop problems
* NEW: Error message when invalid products are added to the cart
* NEW: Ability to change Out of Stock label
* NEW: Option to require custom fields to be filled for purchase
* NEW: Added support for load balancers in HTTPS detection
* NEW: Display Receipt automatically when PayPal Standard return url is set to the Receipt page
* NEW: Shipping validation option for flat rate shipping methods
* NEW: Country validation for flat rate shipping methods
* NEW: Product subtotal option for promotions
* NEW: Currency options to allow placing currency symbol before or after the dollar amount
* NEW: Ability to change currency options for decimal separator and thousands separator
* NEW: Ability to add coupons to cart using custom get variable (default: promotion)
* NEW: Session Timeout message for PayPal Express
* NEW: Options to display Product Item Numbers in the cart and email receipts
* NEW: Filter to allow content to be appended to add to cart buttons
* UPDATED: Compatibility with WordPress 3.5 and Twenty-Twelve theme
* UPDATED: Localization strings
* UPDATED: Messages that explain minimum and maximum quantity when adding to the cart via ajax
* UPDATED: Deprecated split() function to explode()
* UPDATED: Download link to have class on receipt page for better customization
* UPDATED: Settings function to minimize the number of queries to the database
* UPDATED: Removed all javascript out of cart button view
* UPDATED: Promotion error messages for shipping promotions when shipping is free or doesn’t apply
* UPDATED: Display of tax rates to 3 decimal points
* UPDATED: Disabled Ajax tax calculations when no products are taxable
* UPDATED: Styles on cart error messages
* UPDATED: Add to Cart anchor urls to validate
* UPDATED: Tax rate information for Express page
* UPDATED: Tax settings on checkout form
* UPDATED: CSS on checkout form
* FIXED: Currency symbol setting adding extra characters
* FIXED: Invalid markup in style tag in PayPal Standard checkout
* FIXED: Rare case of PayPal Standard not redirecting to PayPal
* FIXED: Promotion redemptions not updating when using Mijireh Checkout
* FIXED: Invalid shipping total issue in PayPal Express
* FIXED: Incorrect display of price string in add to cart button view
* FIXED: Display of currency when not using a decimal point for the decimal indicator
* FIXED: Display of background color for alert messages in IE
* FIXED: PayPal Standard URL to use unencoded ampersand to prevent malformed urls
* FIXED: State/Province and country resetting when toggling ‘same as billing’
* FIXED: Undefined index when adding to the cart via ajax
* FIXED: IE issue with console.log statements
* FIXED: Display of tax rates with no decimals (7% vs. 7.%)
* FIXED: $0 Transactions created from PayPal Express URLS
* FIXED: Issue with shipping promotion not working with PayPal Express
* FIXED: Disable Cart66 database sessions for WordPress admin requests
* FIXED: Display of order notes boxes when showing a new order note
* FIXED: Rendering of Complete Order button
* FIXED: Shopping widget not updating shipping on Ajax add to cart
* FIXED: User price not working when set to 0
* FIXED: Tax rates not displaying correctly for “all sales”
* FIXED: Tax settings to not allow saving of empty tax rates
* FIXED: Missing dashboard update message
* FIXED: Download links in email receipt showing for every product

= 1.5.0.2 =
* FIXED: Tax rates not displaying correctly for "all sales"
* FIXED: Tax settings to not allow saving of empty tax rates
* FIXED: Issue with updating order statuses
* FIXED: PHP errors in email receipts

= 1.5.0.1 =
* FIXED: Fatal error on plugin deactivation

= 1.5.0 =
* NEW: Added 164 countries to the countries list
* NEW: Automatic tax calculations on checkout page
* NEW: Ability to show order summary when tax is calculated
* NEW: Redesigned Settings Page
* NEW: Notifications Center
* NEW: Internal order notes
* NEW: View order notes on main orders page
* NEW: Download links included directly in email receipts
* NEW: Exclude products from promotions
* NEW: Maximum order amount for promotions
* NEW: Australian Capital Territory added as Australian State
* NEW: Malaysian states
* NEW: Custom PayPal Standard and PayPal Express buttons via URL
* NEW: Disable billing landing page for PayPal Express
* NEW: Ability to disable IP Validation for database sessions
* NEW: Set database session length in minutes
* NEW: Ajax Add to Cart queue to prevent skipping items being added simultaneously
* NEW: Added page checker to ensure cart66 required pages are in place
* NEW: Added cart66_add_popup_screens filter to allow the Cart66 Dialog box to show up on custom post types and other screens as needed by plugin and theme developers.
* NEW: Storing gateway transaction number in Cart66 when using Mijireh Checkout
* UPDATED: PayPal Standard button to prevent intermittent parsing problems
* UPDATED: Advanced sidebar widget to include shipping amount
* UPDATED: Mijireh error message when using incorrect access key
* UPDATED: Calculate shipping button to work with button overrides
* UPDATED: Product links to hide in “read” mode
* UPDATED: Default error messages
* UPDATED: PayPal Standard to use UTF-8 character set
* UPDATED: Added translations for previously overlooked text
* FIXED: IPN Page undefined index
* FIXED: Cart66 resources to use correct HTTP/HTTPS method
* FIXED: Undefined error when saving tax rate with one zip code
* FIXED: Apostrophe’s not working in product names
* FIXED: Error field highlighting for IE9
* FIXED: Statistics widget not working for some databases
* FIXED: An issue with user defined pricing
* FIXED: Several checkout form bugs
* FIXED: PHP 5.4 compatibility fixes
* FIXED: Using widgetContent for ajax add to cart instead of content to fix ie7/ie8 conflict

= 1.4.9 =
* FIXED: Javascript conflict breaking the "attach media" feature in the WordPress admin
* FIXED: State/Province field did not update when changing the ship to country when using Mijireh checkout
* UPDATED: Dramatic improvement for discount amount load time for large quantities
* UPDATED: Updated receipt view to close the document so window.print works in all browsers including IE

= 1.4.8 =
* UPDATED: Improved error messages for payment gateways
* UPDATED: Improved responsiveness of page slurp panel when using Mijireh Checkout
* UPDATED: Minimum order total to use subtotal instead of grand total
* UPDATED: Ajax add to cart for multiple form elements on a page
* FIXED: Product options with a forward slash not adding to the cart
* FIXED: Empty error message when Mijireh access key is blank
* FIXED: Issue with incorrect totals for large discounts in Mijireh Checkout

= 1.4.7 =
* NEW: Email address verification on the checkout form
* NEW: Armed Forces AA, Armed Forces AP to state list
* UPDATED: Product option server side validation
* UPDATED: Use htmlspecialchars with product_url to prevent cross site scripting hacks
* UPDATED: All redirects to use wp_redirect();
* FIXED: PHP Warning caused by setting CURLOPT_FOLLOWLOCATION when safe_mode is on or open_basedir is set
* FIXED: Headers already sent error on receipt page

= 1.4.4 =
* FIXED: Problem where the shipping address may be required even if the 'same as billing' checkbox is checked

= 1.4.3 =
UPDATED: Continuing to try to improve session handling to resolve the "empty cart" problem some people have

= 1.4.2 =
* FIXED: Error on receipt page after requiring an unavailable class

= 1.4.1 =
* NEW: Option to use database backed sessions or native PHP sessions. Choose this feature from the "Main Settings" panel of the Cart66 Settings screen.
* FIXED: Unable to update PayPal settiings
* FIXED: DataTables processing error when WordPress is in debug mode

= 1.4.0 =
* NEW: Secure credit card processing with [Mijireh](http://www.mijireh.com "Secure credit card processing")
* NEW: Product links in cart
* NEW: Printer friendly receipt link in orders page
* NEW: Minimum cart amount feature
* NEW: Option to skip PayPal account creation for PayPal Express
* NEW: Link to edit products from product page
* NEW: Cart66 Admin Bar menu
* NEW: Ajax Add to Cart feature with javascript hooks
* NEW: Optional US Territories to state list
* NEW: Custom Button Text input for Add To Cart button
* NEW: Span tags around price label for greater flexibility in display
* NEW: Added Update Total and Apply Coupon button overrides
* NEW: DataTables integration for all tables in the Cart66 Admin
* UPDATED: Hook for meta generator to allow for easy removal
* UPDATED: Default Character Set in MySQL tables to UTF-8
* UPDATED: CSS for price and quantity elements
* UPDATED: Version number meta for W3C compatibility
* UPDATED: Download option to check for individual downloads
* UPDATED: Timestamp for dashboard widgets
* UPDATED: Australia Provinces to States
* UPDATED: PayPal Standard ordered_on date function
* UPDATED: Widgets.css backwards compatibility for WordPress 3.2-
* UPDATED: Error messages when adding to the cart
* UPDATED: Ajax Listener to prevent direct calls to admin-ajax.php
* UPDATED: Subtotal in Advanced Cart Widget
* UPDATED: Localization for Add To Cart buttons
* UPDATED: Sessions to use longtext for user_data
* UPDATED: Coupon code cleaning script
* UPDATED: IP retrieval code for sessions


= 1.3.0 =
* NEW: Product specific promotions
* NEW: Date ranges for promotions
* NEW: Multiple codes for promotions
* NEW: Promotions can apply to shipping, products, and cart total
* NEW: Auto-apply promotions
* NEW: Google Analytics eCommerce Tracking
* NEW: Dashboard Widgets
* NEW: From the Page or Post Editor you can click on the Cart66 icon in the Upload/Insert toolbar in visual or HTML mode
* NEW: Shortcode added for showing content one time, immediately after a sale
* NEW: Compatibility with IPV6
* NEW: Advanced cart widget
* NEW: User-defined pricing
* NEW: User defined and admin defined quantity to add_to_cart shortcodes
* NEW: Minimum quantity option for products
* NEW: Continue shopping override option
* NEW: Option to show shipping form by default
* NEW: French translation added
* Updated: Added datepicker to date fields
* Updated: Added Egypt, Saudi Arabia, Lesotho, and Kenya to country list
* Updated: CSS classes added to various cart components
* Updated: Cart button uses better jQuery conflict protection
* Updated: Added classes to complete order buttons
* Updated: Added button classes to update cart and apply coupon buttons
* Updated: Added shipping country for Express Checkout orders
* Fixed: Tax was sometimes not charged
* Fixed: Scripts problem with CDATA
* Fixed: Previous page url for continue shopping button
* Fixed: Encoding problem for currency symbols in PayPal subscription pricing descriptions
* Fixed: Dialog box errors

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
* Cart66 scripts are only included in admin pages pertaining to Cart66ere the shipping address may be required even if the 'same as billing' checkbox is checked
