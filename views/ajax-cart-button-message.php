<div id="response_warning" class="alert-message warning_<?php echo $data['id']; ?>" style="display: none;">
  <p><strong><span class="message-header"><?php _e( 'Inventory Restriction' , 'cart66' ); ?></span></strong></p>
  <div id="message_warning" class="warning_message_<?php echo $data['id']; ?>"></div>
  <input type="button" name="close" value="<?php _e( 'OK' , 'cart66' ); ?>" class="Cart66ButtonSecondary modalClose"/>
</div>
<div id="response_error" class="alert-message alert-error error_<?php echo $data['id']; ?>" style="display: none;">
  <p><strong><span class="message-header"><?php _e( 'Inventory Failure' , 'cart66' ); ?></span></strong></p>
  <div id="message_error" class="error_message_<?php echo $data['id']; ?>"></div>
  <input type="button" name="close" value="<?php _e( 'OK' , 'cart66' ); ?>" class="Cart66ButtonSecondary modalClose"/>
</div>
<div id="response_success" class="alert-message success success_<?php echo $data['id']; ?>" style="display: none;">
  <p><strong><span class="message-header"><?php _e( 'Success' , 'cart66' ); ?>!</span></strong></p>
  <div id="message_success" class="success_message_<?php echo $data['id']; ?>"></div>
</div>