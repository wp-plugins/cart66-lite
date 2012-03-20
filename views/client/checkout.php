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
/* <![CDATA[ */ 
  (function($){
    var states = <?php echo json_encode(Cart66Common::getZones()); ?>;
    
    function setState(frm, kind) {
      $('#' + frm + ' select[name="' + kind + '[state]"]').empty();
      var st = $('#' + frm + ' select[name="' + kind + '[country]"]').val();
      if(typeof states[st] == 'undefined') {
        $('#' + frm + ' select[name="' + kind + '[state]"]').attr('disabled', 'disabled');
        $('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
        $('#' + frm + ' select[name="' + kind + '[state]"]').hide(); 
        $('#' + frm + ' input[name="' + kind + '[state_text]"]').show();
      }
      else {
        $('#' + frm + ' select[name="' + kind + '[state]"]').removeAttr('disabled');
        $('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
        $('#' + frm + ' select[name="' + kind + '[state]"]').show(); 
        $('#' + frm + ' input[name="' + kind + '[state_text]"]').hide();
        for(var code in states[st]) {
          $('#' + frm + ' select[name="' + kind + '[state]"]').append('<option value="' + code + '">' + states[st][code] + '</option>');
        }
      }

      switch(st){
        case "US":
          $('.' + kind + '-state_label').html("<?php _e( 'State' , 'cart66' ); ?>: ");
          $('.' + kind + '-zip_label').html("<?php _e( 'Zip code' , 'cart66' ); ?>: ");
        break;
        case "AU":
          $('.' + kind + '-state_label').html("<?php _e( 'State' , 'cart66' ); ?>: ");
          $('.' + kind + '-zip_label').html("<?php _e( 'Post code' , 'cart66' ); ?>: ");
        break;
        default:
          $('.' + kind + '-state_label').html("<?php _e( 'Province' , 'cart66' ); ?>: ");
          $('.' + kind + '-zip_label').html("<?php _e( 'Post code' , 'cart66' ); ?>: ");
      }
    }

    function initStateField(frm, kind, country) {
      if(typeof states[country] == 'undefined') {
        $('#' + frm + ' select[name="' + kind + '[state]"]').attr('disabled', 'disabled');
        $('#' + frm + ' select[name="' + kind + '[state]"]').empty(); 
        $('#' + frm + ' select[name="' + kind + '[state]"]').hide(); 
        $('#' + frm + ' input[name="' + kind + '[state_text]"]').show();
      }

      setState(frm,kind);
    }
    
    $(document).ready(function(){
      // Dynamically configure billing state based on country
      $('.billing_countries').change(function() { 
        setState($(this).closest('form').attr('id'), 'billing');
      });

      // Dynamically configure shipping state based on country
      $('select[name="shipping[country]"]').live('change', function() { 
        setState($(this).closest('form').attr('id'), 'shipping');
      });

      // Set the sameAsBilling checkbox
      <?php if($_SERVER['REQUEST_METHOD'] == 'GET' && Cart66Setting::getValue('sameAsBillingOff') != 1): ?>
        $('.sameAsBilling').attr('checked', true);
      <?php else: ?>
        <?php
          if(isset($_POST['sameAsBilling']) && $_POST['sameAsBilling'] == '1') {
            ?>
            $('.sameAsBilling').attr('checked', true);
            <?php
          }
          else {
            ?>
            $('.shippingAddress').css('display', 'block');
            <?php
          }
        ?>
      <?php endif; ?>

      $('.sameAsBilling').click(function() {
        var frm = $(this).closest('form').attr('id');
        if($('#' + frm + ' input[name="sameAsBilling"]').attr('checked')) {
          $('#' + frm + ' .shippingAddress').css('display', 'none');
        }
        else {
          $('#' + frm + ' .shippingAddress').css('display', 'block');
        }
      });

      <?php if(isset($b['country']) && !empty($b['country'])): ?>
        $('.billing_countries').each(function(index) {
          var frm = $(this).closest('form').attr('id');
          initStateField(frm, 'billing', '<?php echo $b['country']; ?>');
        });

        $('.shipping_countries').each(function(index) {
          var frm = $(this).closest('form').attr('id');
          initStateField(frm, 'shipping', '<?php echo $s['country']; ?>');
        });
      <?php endif; ?>

      <?php if(isset($jqErrors) && is_array($jqErrors)): ?>
        <?php foreach($jqErrors as $val): ?>
          $('#<?php echo $val ?>').addClass('errorField');
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if(isset($b['state'])): ?>
        $("#billing-state").val("<?php echo $b['state']; ?>");
      <?php endif; ?>

      <?php if(isset($s['state'])): ?>
        $("#shipping-state").val("<?php echo $s['state']; ?>");
      <?php endif; ?>

      <?php if(isset($p['cardType'])) : ?>
        $("#payment-cardType").val("<?php echo $p['cardType']; ?>");
      <?php endif; ?>
      
      // prevent duplicate submissions
      $("#<?php echo $gatewayName ?>_form").submit(function(){
        $(".Cart66CompleteOrderButton").attr("disabled", "disabled");
      })
    })
  })(jQuery);
/* ]]> */
</script> 