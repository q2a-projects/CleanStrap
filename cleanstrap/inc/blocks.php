<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
	header('Location: /');
	exit;
}

class qa_html_theme extends qa_html_theme_base
{
	var $postid;
	var $widgets;
	function doctype()
	{
		global $widgets;
		$widgets	   = get_all_widgets();
		$this->widgets = $widgets;
		if (isset($_REQUEST['cs_ajax_html'])) {
			$action = 'cs_ajax_' . $_REQUEST['action'];
			if (method_exists($this, $action))
				$this->$action();
		} else {
			$this->output('<!DOCTYPE html>');
			$this->content['navigation']['main']['questions']['icon']	= 'icon-question-sign';
			$this->content['navigation']['main']['unanswered']['icon']   = 'icon-sad';
			$this->content['navigation']['main']['hot']['icon']		  = 'icon-fire';
			$this->content['navigation']['main']['tag']['icon']		  = 'icon-tags2';
			$this->content['navigation']['main']['categories']['icon']   = 'icon-folder-close';
			$this->content['navigation']['main']['user']['icon']		 = 'icon-users';
			$this->content['navigation']['main']['widgets']['icon']	  = 'icon-puzzle';
			$this->content['navigation']['main']['admin']['icon']		= 'icon-wrench';
			$this->content['navigation']['main']['themeoptions']['icon'] = 'icon-wrench';
			
			unset($this->content['navigation']['main']['ask']);
			unset($this->content['navigation']['main']['admin']);
			
			if((bool)qa_opt('cs_enable_category_nav'))
				unset($this->content['navigation']['main']['categories']);
		}
		
	}
	function html()
	{
		if (isset($_REQUEST['cs_ajax_html'])) {
			return;
		} else {
			$this->output('<html lang="' . qa_opt('site_language') . '">');
			
			$this->head();
			$this->body();
			
			$this->output('</html>');
		}
		//cs_set_site_cache();
	}
	
	function body_tags()
	{
		
		$this->output('id="nav-top"');
		qa_html_theme_base::body_tags();
	}
	function finish()
	{
		if (isset($_REQUEST['cs_ajax_html'])) {
			return;
		} else {
			qa_html_theme_base::finish();
		}
	}
	function head_css()
	{
		$this->output('<meta http-equiv="X-UA-Compatible" content="IE=edge" />');
		qa_html_theme_base::head_css();
		$this->output('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
		$fav = qa_opt('cs_favicon_url');
		if( $fav )
			$this->output('<link rel="shortcut icon" href="' .  $fav . '" type="image/x-icon">');
		$this->output('

				<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
				<!--[if lte IE 9]>
					<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/ie.css"/>
				  <script src="' . Q_THEME_URL . '/js/html5shiv.js"></script>
				  <script src="' . Q_THEME_URL . '/js/respond.min.js"></script>
				<![endif]-->
			');
		
		if (qa_opt('cs_enable_gzip')) //Gzip
			$this->output('<LINK REL="stylesheet" TYPE="text/css" HREF="' . Q_THEME_URL . '/inc/gzip.php' . '"/>');
		else {
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/font.css"/>');
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/bootstrap.css"/>');
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/main.css"/>');
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/wide.css"/>');
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/responsive.css"/>');
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/theme-green.css"/>');
			
		}
		if (qa_opt('cs_custom_style_created')){
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/dynamic.css"/>');
		}else{
			$css = qa_opt('cs_custom_css');
			$this->output('<style>' . $css . '</style>');
		}
		$this->output('<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300" rel="stylesheet" type="text/css">');
		$googlefonts = json_decode(qa_opt('typo_googlefonts'), true);
		if (isset($googlefonts) && !empty($googlefonts))
			foreach ($googlefonts as $font_name) {
				$font_name = str_replace(" ", "+", $font_name);
				$link	  = 'http://fonts.googleapis.com/css?family=' . $font_name;
				$this->output('<link href="' . $link . '" rel="stylesheet" type="text/css">');
			}
		if (qa_opt('cs_styling_rtl'))
			$this->output('<link rel="stylesheet" type="text/css" href="' . Q_THEME_URL . '/css/rtl.css"/>');
	}
	function body()
	{
		$this->output('<body');
		$this->body_tags();
		$this->output('>');
		
		$this->body_script();
		$this->body_header();
		$this->body_content();
		$this->body_footer();
		$this->body_hidden();
		
		$this->output('</body>');
	}
	function head_script()
	{
		$this->output('<script> theme_url = "' . Q_THEME_URL . '";</script>');
		qa_html_theme_base::head_script();
		$this->output('<script type="text/javascript" src="' . Q_THEME_URL . '/js/bootstrap.js"></script>');
		
		$this->output('<script type="text/javascript" src="' . Q_THEME_URL . '/js/jquery.sparkline.min.js"></script>');
		
		$this->output('<script type="text/javascript" src="' . Q_THEME_URL . '/js/jquery-ui.min.js"></script>');

		
		if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN))
			$this->output('<script type="text/javascript" src="' . Q_THEME_URL . '/js/jquery.uploadfile.min.js"></script>');
		
		$this->output('<script type="text/javascript" src="' . Q_THEME_URL . '/js/theme.js"></script>');
	}
	
	
	function body_content()
	{
		$this->body_prefix();
		$this->notices();
		$this->header();
		
		if ($this->cs_position_active('Header Left') && $this->cs_position_active('Header') && $this->cs_position_active('Header Right'))
			$class = 4;
		elseif ($this->cs_position_active('Header') && ($this->cs_position_active('Header Left') || $this->cs_position_active('Header Right'))) {
			$class = 5;
		}
		$this->output('<div id="ajax-item">');
		$this->output('<div id="site-body" class="container">');
		
		/* if(qa_opt('cs_nav_position') == 'left')
			$this->left_sidebar(); */
			
		$this->main();
		$this->output('</div>');

		$this->output('</div>');
		
		$this->body_suffix();
		if ((qa_opt('cs_enble_back_to_top')) && (qa_opt('cs_back_to_top_location') == 'right'))
			$this->output('<a id="back-to-top" class="back-to-top-right icon-angle-up t-bg" href="#"></a>');
	}
	function header()
	{
		$this->cs_position('Top');
		
		$this->output('<header id="site-header" class="clearfix">', '<div class="qa-navbar navbar-default' . (qa_opt('cs_nav_fixed')?' navbar-fixed-top ':'') . '" role="navigation">');
		$this->output('<a href="#" class="slide-mobile-menu icon-list"></a>');
		$this->logo();
		
		if (qa_opt('cs_enable_ask_button')){
			$this->output('<a id="nav-ask-btn" href="' . qa_path_html('ask') . '" class="btn btn-sm">' . qa_lang_html('cleanstrap/ask_question') . '</a>');
			$this->output('<a id="nav-ask-btn" href="' . qa_path_html('ask') . '" class="btn btn-sm header-ask-button icon-question-sign"></a>');
		}
		if ( (qa_opt('cs_enable_category_nav')) && (qa_using_categories()) )
			$this->cat_drop_nav();
		$this->head_nav();
		
		$this->user_drop_nav();
		$this->search();
		$this->output('</div>', '</header>');
	}
	function head_nav(){
		$this->nav('main');
	}
	function site_top()
	{
		$this->output('<div id="site-top" class="container">');
		$this->page_title_error();
		if (qa_is_logged_in()) { // output user avatar to login bar
			$this->output('<div class="qa-logged-in-avatar">', QA_FINAL_EXTERNAL_USERS ? qa_get_external_avatar_html(qa_get_logged_in_userid(), 24, true) : qa_get_user_avatar_html(qa_get_logged_in_flags(), qa_get_logged_in_email(), qa_get_logged_in_handle(), qa_get_logged_in_user_field('avatarblobid'), qa_get_logged_in_user_field('avatarwidth'), qa_get_logged_in_user_field('avatarheight'), 24, true), '</div>');
		} else {
			$this->output('<ul class="pull-right top-buttons clearfix">', '<li><a href="#" class="btn">' . qa_lang_html('cleanstrap/login') . '</a></li>', '<li><a href="#" class="btn">' . qa_lang_html('cleanstrap/register') . '</a></li>', '</ul>');
		}
		$this->output('</div>');
	}
	
