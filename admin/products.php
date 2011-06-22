<?php
$product = new Cart66Product();
$adminUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php';
$errorMessage = false;

if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['cart66-action'] == 'save product') {
  try {
    $product->handleFileUpload();
    $product->setData($_POST['product']);
    $product->save();
    $product->clear();
  }
  catch(Cart66Exception $e) {
    $errorCode = $e->getCode();
    if($errorCode == 66102) {
      // Product save failed
      $errors = $product->getErrors();
      $errorMessage = Cart66Common::showErrors($errors, "<p><b>" . __("The product could not be saved for the following reasons","cart66") . ":</b></p>");
    }
    elseif($errorCode == 66101) {
      // File upload failed
      $errors = $product->getErrors();
      $errorMessage = Cart66Common::showErrors($errors, "<p><b>" . __("The file upload failed","cart66") . ":</b></p>");
    }
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product save failed ($errorCode): " . strip_tags($errorMessage));
  }
  
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $product->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = Cart66Common::getVal('id');
  $product->load($id);
  $product->deleteMe();
  $product->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'xdownload' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = Cart66Common::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new Cart66Setting();
  $dir = Cart66Setting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->download_path = '';
  $product->save();
}
?>

<?php if($errorMessage): ?>
<div style="margin: 30px 50px 10px 5px;"><?php echo $errorMessage ?></div>
<?php endif; ?>



<h2>Cart66 Products</h2>

