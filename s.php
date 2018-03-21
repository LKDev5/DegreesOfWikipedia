<?php
	//this script will serve to proxy a search to wikipedia for a given a term, cache it, and return.
	
	require_once('settings.inc.php');
	
	
	
	$lang = $_REQUEST['l'];
	$term = $_REQUEST['t'];
	$querytype = $_REQUEST['qt'];
	if($GLOBALS['wikiurls'][$lang] != null)
	{
		//ac = autocomplete!
		if($querytype == 'ac')
		{
			
			//build the search url
			$url = str_replace('[a]',urlencode($term),$wikiurls[$lang]['search']);
			if($_REQUEST['dbg']) echo $url . "<br/>";
			
			//fetch from wikipedia using curl, hosted in settings.inc.php file
			$data = getHTTP($url);
			if($_REQUEST['dbg']) echo $data . "<br/>";
							
			
		
			$data_array = json_decode($data);
			
			
			
			
			
			$stdclass_results = array();

			if(count($data_array[1]) > 0)
			{
				foreach($data_array[1] as $item)
				{
					$stdobj = new stdClass();
					$stdobj->id = $item;
					$stdobj->label = $item;
					$stdobj->value = $item;
					
					$stdclass_results[] = $stdobj;
				}
			}
			//echo "<pre>";
			//print_r($stdclass_results);
			//echo "</pre>";
			
			$json_stdclass_results = json_encode($stdclass_results);
			
			if($json_stdclass_results == '[]')
			{
				echo '[   ]';
			}
			else
			{
				echo $json_stdclass_results;
			}
			
			
		
		}
		elseif($querytype == 'rand')
		{
		
			//getting a random article for this language
			//build the search url
			$url = $wikiurls[$lang]['rand'];
			//if($_REQUEST['dbg']) echo $url . "<br/>";
			
			//fetch from wikipedia using curl, hosted in settings.inc.php file
			$data = getHTTP($url);
			
			$data_unserialized = unserialize($data);
			//echo "<pre>";
			//print_r($data_unserialized);
			//echo "</pre>";
			
			$random_article = $data_unserialized['query']['random'][0]['title'];
			
			echo $random_article;
		
		}
	}
	else
	{
		echo "nolang";
	}
	





?>