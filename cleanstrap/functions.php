<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}

function get_all_widgets()
{		
	$widgets = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^cs_widgets ORDER BY widget_order'));
	
	foreach($widgets as $k => $w){
		$param =  $w['param'];// @preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $w['param']);
		$param = @unserialize($param);
		$widgets[$k]['param'] = $param;
	}
	
	return $widgets;

}
function get_widgets_by_position($position)
{		
	$widgets = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^cs_widgets WHERE position = $ ORDER BY widget_order', $position));
	foreach($widgets as $k => $w){
		$param = unserialize($w['param']);
		$widgets[$k]['param'] = $param;
	}
	return $widgets;

}
function widget_opt($name, $position=false, $order = false, $param = false, $id= false)
{		
	if($position && $param){
		return widget_opt_update($name, $position, $order, $param, $id);		
	}else{
		qa_db_read_one_value(qa_db_query_sub('SELECT * FROM ^cs_widgets WHERE name = $',$name ), true);		
	}
}


function widget_opt_update($name, $position, $order, $param, $id = false){

	if($id){
		qa_db_query_sub(
			'UPDATE ^cs_widgets SET position = $, widget_order = #, param = $ WHERE id=#',
			$position, $order, $param, $id
		);
		return $id;
	}else{
		qa_db_query_sub(
			'INSERT ^cs_widgets (name, position, widget_order, param) VALUES ($, $, #, $)',
			$name, $position, $order, $param
		);
		return qa_db_last_insert_id();
	}
}
function widget_opt_delete($id ){
	qa_db_query_sub('DELETE FROM ^cs_widgets WHERE id=#', $id);
}

function cs_user_data($handle){
	$userid = qa_handle_to_userid($handle);
	$identifier=QA_FINAL_EXTERNAL_USERS ? $userid : $handle;
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		
		$u=qa_db_select_with_pending( 
			qa_db_user_rank_selectspec($handle),
			qa_db_user_points_selectspec($identifier)
		);
		$user = array();
		$user[]['points'] = $u[1]['points'];
		unset($u[1]['points']);
		$user[] = 0;
		$user[] = $u[1];
	}else{
		$user[0] = qa_db_select_with_pending( qa_db_user_account_selectspec($userid, true) );
		$user[1]['rank'] = qa_db_select_with_pending( qa_db_user_rank_selectspec($handle) );
		$user[2] = qa_db_select_with_pending( qa_db_user_points_selectspec($identifier) );
		$user = ($user[0]+ $user[1]+ $user[2]);
	}
	return $user;
}	

function cs_get_avatar($handle, $size = 40, $html =true){

	$userid = qa_handle_to_userid($handle);
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		$img_html = get_avatar( qa_get_user_email($userid), $size);
	}else if(QA_FINAL_EXTERNAL_USERS){
		$img_html = qa_get_external_avatar_html($userid, $size, false);
	}else{
		if (!isset($handle)){
			if ( (qa_opt('avatar_allow_gravatar')||qa_opt('avatar_allow_upload')) && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
				$html=qa_get_avatar_blob_html(qa_opt('avatar_default_blobid'), qa_opt('avatar_default_width'), qa_opt('avatar_default_height'), $size, 0);
			else
				$img_html = '';
		}else{
			$f = cs_user_data($handle);
			if(empty($f['avatarblobid'])){
				if (qa_opt('avatar_allow_gravatar'))
					$img_html = qa_get_gravatar_html(qa_get_user_email($userid), $size);
				elseif ( (qa_opt('avatar_allow_gravatar')||qa_opt('avatar_allow_upload')) && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
					$img_html = qa_get_avatar_blob_html(qa_opt('avatar_default_blobid'), qa_opt('avatar_default_width'), qa_opt('avatar_default_height'), $size, 0);
				else
					$img_html = '';
			} else
				$img_html = qa_get_user_avatar_html($f['flags'], $f['email'], $handle, $f['avatarblobid'], $size, $size, $size, true);
		}
	}
	if (empty($img_html))
		return;
		
	preg_match( '@src="([^"]+)"@' , $img_html , $match );
	if($html)
		return '<a href="'.qa_path_html('user/'.$handle).'">'.(!defined('QA_WORDPRESS_INTEGRATE_PATH') ?  '<img src="'.$match[1].'" />':$img_html).'</a>';		
	elseif(isset($match[1]))
		return $match[1];
}
function cs_get_post_avatar($post, $userid ,$size = 40, $html=false){
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		$avatar = get_avatar( qa_get_user_email($userid), $size);
	}if (QA_FINAL_EXTERNAL_USERS)
		$avatar = qa_get_external_avatar_html($post['userid'], $size, false);
	else
		$avatar = qa_get_user_avatar_html($post['flags'], $post['email'], $post['handle'],
			$post['avatarblobid'], $post['avatarwidth'], $post['avatarheight'], $size);
	if($html)
		return '<div class="avatar" data-id="'.$userid.'" data-handle="'.$post['handle'].'">'.$avatar.'</div>';
	
	return $avatar;
}

