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
  test = '';
  function updateAjaxTax() {
    var taxed = $('.ajax-tax-cart').val();
    if(taxed === 'true') {
      var ajaxurl = $('#confirm-url').val();
      var state = $('#billing-state').val();
      var zip = $('#billing-zip').val();
      var state_text = $('#billing-state_text').val();
      if($('.sameAsBilling').length !=0 && !$('.sameAsBilling').attr('checked')) {
        if($('#shipping-zip').length != 0) {
          var zip = $('#shipping-zip').val();
        }
        if($('#shipping-state_text').length != 0) {
          var state_text = $('#shipping-state_text').val();
        }
        if($('#shipping-state').length != 0) {
          var state = $('#shipping-state').val();
        }
      }
      else if($('#billing-zip').length == 0) {
        if($('#shipping-zip').length != 0) {
          var zip = $('#shipping-zip').val();
        }
        if($('#shipping-state_text').length != 0) {
          var state_text = $('#shipping-state_text').val();
        }
        if($('#shipping-state').length != 0) {
          var state = $('#shipping-state').val();
        }
      }
      if(zip == '') {
        return false;
      }
      if(state == 0 && state_text == '') {
        return false;
      }
      $('.ajax-spin').show();
      var gateway = $('#cart66-gateway-name').val();
      $.ajax({
        type: "POST",
        url: ajaxurl + '=4',
        data: {
          state: state,
          state_text: state_text,
          zip: zip,
          gateway: gateway
        },
        dataType: 'json',
        success: function(response) {
          if(response.tax != '$0.00') {
            $('.tax-row').removeClass('hide-tax-row').addClass('show-tax-row');
            $('.tax-block').removeClass('hide-tax-block').addClass('show-tax-block');
          }
          $('.tax-amount').html(response.tax);
          $('.grand-total-amount').html(response.total);
          $('.tax-rate').html(response.rate);
          $('.ajax-spin').hide();
          if(test == '') {
            test = 'running';
            $('.tax-update').fadeIn(500).delay(2300).fadeOut(500);
            $('.tax-update').queue(function () {
              test = '';
              $(this).dequeue();
            });
          }
        },
        error: function(xhr,err){
          //alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
        }
      });
    }
    return false;
  }
  
  $(document).ready(function(){
    var shipping_countries = $('#shipping-country').html();
    var billing_countries = $('#billing-country').html();
    
    // Dynamically configure billing state based on country
    $('.billing_countries').change(function() { 
      setState($(this).closest('form').attr('id'), 'billing');
    });

    // Dynamically configure shipping state based on country
    $('select[name="shipping[country]"]').on('change', function() { 
      setState($(this).closest('form').attr('id'), 'shipping');
    });

    if(C66.same_as_billing == 1) {
      $('.sameAsBilling').attr('checked', true);
    }
    else {
      $('.sameAsBilling').attr('checked', false);
    }
    $('.shippingAddress').css('display', C66.shipping_address_display);
    
    $('.sameAsBilling').each(function() {
      var frm = $(this).closest('form').attr('id');
      if($('#' + frm + ' input[name="sameAsBilling"]').attr('checked')) {
        $('#' + frm + ' .billing_countries').html(shipping_countries);
        setState(frm, 'billing');
        $('.limited-countries-label-billing').show();
        $('#billing-state_text, #billing-state, #billing-zip').addClass('ajax-tax');
        $('#shipping-state_text, #shipping-state, #shipping-zip').removeClass('ajax-tax');
        $('#billing_tax_update').addClass('tax-update').show();
        $('#shipping_tax_update').removeClass('tax-update').hide();
      }
      else {
        $('#' + frm + ' .billing_countries').html(billing_countries);
        setState(frm, 'billing');
        $('.limited-countries-label-billing').hide();
        $('#billing-state_text, #billing-state, #billing-zip').removeClass('ajax-tax');
        $('#shipping-state_text, #shipping-state, #shipping-zip').addClass('ajax-tax');
        $('#billing_tax_update').removeClass('tax-update').hide();
        $('#shipping_tax_update').addClass('tax-update').show();
      }
    })
    
    $('.sameAsBilling').click(function() {
      var frm = $(this).closest('form').attr('id');
      if($('#' + frm + ' input[name="sameAsBilling"]').attr('checked')) {
        var billing_country = $('#' + frm + ' .billing_countries').val();
        $('#' + frm + ' .billing_countries').html(shipping_countries);
        $('#' + frm + ' .billing_countries').val(billing_country);
        $('#' + frm + ' .billing_countries option').each(function() {
          if($(this).val() == $('#' + frm + ' .billing_countries').val() && $(this).is(':disabled')) {
            $('#' + frm + ' .billing_countries').val('');
          }
        })
        //setState(frm, 'billing');
        $('.limited-countries-label-billing').show();
        $('#' + frm + ' .shippingAddress').css('display', 'none');
        $('#billing-state_text, #billing-state, #billing-zip').addClass('ajax-tax');
        $('#shipping-state_text, #shipping-state, #shipping-zip').removeClass('ajax-tax');
        $('#billing_tax_update').addClass('tax-update').show();
        $('#shipping_tax_update').removeClass('tax-update').hide();
      }
      else {
        $('#' + frm + ' .shippingAddress').css('display', 'block');
        var billing_country = $('#' + frm + ' .billing_countries').val();
        $('#' + frm + ' .billing_countries').html(billing_countries);
        $('#' + frm + ' .billing_countries').val(billing_country);
        //setState(frm, 'billing');
        $('.limited-countries-label-billing').hide();
        $('#billing-state_text, #billing-state, #billing-zip').removeClass('ajax-tax');
        $('#shipping-state_text, #shipping-state, #shipping-zip').addClass('ajax-tax');
        $('#billing_tax_update').removeClass('tax-update').hide();
        $('#shipping_tax_update').addClass('tax-update').show();
      }
      updateAjaxTax();
    });
    $('#billing-state, #billing-zip, #billing-state_text, #shipping-state, #shipping-zip, #shipping-state_text').listenForChange();
    $('.ajax-tax').on("change", function() {
      updateAjaxTax();
    })
    
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
    
    $(C66.error_field_names).each(function(key, field) {
      $(field).addClass('errorField');
    });
    
  })
})(jQuery);
(function($) {
  $.fn.listenForChange = function(options) {
    settings = $.extend({
      interval: 200 // in microseconds
    }, options);
    
    var jquery_object = this;
    var current_focus = null;
    
    jquery_object.focus(function() {
      current_focus = this;
    }).blur(function() {
      current_focus = null;
    });
    
    setInterval(function() {
      // allow
      jquery_object.each(function() {
        // set data cache on element to input value if not yet set
        if ($(this).data('change_listener') == undefined) {
          $(this).data('change_listener', $(this).val());
          return;
        }
        // return if the value matches the cache
        if ($(this).data('change_listener') == $(this).val()) {
          return;
        }
        // ignore if element is in focus (since change event will fire on blur)
        if (this == current_focus) {
          return;
        }
        // if we make it here, manually fire the change event and set the new value
        $(this).trigger('change');
        $(this).data('change_listener', $(this).val());
      });
    }, settings.interval);
    return this;
  };
})(jQuery);