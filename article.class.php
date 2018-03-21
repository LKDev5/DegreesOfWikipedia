<?php

require_once('curl.class.php');

class Article
{
	//article class
	var $alias = null;
	var $aliases = array();
	
	var $title = array();
	
	var $lang;
	var $loaded = false;
	var $links = array();
	
	
	function article($alias)
	{
		$this->alias = wikiArticleNameEncode($alias);
		//$this->aliases = array($alias);	//not neaded.  this is set in the load() method
		$this->lang = $GLOBALS['currentlang'];
	}
	
	
	//check that this is a valid article
	function validate()
	{
		if($this->loaded == false)
		{
			$this->load();
		}
		
		$xml = $this->fetchXML($this->alias);
		if($GLOBALS['dbg'])
		{
			echo "this->alias:$this->alias<br/>";
			echo "XML:$xml<br/>";
		}
		
		if(preg_match('/<text xml:space="preserve".+>/iS',$xml))
		{
			if($GLOBALS['dbg']) echo "Article $this->alias exists!<br/>";
			return true;
		}
		else
		{
			if($GLOBALS['dbg']) echo "Article $this->alias doesn't exist!<br/>";
			return false;
		}	
	}
	
	
	//important function
	//load all aliases, and get all links for the current article
	function load()
	{
		
		$articlename = $this->alias;
		
		
		do
		{
			//this if block is automatically skipped the first time through, since $redirect_article is blank
			if($redirect_article)
			{
				
				//if there was a redirect for the previous article, add it to the list
				
				$this->aliases[$articlename] = 1;
				$articlename = $redirect_article;
			}
			
			$xml = $this->fetchXML(wikiArticleNameEncode($articlename));
			
			if($GLOBALS['dbg'])
			{
				echo "xml: $xml<br/>";
			}
			
		}while($redirect_article = $this->checkRedirectsTo($xml));
		
		//echo "articlename is $articlename<br/>";
		$this->title = wikiArticleNameEncode($this->parseTitle($xml));
		
		//echo "parsed title = $this->title<br/>";
		
		
		$this->links = $this->parseLinks($xml);
		
		$this->loaded = true;
	}
	
	//parse out the title of the page yo!  it's in <title></title> tags!
	function parseTitle($xml)
	{
		preg_match('/<title>(.+)<\/title>/iS',$xml,$matches);
		
		return($matches[1]);
	}
	
	function checkRedirectsTo($xml)
	{
		//<text xml:space="preserve">#REDIRECT [[United Kingdom]]{{R from abbreviation}}</text>
		preg_match('/#REDIRECT \[\[(.+)\]\]/iS',$xml,$matches);
		if($GLOBALS['dbg'])
		{			
			//echo "checking redirects to:<pre>";
			//print_r($matches);
			//echo "</pre>";
		}
		
		return($matches[1]);
	}
	
	//get a list of links this given article
	function parseLinks($xml)
	{
		
		//if we are trying to avoid showing boxes and templates, scrub them here
		if($GLOBALS['allowsideboxes'] == false)
		{
			//remove template boxes
			//echo "scrubTemplate activated!!!!<br/>";
			$xml = scrubTemplate($xml);
		}
		
		
		//only use whats before the references section.  split on ==References==, 
		// and use the first part
		$xmlparts = explode('==References==',$xml);
		if(count($xmlparts) > 1)
		{
			$xml = $xmlparts[0];
		}
		
		//match all links
		preg_match_all('/\[\[(.+)\]\]/iUS',$xml,$matches);		
		
		
		
		
		$all_links = $matches[1];
		
		$usable_links = array();
		foreach($all_links as $link)
		{
			//echo "link out: $link<br/>"; 
			//In general (for now at least), we don't want articles that have a colon in the name ':'
			//an optional eventual exception to this may be the 'Category:' keyword.
			if(strstr($link,':'))
			{
				//skip this link!  We don't want ones with colons in the article name for now!
			}
			else
			{	
				//check for links that have a different tag than the article
				//if the pipe symbol is present, split on it, and keep the first part
				if(strstr($link,'|'))
				{
					$parts = explode('|',$link);
					
					$link = $parts[0];
				}
				
				if(preg_match('/([^#]+)?#/S',$link,$matches))
				{
					$link = $matches[1];
				}
				
				//if the frist character is not a '#', meaning the article links to itself.
				if(substr($link,0,1) != '#')
				{
					//add it to the usable links list!
					$usable_links[$link] = 1;
				}
			}		
		}
		
		//ksort($usable_links);
		
		
		
		if($GLOBALS['dbg'])
		{
			//echo count($usable_links) . " usable links";
			//echo "<pre>";
			//print_r($usable_links);
			//echo "</pre>";
		}
		
		return($usable_links);
		
		
		
	
	}
	
	
	//pass in the article name explicitly here, because we might be looking 
	// for possible redirects
	function fetchXML($articlename)
	{
	
		global $wikicachelength;
		
		global $wikiurls;
		global $currentlang;
		
		
		//try to retrieve the xml from the web
		$articlename = wikiArticleNameEncode($articlename);
		
		//fetch the XML from the website, and cache it in the database
		if($wikiurls[$this->lang]['xml'] == false)
		{
			die('error, unknown lang ' . $this->lang . ' for xml request');
		
		}
		else
		{
			$url = str_replace('[a]',urlencode($articlename),$wikiurls[$this->lang]['xml']);
			//$xml = $this->getHTTP($url);
			$xml = getHTTP($url);
			if($GLOBALS['dbg'])
			{
				echo "xml after getHTTP call:<br/>";
				echo $xml;
				
			}
			if($xml)
			{
				
				return $xml;
			}			
		}

	}
}



?>