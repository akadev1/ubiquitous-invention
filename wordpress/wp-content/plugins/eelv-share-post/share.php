<?php
/*
  Plugin Name: Share Post
  Plugin URI: http://ecolosites.eelv.fr/eelv-share-post/
  Description: Share a post link from a blog to another blog on the same WP multisite network and include the post content !
  Version: 1.0.4
  Author: bastho, n4thaniel // EELV
  Author URI: http://ecolosites.eelv.fr/
  License: GPL v2
  Text Domain: eelv-share-post
  Domain Path: /languages/
  Network: 1
 */


require( dirname(__FILE__) . '/func.php' );

$EELV_Share_Posts = new EELV_Share_Posts();
class EELV_Share_Posts {
    private $table_share;
    function __contstruct() {
        add_action('init', array($this, 'init'));

        add_action('wp_enqueue_scripts', array($this, 'scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_filter( 'press_this_data', array($this, 'pressthis_data') );
        add_action('save_post', array($this, 'save_post'));
        add_action('admin_post_eelv-share-post-suggest', array(&$this, 'suggest'));
        add_action('add_meta_boxes', array($this, 'add_custom_boxes'));

        add_action('admin_bar_menu', array($this, 'adminbar'), 999);
        add_action('wp_head', array($this, 'header'), 1);

	add_action('wp_dashboard_setup', array($this, 'dashboard_setup'));

        global $wpdb;
        $this->table_share = $wpdb->base_prefix.'share_posts';
    }

    // PHP4 constructor
    function EELV_Share_Posts() {
        $this->__contstruct();
    }

    /**
     * Check for the version of a SQL file et update DB if needed
     * @param string $file
     */
    function sql_update($file){
        $sql_path = plugin_dir_path(__FILE__).'sql/'.$file.'.sql';
        $sql_infos = get_file_data($sql_path,array('Author'=>'-- Author','Version'=>'-- Version'));
        $option = 'eelv_share_post_'.basename($sql_path);
        if(!empty($sql_infos['Version'])){
            $current_db_version = get_site_option($option);
            if($current_db_version!=$sql_infos['Version']){
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                $sql_content = sprintf(file_get_contents($sql_path), $this->table_share );
                $delta = dbDelta($sql_content);
                update_site_option($option, $sql_infos['Version']);
            }
        }
    }
    /**
     * Adds a post in the featured posts list
     * @global \wpdb $wpdb
     * @param int $blog_id
     * @param int $post_id
     * @param string $url
     * @param string $desc
     * @return boolean
     */
    function add_suggest($blog_id, $post_id, $url, $desc){
        global $wpdb;
        return($wpdb->insert($this->table_share,
                    array(
                        'blog_id'=>$blog_id,
                        'post_id'=>$post_id,
                        'post_url'=>$url,
                        'share_description'=>$desc,
                        'user_id'=>get_current_user_id(),
                        'post_date'=>date('Y-m-d H:i:s')
                    )
                ));
    }
    /**
     * Checks if a post is in the featured posts list
     * @global \wpdb $wpdb
     * @param int $blog_id
     * @param int $post_id
     * @return int
     */
    function is_suggested($blog_id, $post_id){
        global $wpdb;
        $result = $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT `id` FROM `'.$this->table_share.'` WHERE `blog_id`=%d AND `post_id`=%d',
                    $blog_id,
                    $post_id
                    )
                );
        return $result;
    }
    /**
     * Remove a post from the feature posts list
     * @global \wpdb $wpdb
     * @param int $blog_id
     * @param int $post_id
     * @return boolean
     */
    function unsuggest($blog_id, $post_id){
        global $wpdb;
        return $wpdb->delete( $this->table_share, array('blog_id'=>$blog_id, 'post_id'=>$post_id) );
    }
    /**
     * Retreives a list of featured posts
     * @global \wpdb $wpdb
     * @param array $_params
     * @param $_params[page]=1
     * @param $_params[per_page]=20
     * @param $_params[search]=''
     * @param $_params[orderby]='post_date'
     * @param $_params[page]='DESC'
     * @return array
     */
    function suggested($_params = array()){
        global $wpdb;
        $params = wp_parse_args( (array) $_params, array(
                    'page'=>1,
                    'per_page'=>20,
                    'search'=>'',
                    'orderby'=>'post_date',
                    'order'=>'DESC'
                )
            );
        return $wpdb->get_results( 'SELECT * FROM `'.$this->table_share.'` '
                . 'WHERE '
                . (empty($params['search']) ? '1' : '`share_description`LIKE\'%'.$params['search'].'%\'').' '
                . 'ORDER BY `'.$params['orderby'].'`'.$params['order'].' '
                . 'LIMIT '.($params['page']-1)*$params['per_page'].','.$params['per_page']
                ,
                OBJECT
            );
    }

