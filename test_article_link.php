<?php
	require_once('settings.inc.php');
	require_once('article.class.php');
	require_once('functions.inc.php');
	

	
	//allow immediate flushing of the output buffers
	ob_implicit_flush();
	ini_set('output_buffering',0);

	
	$starttime = getmicrotime();
	
	$dbg = 0;
	if($_REQUEST['dbg'])
	{
		//echo "dbg is 1";
		$GLOBALS['dbg'] = true;
	}
	
	
	
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
	
	//hardcode linktype to 1, no matter what!
	$linktype = 1;
	
	//calculate if submit button seconds are valid to prevent search engines from killing the site in a year from old links
	$submitok = false;
	$submitarrow = false;
	$submitvalue = '';
	//if submit is set, and it is numeric
	if($_REQUEST['submit'])
	{
		$submitvalue = $_REQUEST['submit'];
		
		
		//if the submit timestamp is within the designated range
		if(checkKey($submitvalue))
		{
			$submitok = true;
		}
		else
		{
			$submitarrow = true;
		}
	}
	

	
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta charset="utf-8" />
<?php
	//the following is to modify the title of the link, so it is more interesting to people on facebook
	$temp_a1 = htmlentities($_REQUEST['a1'],ENT_NOQUOTES,'UTF-8');
	$temp_a2 = htmlentities($_REQUEST['a2'],ENT_NOQUOTES,'UTF-8');
	if(trim($temp_a1) != '' && trim($temp_a2) != '')
	{
		$modifiedtitle = $temp_a1 . ' -> ' . $temp_a2 . " :: ";
	}
?>
<title><?=$modifiedtitle?><?=$wikiurls[$GLOBALS['currentlang']]['text']['title']?></title>



<link type="text/css" href="css/eggplant/jquery-ui-1.8.2.custom.css" rel="stylesheet" />	
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.2.custom.min.js"></script>

<!-- ga -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-109861705-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-109861705-1');
</script>
<!-- -->

</head>
<body style="font-family:Segoe UI,Arial,sans-serif">

<h3 style="border:2px solid gray;padding:5px;font-size:12px;">2018-02-26: A much improved version of DegreesOfWikipedia has been released by a different author, with visual rendering.  Visit <a style="text-decoration:none;" target="_blank" href="https://www.sixdegreesofwikipedia.com/"><span style="text-decoration:underline;">Six</span>DegreesOfWikipedia.com</a> !</h3>


<?php
	
?>

<?php
	require_once('language_choice.inc.php');
?>

<form>
	<br/>
	<!-- about text -->
	<span style="font-size:12px;">
	<?php echo $wikiurls[$GLOBALS['currentlang']]['text']['about']; ?>
	<br/>
	</span>
	
	
	<br/>
	<table>
	
		<tr>
			<td>a1:</td>
			<td><input type="textbox" id="a1" name="a1" value="<?=htmlentities($_REQUEST['a1'],ENT_NOQUOTES,'UTF-8')?>" size="40"/></td>
			<td>
				<select name="linktype">
					<!--
					<option value="0" <?php if($_REQUEST['linktype'] == 0) echo ' selected="selected"'?>>a1 &lt;-&gt; a2 (ANY)</option> 
					-->
					<option value="1" <?php if($_REQUEST['linktype'] == 1) echo ' selected="selected"'?>>a1 -&gt; a2 (L to R)</option> 
					<!--
					<option value="2" <?php if($_REQUEST['linktype'] == 2) echo ' selected="selected"'?>>a1 &lt;- a2 (R to L)</option> 
					<option value="3" <?php if($_REQUEST['linktype'] == 3) echo ' selected="selected"'?>>&lt;=&gt; Mutual</option> 
					-->
				</select>
			</td>
			<td>a2:</td>
			<td><input type="textbox" id="a2" name="a2" value="<?=htmlentities($_REQUEST['a2'],ENT_NOQUOTES,'UTF-8')?>" size="40"/></td>
		</tr>
		<tr>
			<td></td>
			<td><a href="#" onclick='$.get("s.php", {qt:"rand",l:"<?=$GLOBALS['currentlang']?>"} ,function(data){$("#a1").val(data);});'><?php echo $wikiurls[$GLOBALS['currentlang']]['text']['random_article']; ?></a></td>
			<td></td>
			<td></td>
			<td><a href="#" onclick='$.get("s.php", {qt:"rand",l:"<?=$GLOBALS['currentlang']?>"} ,function(data){$("#a2").val(data);});'><?php echo $wikiurls[$GLOBALS['currentlang']]['text']['random_article']; ?></a></td>
			<td></td>
		</tr>
	
	</table>
	
	
	<br/>
	<?php echo $wikiurls[$GLOBALS['currentlang']]['text']['additional_options']; ?>:
	<br/>
	<a href="?a1=<?=urlencode($_REQUEST['a2']);?>&linktype=<?=$linktype?>&a2=<?=urlencode($_REQUEST['a1'])?>&skips=<?=urlencode($_REQUEST['skips'])?>&submit=<?=$submitvalue?>&currentlang=<?=$GLOBALS['currentlang']?>"><?echo $wikiurls[$GLOBALS['currentlang']]['text']['article_direction'];?></a>
	<br/>
	<!-- Templates screening checkbox -->
	<?=$wikiurls[$GLOBALS['currentlang']]['text']['skip']?>:<br/>
	<textarea name="skips" rows="5" cols="50"><?=htmlentities($_REQUEST['skips'],ENT_NOQUOTES,'UTF-8')?></textarea>
	<br/>
	<input type="checkbox" name="allowsideboxes" <?php if($_REQUEST['allowsideboxes']=='1'){echo "checked=\"checked\"";}?> value="1" /><?php echo $wikiurls[$GLOBALS['currentlang']]['text']['template_check']; ?>
	<br/>
	<?php
		//blocking random path, due to the high server load that would be created :-p
		/*
		<!--
		<input type="checkbox" name="randompath" <?php if($_REQUEST['randompath']=='1'){echo "checked=\"checked\"";}?> value="1" /><?php echo $wikiurls[$GLOBALS['currentlang']]['text']['shuffle']; ?>
		<br/>
		-->
		*/
	?>
	<input type="checkbox" name="allowyears" <?php if($_REQUEST['allowyears']=='1'){echo "checked=\"checked\"";}?> value="1" /><?php echo $wikiurls[$GLOBALS['currentlang']]['text']['allowyears']; ?>
	<br/>
	<br/>
	<input style="vertical-align:middle;" type="submit" value="go"/>&nbsp;&nbsp;&nbsp;
	<?php
		if($submitarrow)
		{
			echo "<span style=\"color:#FF0000;font-size:50px;vertical-align:middle;\">&larr;</span>";
		}
	?>
	<input type="hidden" name="submit" value="<?=makekey();?>"/>
	<input type="hidden" value="<?=$GLOBALS['currentlang']?>" name="currentlang"/>
	
	
	

	
	
