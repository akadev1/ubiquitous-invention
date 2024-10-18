<?php
/*
Plugin Name: Contact Form 7 Tiny MCE
Plugin URI: http://ecolosites.eelv.fr/cf7mce
Description: Add tiny MCE to CF7 editor
Author: bastho
Author URI: http://urbancube.fr/
Text Domain: wpcf7
Domain Path: /languages/
Version: 1.1.0
*/
add_action('wpcf7_admin_footer','cf7mce_add');

function cf7mce_add($contact_form){
        cf7mce_editor($contact_form);
        wp_enqueue_script('cf7mce', plugins_url('/cf7mce.js', __FILE__), array('jquery'), '', true);
}
function cf7mce_editor($contact_form){
    echo'<div id="tiny-mce-hidden-4-cf7">';
        wp_editor( $contact_form->prop('form'),'hiddeneditor',array('teeny'=>true));
    echo'</div>';
}
