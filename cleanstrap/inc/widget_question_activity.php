<?php
	class cs_question_activity_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_qa_count' => array(
						'label' => 'Numbers of questions',
						'type' => 'number',
						'tags' => 'name="cs_qa_count"',
						'value' => '10',
					)
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
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = @$themeobject->current_widget['param']['options'];
			require_once QA_INCLUDE_DIR.'qa-db-selects.php';
			require_once QA_INCLUDE_DIR.'qa-app-format.php';
			require_once QA_INCLUDE_DIR.'qa-app-q-list.php';
			
			$categoryslugs='';
			$userid=qa_get_logged_in_userid();


		//	Get lists of recent activity in all its forms, plus category information
			
			list($questions1, $questions2, $questions3, $questions4)=qa_db_select_with_pending(
				qa_db_qs_selectspec($userid, 'created', 0, $categoryslugs, null, false, false, $qcount),
				qa_db_recent_a_qs_selectspec($userid, 0, $categoryslugs),
				qa_db_recent_c_qs_selectspec($userid, 0, $categoryslugs),
				qa_db_recent_edit_qs_selectspec($userid, 0, $categoryslugs)
			);
			
		//	Prepare and return content for theme
			$content =  qa_q_list_page_content(
				qa_any_sort_and_dedupe(array_merge($questions1, $questions2, $questions3, $questions4)), // questions
				$qcount, // questions per page
				0, // start offset
				null, // total count (null to hide page links)
				null, // title if some questions
				null, // title if no questions
				null, // categories for navigation
				null, // selected category id
				true, // show question counts in category navigation
				'activity/', // prefix for links in category navigation
				null, // prefix for RSS feed paths (null to hide)
				null, // suggest what to do next
				null, // page link params
				null // category nav params
			);
			$content= $content['q_list']['qs'];
			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang('cleanstrap/recent_activities').' <a href="'.qa_path_html('activity').'">'.qa_lang('cleanstrap/view_all').'</a></h3>');

			$themeobject->output('<div class="ra-question-activity-widget">');

			$q_list = $content;
			
			$themeobject->output('<ul class="activity-list">');
			foreach ($q_list as $list){
				$themeobject->output('<li class="clearfix '.(is_featured($list['raw']['postid']) ? ' featured' : '').'"><span class="fav-star icon-heart'.(@$list['raw']['userfavoriteq'] ? ' active' : '').'"></span><span class="post-status-c">'.cs_post_status($list).'</span><a href="'.$list['url'].'">'.$list['title'].'<span class="time">'.implode(' ', $list['when']).'</span><span class="ans-count total-'.$list['raw']['acount'].'">'.$list['raw']['acount'].'</span></a></li>');
			}
			$themeobject->output('</ul>');
			$themeobject->output('</div>');
		}
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/