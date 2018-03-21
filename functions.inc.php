<?php
	require_once('article.class.php');
	set_time_limit(30);
	
	
	
	function makeKey()
	{
		return time() . "|" . hash('md5',date('Ymd') . "|" . $GLOBALS['supersecretkey']);
	}
	
	function checkKey($key)
	{
		$keyparts = explode("|",$key);
		//print_r($keyparts);
		
		$timestamp = $keyparts[0];
		$hashval = $keyparts[1];
		
		//if timestamp is recent enough - within 24 hours
		$timestampok = false;
		if(time() - $timestamp < 24*60*60)
		{
			//echo "timestamp OK!\n";
			$timestampok = true;
		}
		
		//check hash
		$hashvalok = false;
		if(hash('md5',date('Ymd') . "|" . $GLOBALS['supersecretkey']) == $hashval)
		{
			//echo "hashval OK";
			$hashvalok = true;
		}
		
		
		//return
		if($hashvalok && $timestampok)
		{
			return true;
		}
		else
		{
			return false;
		}
		
		
	}
	
	//check the match against the various link types
	//values for $linktype
	//	0 - any
	//	1 - L to R
	//	2 - R to L
	//	3 - Mutual (Both Directions)
	function doLink($a1,$a2,$linktype)
	{
		$a1 = wikiArticleNameEncode(trim($a1));
		$a2 = wikiArticleNameEncode(trim($a2));
	
		//create the initial lists that hold the individual sides
		
		
		$lists[0] = array(cleanArticleKey($a1)=>array('level'=>0,'done'=>false,'names'=>array($a1=>1)));
		$lists[1] = array(cleanArticleKey($a2)=>array('level'=>0,'done'=>false,'names'=>array($a2=>1)));
	
		$continue = true;
		$loopcount = 0;
		do
		{
			//pick the bigger of the two article lists
			if(count($lists[0]) <= count($lists[1]))
			{
				$smallerside 	= 0;
				$largerside 	= 1;
			}
			else
			{
				$smallerside = 1;
				$largerside = 0;
			}
			
			
			//get links on the smaller of the two sides
			
			
			$curlinkdirection = null;
			//calculate link direction that we are interested in
			if($smallerside==0)
			{
				if($linktype == 1
				)
				{
					//Left to Right, currently on the Left.
					//Outbound links
					$curlinkdirection = 'out';
				}
			}
			elseif($smallerside==1)
			{
				if($linktype == 1)
				{
					//Left to Right, currently on the Right.
					//Inbound links
					$curlinkdirection = 'in';
				}
			}
			
			//go get the links!
			//echo "curlinkdirection: $curlinkdirection<br/>";
			
			if(hasUncheckedLinks($lists[$smallerside]))
			{
				$return = fetchLinks($lists,$smallerside,$largerside,$curlinkdirection,$linktype);
				$lists = $return['lists'];
				$finalbt = $return['finalbt'];
				
				
				
				
			}
			else
			{
				$continue = false;
			}
			

			$loopcount++;
			
		}while($continue == true && count($intersect_results) == 0 && $finalbt == null);
	
		//scenarios for checking links.  should be checked AFTER seeing if there was a positive link path established
		if(!hasUncheckedLinks($lists[0]))
		{
			//echo "$a1 was a dead end. :-(<br/>";
			return(array('error'=>"$a1 {$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['text']['deadend']}. :-("));
		}
		elseif(!hasUncheckedLinks($lists[1]))
		{
			//echo "$a2 was a dead end. :-(<br/>";
			return(array('error'=>"$a2 {$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['text']['deadend']}. :-("));
		}
		elseif($finalbt != null)
		{
			return(array('result'=>$finalbt));			
		}
		
		return($finalbt_values);
		

	}
	
	function backtrace($lists,$middlepoint,$curlinkdirection)
	{
		//echo "starting backtrace... with $middlepoint as middlepoint, curlinkdirection=$curlinkdirection<br/>";
		
		//backtrace the left list
		$x = 0;
		$bt[$x] = array();	//back trace array
		$nextkey = $middlepoint;
		do
		{
			$curkey = cleanArticleKey($nextkey);
			$bt[$x][] = $curkey;
			$level = $lists[$x][$curkey]['level'];
			$nextkey = $lists[$x][$curkey]['parent'];
		}while($level != 0);

		
		//backtrace the right list
		$x = 1;
		$bt[$x] = array();	//back trace array
		$nextkey = $middlepoint;
		do
		{
			$curkey = cleanArticleKey($nextkey);
			$bt[$x][] = $curkey;
			$level = $lists[$x][$curkey]['level'];
			$nextkey = $lists[$x][$curkey]['parent'];
		}while($level != 0);

		
		
		//L to R
		if($curlinkdirection == 1)
		{
			//reverse the first part
			$first_part = array_reverse($bt[0]);
			
			//leave the second part in-tact, but remove the first key
			$second_part = $bt[1];
			
			//write both of these to an array's keys, to eliminate the duplicate middle part
			$final_bt_keys = array();
			foreach($first_part as $junk=>$val)
			{
				$final_bt_keys[$val] = 1;
			}
			foreach($second_part as $junk=>$val)
			{
				$final_bt_keys[$val] = 1;
			}
			
			//swap keys and vals
			$final_bt_keys = array_keys($final_bt_keys);
			
			
			
			
			
			
		}
		
		//echo "Final BT:<pre>";
		//print_r($final_bt_keys);
		//echo "</pre>";
		
		//get Final BT article names
		$final_bt_articles = array();
		foreach($final_bt_keys as $junk=>$key)
		{
			//echo "$key<br/>";
			for($x=0;$x<=1;$x++)
			{
				if(popfirst($lists[$x][$key]['names']) != null)
				{
					$final_bt_articles[$key] = wikiArticleNameEncode(popfirst($lists[$x][$key]['names']));
				}
			}
		}		
		
		
		
		return(array('lists'=>$lists,'finalbt'=>$final_bt_articles));
	}
	
	function recursivelyRemoveFromList($lists,$badkey)
	{
		//echo "lists before removing '$badkey':<pre>";
		//print_r($lists);
		//echo "</pre>";
		
		for($x=0;$x<count($lists);$x++)
		{
			if($lists[$x][$badkey] != null)
			{
				//if this side has an entry for the bad key...
				//remove the bad key entry
				unset($lists[$x][$badkey]);
				echo "calling destroyParentByKey for first time, $keys[$x]$badkey, listside is $x<br/>";
				$lists[$x] = destroyParentByKey($lists[$x],$badkey);
			}
		}
		
		
		//echo "lists after removing '$badkey':<pre>";
		//print_r($lists);
		//echo "</pre>";
		
		//exit();
		
		return $lists;
	}
	
	function destroyParentByKey($list,$badparentkey)
	{
		//echo "IN destroyParentByKey, $badparentkey<br/>";
		$keys = array_keys($list);
		for($x=0;$x<count($keys);$x++)
		{
			if($list[$keys[$x]]['parent'] == $badparentkey)
			{
				//echo "$keys[$x] parent is: " . $list[$keys[$x]]['parent'] . "<br/>";
				//echo "calling destroyParentByKey, $keys[$x], current key<br/>";
				$list = destroyParentByKey($list,$keys[$x]);
				unset($list[$keys[$x]]);
			}
		}
		
		return $list;
	}

	//check to see that there are some links that are still not marked as "done"
	function hasUncheckedLinks($list)
	{
		//only checking one list side per call
		foreach($list as $l)
		{
			if($l['done'] == 0)
			{
				return true;
			}
		}
		return false;
		
		
	}
	
	
	//
	function fetchLinks($lists,$smallerside,$largerside,$curlinkdirection,$linktype)
	{
		//go over all the links on the smaller side, until we find one that is not 'done'.
		//load a list of it's links. If any of them exist on the larger side, we have a match!
		
		$smallersidekeys = array_keys($lists[$smallerside]);

		for($x=0;$x<count($smallersidekeys);$x++)
		{
			$curitem = $smallersidekeys[$x];
			if($lists[$smallerside][$curitem]['done'] == true)
			{
				//skip it, its already been done!
				

			}
			else
			{
				//load and check it baby!  YEAH BABY YEAH!!!
				//Pop First result:
				$popfirstresult = popfirst($lists[$smallerside][$curitem]['names']);
				//echo "curitem: $curitem<br/>";
				//echo "popfirstresult:$popfirstresult<br/>";
				$curlinks = getArticleLinks($popfirstresult,$curlinkdirection);
				
				//if GLOBALS[randomalternatepath] is true, then shuffle the curlinks to make them appear in a more random order!
				if($GLOBALS['randomalternatepath'])
				{
					$curlinks_copy = $curlinks;
					$curlinks_copy_keys = array_keys($curlinks_copy);

					shuffle($curlinks_copy_keys);

					$curlinks = array();
					foreach($curlinks_copy_keys as $junk=>$val)
					{
						$curlinks[$val] = 1;
					}
				}
				
				//set this item as being done to 'true' so it is not attempted on the next pass
				$lists[$smallerside][$curitem]['done'] = true;
				
				foreach($curlinks as $newlink => $junk)
				{
					$allowarticle = true;
					if($GLOBALS['skips'][cleanArticleKey($newlink)] != null)
					{
						//skip article if it is in the skips list
						$allowarticle = false;
					}
					if($GLOBALS['allowyears'] == false && preg_match('/\d\d\d\d/i',cleanArticleKey($newlink)))
					{
						//skip article if allowyears is unchecked, and it matches a 4 digit year, like 2009 or 2010
						$allowarticle = false;
					}
					//if the new article is on the skip list, don't let it be added to either side
					if($allowarticle == true)
					{
						//new link on smaller side
						if($lists[$smallerside][cleanArticleKey($newlink)] == null)
						{
							//$lists[0] = array(cleanArticleKey($a1)=>array('level'=>0,'done'=>false,'names'=>array($a1=>1)));

							//20100711 - new version with cleanArticleKey and names fix
							$lists[$smallerside][cleanArticleKey($newlink)] = array('level'=>($lists[$smallerside][$curitem]['level'] + 1),'done'=>false,'parent'=>cleanArticleKey($curitem),'names'=>array($newlink=>1));
							//20100711 - old version
							//$lists[$smallerside][$newlink] = array('level'=>($lists[$smallerside][$curitem]['level'] + 1),'done'=>false,'parent'=>$curitem);
						}
						
						if($lists[$largerside][cleanArticleKey($newlink)] != null)
						{

							
							
							//attempt a backtrace using $newlink as the center point
							$bt_result = backTrace($lists,$newlink,$linktype);
							$finalbt = $bt_result['finalbt'];
							$lists = $bt_result['lists'];
							if($finalbt != null)
							{
								//return block
								$return = array();
								$return['lists'] = $lists;
								$return['finalbt'] = $finalbt;
								return $return;
							}
							
							
						}
					}
					else
					{
						//echo "Skipping article <b>$newlink</b>, it was in the skip list!<br/>";
					}
					
				}
				

			}
		}	
	
		//return block
		$return = array();
		$return['lists'] = $lists;
		$return['finalbt'] = null;
		return($return);
	}

	function cleanArticleKey($article_name)
	{
		return strtolower(preg_replace('/_|\'|\s|-/iS','',$article_name));
	}

	function getArticleLinks($articlename,$curlinkdirection)
	{
		global $wikiurls;
		global $currentlang;
		global $wikicachelength;
		
		
		//extend the time limit by another minute
		echo "\n";
		set_time_limit(200);
		
		//echo "getArticleLinks: $articlename, $curlinkdirection<br/>";
		
		$foundlinks = array();
		
		if($curlinkdirection == 'out')
		{
			
			//create a new article object
			$a = new Article($articlename);
			$a->load();
			//$title 		= wikiArticleNameEncode($a->title);
			//$aliases 	= array_map('wikiArticleNameEncode',array_keys($a->aliases));
			$newlinks	= array_map('wikiArticleNameEncode',array_keys($a->links));
			
			foreach($newlinks as $newlink)
			{
				$foundlinks[$newlink] = 1;
			}
			
		}
		elseif($curlinkdirection == 'in')
		{
			
			//incoming links
			
		
			
			
			//go fetch from the web and store in the database, if its not already cached!
			//get these using the wikipedia API for a serialized php array
			//ex: http://en.wikipedia.org/w/api.php?action=query&list=backlinks&bltitle=North Korea&bllimit=10000
			$url = str_replace('[a]',urlencode(wikiArticleNameEncode($articlename)),$wikiurls[$currentlang]['api']);
			if($GLOBALS['dbg']){ echo "link to: $articlename, $url<br/>";}
			

			//go fetch from the web and store in the database, if its not already cached!
			//get these using the wikipedia API for a serialized php array
			//ex: http://en.wikipedia.org/w/api.php?action=query&list=backlinks&bltitle=North Korea&bllimit=10000
			$url = str_replace('[a]',urlencode(wikiArticleNameEncode($articlename)),$wikiurls[$currentlang]['api']);

			$serialized_data = getHTTP($url);
			$unserialized_data = unserialize($serialized_data);
			
			//echo $serialized_data;
			
			//consolidate all backlinks, and only return those with a namesapace (ns) of zero,
			// and recursively pick up on all 'redirlinks' indexes
			$backlinks = consolidateBackLinks($unserialized_data['query']['backlinks']);
			
			//selective compression for the serializedlinks column
			if($GLOBALS['usesqlcompression'])
			{
				//compression on
				$serializedlink_data = gzcompress(serialize($backlinks));
				$compressed = 1;
			}
			else
			{
				//compression off
				$serializedlink_data = serialize($backlinks);
				$compressed = 0;
			}
			
			
			
			//mysql_query($q);
			
			
			
		
		
			
			
			//echo "backlinks:<pre>";
			//print_r($backlinks);
			//echo "</pre>";
			
			
			//exit();
			
			foreach($backlinks as $link =>$junk)
			{
				$foundlinks[$link] = 1;
			}
			
			
			
		}
		
		//echo "<pre>";
		//print_r($foundlinks);
		//echo "</pre>";
		//echo "returning " . count($foundlinks) . "<br/>";
		return($foundlinks);
		
	}
	
	function consolidateBackLinks($unserialized_data)
	{
		$validbacklinks = array();
		
		if(count($unserialized_data) > 0)
		{
			foreach($unserialized_data as $item)
			{
				if($item['ns'] == 0)
				{
					$validbacklinks[$item['title']] = 1;
					
					if($item['redirlinks'] != null)
					{
					
						$result = consolidateBackLinks($item['redirlinks']);

						foreach($result as $result_item=>$junk)
						{
							$validbacklinks[$result_item] = 1;
						}
					}
				}
			
			}
		}
		return $validbacklinks;
	}
	
	//encode article names
	//ex: "United Kingdom" becomes "United_Kingdom"
	function wikiArticleNameEncode($articlename)
	{
		$articlename = str_replace(' ','_',$articlename);
		
		return ($articlename);
	}
	
	
	


    //return the first key element of the array, without removing it.  Uses a simple foreach
    function popfirst($arr)
    {
        if(!is_array($arr) || count($arr) ==0)
        {
            return null;
        }
        else
        {
            foreach($arr as $key=>$val)
            {
                //immediately return the key
                return $key;
            }
        }

        return null;
    }
	
	//TODO: Make this function way more efficient.  according to the profiler, its eating 67% of resources. BAD BAD BAD BAD BAD!
	//Objective: remove everything inside curly braces because it is part of a template box.
	//IN: the XML export portion of an article, obtained from the Special:Export page. Ex:
	//  http://en.wikipedia.org/wiki/Special:Export/North_Korea		
	//OUT:
	//	the exact same thing, except everything within a double curly brace section has been completely removed. 
	//	curly brace sections are template sections.  They insert links that aren't normally found in the page
	//	20100818 - this version should be faster!
	function scrubTemplate($text)
	{
		
		$split_array = preg_split('/(\{\{|\}\})/i',$text,-1,PREG_SPLIT_DELIM_CAPTURE);
		
		//loop over each part of $split_array, tracking the count of {{ and }}
		// {{ Adds 1 to stack counter
		// }} subtracts one from stack counter
		$cleaned_text = "";
		$stackcounter = 0;
		foreach($split_array as $split_part)
		{
			if($split_part == '{{')
			{
				$stackcounter++;
			}
			elseif($split_part == '}}')
			{
				$stackcounter--;
			}
			else
			{
				if($stackcounter == 0)
				{
					$cleaned_text .= $split_part;
				}			
			}		
		}
		return $cleaned_text;
	}
	
	//following function was taken from:
	//http://www.webcheatsheet.com/PHP/get_current_page_url.php
	function curPageURL() 
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") 
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} 
		else 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	
	function getContactID()
	{
		return(sha1(date('Y-m-d') . 'zomgomomomomgomgomgSECRAT!!!'));
	
	}
	
?>