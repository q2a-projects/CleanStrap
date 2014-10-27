<?php
	class cs_ticker_widget {
		
		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_ticker_count' => array(
						'label' => 'Questions to show',
						'type' => 'number',
						'tags' => 'name="cs_ticker_count"',
						'value' => '10',
					),
					'cs_ticker_data' => array(
						'label' => 'Data from',
						'type' => 'select',
						'tags' => 'name="cs_ticker_data"',
						'value' => 'Category',
						'options' => array(
							'Category' => 'Category',
							'Tags' => 'Tags',
							'Keyword' => 'Keyword',
						)
					),
					'cs_ticker_slug' => array(
						'label' => 'Enter slug(Keyword)',
						'type' => 'text',
						'tags' => 'name="cs_ticker_slug"',
					),
					'avatar_size' => array(
						'label' => 'Avatar Size',
						'type' => 'number',
						'tags' => 'name="avatar_size"',
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
		function cs_relative_post_list($limit, $slug, $type, $return = false, $avatar_size){
			require_once QA_INCLUDE_DIR.'qa-app-posts.php';
			if(!empty($slug)){
				if($type=='Category'){
					$post_type='Q';
					$categories = explode("/", $slug);
					if (count($categories)){
						$category_bread_crup = implode(" > ", $categories);
						$category_link = implode("/", $categories);
						$categories = array_reverse($categories);
						$slug = implode("/", $categories);
					}
					$posts = qa_db_read_all_assoc(qa_db_query_sub(
							'SELECT * FROM ^posts WHERE ^posts.type=$
							AND categoryid=(SELECT categoryid FROM ^categories WHERE ^categories.backpath=$ LIMIT 1) 
							ORDER BY ^posts.created DESC LIMIT #',
							'Q', $slug, $limit)); //refresh every 15 minutes
					$title = 'Questions in <a href="'.qa_path_html('questions/'.qa_strtolower($category_link)).'">'.$category_bread_crup.'</a>';
				}elseif($type=='Tags'){
					$post_type='Q';
					$title = 'Questions in <a href="'.qa_path_html('tag/'.qa_strtolower($slug)).'">'.$slug.'</a>';
					$posts = qa_db_read_all_assoc(qa_db_query_sub(
						'SELECT * FROM ^posts WHERE ^posts.type=$
						AND ^posts.postid IN (SELECT postid FROM ^posttags WHERE 
							wordid=(SELECT wordid FROM ^words WHERE word=$ OR word=$ COLLATE utf8_bin LIMIT 1) ORDER BY postcreated DESC)
						ORDER BY ^posts.created DESC LIMIT #',
						'Q', $slug, qa_strtolower($slug), $limit));
				}else{ // Relative to Keyword
					require_once QA_INCLUDE_DIR.'qa-app-search.php';
					$keyword=$slug;
					$userid = qa_get_logged_in_userid();
					$title = 'Posts About <a href="'.qa_path_html('search/'.qa_strtolower($keyword)).'">'.$keyword.'</a>';
					//$post=qa_get_search_results($keyword, 0, $limit, $userid , false, false);
					$words=qa_string_to_words($keyword);
					$posts=qa_db_select_with_pending(qa_db_search_posts_selectspec($userid, $words, $words, $words, $words, trim($keyword), 0, false, $limit));

					$output = '<h3 class="widget-title">'.$title.'</h3>';
					$output .= '<ul class="question-list">';
					foreach ($posts as $post) {
						$post_type = $post['type'];
						if($post_type=='Q'){
							$what = qa_lang('cleanstrap/asked');
						}elseif($post_type=='A'){
							$what = qa_lang('cleanstrap/answered');
						}elseif('C'){
							$what = qa_lang('cleanstrap/commented');
						}
						$handle = qa_post_userid_to_handle($post['userid']);
						$avatar = cs_get_post_avatar($post, $avatar_size);
						$output .= '<li id="q-list-'.$post['postid'].'" class="question-item">';
						$output .= '<div class="pull-left avatar" data-handle="'.$handle.'" data-id="'. $post['userid'] .'">' . $avatar . '</div>';
						$output .= '<div class="list-right">';
						if($post_type=='Q'){
							$output .= '<a class="title" href="'. qa_q_path_html($post['postid'], $post['title']) .'" title="'. $post['title'] .'">'.cs_truncate(strip_tags($post['title']), 70).'</a>';
						}elseif($post_type=='A'){
							$output .= '<p><a href="'.cs_post_link($post['parentid']).'#a'.$post['postid'].'">'. cs_truncate(strip_tags($post['content']),70).'</a></p>';
						}else{
							$output .= '<p><a href="'.cs_post_link($post['parentid']).'#c'.$post['postid'].'">'. cs_truncate(strip_tags($post['content']),70).'</a></p>';
						}
						$output .= '<div class="meta"><a href="'.qa_path_html('user/'.$handle).'">'.cs_name($handle).'</a> '.$what;
						if ($post_type=='Q'){
							$output .= ' <span class="vote-count">'.$post['netvotes'].' votes</span>';
							$output .= ' <span class="ans-count">'.$post['acount'].' ans</span>';
						}elseif($post_type=='A'){
							$output .= ' <span class="vote-count">'.$post['netvotes'].' votes</span>';
						}
						$output .= '</div></div>';	
						$output .= '</li>';	
					}
					$output .= '</ul>';
					if($return)
						return $output;
					echo $output;
					return;
				}
			}
			else
				return;

			$output = '<h3 class="widget-title">'.$title.'</h3>';
			
			$output .= '<ul class="question-list">';
			foreach($posts as $p){
				if (empty($p['userid'])) $p['userid']=NULL; // to prevent error for anonymous posts while calling qa_post_userid_to_handle()
				if($post_type=='Q'){
					$what = qa_lang_html('cleanstrap/asked');
				}elseif($post_type=='A'){
					$what = qa_lang_html('cleanstrap/answered');
				}elseif('C'){
					$what = qa_lang_html('cleanstrap/commented');
				}
				$handle = qa_post_userid_to_handle($p['userid']);
				$avatar = cs_get_avatar($handle, 35, false);
				$output .= '<li id="q-list-'.$p['postid'].'" class="question-item">';
				$output .= '<div class="pull-left avatar" data-handle="'.$handle.'" data-id="'. qa_handle_to_userid($handle).'">' . (isset($avatar)?'<img src="'. $avatar .'" />':'') . '</div>';
				$output .= '<div class="list-right">';

				if($post_type=='Q'){

					$output .= '<a class="title" href="'. qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'] .'">'.cs_truncate(qa_html($p['title']), 70).'</a>';

				}elseif($post_type=='A'){

					$output .= '<p><a href="'.cs_post_link($p['parentid']).'#a'.$p['postid'].'">'. cs_truncate(strip_tags($p['content']),70).'</a></p>';

				}else{

					$output .= '<p><a href="'.cs_post_link($p['parentid']).'#c'.$p['postid'].'">'. cs_truncate(strip_tags($p['content']),70).'</a></p>';

				}
				$output .= '<div class="meta"><a href="'.qa_path_html('user/'.$handle).'">'.cs_name($handle).'</a> '.$what;
				if ($post_type=='Q'){

					$output .= ' <span class="vote-count">'.$p['netvotes'].' votes</span>';

					$output .= ' <span class="ans-count">'.$p['acount'].' ans</span>';

				}elseif($post_type=='A'){
					$output .= ' <span class="vote-count">'.$p['netvotes'].' votes</span>';
				}
				$output .= '</div></div>';	

				$output .= '</li>';
			}

			$output .= '</ul>';

			if($return)

				return $output;

			echo $output;

		}
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = $themeobject->current_widget['param']['options'];

			$count = (isset($widget_opt['cs_ticker_count']) && !empty($widget_opt['cs_ticker_count'])) ?(int)$widget_opt['cs_ticker_count'] : 10;
			
			$slug = @$widget_opt['cs_ticker_slug'];
			$avatar_size = @$widget_opt['avatar_size'];
			
			$type = (isset($widget_opt['cs_ticker_data'])) ? $widget_opt['cs_ticker_data'] : 'Keyword';
			
			$themeobject->output('<div class="ra-ticker-widget">');
			
			if(isset($slug))
				$themeobject->output($this->cs_relative_post_list($count, $slug, $type, true, $avatar_size));
			else
				$themeobject->output('<p>'.qa_lang('cleanstrap/tag_slug_empty').'</p>');
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/