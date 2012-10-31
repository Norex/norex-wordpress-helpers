<?

/**
 * File: norex-wordpress-helpers-core.php
 * Description: Useful functions to use when developing Wordpress sites.
 */

//date/time formats
define('TS_DATETIME_ABBR', 'M. j, Y g:i:s A');
define('TS_DATETIME_DAY_ABBR', 'D., M. j, Y g:i:s A');
define('TS_DATETIME_FULL', 'F jS, Y g:i:s A');
define('TS_DATETIME_DAY_FULL', 'l, F jS, Y g:i:s A');
define('TS_DATE_ABBR', 'M. j, Y');
define('TS_DATE_DAY_ABBR', 'D., M. j, Y');
define('TS_DATE_FULL', 'F jS, Y');
define('TS_DATE_DAY_FULL', 'l, F jS, Y');
define('TS_DATE_FULL_NO_SUFF', 'F j, Y');
define('TS_DATE_DAY_FULL_NO_SUFF', 'l, F j, Y');
define('TS_TIME_12_SEC', 'g:i:s A');
define('TS_TIME_12', 'g:i A');
define('TS_TIME_24_SEC', 'g:i:s');
define('TS_TIME_24', 'G:i');

//print_r any number of paramters and wrap them in <pre> tags
function pre() {
    $args = func_get_args();
    if (is_array($args) && !empty($args)) {
        foreach ($args as $a) {
            echo '<pre>';
            print_r($a);
            echo '</pre>';
        }
    }
}

//hold on to the original post before you overwrite it
function preserve_post() {
    global $post, $opost;
    $opost = $post;
}

//restore original post after you've overwritten it for something
function restore_post() {
    global $post, $opost;
    $post = $opost;
    setup_postdata($post);
}

//get_the_content() doesn't use filters, this will apply filters first
function formatted_content() {
    global $post;
    $r = false;
    if ($post) {
        if (isset($post->post_content)) {
            $r = apply_filters('the_content', get_the_content());
        }
    }
    return $r;
}

function formatted_post_content($post) {
    $r = false;
    if ($post) {
        if (isset($post->post_content)) {
            $r = apply_filters('the_content', $post->post_content);
        }
    }
    return $r;
}

//get_the_excerpt() doesn't use filters, this will apply filters first
function formatted_excerpt() {
    global $post;
    $r = false;
    if ($post) {
        if (isset($post->post_content)) {
            $r = apply_filters('the_content', get_the_excerpt());
        }
    }
    return $r;
}

function get_the_content_by_id($post_id) {
    global $post;  
    $save_post = $post;
    $post = get_post($post_id);
    setup_postdata($post);

    $output = formatted_content();

    $post = $save_post;
    setup_postdata($post);
    return $output;
}

function get_the_excerpt_by_id($post_id, $length=false) {
    global $post;  
    $save_post = $post;
    $post = get_post($post_id);
    setup_postdata($post);

    $excerpt = get_the_excerpt();


    if ($length) {
        $length++;

        $final_excerpt = '';
        if ( mb_strlen( $excerpt ) > $length ) {
            $subex = mb_substr( $excerpt, 0, $length - 5 );
            $exwords = explode( ' ', $subex );
            $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
            if ( $excut < 0 )
                $final_excerpt .= mb_substr( $subex, 0, $excut );
            else
                $final_excerpt .= $subex;

            if(!is_front_page())
                $final_excerpt .= '[...]';
        } 
        else {
            $final_excerpt .= $excerpt;
        }

        $excerpt = $final_excerpt;
    }

    $post = $save_post;
    setup_postdata($post);
    return $final_excerpt;
}

function get_the_date_by_id($post_id, $format = false) {
    global $post;  
    $save_post = $post;
    $post = get_post($post_id);
    setup_postdata($post);
    if (!$format)
        $format = get_option('date_format');
    $output = get_the_date($format);
    $post = $save_post;
    setup_postdata($post);
    return $output;
}

//I got sick of writting if(is_array($arr) && !empty($arr)){
function array_clean($arr) {
    return (is_array($arr) && !empty($arr));
}

