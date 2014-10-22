<?php
/* don't allow this page to be requested directly from browser */	

require_once '../../../qa-include/qa-base.php';
require_once QA_INCLUDE_DIR.'qa-app-users.php';
// Security Check
if(qa_get_logged_in_level()<QA_USER_LEVEL_ADMIN){
		header('Location: /');
		exit;
}

$output_dir = "../uploads/";
if(isset($_POST["op"]) && $_POST["op"] == "delete" && isset($_POST['name']))
{
	$fileName =$_POST['name'];
	$filePath = $output_dir. $fileName;
	if (file_exists($filePath)) 
	{
        unlink($filePath);
    }
	echo "Deleted File ".$fileName."<br>";
}

/*
	Omit PHP closing tag to help avoid accidental output
*/