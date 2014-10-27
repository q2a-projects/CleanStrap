<?php
	class cs_related_questions {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'count' => array(
						'label' => 'Numbers of questions',
						'type' => 'number',
						'tags' => 'name="count"',
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
				case 'questions':
					return true;
					break;
			}
			
			return false;
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
			
			if (@$qa_content['q_view']['raw']['type']!='Q') // question might not be visible, etc...
				return;
				
			$questionid=$qa_content['q_view']['raw']['postid'];
			
			$userid=qa_get_logged_in_userid();
			$cookieid=qa_cookie_get();
			
			$questions=qa_db_single_select(qa_db_related_qs_selectspec($userid, $questionid, (int)$widget_opt['count']));
				
			$minscore=qa_match_to_min_score(qa_opt('match_related_qs'));
			
			foreach ($questions as $key => $question)
				if ($question['score']<$minscore) 
					unset($questions[$key]);

			$titlehtml=qa_lang_html(count($questions) ? 'main/related_qs_title' : 'main/no_related_qs_title');
			

			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang('cleanstrap/related_questions').'</h3>');
			
			$themeobject->output('<div class="ra-rq-widget">');
				$themeobject->output('<ul>');

				foreach ($questions as $p){
					$timeCode = qa_when_to_html(  $p['created']  ,7);
					$when = @$timeCode['prefix'] . @$timeCode['data'] . @$timeCode['suffix'];
					
					$themeobject->output('<li>'.cs_get_post_avatar($p, $p['userid'] ,30, true));
					
					$themeobject->output('<div class="post-content">');
					$themeobject->output('<a class="title" href="'.qa_q_path_html($p['postid'], $p['title']).'">'.qa_html($p['title']).'</a>');
					$themeobject->output('<div class="meta">');
					$themeobject->output('<span>' . qa_lang_sub('cleanstrap/x_answers', $p['acount']) . '</span>');     
					$themeobject->output('<span class="time icon-time">' .  $when . '</span>');
					$themeobject->output('<span class="vote-count icon-thumbs-up2">' . qa_lang_sub('cleanstrap/x_votes', $p['netvotes']) . '</span>');		
					$themeobject->output('</div>');
					$themeobject->output('</div>');
					
					$themeobject->output('</li>');
				}
				
				$themeobject->output('</ul>');
			$themeobject->output('</div>');
		}
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/