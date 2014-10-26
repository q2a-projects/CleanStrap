<?php
class cs_widget_posts
{
    
    function cs_widget_form()
    {
        
        return array(
            'style' => 'wide',
            'fields' => array(
                'cs_qa_count' => array(
                    'label' => 'Numbers of questions',
                    'type' => 'number',
                    'tags' => 'name="cs_qa_count"',
                    'value' => '10'
                ),
				'post_type' => array(
                    'label' 	=> 'Post Type',
                    'type' 		=> 'select',
                    'tags' 		=> 'name="post_type"',
                    'options' 	=> array('Q' => 'Questions','A' => 'Answers','C' => 'Comments')
                )
            )
            
        );
    }
    
    
    function allow_template($template)
    {
        $allow = false;
        
        switch ($template) {
            case 'activity':
            case 'qa':
            case 'questions':
            case 'hot':
            case 'ask':
            case 'categories':
            case 'question':
            case 'tag':
            case 'tags':
            case 'unanswered':
            case 'user':
            case 'users':
            case 'search':
            case 'admin':
            case 'custom':
                $allow = true;
                break;
        }
        
        return $allow;
    }
    
    
    function allow_region($region)
    {
        $allow = false;
        
        switch ($region) {
            case 'main':
            case 'side':
            case 'full':
                $allow = true;
                break;
        }
        
        return $allow;
    }
    function cs_post_list($type, $limit, $return = false)
    {

        $posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT ^posts.* , ^users.* FROM ^posts, ^users WHERE ^posts.userid=^users.userid AND ^posts.type=$ ORDER BY ^posts.created DESC LIMIT #',$type, $limit));
        
        $output = '<ul class="posts-list">';
        foreach($posts as $p) {

            if ($type == 'Q') {
                $what = qa_lang_html('cleanstrap/asked');
            } elseif ($type == 'A') {
                $what = qa_lang_html('cleanstrap/answered');
            } elseif ('C') {
                $what = qa_lang_html('cleanstrap/commented');
            }

            $handle = $p['handle'];

			$timeCode = qa_when_to_html(  strtotime( $p['created'] ) ,7);
			$when = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];

            $output .= '<li>';
            $output .= cs_get_post_avatar($p, $p['userid'], 30, true);
            $output .= '<div class="post-content">';
            
            if ($type == 'Q') {
                $output .= '<a class="title question" href="' . qa_q_path_html($p['postid'], $p['title']) . '" title="' . $p['title'] . '">' . qa_html($p['title']) . '</a>';
            } elseif ($type == 'A') {
                $output .= '<a class="title" href="' . cs_post_link($p['parentid']) . '#a' . $p['postid'] . '">' . cs_truncate(strip_tags($p['content']),100) . '</a>';
            } else {
                $output .= '<a class="title" href="' . cs_post_link($p['parentid']) . '#c' . $p['postid'] . '">' . cs_truncate(strip_tags($p['content']),100) . '</a>';
            }
            
            $output .= '<div class="meta">';
			//$output .= '<span><a href="' . qa_path_html('user/' . $handle) . '">' . cs_name($handle) . '</a> ' . $what . '</span>';
            
			if ($type == 'Q')
                $output .= '<span>' . qa_lang_sub('cleanstrap/x_answers', $p['acount']) . '</span>';
            
            $output .= '<span class="time icon-time">' .  $when . '</span>';
            $output .= '<span class="vote-count icon-thumbs-up2">' . qa_lang_sub('cleanstrap/x_votes', $p['netvotes']) . '</span>';
            
			
			$output .= '</div>';
            
            
            $output .= '</div>';
            $output .= '</li>';
        }
        $output .= '</ul>';
        if ($return)
            return $output;
        echo $output;
    }
    function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
        $widget_opt = @$themeobject->current_widget['param']['options'];

		if ($widget_opt['post_type'] == 'Q') {
			$what = qa_lang_html('cleanstrap/questions');
		} elseif ($widget_opt['post_type'] == 'A') {
			$what = qa_lang_html('cleanstrap/answers');
		} elseif ($widget_opt['post_type'] == 'C') {
			$what = qa_lang_html('cleanstrap/comments');
		}
        if (@$themeobject->current_widget['param']['locations']['show_title'])
            $themeobject->output('<h3 class="widget-title">' . qa_lang_sub('cleanstrap/recent_posts', $what) . '</h3>');
        
        $themeobject->output('<div class="ra-post-list-widget">');
        $themeobject->output($this->cs_post_list($widget_opt['post_type'], (int)$widget_opt['cs_qa_count']));
        $themeobject->output('</div>');
    }
}
/*
Omit PHP closing tag to help avoid accidental output
*/