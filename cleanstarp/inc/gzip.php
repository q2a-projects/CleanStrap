<?php
if(extension_loaded('zlib')){
	if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
		ob_start('ob_gzhandler');
	else
		ob_start();
	header ("content-type: text/css; charset: UTF-8");
	header ("cache-control: must-revalidate");
	$offset = 3600; // expires in one hour
	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	header ($expire);
	// list CSS files to be included in the Gzip
	include('../css/font.css');
	include('../css/bootstrap.css');
	include('../css/main.css');
	include('../css/wide.css');
	include('../css/responsive.css');
	include('../css/theme-green.css');
	ob_end_flush();
}else{
	ob_start();
	header ("content-type: text/css; charset: UTF-8");
	header ("cache-control: must-revalidate");
	$offset = 3600; // expires in one hour
	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	header ($expire);
	// list CSS files to be included in the Gzip
	include('../css/font.css');
	include('../css/bootstrap.css');
	include('../css/main.css');
	include('../css/wide.css');
	include('../css/responsive.css');
	include('../css/theme-green.css');
	ob_end_flush();
}
/*
	Omit PHP closing tag to help avoid accidental output
*/