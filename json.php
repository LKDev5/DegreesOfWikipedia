<?php
	//json.php - similar to test_article_link.php, but returns results as JSON
	//set json header type
	//header('Content-Type: application/json');
	//header('Content-Type: text/html; charset=utf-8');
	//header('Access-Control-Allow-Origin: *');

	require_once('settings.inc.php');
	require_once('article.class.php');
	require_once('functions.inc.php');





	//hardcode the current language to english if it is not already set
	if($GLOBALS['wikiurls'][$_REQUEST['currentlang']] == null)
	{
		//default to english
		$GLOBALS['currentlang'] = 'en';
	}
	else
	{
		//allow selected language
		$GLOBALS['currentlang'] = $_REQUEST['currentlang'];

	}


	//calculate if submit button seconds are valid to prevent search engines from killing the site in a year from old links
	$submitok = false;
	$submitarrow = false;
	$submitvalue = '';
	//if submit is set, and it is numeric
	if(is_numeric($_REQUEST['submit']))
	{
		$submitvalue = $_REQUEST['submit'];

		//if the submit timestamp is within the designated range
		if((time() - $_REQUEST['submit'])  < $submitbuttontimeoutseconds)
		{
			$submitok = true;
		}
		else
		{
			$submitarrow = true;
		}
	}




	if($submitok && $_REQUEST['a1'] && $_REQUEST['a2'] && $_REQUEST['linktype'] != '')
	{


		$a1 = $_REQUEST['a1'];
		$a2 = $_REQUEST['a2'];
		$linktype = $_REQUEST['linktype'];


		//exit();

		//verify that the links are valid
		$a1_obj = new Article($a1);
		$a2_obj = new Article($a2);

		//echo "a1_obj->validate(): " . $a1_obj->validate() . "<br/>";
		//echo "a2_obj->validate(): " . $a2_obj->validate() . "<br/>";
		//echo "<pre>";
		//print_r($a2_obj);
		//echo "</pre>";
		//exit();

		if(!$a1_obj->validate())
		{
			if($_REQUEST['dbg'])
			{
				echo htmlentities($a1,ENT_NOQUOTES,'UTF-8') . " was not a valid article.<br/>";
				exit();
			}
			else
			{
				$result = array('error'=>"Oops! " . htmlentities($a1,ENT_NOQUOTES,'UTF-8') . " couldn't be matched to a Wikipedia article.");
				echo json_encode($result);
				exit();
			}
		}
		elseif(!$a2_obj->validate())
		{
			if($_REQUEST['dbg'])
			{
				echo htmlentities($a2,ENT_NOQUOTES,'UTF-8') . " was not a valid article.<br/>";
				exit();
			}
			else
			{
				$result = array('error'=>"Oops! " . htmlentities($a2,ENT_NOQUOTES,'UTF-8') .  " couldn't be matched to a Wikipedia article.");
				echo json_encode($result);
				exit();
			}
		}
		else
		{

			if($_REQUEST['allowsideboxes'] == 1)
			{
				$GLOBALS['allowsideboxes'] = true;
			}
			else
			{
				$GLOBALS['allowsideboxes'] = false;
			}

			//prepare the skip list, if it is not empty
			$GLOBALS['skips'] = array();
			if($_REQUEST['skips'] != '')
			{
				$temp_skips = explode("\n",$_REQUEST['skips']);
				foreach($temp_skips as $temp_skip)
				{
					if(trim($temp_skip) != '')
					{
						$GLOBALS['skips'][cleanArticleKey($temp_skip)] = 1;
					}
				}
			}

			//allow years
			if($_REQUEST['allowyears'])
			{
				$GLOBALS['allowyears'] = true;
			}
			else
			{
				$GLOBALS['allowyears'] = false;
			}

			//Allow random alternate path, if selected
			if($_REQUEST['randompath'])
			{
				$GLOBALS['randomalternatepath'] = true;
			}

			if($GLOBALS['dbg'])
			{
				echo "skips:<pre>";
				print_r($GLOBALS['skips']);
				echo "</pre>";
			}

			$a1_url = str_replace('[a]',$a1_obj->title,$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['link']);
			$a2_url = str_replace('[a]',$a2_obj->title,$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['link']);


			if($_REQUEST['dbg'])
			{
				echo "Starting linking of <a href=\"$a1_url\">" . htmlentities($a1,ENT_NOQUOTES,'UTF-8') . "</a> to <a href=\"$a2_url\">" . htmlentities($a2,ENT_NOQUOTES,'UTF-8') . "</a>...<br/>";
			}



			//if the size of the data table is greater than 10k, truncate it



			//flush the output buffers
			ob_flush();
			flush();

			//20100724 - passing the Title of the article to the link function
			$finalbt_result = doLink(htmlspecialchars_decode($a1_obj->title),htmlspecialchars_decode($a2_obj->title),$linktype);

			if($finalbt_result['result'])
			{
				$finalbt = $finalbt_result['result'];
				if($_REQUEST['dbg'])
				{
					echo "success!!!:<br/>";
					echo "Final BT Article Names:<br/>";
					echo "<pre>";
					print_r($finalbt);
					echo "</pre>";

					//print some nice pretty links based on the current language!
					foreach($finalbt as $article_name)
					{
						$url = str_replace('[a]',$article_name,$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['link']);
						echo "<a href=\"$url\">$article_name</a><br/>";
					}

					echo "<h1>";
					$finalbt_values = array_values($finalbt);
					echo $finalbt_values[0] . " &rarr; " . $finalbt_values[count($finalbt_values) - 1] . "<br/>";
					echo (count($finalbt) - 1) . " degrees.<br/>";
					echo "</h1>";

				}
				else
				{

					//reformat the finalbt array to be more fancy looking,
					// with urls to wiki articles

					$jsonfinal = array();
					foreach($finalbt as $article_key=>$article_name)
					{
						$url = str_replace('[a]',$article_name,$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['link']);

						//get the length of this article
						$a_temp = new Article('');

						$temp_xml = $a_temp->fetchXML($article_name);
						//echo $temp_xml;

						//match bytes="10507" out of the xml
						preg_match('/bytes="(\d+)"/',$temp_xml,$matches);
						$bytelength = $matches[1];

						//exit();

						$jsonfinal[] = array(
										'article_key'=>$article_key,
										'article_name'=>$article_name,
										'article_bytelength'=>$bytelength,
										'article_url'=>$url,


									);
						//echo "<a href=\"$url\">$article_name</a><br/>";
					}


					//loop over jsonfinal and choose the middle term,
					// based on article_bytelength!
					//now choose the 'center' of the chain
					if(count($jsonfinal) % 2 == 0)
					{
						//even

						//choose the two 'center' items that are competing
						$center_index_1 = (count($jsonfinal) / 2) - 1;
						$center_index_2 = $center_index_1 + 1;

						//see wich center item has a longer bytelength
						if($jsonfinal[$center_index_1]['article_bytelength'] > $jsonfinal[$center_index_2]['article_bytelength'])
						{
							$middleindex = $center_index_1;
						}
						else
						{
							$middleindex = $center_index_2;
						}

					}
					else
					{
						//odd
						$middleindex = floor(count($jsonfinal) / 2);
					}


					//echo "json echo goes here";
					//$result = array('path'=>$jsonfinal,'auth'=>md5($jsonfinal[0]['article_name'] . $jsonfinal[count($jsonfinal) - 1]['article_name'] . date('Ymd')));
					$result = array(
									'path'=>$jsonfinal,
									'auth'=>md5($jsonfinal[0]['article_name'] . $jsonfinal[count($jsonfinal) - 1]['article_name'] . $jsonfinal[$middleindex]['article_name'] . count($jsonfinal) . 'secrat'),
									'middle'=>array(
												'middleindex'=>$middleindex,
												'midpathitem'=>$jsonfinal[$middleindex],
											),
									);
					echo json_encode($result);


				}


			}
			elseif($finalbt_result['error'])
			{
				if($_REQUEST['dbg'])
				{
					echo $finalbt_result['error'];
				}
				else
				{
					$result = array('error'=>$finalbt_result['error']);
					echo json_encode($result);
				}
			}
			else
			{
				if($_REQUEST['dbg'])
				{
					echo "unknown error.<br/>";
				}
				else
				{
					$result = array('error'=>$finalbt_result['error']);
					echo json_encode($result);
				}
			}


			//echo "count of finalbt_result is:" . count($finalbt_result);
			$degree_count = count($finalbt_result['result']) - 1;

			

		}
	}

?>