function cs_post_type($id){
	$result = qa_db_read_one_value(qa_db_query_sub('SELECT type FROM ^posts WHERE postid=#', $id ),true);
	return $result;
}

function cs_post_status($item){

	if (@$item['answer_selected'] || @$item['raw']['selchildid']){	
		$notice =   '<span class="post-status selected">'.qa_lang_html('cleanstrap/solved').'</span>' ;
	}elseif(@$item['raw']['closedbyid']){
		$type = cs_post_type(@$item['raw']['closedbyid']);
		if($type == 'Q')
			$notice =   '<span class="post-status duplicate">'.qa_lang_html('cleanstrap/duplicate').'</span>' ;	
		else
			$notice =   '<span class="post-status closed">'.qa_lang_html('cleanstrap/closed').'</span>' ;	
	}else{
		$notice =   '<span class="post-status open">'.qa_lang_html('cleanstrap/open').'</span>' ;	
	}
	return $notice;
}
function cs_get_post_status($item){
	// this will return question status whether question is open, closed, duplicate or solved
	
	if (@$item['answer_selected'] || @$item['raw']['selchildid']){	
		$status =   'solved' ;
	}elseif(@$item['raw']['closedbyid']){
		$type = cs_post_type(@$item['raw']['closedbyid']);
		if($type == 'Q')
			$status =   'duplicate' ;	
		else
			$status =   'closed' ;	
	}else{
		$status =   'open' ;	
	}
	return $status;
}
function cs_get_excerpt($id){
	$result = qa_db_read_one_value(qa_db_query_sub('SELECT content FROM ^posts WHERE postid=#', $id ),true);
	return strip_tags($result);
}
function cs_truncate($string, $limit, $pad="...") {
	if(strlen($string) <= $limit) 
		return $string; 
	else{ 
		//preg_match('/^.{1,'.$limit.'}\b/s', $string, $match);
		//return $match[0].$pad;
		$text = $string.' ';
		$text = substr($text,0,$limit);
		$text = substr($text,0,strrpos($text,' '));
		return $text.$pad;
	} 
}
		
function cs_user_profile($handle, $field =NULL){
	$userid = qa_handle_to_userid($handle);
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		return get_user_meta( $userid );
	}else{
		$query = qa_db_select_with_pending(qa_db_user_profile_selectspec($userid, true));
		
		if(!$field) return $query;
		if (isset($query[$field]))
			return $query[$field];
	}
	
	return false;
}	