<form action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="cart66-action" value="save product" />
  <input type="hidden" name="product[id]" value="<?php echo $product->id ?>" />
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="long" for="product-name"><?php _e( 'Product name' , 'cart66' ); ?>:</label>
                <input class="long" type="text" name='product[name]' id='product-name' value='<?php echo $product->name ?>' />
              </li>
              <li>
                <label class="long" for='product-item_number'><?php _e( 'Item number' , 'cart66' ); ?>:</label>
                <input type='text' name='product[item_number]' id='product-item_number' value='<?php echo $product->itemNumber ?>' />
                <span class="label_desc"><?php _e( 'Unique item number required.' , 'cart66' ); ?></span>
              </li>
              
              <?php if(CART66_PRO && Cart66Setting::getValue('spreedly_shortname')): ?>
              <li>
                <label for="product-spreedly_subscription_id" class="long"><?php _e( 'Attach Spreedly subscription' , 'cart66' ); ?>:</label>
                <select name="product[spreedly_subscription_id]" id="product-spreedly_subscription_id">
                  <?php foreach($data['subscriptions'] as $id => $name): ?>
                    <?php
                      $selected = ($id == $product->spreedlySubscriptionId) ? 'selected="selected"' : '';
                    ?>
                  <option value="<?php echo $id ?>" <?php echo $selected ?>><?php echo $name ?></option>
                  <?php endforeach; ?>
                </select>
              </li>
              <?php endif; ?>
              
              <li>
                <label class="long" for="product-price" id="price_label"><?php _e( 'Price' , 'cart66' ); ?>:</label>
                <?php echo CART66_CURRENCY_SYMBOL ?><input type='text' style="width: 75px;" id="product-price" name='product[price]' value='<?php echo $product->price ?>'>
                <span class="label_desc" id="price-description"></span>
              </li>
              <li>
                <label class="long" for="product-price_description" id="price_label"><?php _e( 'Price description' , 'cart66' ); ?>:</label>
                <input type='text' style="width: 275px;" id="product-price_description" name='product[price_description]' value='<?php echo $product->priceDescription ?>'>
                <span class="label_desc" id="price_description"><?php _e( 'If you would like to customize the display of the price' , 'cart66' ); ?></span>
              </li>
              <li>
                <label class="long" for="product-taxable"><?php _e( 'Taxed' , 'cart66' ); ?>:</label>
                <select id="product-taxable" name='product[taxable]'>
                  <option value='1' <?php echo ($product->taxable == 1)? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  <option value='0' <?php echo ($product->taxable == 0)? 'selected="selected"' : '' ?>><?php _e( 'No' , 'cart66' ); ?></option>
                </select>
                <span class="label_desc"><?php _e( 'Do you want to collect sales tax when this item is purchased?' , 'cart66' ); ?></span>
                <p class="label_desc"><?php _e( 'For subscriptions, tax is only collected on the one time fee.' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="long" for="product-shipped">Shipped:</label>
                <select id="product-shipped" name='product[shipped]'>
                  <option value='1' <?php echo ($product->shipped === '1')? 'selected="selected"' : '' ?>><?php _e( 'Yes' , 'cart66' ); ?></option>
                  <option value='0' <?php echo ($product->shipped === '0')? 'selected="selected"' : '' ?>><?php _e( 'No' , 'cart66' ); ?></option>
                </select>
                <span class="label_desc"><?php _e( 'Does this product require shipping' , 'cart66' ); ?>?</span>
              </li>
              <li>
                <label class="long" for="product-weight"><?php _e( 'Weight' , 'cart66' ); ?>:</label>
                <input type="text" name="product[weight]" value="<?php echo $product->weight ?>" size="6" id="product-weight" /> lbs 
                <p class="label_desc"><?php _e( 'Shipping weight in pounds. Used for live rates calculations. Weightless items ship free.<br/>
                  If using live rates and you want an item to have free shipping you can enter 0 for the weight.' , 'cart66' ); ?></p>
              </li>
              <li class="nonSubscription">
                <label class="long" for="product-max_qty"><?php _e( 'Max quantity' , 'cart66' ); ?>:</label>
                <input type="text" style="width: 50px;" id="product-max_qty" name='product[max_quantity]' value='<?php echo $product->maxQuantity ?>' />
                <p class="label_desc"><?php _e( 'Limit the quantity that can be added to the cart. Set to 0 for unlimited.<br/>
                  If you are selling digital products you may want to limit the quantity of the product to 1.' , 'cart66' ); ?></p>
              </li>
              
              
              <?php if(CART66_PRO && class_exists('RGForms')): ?>
                <li class="">
                  <label for="product-gravity_form_id" class="long"><?php _e( 'Attach Gravity Form' , 'cart66' ); ?>:</label>
                  <select name='product[gravity_form_id]' id="product-gravity_form_id">
                    <option value='0'><?php _e( 'None' , 'cart66' ); ?></option>
                    <?php
                      global $wpdb;
                      require_once(CART66_PATH . "/pro/models/Cart66GravityReader.php");
                      $gfIdsInUse = Cart66GravityReader::getIdsInUse();
                      $gfTitles = array();
                      $forms = Cart66Common::getTableName('rg_form', '');
                      $sql = "SELECT id, title from $forms where is_active=1 order by title";
                      $results = $wpdb->get_results($sql);
                      foreach($results as $r) {
                        $disabled = ( in_array($r->id, $gfIdsInUse) && $r->id != $product->gravityFormId) ? 'disabled="disabled"' : '';
                        $gfTitles[$r->id] = $r->title;
                        $selected = ($product->gravityFormId == $r->id) ? 'selected="selected"' : '';
                        echo "<option value='$r->id' $selected $disabled>$r->title</option>";
                      }
                    ?>
                  </select>
                  <span class="label_desc"><?php _e( 'A Gravity Form may only be linked to one product' , 'cart66' ); ?></span>
                </li>
                <li class="">
                  <label class="long"><?php _e( 'Quantity field' , 'cart66' ); ?>:</label>
                  <select name="product[gravity_form_qty_id]" id="gravity_form_qty_id">
                    <option value='0'><?php _e( 'None' , 'cart66' ); ?></option>
                    <?php
                      $gr = new Cart66GravityReader($product->gravityFormId);
                      $fields = $gr->getStandardFields();
                      foreach($fields as $id => $label) {
                        $selected = ($product->gravityFormQtyId == $id) ? 'selected="selected"' : '';
                        echo "<option value='$id' $selected>$label</option>\n";
                      }
                    ?>
                  </select>
                  <span class="label_desc"><?php _e( 'Use one of the Gravity Form fields as the quantity for your product.' , 'cart66' ); ?></span>
                </li>
              <?php endif; ?>
              
              <?php if(CART66_PRO): ?>
                </ul>
                <ul id="membershipProductFields">
                  <li>
                    <label for="product-membership_products" class="long"><?php _e( 'Membership Product' , 'cart66' ); ?>:</label>
                    <select name="product[is_membership_product]" id="product-membership_product">
                      <option value='0' <?php echo $product->isMembershipProduct == 0 ? 'selected="selected"' : ''; ?> ><?php _e( 'No' , 'cart66' ); ?></option>
                      <option value='1' <?php echo $product->isMembershipProduct == 1 ? 'selected="selected"' : ''; ?> ><?php _e( 'Yes' , 'cart66' ); ?></option>
                    </select>
                    <span class="label_desc"><?php _e( 'Should purchasing this product create a membership account?' , 'cart66' ); ?></span>
                  </li>
                  <li class="member_product_attrs">
                    <label class="long" for="product-feature_level"><?php _e( 'Feature level' , 'cart66' ); ?>:</label>
                    <input type="text" name="product[feature_level]" value="<?php echo $product->featureLevel; ?>" id="product-feature_level">
                  </li>
                  <li class="member_product_attrs">
                    <label for="product-billing_interval" class="long" for="membership_duration"><?php _e( 'Duration' , 'cart66' ); ?>:</label>
                    <input type="text" name="product[billing_interval]" value="<?php echo $product->billingInterval > 0 ? $product->billingInterval : ''; ?>" id="product-billing_interval" style="width: 5em;" />
                    <select name="product[billing_interval_unit]" id="product-billing_interval_unit">
                      <option value="days"   <?php echo $product->billingIntervalUnit == 'days' ? 'selected="selected"' : ''; ?> >Days</option>
                      <option value="weeks"  <?php echo $product->billingIntervalUnit == 'weeks' ? 'selected="selected"' : ''; ?> >Weeks</option>
                      <option value="months" <?php echo $product->billingIntervalUnit == 'months' ? 'selected="selected"' : ''; ?> >Months</option>
                      <option value="years"  <?php echo $product->billingIntervalUnit == 'years' ? 'selected="selected"' : ''; ?> >Years</option>
                    </select>
                  
                    <span style="padding: 0px 10px;"><?php _e( 'or' , 'cart66' ); ?></span>
                    <input type="checkbox" value="1" name="product[lifetime_membership]" id="product-lifetime_membership"  <?php echo $product->lifetimeMembership == 1 ? 'checked="checked"' : ''; ?>> <?php _e( 'Lifetime' , 'cart66' ); ?>
                  </li>
                </ul>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo (strlen($product->download_path) || strlen($product->s3_file) ) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Digital Product Options' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <h4 style="padding-left: 10px;"><?php _e( 'Limit The Number Of Times This File Can Be Downloaded' , 'cart66' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-download_limit'><?php _e( 'Download limit' , 'cart66' ); ?>:</label>
                <input style="width: 35px;" type='text' name='product[download_limit]' id='product-download_limit' value='<?php echo $product->download_limit ?>' />
                <span class="label_desc"><?php _e( 'Max number of times customer may download product. Enter 0 for no limit.' , 'cart66' ); ?></span>
              </li>
            </ul>
            
            <?php if(Cart66Setting::getValue('amazons3_id')): ?>
              <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products With Amazon S3' , 'cart66' ); ?></h4>
              <ul>
                <li>
                  <label for="product-s3_bucket" class="med"><?php _e( 'Bucket' , 'cart66' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_bucket]' id='product-s3_bucket' value="<?php echo $product->s3_bucket ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 bucket name that is holding the digital file.' , 'cart66' ); ?></p>
                </li>
                <li>
                  <label class="med" for='product-s3_file'><?php _e( 'File' , 'cart66' ); ?>:</label>
                  <input class="long" type='text' name='product[s3_file]' id='product-s3_file' value="<?php echo $product->s3_file ?>" />
                  <p class="label_desc"><?php _e( 'The Amazon S3 file name of your digital product.' , 'cart66' ); ?></p>
                </li>
              </ul>
              <p style="width: 600px; padding: 0px 10px;"><a href="#" id="amazons3ForceDownload"><?php _e( 'How do I force the file to download rather than being displayed in the browser?' , 'cart66' ); ?></a></p>
              <p id="amazons3ForceDownloadAnswer" style="width: 600px; padding: 10px; display: none;"><?php _e( 'If you want your digital product to download rather than display in the web browser, log into your Amazon S3 account and click on the file that you want to force to download and enter the following Meta Data in the file\'s properties:<br/>
                Key = Content-Type | Value = application/octet-stream<br/>
                Key = Content-Disposition | Value = attachment' , 'cart66' ); ?><br/><br/>
                <img src="<?php echo CART66_URL; ?>/admin/images/s3-force-download-help.png" /></p>
            <?php  endif; ?>
            
            <?php
              $setting = new Cart66Setting();
              $dir = Cart66Setting::getValue('product_folder');
              if($dir) {
                if(!file_exists($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> The digital products folder does not exist. Please update your <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "<br/>$dir</p>";
                elseif(!is_writable($dir)) echo "<p style='color: red;'>" . __("<strong>WARNING:</strong> WordPress cannot write to your digital products folder. Please make your digital products file writeable or change your digital products folder in the <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "<br/>$dir</p>";
              }
              else {
                echo "<p style='color: red;'>" . 
                __("Before you can upload your digital product, please specify a folder for your digital products in the<br/>
                <strong>Digital Product Settings</strong> on the <a href='?page=cart66-settings'>settings page</a>.","cart66") . "</p>";
              }
            ?>
            <h4 style="padding-left: 10px;"><?php _e( 'Deliver Digital Products From Your Server' , 'cart66' ); ?></h4>
            <ul>
              <li>
                <label class="med" for='product-upload'><?php _e( 'Upload product' , 'cart66' ); ?>:</label>
                <input class="long" type='file' name='product[upload]' id='product-upload' value='' />
                <p class="label_desc"><?php _e( 'If you FTP your product to your product folder, enter the name of the file you uploaded in the field below.' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for='product-download_path'><em><?php _e( 'or' , 'cart66' ); ?></em> <?php _e( 'File name' , 'cart66' ); ?>:</label>
                <input class="long" type='text' name='product[download_path]' id='product-download_path' value='<?php echo $product->download_path ?>' />
                <?php
                  if(!empty($product->download_path)) {
                    $file = $dir . DIRECTORY_SEPARATOR . $product->download_path;
                    if(file_exists($file)) {
                      echo "<p class='label_desc'><a href='?page=cart66-products&task=xdownload&id=" . $product->id . "'>" . __("Delete this file from the server","cart66") . "</a></p>";
                    }
                    else {
                      echo "<p class='label_desc' style='color: red;'>" . __("<strong>WARNING:</strong> This file is not in your products folder","cart66");
                    }
                  }
                  
                ?>
              </li>
            </ul>
            
            <div class="description" style="width: 600px; margin-left: 10px;">
            <p><strong><?php _e( 'NOTE: If you are delivering large digital files, please consider using Amazon S3.' , 'cart66' ); ?></strong></p>
            <p><a href="#" id="viewLocalDeliverInfo"><?php _e( 'View local delivery information' , 'cart66' ); ?></a></p>
            <p id="localDeliveryInfo" style="display:none;"><?php _e( 'There are several settings built into PHP that affect the size of the files you can upload. These settings are set by your web host and can usually be configured for your specific needs.Please contact your web hosting company if you need help change any of the settings below.
              <br/><br/>
              If you need to upload a file larger than what is allowed via this form, you can FTP the file to the products folder' , 'cart66' ); ?> 
              <?php echo $dir ?> <?php _e( 'then enter the name of the file in the "File name" field above.' , 'cart66' ); ?>
              <br/><br/>
              <?php _e( 'Max Upload Filesize' , 'cart66' ); ?>: <?php echo ini_get('upload_max_filesize');?>B<br/><?php _e( 'Max Postsize' , 'cart66' ); ?>: <?php echo ini_get('post_max_size');?>B</p>
            </div>
          </div>
        </div>
      </div>
    
      <div class="widgets-holder-wrap <?php echo strlen($product->options_1) ? '' : 'closed'; ?>">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product Variations' , 'cart66' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="med" for="product-options_1"><?php _e( 'Option Group 1' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type="text" name="product[options_1]" id="product-options_1" value="<?php echo htmlentities($product->options_1); ?>" />
                <p class="label_desc"><?php _e( 'Small, Medium +$2.00, Large +$4.00' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-options_2"><?php _e( 'Option Group 2' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type="text" name='product[options_2]' id="product-options_2" value="<?php echo htmlentities($product->options_2); ?>" />
                <p class="label_desc"><?php _e( 'Red, White, Blue' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom"><?php _e( 'Custom field' , 'cart66' ); ?>:</label>
                <select name='product[custom]' id="product-custom">
                  <option value="none"><?php _e( 'No custom field' , 'cart66' ); ?></option>
                  <option value="single" <?php echo ($product->custom == 'single')? 'selected' : '' ?>><?php _e( 'Single line text field' , 'cart66' ); ?></option>
                  <option value="multi" <?php echo ($product->custom == 'multi')? 'selected' : '' ?>><?php _e( 'Multi line text field' , 'cart66' ); ?></option>
                </select>
                <p class="label_desc"><?php _e( 'Include a free form text area so your buyer can provide custom information such as a name to engrave on the product.' , 'cart66' ); ?></p>
              </li>
              <li>
                <label class="med" for="product-custom_desc"><?php _e( 'Instructions' , 'cart66' ); ?>:</label>
                <input style="width: 80%" type='text' name='product[custom_desc]' id="product-custom_desc" value='<?php echo $product->custom_desc ?>' />
                <p class="label_desc"><?php _e( 'Tell your buyer what to enter into the custom text field. (Ex. Please enter the name you want to engrave)' , 'cart66' ); ?></p>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div style="padding: 0px;">
        <?php if($product->id > 0): ?>
        <a href='?page=cart66-products' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'cart66' ); ?></a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
      </div>
  
    </div>
  </div>

</form>
  
<?php
  $product = new Cart66Product();
  $products = $product->getNonSubscriptionProducts();
  if(count($products)):
?>
  <h3 style="margin-top: 20px;"><?php _e( 'Your Products' , 'cart66' ); ?></h3>
  <table class="widefat" style="width: 95%">
  <thead>
    <tr>
      <th colspan="8">Search: <input type="text" name="Cart66AccountSearchField" value="" id="Cart66AccountSearchField" /></th>
    </tr>
  	<tr>
  		<th>ID</th>
  		<th>Item Number</th>
  		<th>Product Name</th>
  		<th>Price</th>
  		<th>Taxed</th>
  		<th>Shipped</th>
  		<th>Actions</th>
  	</tr>
  </thead>
  <tbody>
    <?php foreach($products as $p): ?>
     <tr>
       <td><?php echo $p->id ?></td>
       <td><?php echo $p->itemNumber ?></td>
       <td><?php echo $p->name ?>
         <?php
           if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
             echo '<br/><em>Linked To Gravity From: ' . $gfTitles[$p->gravityFormId] . '</em>';
           }
          ?>
       </td>
       <td><?php echo CART66_CURRENCY_SYMBOL ?><?php echo $p->price ?></td>
       <td><?php echo $p->taxable? ' Yes' : 'No'; ?></td>
       <td><?php echo $p->shipped? ' Yes' : 'No'; ?></td>
       <td>
         <a href='?page=cart66-products&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
         <a class='delete' href='?page=cart66-products&task=delete&id=<?php echo $p->id ?>'>Delete</a>
       </td>
     </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
<?php endif; ?>

<?php
  $products = $product->getSpreedlyProducts();
  if(count($products)):
?>
<h3 style="margin-top: 20px;"><?php _e( 'Your Spreedly Subscription Products' , 'cart66' ); ?></h3>
<table class="widefat" style="width: 95%">
<thead>
  <tr>
    <th colspan="8">Search: <input type="text" name="Cart66AccountSearchField" value="" id="Cart66AccountSearchField" /></th>
  </tr>
	<tr>
		<th>ID</th>
		<th>Item Number</th>
		<th>Product Name</th>
		<th>Price</th>
		<th>Taxed</th>
		<th>Shipped</th>
		<th>Actions</th>
	</tr>
</thead>
<tbody>
  <?php foreach($products as $p): ?>
   <tr>
     <td><?php echo $p->id ?></td>
     <td><?php echo $p->itemNumber ?></td>
     <td><?php echo $p->name ?>
       <?php
         if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
           echo '<br/><em>Linked To Gravity From: ' . $gfTitles[$p->gravityFormId] . '</em>';
         }
        ?>
     </td>
     <td><?php echo $p->getPriceDescription() ?></td>
     <td><?php echo $p->taxable? ' Yes' : 'No'; ?></td>
     <td><?php echo $p->shipped? ' Yes' : 'No'; ?></td>
     <td>
       <a href='?page=cart66-products&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
       <a class='delete' href='?page=cart66-products&task=delete&id=<?php echo $p->id ?>'>Delete</a>
     </td>
   </tr>
  <?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>


<script type='text/javascript'>
  jQuery.noConflict();
  jQuery(document).ready(function($) {
    
    toggleSubscriptionText();
    toggleMembershipProductAttrs();
    
    $('.sidebar-name').click(function() {
      $(this.parentNode).toggleClass("closed");
    });

    $('.delete').click(function() {
      return confirm('Are you sure you want to delete this item?');
    });

    // Ajax to populate gravity_form_qty_id when gravity_form_id changes
    $('#product-gravity_form_id').change(function() {
      var gravityFormId = $('#product-gravity_form_id').val();
      $.get(ajaxurl, { 'action': 'update_gravity_product_quantity_field', 'formId': gravityFormId}, function(myOptions) {
        $('#gravity_form_qty_id >option').remove();
        $('#gravity_form_qty_id').append( new Option('None', 0) );
        $.each(myOptions, function(val, text) {
            $('#gravity_form_qty_id').append( new Option(text,val) );
        });
      });
    });
    
    $('#spreedly_subscription_id').change(function() {
      toggleSubscriptionText();
    });
    
    $('#paypal_subscription_id').change(function() {
      toggleSubscriptionText();
    });
    
    $('#Cart66AccountSearchField').quicksearch('table tbody tr');
    
    $('#product-membership_product').change(function() {
      toggleMembershipProductAttrs();
    });
    
    $('#product-lifetime_membership').click(function() {
      toggleLifeTime();
    });
    
    $('#viewLocalDeliverInfo').click(function() {
      $('#localDeliveryInfo').toggle();
      return false;
    });
    
    $('#amazons3ForceDownload').click(function() {
      $('#amazons3ForceDownloadAnswer').toggle();
      return false;
    });
    
  });
  
  function toggleLifeTime() {
    if(jQuery('#product-lifetime_membership').attr('checked')) {
      jQuery('#product-billing_interval').val('');
      jQuery('#product-billing_interval').attr('disabled', true);
      jQuery('#product-billing_interval_unit').val('days');
      jQuery('#product-billing_interval_unit').attr('disabled', true);
    }
    else {
      jQuery('#product-billing_interval').attr('disabled', false);
      jQuery('#product-billing_interval_unit').attr('disabled', false);
    }
  }
  
  function toggleMembershipProductAttrs() {
    if(jQuery('#product-membership_product').val() == '1') {
      jQuery('.member_product_attrs').css('display', 'block');
    }
    else {
      jQuery('.member_product_attrs').css('display', 'none');
    }
  }
  
  function toggleSubscriptionText() {
    if(isSubscriptionProduct()) {
      jQuery('#price_label').text('One Time Fee:');
      jQuery('#price_description').text('One time fee charged when subscription is purchased. This could be a setup fee.');
      jQuery('#subscriptionVariationDesc').show();
      jQuery('.nonSubscription').hide();
      jQuery('#membershipProductFields').hide();
      jQuery('#product-membership_product').val(0);
      jQuery('#product-feature_level').val('');
      jQuery('#product-billing_interval').val('');
      jQuery('#product-billing_interval_unit').val('days');
      jQuery('#product-lifetime_membership').removeAttr('checked');
    }
    else {
      jQuery('#price_label').text('Price:');
      jQuery('#price_description').text('');
      jQuery('#subscriptionVariationDesc').hide();
      jQuery('.nonSubscription').show();
      jQuery('#membershipProductFields').show();
    }
  }
  
  function isSubscriptionProduct() {
    var spreedlySubId = jQuery('#spreedly_subscription_id').val();
    var paypalSubId = jQuery('#paypal_subscription_id').val();
    
    if(spreedlySubId > 0 || paypalSubId > 0) {
      return true;
    }
    return false;
  }
  
</script>