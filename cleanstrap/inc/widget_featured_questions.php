<?php
	class cs_featured_questions_widget {
		
		function cs_widget_form()
		{
			
			return array(
				'style' => 'wide',
				'fields' => array(
					'cs_fq_count' => array(
						'label' => 'Questions to show',
						'type' => 'number',
						'tags' => 'name="cs_fq_count"',
						'value' => '10',
					),
					'cs_fq_boxes' => array(
						'label' => 'Number of box per row',
						'type' => 'number',
						'tags' => 'name="cs_fq_boxes"',
						'value' => '4'						
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
		

		// output the list of selected post type
		function carousel_item($type, $limit, $col_item = 1){
			require_once QA_INCLUDE_DIR.'qa-app-posts.php';
			$posts = qa_db_read_all_assoc(qa_db_query_sub('SELECT * FROM ^postmetas, ^posts INNER JOIN ^users ON ^posts.userid=^users.userid WHERE ^posts.type=$ and ( ^postmetas.postid = ^posts.postid and ^postmetas.title = "featured_question" ) ORDER BY ^posts.created DESC LIMIT #', $type, $limit));
			$output ='<div class="item"><div class="row">';
			$i = 1;
			foreach ($posts as $p) {
				if($type=='Q'){
					$what = qa_lang('cleanstrap/asked');
				}elseif($type=='A'){
					$what = qa_lang('cleanstrap/answered');
				}elseif('C'){
					$what = qa_lang('cleanstrap/commented');
				}
				
				$handle = $p['handle'];
				
				if($type=='Q'){
					$link_header = '<a href="'. qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'] .'">';
				}elseif($type=='A'){
					$link_header = '<a href="'.cs_post_link($p['parentid']).'#a'.$p['postid'].'">';
				}else{
					$link_header = '<a href="'.cs_post_link($p['parentid']).'#c'.$p['postid'].'">';
				}
				
				$output .= '<div class="slider-item col-sm-'.(12/$col_item).'">';
				$output .= '<div class="slider-item-inner">';
				$featured_img = get_featured_thumb($p['postid']);
				if ($featured_img)
					$output .= $link_header . '<div class="featured-image">'.$featured_img.'</div></a>';
				if ($type=='Q'){
					$output .= '<div class="big-ans-count pull-left">'.$p['acount'].'<span> ans</span></div>';
				}elseif($type=='A'){
					$output .= '<div class="big-ans-count pull-left vote">'.$p['netvotes'].'<span>'.qa_lang('cleanstrap/vote').'</span></div>';
				}

				$output .= '<h5>' . $link_header . cs_truncate(qa_html($p['title']), 50).'</a></h5>';


				$output .= '<div class="meta">';
				$when = qa_when_to_html(strtotime($p['created']), 7);
				$avatar = cs_get_avatar($handle, 15, false);
				if($avatar)
					$output .= '<img src="'.$avatar.'" />';	
				$output .= '<span class="icon-time">'.implode(' ', $when).'</span>';	
				$output .= '<span class="vote-count">'.$p['netvotes'].' '.qa_lang('cleanstrap/votes').'</span></div>';	
				
				$output .= '</div>';
				$output .= '</div>';
				if($col_item == $i){
					$output .= '</div></div><div class="item active"><div class="row">';
				}
				
				$i++;
			}
			$output .= '</div></div>';

			return $output;
		}
		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$widget_opt = $themeobject->current_widget['param']['options'];

			$count = (isset($widget_opt['cs_fq_count']) && !empty($widget_opt['cs_fq_count'])) ?(int)$widget_opt['cs_fq_count'] : 10;
			
			$col = (int)$widget_opt['cs_fq_boxes'];
	
			
			$themeobject->output('<div class="ra-featured-widget">');
			
			if(@$themeobject->current_widget['param']['locations']['show_title'])
				$themeobject->output('<h3 class="widget-title">Featured Questions</h3>');
				
			$themeobject->output('

            <div id="featured-slider" class="carousel slide">
                <!-- Carousel items -->
                <div class="carousel-inner">
                    '.$this->carousel_item('Q', $count, $col).'                    
                </div>
                <a class="left carousel-control icon-angle-left" href="#featured-slider" data-slide="prev"></a><a class="right carousel-control icon-angle-right" href="#featured-slider" data-slide="next"></a>
            </div>

			');
			$themeobject->output('</div>');
		}
	
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/
