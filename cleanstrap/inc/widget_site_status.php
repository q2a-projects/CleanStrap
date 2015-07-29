<?php
	class cs_site_status_widget {
		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'type' => array(
						'label' => 'Type of chart',
						'type' => 'select',
						'tags' => 'name="type"',
						'value' => 'bar',
						'options' => array(
							'bar' => 'bar',
							'pie' => 'pie',
						)
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
		function cs_stats_chart($type = 'bar'){
			$o = '<div class="site-status-inner clearfix">';
			
			if($type == 'bar'){
			$o .='<div class="bar-float"><div class="sparkline" data-type="bar" data-bar-color="#E45840" data-bar-width="30" data-height="80">
					<!--'.qa_opt('cache_qcount').','.qa_opt('cache_acount').','.qa_opt('cache_ccount').','.qa_opt('cache_unaqcount').','.qa_opt('cache_unselqcount').'--></div>
                    <ul class="list-inline text-muted axis">
						<li>'.qa_lang_html('cleanstrap/que').'</li><li>'.qa_lang_html('cleanstrap/ans').'</li><li>'.qa_lang_html('cleanstrap/com').'</li><li>'.qa_lang_html('cleanstrap/ua').'</li><li>'.qa_lang_html('cleanstrap/us').'</li>
					</ul></div>
					<div class="acti-indicators">
						<ul>
					<li><i class="fa fa-circle text-info" style="color:#233445"></i> ' . qa_lang_html('cleanstrap/questions') . ' <span>'.qa_opt('cache_qcount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#3fcf7f"></i> ' . qa_lang_html('cleanstrap/answers') . ' <span>'.qa_opt('cache_acount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#FF5F5F"></i> ' . qa_lang_html('cleanstrap/comments') . ' <span>'.qa_opt('cache_ccount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#13C4A5"></i> ' . qa_lang_html('cleanstrap/unanswered') . ' <span>'.qa_opt('cache_unaqcount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#F4C414"></i> ' . qa_lang_html('cleanstrap/unselected') . ' <span>'.qa_opt('cache_unselqcount').'</span></li>
				</ul>
					</div>';
			}else{
			$o .= '<div class="bar-float"><div class="sparkline pieact inline" data-type="pie" data-height="130" data-slice-colors="[\'#233445\',\'#3fcf7f\',\'#ff5f5f\',\'#f4c414\',\'#13c4a5\']">'.qa_opt('cache_qcount').','.qa_opt('cache_acount').','.qa_opt('cache_ccount').','.qa_opt('cache_unaqcount').','.qa_opt('cache_unselqcount').'</div>
				<div class="line pull-in"></div></div>
				<div class="acti-indicators">
				<ul>
					<li><i class="fa fa-circle text-info" style="color:#233445"></i>  ' . qa_lang_html('cleanstrap/questions') . '<span>'.qa_opt('cache_qcount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#3fcf7f"></i>  ' . qa_lang_html('cleanstrap/answers') . ' <span>'.qa_opt('cache_acount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#FF5F5F"></i>  ' . qa_lang_html('cleanstrap/comments') . ' <span>'.qa_opt('cache_ccount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#13C4A5"></i>  ' . qa_lang_html('cleanstrap/unanswered') .  ' <span>'.qa_opt('cache_unaqcount').'</span></li>
					<li><i class="fa fa-circle text-info" style="color:#F4C414"></i>  ' . qa_lang_html('cleanstrap/unselected') .  '<span>'.qa_opt('cache_unselqcount').'</span></li>
				</ul>
				</div>';
			}
			$o .= '</div>';
			return $o;
		}
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = @$themeobject->current_widget['param']['options'];
			$type = @$widget_opt['type'];

			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">' . qa_lang_html('cleanstrap/site_status') . '</h3>');
				
			$themeobject->output('<div class="ra-site-status-widget">');
			$themeobject->output($this->cs_stats_chart($type));
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/