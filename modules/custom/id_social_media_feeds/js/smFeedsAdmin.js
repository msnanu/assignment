(function($, Drupal){
  jQuery('#socialmediafeedtwitterconfig-admin-settings #edit-delete').click(function(e){
    if(confirm(Drupal.t('Do you really want to delte this Account Configuraion?')) === true){
      jQuery('#socialmediafeedtwitterconfig-admin-settings').submit();
    }
    e.preventDefault();
  });
})(jQuery, Drupal)