/**
 * pass this a multi-dimentional array followed by any number of keys (numeric or associative) and this will drill down to the value you are looking for
 * Example:
 *    $array = Array
 *                (
 *                  [1] => Array
 *                            (
 *                              [7] => Array
 *                                        (
 *                                          ["Monkey"] => Array
 *                                                          (
 *                                                            ["Colour"] => "Purple"
 *                                                          )
 *                                        )
 *                            )
 *                )
 * 
 * drillset($array,1,7,"Monkey","Colour");
 * the above will return "Purple"
 * 
 * drillset($array,1,7,"Duckey","Colour");
 * the above will return (boolean) false
 * 
 * drillset($array,1,7,"Monkey","Colour",0);
 * the above will return "P"
 * 
 * drillset($array,1,7,"Monkey","Colour",0,0);
 * drillset($array,1,7,"Monkey","Colour",0,0,0);
 * drillset($array,1,7,"Monkey","Colour",0,0,0,0);
 * all of the above will return "P" because the Nth element of a string is the Nth character
 */
function drillset() {
    $arr = func_get_args();
    $valid = false;
    $curObj = false;
    if (array_clean($arr)) {
        if (array_clean($arr[0])) {
            $valid = true;
            $curObj = $arr[0];
            for ($i = 1; $i < count($arr); $i++) {
                if (isset($curObj[$arr[$i]]) && $valid)
                    $curObj = $curObj[$arr[$i]];
                else
                    $valid = false;
            }
        }
    }

    if ($valid)
        return $curObj;
    else
        return false;
    
}

function get_image($int, $size) {
    $r = false;
    if (positiveInt($int)) {
        $img = wp_get_attachment_image_src($int, $size, false);
        if (array_clean($img))
            $r = $img[0];
    }
    return $r;
}

function array_group($arr) {
    $r = false;
    if (array_clean($arr)) {
        foreach ($arr as $index => $array) {
            if (array_clean($array)) {
                foreach ($array as $k => $v) {
                    $r[$k][$index] = $v;
                }
            }
        }
    }

    return $r;
}

function sanitize(&$s, $extreme=false) {
    $s = str_replace(' ', '', $s);
    $s = strtolower($s);
    if ($extreme)
        $s = ereg_replace("[^A-Za-z0-9_]", "", $s);
}

function positive_int($i) {
    return (is_numeric($i) && $i > 0);
}

function update_user_email($email, $userID=false) {
    global $current_user;
    get_currentuserinfo();
    $r = false;

    if (!is_numeric($userID)) {
        if (is_numeric($current_user->ID))
            $userID = $current_user->ID;
        else
            return $r;
    }

    if (is_email($email) && !email_exists($email)) {
        global $wpdb;
        $wpdb->update($wpdb->users, array('user_email' => $email), array('ID' => $userID));
        $current_user->data->user_email = $email;
        $current_user->user_email = $email;
        $r = $email;
    }

    return $r;
}

function cur2float($cur) {
    $cur = str_replace('$', '', $cur);
    $cur = str_replace(',', '', $cur);
    return $cur;
}

function num2cur(&$num) {
    $num = str_replace(',', '', $num);
    $num = '$' . number_format($num, 2);
}

function ts_to_human(&$ts, $format=TS_DATETIME_ABBR) {
    $ts = date($format, $ts);
    return $ts;
}

