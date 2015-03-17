<?php
	class cs_user_posts_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_up_count' => array(
						'label' => 'Numbers of post',
						'type' => 'number',
						'tags' => 'name="cs_up_count"',
						'value' => '10',
					),
					'cs_up_type' => array(
						'label' => 'Numbers of Questions',
						'type' => 'select',
						'tags' => 'name="cs_up_type"',
						'value' => array('Q' => 'Questions'),
						'options' => array(
							'Q' => 'Questions',
							'A' => 'Answers',
							'C' => 'Comments',
						)
					),
				),

			);
		}

		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
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
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				case 'main':
				case 'side':
				case 'full':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		// output the list of selected post type
		function cs_user_post_list($handle, $type, $limit){
			$userid = qa_handle_to_userid($handle);
			require_once QA_INCLUDE_DIR.'qa-app-posts.php';
			$post = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^posts INNER JOIN ^users ON ^posts.userid=^users.userid WHERE ^posts.type=$ and ^posts.userid=# ORDER BY ^posts.created DESC LIMIT #',$type, $userid, $limit));	
			
			$output = '<ul class="question-list users-post-widget post-type-'.$type.'">';
			
			if(count($post) > 0){
				foreach($post as $p){
				
					if($type=='Q'){
						$what = qa_lang_html('cleanstrap/asked');
					}elseif($type=='A'){
						$what = qa_lang_html('cleanstrap/answered');
					}elseif('C'){
						$what = qa_lang_html('cleanstrap/commented');
					}
					$handle = $p['handle'];

					$output .= '<li id="q-list-'.$p['postid'].'" class="question-item">';
					if ($type=='Q'){
						$output .= '<div class="big-ans-count pull-left">'.$p['acount'].'<span>'.qa_lang_html('cleanstrap/ans').'</span></div>';
					}elseif($type=='A'){
						$output .= '<div class="big-ans-count pull-left icon-answer"></div>';
					}elseif($type=='C'){
						$output .= '<div class="big-ans-count pull-left icon-comment icon-comments"></div>';
					}
					$output .= '<div class="list-right">';
					$timeCode = qa_when_to_html(  strtotime( $p['created'] ) ,7);
					$when = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
					if($type=='Q'){
						$output .= '<h5><a href="'. qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'] .'">'.qa_html($p['title']).'</a></h5>';
					}elseif($type=='A'){
						$output .= '<h5><a href="'.cs_post_link($p['parentid']).'#a'.$p['postid'].'">'. cs_truncate(strip_tags($p['content']), 300).'</a></h5>';
					}else{
						$output .= '<h5><a href="'.cs_post_link($p['parentid']).'#c'.$p['postid'].'">'. cs_truncate(strip_tags($p['content']), 300).'</a></h5>';
					}
					
					$output .= '<div class="list-date"><span class="icon-clock">'.$when.'</span>';	
					$output .= '<span class="icon-thumbs-up2">'.qa_lang_sub('cleanstrap/x_votes', $p['netvotes']).'</span></div>';	
					$output .= '</div>';	
					$output .= '</li>';
				}
			}else{
				if($type=='Q'){
					$what = 'questions';
				}elseif($type=='A'){
					$what = 'answers';
				}elseif('C'){
					$what = 'comments';
				}
				$output .= '<li class="no-post-found">' . qa_lang('cleanstrap/no_'.$what) .' </li>';
			}
			$output .= '</ul>';
			echo $output;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = @$themeobject->current_widget['param']['options'];
			$handle = $qa_content['raw']['account']['handle'];
			
			if($widget_opt['cs_up_type'] == 'Q'){
				$url = 'questions';
				$type_title = qa_lang_sub('cleanstrap/users_questions', $handle);
			}elseif($widget_opt['cs_up_type'] == 'A'){
				$url = 'answers';
				$type_title = qa_lang_sub('cleanstrap/users_answers', $handle);
			}else{
				$url = 'comments';
				$type_title = qa_lang_sub('cleanstrap/users_comments', $handle);
			}
				
			if($widget_opt['cs_up_type'] != 'C')
				$type_link = '<a class="see-all" href="'.qa_path_html('user/'.$handle.'/'.$url).'">' . qa_lang_html('cleanstrap/show_all') . '</a>';
			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title user-post-title">' . $type_title.@$type_link.'</h3>');
				
			$themeobject->output('<div class="ra-ua-widget">');
			$themeobject->output($this->cs_user_post_list($handle, @$widget_opt['cs_up_type'],  (int)$widget_opt['cs_up_count']));
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/