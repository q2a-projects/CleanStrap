<?php

//	Output this header as early as possible
	header('Content-Type: text/plain; charset=utf-8');


//	Ensure no PHP errors are shown in the Ajax response
	error_reporting(0);
	@ini_set('display_errors', 0);


//	Load the Q2A base file which sets up a bunch of crucial functions
	require_once '../../../qa-include/qa-base.php';
	qa_report_process_stage('init_ajax');		

//	Get general Ajax parameters from the POST payload, and clear $_GET
	qa_set_request(qa_post_text('qa_request'), qa_post_text('qa_root'));
	
	require_once QA_INCLUDE_DIR.'qa-app-users.php';
	require_once QA_INCLUDE_DIR.'qa-app-cookies.php';
	require_once QA_INCLUDE_DIR.'qa-app-votes.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	require_once QA_INCLUDE_DIR.'qa-app-options.php';
	require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	require_once '../functions.php';

if(isset($_REQUEST['action'])){
	$action = 'cs_ajax_'.$_REQUEST['action'];
	if(function_exists($action))
		$action();
}	


	
function cs_ajax_save_widget_position()
{
	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
		$position     = strip_tags($_REQUEST['position']);
		$jsonstring = $_REQUEST['widget_names'];//stripslashes2(str_replace('\"', '"', $_REQUEST['widget_names']));
		$widget_names = json_decode($jsonstring, true);
		$newid        = array();
		if (isset($widget_names) && is_array($widget_names))
			foreach ($widget_names as $k => $w) {
				$param = array(
					'locations' => $w['locations'],
					'options' => $w['options']
				);
				if (isset($w['id']) && $w['id'] > 0)
					$newid[] = widget_opt($w['name'], $position, $k, serialize($param), $w['id']);
				else
					$newid[] = widget_opt($w['name'], $position, $k, serialize($param));
			}
		
		echo json_encode($newid);
	}
	die();
}


function cs_ajax_delete_widget()
{

	if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
		$id = strip_tags($_REQUEST['id']);
		widget_opt_delete($id);
	}
	die();
}