function nxpress_select($args) {
    $r = false;
    $kv = $args['kv'] ? $args['kv'] : false;
    $class = $args['class'] ? ' class="' . $args['class'] . '" ' : '';
    $id = $args['id'] ? ' id="' . $args['id'] . '" ' : '';
    $name = $args['name'] ? ' name="' . $args['name'] . '" ' : '';
    $style = $args['style'] ? ' style="' . $args['style'] . '" ' : '';
    $option_value = $args['option_value'] ? 'value' : 'key';
    $selected = $args['selected'] ? $args['selected'] : '';
    $multiple = $args['multiple'] ? ' MULTIPLE' : '';
    $excludes = is_array($args['exclude']) ? $args['exclude'] : '';

    if (is_array($kv)) {
        $r = '<select' . $name . $id . $class . $style . $multiple . '>';
        foreach ($kv as $k => $v) {
            $sel = "";
            if ($option_value == 'value')
                $k = $v;

            if ($multiple) {
                if (array_clean($selected)) {
                    foreach ($selected as $select) {
                        if ($select == $k)
                            $sel = $select != '' ? ' SELECTED="SELECTED" ' : '';
                    }
                }
            } 
            else {
                $sel = $k == $selected && $selected != '' ? ' SELECTED="SELECTED" ' : '';
            }
            $skip = false;
            if (is_array($excludes)) {
                foreach ($excludes as $exclude) {
                    if ($k === $exclude || $v === $exclude)
                        $skip = true;
                }
            }
            if (!$skip)
                $r .= '<option ' . $sel . ' value="' . $k . '">' . $v . '</option>';
        }

        $r .= '</select>';
    }

    return $r;
}

function get_url_qs($newParams=false, $remove=false) {
    $string = '';
    $params = array();
    if (array_clean($_GET))
        $params = $_GET;

    if (array_clean($newParams)) {
        foreach ($newParams as $k => $v) {
            $params[$k] = $v;
        }
    }

    if ($remove) {
        if (array_clean($remove)) {
            foreach ($remove as $rem) {
                if (isset($params[$rem]))
                    unset($params[$rem]);
            }
        } 
        elseif (is_string($remove)) {
            if (isset($params[$remove]))
                unset($params[$remove]);
        }
    }

    if (array_clean($params)) {
        foreach ($params as $k => $v) {
            $v = stripslashes($v);
            $string .= $string ? '&' : '?';
            $string .= $k . '=' . $v;
        }
    }

    return $string;
}

/**
 * Gets the current URL of the page
 */
function get_url() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on")
        $pageURL .= "s";

    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80")
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    else
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    return $pageURL;
}

function preg_alphanumeric(&$string) {
    $string = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $string);
    return $string;
}

function boolInt($bool) {
    if ($bool)
        return 1;
    return 0;
}

function nx_site_search_with_exclude($get_key, $exclude=false, $post_types = array('post' => 'Blog Posts', 'page' => 'Site Content'), $query_type='get', $input_type='string', $suppress_filters=true, $lang='en'){
    return nx_site_search($get_key, $post_types, $query_type, $input_type, $suppress_filters, $lang, $exclude);
}