</form>



<script type="text/javascript">
	$(function() {
		var cache = {};
		//$( "#birds" ).autocomplete({
		$( "#a1,#a2" ).autocomplete({
			delay: 300,
			minLength: 1,
			source: function(request, response) {
				if ( request.term in cache ) {
					response( cache[ request.term ] );
					return;
				}
				
				$.ajax({
					url: "s.php",
					dataType: "json",
					data: "l=<?=$GLOBALS['currentlang']?>&t=" + request.term + "&qt=ac",
					//data: "&term=" + this.text(),
					success: function( data ) {
						cache[ request.term ] = data;
						response( data );
					}
				});
			}
		});
	});
</script>









<?php


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
			echo htmlentities($a1,ENT_NOQUOTES,'UTF-8') . " was not a valid article.<br/>";
		}
		elseif(!$a2_obj->validate())
		{
			echo htmlentities($a2,ENT_NOQUOTES,'UTF-8') . " was not a valid article.<br/>";
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
			
			echo "{$wikiurls[$GLOBALS['currentlang']]['text']['choose_language']} <a href=\"$a1_url\">" . htmlentities($a1,ENT_NOQUOTES,'UTF-8') . "</a> to <a href=\"$a2_url\">" . htmlentities($a2,ENT_NOQUOTES,'UTF-8') . "</a>...<br/>";
			
			
			
			
			
			//flush the output buffers
			ob_flush();
			flush();
			
			//20100724 - passing the Title of the article to the link function
			$finalbt_result = doLink(htmlspecialchars_decode($a1_obj->title),htmlspecialchars_decode($a2_obj->title),$linktype);
			
			if($finalbt_result['result'])
			{
				$finalbt = $finalbt_result['result'];
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
			elseif($finalbt_result['error'])
			{
				echo $finalbt_result['error'];
			}
			else
			{
				echo "unknown error.<br/>";
			}
			
			
			//echo "count of finalbt_result is:" . count($finalbt_result);
			$degree_count = count($finalbt_result['result']) - 1;
			
			
			
			
			
			//doLink($a1,$a2,$linktype);
		}
	}
	
	?>

	<?php
	//memory usage
	echo "<br/>";
	echo "{$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['text']['peakmemory']}: " . number_format(memory_get_peak_usage(),0,'.',',') . "<br/>" ;
	
    //calculate and print out the time it took to generate the page
    $endtime = getmicrotime();
    $diff = $endtime - $starttime;
    echo "\n{$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['text']['pagegeneratedin']} " . round($diff, 4) . " sec <br>";
	?> 
	
	<br/>
	<br/>
	<br/>
	<br/>
	Wikipedia&reg; is a registered trademark of the <a href="http://en.wikipedia.org/wiki/Wikimedia_Foundation">Wikimedia Foundation</a>, Inc., a non-profit organization
	<br/>
	<a href="http://wikimediafoundation.org/wiki/Support_Wikipedia/en">Please Donate</a> to support Wikipedia
	<br/>
	<br/>
	<a href="./contact.php?contactid=<?=getContactID()?>"><b><?=$GLOBALS['wikiurls'][$GLOBALS['currentlang']]['text']['contact']?></b></a>
	<br/>
	<br/>
	(c)<?=date('Y')?> <a href="http://lkdev.com">LKDev</a>&nbsp;&nbsp;|&nbsp;&nbsp; GitHub - <a style="text-decoration:none;" href="https://github.com/LKDev5/degreesofwikipedia" target="_blank">LKDev5/degreesofwikipedia</a>
	<br/>
	<?php
		//<br/>
		//Written by Dave Bachtel
	?>
	<br/>
	Thanks to Adam, DaveM, Josh Pettett, and Doug Shaffren for testing, and to Janson Feng for jQuery helps!
	<br/>
	And many thanks to Fred Gutierrez for some much needed security butt kicking.
	<br/>
	
</body>
</html>