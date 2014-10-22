<?php
/* don't allow this page to be requested directly from browser */
if (!defined('QA_VERSION')) {
    header('Location: /');
    exit;
}

/* User menu login, logout in navbar */
$context = $this; 
$user_link = $context->content['navigation']['user'];
if (qa_is_logged_in()) {
    ?>
    <ul class="nav-userbox nav pull-right">
        <li id="user-nag">
            <a class="profile" href="<?php echo qa_path_html('user/' . qa_get_logged_in_handle()); ?>">

                <img src="<?php //echo cs_get_avatar(qa_get_logged_in_handle(), 20, false); ?>" />	
                <span><?php //echo $handle; ?></span>
                <span class="points"><?php echo qa_get_logged_in_points(); ?></span>
            </a>
        </li>
        <li class="dropdown" id="menuLogin">
            <a class="dropdown-toggle user-tools" data-toggle="dropdown" href="#">
                <i class="icon-cog"></i>
            </a>

            <div class="dropdown-menu">				
                <ul class="user-nav">
                    <li><a class="icon-profile" href="<?php echo qa_path_html('user/' . qa_get_logged_in_handle()); ?>"><?php qa_lang('Profile'); ?></a></li>
                    <?php
                    foreach ($context->content['navigation']['user'] as $a) {
                        if (isset($a['url'])) {
                            $icon = (isset($a['icon']) ? ' class="' . $a['icon'] . '" ' : '');
                            echo '<li' . (isset($a['selected']) ? ' class="active"' : '') . '><a' . $icon . ' href="' . @$a['url'] . '" title="' . @$a['label'] . '">' . @$a['label'] . '</a></li>';
                        }
                    }
                    if (!isset($context->content['navigation']['user']['logout']['url'])) {
//                        echo '<li>' . $context->content['navigation']['user']['logout']['label'] . '</li>';
                        //this is changed 
                        $link = qa_opt('site_url')."logout";
                        echo "<li><a class='icon-switch' href = '$link'> Logout </a></li>";
                    }
                    ?>
                </ul>
            </div>
        </li>
    </ul>


<?php } else { ?>
        <div class="navbar-form navbar-right">
                        <a class="btn btn-success" href="<?php echo $user_link['register']['url']; ?>" title="<?php echo $user_link['register']['label']; ?>"><?php echo $user_link['register']['label']; ?></a>
                        <a class="btn btn-success dropdown-toggle"  href="#" data-toggle="dropdown" id="navLogin" >  <?php qa_lang('Login'); ?>  </a>
                        <div class="dropdown-menu login-form keep_open">
                            <form id="loginform" class="keep_open" role="form" action="<?php echo $user_link['login']['url']; ?>" method="post">
                                <input type="text" class="form-control" id="qa-userid" name="emailhandle" placeholder="<?php echo trim(qa_lang_html('users/email_handle_label'), ':'); ?>" />
                                <input type="password" class="form-control" id="qa-password" name="password" placeholder="<?php echo trim(qa_lang_html('users/password_label'), ':'); ?>" />
                                <label class="checkbox inline">
                                    <input type="checkbox" name="remember" id="qa-rememberme" value="1"> <?php echo qa_lang_html('users/remember'); ?>
                                </label>
                                <input type="hidden" name="code" value="<?php echo qa_html(qa_get_form_security_code('login')); ?>"/>
                                <input type="submit" value="<?php echo $user_link['login']['label']; ?>" id="qa-login" name="dologin" class="btn btn-primary btn-block" />
                            </form>					
                            <?php
                            foreach ($context->content['navigation']['user'] as $k => $custom) {
                                if (isset($custom) && (($k != 'login') && ($k != 'register'))) 
                                    $context->output('<div class="btn btn-primary btn-block">' . $custom['label'] . '</div>');
                            }
                            ?>	

                        </div>	
                            <script type="text/javascript">

                                $(document).ready(function() {
                                    $(document).on('click', '.dropdown-menu', function (e) {
                                        $(this).hasClass('keep_open') && e.stopPropagation(); // This replace if conditional.
                                    }); 
                                });
                              </script>
        </div>
<?php } ?>