function nx_site_search($get_key, $post_types = array('post' => 'Blog Posts', 'page' => 'Site Content'), $query_type='get', $input_type='string', $suppress_filters=true, $lang='en', $exclude=false) {
    $total_results = 0;
    $results = array();
    $q = false;
    switch ($query_type) {
        case 'post':
        $q = $_POST[$get_key];
        break;
        default:
        $q = $_GET[$get_key];
        break;
    }
    
    $words = false;
    switch ($input_type) {
        case 'array':
        $words = $q;
        if (array_clean($words)) {
            foreach ($words as $k => $v) {
                preg_alphanumeric($v);
                $words[$k] = $v;
            }
        }
        break;
        default:
        preg_alphanumeric($q);
        $words = explode(' ', $q);
        break;
    }
    
    /*if(array_clean($exclude)){
        foreach($exclude as $ex){
            if(array_clean($words)){
                foreach($words as $k => $v){
                    if(strtolower($v)==strtolower($ex))
                        unset($words[$k]);
                }
            }
        }
    }*/

    $sql = '';
    $sql_types = '';
    $sql_words = '';

    $acf = false;
    if (function_exists('get_field')) {
        $acf = true;
    }

    if (array_clean($post_types)) {
        foreach ($post_types as $type => $label) {
            $sql_types .= $sql_types ? ' OR ' : '';
            $sql_types .= ' `post_type` = "' . $type . '" ';
            $results[$type] = array();
        }
    }

    if (array_clean($words)) {
        foreach ($words as $word) {
            if ($word) {
                $sql_words .= $sql_words ? ' OR ' : '';
                $sql_words .= ' p.post_content LIKE "%' . $word . '%" OR p.post_title LIKE "%' . $word . '%" OR te.name LIKE "%' . $word . '%" ';
                if ($acf) {
                    $sql_words .= 'OR m.meta_value LIKE "%' . $word . '%"';
                }
            }
        }
    }

    if ($sql_types)
        $sql_types = '(' . $sql_types . ')';
    if ($sql_words)
        $sql_words = '(' . $sql_words . ')';

    global $wpdb;

    $sql = 'SELECT DISTINCT p.ID, p.post_type FROM `' . $wpdb->prefix . 'posts` p';
    if ($acf) {
        //$acf_values = $wpdb->prefix.'acf_values';
        //$acf_fields = $wpdb->prefix.'acf_fields';
        $wp_postmeta = $wpdb->prefix . 'postmeta';
        $sql .= " LEFT JOIN $wp_postmeta m ON m.post_id = p.ID";
        $sql .= " LEFT JOIN $wp_postmeta m2 ON m2.post_id = p.ID";
        //LEFT JOIN $acf_values v ON m.meta_id = v.value
        //LEFT JOIN $acf_fields f ON v.field_id = f.id
        //";
    }

    // Add support for custom taxonomies and tags.
    $wp_terms = $wpdb->prefix . 'terms';
    $wp_term_relationships = $wpdb->prefix . 'term_relationships';
    $wp_term_taxonomy = $wpdb->prefix . 'term_taxonomy';

    $sql .= "
    LEFT JOIN $wp_term_relationships tr ON  tr.object_id = p.ID
    LEFT JOIN $wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
    LEFT JOIN $wp_terms te ON te.term_id = tt.term_id
    ";
    
    $sql_additional_where = apply_filters('nx_site_search_additional_where', null);
    
    
    
    $sql .= ' WHERE ';
    if ($sql_types)
        $sql .= $sql_types . ' AND ';
    if ($sql_words)
        $sql .= $sql_words . ' AND ';
    if($sql_additional_where)
        $sql .= $sql_additional_where . ' AND ';

    $sql .= 'p.post_status = "publish" ';

    if ($acf) {
        $sql .= "AND (p.post_type != 'post' OR (m.meta_key NOT LIKE '\_%')) ";
    }

    $sql .= " AND (p.post_type != 'post' OR (m2.meta_key = '_display_in_blog_reel' AND m2.meta_value = 'on')) ";

    $sql .= ' ORDER BY p.post_type, p.post_modified DESC;';
    
    $rs = $wpdb->get_results($sql);

    $collected_posts = array();

    if (array_clean($rs)) {
        global $post;
        $c = 0;
        preserve_post();
        foreach ($rs as $k => $v) {
            if (!in_array($v->ID, $collected_posts)) {
                $post = get_post($v->ID);
                setup_postdata($post);
                $skip_for_lang = false;
                if (!$suppress_filters) {
                    if (get_translation_original($lang, false, false) != $post->ID) {
                        $skip_for_lang = true;
                    }
                }
                if (!$skip_for_lang) {
                    $results[$v->post_type]['posts'][$c]['post'] = $post;
                    $results[$v->post_type]['posts'][$c]['excerpt'] = formatted_excerpt();
                    $results[$v->post_type]['posts'][$c]['link'] = get_permalink($post->ID);
                    $results[$v->post_type]['posts'][$c]['title'] = $post->post_title;
                    if($results[$v->post_type]['posts'][$c]['link'] == get_option('siteurl') . '/search'){

                    }else{
                        $c++;
                        $collected_posts[] = $v->ID;
                    }
                }
            }
        }
        restore_post();
        if (array_clean($results)) {
            foreach ($results as $type => $posts) {
                $results[$type]['count'] = count($results[$type]['posts']);
                $total_results += $results[$type]['count'];
                $results[$type]['label'] = $post_types[$type];
            }
        }
    }

    // Still returns results if no content to show empty sections.
    if (!$total_results) {
        foreach ($results as $type => $posts) {
            $results[$type]['label'] = $post_types[$type];
        }
    }

    status_header(200);
    return $results;
}

