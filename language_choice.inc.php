<?php
	//present a selection of available languages
	require_once('settings.inc.php');

	echo "{$wikiurls[$GLOBALS['currentlang']]['text']['choose_language']}<br/>";

	
	//print languages in a nice table
	echo "<table>";
	echo "<tr>";
	foreach($GLOBALS['wikiurls'] as $lang=>$vals)
	{
		echo "<td align=\"center\">";
		if($lang == $GLOBALS['currentlang'])
		{
			echo "<b>$lang</b>";
		}
		else
		{
			echo "<a style=\"text-decoration:none;\" href=\"?currentlang=$lang\">$lang</a>";
		}
		echo "</td>";
	}
	echo "</tr>";
	
	//print some flags!
	echo "<tr>";
	foreach($GLOBALS['wikiurls'] as $lang=>$vals)
	{
		echo "<td align=\"center\" valign=\"top\">";
		$flag_images = array();
		
		foreach($vals['flags'] as $flag_image)
		{
			$flag_images[] = "<a href=\"?currentlang=$lang\"><img style=\"height:15px;border:1px solid #ddd;\" src=\"flags/$flag_image\" /></a>";
		}
		echo join("<br/>",$flag_images);
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";
?>