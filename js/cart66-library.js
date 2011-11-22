(function($){
  $(document).ready(function(){
    $('.modalClose').click(function() {
      $('.Cart66Unavailable').fadeOut(800);
    });

    $('#Cart66CancelPayPalSubscription').click(function() {
      return confirm('Are you sure you want to cancel your subscription?\n');
    });
  })
})(jQuery);

function getCartButtonFormData(formId) {
	$jq = jQuery.noConflict();
  var theForm = $jq('#' + formId);
  var str = '';
  $jq('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $jq(this).attr('name');
        var val = $jq(this).val();
        str += name + '=' + encodeURIComponent(val) + '&';
      }
  );

  return str.substring(0, str.length-1);
}