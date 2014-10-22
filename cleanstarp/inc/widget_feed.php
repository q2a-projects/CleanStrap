<?php
	class cs_feed_widget {

		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_feed_title_field' => array(
						'label' => 'Widget Title:',
						'type' => 'string',
						'value' => 'Feed Widget',
						'tags' => 'NAME="cs_feed_title_field"',
					),
					'cs_feed_url_field' => array(
						'label' => 'Feed URL:',
						'type' => 'string',
						'value' => '',
						'tags' => 'NAME="cs_feed_url_field"',
					),
					'cs_feed_count_field' => array(
						'label' => 'number of recent feeds:',
						'suffix' => 'item',
						'type' => 'number',
						'value' => 10,
						'tags' => 'NAME="cs_feed_count_field"',
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
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			global $cache;
			$age = 3600; //one hour

			if (isset($cache['feed'])){
				if ( ((int)$cache['feed']['age'] + $age) > time()) {
					$feed = $cache['feed']['content'];
					$themeobject->output($feed);
					return;
				}
			}
			$widget_opt = @$themeobject->current_widget['param']['options'];
			
			$url = $widget_opt['cs_feed_url_field'];
			$count=(int)$widget_opt['cs_feed_count_field'];
			$title=$widget_opt['cs_feed_title_field'];
			//$url = 'http://qa-themes.com/?feed=rss';
			//$count=10;
			//$title='Blog Feed';
			// read live content
			
			$content = file_get_contents($url);
			$x = new SimpleXmlElement($content);  
			$output ='<aside class="qa-feed-widget">';
			$output .='<H2 class="qa-feed-header">'.$title.'</H2>';

			$output .= '<ul class="qa-feed-list">'; 
			$i=0;
			foreach($x->channel->item as $entry) {  
				$output .= "<li class=\"qa-feed-item\"><a href='$entry->link' title='$entry->title'>" . $entry->title . "</a></li>";  
				$i++;
				if ($i>=$count)
					break;
			}  
			$output .= "</ul>";  
			$output .= '</aside>';
			$themeobject->output($output);
		
			$cache['feed']['content'] =  $output;
			$cache['feed']['age'] = time();
			$cache['changed'] = true;	
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/