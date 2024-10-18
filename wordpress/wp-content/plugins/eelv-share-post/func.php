<?php

if(!defined('DOMAIN_CURRENT_SITE')){
    define('DOMAIN_CURRENT_SITE', $_SERVER['SERVER_NAME']);
}
/**
 *
 * @return array
 */
function eelv_share_get_domains(){
	$eelv_share_domains = get_site_option( 'eelv_share_domains' ,array(), true);
	$domains_map=array();
	foreach ($eelv_share_domains as $domain){
	 if(is_string($domain)) $domain = array($domain,$domain);
	 $domains_map[$domain[0]]=$domain[1];
	}
	$domains_map[DOMAIN_CURRENT_SITE]=DOMAIN_CURRENT_SITE;
	return $domains_map;
}


/////////////////////////////////////////////////////
// Thumbnails tools


/**
 *
 * @global $wpdb
 * @param int $blog_id
 * @param int $post_id
 * @return string or false
 */
function get_blog_post_thumbnail($blog_id, $post_id){
  global $wpdb;
	$base = $wpdb->base_prefix."".$blog_id."_posts";
	$basemeta = $wpdb->base_prefix."".$blog_id."_postmeta";
	if($matches[1]=='www'){
	  $base = $wpdb->base_prefix."posts";
	  $basemeta = $wpdb->base_prefix."postmeta";
	}

	$querymeta="
	SELECT `meta_value` FROM ".$basemeta."
	WHERE post_id = ".$post_id."
	AND meta_key = '_thumbnail_id'
	  LIMIT 0,1
	";
 $thumb_query_id=$wpdb->get_row($querymeta);
  if(is_object($thumb_query_id)){
	$query="
	SELECT `guid`,`post_title` FROM ".$base."
	WHERE ID = ".$thumb_query_id->meta_value;
	 $thumb_query=$wpdb->get_row($query);
	  if(is_object($thumb_query)){
		return $thumb_query;
	  }
  }
  return false;
}


/**
 *
 * @param string $html
 * @param int $post_id
 * @param int $post_thumbnail_id
 * @param string $size
 * @param array $attr
 * @return string
 */
function eelv_distant_thumbnail($html, $post_id, $post_thumbnail_id, $size, $attr){
	$distant_thumbnail_post_id = get_post_meta($post_id,'_thumbnail_from_shared_post',true);
	$distant_thumbnail_blog_id = get_post_meta($post_id,'_thumbnail_from_shared_blog',true);

	if($distant_thumbnail_post_id && $distant_thumbnail_blog_id){
		if(has_filter( "post_thumbnail_html", 'eelv_distant_thumbnail'))  remove_filter( "post_thumbnail_html", 'eelv_distant_thumbnail',30);
		switch_to_blog($distant_thumbnail_blog_id);
		$html=get_the_post_thumbnail($distant_thumbnail_post_id,$size,$attr);
		restore_current_blog();
		add_filter( "post_thumbnail_html", 'eelv_distant_thumbnail',30,5);
	}
	return $html;
}
/**
 *
 * @param string $content
 * @param int $post_id
 * @return string
 */
function eelv_admin_thumbnail( $content, $post_id ){
	$distant_thumbnail_post_id = get_post_meta($post_id,'_thumbnail_from_shared_post',true);
	$distant_thumbnail_blog_id = get_post_meta($post_id,'_thumbnail_from_shared_blog',true);

	if($distant_thumbnail_post_id && $distant_thumbnail_blog_id){
		switch_to_blog($distant_thumbnail_blog_id);
		$content.='<label>
		<p>
		   <input type="checkbox" name="eelv_share_featured_image" id="eelv_share_featured_image" value="'.$distant_thumbnail_post_id.'" checked="checked">
		   '.__( 'Synchronise featured image from distant post','eelv-share-post').'
		</p>
		'.get_the_post_thumbnail($distant_thumbnail_post_id,'thumb').'
	</label>
	<input type="hidden" name="eelv_share_featured_image_keep" value="1">';
		restore_current_blog();
	}
	return $content;
}



