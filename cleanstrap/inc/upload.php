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
$inc_dir = "../inc/class_images.php";
if(isset($_FILES["featured"]))
{
	$ret = array();

	$error =$_FILES["featured"]["error"];
	//You need to handle  both cases
	//If Any browser does not support serializing of multiple files using FormData() 
	if(!is_array($_FILES["featured"]["name"])) //single file
	{
		require_once $inc_dir;
			$filenames = array();
			$uploaddir 	= $output_dir;
			$ext = pathinfo( $_FILES['featured']['name'], PATHINFO_EXTENSION);
			$file_name = md5(time().uniqid());
			$temp_name = $file_name.'_temp';
			$temp_name_with_ext =$file_name.'_temp'.$ext;
			$file_name_with_ext = $file_name .'.'.$ext;
			move_uploaded_file($_FILES['featured']['tmp_name'], $uploaddir.$temp_name_with_ext);
			
			$image = new Image($uploaddir.$temp_name_with_ext);
			
			$width = (int)qa_opt('cs_featured_image_width');
			if ($width<=0) $width = 800;
			$height = (int)qa_opt('cs_featured_image_height');
			if ($height<=0) $height = 300;
			$t_width = (int)qa_opt('cs_featured_thumbnail_width');
			if ($t_width<=0) $t_width = 278;
			$t_height = (int)qa_opt('cs_featured_thumbnail_height');
			if ($t_height<=0) $t_height = 120;
			
			$crop_x = qa_opt('cs_crop_x');
			$crop_y = qa_opt('cs_crop_y');
			
			$image->resize($width, $height, 'crop', $crop_x, $crop_y, 99);
			$image->save($file_name, $uploaddir);
			
			$thumb = new Image($uploaddir.$temp_name_with_ext);
			$thumb->resize($t_width, $t_height, 'crop', $crop_x, $crop_y, 99);
			$thumb->save($file_name.'_s', $uploaddir);
			unlink ($uploaddir.$temp_name_with_ext); 
			
 			$filenames[] = $file_name_with_ext;
			$filenames[] = $file_name .'_s.'.$ext;
	 	
    	//echo $file_name_with_ext;
		 echo json_encode($filenames);
	}
}
if(isset($_FILES["myfile"]["name"])) //single file
{
	$fileName = $_FILES["myfile"]["name"];
	if (file_exists($output_dir.$fileName)) {
		$exts = substr(strrchr($fileName,'.'),1);
		$withoutExt = preg_replace("/\\.[^.\\s]{3,4}$/", "", $fileName);
		for($i=2; file_exists($output_dir.$withoutExt.'-'.$i.'.'.$exts); $i++);
		$fileName = $withoutExt.'-'.$i.'.'.$exts;
	}
	move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$fileName);
	echo $fileName;
}

/*
	Omit PHP closing tag to help avoid accidental output
*/