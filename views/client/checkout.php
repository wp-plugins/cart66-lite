<?php
/**
 * This script requires the following variables from the parent page:
 *   $jqErrors -- an array of jQuery error information
 *   $s -- an array of shipping information
 *   $b -- an array of billing information
 *   $p -- an array of payment information
 */
?>
<script type="text/javascript">

  var states = <?php echo json_encode(Cart66Common::getZones()); ?>;
  
  function setState(frm, kind) {
    $jq('#' + frm + ' select[name="' + kind + '[state]"]').empty();
    var st = $jq('#' + frm + ' select[name="' + kind + '[country]"]').val();
    if(typeof states[st] == 'undefined') {
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').attr('disabled', 'disabled');
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').hide(); 
      $jq('#' + frm + ' input[name="' + kind + '[state_text]"]').show();
    }
    else {
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').removeAttr('disabled');
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').show(); 
      $jq('#' + frm + ' input[name="' + kind + '[state_text]"]').hide();
      for(var code in states[st]) {
        $jq('#' + frm + ' select[name="' + kind + '[state]"]').append('<option value="' + code + '">' + states[st][code] + '</option>');
      }
    }
  }
  
  function initStateField(frm, kind, country) {
    if(typeof states[country] == 'undefined') {
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').attr('disabled', 'disabled');
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
      $jq('#' + frm + ' select[name="' + kind + '[state]"]').hide(); 
      $jq('#' + frm + ' input[name="' + kind + '[state_text]"]').show();
    }
  }
  
  $jq = jQuery.noConflict();
  $jq('document').ready(function() {

    // Dynamically configure billing state based on country
    $jq('.billing_countries').change(function() { 
      setState($jq(this).closest('form').attr('id'), 'billing');
    });
    
    // Dynamically configure shipping state based on country
    $jq('select[name="shipping[country]"]').live('change', function() { 
      setState($jq(this).closest('form').attr('id'), 'shipping');
    });
    
    // Set the sameAsBilling checkbox
    <?php if($_SERVER['REQUEST_METHOD'] == 'GET'): ?>
      $jq('.sameAsBilling').attr('checked', true);
    <?php else: ?>
      <?php
        if(isset($_POST['sameAsBilling']) && $_POST['sameAsBilling'] == '1') {
          ?>
          $jq('.sameAsBilling').attr('checked', true);
          <?php
        }
        else {
          ?>
          $jq('.shippingAddress').css('display', 'block');
          <?php
        }
      ?>
    <?php endif; ?>
    
    $jq('.sameAsBilling').click(function() {
      var frm = $jq(this).closest('form').attr('id');
      if($jq('#' + frm + ' input[name="sameAsBilling"]').attr('checked')) {
        $jq('#' + frm + ' .shippingAddress').css('display', 'none');
      }
      else {
        $jq('#' + frm + ' .shippingAddress').css('display', 'block');
      }
    });
    
    <?php if(isset($b['country']) && !empty($b['country'])): ?>
      $jq('.billing_countries').each(function(index) {
        var frm = $jq(this).closest('form').attr('id');
        initStateField(frm, 'billing', '<?php echo $b['country']; ?>');
      });
    
      $jq('.shipping_countries').each(function(index) {
        var frm = $jq(this).closest('form').attr('id');
        initStateField(frm, 'shipping', '<?php echo $s['country']; ?>');
      });
    <?php endif; ?>
    
    <?php if(isset($jqErrors) && is_array($jqErrors)): ?>
      <?php foreach($jqErrors as $val): ?>
        $jq('#<?php echo $val ?>').addClass('errorField');
      <?php endforeach; ?>
    <?php endif; ?>
    
  });
  
  
</script>