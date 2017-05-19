<?php
$CURRENT_MODULE = "URL_PARSER v0.0a";
append_log("Loading URL parser");

class URL_Parser{
	private $URLS, $BUILTINS = array("/^.*(?P<file>css\/.*)$/:css:css", "/^.*(?P<file>js\/.*)$/:js:js", "/^.*(?P<file>img\/.*)$/:img:img");
	function __construct(){
		$URLS_FILE = fopen("pages.txt", "r");
		if($URLS_FILE){
			$URLS = array();
			while(($line = fgets($URLS_FILE)) != null){
				$QQ = explode(":",$line);
				$REGEX = $QQ[0];
				$TARGET = $QQ[1];
				$ID = trim($QQ[2]);
				$URLS[$ID] = array("REX" =>$REGEX, "TARGET"=>$TARGET);
			}
			
			foreach($this->BUILTINS as $line){
				$QQ = explode(":",$line);
				$REGEX = trim($QQ[0]);
				$TARGET = $QQ[1];
				$ID = trim($QQ[2]);
				$URLS[$ID] = array("REX" =>$REGEX, "TARGET"=>$TARGET);
			}
			
			$this->URLS = $URLS;
		}else{
			append_log("No pages.txt file found");
		}
		append_log("Created URL parser");
	}
	
	function get_url_for_label($label){
		
		return $this->URLS[$label];
	}
	
	function PARESE_URL($URL){
		if($URL == 'index.php' or $URL =="")
			$URL = "index";
		append_log("Parsing URL: ".$URL);
		
		foreach($this->URLS as $k=>$v){
			append_log($URL."->".$v['REX']);
			if(preg_match_all($v["REX"], $URL, $matches, PREG_SET_ORDER, 0)){
				append_log(print_r($matches, true));
				
				$e = $v;
				@$e["REQUEST"] = array("URL"=>$URL, "REFERER"=>$_SERVER['HTTP_REFERER']);
				$e["MATCHES"] = $matches;
				return $e;
			}
		}
		return array("TARGET"=>"404", "REQUEST"=> array(array("URL"=>$URL, "REFERER"=>$_SERVER['HTTP_REFERER'])));
		
	}
}

?>