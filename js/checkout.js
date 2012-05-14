/**
 * This script requires the following variables from the parent page:
 *   $jqErrors -- an array of jQuery error information
 *   $s -- an array of shipping information
 *   $b -- an array of billing information
 *   $p -- an array of payment information
 */
(function($){
  
  function setState(frm, kind) {
    $('#' + frm + ' select[name="' + kind + '[state]"]').empty();
    var st = $('#' + frm + ' select[name="' + kind + '[country]"]').val();
    if(typeof C66.zones[st] == 'undefined') {
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
      for(var code in C66.zones[st]) {
        $('#' + frm + ' select[name="' + kind + '[state]"]').append('<option value="' + code + '">' + C66.zones[st][code] + '</option>');
      }
    }

    switch(st){
      case "US":
        $('.' + kind + '-state_label').html(C66.text_state + ": ");
        $('.' + kind + '-zip_label').html(C66.text_zip_code + ": ");
      break;
      case "AU":
        $('.' + kind + '-state_label').html(C66.text_state + ": ");
        $('.' + kind + '-zip_label').html(C66.text_post_code + ": ");
      break;
      default:
        $('.' + kind + '-state_label').html(C66.text_province + ": ");
        $('.' + kind + '-zip_label').html(C66.text_post_code + ": ");
    }
  }

  function initStateField(frm, kind, country) {
    if(typeof C66.zones[country] == 'undefined') {
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

    if(C66.same_as_billing == 1) {
      $('.sameAsBilling').attr('checked', true);
    }
    else {
      $('.sameAsBilling').attr('checked', false);
    }
    $('.shippingAddress').css('display', C66.shipping_address_display);
    
    $('.sameAsBilling').click(function() {
      var frm = $(this).closest('form').attr('id');
      if($('#' + frm + ' input[name="sameAsBilling"]').attr('checked')) {
        $('#' + frm + ' .shippingAddress').css('display', 'none');
      }
      else {
        $('#' + frm + ' .shippingAddress').css('display', 'block');
      }
    });
    
    if(C66.billing_country != '') {      
      $('.billing_countries').each(function(index) {
        var frm = $(this).closest('form').attr('id');
        initStateField(frm, 'billing', C66.billing_country);
      });
      
      $('.shipping_countries').each(function(index) {
        var frm = $(this).closest('form').attr('id');
        initStateField(frm, 'shipping', C66.shipping_country);
      });
    }
    
    $("#billing-state").val(C66.billing_state);
    $("#shipping-state").val(C66.shipping_state);
    $("#payment-cardType").val(C66.card_type);
    
    // prevent duplicate submissions
    $(C66.form_name).submit(function(){
      $(".Cart66CompleteOrderButton").attr("disabled", "disabled");
    });
    
    C66.error_field_names.forEach(function(field) {
      console.log(field);
      $(field).addClass('errorField');
    });
    
  })
})(jQuery);