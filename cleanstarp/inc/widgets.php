<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
//class qa_html_theme extends qa_html_theme_base
class qa_html_theme_layer extends qa_html_theme_base {

	var $theme_directory;
	var $theme_url;

	function qa_html_theme_layer($template, $content, $rooturl, $request)
	{
		global $qa_layers;
		$this->theme_directory = $qa_layers['Theme Widgets']['directory'];
		$this->theme_url = $qa_layers['Theme Widgets']['urltoroot'];
		qa_html_theme_base::qa_html_theme_base($template, $content, $rooturl, $request);
	}

	function doctype(){
		$this->widgets = get_all_widgets();
		// Setup Navigation
		global $qa_request;
		//var_dump($qa_request);
		$this->content['navigation']['user']['widgets'] = array(
			'label' => 'Theme Widgets',
			'url' => qa_path_html('widgets'),
			'icon' => 'icon-puzzle',
		);
		if($qa_request == 'widgets') {
			$this->content['navigation']['user']['widgets']['selected'] = true;
			$this->content['navigation']['user']['selected'] = true;
			$this->template="widgets";
			$this->content['site_title']="Theme Widgets";
			$this->content['error']="";
			$this->content['suggest_next']="";
			$this->content['title']="Theme Widgets";
			//$this->content['custom']='';
		
			$saved=false;
			if (qa_clicked('cs_remove_all_button')) {	
				qa_db_query_sub('TRUNCATE TABLE ^cs_widgets');
				$saved=true;
			}
			if (qa_clicked('cs_reset_widgets_button')) {	
				$handle = fopen(Q_THEME_DIR.'/demo_content/widget_builder.sql', 'r');
				$sql = '';
								

				if($handle) {
					while(($line = fgets($handle, 4096)) !== false) {
						$sql .= trim(' ' . trim($line));
						if(substr($sql, -strlen(';')) == ';') {
								qa_db_query_sub($sql);
								$sql = '';
						}
					}
					fclose($handle);
				}					
				$saved=true;
			}
			
			$cs_page = '
				<div id="ra-widgets">
					<div class="widget-list col-sm-5">
						'. $this->cs_get_widgets() .'
					</div>
					<div class="widget-postions col-sm-7">
						'.$this->cs_get_widgets_positions().'
					</div>
				</div>
				<div class="form-widget-button-holder">
					<form class="form-horizontal" method="post">
						<input class="qa-form-tall-button btn-primary" type="submit" name="cs_remove_all_button" value="Remove All Widgets" title="">
						<input class="qa-form-tall-button btn-primary" type="submit" name="cs_reset_widgets_button" value="Reset All Widgets To Theme Default" title="">
					</form>
				</div>
			';
			$this->content['custom'] = $cs_page;
		}
		qa_html_theme_base::doctype();
	}	
	
		function main()
		{
			if($this->request == 'widgets') {
				$content=$this->content;
				$this->output('<div class="qa-main theme-widgets clearfix"><div class="col-sm-12">');
				$this->output(
					'<h1 class="page-title">',
					$this->content['title'],
					'</h1>'
				);
				$this->main_parts($content);
				$this->output('</div></div> <!-- END qa-main -->', '');
			}else
				qa_html_theme_base::main();
		}
		function main_part($key, $part)
		{
			if( ($this->request == 'widgets') && ($key == 'custom') ){
				$this->output_raw($part);
			}else
				qa_html_theme_base::main_part($key, $part);
		}
		
		function cs_get_widgets(){
			ob_start();
			foreach(qa_load_modules_with('widget', 'allow_template') as $k => $widget){
				?>
				<div class="draggable-widget" data-name="<?php echo $k; ?>">					
					<div class="widget-title"><?php echo $k; ?> 
						<div class="drag-handle icon-move"></div>
						<div class="widget-delete icon-trash"></div>
						<div class="widget-template-to icon-list"></div>
						<div class="widget-options icon-wrench"></div>
					</div>
					<div class="select-template">
						<label>
						<input type="checkbox" name="show_title" checked> Show widget title</label><br />
						<span>Select where you want to show</span>
						<?php
							$this->get_widget_template_checkbox();
						?>
					</div>
					<div class="widget-option">
						<?php $this->get_widget_form($k); ?>
					</div>
				</div>
				<?php
			}
			
			return ob_get_clean();
		}	


		function cs_get_widgets_positions(){
			$widget_positions = unserialize(qa_opt('cs_widgets_positions'));

			ob_start();
			if(is_array($widget_positions)){
				foreach($widget_positions as $name => $description){
				
					?>
					<div class="widget-canvas" data-name="<?php echo $name; ?>">		
						<div  class="position-header">		
							<?php echo $name; ?><span class="position-description"><?php echo $description; ?></span>							
							<i class="position-toggler icon-angle-down"></i>
							<div class="widget-save icon-ok"> Save</div>
						</div>
						<div class="position-canvas" data-name="<?php echo $name; ?>">
							<?php
								$pos_widgets = get_widgets_by_position($name);
								if(isset($pos_widgets) && !empty($pos_widgets))
									foreach($pos_widgets as $w){ ?>
										<div class="draggable-widget" data-name="<?php echo $w['name']; ?>" data-id="<?php echo $w['id']; ?>">	
											<div class="widget-title"><?php echo $w['name']; ?> 
												<div class="drag-handle icon-move"></div>
												<div class="widget-delete icon-trash"></div>
												<div class="widget-template-to icon-list"></div>		
												<div class="widget-options icon-wrench"></div>		
											</div>
											<div class="select-template">
											<input type="checkbox" name="show_title" <?php echo (@$w['param']['locations']['show_title'] ? 'checked' : ''); ?>> Show widget title</label><br />
												<span>Select pages where you want to show</span>
												<?php
													foreach(cs_get_template_array() as $k => $t){
														$checked = @$w['param']['locations'][$k] ? 'checked' : '';
														echo '												
															<div class="checkbox">
																<label>
																	<input type="checkbox" name="'.$k.'" '.$checked.'> '.$t.'
																</label>
															</div>
														';
													}
												?>
											</div>
											<div class="widget-option">
												<?php $this->get_widget_form($w['name'], $w['param']['options']); ?>
											</div>
										</div>									
									<?php
									}
									
							?>
						</div>
					</div>
					<?php
				}
			}
			return ob_get_clean();
		}
		
		function get_widget_template_checkbox(){
			foreach(cs_get_template_array() as $t_name => $t)
				$this->output( '												
					<div class="checkbox">
						<label>
							<input type="checkbox" name="'.$t_name.'" checked> '.$t.'
						</label>
					</div>
				');

		}
		function get_widget_form($name, $options = false){
			$module	=	qa_load_module('widget', $name);							
			if(is_object($module) && method_exists($module, 'cs_widget_form')){
				$fields = $module->cs_widget_form();
				
				if($options){
					foreach($options as $k => $opt){
						if(isset($fields['fields'][$k]))
							$fields['fields'][$k]['value'] = $opt;
					}
				}
				$this->form($fields); 
			}
		}
		
}


/*
	Omit PHP closing tag to help avoid accidental output
*/