    /**
     * Adds the scripts
     */
    function scripts() {
        wp_enqueue_style(
            'eelv-share-post', plugins_url('/css/share.css', __FILE__), false, null
        );
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'eelv-share-post', plugins_url('/js/share.js', __FILE__), array('jquery'), false, false
        );
    }

    /**
     * Adds scripts in the admin
     */
    function admin_scripts() {
        $this->scripts();
        wp_enqueue_script(
            'eelv-share-post-admin', plugins_url('/js/share-admin.js', __FILE__), array('jquery'), false, false
        );
    }

    /**
     * Initialize features
     */
    function init() {
        load_plugin_textdomain('eelv-share-post', false, 'eelv-share-post/languages');

        require( dirname(__FILE__) . '/htmlparser.php' );
        require( dirname(__FILE__) . '/admin.php' );

        $this->sql_update('share');

        add_filter("post_thumbnail_html", 'eelv_distant_thumbnail', 30, 5);
        add_filter('admin_post_thumbnail_html', 'eelv_admin_thumbnail', 30, 2);

        /* IN THE LOOP */
        remove_filter('get_the_excerpt', 'wp_trim_excerpt');
        remove_filter('get_the_excerpt', 'wp_trim_excerpt', 100);
        //add_filter('get_the_excerpt', 'new_wp_trim_excerpt');

        add_filter('get_the_excerpt', array($this, 'embed_exerpt'), 999);
        //add_filter('excerpt_more', array($this, 'embed_exerpt')');
        add_filter('the_content_rss', array($this, 'embed_exerpt'));

        /* SINGLE PAGE */
        $eelv_share_domains = eelv_share_get_domains();

        foreach ($eelv_share_domains as $domain => $internal_domain) {
            $id_domain = str_replace(array('.', '-'), '', $domain);
            wp_embed_register_handler('embedInMultiSite_' . $id_domain . '_p', '/<p[^>]*>https?:\/\/(.+)?(' . str_replace('.', '\.', $domain) . ')\/\?p=(\d+)(\&.+)?<\/p>/i', array($this, 'embed_locals'));
            wp_embed_register_handler('embedInMultiSite_' . $id_domain, '#https?://(.+)?(' . str_replace('.', '\.', $domain) . ')/\?p=(\d+)(\&.+)?#i', array($this, 'embed_locals'));
        }
    }
    function dashboard_setup() {
        wp_add_dashboard_widget('featured_posts', __('Suggested posts','eelv-share-post'), array($this, 'dashboard_widget'));
    }

    function embed_exerpt($excerpt) {
        $eelv_share_domains = eelv_share_get_domains();
        $sharer = false;
        $tumb = '';
        $original_thumbnail = has_post_thumbnail();


        $eelv_share_options = get_option('eelv_share_options');
        if (!isset($eelv_share_options['l']) || $eelv_share_options['l'] == 0 || empty($eelv_share_options['l'])) {
            $eelv_share_options['l'] = 400;
        }

        $domains_to_parse = array_keys($eelv_share_domains);

        // INTERNAL LINKS REFERENCES
        preg_match_all('#https?://([a-zA-Z0-9\-\.]+)?(' . str_replace('.', '\.', implode('|', $domains_to_parse)) . ')/\?p=(\d+)([a-zA-Z0-9=;\&]+)?[\n\t\s ]#i', $excerpt, $out, PREG_PATTERN_ORDER);
        if (is_array($out)) {

            $sharer = true;
            $thumb_output = false;
            foreach ($out[0] as $id => $match) {
                $blogname = substr($out[1][$id], 0, -1);
                $domain = $out[2][$id];
                $postid = $out[3][$id];
                parse_str($out[4][$id], $vars);

                if (isset($eelv_share_domains[$domain]) && $domain != $eelv_share_domains[$domain]) {
                    $blogname = $eelv_share_domains[$domain];
                }
                if (empty($blogname)) {
                    $blogname = 'www';
                    $blog = get_blog_details($domain);
                    $blog_post = get_blog_post(1, $postid);
                } else {
                    $blog = get_blog_details($blogname);
                    $blog_post = get_blog_post($blog->blog_id, $postid);
                }


                // THUMBNAIL
                if (isset($eelv_share_options['i']) && $eelv_share_options['i'] == 1) {
                    if ($original_thumbnail == false && $thumb_output == false) {
                        if (false !== $image = get_blog_post_thumbnail($blog->blog_id, $blog_post->ID)) {
                            $tumb.='<img src="' . $image->guid . '" alt="' . $image->post_name . '" class="embeelv_img"/>';
                            $thumb_output = true;
                        }
                    }
                }
                $content = substr(strip_tags($blog_post->post_content), 0, $eelv_share_options['l']) . '...';
                $val = "<div class='embeelv_excerpt'><p>";
                if (isset($eelv_share_options['a']) && $eelv_share_options['a'] == 1) {
                    $val.='<a href="' . $blog_post->guid . '" target="_blank"class="embeelv_direct_blank">' . $content . '</a>';
                } else {
                    $val.=$content . '<a href="' . $blog_post->guid . '" target="_blank" class="embeelv_blank"><span class="dashicons dashicons-admin-links"></span></a>';
                }
                $val.="</p></div>";
                $excerpt = str_replace($match, $val, $excerpt);
            }
        }

        // YOUTUBE
        if (isset($eelv_share_options['y']) && $eelv_share_options['y'] == 1) {
            $tumb.=eelv_share_parse_youtube($excerpt);
        }

        // DAILYMOTION
        if (isset($eelv_share_options['d']) && $eelv_share_options['d'] == 1) {
            $tumb.=eelv_share_parse_dailymotion($excerpt);
        }

        // TWITTER
        if (isset($eelv_share_options['t']) && $eelv_share_options['t'] == 1) {
            $excerpt = eelv_share_parse_twitter($excerpt);
        }


        if ($sharer == false) {
            /* $excerpt.="<a href=\"var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='".$blogurl."/wp-admin/press-this.php',l=d.location,e=encodeURIComponent,u=f+'?u=&t=".$post->post_title."&s=".$post->guid."&v=4';a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'));};if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();void(0)\">#</a>"; */
        }
        return $tumb . $excerpt;
    }

    function embed_locals($matches, $attr, $url, $rawattr) {

        // init values
        $subdomain = substr($matches[1], 0, -1);
        $domain = $matches[2];
        $postid = $matches[3];
        parse_str($matches[4], $vars);
        $eelv_share_options = get_option('eelv_share_options');
        $eelv_share_domains = eelv_share_get_domains();
        // init vars
        $max_words = 55;
        if (isset($vars['s']) && is_numeric($vars['s'])) {
            $max_words = $vars['s'];
        }


        if (isset($eelv_share_domains[$domain]) && $domain != $eelv_share_domains[$domain]) {
            $subdomain = $eelv_share_domains[$domain];
        }


        if (empty($subdomain)) {
            //$subdomain=$domain;
            $subdomain = 'www';
            $blog = get_blog_details($domain);
            $blog_post = get_blog_post(1, $postid);
        } else {
            $blog = get_blog_details($subdomain);
            $blog_post = get_blog_post($blog->blog_id, $postid);
        }

        global $it;
        $it = abs($it);
        $js_var = 'str_' . $postid . '_' . $it . '_' . str_replace(array('-', '.'), '', $subdomain);
        $it++;

        $embed = '<div class="embeelv">';
        $embed.='<a href="' . $blog_post->guid . '" target="_blank" id="' . $js_var . '">';
        $embed.=$blog_post->post_name;
        $embed.='</a></div>';
        $embed.='<script>var ' . $js_var . '="';
        if (is_object($blog_post)) {
            if (false !== $image = get_blog_post_thumbnail($blog->blog_id, $blog_post->ID)) {
                $embed.='[img src=\"' . $image->guid . '\" alt=\"' . $image->post_name . '\"/]';
            }
            $embed.='[h4]';
            $embed.=trim(str_replace('"', '\"', $blog_post->post_title));
            $embed.='[/h4][p]';

            $w = 0;
            $txt = trim(strip_tags($blog_post->post_content));
            $txts = preg_split('/\s/', $txt);

            $emtxt = '';
            foreach ($txts as $str) {
                $str = str_replace('"', '\"', trim($str));
                $w++;
                $emtxt.=$str . ' ';
                if ($max_words > 0 && $w > $max_words)
                    break;
            }
            // YOUTUBE
            if (isset($eelv_share_options['y']) && $eelv_share_options['y'] == 1) {
                $emtxt = eelv_share_untag(eelv_share_parse_youtube($emtxt, true));
            }

            // DAILYMOTION
            if (isset($eelv_share_options['d']) && $eelv_share_options['d'] == 1) {
                $emtxt = eelv_share_untag(eelv_share_parse_dailymotion($emtxt, true));
            }

            // TWITTER
            if (isset($eelv_share_options['t']) && $eelv_share_options['t'] == 1) {
                $emtxt = eelv_share_untag(eelv_share_parse_twitter($emtxt));
            }
            if (sizeof($txts) > $w)
                $emtxt.='...';
            $embed.=$emtxt;

            $embed.='[/p]';
            if (sizeof($txts) > $w)
                $embed.='[p][u]' . __('&raquo; Read full post', 'eelv-share-post') . '[/u]';
            $embed.='[div style=\"clear:both\"][/div][/p]';
        }
        else {
            $embed.='[h4 class=\"nondispo\"]' . __('This post isn\'t avaible any more', 'eelv-share-post') . '[/h4]';
        }
        $embed.='";';
        $embed.='while(' . $js_var . '.indexOf("[") != -1){' . $js_var . ' = ' . $js_var . '.replace("[","<");}';
        $embed.='while (' . $js_var . '.indexOf("]") != -1){' . $js_var . ' = ' . $js_var . '.replace("]",">");}';
        $embed.='document.getElementById("' . $js_var . '").innerHTML=' . $js_var . '; setTimeout(function(){document.getElementById("wp-admin-bar-embed_post_menu").style.display="none";},2000);</script>';
        return $embed;
    }

    /*     * *********************** SHARING ACTIONS ** */

    /**
     * Add the custom box
     */
    function add_custom_boxes() {
        add_meta_box(
                'eelv_share_from_admin', __("Share on", 'eelv-share-post'), array($this, 'custom_box'), 'post', 'side'
        );
    }

    /**
     * Custom box
     */
    function custom_box() {
        $user_id = get_current_user_id();
        $cb = get_current_blog_id();
        $user_blogs = get_blogs_of_user($user_id);
        $shared_on = get_post_meta(get_the_ID(), 'share_on_blog', true);
        wp_nonce_field('eelv_share_post_via_admin', 'eelv_share_post_via_admin');
        //print_r($shared_on);
        foreach ($user_blogs as $user_blog) {
            if ($user_blog->userblog_id != $cb) {
                ?>
                <p>
                    <label for="share_on_blog_<?= $user_blog->userblog_id ?>">
                        <input type="checkbox" name="share_on_blog[<?= $user_blog->userblog_id ?>]" id="share_on_blog_<?= $user_blog->userblog_id ?>" <?php
                if (isset($shared_on[$user_blog->userblog_id])) {
                    echo' checked="checked" value="' . $shared_on[$user_blog->userblog_id] . '"';
                } else {
                    echo' value=""';
                }
                ?>/> <?= $user_blog->blogname ?>
                    </label>

                    <select name="share_on_blog_cat_<?= $user_blog->userblog_id ?>[]" multiple>
                        <?php
                        switch_to_blog($user_blog->userblog_id);
                        $cats = get_categories();
                        $dogs = array();
                        if (isset($shared_on[$user_blog->userblog_id])) {
                            $dogs = wp_get_post_categories($shared_on[$user_blog->userblog_id], array('hide_empty' => false));
                        }
                        foreach ($cats as $cat) {
                            ?>
                            <option value="<?= $cat->term_id ?>" <?php
                                    if (in_array($cat->term_id, $dogs)) {
                                        echo'selected';
                                    }
                                    ?>><?= $cat->cat_name ?></option>
                <?php } switch_to_blog($cb); ?>
                    </select>
                </p>
                <?php
            }
        }
        ?>
        <script>
            jQuery(document).ready(function (e) {
                jQuery('#eelv_share_from_admin select').hide();
                jQuery('#eelv_share_from_admin input[type=checkbox]:checked').parent().parent().children('select').show();
                jQuery('#eelv_share_from_admin input').change(function () {
                    if (jQuery(this).is(':checked') == true) {
                        jQuery(this).parent().parent().children('select').show().animate({height: 100});
                    }
                    else {
                        jQuery(this).parent().parent().children('select').animate({height: 0});
                    }
                });
            });
        </script>
        <?php
    }

    /**
     * Init features in the press-this form
     * @return type
     */
    function admin_head() {
        if ($_SERVER['PHP_SELF'] != '/wp-admin/press-this.php') {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $post_id = $_REQUEST['post_id'];
        if (wp_is_post_revision($post_id)) {
            return;
        }
        if (!empty($_REQUEST['content'])) {
            return;
        }
        $eelv_share_domains = eelv_share_get_domains();
        $domains_to_parse = array_keys($eelv_share_domains);
        preg_match_all('#http://([a-zA-Z0-9\-\.]+)?(' . str_replace('.', '\.', implode('|', $domains_to_parse)) . ')/\?p=(\d+)([a-zA-Z0-9=;\&]+)?[\n\t\s ]#i', $_REQUEST['content'] . ' ', $out, PREG_PATTERN_ORDER);
        if (is_array($out)) {
            $cb = get_current_blog_id();
            foreach ($out[0] as $id => $match) {
                $blogname = substr($out[1][$id], 0, -1);
                $domain = $out[2][$id];
                $postid = $out[3][$id];

                if (isset($eelv_share_domains[$domain]) && $domain != $eelv_share_domains[$domain]) {
                    $blogname = $eelv_share_domains[$domain];
                }
                if (empty($blogname)) {
                    $blogname = 'www';
                    $blog = get_blog_details($domain);
                    $blog_post = get_blog_post(1, $postid);
                } else {
                    $blog = get_blog_details($blogname);
                    $blog_post = get_blog_post($blog->blog_id, $postid);
                }
                switch_to_blog($blog->blog_id);
                $share_tmp = $shared_on = get_post_meta($postid, 'share_on_blog', true);
                $share_tmp[$cb] = $post_id;
                update_post_meta($postid, 'share_on_blog', $share_tmp, $shared_on);
                switch_to_blog($cb);
            }
        }
    }

    function pressthis_data($data){
        if(isset($data['_meta']['sharepost-images:description'])){
            $data['_images'] = array_unique(array_merge($data['_images'], explode(',', $data['_meta']['sharepost-images:description'])));
            unset($data['_meta']['sharepost-images:description']);
        }
        return $data;
    }

    /**
     * Triggered when a post is saved
     * @param type $post_id
     * @return type
     */
    function save_post($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (wp_is_post_revision($post_id))
            return;
        if (
                !wp_verify_nonce($_POST['eelv_share_post_via_admin'], 'eelv_share_post_via_admin') &&
                !wp_verify_nonce($_POST['_wpnonce'], 'press-this')
        )
            return;

        if (isset($_POST['eelv_share_featured_image']) && isset($_POST['eelv_share_featured_image_blogid'])) {
            update_post_meta($post_id, '_thumbnail_from_shared_post', $_POST['eelv_share_featured_image']);
            update_post_meta($post_id, '_thumbnail_from_shared_blog', $_POST['eelv_share_featured_image_blogid']);
            update_post_meta($post_id, '_thumbnail_id', 1);
            return;
        } elseif (isset($_POST['eelv_share_featured_image_keep']) && !isset($_POST['eelv_share_featured_image'])) {
            delete_post_meta($post_id, '_thumbnail_from_shared_post');
            delete_post_meta($post_id, '_thumbnail_from_shared_blog');
        }

        $shared_on = get_post_meta($post_id, 'share_on_blog', true);
        if (isset($_POST['share_on_blog']) || !empty($shared_on)) {
            remove_action('save_post', array($this, 'save_post'));

            $share_on = $_REQUEST['share_on_blog'];
            $share_tmp = array();
            if (!is_array($shared_on))
                $shared_on = array($shared_on);
            if (!is_array($share_on))
                $share_on = array($share_on);

            $user_id = get_current_user_id();
            $cb = get_current_blog_id();
            $user_blogs = get_blogs_of_user($user_id);

            $link = get_permalink($post_id);

            foreach ($user_blogs as $user_blog) {
                if ($user_blog->userblog_id != $cb) {
                    $share_on_blog_cat = $_REQUEST['share_on_blog_cat_' . $user_blog->userblog_id];
                    switch_to_blog($user_blog->userblog_id);
                    // blog added to list
                    if (!array_key_exists($user_blog->userblog_id, $shared_on) && array_key_exists($user_blog->userblog_id, $share_on)) {

                        if (0 !== $publication = wp_insert_post(array('post_type' => get_post_type(), 'post_title' => get_the_title(), 'post_content' => $link, 'post_status' => get_post_status()))) {
                            $share_tmp[$user_blog->userblog_id] = $publication;
                            wp_set_post_categories($publication, $share_on_blog_cat);
                        }
                    }
                    // blog removed from list
                    elseif (array_key_exists($user_blog->userblog_id, $shared_on) && !array_key_exists($user_blog->userblog_id, $share_on)) {
                        wp_delete_post($shared_on[$user_blog->userblog_id], true);
                    }
                    // blog stay in list
                    elseif (array_key_exists($user_blog->userblog_id, $shared_on) && array_key_exists($user_blog->userblog_id, $share_on)) {
                        $share_tmp[$user_blog->userblog_id] = $shared_on[$user_blog->userblog_id];
                        wp_set_post_categories($shared_on[$user_blog->userblog_id], $share_on_blog_cat);
                    }
                    // blog really unwanted
                    else {
                        //nothing to do
                    }
                    switch_to_blog($cb);
                }
            }
            add_action('save_post', array($this, 'save_post'));
            update_post_meta($post_id, 'share_on_blog', $share_tmp, $shared_on);
        }
    }

    /**
     * Triggered by a form submission to add a post to the featured posts list
     * @uses add_suggest()
     */
    function suggest(){
        if(false === $post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT)){
            wp_safe_redirect( wp_get_referer() );
        }
        if(filter_input(INPUT_GET, 'remove')){
            $this->unsuggest(get_current_blog_id(), $post_id);
        }
        else{
            $this->add_suggest(get_current_blog_id(), $post_id, filter_input(INPUT_GET, 'post_url', FILTER_SANITIZE_STRING), filter_input(INPUT_GET, 'post_title', FILTER_SANITIZE_STRING));
        }
        wp_safe_redirect( wp_get_referer() );
        exit;
    }
    /**
     *
     * @param string $domain
     * @param int $target_blog_id
     * @param int $blog_id
     * @param int $post_id
     * @param string $url
     * @param string $title
     * @return string
     */
    function pressthis_link($domain, $target_blog_id, $blog_id, $post_id, $url='', $title=''){
        return $domain . '/wp-admin/press-this.php?p=' . $post_id . '&t=' . urlencode(esc_attr($title)) . '&u=' . (!empty($url) ? $url : $this->make_link($blog_id, $post_id)) . '&v=8&b=' . $target_blog_id;
    }
    function header(){
        if (is_single() || is_page()) {
            global $post;
            $eelv_share_options = get_option('eelv_share_options');
            if (!isset($eelv_share_options['l']) || $eelv_share_options['l'] == 0 || empty($eelv_share_options['l'])) {
                $eelv_share_options['l'] = 400;
            }
            echo'<meta property="sharepost:description" content="'.substr(strip_tags(strip_shortcodes($post->post_content)), 0, $eelv_share_options['l']) . '..." />';
            $images=array();
            $medias = apply_filters( 'sharepost_get_images', array(), get_the_ID());
            if($medias && count($medias)){
                echo"\n";
                foreach($medias as $media){
                    $images[]=$media->guid;
                }
                echo '<meta property="sharepost-images:description" content="'.implode(',', $images).'">';
            }
        }
    }
    /**
     * Add items in the toolbar
     * @param type $wp_admin_bar
     */
    function adminbar($wp_admin_bar) {
        if (is_single() || is_page()) {
            // add a parent item
            $args_top = array('id' => 'share_post_menu', 'title' => '<span class="ab-icon dashicons dashicons-share"></span> <span class="ab-label">' . __('Share', 'eelv-share-post') . '</span>');
            $wp_admin_bar->add_node($args_top);

            $args_suggest = array(
                    'id' => 'share_post_suggest',
                    'title' => '<span class="ab-icon dashicons dashicons-star-empty"></span> '.__('Suggest as feature post', 'eelv-share-post'),
                    'parent' => 'share_post_menu',
                    'target' => '_blank',
                    'href' => admin_url('admin-post.php').'?'.  http_build_query(array(
                        'action'=>'eelv-share-post-suggest',
                        'post_id'=>get_the_ID(),
                        'post_title'=>esc_attr(get_the_title()),
                        'post_url'=>get_the_permalink(),
                        '_wp_http_referer'=>get_the_permalink()
                    ))
                );
            if($this->is_suggested(get_current_blog_id(), get_the_ID())){
                $args_suggest['title']='<span class="ab-icon dashicons dashicons-star-filled"></span> '.__('Suggested', 'eelv-share-post');
                $args_suggest['href']=admin_url('admin-post.php').'?'.  http_build_query(array(
                        'action'=>'eelv-share-post-suggest',
                        'post_id'=>get_the_ID(),
                        'remove'=>'true',
                        '_wp_http_referer'=>get_the_permalink()
                    ));
            }
            $wp_admin_bar->add_node($args_suggest);

            $args_group = array(
		'id'     => 'share_post_group',
		'parent' => 'share_post_menu',
		'meta'   => array( 'class' => 'first-toolbar-group' )
            );
            $wp_admin_bar->add_group( $args_group );

            $n = 1;
            $user_id = get_current_user_id();
            $cb = get_current_blog_id();
            $user_blogs = get_blogs_of_user($user_id);
            // add a child item to a our parent item
            foreach ($user_blogs as $user_blog) {
                $args = array(
                    'id' => 'share_post_item-'.$n,
                    'title' => $user_blog->blogname,
                    'parent' => 'share_post_group',
                    'target' => '_blank',
                    'href' => $this->pressthis_link('http://'.$user_blog->domain, $cb, $user_blog->userblog_id, get_the_ID(), get_the_permalink(), get_the_title())
                );
                $wp_admin_bar->add_node($args);
                $n++;
            }
        }
    }
    function dashboard_widget(){
        $posts = $this->suggested();
        $cb = get_current_blog_id();
        ?>
        <table>
            <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td>
                    <a href="<?php echo $this->make_link($post->blog_id, $post->post_id); ?>" target="_blank">
                        <strong><?php echo $post->share_description; ?></strong>
                    </a>
                <?php $user = get_userdata($post->user_id); ?>
                <?php printf(__('Suggested by: %s', 'eelv-share-post'), $user->display_name); ?>
                </td>
                <td>
                <a href="<?php echo $this->pressthis_link(site_url(), $post->blog_id, $cb, $post->post_id, $post->post_url, $post->share_description); ?>" title="<?php _e('Share', 'eelv-share-post'); ?>" class="sharepost-pressthis">
                    <span class="dashicons dashicons-share"></span>
                    <span class="screen-reader-text"><?php _e('Share', 'eelv-share-post'); ?></span>
                </a>
                </td>
            </li>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Generates a link that can be shared
     * @param int $blog_dest
     * @param int $post_id
     * @param booleean $encode
     * @return string
     */
    function make_link($blog_dest, $post_id, $encode = true) {
        $blog_details = get_blog_details($blog_dest);
        $domain = $blog_details->siteurl;
        $tmp_blog_id = get_current_blog_id();

        $link = $domain . '/?p=' . $post_id;

        $sep = '&';
        if ($encode) {
            $sep = '%26amp;';
        }
        switch_to_blog($blog_details->blog_id);
        $eelv_share_options = get_option('eelv_share_options');
        switch_to_blog($tmp_blog_id);
        if (isset($eelv_share_options['s']) && is_numeric($eelv_share_options['s'])) {
            $link.=$sep . 's=' . $eelv_share_options['s'];
        }
        return $link;
    }

    /**
     * Add part in the press-this form
     * @return void
     */
    function admin_footer() {
        if ($_SERVER['PHP_SELF'] != '/wp-admin/press-this.php') {
            return;
        }
        if (!isset($_REQUEST['p']) || !isset($_REQUEST['v']) || !isset($_REQUEST['t']) || !isset($_REQUEST['b']) || !isset($_REQUEST['s'])) {
            return;
        }
        if (!is_numeric($_REQUEST['p'])) {
            return;
        }
        if (!is_numeric($_REQUEST['b'])) {
            return;
        }
        if (has_filter("post_thumbnail_html", 'eelv_distant_thumbnail')) {
            remove_filter("post_thumbnail_html", 'eelv_distant_thumbnail', 30);
        }
        $postid = $_REQUEST['p'];
        $blogid = $_REQUEST['b'];
        switch_to_blog($blogid);

        if (has_post_thumbnail($postid)) {
            ?>
            <div id="eelv_share_featured_image" class="postbox">
                <div class="handlediv" title="<>"><br></div>
                <h3 class="hndle"><?php _e('Featured image', 'eelv-share-post') ?> <?php echo $file; ?></h3>
                <div class="inside">
                    <label>
                        <p>
                            <input type="checkbox" name="eelv_share_featured_image" id="eelv_share_featured_image" value="<?= $postid ?>" checked="checked">
            <?php _e('Synchronise featured image from distant post', 'eelv-share-post') ?>
                        </p>
            <?php echo get_the_post_thumbnail($postid, 'thumb'); ?>
                    </label>
                    <input type="hidden" name="eelv_share_featured_image_blogid" value="<?= $blogid ?>">

                </div>
            </div>
            <?php
        }
        restore_current_blog();
    }

}