function nx_user_search($get_key = 'q', $user_types = array('agent' => 'Agent')) {
    $q = false;
    switch ($query_type) {
        case 'post':
        $q = $_POST[$get_key];
        break;
        default:
        $q = $_GET[$get_key];
        break;
    }

    $results = array();

    if ($q) {
        $words = false;
        preg_alphanumeric($q);
        $words = explode(' ', $q);

        if (array_clean($words)) {
            foreach ($words as $word) {
                if ($word) {
                    $sql_words .= $sql_words ? ' OR ' : '';
                    $sql_words .= "da.VALUE LIKE '%$word%' OR um1.meta_value LIKE '%$word%' OR um2.meta_value LIKE '%$word%' ";
                }
            }
        }

        if (array_clean($user_types)) {
            foreach ($user_types as $type => $label) {
                $sql_types .= $sql_types ? ' OR ' : '';
                $sql_types .= "um3.meta_value LIKE '%\"$type\"%' ";
            }
        }

        $sql = "SELECT DISTINCT da.user_id as user_id, um1.meta_value as first_name, um2.meta_value as last_name ";
        $sql .= "FROM wp_cimy_uef_data da ";
        $sql .= "LEFT JOIN wp_cimy_uef_fields fi ON fi.id = da.field_id ";
        $sql .= "LEFT JOIN wp_usermeta um1 ON da.user_id = um1.user_id ";
        $sql .= "LEFT JOIN wp_usermeta um2 ON da.user_id = um2.user_id ";
        $sql .= "LEFT JOIN wp_usermeta um3 ON da.user_id = um3.user_id ";
        $sql .= "WHERE (fi.name = 'agent-bio' OR fi.name = 'more-information' OR fi.name = 'testimonials') AND (um1.meta_key = 'first_name' AND um2.meta_key = 'last_name' AND um3.meta_key = 'wp_capabilities') ";

        if ($sql_words)
            $sql .= "AND ($sql_words) ";
        if ($sql_types)
            $sql .= "AND ($sql_types) ";

        global $wpdb;
        $results = $wpdb->get_results($sql);

        $user_results = array();
        if (array_clean($results)) {
            foreach ($results as $result) {
                $user_results[] = array(
                    'name' => $result->first_name . ' ' . $result->last_name,
                    'url' => get_author_posts_url($result->user_id)
                    );
            }
        }
    }

    return array_clean($user_results) ? $user_results : false;
}

function smart_truncate($string, $chars=50, $sep=' ', $cont='...') {
    $parts = explode($sep, $string);
    if (is_array($parts)) {
        $string = '';
        $exceeded = false;
        foreach ($parts as $part) {
            if (strlen($string) + strlen($sep) + strlen($part) <= $chars) {
                if (!$exceeded)
                    $string .= $string ? $sep . $part : $part;
            }else {
                if (!$exceeded)
                    $string .= $cont;
                $exceeded = true;
            }
        }
        if ($string == '' || $string == $cont) {
            $string = substr($parts[0], 0, $chars - strlen($cont)) . $cont;
        }
    }
    return $string;
}

//Allow any chars in a user name, use wp_create_unsanitary_user instead of wp_create_user, use with caution
function wp_create_unsanitary_user($user_name, $random_password, $user_email) {
    global $UNSANITARY_username;
    $UNSANITARY_username = $user_name;
    $user_id = wp_create_user($user_name, $random_password, $user_email);
    $UNSANITARY_username = null;
    return $user_id;
}

function nx_unsanitary_sanitize_user($un) {
    global $UNSANITARY_username;
    if (isset($UNSANITARY_username)) {
        if ($UNSANITARY_username) {
            if ($un == sanitize_user($UNSANITARY_username, true)) {
                return $UNSANITARY_username;
            }
        }
    }
    return $un;
}

add_filter('pre_user_login', 'nx_unsanitary_sanitize_user');

function get_attachment_id_from_src($image_src) {
    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
    $id = $wpdb->get_var($query);
    return $id;
}

