
(function ($) {

Drupal.behaviors.redirectAdmin = {
  attach: function (context) {
	  
    $('#qm-redirect-table th.select-all ').bind('change', function(context) {
	   
      var checked = $('#qm-redirect-table input:checkbox:checked').length;
      if (checked) {
        $('fieldset.questionmarkredirect-list-operations').slideDown();
      }
      else {
        $('fieldset.questionmarkredirect-list-operations').slideUp();
      }
    });
	
	    $('#qm-redirect-table tbody input:checkbox').bind('change', function(context) {
     
      var checked = $('#qm-redirect-table input:checkbox:checked').length;
      if (checked) {
        $('fieldset.questionmarkredirect-list-operations').slideDown();
      }
      else {
        $('fieldset.questionmarkredirect-list-operations').slideUp();
      }
    });
    $('fieldset.questionmarkredirect-list-operations').hide();
  }
};

})(jQuery);
