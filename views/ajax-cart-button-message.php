<div id="response_warning" class="Cart66AjaxMessage Cart66Warning warning_<?php echo $data['id']; ?>" style="display: none;">
  <h2><?php _e( 'Inventory Restriction' , 'cart66' ); ?></h2>
  <div id="message_warning" class="warning_message_<?php echo $data['id']; ?>"></div>
  <input type="button" name="close" value="<?php _e( 'OK' , 'cart66' ); ?>" class="Cart66ButtonSecondary modalClose"/>
</div>
<div id="response_error" class="Cart66AjaxMessage Cart66Error error_<?php echo $data['id']; ?>" style="display: none;">
  <h2><?php _e( 'Inventory Failure' , 'cart66' ); ?></h2>
  <div id="message_error" class="error_message_<?php echo $data['id']; ?>"></div>
  <input type="button" name="close" value="<?php _e( 'OK' , 'cart66' ); ?>" class="Cart66ButtonSecondary modalClose"/>
</div>
<div id="response_success" class="Cart66AjaxMessage Cart66Success success_<?php echo $data['id']; ?>" style="display: none;">
  <h2><?php _e( 'Success' , 'cart66' ); ?>!</h2>
  <div id="message_success" class="success_message_<?php echo $data['id']; ?>"></div>
</div>