function cs_user_badge($handle) {
	if(qa_opt('badge_active')){
	$userids = qa_handles_to_userids(array($handle));
	$userid = $userids[$handle];

	
	// displays small badge widget, suitable for meta
	
	$result = qa_db_read_all_values(
		qa_db_query_sub(
			'SELECT badge_slug FROM ^userbadges WHERE user_id=#',
			$userid
		)
	);

	if(count($result) == 0) return;
	
	$badges = qa_get_badge_list();
	foreach($result as $slug) {
		$bcount[$badges[$slug]['type']] = isset($bcount[$badges[$slug]['type']])?$bcount[$badges[$slug]['type']]+1:1; 
	}
	$output='<ul class="user-badge clearfix">';
	for($x = 2; $x >= 0; $x--) {
		if(!isset($bcount[$x])) continue;
		$count = $bcount[$x];
		if($count == 0) continue;

		$type = qa_get_badge_type($x);
		$types = $type['slug'];
		$typed = $type['name'];

		$output.='<li class="badge-medal '.$types.'"><i class="icon-badge" title="'.$count.' '.$typed.'"></i><span class="badge-pointer badge-'.$types.'-count" title="'.$count.' '.$typed.'"> '.$count.'</span></li>';
	}
	$output = substr($output,0,-1);  // lazy remove space
	$output.='</ul>';
	return($output);
	}
}
function cs_name($handle){
	return strlen(cs_user_profile($handle, 'name')) ? cs_user_profile($handle, 'name') : $handle;
}



function cs_post_link($id){
	$type = mysql_result(qa_db_query_sub('SELECT type FROM ^posts WHERE postid = "'.$id.'"'), 0);
	
	if($type == 'A')
		$id = mysql_result(qa_db_query_sub('SELECT parentid FROM ^posts WHERE postid = "'.$id.'"'),0);
	
	$post = qa_db_query_sub('SELECT title FROM ^posts WHERE postid = "'.$id.'"');
	return qa_q_path_html($id, mysql_result($post,0));
}	

function cs_tag_list($limit = 20){
	$populartags=qa_db_single_select(qa_db_popular_tags_selectspec(0, $limit));
			
	$i= 1;
	foreach ($populartags as $tag => $count) {							
		echo '<li><a class="icon-tag" href="'.qa_path_html('tag/'.$tag).'">'.qa_html($tag).'<span>'.filter_var($count, FILTER_SANITIZE_NUMBER_INT).'</span></a></li>';
	}
}

function cs_url_grabber($str) {
	preg_match_all(
	  '#<a\s
		(?:(?= [^>]* href="   (?P<href>  [^"]*) ")|)
		(?:(?= [^>]* title="  (?P<title> [^"]*) ")|)
		(?:(?= [^>]* target=" (?P<target>[^"]*) ")|)
		[^>]*>
		(?P<text>[^<]*)
		</a>
	  #xi',
	  $str,
	  $matches,
	  PREG_SET_ORDER
	);
	

	foreach($matches as $match) {
	 return '<a href="'.$match['href'].'" title="'.$match['title'].'">'.$match['text'].'</a>';
	}	
}

function cs_register_widget_position($widget_array){
	if(is_array($widget_array)){
		qa_opt('cs_widgets_positions', serialize($widget_array));
	}
	return;
}



function cs_get_template_array(){
	return array(
		'qa' 			=> 'QA',
		'home' 			=> 'Home',
		'ask' 			=> 'Ask',
		'question' 		=> 'Question',
		'questions' 	=> 'Questions',
		'activity' 		=> 'Activity',
		'unanswered' 	=> 'Unanswered',
		'hot' 			=> 'Hot',
		'tags' 			=> 'Tags',
		'tag' 			=> 'Tag',
		'categories' 	=> 'Categories',
		'users' 		=> 'Users',
		'user' 			=> 'User',
		'account' 		=> 'Account',
		'favorite' 		=> 'Favorite',
		'user-wall' 	=> 'User Wall',
		'user-activity' => 'User Activity',
		'user-questions' => 'User Questions',
		'user-answers' 	=> 'User Answers',
		'custom' 		=> 'Custom',
		'login' 		=> 'Login',
		'feedback' 		=> 'Feedback',
		'updates' 		=> 'Updates',
		'search' 		=> 'Search',
		'admin' 		=> 'Admin'
	);
}

function cs_social_icons(){
	return array(
		'icon-facebook' 	=> 'Facebook',
		'icon-twitter' 		=> 'Twitter',
		'icon-googleplus' 	=> 'Google',
		'icon-pinterest' 	=> 'Pinterest',
		'icon-linkedin' 	=> 'Linkedin',
		'icon-github' 		=> 'Github',
		'icon-stumbleupon' 	=> 'Stumbleupon',
	);
}