/////////////////////////////////////////////
// Text parsers

/**
 *
 * @param string $str
 * @return string
 */
function eelv_share_untag($str){
	return trim(str_replace(array('<','>',"\\r\\n",'"'),array('[',']',' ','\''),$str));
}
/**
 *
 * @param string $excerpt
 * @param bool $insert
 * @return string
 */
function eelv_share_parse_youtube($excerpt,$insert=false){
	$thumb='';
	preg_match_all('#[\n\t\r\s]http://www\.youtube\.com/watch\?v=(.+)\&?(.+)?[\n\t\r\s]#i',$excerpt,$yout, PREG_PATTERN_ORDER);
	if(is_array($yout)){
	  foreach($yout[0] as $id=>$match){
		$url=explode(' ',$yout[1][$id]);
		  $url=$url[0];
		$val="<iframe class='embeelv_iframe' src='http://www.youtube.com/embed/".$url."' width='250' height='150'>video</iframe>";
		if(!$insert) $thumb.= $val;
		else $excerpt=str_replace($match,$val,$excerpt);
	  }
	}
	if(!$insert) return $thumb;
	else return $excerpt;
}
/**
 *
 * @param string $excerpt
 * @param bool $insert
 * @return string
 */
function eelv_share_parse_dailymotion($excerpt,$insert=false){
	$thumb='';
	preg_match_all('#[\n\t\r\s]http://www\.dailymotion\.com/video/(.+)_??(.+)??[\n\t\r\s]#i',$excerpt,$dail, PREG_PATTERN_ORDER);
	if(is_array($dail)){
	  foreach($dail[0] as $id=>$match){
		  $url=explode(' ',$dail[1][$id]);
		  $url=$url[0];
		$val="<iframe class='embeelv_iframe' src='http://www.dailymotion.com/embed/video/".$url."' width='250' height='150'>video</iframe>";
		if(!$insert) $thumb.= $val;
		else $excerpt=str_replace($match,$val,$excerpt);
	  }
	}
	if(!$insert) return $thumb;
	else return $excerpt;
}
/**
 *
 * @param string $excerpt
 * @return string
 */
function eelv_share_parse_twitter($excerpt){
	preg_match_all('#[\n\t\r\s]https?://twitter\.com/(.+)/status/(.+)[\n\t\r\s]#i',$excerpt,$twi, PREG_PATTERN_ORDER);
	if(is_array($twi)){
	  foreach($twi[0] as $id=>$match){
		$twit = json_decode(file_get_contents('https://api.twitter.com/1/statuses/oembed.json?id='.$twi[2][$id].'&omit_script=true&hide_media=true&hide_thread=true&lang=fr'));
	     $parser = new htmlParser($twit->html);
 		 $twitxt = $parser->toArray();
		$val="<div class='embeelv_twit'>@".$twi[1][$id]." &laquo;".$twitxt[0]['innerHTML']."&raquo;</div>";
		$excerpt=str_replace($match,$val,$excerpt);
	  }
	}
	return $excerpt;
}
/**
 *
 * @param string $text
 * @return string
 */
function new_wp_trim_excerpt($text) {
  $raw_excerpt = $text;
  if ( '' == $text ) {
	  $text = get_the_content('');

	  $text = strip_shortcodes( $text );

	  $text = apply_filters('the_content', $text);
	  $text = str_replace(']]>', ']]>', $text);
	  $text = strip_tags($text, '<iframe>');
	  $excerpt_length = apply_filters('excerpt_length', 55);

	  $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
	  $words = preg_split('/(<a.*?a>)|\n|\r|\t|\s/', $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );
	  if ( count($words) > $excerpt_length ) {
		  array_pop($words);
		  $text = implode(' ', $words);
		  $text = $text . $excerpt_more;
	  } else {
		  $text = implode(' ', $words);
	  }
  }
  return apply_filters('new_wp_trim_excerpt', $text, $raw_excerpt);

}
