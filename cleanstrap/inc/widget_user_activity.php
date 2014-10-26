<?php
	class cs_user_activity_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_ua_count' => array(
						'label' => 'Numbers of Questions',
						'type' => 'number',
						'tags' => 'name="cs_ua_count"',
						'value' => '10',
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

		function get_user_activity($handle, $limit = 10){
			$userid = qa_handle_to_userid($handle);
			require_once QA_INCLUDE_DIR.'qa-db-selects.php';
			require_once QA_INCLUDE_DIR.'qa-app-format.php';
			
			$identifier=QA_FINAL_EXTERNAL_USERS ? $userid : $handle;

			list($useraccount, $questions, $answerqs, $commentqs, $editqs)=qa_db_select_with_pending(
				QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
				qa_db_user_recent_qs_selectspec($userid, $identifier, $limit),
				qa_db_user_recent_a_qs_selectspec($userid, $identifier),
				qa_db_user_recent_c_qs_selectspec($userid, $identifier),
				qa_db_user_recent_edit_qs_selectspec($userid, $identifier)
			);
			
			if ((!QA_FINAL_EXTERNAL_USERS) && !is_array($useraccount)) // check the user exists
				return include QA_INCLUDE_DIR.'qa-page-not-found.php';


		//	Get information on user references

			$questions=qa_any_sort_and_dedupe(array_merge($questions, $answerqs, $commentqs, $editqs));
			$questions=array_slice($questions, 0, $limit);
			$usershtml=qa_userids_handles_html(qa_any_get_userids_handles($questions), false);
			$htmldefaults=qa_post_html_defaults('Q');
			$htmldefaults['whoview']=false;
			$htmldefaults['voteview']=false;
			$htmldefaults['avatarsize']=0;
			
			foreach ($questions as $question)
				$qa_content[]=qa_any_to_q_html_fields($question, $userid, qa_cookie_get(),
					$usershtml, null, array('voteview' => false) + qa_post_html_options($question, $htmldefaults));


			$output = '<div class="user-activities">';
			$output .='<ul>';
			if(isset($qa_content)){
				foreach ($qa_content as $qs){
					$icon = 'icon-time undefined';					
					$output .='<li class="activity-item">';					
					$output .= '<div class="list-right">';
					$output .= '<div class="type pull-left '.$icon.'"></div>';					
					$output .= '<div class="what"><a class="what-task" href="'.@$qs['url'].'">'.$qs['what'].'</a>';
					$output .= '<a href="'.$qs['url'].'">'.$qs['title'].'</a> </div>';
					$output .= '<strong class="when">'.implode(' ', $qs['when']).'</strong>';					
					$output .= '</div>';
					$output .='</li>';
				}
			}else{
				$output .='<li>'.qa_lang('cleanstrap/no_activity_yet').'</li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
			return $output;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = @$themeobject->current_widget['param']['options'];
			$handle = $qa_content['raw']['account']['handle'];
			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang_sub('cleanstrap/x_activities', $handle).'</h3>');
				
			$themeobject->output('<div class="ra-ua-widget">');
			$themeobject->output($this->get_user_activity($handle, (int)$widget_opt['cs_ua_count']));
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/