function reset_theme_options(){
	qa_opt('cs_custom_style','');
	// General
	qa_opt('logo_url', Q_THEME_URL . '/images/logo.png');
	qa_opt('cs_mobile_logo_url', Q_THEME_URL . '/images/small-logo.png');
	qa_opt('cs_favicon_url', '');
	qa_opt('cs_featured_image_width', 800);
	qa_opt('cs_featured_image_height', 300);
	qa_opt('cs_featured_thumbnail_width', 278);
	qa_opt('cs_featured_thumbnail_height', 120);
	qa_opt('cs_crop_x', 'c');
	qa_opt('cs_crop_y', 'c');
	
	
	

	// Layout
	qa_opt('cs_styling_rtl', false);
	qa_opt('cs_nav_position', 'top');
	qa_opt('cs_theme_layout', 'boxed');
	qa_opt('cs_nav_fixed', true);	
	qa_opt('cs_show_icon', true);	
	qa_opt('cs_enable_ask_button', true);	
	qa_opt('cs_enable_category_nav', true);	
	qa_opt('cs_enable_clean_qlist', true);	
	qa_opt('cs_enable_default_home', true);	
	qa_opt('cs_enable_except', false);
	qa_opt('cs_except_len', 240);
	if ((int)qa_opt('avatar_q_list_size')>0){
		qa_opt('avatar_q_list_size',35);
		qa_opt('cs_enable_avatar_lists', true);
	}else
		qa_opt('cs_enable_avatar_lists', false);
	qa_opt('show_view_counts', false);
	qa_opt('cs_show_tags_list', true);
	qa_opt('cs_horizontal_voting_btns', false);
	qa_opt('cs_enble_back_to_top', true);
	qa_opt('cs_back_to_top_location', 'nav');
	// Styling
	qa_opt('cs_styling_duplicate_question', false);
	qa_opt('cs_styling_solved_question', false);
	qa_opt('cs_styling_closed_question', false);
	qa_opt('cs_styling_open_question', false);
	qa_opt('cs_bg_select', false);
	qa_opt('cs_bg_color', '#F4F4F4');
	qa_opt('cs_text_color', '');
	qa_opt('cs_border_color', '#EEEEEE');
	qa_opt('cs_q_link_color', '');
	qa_opt('cs_q_link_hover_color', '');
	qa_opt('cs_nav_link_color', '');
	qa_opt('cs_nav_link_color_hover', '');
	qa_opt('cs_subnav_link_color', '');
	qa_opt('cs_subnav_link_color_hover', '');
	qa_opt('cs_link_color', '');
	qa_opt('cs_link_hover_color', '');
	qa_opt('cs_highlight_color', '');
	qa_opt('cs_highlight_bg_color', '');
	qa_opt('cs_ask_btn_bg', '');
	qa_opt('cs_custom_css', '');
	
	// Typography
	$typo = array('h1','h2','h3','h4','h5','p','span','quote','qtitle','qtitlelink','pcontent','mainnav');
	foreach($typo as $k ){
		qa_opt('typo_options_family_' . $k , '');
		qa_opt('typo_options_style_' . $k , '');
		qa_opt('typo_options_size_' . $k , '');
		qa_opt('typo_options_linehight_' . $k , '');
		qa_opt('typo_options_backup_' . $k , '');
	}
	
	// Social
	qa_opt('cs_social_list','');
	qa_opt('cs_social_enable', false);
	
	// Advertisement
	qa_opt('cs_advs','');
	qa_opt('cs_enable_adv_list', false);
	qa_opt('cs_ads_below_question_title', '');
	qa_opt('cs_ads_after_question_content','');

	// footer							
	qa_opt('cs_footer_copyright', 'Copyright © 2015');
	qa_opt('cs_enable_footer_nav', true);
}