function manual_resize($path, $w, $h, $crop=true) {
    if(strpos($path, get_option('siteurl').'/')!==false){
        $path = str_replace(get_option('siteurl').'/', ABSPATH, $path);
    }
    $info = pathinfo($path);
    $resized_name = $info['dirname'] . '/' . $info['filename'] . '-' . $w . 'x' . $h . '.' . $info['extension'];
    if (!file_exists($resized_name)) {
        $resized_name = image_resize($path, $w, $h, true);
    }
    
    if(!file_exists($resized_name)){
        $resized_name = $path;
    }
    $resized_name = str_replace(ABSPATH, get_option('siteurl') . '/', $resized_name);
    return $resized_name;
}

function dir_to_url($dir){
    $dir = explode('/', $dir);
    $found_wp_content = false;
    foreach($dir as $k => $v){
        if(!$found_wp_content){
            if($v=='wp-content'){
                $found_wp_content = true;
            }else{
                unset($dir[$k]);
            }
        }
    }
    $dir = get_option('siteurl') . '/' . implode('/', $dir);
    return $dir;
}

/**
 * Gets all the site users along with their meta information.
 * @return array 
 */
function get_users_with_meta($args = array()) {
    $defaults = array(
        'role' => ''
        );
    
    $args = wp_parse_args($args, $defaults);
    
    $users = get_users($args);
    foreach ($users as &$user) {
        foreach (get_user_meta($user->ID) as $key => $value) {
            $user->$key = $value; 
        }
    }
    
    return $users;
}

function nx_create_user_and_login($username, $email, $password, $meta) {
    $user_id = wp_create_unsanitary_user($username, $password, $email);
    
    if ($user_id) {
        foreach ($meta as $key => $value) {
            add_user_meta($user_id, $key, $value);
        }

        $user = wp_signon(array('user_login' => $username, 'user_password' => $password, 'remember' => true), false);
        if (is_wp_error($user))
            echo $user->get_error_message();
    }
    
    return $user_id;
}

function nx_create_user($username, $email, $password, $meta) {
    $user_id = wp_create_unsanitary_user($username, $password, $email);
    
    if ($user_id) {
        foreach ($meta as $key => $value) {
            add_user_meta($user_id, $key, $value);
        }
    }
    
    return $user_id;
}

function nx_create_user_and_send_notification($username, $email, $password, $meta) {
    $user_id = wp_create_unsanitary_user($username, $password, $email);
    
    if ($user_id) {
        foreach ($meta as $key => $value) {
            add_user_meta($user_id, $key, $value);
        }
        wp_new_user_notification($username, $password);
    }
    
    return $user_id;
}

function nx_the_excerpt($charlength) {
    $excerpt = get_the_excerpt();
    $charlength++;

    if ( mb_strlen( $excerpt ) > $charlength ) {
        $subex = mb_substr( $excerpt, 0, $charlength - 5 );
        $exwords = explode( ' ', $subex );
        $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
        if ( $excut < 0 ) {
            echo mb_substr( $subex, 0, $excut );
        } else {
            echo $subex;
        }
        echo '[...]';
    } else {
        echo $excerpt;
    }
}

function nx_get_the_excerpt($length) {
    $excerpt = get_the_excerpt();
    $length++;

    $final_excerpt = '';
    if ( mb_strlen( $excerpt ) > $length ) {
        $subex = mb_substr( $excerpt, 0, $length - 5 );
        $exwords = explode( ' ', $subex );
        $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
        if ( $excut < 0 ) {
            $final_excerpt .= mb_substr( $subex, 0, $excut );
        } else {
            $final_excerpt .= $subex;
        }
        $final_excerpt .= '[...]';
    } else {
        $final_excerpt .= $excerpt;
    }

    return $final_excerpt;
}

function get_dynamic_sidebar($index = 1) {
    $sidebar_contents = "";
    ob_start();
    dynamic_sidebar($index);
    $sidebar_contents = ob_get_clean();
    return $sidebar_contents;
}

function get_field_image($postID, $field_name, $size){
    $id = get_field($field_name, $postID);
    return get_image($id, $size);
}

function in_admin(){
    return strpos($_SERVER['HTTP_REFERER'], 'wp-admin') !== false;
}

function get_option_by_blog($option, $default=false, $blog=1){
    switch_to_blog($blog);
    $option_value=get_option($option, $default);
    restore_current_blog();
    return $option_value;
}