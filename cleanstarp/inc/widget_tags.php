<?php
	class cs_tags_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_tags_count' => array(
						'label' => 'Numbers of tags',
						'type' => 'number',
						'tags' => 'name="cs_tags_count"',
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
			require_once QA_INCLUDE_DIR.'qa-db-selects.php';
			$widget_opt = @$themeobject->current_widget['param']['options'];

			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">'.qa_lang('cleanstrap/tags').'<a href="'.qa_path_html('tags').'">'.qa_lang('cleanstrap/view_all').'</a></h3>');
				
			$to_show = (int)$widget_opt['cs_tags_count'];
			$populartags = qa_db_single_select(qa_db_popular_tags_selectspec(0, (!empty($to_show) ? $to_show : 20)));
			
			reset($populartags);
			$themeobject->output('<div class="ra-tags-widget clearfix">');
	
			$blockwordspreg=qa_get_block_words_preg();			
			foreach ($populartags as $tag => $count) {
				if (count(qa_block_words_match_all($tag, $blockwordspreg)))
					continue; // skip censored tags

				$themeobject->output('<a href="'.qa_path_html('tag/'.$tag).'" class="widget-tag">'.qa_html($tag).'<span>'.$count.'</span></a>');
			}
			
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/