function is_featured($postid){
	require_once QA_INCLUDE_DIR.'qa-db-metas.php';
	return (bool)qa_db_postmeta_get($postid, 'featured_question');
}
function get_featured_thumb($postid){
	require_once QA_INCLUDE_DIR.'qa-db-metas.php';
	$img =  qa_db_postmeta_get($postid, 'featured_image');

	if (!empty($img)){
		$thumb_img = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '_s'), $img);
		return '<img class="featured-image" src="'.Q_THEME_URL . '/uploads/' . $thumb_img .'" />';
	}
	return false;
}
function get_featured_image($postid){
	require_once QA_INCLUDE_DIR.'qa-db-metas.php';
	$img =  qa_db_postmeta_get($postid, 'featured_image');

	if (!empty($img))
		return '<img class="image-preview" id="image-preview" src="'.Q_THEME_URL . '/uploads/' . $img.'" />';
		
	return false;
}
function cs_cat_path($categorybackpath){
	return qa_path_html(implode('/', array_reverse(explode('/', $categorybackpath))));
}

/**
 * multi_array_key_exists function.
 *
 * @param mixed $needle The key you want to check for
 * @param mixed $haystack The array you want to search
 * @return bool
 */
function multi_array_key_exists( $needle, $haystack ) {
 
    foreach ( $haystack as $key => $value ) :

        if ( $needle == $key )
            return true;
       
        if ( is_array( $value ) ) :
             if ( multi_array_key_exists( $needle, $value ) == true )
                return true;
             else
                 continue;
        endif;
       
    endforeach;
   
    return false;
}
function make_array_utf8( $arr ) {
    foreach ( $arr as $key => $value )
        if ( is_array( $value ) ) 
            $arr[$key] = make_array_utf8( $value );
        else
			$arr[$key] = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($value));
	return $arr;
}

function cs_get_site_cache(){
	global $cache;
	$cache = json_decode( qa_db_cache_get('cs_cache', 0),true );
}

function cs_ajax_user_popover(){
	
	$handle_id= qa_post_text('handle');
	$handle= qa_post_text('handle');
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
	if(isset($handle)){
		$userid = qa_handle_to_userid($handle);
		//$badges = cs_user_badge($handle);
		
		if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			$userid = qa_handle_to_userid($handle);
			$cover = get_user_meta( $userid, 'cover' );
			$cover = $cover[0];
		}else{
			$data = cs_user_data($handle);
		}

		?>
		<div id="<?php echo $userid;?>_popover" class="user-popover">
			<div class="counts clearfix">
				<div class="points">
					<?php echo '<span>'.$data['points'] .'</span>' . qa_lang_html('cleanstrap/points'); ?>
				</div>
				<div class="qcount">
					<?php echo '<span>'.$data['qposts'] .'</span>' . qa_lang_html('cleanstrap/questions'); ?>
				</div>
				<div class="acount">
					<?php echo '<span>'.$data['aposts'] .'</span>' . qa_lang_html('cleanstrap/answers'); ?>
				</div>
				<div class="ccount">
					<?php echo '<span>'.$data['cposts'] .'</span>' . qa_lang_html('cleanstrap/comments'); ?>
				</div>
			</div>
			<div class="bottom">	
				<div class="avatar pull-left"><?php echo cs_get_avatar($handle, 30); ?></div>
				<span class="name"><?php echo cs_name($handle); ?></span>				
				<span class="level"><?php echo qa_user_level_string($data['level']); ?></span>				
			</div>
		</div>	
		<?php
	}
	die();
}


function cs_ago($time)
{
   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");

   $now = time();

       $difference     = $now - $time;
       $tense         = "ago";

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
       $periods[$j].= "s";
   }

   return "$difference $periods[$j] 'ago' ";
}

function stripslashes2($string) {
	str_replace('\\', '', $string);
    return $string;
}
function qw_is_state_edit(){
	$request = qw_request_text('state');
	if( $request == 'edit')
		return true;
		
	return false;
}
function qw_request_text($field)
/*
	Return string for incoming POST field, or null if it's not defined.
	While we're at it, trim() surrounding white space and converted to Unix line endings.
*/
	{
		if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }
		
		return isset($_REQUEST[$field]) ? preg_replace('/\r\n?/', "\n", trim(qa_gpc_to_string($_REQUEST[$field]))) : null;
	}
