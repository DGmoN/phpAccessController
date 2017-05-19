<?php
require("url_parser.php");
$CURRENT_MODULE = "ACCESS v0.0c";
append_log("Loading access controll");

$ENABLE_IMAGES_REWRITE = false;
$ENABLE_FILE_REWRITE = false;
$FILE_REWRITE_DIR = "";
if(file_exists(__DIR__."/config.txt")){
	append_log("Loading config");
	$cfg = fopen(__DIR__."/config.txt", "r");
	while(($line=fgets($cfg)) != null){
		append_log($line);
		eval($line);
	}
	fclose($cfg);
}

if(@$_SESSION['PARSER']){
	$URL_PARSER = load("PARSER");
}else{
	$URL_PARSER = new URL_Parser();
	save($URL_PARSER, "PARSER");
}




$PAGES = array();


	$PAGES['404'] = function($GET = null){
					return "error 404";
				};
				
	$PAGES['400'] = function($GET = null){
						append_log(print_r($GET, true));
						return "400:Its a dead link ".$GET;
					};
					
	$PAGES['css'] = function($GET = null){
						global $ASSETS_ROOT;
						$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
						if(file_exists($file)){
							
							global $ASSETS_ROOT;
							header('Content-Type: text/css');
							require($file);
						}else
							return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
					};
	$PAGES['js'] = function($GET = null){
						global $ASSETS_ROOT;
						$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
						if(file_exists($file)){
							global $ASSETS_ROOT;
							include($file);
						}else
							return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
					};
	$PAGES['img'] = function($GET = null){
						global $ASSETS_ROOT;
						
						$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
						if(file_exists($file)){
							global $ASSETS_ROOT;
							
							$remoteImage = $file;
							$imginfo = getimagesize($remoteImage);
							header("Content-type: {$imginfo['mime']}");
							readfile($remoteImage);
														
							#include($ASSETS_ROOT."/img/".$GET);
						}else
							return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
					};
	if($PAGES_SCRIPT)
		require($PAGES_SCRIPT);
	
	
if(file_exists(".htaccess")){
	append_log(".httacess file found");
}else{
	append_log("no .httacess file found\n creating...");
	$F = fopen(".htaccess", "w");
	fwrite($F, "RewriteEngine on\n");
	if($ENABLE_FILE_REWRITE){
		fwrite($F, "RewriteCond %{REQUEST_URI} !^(.*)/".$FILE_REWRITE_DIR."(.*)$\n");
		fwrite($F, "RewriteCond %{REQUEST_URI} !^(.*)/store(.*)$\n");
	}
	fwrite($F, "RewriteCond %{REQUEST_URI} !^(.*)/img/(.*)$\n");
	fwrite($F, "RewriteRule ^(.*)$ index.php?dir=$1 [QSA]\n");

	if($ENABLE_IMAGES_REWRITE){
		fwrite($F, "RewriteCond %{REQUEST_URI} ^(.*)/img/(.*)$\n");
		fwrite($F, "RewriteRule ^(.*)img(.*)$ ".$ASSETS_ROOT."img$2 [QSA]\n");
	}
	
	if($ENABLE_FILE_REWRITE){
		fwrite($F, "RewriteCond %{REQUEST_URI} ^(.*)/store/(.*)$\n");
		fwrite($F, "RewriteRule ^(.*)/store/(.*)$ ".$FILE_REWRITE_DIR."$2 [QSA]\n");
	}

	fclose($F);
}

if(!isset($_GET['dir'])){
	exit("Something went terribly wrong");
}

$TARGET = $URL_PARSER->PARESE_URL($_GET['dir']);

if(@$PAGES[$TARGET['TARGET']]){
	echo $PAGES[$TARGET['TARGET']]($TARGET);
}else
	echo $PAGES['400'](print_r($TARGET["REQUEST"], true));

?>