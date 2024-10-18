function eelv_share_pressthis(url){
	t=window.open(url,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570');
	t.focus();  
}
jQuery(document).ready(function(){
	jQuery('#wp-admin-bar-share_post_group a, .sharepost-pressthis').click(function(){
            eelv_share_pressthis(jQuery(this).attr('href'));
            return false;
	});
});
