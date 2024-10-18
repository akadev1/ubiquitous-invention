<?php
/********************** ADMIN SETTINGS */
add_action( 'admin_menu', 'eelv_share_ajout_menu');
function eelv_share_ajout_menu() {
	add_submenu_page('options-general.php',__('Post sharing', 'eelv-share-post' ), __('Post sharing', 'eelv-share-post' ), 'manage_options', 'eelv_share_configuration', 'eelv_share_configuration');
}
function eelv_share_configuration(){
  if( $_REQUEST[ 'type' ] == 'update' ) {    
      update_option( 'eelv_share_options', $_REQUEST['eelv_share_options'] );
	       
      ?>
      <div class="updated"><p><strong><?php _e('Options saved', 'eelv-share-post' ); ?></strong></p></div>
      <?php 
    }
   $eelv_share_options = get_option( 'eelv_share_options' );
  ?>  
        <div class="wrap">
        <div id="icon-edit" class="icon32"><br/></div>
        <h2><?=_e('Post sharing', 'eelv-share-post' )?></h2>
        
    <form name="eelv_share_option_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
    <input type="hidden" name="type" value="update">
    
        
        <table class="widefat" style="margin-top: 1em;">
            <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Default values', 'eelv-share-post' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="30%">
                        <label for="eelv_share_options_s"><?=_e('Default values for text length to display :', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="number" name="eelv_share_options[s]"  size="3"  id="eelv_share_options_s"  value="<?=$eelv_share_options['s']?>" />
                        <legend><?=_e('Can be modified for each post ex: http://eelv.fr?p=[$post_ID]&s=[$size] where $size is the number of words to display. Value 0 shows all the text, default is 55 words', 'eelv-share-post' )?></legend>
                   </td>
                 </tr>
             </tbody>
             <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Configuration', 'eelv-share-post' ) ?></th>
                </tr>
            </thead>
            <tbody>
           		 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_l"><?=_e('number of caracters to display in the loop :', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="number" name="eelv_share_options[l]"  size="3"  id="eelv_share_options_l"  value="<?=$eelv_share_options['l']?>" />
                        <legend><?=_e('default is 400', 'eelv-share-post' )?></legend>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_a"><?=_e('Open target post directly from the loop', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[a]"  size="60"  id="eelv_share_options_a"  <?php if(abs($eelv_share_options['a'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_i"><?=_e('Display post thumbnail in the loop', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[i]"  size="60"  id="eelv_share_options_i"  <?php if(abs($eelv_share_options['i'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_s"><?=_e('Display youtube links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[y]"  size="60"  id="eelv_share_options_y"  <?php if(abs($eelv_share_options['y'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_d"><?=_e('Display dailymotion links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[d]"  size="60"  id="eelv_share_options_d"  <?php if(abs($eelv_share_options['d'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
                 <tr>
                    <td width="30%">
                        <label for="eelv_share_options_t"><?=_e('Display twitter links', 'eelv-share-post' )?></label>
                    </td><td>
                        <input  type="checkbox" name="eelv_share_options[t]"  size="60"  id="eelv_share_options_t"  <?php if(abs($eelv_share_options['t'])!=0){ echo'checked';} ?> value="1"/>
                   </td>
                 </tr>
               </tbody>
               <tfoot>  
                 <tr>
                    <td colspan="2">
                        <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Save changes', 'eelv-share-post' ) ?>" />
                        </p>                    
                    </td>
                </tr>
            </tfoot>
        </table>
        
    </form>
    </div>
    
<?php
}



/********************** NETWORK ADMN SETTINGS */
add_action( 'network_admin_menu', 'eelv_share_ajout_network_menu');
function eelv_share_ajout_network_menu() {
  add_submenu_page('settings.php', __('Post sharing', 'eelv-share-post' ), __('Post sharing', 'eelv-share-post' ), 'Super Admin', 'eelv_share_network_configuration', 'eelv_share_network_configuration');   
}

function eelv_share_network_configuration(){
  if( $_REQUEST[ 'type' ] == 'update' ) { 
  		$eelv_share_domains=array();
  		foreach ($_REQUEST['eelv_share_domains'] as $domain){
	  		if(!empty($domain[0]) && !empty($domain[1])){
				$eelv_share_domains[]=$domain;
			}
  		}
      update_site_option( 'eelv_share_domains', $eelv_share_domains );
	       
      ?>
      <div class="updated"><p><strong><?php _e('Options saved', 'eelv-share-post' ); ?></strong></p></div>
      <?php 
    }
   $eelv_share_domains = get_site_option( 'eelv_share_domains' );
  ?>  
        <div class="wrap">
        <div id="icon-edit" class="icon32"><br/></div>
        <h2><?=_e('Post sharing', 'eelv-share-post' )?></h2>
        
    <form name="eelv_share_net_option_form" id="eelv_share_net_option_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
    <input type="hidden" name="type" value="update">
    
        
        <table class="widefat" style="margin-top: 1em;">
            <thead>
                <tr>
                  <th scope="col" colspan="2"><?= __( 'Configuration ', 'eelv-share-post' ) ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="30%">
                        <label for="eelv_share_domains"><?=_e('Other domains to parse :', 'eelv-share-post' )?></label>
                        <legend><?=_e('Use it for network domain mapping', 'eelv-share-post' )?></legend>
                    </td><td>
                    	<table>
                           <thead>
                                <tr>
                                  <th scope="col"><?php _e( 'External domain', 'eelv-share-post' ) ?></th>
                                  <th scope="col" colspan="2"><?php _e( 'Wordpress domain', 'eelv-share-post' ) ?></th>
                                </tr>
                            </thead>
                            <tbody id="eelv_share_domains_list">
                            	<?php $i=0; foreach ($eelv_share_domains as $domain){ ?>
									<?php if(is_string($domain)) $domain = array($domain,$domain); ?>
                                    <tr>
                                      <td scope="col"><input type="text" id="eelv_share_domains_ext_<?=$i?>" name="eelv_share_domains[<?=$i?>][0]" value="<?=$domain[0]?>" class="wide" size="36"/></td>
                                      <td scope="col"><input type="text" id="eelv_share_domains_int_<?=$i?>" name="eelv_share_domains[<?=$i?>][1]" value="<?=$domain[1]?>" class="wide" size="36"/></td>
                                      <td scope="col"><a class="button" data-id="<?=$i?>">X</a></td>
                                    </tr>
								<?php $i++; } ?>
                            </tbody>
                            <tfoot>
                            	<tr>
                                	<td colspan="3">
                                    <a id="eelv_share_add_domain" class="button"><?php _e( 'Add a domain', 'eelv-share-post' ) ?></a>
                                    <script>
									var i=<?=$i?>;
									function delbuttons(){
										jQuery('#eelv_share_domains_list a').click(function(){
											var id = jQuery(this).data('id');
											jQuery('#eelv_share_domains_ext_'+id).attr('value','');
											jQuery('#eelv_share_domains_int_'+id).attr('value','');
											jQuery('#eelv_share_net_option_form').submit();
										});
									}
									jQuery(document).ready(function(e) {
                                        jQuery('#eelv_share_add_domain').click(function(){
											jQuery('#eelv_share_domains_list').append('<tr><td scope="col"><input type="text" id="eelv_share_domains_ext_'+i+'" name="eelv_share_domains['+i+'][0]" value="" class="wide" size="26"/></td><td scope="col"><input type="text" id="eelv_share_domains_int_'+i+'" name="eelv_share_domains['+i+'][1]" value="" class="wide" size="26"/></td><td scope="col"><a class="button" data-id="'+i+'">X</a></td></tr>');
											i++;
											delbuttons();
										});
										delbuttons();
                                    });
									</script>
                                    </td>
                                 </tr>
                            	<tr>
                                  <td scope="col">ex: urbancube.fr</td>
                                  <td scope="col" colspan="2">eelv</td>
                                </tr>
                                <tr>
                                  <td colspan="3"><?php _e('for final blog url : ', 'eelv-share-post' ) ?> <u>eelv.<?=DOMAIN_CURRENT_SITE?></u> = <u>urbancube.fr</u></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                   </td>
                 </tr>
                     
                 <tr>
                    <td colspan="2">
                        <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Save changes', 'eelv-share-post' ) ?>" class="button-primary" />
                        </p>                    
                    </td>
                </tr>
            </tbody>
        </table>
        
    </form>
    </div>
    
<?php
}
