(function($){
  $(document).ready(function(){
    $('.ajaxSettingForm').submit(function() {
      var data = getFormData($(this).attr('id'));
      $.ajax({
          type: "POST",
          url: ajaxurl,
          data: data,
          dataType: 'json',
          success: function(result) {
            $('#saveResult').html("<div id='saveMessage' class='" + result[0] + "'></div>");
            $('#saveMessage').append("<p>" + result[1] + "</p>").hide().fadeIn(1500).delay(5000).fadeOut(1500);
          }
      });
      return false;
    });
  })
})(jQuery);

function getFormData(formId) {
	$jq = jQuery.noConflict();
  var theForm = $jq('#' + formId);
  var str = '';
  $jq('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $jq(this).attr('name');
        var val = encodeURIComponent($jq(this).val());
        str += name + '=' + val + '&';
      }
  );
  return str.substring(0, str.length-1);
}