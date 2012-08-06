<?php
$tab = $data['tab'];
?>
<div id="saveResult"></div>
<div id="cart66-inner-tabs">
  <ul class="subsubsub">
    <li><a href="#debug-error_logging" class="debug-error_logging"><?php _e('Error Logging', 'cart66'); ?></a> | </li>
    <li><a href="#debug-session_settings" class="debug-session_settings"><?php _e('Session Management', 'cart66'); ?></a> | </li>
    <li><a href="#debug-debug_data" class="debug-debug_data"><?php _e('Debug Data', 'cart66'); ?></a></li>
  </ul>
  <br clear="all">
  <form id="errorLoggingAndDebugging" action="" method="post" class="ajaxSettingForm">
    <input type="hidden" name="action" value="save_settings" />
    <input type="hidden" name="_success" value="<?php _e('Your debug settings have been saved', 'cart66'); ?>." />
    <div id="debug-error_logging" class="pane">
      <h3><?php _e('Error Logging & Debugging', 'cart66'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Enable Logging', 'cart66'); ?></th>
            <td>
              <input type="radio" name="enable_logging" id="enable_logging_yes" value="1" <?php echo Cart66Setting::getValue('enable_logging') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_logging_yes"><?php _e('Yes', 'cart66'); ?></label>
              <input type="radio" name="enable_logging" id="enable_logging_no" value="" <?php echo Cart66Setting::getValue('enable_logging') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="enable_logging_no"><?php _e('No', 'cart66'); ?></label>
              <p class="description"><?php _e( 'Only enable logging when testing your site. The log file will grow quickly.' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Disable Caching', 'cart66'); ?></th>
            <td>
              <select name="disable_caching" id="disable_caching">
                <option value="0"><?php _e( 'Never' , 'cart66' ); ?></option>
                <option value="1" <?php echo Cart66Setting::getValue('disable_caching') == 1 ? 'selected="selected"' : '' ?>><?php _e( 'On cart pages' , 'cart66' ); ?></option>
                <option value="2" <?php echo Cart66Setting::getValue('disable_caching') == 2 ? 'selected="selected"' : '' ?>><?php _e( 'On all pages' , 'cart66' ); ?></option>
              </select>
              <span class="label_desc"><?php _e( 'Send HTTP headers to prevent pages from being cached by web browsers.' , 'cart66' ); ?></span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Delete Database when Uninstalling', 'cart66'); ?></th>
            <td>
              <input type="radio" name="uninstall_db" id="uninstall_db_yes" value="1" <?php echo Cart66Setting::getValue('uninstall_db') == 1 ? 'checked="checked" ' : '' ?>/>
              <label for="uninstall_db_yes"><?php _e('Yes', 'cart66'); ?></label>
              <input type="radio" name="uninstall_db" id="uninstall_db_no" value="" <?php echo Cart66Setting::getValue('uninstall_db') != 1 ? 'checked="checked" ' : '' ?>/>
              <label for="uninstall_db_no"><?php _e('No', 'cart66'); ?></label>
              <p class="description"><strong><?php _e( 'WARNING:', 'cart66'); ?></strong> <?php _e('Cart66 Lite and Cart66 Professional share the same database. If you are upgrading from Cart66 Lite to Professional and want to keep all your settings', 'cart66'); ?>, <strong><?php _e('do not delete the database', 'cart66'); ?></strong> <?php _e('when uninstalling Cart66 Lite', 'cart66'); ?>.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="debug-session_settings" class="pane">
      <h3><?php _e('Session Management', 'cart66'); ?></h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e('Database Sessions', 'cart66'); ?></th>
            <?php
              $session_type = Cart66Common::sessionType();
            ?>
            <td>
              <input type="radio" name="session_type" id="session_type_database" value="database" <?php echo $session_type == 'database' ? 'checked="checked" ' : '' ?>/>
              <label for="session_type_database"><?php _e( 'Yes' , 'cart66' ); ?></label>
              <input type="radio" name="session_type" id="session_type_native" value="native" <?php echo $session_type == 'native' ? 'checked="checked" ' : '' ?>/>
              <label for="session_type_native"><?php _e( 'No' , 'cart66' ); ?></label>
              <p class="description"><?php _e( 'Database sessions offer better performance but if you have trouble with them, try using the standard PHP sessions by disabling database sessions' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Disable IP Validation', 'cart66'); ?></th>
            <td>
              <input type="radio" name="session_ip_validation" id="session_ip_validation_yes" value="1" <?php echo Cart66Setting::getValue('session_ip_validation') == '1' ? 'checked="checked"' : '' ?>/>
              <label for="session_ip_validation_yes"><?php _e( 'Yes' , 'cart66' ); ?></label>
              <input type='radio' name='session_ip_validation' id='session_ip_validation_no' value="" <?php echo Cart66Setting::getValue('session_ip_validation') != '1'? 'checked="checked"' : '' ?>/>
              <label for="session_ip_validation_no"><?php _e( 'No' , 'cart66' ); ?></label>
              <p class="description"><?php _e( 'If the shopping cart seems to be dropping it\'s contents it could be because your ip address changes while you are shopping. If so, disable IP address validation.' , 'cart66' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Session Length', 'cart66'); ?></th>
            <td>
              <input type="text" name="session_length" id="session_length" class="small-text" value="<?php echo Cart66Setting::getValue('session_length'); ?>" />
              <span class="description"><?php _e( 'Set the length of the session in minutes. Leave this blank to keep the default session length of 30 minutes' , 'cart66' ); ?></span>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Session Table Status', 'cart66'); ?></th>
            <td>
              <?php
              global $wpdb;
              $wpdb->query('CHECK TABLE `' . Cart66Common::getTableName('sessions') . '` QUICK');
              if($wpdb->last_result[0]->Msg_text != "OK" && isset($_GET['sessions']) && $_GET['sessions'] == 'repair') {
                $wpdb->query('REPAIR TABLE `' . Cart66Common::getTableName('sessions') . '`');
                $wpdb->query('CHECK TABLE `' . Cart66Common::getTableName('sessions') . '` QUICK');
              }
              echo Cart66Setting::validateDebugValue($wpdb->last_result[0]->Msg_text,__("OK", "cart66"));
              if($wpdb->last_result[0]->Msg_text != "OK") { ?>
                <a href="?page=cart66-settings&tab=debug_settings&sessions=repair" class="button-secondary"><?php _e('Repair Table', 'cart66'); ?></a>
              <?php } ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <table class="form-table debug-submit">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <?php submit_button(); ?>
          </th>
          <td></td>
        </tr>
      </tbody>
    </table>
  </form>
  <div id="debug-debug_data" class="pane">
    <table class="form-table">
      <tbody>
        <?php if(Cart66Log::exists()): ?>
          <tr valign="top">
            <th scope="row"><?php _e('Error Log', 'cart66'); ?></th>
            <td>
              <form action="" method="post" style="display:inline-block">
                <input type="hidden" name="cart66-action" value="download log file" id="download-log-file" />
                <input type="submit" value="<?php _e('Download Log File', 'cart66'); ?>" class="button-secondary" />
              </form>
              <form action="" method="post" style="display:inline-block">
                <input type="hidden" name="cart66-action" value="clear log file" id="clear-log-file" />
                <input type="submit" value="<?php _e('Clear Log File', 'cart66'); ?>" class="button-secondary" />
              </form>
            </td>
          </tr>
        <?php endif; ?>
        <tr valign="top">
          <th scope="row"><?php _e('Cart66', 'cart66'); ?><?php echo CART66_PRO ? __(" Professional","cart66") : ""; ?> <?php _e('Version', 'cart66'); ?></th>
          <td>
            <?php echo Cart66Setting::getValue('version');?>
            
            <form action="" method="post" id="forcePluginUpdate">
              <input type="hidden" name="action" value="force_plugin_update" />
              <input type="submit" value="<?php _e('Check for updates', 'cart66'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>        
        <tr valign="top">
          <th scope="row"><?php _e('WordPress Version', 'cart66'); ?></th>
          <td>
            <?php echo get_bloginfo("version"); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('WPMU', 'cart66'); ?></th>
          <td>
            <?php echo Cart66Setting::validateDebugValue((!defined('MULTISITE') || !MULTISITE) ? "False" : "True", "False");  ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('PHP Version', 'cart66'); ?></th>
          <td>
            <?php echo phpversion(); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Session Save Path', 'cart66'); ?></th>
          <td>
            <?php echo ini_get("session.save_path"); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('MySQL Version', 'cart66'); ?></th>
          <td>
            <?php global $wpdb; ?>
            <?php echo $wpdb->db_version();?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('MySQL Mode', 'cart66'); ?></th>
          <td>
            <?php 
            $mode = $wpdb->get_row("SELECT @@SESSION.sql_mode as Mode"); 
            if(empty($mode->Mode)){
              $sqlMode = __("Normal", "cart66");
            }
            else {
              $sqlMode = $mode->Mode;
            }
            echo Cart66Setting::validateDebugValue($sqlMode,__("Normal", "cart66")); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Table Prefix', 'cart66'); ?></th>
          <td>
            <?php echo $wpdb->prefix; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Tables', 'cart66'); ?></th>
          <td>
            <?php 
            $required_tables = array(
              $wpdb->prefix . "cart66_products",
              $wpdb->prefix . "cart66_downloads",
              $wpdb->prefix . "cart66_promotions",
              $wpdb->prefix . "cart66_shipping_methods",
              $wpdb->prefix . "cart66_shipping_rates",
              $wpdb->prefix . "cart66_shipping_rules",
              $wpdb->prefix . "cart66_tax_rates",
              $wpdb->prefix . "cart66_cart_settings",
              $wpdb->prefix . "cart66_membership_reminders",
              $wpdb->prefix . "cart66_email_log",
              $wpdb->prefix . "cart66_orders",
              $wpdb->prefix . "cart66_order_items",
              $wpdb->prefix . "cart66_order_fulfillment",
              $wpdb->prefix . "cart66_inventory",
              $wpdb->prefix . "cart66_accounts",
              $wpdb->prefix . "cart66_account_subscriptions",
              $wpdb->prefix . "cart66_pp_recurring_payments",
              $wpdb->prefix . "cart66_sessions"
            );
            $matched_tables = $wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix."cart66_%'","ARRAY_N");
            if(empty($matched_tables)){
              $tableStatus = __("All Tables Are Missing!", "cart66");
            }
            else {
              foreach($matched_tables as $key=>$table){
                $cart_tables[] = $table[0];
              }

              $diff = array_diff($required_tables,$cart_tables);
              if(!empty($diff)){
                $tableStatus = __("Missing tables: ", "cart66") . '<br />';
                foreach($diff as $key=>$table){
                  $tableStatus .= "$table" . '<br />';
                }
              }
              else {
                $tableStatus = __("All Tables Present", "cart66");
              }
            }
            echo Cart66Setting::validateDebugValue($tableStatus,__("All Tables Present", "cart66"));
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Current Directory', 'cart66'); ?></th>
          <td>
            <?php echo getcwd(); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('WordPress URL', 'cart66'); ?></th>
          <td>
            <?php echo get_bloginfo('wpurl'); ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Server Name', 'cart66'); ?></th>
          <td>
            <?php echo $_SERVER['SERVER_NAME']; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Cookie Domain', 'cart66'); ?></th>
          <td>
            <?php $cookieDomain = parse_url( strtolower( get_bloginfo('wpurl') ) ); echo $cookieDomain['host']; ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Curl Test', 'cart66'); ?></th>
          <td>
            <?php
            if(!function_exists('curl_init')){ 
              echo "<span class=\"failedDebug\">" . __("CURL is not installed","cart66") . "</span>";
            }
            else {
              $cart66CurlTest = (isset($_GET['cart66_curl_test'])) ? $_GET['cart66_curl_test'] : false;
              if($cart66CurlTest == "run"){
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,"https://cart66.com/curl-test.php");
                curl_setopt($ch, CURLOPT_POST, 1); 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"curl_check=validate");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                $result = curl_exec($ch);
                curl_close($ch);
                echo ($result == "PASS") ? __("PASSED","cart66") : __("FAILED","cart66");
              }
              else{
                echo "<a href='admin.php?page=cart66-settings&tab=debug_settings&cart66_curl_test=run'>" . __("Run Test","cart66") . "</a>";
              }
            }
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Write Permissions', 'cart66'); ?></th>
          <td>
            <?php 
            $isWritable = (is_writable(CART66_PATH)) ? __("Writable", "cart66") : __("Not Writable", "cart66");
            echo Cart66Setting::validateDebugValue($isWritable,__("Writable", "cart66"));
            ?>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Subscription Reminders Last Checked', 'cart66'); ?></th>
          <td>
            <?php echo (Cart66Setting::getValue('daily_subscription_reminders_last_checked')) ? Cart66Common::getElapsedTime(date('Y-m-d H:i:s', Cart66Setting::getValue('daily_subscription_reminders_last_checked'))) : __("Never","cart66"); ?>
            <form action="" method="post" style="display:inline-block">
              <input type="hidden" name="cart66-action" value="check subscription reminders" id="cart66-action" />
              <input type="submit" value="<?php _e('Send Subscription Reminders', 'cart66'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Followup Emails Last Checked', 'cart66'); ?></th>
          <td>
            <?php echo (Cart66Setting::getValue('daily_followup_last_checked')) ? Cart66Common::getElapsedTime(date('Y-m-d H:i:s', Cart66Setting::getValue('daily_followup_last_checked'))) : __("Never","cart66"); ?>
            <form action="" method="post" style="display:inline-block">
              <input type="hidden" name="cart66-action" value="check followup emails" id="cart66-action" />
              <input type="submit" value="<?php _e('Send Followup Emails', 'cart66'); ?>" class="button-secondary" />
            </form>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e('Next Scheduled Cron Check', 'cart66'); ?></th>
          <td>
            <?php echo Cart66Common::getTimeLeft(date('Y-m-d H:i:s', Cart66Common::localTs(wp_next_scheduled('daily_subscription_reminder_emails')))) . ' (' . date('Y-m-d H:i:s', Cart66Common::localTs(wp_next_scheduled('daily_subscription_reminder_emails')) ) . ')'; ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
      $('#cart66-inner-tabs div.pane').hide();
      $('#cart66-inner-tabs div#<?php echo $tab; ?>').show();
      $('#cart66-inner-tabs ul li a.<?php echo $tab; ?>').addClass('current');
      
      if($('#debug-debug_data').attr('id') == '<?php echo $tab; ?>') {
        $('.debug-submit').hide();
      }
      
      $('#cart66-inner-tabs ul li a').click(function(){
        $('#cart66-inner-tabs ul li a').removeClass('current');
        $(this).addClass('current');
        var currentTab = $(this).attr('href');
        $('#cart66-inner-tabs div.pane').hide();
        $(currentTab).show();
        $('.debug-submit').show();
        if(currentTab == '#debug-debug_data') {
          $('.debug-submit').hide();
        }
        return false;
      });
    })
  })(jQuery);
</script>