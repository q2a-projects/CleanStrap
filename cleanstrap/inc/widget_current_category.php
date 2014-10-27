<?php
	class cs_current_category_widget {

		
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
	
		function cs_get_cat_desc($slug){
			
			$result = qa_db_read_one_assoc(qa_db_query_sub('SELECT title,content FROM ^categories WHERE tags=$', $slug ),true);
			
			return $result;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$req = end(explode('/', qa_request()));
			
			if(!isset($req))
				return;
				
			if(!(qa_opt('event_logger_to_database'))) return;
			$widget_opt = @$themeobject->current_widget['param']['options'];
			$cat = $this->cs_get_cat_desc($req);
			
			if(@$themeobject->current_widget['param']['locations']['show_title'] && isset($cat['title']))
				$themeobject->output('<h3 class="widget-title">'.$cat['title'].'</h3>');
				
			$themeobject->output('<div class="ra-cc-widget">');	
				$themeobject->output($cat['content']);
					
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/