	function logo()
	{
		$logo = qa_opt('logo_url');
		$this->output('<div class="site-logo">', '<a class="navbar-brand" title="' . strip_tags($this->content['logo']) . '" href="' . qa_opt('site_url') . '">
						<img class="navbar-site-logo" src="' . $logo . '">
					</a>', '</div>');
	}
	function cat_drop_nav()
	{
		ob_start();
?>
			<ul class="nav navbar-nav category-nav pull-left">
				<li class="dropdown pull-left">
					<a data-toggle="dropdown" href="#" class="category-toggle icon-folder-close"><?php
		echo qa_lang_html('cleanstrap/categories');
?></a>
					<ul class="category-list-drop dropdown-menu">
						<?php
		$this->cs_full_categories_list();
?>
					</ul>
				</li>
			</ul>
			<?php
		$this->output(ob_get_clean());
	}
	function user_drop_nav()
	{
		ob_start();
		if (qa_is_logged_in()) {
			
?>
				<ul class="nav navbar-nav navbar-avatar pull-right">
					<li class="dropdown pull-right" id="menuLogin">
						<a id="profile-link" data-toggle="dropdown" href="<?php
			echo qa_path_html('user/' . qa_get_logged_in_handle());
?>" class="avatar">
							<?php
			$LoggedinUserAvatar = cs_get_avatar(qa_get_logged_in_handle(), 30, false);
			if (!empty($LoggedinUserAvatar))
				echo '<img src="' . $LoggedinUserAvatar . '" />'; // in case there is no Avatar image and theme doesn't use a default avatar
			else
				echo '<span class="profile-name">' . qa_get_logged_in_handle() . '</span>';
?>
						</a>
						<ul class="user-nav dropdown-menu">
							
							<?php
			if(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)	
				$this->content['navigation']['user']['admin'] = array('label' => qa_lang_html('cleanstrap/admin'), 'url' => qa_path_html('admin'), 'icon'=> 'icon-wrench');
			$this->content['navigation']['user']['profile'] = array('label' => qa_lang_html('cleanstrap/profile'), 'url' => qa_path_html('user/' . qa_get_logged_in_handle()), 'icon'=> 'icon-vcard');
			$this->content['navigation']['user']['updates']['icon'] = 'icon-signal';
			$this->content['navigation']['user']['account'] = array('label' => qa_lang('cleanstrap/account'), 'url' => qa_path_html('account'), 'icon' => 'icon-cog');
			$this->content['navigation']['user']['favorites'] = array('label' => qa_lang('cleanstrap/favorites'), 'url' => qa_path_html('favorites'), 'icon' =>'icon-heart');
			$this->content['navigation']['user']['wall'] = array('label' => qa_lang('cleanstrap/wall'), 'url' => qa_path_html('user/'.qa_get_logged_in_handle().'/wall'), 'icon' =>'icon-edit');
			$this->content['navigation']['user']['recent_activity'] = array('label' => qa_lang('cleanstrap/recent_activity'), 'url' => qa_path_html('user/'.qa_get_logged_in_handle().'/activity'), 'icon' =>'icon-time');
			$this->content['navigation']['user']['all_questions'] = array('label' => qa_lang('cleanstrap/all_questions'), 'url' => qa_path_html('user/'.qa_get_logged_in_handle().'/questions'), 'icon' =>'icon-question');
			$this->content['navigation']['user']['all_answers'] = array('label' => qa_lang('cleanstrap/all_answers'), 'url' => qa_path_html('user/'.qa_get_logged_in_handle().'/answers'), 'icon' =>'icon-answer');
			
			$user_menu = array_merge(array_flip(array('admin', 'widgets', 'themeoptions', 'profile')), $this->content['navigation']['user']);

			foreach ($user_menu as $k => $a) {
				if (isset($a['url']) && $k != 'logout') {
					$icon = (isset($a['icon']) ? ' class="' . $a['icon'] . '" ' : '');
					echo '<li class="user-nav-'.$k . (isset($a['selected']) ? ' active' : '') . '"><a' . $icon . ' href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
				}
			}
			
			if (isset($this->content['navigation']['user']['logout']['url']))
				$link = $this->content['navigation']['user']['logout']['url'];
			else
				$link = qa_opt('site_url') . "logout";
			echo "<li><a class='icon-switch' href = '$link'> " . qa_lang_html('cleanstrap/logout') . " </a></li>";

?>
						</ul>
					</li>
				</ul>
			
			<?php
		} else {
?>				
				<a class="btn login-register icon-login"  href="#" data-toggle="modal" data-target="#login-modal" title="<?php
			echo qa_lang_html('cleanstrap/login_register');
?>"></a>
			<?php
		}
		//unset($this->content['navigation']['user']['login']);
		//unset($this->content['navigation']['user']['register']);
		$this->output(ob_get_clean());
		
	}
	function cs_add_modal(){
	?>
		<!-- Modal -->
					<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					  <div class="modal-dialog">
						<div class="modal-content">	
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						</div>	
						  <div class="modal-body">
							<h3><?php
				echo qa_lang_html('cleanstrap/login_register');
	?></h3>
							<p class="login-desc"><?php
				echo qa_lang_html('cleanstrap/access_account');
	?></p>
							<div class="social-logins">
							<?php
				
				foreach ($this->content['navigation']['user'] as $k => $custom) {
					if (isset($custom) && (($k != 'login') && ($k != 'register'))) {
						preg_match('/class="([^"]+)"/', $custom['label'], $class);
						
						if ($k == 'facebook')
							$icon = 'class="' . $class[1] . ' icon-facebook"';
						elseif ($k == 'google')
							$icon = 'class="' . $class[1] . ' icon-googleplus"';
						elseif ($k == 'twitter')
							$icon = 'class="' . $class[1] . ' icon-twitter"';
						
						$this->output(str_replace($class[0], $icon, $custom['label']));
					}
				}
				
	?>
							</div>
							<div class="row">
								<div class="col-sm-6">
								<form id="loginform" role="form" action="<?php
				echo $this->content['navigation']['user']['login']['url'];
	?>" method="post">
								<div class="input-group">
								  <span class="input-group-addon"><i class="icon-user"></i></span>
								  <input type="text" class="form-control" id="qa-userid" name="emailhandle" placeholder="<?php
				echo trim(qa_lang_html('users/email_handle_label'), ':');
	?>" />
								</div>
								<div class="input-group">
								  <span class="input-group-addon"><i class="icon-key"></i></span>
								  <input type="password" class="form-control" id="qa-password" name="password" placeholder="<?php
				echo trim(qa_lang_html('users/password_label'), ':');
	?>" />
								</div>
									
									<label class="inline">
										<input type="checkbox" name="remember" id="qa-rememberme" value="1"> <?php
				echo qa_lang_html('users/remember');
	?>
									</label>
									<input type="hidden" name="code" value="<?php
				echo qa_html(qa_get_form_security_code('login'));
	?>"/>
									<input type="submit" value="<?php
				echo qa_lang_html('cleanstrap/login');
	?>" id="qa-login" name="dologin" class="btn btn-primary btn-large btn-block" />
								</form>
								</div>
								<div class="col-sm-6">
								<form id="loginform" role="form" action="<?php
				echo $this->content['navigation']['user']['register']['url'];
	?>" method="post">
									<div class="input-group">
										<span class="input-group-addon"><i class="icon-user"></i></span>
										<input type="text" class="form-control" id="qa-userid" name="handle" placeholder="<?php
				echo trim(qa_lang_html('users/handle_label'), ':');
	?>" />
									</div>
									<div class="input-group">
										<span class="input-group-addon"><i class="icon-key"></i></span>
										<input type="password" class="form-control" id="qa-password" name="password" placeholder="<?php
				echo trim(qa_lang_html('users/password_label'), ':');
	?>" />
									</div>
									<div class="input-group">
										<span class="input-group-addon"><i class="icon-envelope"></i></span>
										<input type="text" id="email" class="form-control" name="email" 	placeholder="<?php
				echo trim(qa_lang_html('users/email_label'), ':');
	?>">
									</div>
									
									<input type="hidden" name="code" value="<?php
				echo qa_html(qa_get_form_security_code('register'));
	?>"/>
									<input type="submit" value="<?php
				echo $this->content['navigation']['user']['register']['label'];
	?>" id="qa-register" name="doregister" class="btn btn-primary btn-block" />								
								</form>
								</div>
							</div>							
						  </div>
						</div>
					  </div>
					</div>
				<?php
	}
	function search()
	{
		$search = $this->content['search'];
		
		$this->output('<form ' . $search['form_tags'] . ' class="navbar-form navbar-right form-search" role="search" >', @$search['form_extra']);
		
		$this->search_field($search);
		//$this->search_button($search);
		
		$this->output('</form>');
	}
	function search_field($search)
	{
		$this->output('<span class="icon-search"></span>', '<input type="text" ' . $search['field_tags'] . ' value="' . @$search['value'] . '" class="form-control search-query" placeholder="' . qa_lang_html('cleanstrap/search') . '" autocomplete="off" />', '');
	}
	
	function search_button($search)
	{
		$this->output('<input type="submit" value="' . $search['button_label'] . '" class="btn btn-default"/>');
	}
	
	function sidepanel()
	{
		if ($this->cs_position_active('Right')) {
			$this->output('<div class="col-sm-4 side-c">');
			$this->output('<div class="qa-sidepanel">');
			$this->cs_position('Right');
			$this->output('</div>', '');
			
			$this->output('</div>');
		}
	}
	
	function right_tabs()
	{
		ob_start();
?>
				<div class="user-tabs">
					<ul class="nav nav-tabs">
					  <li class="active"><a href="#right-top-users" data-toggle="tab"><?php
		echo qa_lang_html('cleanstrap/top_users');
?></a></li>
					  <li><a href="#right-new-user" data-toggle="tab"><?php
		echo qa_lang_html('cleanstrap/new_users');
?></a></li>
					</ul>

					<!-- Tab panes -->
					<div class="tab-content">
					  <div class="tab-pane active" id="right-top-users">
						<?php
		$this->cs_position('Top Users');
?>
					  </div>
					  <div class="tab-pane" id="right-new-user">
						<?php
		$this->cs_position('New Users');
?>
					  </div>
					</div>
				</div>
			<?php
		$this->output(ob_get_clean());
	}
	function cs_pie_stats()
	{
		$this->output('
			<section class="panel">
				<header class="panel-heading">Activity</header>
				<div class="panel-body text-center">			  
			  <div class="pieact inline" data-type="pie" data-height="175" data-slice-colors="[\'#233445\',\'#3fcf7f\',\'#ff5f5f\',\'#f4c414\',\'#13c4a5\']">' . qa_opt('cache_qcount') . ',' . qa_opt('cache_acount') . ',' . qa_opt('cache_ccount') . ',' . qa_opt('cache_unaqcount') . ',' . qa_opt('cache_unselqcount') . '</div>
			  <div class="line pull-in"></div>
			  <div class="acti-indicators">
				<ul>
					<li><i class="fa fa-circle text-info" style="color:#233445"></i> ' . qa_lang_html('cleanstrap/questions') . ' <span>' . qa_opt('cache_qcount') . '</span></li>
					<li><i class="fa fa-circle text-info" style="color:#3fcf7f"></i> ' . qa_lang_html('cleanstrap/answers') . ' <span>' . qa_opt('cache_acount') . '</span></li>
					<li><i class="fa fa-circle text-info" style="color:#FF5F5F"></i> ' . qa_lang_html('cleanstrap/comments') . ' <span>' . qa_opt('cache_ccount') . '</span></li>
					<li><i class="fa fa-circle text-info" style="color:#13C4A5"></i> ' . qa_lang_html('cleanstrap/unanswered') . ' <span>' . qa_opt('cache_unaqcount') . '</span></li>
					<li><i class="fa fa-circle text-info" style="color:#F4C414"></i> ' . qa_lang_html('cleanstrap/unselected') . ' <span>' . qa_opt('cache_unselqcount') . '</span></li>
				</ul>
			  </div>
			</div>
			</section>
			');
	}
	
	function user_sidebar()
	{
		ob_start();
?>
				<ul class="user-sidebar">
					<li class="points"><?php
		echo qa_get_logged_in_points();
?></li>
					<li><a class="icon-profile" href="<?php
		echo qa_path_html('user/' . qa_get_logged_in_handle());
?>"><?php
		qa_lang('Profile');
?></a></li>
					<?php
		foreach ($this->content['navigation']['user'] as $a) {
			if (isset($a['url'])) {
				$icon = (isset($a['icon']) ? ' class="' . $a['icon'] . '" ' : '');
				echo '<li' . (isset($a['selected']) ? ' class="active"' : '') . '><a' . $icon . ' href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
			}
		}
		if (!isset($this->content['navigation']['user']['logout']['url'])) {
			$link = qa_opt('site_url') . "logout";
			echo "<li><a class='icon-switch' href = '$link'> " . qa_lang_html('cleanstrap/logout') . " </a></li>";
		}
?>
				</ul>
			<?php
		$this->output(ob_get_clean());
	}
	
	function left_sidebar()
	{
		$this->output('<div class="left-sidebar">');
		$this->output('<div class="float-nav">');
		
		if ($this->template == 'admin')
			$this->nav('sub');
		else
			$this->nav('main');
		
		$this->cs_position('Left');
		
		
		if ((qa_opt('cs_enble_back_to_top')) && (qa_opt('cs_back_to_top_location') == 'nav'))
			$this->output('<a id="back-to-top" class="back-to-top-nav icon-angle-up t-bg" href="#"></a>');
		
		$this->output('</div>');
		$this->output('</div>');
	}
	
	function cs_full_categories_list($show_sub = false)
	{
		
		$level	  = 1;
		$navigation = @$this->content['navigation']['cat'];
		
		if (!isset($navigation)) {
			$categoryslugs = qa_request_parts(1);
			$cats		  = qa_db_select_with_pending(qa_db_category_nav_selectspec($categoryslugs, false, false, true));
			$navigation	= qa_category_navigation($cats);
		}
		if (count($navigation) > 1) { // if there are any categories (except 'all categories' navigation item)
			//$this->output( '<div class="qa-nav-cat">');
			//$this->output( '<ul class="qa-nav-cat-list">');
			$index = 0;
			if(isset($QA_CONST_PATH_MAP))
				$navigation['all']['url'] = qa_path_html($QA_CONST_PATH_MAP['categories']);
			else
				$navigation['all']['url'] = qa_path_html('categories');
			foreach ($navigation as $key => $navlink) {
				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				$this->cs_full_categories_list_item($key, $navlink, '', $level, $show_sub);
			}
			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			
			//$this->output('</ul></div>');
		}
		unset($navigation);
	}
	
	function cs_full_categories_list_item($key, $navlink, $class, $level = null, $show_sub)
	{
		$suffix = strtr($key, array( // map special character in navigation key
			'$' => '',
			'/' => '-'
		));
		$class .= "nav-cat";
		$this->output('<li class="qa-nav-cat-item">');
		$this->nav_link($navlink, $class);
		//$this->output('</li>');
		
		if (count(@$navlink['subnav']) && $show_sub)
			$this->nav_list($navlink['subnav'], $class, 1 + $level);
		
		$this->output('</li>');
	}
	
	function main()
	{
		$content = $this->content;
		$class = 'main';
		if ($this->template == 'admin' && qa_opt('cs_nav_position') == 'top')
			$this->nav('sub');
			
		$this->output('<div class="clearfix qa-main' . (@$this->content['hidden'] ? ' qa-main-hidden' : '') . '">');
		$col_width = ($this->cs_position_active('Right') && $this->template != 'question');
		
		$this->output('<div class="col-sm-' . ($col_width ? '8' : '12') . ' list-c">');
		
		if ($this->template != 'user-answers' && $this->template != 'user-questions' && $this->template != 'user-activity' && $this->template != 'user-wall' && $this->template != 'question' && $this->template != 'user' && (!strlen(qa_request(1)) == 0) && (!empty($this->content['title']))) {
			$this->output('<h1 class="page-title">', $this->content['title']);
			$this->feed();
			$this->favorite();
			$this->output('</h1>');
		}
		if ($this->cs_position_active('Header')) {
			$this->output('<div class="header-position-c clearfix">');
			
			$this->output('<h1 class="intro-title"> ' . qa_lang_html('cleanstrap/got_questions') . '</h1>');
			
			if ($this->cs_position_active('Header Left')) {
				$this->output('<div class="col-md-' . $class . '">');
				$this->cs_position('Header Left');
				$this->output('</div>');
			}
			
			if ($this->cs_position_active('Header')) {
				$this->output('<div class="col-md-' . (12 - @$class) . '">');
				$this->cs_position('Header');
				$this->output('</div>');
			}
			if ($this->cs_position_active('Header Right')) {
				$this->output('<div class="col-md-' . $class . '">');
				$this->cs_position('Header Right');
				$this->output('</div>');
			}
			$this->output('</div>');
		}
		
		if ($this->template != 'question')
			$this->cs_position('Content Top');
		
		if (isset($this->content['error']))
			$this->error(@$this->content['error']);
		if ($this->template == 'user' && !(isset($_REQUEST['state']) && $_REQUEST['state'] == 'edit')) {
			$this->profile_page();
		} elseif (strlen(qa_request(1)) == 0) {
			$this->home($content);
		} elseif ($this->template == 'question') {
			$this->question_view($content);
		} elseif ($this->template == 'user-wall') {
			$handle = qa_request_part(1);
			$this->output('<section id="content" class="content-sidebar user-cols">');
			$this->cs_user_nav($handle);
			$this->output('<div class="messages">');
			$this->message_list_and_form($this->content['message_list']);
			$this->output('</div></section>');
		} elseif ($this->template == 'user' && (isset($_REQUEST['state']) && $_REQUEST['state'] == 'edit')) {
			$handle = qa_request_part(1);
			if (!strlen($handle)) {
				$handle = qa_get_logged_in_handle();
			}
			
			$this->output('<section id="content" class="content-sidebar user-cols">');
			$this->cs_user_nav($handle);
			$this->main_parts($content);
			$this->output('</section>');
		} elseif ($this->template == 'account' || $this->template == 'favorites' || $this->template == 'user-activity' || $this->template == 'user-questions' || $this->template == 'user-answers') {
			$handle = qa_request_part(1);
			if (!strlen($handle)) {
				$handle = qa_get_logged_in_handle();
			}
			
			$this->output('<section id="content" class="content-sidebar user-cols">');
			$this->cs_user_nav($handle);
			$this->main_parts($content);
			$this->output('</section>');
		} else {
			
			if ($this->template != 'admin')
				$this->nav('sub');
			$this->main_parts($content);
			
			if ($this->template != 'question')
				$this->page_links();
			
			$this->suggest_next();
		}
		$this->cs_position('Content Bottom');
		
		$this->output('</div>');
		
		if ($col_width) {
			$this->sidepanel();
		}
		$this->output('</div>');
		$this->cs_position('Bottom');
		$this->footer();
	}
	function title() // add RSS feed icon after the page title
	{
		qa_html_theme_base::title();
		
		$feed = @$this->content['feed'];
		
		if (!empty($feed))
			$this->output('<a href="' . $feed['url'] . '" title="' . @$feed['label'] . '"><img src="' . $this->rooturl . 'images/rss.jpg" alt="" width="16" height="16" border="0" class="qa-rss-icon"/></a>');
	}
	
	function home($content)
	{
		$this->output('<div class="home-left-inner">');
		$this->cs_position('Home Slide');
		$this->output('<div class="row home-pos-one">');
		$this->output('<div class="col-md-8">');
		$this->output('<div class="row">');
		$this->output('<div class="col-sm-6">');
		$this->cs_position('Home 1 Left');
		$this->output('</div>');
		$this->output('<div class="col-sm-6">');
		$this->cs_position('Home 1 Center');
		$this->output('</div>');
		$this->output('</div>');
		$this->output('<div class="row">');
		if (!(bool) qa_opt('cs_enable_default_home'))
			$this->cs_position('Home 2');
		else
			$this->main_parts($content);
		
		$this->output('</div>');
		$this->output('<div class="row">');
		$this->output('<div class="col-sm-6">');
		$this->cs_position('Home 3 Left');
		$this->output('</div>');
		$this->output('<div class="col-sm-6">');
		$this->cs_position('Home 3 Center');
		$this->output('</div>');
		$this->output('</div>');
		$this->output('</div>');
		$this->output('<div class="col-md-4">');
		$this->cs_position('Home Right');
		$this->output('</div>');
		$this->output('</div>');
		$this->suggest_next();
		$this->output('</div>');
	}
	
	function question_view($content)
	{
		
		$this->output('<div class="question-main">');
		$this->cs_position('Content Top');
		
		$this->output('<div class="row">');
			$this->output('<div class="col-md-8 question-c-l">');
				$this->main_parts($content);
			$this->output('</div>');
			$this->output('<div class="col-md-4 question-sidebar">');

				if(isset($content['q_view']['raw']['postid'])){
					$featured_image = get_featured_image($content['q_view']['raw']['postid']);
					if($featured_image){
						$this->output('<div class="widget">');   
						$this->output('<h3 class="widget-title">'.qa_lang('cleanstrap/featured_image').'</h3>');   
						$this->output('<div class="question-image-container">');   
						$this->output($featured_image);
						$this->output('</div>');
						$this->output('</div>');
					}
				}
				$this->cs_position('Question Right');
			$this->output('</div>');
		
		$this->output('</div>');
		$this->output('</div>');
	}
	
	function q_list_item($q_item)
	{
		$status = cs_get_post_status($q_item);
		if (qa_opt('styling_' . $status . '_question'))
			$status_class = ' qa-q-status-' . $status;
		else
			$status_class = '';
		$this->output('<div class="qa-q-list-item' . rtrim(' ' . @$q_item['classes']) . $status_class . (is_featured($q_item['raw']['postid']) ? ' featured' : '') . ' clearfix" ' . @$q_item['tags'] . '>');
		
		
		$this->q_item_main($q_item);
		
		$this->output('</div> <!-- END question list item -->', '');
	}
	
	
	function q_item_stats($q_item) // add view count to question list
	{
		$this->output('<div class="qa-q-item-stats">');
		$this->a_count($q_item);
		qa_html_theme_base::view_count($q_item);
		$this->output('</div>');
	}
	
	function q_item_main($q_item)
	{
		$avatar_arg = array(
			'flags' => $q_item['raw']['flags'],
			'email' => $q_item['raw']['email'],
			'handle' => $q_item['raw']['handle'],
			'avatarblobid' => $q_item['raw']['avatarblobid'],
			'avatarwidth' => $q_item['raw']['avatarwidth'],
			'avatarheight' => $q_item['raw']['avatarheight']
		);
		
		$enable_avatar = ((int)qa_opt('avatar_q_page_q_size')>0);
		$avatar_size = ((bool) qa_opt('cs_enable_clean_qlist') ? 20 : 30);
		$timeCode	= $q_item['when'];
		$when		= @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
		
		if ($enable_avatar && isset($q_item['avatar']) && !((bool) qa_opt('cs_enable_clean_qlist'))) {
			$this->output('<div class="asker-avatar">');
			$this->output(cs_get_post_avatar($avatar_arg, $q_item['raw']['userid'], $avatar_size, true));
			$this->output('</div>');
		}
		$this->output('<div class="qa-q-item-main">');
		if (!(bool) qa_opt('cs_enable_clean_qlist')){
			$this->output('<div class="ans-count total-' . $q_item['raw']['acount'] . '">' . $q_item['raw']['acount'] . '<span>'.qa_lang('cleanstrap/ans') . '</span></div>');
		}
		$this->output('<div class="q-item-head">');
		
		
		if ($enable_avatar && (bool) qa_opt('cs_enable_clean_qlist')) {
			 
			$this->output('<div class="count-time"><span class="time">' . $when . '</span>');
			$this->output('<span class="ans-count total-' . $q_item['raw']['acount'] . '">' . $q_item['raw']['acount'] . '</span></div>');
			$this->output(cs_get_post_avatar($avatar_arg, $q_item['raw']['userid'], $avatar_size, true));
			$this->output('<span class="status-c">' . cs_post_status($q_item) . '</span>');
		}
		
		$this->q_item_title($q_item);
		if (!(bool) qa_opt('cs_enable_clean_qlist')) {
			$this->output('<div class="list-meta">');
			$this->output(cs_post_status($q_item));
			$this->post_meta($q_item, 'qa-q-item');
			qa_html_theme_base::view_count($q_item);
			if (qa_opt('cs_show_tags_list')) {
				$this->output('<span>' . qa_lang('cleanstrap/tagged') . ': </span>');
				$this->post_tag_list($q_item, 'list-tag');
			}
			$this->output('</div>');
		}
		$this->output('</div>');
		
		$this->q_item_content($q_item);
		
		$this->q_item_buttons($q_item);
		
		$this->output('</div>');
	}
	
	function attribution()
	{
	}
	
	function footer()
	{
		$this->output('<footer id="site-footer" class="clearfix">');
		if(qa_opt('cs_enable_footer_nav'))
			$this->nav('main');
		$this->nav('footer');
		$this->get_social_links();
		
		$this->output('<div class="qa-attribution-right">');
			if ((bool) qa_opt('cs_footer_copyright'))
				$this->output(qa_opt('cs_footer_copyright'));
			$this->output('<p class="developer">Powered by Q2A and <a href="http://qa-themes.com/themes/cleanstrap">CleanStrap Theme</a></p>');
		$this->output('</div>');
		
		$this->output('</footer>');
		// content for login/register modal
		if (! qa_is_logged_in()) 
			$this->cs_add_modal();
	}
	
	function get_social_links()
	{
		if ((bool) qa_opt('cs_social_enable')) {
			$links = json_decode(qa_opt('cs_social_list'));
			
			$this->output('<ul class="ra-social-links">');
			foreach ($links as $link) {
				$icon  = ($link->social_icon != '1' ? ' ' . $link->social_icon . '' : '');
				$image = ($link->social_icon == '1' ? '<img src="' . $link->social_icon_file . '" />' : '');
				$this->output('<li><a class="t-bg-4' . @$icon . '" href="' . $link->social_link . '" title="Link to ' . $link->social_title . '" >' . @$image . '</a></li>');
			}
			$this->output('</ul>');
		}
	}
	
	function nav_item($key, $navlink, $class, $level = null)
	{
		$suffix = strtr($key, array( // map special character in navigation key
			'$' => '',
			'/' => '-'
		));
		
		$this->output('<li class="qa-' . $class . '-item' . (@$navlink['opposite'] ? '-opp' : '') . (@$navlink['state'] ? (' qa-' . $class . '-' . $navlink['state']) : '') . ' qa-' . $class . '-' . $suffix . '">');
		$this->nav_link($navlink, $class);
		
		if (count(@$navlink['subnav']))
			$this->nav_list($navlink['subnav'], $class, 1 + $level);
		
		$this->output('</li>');
	}
	function nav_link($navlink, $class)
	{
		$icon = isset($navlink['icon']) ? $navlink['icon'] : ($this->template == 'admin' ? 'icon-cog' : '');
		if (isset($navlink['url']))
			$this->output('<a href="' . $navlink['url'] . '" class="' . $icon . ' qa-' . $class . '-link' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' qa-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . (isset($navlink['target']) ? (' target="' . $navlink['target'] . '"') : '') . '>' . $navlink['label'] . (strlen(@$navlink['note']) ? '<span class="qa-' . $class . '-note">' . filter_var($navlink['note'], FILTER_SANITIZE_NUMBER_INT) . '</span>' : '') . '</a>');
		
		else
			$this->output('<span class="qa-' . $class . '-nolink' . (@$navlink['selected'] ? (' qa-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' qa-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . '>' . @$navlink['label'] . (strlen(@$navlink['note']) ? '<span class="qa-' . $class . '-note">' . filter_var($navlink['note'], FILTER_SANITIZE_NUMBER_INT) . '</span>' : '') . '</span>');
	}
	function page_title_error()
	{
		$favorite = @$this->content['favorite'];
		
		if (isset($favorite))
			$this->output('<form ' . $favorite['form_tags'] . '>');
		
		$this->output('<h1 class="main-title">');
		$this->favorite();
		$this->title();
		$this->output('</h1>');
		
		if (isset($this->content['error']))
			$this->error(@$this->content['error']);
		
		if (isset($favorite)) {
			$this->form_hidden_elements(@$favorite['form_hidden']);
			$this->output('</form>');
		}
	}
	
	function post_avatar_meta($post, $class, $avatarprefix = null, $metaprefix = null, $metaseparator = '<br/>')
	{
		$this->output('<div class="' . $class . '-avatar-meta">');
		//$this->post_avatar($post, $class, $avatarprefix);
		$this->post_meta($post, $class, $metaprefix, $metaseparator);
		$this->output('</div>');
	}
	
	function feed()
	{
		$feed = @$this->content['feed'];
		
		if (!empty($feed)) {
			$this->output('<a href="' . $feed['url'] . '" class="feed-link icon-rss" title="' . @$feed['label'] . '"></a>');
		}
	}
	
	function page_links()
	{
		$page_links = @$this->content['page_links'];
		
		if (!empty($page_links)) {
			$this->page_links_list(@$page_links['items']);
		}
	}
	function page_links_list($page_items)
	{
		if (!empty($page_items)) {
			$this->output('<ul class="pagination clearfix">');
			
			$index = 0;
			
			foreach ($page_items as $page_link) {
				$this->set_context('page_index', $index++);
				$this->page_links_item($page_link);
				
				if ($page_link['ellipsis'])
					$this->page_links_item(array(
						'type' => 'ellipsis'
					));
			}
			
			$this->clear_context('page_index');
			
			$this->output('</ul>');
		}
	}
	
	function voting($post)
	{
		
		$code = @$post['voting_form_hidden']['code'];
		if (isset($post['vote_view'])) {
			$state = @$post['vote_state'];
			$this->output('<div class="' . $state . ' ' . (isset($this->content['q_list']) ? 'list-' : '') . 'voting clearfix ' . (qa_opt('cs_horizontal_voting_btns') ? 'voting-horizontal ' : 'voting-vertical ') . (($post['vote_view'] == 'updown') ? 'qa-voting-updown' : 'qa-voting-net') . (($post['raw']['netvotes'] < (0)) ? ' negative' : '') . (($post['raw']['netvotes'] > (0)) ? ' positive' : '') . '" ' . @$post['vote_tags'] . '>');
			$this->voting_inner_html($post);
			$this->output('</div>');
		}
	}
	
	
	function voting_inner_html($post)
	{
		
		$up_tags   = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_up_tags']));
		$down_tags = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_down_tags']));
		if (qa_is_logged_in()) {
			$user_point = qa_get_logged_in_points();
			if ($post['raw']['type'] == 'Q') {
				if ((qa_opt('permit_vote_q') == '106')) {
					$need	= (qa_opt('permit_vote_q_points') - $user_point);
					$up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need ' . $need . ' more points to vote', $up_tags);
				}
				
				if ((qa_opt('permit_vote_q') == '106') && (qa_opt('permit_vote_down') == '106')) {
					$max	   = max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_q_points'));
					$need	  = ($max - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
					
				} elseif (qa_opt('permit_vote_q') == '106') {
					$need	  = (qa_opt('permit_vote_q_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
				} elseif (qa_opt('permit_vote_down') == '106') {
					$need	  = (qa_opt('permit_vote_down_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
				}
			}
			if ($post['raw']['type'] == 'A') {
				if ((qa_opt('permit_vote_a') == '106')) {
					$need	= (qa_opt('permit_vote_a_points') - $user_point);
					$up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need ' . $need . ' more points to vote', $up_tags);
				}
				if ((qa_opt('permit_vote_a') == '106') && (qa_opt('permit_vote_down') == '106')) {
					$max	   = max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_a_points'));
					$need	  = ($max - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
					
				} elseif (qa_opt('permit_vote_a') == '106') {
					$need	  = (qa_opt('permit_vote_a_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
				} elseif (qa_opt('permit_vote_down') == '106') {
					$need	  = (qa_opt('permit_vote_down_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need ' . $need . ' more points to vote" ', $down_tags);
				}
			}
		}
		
		$state	 = @$post['vote_state'];
		$code	  = qa_get_form_security_code('vote');
		$vote_text = ($post['raw']['netvotes'] > 1 || $post['raw']['netvotes'] < (-1)) ? qa_lang('cleanstrap/votes') : qa_lang('cleanstrap/vote');
		
		if (isset($post['vote_up_tags']))
			$this->output('<a ' . @$up_tags . ' href="#" data-code="' . $code . '" class=" icon-thumbs-up2 enabled vote-up ' . $state . '"></a>');
		$this->output('<span class="count">' . $post['raw']['netvotes'] . '</span>');
		if (isset($post['vote_down_tags']))
			$this->output('<a ' . @$down_tags . ' href="#" data-code="' . $code . '" class=" icon-thumbs-down2 enabled vote-down ' . $state . '"></a>');
		
	}
	
	
	function vote_count($post)
	{
		// You can also use $post['upvotes_raw'], $post['downvotes_raw'], $post['netvotes_raw'] to get
		// raw integer vote counts, for graphing or showing in other non-textual ways
		
		$this->output('<div class="qa-vote-count ' . (($post['vote_view'] == 'updown') ? 'qa-vote-count-updown' : 'qa-vote-count-net') . '"' . @$post['vote_count_tags'] . '>');
		
		if ($post['vote_view'] == 'updown') {
			$this->output_split($post['upvotes_view'], 'qa-upvote-count');
			$this->output_split($post['downvotes_view'], 'qa-downvote-count');
			
		} else
			$this->output($post['raw']['netvotes']);
		
		$this->output('</div>');
	}
	function vote_hover_button($post, $element, $value, $class)
	{
		if (isset($post[$element]))
			$this->output('<button ' . $post[$element] . ' type="submit" class="' . $class . '-button btn">' . $value . '</button>');
	}
	function vote_disabled_button($post, $element, $value, $class)
	{
		if (isset($post[$element]))
			$this->output('<button ' . $post[$element] . ' type="submit" class="btn ' . $class . '-disabled" disabled="disabled">' . $value . '</button>');
	}
	function q_view($q_view)
	{
		if (!empty($q_view)) {
			$this->output('<div class="qa-q-view' . (@$q_view['hidden'] ? ' qa-q-view-hidden' : '') . rtrim(' ' . @$q_view['classes']) . '"' . rtrim(' ' . @$q_view['tags']) . '>');
			
			$this->q_view_main($q_view);
			
			$this->output('</div>', '');
		}
	}
	function q_view_main($q_view)
	{
		
		$this->output('<div class="question-head">');
		$this->output('<div class="big-s-avatar avatar">' . cs_get_avatar($q_view['raw']['handle'], 70) . '</div>');
		
		$this->output('<div class="no-overflow">');
		$this->output('<h2 class="question-title">');
		
		$this->favorite();
		$this->voting($q_view);
		$this->output(htmlspecialchars($q_view['raw']['title']));
		$this->output('</h2>'); 
		
		$this->output('<div class="question-meta">', cs_post_status($q_view), (is_featured($q_view['raw']['postid']) ? '<span class="featured-sticker icon-star">' . qa_lang_html('cleanstrap/featured') . '</span>' : ''), 
			'<span class="q-view-a-count">'.$q_view['raw']['acount'].' '.qa_lang('cleanstrap/answers').'</span>',
				'<span class="icon-eye-open q-view-v-count">' . $q_view['raw']['views'] . ' ' . qa_lang_html('cleanstrap/views') . '</span>
			<a class="cat-in icon-folder-close" href="' . cs_cat_path($q_view['raw']['categorybackpath']) . '">' . $q_view['raw']['categoryname'] . '</a>');
			
		$this->output('</div>');
		
		$this->output('</div>');
		$this->output('</div>');
		
		$pagestate=qa_get_state();
		// this will prevent showing extra sections while Question Edit, close or other action forms
		if (! (substr($pagestate, 0, 5)=='edit-')) {
			$this->output(base64_decode(qa_opt('cs_ads_below_question_title')));
			
			$this->output('<div class="qa-q-view-main">');
			
			if (isset($q_view['main_form_tags']))
				$this->output('<form ' . $q_view['main_form_tags'] . '>'); // form for buttons on question	
			
			$this->output('<div class="q-cont-right">');
			
			$this->output('<div class="qa-q-view-wrap">');
			$this->output('<div class="qa-q-view-inner">');
			$this->output('<div class="clearfix">');
			

			$this->q_view_content($q_view);
			
			$this->output('</div>');
			$this->output('<div class="qa-post-meta">');
			$this->post_meta($q_view, 'qa-q-item');
			if (!empty($q_view['q_tags'])) {
				$this->output('<div class="question-tags">');
				$this->output('<h3 class="tags-label">'.qa_lang('cleanstrap/tags').'</h3>');
				$this->post_tag_list($q_view, 'tags');
				$this->output('</div>');
			}
			$this->output('</div>');
			
			$this->q_view_extra($q_view);
			$this->cs_post_buttons($q_view, true);
			
			$this->output('</div>');
			$this->c_list(@$q_view['c_list'], 'qa-q-view');
			$this->output('</div>');
			if (isset($q_view['main_form_tags'])) {
				$this->form_hidden_elements(@$q_view['buttons_form_hidden']);
				$this->output('</form>');
			}
			$this->output('</div>');
			
			$this->c_form(@$q_view['c_form']);
			
			$this->q_view_follows($q_view);
			$this->q_view_closed($q_view);
			
			$this->output(base64_decode(qa_opt('cs_ads_after_question_content')));
			$this->output('</div> <!-- END qa-q-view-main -->');
		}
	}
	function q_view_follows($q_view)
	{
		if (!empty($q_view['follows']))
			$this->output(
				'<div class="qa-q-view-follows icon-question-sign">',
				$q_view['follows']['label'],
				'<a href="'.$q_view['follows']['url'].'" class="qa-q-view-follows-link">'.$q_view['follows']['title'].'</a>',
				'</div>'
			);
	}
		
	function q_view_closed($q_view)
	{
		if (!empty($q_view['closed'])) {
			$haslink=isset($q_view['closed']['url']);
			
			$this->output(
				'<div class="qa-q-view-closed icon-cross">',
				$q_view['closed']['label'],
				($haslink ? ('<a href="'.$q_view['closed']['url'].'"') : '<span').' class="qa-q-view-closed-content">',
				$q_view['closed']['content'],
				$haslink ? '</a>' : '</span>',
				'</div>'
			);
		}
	}
	function cs_post_buttons($q_view, $show_feat_img=false)
	{
		$this->output('<div class="post-button clearfix">');
		if(isset($q_view['form']['buttons'])){
			$buttons = $q_view['form']['buttons'];
			
			if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && (!empty($q_view)) && $show_feat_img)
				$buttons['featured'] = array(
					'tags' => 'id="set_featured"',
					'label' => !is_featured($q_view['raw']['postid']) ? qa_lang_html('cleanstrap/featured') : qa_lang_html('cleanstrap/unfeatured'),
					'popup' => !is_featured($q_view['raw']['postid']) ? qa_lang_html('cleanstrap/set_featured') : qa_lang_html('cleanstrap/remove_featured'),
					'class' => 'icon-star'
				);
			
			foreach ($buttons as $k => $btn) {
				if ($k == 'edit')
					$btn['class'] = 'icon-edit';
				if ($k == 'flag')
					$btn['class'] = 'icon-flag';
				if ($k == 'unflag')
					$btn['class'] = 'icon-flag';
				if ($k == 'close')
					$btn['class'] = 'icon-cancel';
				if ($k == 'hide')
					$btn['class'] = 'icon-eye-open';
				if ($k == 'answer')
					$btn['class'] = 'icon-answer';
				if ($k == 'comment')
					$btn['class'] = 'icon-chat';
				if ($k == 'follow')
					$btn['class'] = 'icon-add-answer';
				
				$this->output('<button ' . $btn['tags'] . ' class="btn ' . @$btn['class'] . '" title="' . @$btn['popup'] . '" type="submit">' . $btn['label'] . '</button>');
			}
		}
		if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && (!empty($q_view)) && $show_feat_img){
			$this->output('
				<div class="btn-group featured-image-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					'.qa_lang('cleanstrap/featured_image').'<span class="caret"></span>
					</button>
					<div class="dropdown-menu">');
					$this->question_meta_form();
					$this->output('</div>
				</div>
			');
		}
		$this->output('</div>');
	}
	function post_tags($post, $class)
	{
		if (!empty($post['q_tags'])) {
			$this->output('<div class="' . $class . '-tags clearfix">');
			$this->post_tag_list($post, $class);
			$this->output('</div>');
		}
	}
	function c_list($c_list, $class)
		{
			if (!empty($c_list)) {
				$this->output('', '<div class="'.$class.'-c-list"'.(@$c_list['hidden'] ? ' style="display:none;"' : '').' '.@$c_list['tags'].'>');
				$this->output('<div class="comment-count icon-comments">'.count($c_list['cs']).'<span>'.qa_lang('cleanstrap/comments').'</span></div>');
				$this->output('<div class="comment-items">');
				$this->c_list_items($c_list['cs']);
				$this->output('</div>');
				$this->output('</div> <!-- END qa-c-list -->', '');
			}
		}
	function c_list_item($c_item)
	{
		$extraclass = @$c_item['classes'] . (@$c_item['hidden'] ? ' qa-c-item-hidden' : '');
		
		$this->output('<div class="qa-c-list-item ' . $extraclass . '" ' . @$c_item['tags'] . '>');
		$this->output('<div class="asker-avatar">');
		
		if (isset($c_item['raw']['handle']))
			$this->output(cs_get_avatar($c_item['raw']['handle'], 30));
		
		$this->output('</div>');
		$this->output('<div class="qa-c-wrap">');
		$this->cs_comment_buttons($c_item);
		$this->post_meta($c_item, 'qa-c-item');
		$this->c_item_main($c_item);
		$this->output('</div>');
		$this->output('</div> <!-- END qa-c-item -->');
	}
	
	function c_item_main($c_item)
	{
		$this->error(@$c_item['error']);
		
		if (isset($c_item['expand_tags']))
			$this->c_item_expand($c_item);
		elseif (isset($c_item['url']))
			$this->c_item_link($c_item);
		else
			$this->c_item_content($c_item);
	}
	function cs_comment_buttons($c_item)
	{
		if(isset($c_item['form']['buttons'])){
			$buttons = $c_item['form']['buttons'];
			$this->output('<div class="post-button">');
			foreach ($buttons as $k => $btn) {
				if ($k == 'edit')
					$btn['class'] = 'icon-edit';
				if ($k == 'flag')
					$btn['class'] = 'icon-flag';
				if ($k == 'unflag')
					$btn['class'] = 'icon-flag';
				if ($k == 'hide')
					$btn['class'] = 'icon-cancel';
				if ($k == 'reshow')
					$btn['class'] = 'icon-eye-open';
				if ($k == 'comment')
					$btn['class'] = 'icon-answers';
				
				$this->output('<button ' . $btn['tags'] . ' class="btn ' . @$btn['class'] . '" title="' . @$btn['popup'] . '" type="submit">' . @$btn['label'] . '</button>');
			}
			$this->output('</div>');
		}
	}
	function a_list($a_list)
	{
		if (isset($a_list['title']) && (strlen(@$a_list['title']) || strlen(@$a_list['title_tags'])))
				$this->output('<h3 class="answers-label icon-answer">' . @$a_list['title'] . '</h3>');
		
		if (!empty($a_list)) {
			
			$this->output('<div class="qa-a-list' . ($this->list_vote_disabled($a_list['as']) ? ' qa-a-list-vote-disabled' : '') . '" ' . @$a_list['tags'] . '>', '');
		   
			$this->a_list_items($a_list['as']);
			$this->output('</div> <!-- END qa-a-list -->', '');
		}
		$this->page_links();
	}
	function a_list_item($a_item)
	{
		$extraclass = @$a_item['classes'] . ($a_item['hidden'] ? ' qa-a-list-item-hidden' : ($a_item['selected'] ? ' qa-a-list-item-selected' : ''));
		
		$this->output('<div class="qa-a-list-item ' . $extraclass . '" ' . @$a_item['tags'] . '>');
		
		$this->a_item_main($a_item);
		
		$this->output('</div> <!-- END qa-a-list-item -->', '');
	}
	function a_item_main($a_item)
	{
		//$this->output('<div class="big-s-avatar avatar">' . cs_get_avatar($a_item['raw']['handle'], 40) . '</div>');
		
		//$this->output('<div class="q-cont-right">');
		$this->output('<div class="qa-a-item-main">');
		$this->output('<div class="asker-detail clearfix">');
		$this->output('<div class="asker-avatar avatar">' . cs_get_avatar($a_item['raw']['handle'], 40) . '</div>');
		$this->output('
					<div class="user-info no-overflow">
						<h3 class="asker-name">' . cs_name($a_item['raw']['handle']) . '</h3>
						<p class="asker-point">' . implode(' ', $a_item['who']['points']) . ' <span class="title">' . $a_item['who']['level'] . '</span></p>
					</div>');
		
		$this->voting($a_item);
		
		if (isset($a_item['main_form_tags']))
			$this->output('<form ' . $a_item['main_form_tags'] . '>'); // form for buttons on answer
			
			$this->a_selection($a_item);
		if (isset($a_item['main_form_tags'])) {
			$this->form_hidden_elements(@$a_item['buttons_form_hidden']);
			$this->output('</form>');
		}
		
		$this->output('</div>');
		$this->output('<div class="a-item-inner-wrap">');
		
		if (isset($a_item['main_form_tags']))
			$this->output('<form ' . $a_item['main_form_tags'] . '>'); // form for buttons on answer
		
		/* if ($a_item['hidden'])
		$this->output('<div class="qa-a-item-hidden">');
		elseif ($a_item['selected'])
		$this->output('<div class="qa-a-item-selected">'); */
		
		
		$this->output('<div class="a-item-wrap">');
		
		$this->error(@$a_item['error']);
		$this->a_item_content($a_item);
		
		$this->post_meta($a_item, 'qa-a-item');
		//if ($a_item['hidden'] || $a_item['selected'])
		
		
		$this->cs_post_buttons($a_item);
		$this->output('</div>');
		$this->c_list(@$a_item['c_list'], 'qa-a-item');
		if (isset($a_item['main_form_tags'])) {
			$this->form_hidden_elements(@$a_item['buttons_form_hidden']);
			$this->output('</form>');
		}
		
		$this->c_form(@$a_item['c_form']);
		$this->output('</div>');
	   // $this->output('</div>');
		$this->output('</div> <!-- END qa-a-item-main -->');
	}
	function main_part($key, $part)
	{
		$partdiv = ((strpos($key, 'custom') === 0) || (strpos($key, 'form') === 0) || (strpos($key, 'q_list') === 0) || (strpos($key, 'q_view') === 0) || (strpos($key, 'a_form') === 0) || (strpos($key, 'a_list') === 0) || (strpos($key, 'ranking') === 0) || (strpos($key, 'message_list') === 0) || (strpos($key, 'nav_list') === 0));
		
		
		if ($partdiv)
			$this->output('<div class="qa-part-' . strtr($key, '_', '-') . '">'); // to help target CSS to page parts
		
		if (strpos($key, 'custom') === 0)
			$this->output_raw($part);
		
		elseif (strpos($key, 'form') === 0)
			$this->form($part);
		elseif (strpos($key, 'q_list') === 0)
			$this->q_list_and_form($part);
		elseif (strpos($key, 'q_view') === 0)
			$this->q_view($part); 
		elseif (strpos($key, 'a_form')===0)
			$this->a_form($part); 
		elseif (strpos($key, 'a_list') === 0)
			$this->a_list($part);
		elseif (strpos($key, 'ranking') === 0)
			$this->ranking($part);
		elseif (strpos($key, 'message_list') === 0)
			$this->message_list_and_form($part);
		elseif (strpos($key, 'nav_list') === 0) {
			$this->part_title($part);
			$this->nav_list($part['nav'], $part['type'], 1);
		}
		
		if ($partdiv)
			$this->output('</div>');
	}
	
	function profile_page()
	{
		$handle = $this->content['raw']['account']['handle'];
		$userid = $this->content['raw']['account']['userid'];
		$about  = cs_user_profile($handle, 'about');
		$this->output('<div class="user-cols content-sidebar">');
		$this->cs_user_nav($handle);
		$this->output('<div class="user-cols-right">');
		if (isset($about) && strlen($about))
			$this->output('<div class="about-me widget">', '<h3 class="widget-title">' . qa_lang_html('cleanstrap/about_me') . '</h3>', $about, '</div>');
		$this->cs_user_activity_count($handle);
		
		$this->output('<div class="about-me widget">', '<h3 class="widget-title">' . qa_lang_html('cleanstrap/user_profile') . '</h3>');
		qa_html_theme_base::form($this->content['form_profile']);
		$this->output('</div>');
		
		$this->cs_user_qa($handle);
		$this->output('</div>');
		$this->output('</div>');
		
	}
	
	function cs_user_nav($handle)
	{
		$user = cs_user_data($handle);
		
		$this->output('			
			<div class="user-header">
				<div class="user-header-inner clearfix">
			  <div class="user-thumb">
				' . cs_get_avatar($handle, 150) . '
			  </div>
			  <div class="user-name-detail">
				<h3>' . cs_name($handle) . '<small class="block m-t-mini">' . qa_user_level_string($user['level']) . '</small>
				</h3>
				
				');
		
		$this->output('</div>');
	
		$this->cs_user_profile_button($handle);
		
		$this->nav('sub');
		$this->favorite();
		$this->output('</div>');
		$this->output('</div>');
	}
	
	
	function cs_user_activity_count($handle)
	{
		$user = cs_user_data($handle);
		$this->output('<div class="user-activity-count clearfix">', '<div class="points">', $user['points'], '<span>' . qa_lang_html('cleanstrap/points') . '</span>', '</div>', '<div class="counts">', '<div class="a-counts">', '<span>' . $user['aposts'] . '</span>', qa_lang_html('cleanstrap/answers'), '</div>', '<div class="q-counts">', '<span>' . $user['qposts'] . '</span>', qa_lang_html('cleanstrap/questions'), '</div>', '<div class="c-counts">', '<span>' . $user['cposts'] . '</span>', qa_lang_html('cleanstrap/comments'), '</div>', '</div>', '
				<div class="bar-chart">	
					<div class="sparkline" data-type="bar" data-bar-color="#FDAB0C" data-bar-width="20" data-height="28"><!--' . $user['aposts'] . ',' . $user['qposts'] . ',' . $user['cposts'] . '--></div>
					<ul class="list-inline text-muted axis"><li>A</li><li>Q</li><li>C</li></ul>
				</div>
				', '</div>');
	}
	
	function cs_user_qa($handle)
	{
		ob_start();
?>
			<div class="user-qac-list">
				<?php
		$this->cs_position('User Content');
?>
			</div>
			<?php
		//echo '<pre>'; cs_user_activity($handle); echo '</pre>';
		$this->output(ob_get_clean());
	}
	
	function a_form($a_form)
	{
		$this->output('<div class="qa-a-form"' . (isset($a_form['id']) ? (' id="' . $a_form['id'] . '"') : '') .
			(@$a_form['collapse'] ? ' style="display:none;"' : '').'>');
		$this->output('<div class="answer-form">');
		if (isset($a_form)) {
			$this->output('<div class="big-s-avatar avatar">' . cs_get_avatar(qa_get_logged_in_handle(), 40) . '</div>');
			$this->output('<div class="q-cont-right">');
		   $this->output('<h3 class="your-answer">' . $a_form['title'] . '</h3>');

			$this->output('<div class="answer-f-wrap">');
			
			$a_form['title'] = '';
			$this->form($a_form);
			$this->c_list(@$a_form['c_list'], 'qa-a-item');
			$this->output('</div>');
			$this->output('</div>');
		} else {
			$this->output('<div class="login-to-answer">' . $a_form['title'] . '</div>');
		}
		 $this->output('</div>');
		$this->output('</div> <!-- END qa-a-form -->', '');
	}
	
	function favorite()
	{
		$favorite = @$this->content['favorite'];
		
		if (isset($favorite)) {
			$this->output('<div class="fav-parent">');
			$this->favorite_inner_html($favorite);
			$this->output('</div>');
		}
	}
	function favorite_inner_html($favorite)
	{
		$this->favorite_button(@$favorite['favorite_add_tags'], 'icon-heart,' . @$favorite['form_hidden']['code'] . ',');
		$this->favorite_button(@$favorite['favorite_remove_tags'], 'icon-heart active remove,' . @$favorite['form_hidden']['code'] . ',');
	}
	function favorite_button($tags, $class)
	{
		
		if (isset($tags)) {
			if ($this->template == 'user')
				$text = isset($favorite['favorite_add_tags']) ? qa_lang('cleanstrap/follow') : qa_lang('cleanstrap/unfollow');
			$code_icon = explode(',', $class);
			$data	  = str_replace('name', 'data-id', @$tags);
			$data	  = str_replace('onclick="return qa_favorite_click(this);"', '', @$data);
			
			$this->output('<a href="#" ' . @$favorite['favorite_tags'] . ' ' . $data . ' data-code="' . $code_icon[1] . '" class="fav-btn ' . $code_icon[0] . '"><span>' . @$code_icon[2] . '</span></a>');
		}
	}
	
	function c_form($c_form)
	{
		if(@$_POST['qa']=='ajax' or empty($c_form))
			return;

		$pagestate=qa_get_state();
		if(!isset($c_form['collapse']) && empty($pagestate))
			$c_form['collapse'] = true;
		
		$this->output('<div class="qa-c-form"' . (isset($c_form['id']) ? (' id="' . $c_form['id'] . '"') : '') . (@$c_form['collapse'] ? ' style="display:none;"' : '') . '>');
		
		$this->output('<div class="asker-avatar no-radius">');
		$this->output(cs_get_avatar(qa_get_logged_in_handle(), 30));
		$this->output('</div>');
		if (!empty($c_form['title'])) {
			$this->output('<div class="comment-f-wrap">');
			$this->output('<h3>', $c_form['title'], '</h3>');
			$c_form['title'] = '';
			$this->form($c_form);
			$this->output('</div>', '');
		} else
			$this->form($c_form);
		$this->output('</div>', '');
	}
	
	function ranking($ranking)
	{
		$this->part_title($ranking);
		
		$class = (@$ranking['type'] == 'users') ? 'qa-top-users' : 'qa-top-tags';
		$rows  = min($ranking['rows'], count($ranking['items']));
		
		if (@$ranking['type'] == 'users') {
			$this->output('<div class="page-users-list clearfix">');
			
			/* if($ranking['items'])
			$columns=ceil(count($ranking['items'])/$rows); */
			if ($ranking['items'])
				foreach ($ranking['items'] as $user) {
					if (isset($user['raw']))
						$handle = $user['raw']['handle'];
					else
						$handle = ltrim(strip_tags($user['label']));
					
					$data   = cs_user_data($handle);

					$avatar = cs_get_avatar($handle, 150, false);
					$this->output('
							<div class="user-card">
							<div class="user-card-inner">	
								<div class="card-container">
								' . (isset($avatar) ? '
									<div class="f1_card">
										<div class="front face">
											<img class="avatar" height="150" src="' . $avatar . '" />
										</div>
										<div class="back face center">
											<span class="activity q"><i>' . $data['qposts'] . '</i>' . qa_lang_html('cleanstrap/questions') . ' </span>
											<span class="activity a"><i>' . $data['aposts'] . '</i>' . qa_lang_html('cleanstrap/answers') . ' </span>
											<span class="activity c"><i>' . $data['cposts'] . '</i>' . qa_lang_html('cleanstrap/comments') . ' </span>
										</div>
									</div>
									' : '
									<div class="card-metas center">
										<span class="activity q"><i>' . $data['qposts'] . '</i>' . qa_lang_html('cleanstrap/questions') . ' </span>
										<span class="activity a"><i>' . $data['aposts'] . '</i>' . qa_lang_html('cleanstrap/answers') . ' </span>
										<span class="activity c"><i>' . $data['cposts'] . '</i>' . qa_lang_html('cleanstrap/comments') . ' </span>
									</div>													
								') . '
								</div>	
								<div class="card-bottom">
								<a class="user-name" href="' . qa_path_html('user/' . $handle) . '">' . cs_name($handle) . '</a>								
								<span class="score">' . $data['points'] . qa_lang_html('cleanstrap/points') . ' </span>
								</div>');
					if (qa_opt('badge_active') && function_exists('qa_get_badge_list'))
						$this->output('<td class="badge-list">' . cs_user_badge($handle) . '</td>');
					
					$this->output('</div>');
					$this->output('</div>');
				} else
				$this->output('
							<div class="no-items">
								<h3 class="icon-sad">' . qa_lang_html('cleanstrap/no_users') . '</h3>
								<p>' . qa_lang_html('cleanstrap/edit_user_detail') . '</p>
							</div>');
			
			
			$this->output('</div>');
			
		} elseif (@$ranking['type'] == 'tags') {
			
			if ($rows > 0) {
				$this->output('<div class="row ' . $class . '">');
				
				$columns = ceil(count($ranking['items']) / $rows);
				
				for ($column = 0; $column < $columns; $column++) {
					$this->set_context('ranking_column', $column);
					$this->output('<div class="col-lg-' . ceil(12 / $columns) . '">');
					$this->output('<ul>');
					
					for ($row = 0; $row < $rows; $row++) {
						$this->set_context('ranking_row', $row);
						$this->cs_tags_item(@$ranking['items'][$column * $rows + $row], $class, $column > 0);
					}
					
					$this->clear_context('ranking_column');
					
					$this->output('</ul>');
					$this->output('</div>');
				}
				
				$this->clear_context('ranking_row');
				
				$this->output('</div>');
			} else
				$this->output('
					<div class="no-items">
					<h3 class="icon-sad">' . qa_lang('cleanstrap/no_tags') . '</h3>
					<p>' . qa_lang('cleanstrap/no_results_detail') . '</p>
					</div>');
			
		} else {
			
			
			if ($rows > 0) {
				$this->output('<table class="' . $class . '-table">');
				
				$columns = ceil(count($ranking['items']) / $rows);
				
				for ($row = 0; $row < $rows; $row++) {
					$this->set_context('ranking_row', $row);
					$this->output('<tr>');
					
					for ($column = 0; $column < $columns; $column++) {
						$this->set_context('ranking_column', $column);
						$this->ranking_item(@$ranking['items'][$column * $rows + $row], $class, $column > 0);
					}
					
					$this->clear_context('ranking_column');
					
					$this->output('</tr>');
				}
				
				$this->clear_context('ranking_row');
				
				$this->output('</table>');
			} else
				$this->output('
						<div class="no-items">
							<h3 class="icon-sad">' . qa_lang_html('cleanstrap/no_results') . '</h3>
							<p>' . qa_lang_html('cleanstrap/no_results_detail') . '</p>
						</div>');
		}
	}
	function cs_tags_item($item, $class, $spacer)
	{
		if (isset($item))
			$this->output('<li class="list-group-item">' . $item['label'] . '<span>' . $item['count'] . '</span></li>');
	}
	function message_list_form($list)
	{
		if (!empty($list['form'])) {
			$this->output('<div class="qa-message-list-form">');
			$this->output('<div class="asker-avatar no-radius">');
			$this->output(cs_get_avatar(qa_get_logged_in_handle(), 30));
			$this->output('</div>');
			$this->output('<div class="qa-message-list-inner">');
			$this->form($list['form']);
			$this->output('</div>');
			$this->output('</div>');
		}
	}
	
	function message_item($message)
	{
		$this->output('<div class="qa-message-item" ' . @$message['tags'] . '>');
		$this->output('<div class="asker-avatar">');
		$this->output(cs_get_avatar($message['raw']['fromhandle'], 30));
		$this->output('</div>');
		$this->output('<div class="qa-message-item-inner">');
		$this->post_meta($message, 'qa-message');
		$this->message_content($message);
		$this->message_buttons($message);
		$this->output('</div>');
		$this->output('</div> <!-- END qa-message-item -->', '');
	}
	
	function cs_ajax_get_ajax_block()
	{
		$mheight = floor($_REQUEST['height']);
		$height  = $mheight - 600;
		$height  = floor($height / 60);
		
		$this->cs_pie_stats();
		if ($this->template != 'admin' && $height > 0) {
			$height = ($height > 10) ? 10 : $height;
			$this->output('<div class="panel">');
			$this->output('<div class="panel-heading">' . qa_lang_html('cleanstrap/latest_answers') . '</div>');
			cs_post_list('A', $height);
			$this->output('</div>');
		}
		if ($this->template != 'admin' && $mheight > 1360) {
			$height = $mheight - 1360;
			$height = floor($height / 60);
			$height = ($height > 10) ? 10 : $height;
			$this->output('<div class="panel">');
			$this->output('<div class="panel-heading">' . qa_lang_html('cleanstrap/latest_comments') . '</div>');
			cs_post_list('C', $height);
			$this->output('</div>');
		}
		die();
	}
	
	

	
	function nav_list($navigation, $class, $level = null)
	{
	 

		if ($class == 'browse-cat') {
			$row = ceil(count($navigation) / 2);
			$this->output('<div class="category-list-page"><div class="row">');
			if($level < 2)
	$this->output('<div class="col-lg-6"><ul class="page-cat-list">');
   else
	$this->output('<div class="col-lg-12"><ul class="page-cat-list">');

			$index = 0;
			$i = 1;
			foreach ($navigation as $key => $navlink) {
				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				$this->cs_cat_items($key, $navlink, $class, $level);
				if ($row == $i){
					$this->output('</ul></div>');
					 if($level < 2)
	  $this->output('<div class="col-lg-6"><ul class="page-cat-list">');
	 else
	  $this->output('<div class="col-lg-12"><ul class="page-cat-list">');
	}

				$i++;
			}
			
			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			
			$this->output('</ul></div></div></div>');
			
		} else {
			
			$this->output('<ul class="qa-' . $class . '-list' . (isset($level) ? (' qa-' . $class . '-list-' . $level) : '') . '">');
			
			$index = 0;

			foreach ($navigation as $key => $navlink) {
				if (count($navlink)>1){
					$this->set_context('nav_key', $key);
					$this->set_context('nav_index', $index++);
					$this->nav_item($key, $navlink, $class, $level);
				}
			}
			
			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			
			$this->output('</ul>');
		}
	}

	function cs_cat_items($key, $navlink, $class, $level = null)
	{
		$suffix = strtr($key, array( // map special character in navigation key
			'$' => '',
			'/' => '-'
		));
		
		$this->output('<li class="panel ra-cat-item' . (@$navlink['opposite'] ? '-opp' : '') . (@$navlink['state'] ? (' ra-cat-' . $navlink['state']) : '') . ' ra-cat-' . $suffix . '">');
		$this->cs_cat_item($navlink, 'cat');
		if (count(@$navlink['subnav']))
			$this->nav_list($navlink['subnav'], $class, 2);
		
		$this->output('</li>');
	}
	function cs_cat_item($navlink, $class)
	{
		if (isset($navlink['url']))
			$this->output('<h4>' . (strlen(@$navlink['note']) ? '<span>' . cs_url_grabber($navlink['note']) . '</span>' : '') . '<a href="' . $navlink['url'] . '" class="ra-' . $class . '-link' . (@$navlink['selected'] ? (' ra-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' ra-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . (isset($navlink['target']) ? (' target="' . $navlink['target'] . '"') : '') . '>' . (@$navlink['favorited'] ? '<i class="icon-star" title="' . qa_lang_html('cleanstrap/category_favourited') . '"></i>' : '') . $navlink['label'] . '</a>' . '</h4>');
		
		else
			$this->output('<h4 class="ra-' . $class . '-nolink' . (@$navlink['selected'] ? (' ra-' . $class . '-selected') : '') . (@$navlink['favorited'] ? (' ra-' . $class . '-favorited') : '') . '"' . (strlen(@$navlink['popup']) ? (' title="' . $navlink['popup'] . '"') : '') . '>' . (strlen(@$navlink['note']) ? '<span>' . cs_url_grabber($navlink['note']) . '</span>' : '') . (@$navlink['favorited'] ? '<i class="icon-star" title="' . qa_lang_html('cleanstrap/category_favourited') . '"></i>' : '') . $navlink['label'] . '</h4>');
		
		if (strlen(@$navlink['note']))
			$this->output('<span class="ra-' . $class . '-note">' . str_replace('-', '', preg_replace('/<a[^>]*>(.*)<\/a>/iU', '', $navlink['note'])) . '</span>');
	}
	
	function a_selection($post)
	{
		$this->output('<div class="qa-a-selection">');
		
		if (isset($post['select_tags']))
			$this->cs_hover_button($post, 'select_tags', qa_lang('cleanstrap/select_answer'), 'icon-checkmark3 qa-a-select');
		elseif (isset($post['unselect_tags']))
			$this->post_hover_button($post, 'unselect_tags', @$post['select_text'], 'qa-a-unselect');
		elseif ($post['selected'])
			$this->output('<div class="qa-a-selected">' . @$post['select_text'] . '</div>');
		
		
		$this->output('</div>');
	}
	function cs_hover_button($post, $element, $value, $class)
	{
		if (isset($post[$element]))
			$this->output('<button ' . $post[$element] . ' type="submit" class="' . $class . '-button">'.$value.'</button>');
	}
	
	function cs_position($position)
	{
		$widgets		 = $this->widgets;
		$position_active = multi_array_key_exists($position, $widgets);
		
		if (isset($widgets) && $position_active && $this->cs_position_active($position)) {
			$this->output('<div id="' . str_replace(' ', '-', strtolower($position)) . '-position">');
			foreach ($widgets as $w) {
				
				if (($w['position'] == $position) && isset($w['param']['locations'][$this->template]) && (bool) $w['param']['locations'][$this->template]) {
					$this->current_widget = $w;
					$this->cs_get_widget($w['name'], @$w['param']['locations']['show_title'], $position);
				}
			}
			$this->output('</div>');
		}
	}
	
	function cs_get_widget($name, $show_title = false, $position)
	{
		
		$module = qa_load_module('widget', ltrim($name));
		if (is_object($module)) {
			ob_start();
			echo '<div class="widget '.strtolower(str_replace(' ', '_', $name)).'">';
			$module->output_widget('side', 'top', $this, $this->template, $this->request, $this->content);  
			echo '</div>';
			$this->output(ob_get_clean());
		}
		return;
	}
	
	
	function cs_ajax_get_question_suggestion()
	{
		$query			= strip_tags($_REQUEST['start_with']);
		$relatedquestions = qa_db_select_with_pending(qa_db_search_posts_selectspec(null, qa_string_to_words($query), null, null, null, null, 0, false, 10));
		//print_r($relatedquestions);
		
		if (isset($relatedquestions) && !empty($relatedquestions)) {
			$data = array();
			foreach ($relatedquestions as $k => $q) {
				$data[$k]['title']   = $q['title'];
				$data[$k]['blob']	= cs_get_avatar($q['handle'], 30, false);
				$data[$k]['url']	 = qa_q_path_html($q['postid'], $q['title']);
				$data[$k]['tags']	= $q['tags'];
				$data[$k]['answers'] = $q['acount'];
			}
			echo json_encode($data);
		}
		
		die();
	}
	function q_list($q_list)
	{
		if (isset($q_list['qs'])) {
			if (qa_opt('cs_enable_except')) { // first check it is not an empty list and the feature is turned on
				//	Collect the question ids of all items in the question list (so we can do this in one DB query)
				$postids = array();
				foreach ($q_list['qs'] as $question)
					if (isset($question['raw']['postid']))
						$postids[] = $question['raw']['postid'];
				if (count($postids)) {
					//	Retrieve the content for these questions from the database and put into an array
					//$result		 = qa_db_query_sub('SELECT postid, content, format FROM ^posts WHERE postid IN (#)', $postids);
					//$postinfo	   = qa_db_read_all_assoc($result, 'postid');
					//cache and apply keys to array now that I can't use array key argument in qa_db_read_all_assoc
					$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT postid, content, format FROM ^posts WHERE postid IN (#)',$postids));
					$postinfo= array();
					foreach ($posts as $qitem) {
						$postinfo[$qitem['postid']] = $qitem;
					}
					//	Get the regular expression fragment to use for blocked words and the maximum length of content to show
					$blockwordspreg = qa_get_block_words_preg();
					$maxlength	  = qa_opt('cs_except_len');
					//	Now add the popup to the title for each question
					foreach ($q_list['qs'] as $index => $question) {
						$thispost = @$postinfo[$question['raw']['postid']];
						if (isset($thispost)) {
							$text							= qa_viewer_text($thispost['content'], $thispost['format'], array(
								'blockwordspreg' => $blockwordspreg
							));
							$text							= qa_shorten_string_line($text, $maxlength);
							$q_list['qs'][$index]['content'] = '<SPAN>' . qa_html($text) . '</SPAN>';
						}
					}
				}
			}
			$this->output('<div class="qa-q-list' . ($this->list_vote_disabled($q_list['qs']) ? ' qa-q-list-vote-disabled' : '') . ((bool) qa_opt('cs_enable_clean_qlist') ? ' clean' : '') . '">', '');
			$this->q_list_items($q_list['qs']);
			$this->output('</div> <!-- END qa-q-list -->', '');
		} else
			$this->output('
					<div class="no-items">
						<h3 class="icon-sad">' . qa_lang_html('cleanstrap/no_users') . '</h3>
						<p>' . qa_lang_html('cleanstrap/no_results_detail') . '.</p>
					</div>');
	}
	function q_list_items($q_items)
	{
		if (!empty($q_items)) {
			foreach ($q_items as $q_item)
				$this->q_list_item($q_item);
		}
	}
	
	function question_meta_form()
	{
		
		$postid = @$this->content["q_view"]["raw"]["postid"];
		if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && (!empty($postid))) {
			require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
			$featured_image_name = qa_db_postmeta_get($postid, 'featured_image');
			
			$this->output('
					<div class="question-image" id="question-meta">
						<btn data-args="' . qa_get_form_security_code('delete-image') . '_' . $postid . '" ' . ((isset( $featured_image_name ))? '':'style="display:none;"') . ' id="q_meta_remove_featured_image" class="qa-form-light-button qa-form-light-button-features " title="Remove featured image" type="submit" name="q_meta_remove_featured_image">' . qa_lang_html('cleanstrap/delete') . '</btn>
						<div id="fileuploader">' . qa_lang_html('cleanstrap/upload') . '</div>
					
					</div>
				');
		}
	}
	function cs_ajax_save_q_meta()
	{
		require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
		$postid = @$this->content["q_view"]["raw"]["postid"];
		@$featured_image = $_REQUEST['featured_image'];
		
		if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)) {
			if (!empty($featured_image)) {
				qa_db_postmeta_set($postid, 'featured_image', $featured_image);
			} else
				qa_db_postmeta_clear($postid, 'featured_image');
			
		}
		die(Q_THEME_URL . '/uploads/' . $featured_image);
	}
	function cs_position_active($name)
	{
		$widgets  = $this->widgets;
		$template = $this->template;
		$template = (!empty($this->request) ? $template : 'home');
		if (isset($widgets) && is_array($widgets)) {
			foreach ($widgets as $w) {
				
				if (($w['position'] == $name) && isset($w['param']['locations'][$template]) && (bool) $w['param']['locations'][$template])
					return true;
			}
			
		}
		return false;
	}
	
	function cs_ajax_set_question_featured()
	{
		require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
		$postid = @$this->content["q_view"]["raw"]["postid"];
		
		if (($this->template == 'question') && (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN)) {
			if (!is_featured($postid))
				qa_db_postmeta_set($postid, 'featured_question', true);
			else
				qa_db_postmeta_clear($postid, 'featured_question');
		}
		die();
	}
	function cs_ajax_delete_featured_image()
	{
		$args = strip_tags($_REQUEST['args']);
		$args = explode('_', $args);
		print_r($args);
		if ((qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) && isset($args) && qa_check_form_security_code('delete-image', $args[0])) {
			require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
			$img = qa_db_postmeta_get($args[1], 'featured_image');
			
			if (!empty($img)) {
				$thumb_img = preg_replace('/(\.[^.]+)$/', sprintf('%s$1', '_s'), $img);
				$thumb	 = Q_THEME_DIR . '/uploads/' . $thumb_img;
				
				$big_img = Q_THEME_DIR . '/uploads/' . $img;
				qa_db_postmeta_clear($args[1], 'featured_image');
				if (file_exists($big_img))
					unlink($big_img);
				
				if (file_exists($thumb))
					unlink($thumb);
				
				
			}
		}
		
		die();
	}

	function body_hidden()
	{
		if(qa_opt('cs_styling_rtl'))
			$this->output('<div style="position:absolute; left:9999px; bottom:9999px;">');
		else
			$this->output('<div style="position:absolute; left:-9999px; top:-9999px;">');
			
		$this->waiting_template();
		$this->output('</div>');
	}

	function cs_user_profile_button($handle){
		
		$this->output('<div class="user-personal-links clearfix">');
		if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
			
			$form = @$this->content['form_profile'];
			
			if(isset($form) && !qw_is_state_edit() && $this->template != 'account'){
				$this->output('<form class="user-buttons" '.$form['tags'].'>');
				if(isset($form['buttons']) && !empty($form['buttons']) && is_array($form['buttons'])){
					foreach($form['buttons'] as $k => $btn)
					$this->output('<button class="btn '.$k .($k == 'delete' ? ' btn-danger' : '' ).'" ' . $btn['tags'] . ' type="submit">' . $btn['label'] . '</button>');
				}
				$this->output('<input type="hidden" name="code" value="'.@$form['hidden']['code'].'" />');
				$this->output('</form>');
			}
		}
			
		$this->output('</div>');
	}
	
}


/*
Omit PHP closing tag to help avoid accidental output
*/
