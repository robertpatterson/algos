

<?php

/* 10-10-06
in mapProc
parse for shape
upload the .gif to its proper place.
*/

require_once('../common/class/DatabaseConnection.inc.php');

//echo "got here";

if(isset($_POST['task']))
{
	$task = $_POST['task'];
	switch ($task)
	{
		case "procRefs":
			procRefs();
			break;
		case "mapProc":
			mapProc();
			break;
		case "uploadGIF":
			uploadGIF();
			break;
		default:
			echo "No task specified.";
	}
}
else
{
	echo "What task?";
}

function uploadGIF()
{
	if ($_FILES["gifFile"]["error"] > 0)
	{
		  echo "Error: " . $_FILES["gifFile"]["error"] . "<br />";
		  return;
	}
	$size = $_FILES["gifFile"]["size"];
	if($_FILES["gifFile"]["size"] > 200000 || $_FILES["gifFile"]["size"] == 0)
	{
	  	echo "File size is either 0 or too large (over 200000 characters).";  
	}
	$originalName = $_FILES["gifFile"]["name"];   // The original name of the file
	$ar = explode(".",$originalName);
	if($ar[1] != 'gif')
	{
	  	echo "File type is incorrect.";
	}
//	$fileTempName = $_FILES["gifFile"]["tmp_name"];   // The name it is under at present
	
	$algoAlias = $_POST['aliasGIF'];
	$ret = checkAlias($algoAlias);  // Check that alias is correct and known to Joomla
	if(!$ret)
	{
		echo "We don't have an algorithm with the alias $algoAlias.";
		return;
	}
	echo "<br><h2>Flowchart Upload Results</h2><br>";

	$uploaddir = "./" . $fileTempName;
	$destination = "../images/" . $algoAlias . '.gif';   // $originalName;
//	$destination = $algoAlias . '.gif';  
//	$destination = "psychopharm.mobi/joomla/images/stories/" . $originalName;
	
	
	// Delete old file	
//	$fh = fopen($destination, 'w') or die("can't open file");
//	fclose($fh);
//	unlink($destination);

	if (move_uploaded_file($_FILES["gifFile"]["tmp_name"], $destination)) 
	{
		echo "File is valid, and was successfully uploaded.\n";
	} 
	else 
	{
		echo "Possible file upload attack. Uploaded file=" . $originalName . ' Destination=' . $destination . "\n";
	}
}


function mapProc()
{
	$link = DatabaseConnection::algo();
	$target_path = "/uploads/";
	if(!checkAlias($_POST['aliasMaps'])){
		return;
	}	
	$target_path = $target_path . basename( $_FILES['uploadedfile2']['name']); 
	if ($_FILES["uploadedfile"]["error"] > 0){
		  echo "Error: " . $_FILES["uploadedfile2"]["error"] . "<br />";
		  return;
	}
	if($_FILES["uploadedfile2"]["size"] > 50000){
	  	echo "File size is too large (over 50000 characters).";  
	}
	if( $_FILES["uploadedfile2"]["size"] == 0){
	  	echo "File size is either 0 characters).";
	}
	$checkName = $_FILES["uploadedfile2"]["name"];
	$ar = explode(".",$checkName);
	if($ar[1] != 'cmapx')
	{
	  	echo "File name extension is not 'cmapx'.";
	}
	$inputFile = $_FILES["uploadedfile2"]["tmp_name"];

	echo "<br><h2>Flowchart Map Processing Report</h2><br><br>";
	mysqli_query($link,"SET NAMES 'utf8'");
	// -- process the map
	
	$lines = file("$inputFile", FILE_SKIP_EMPTY_LINES);
	$finalMap = "<map id=\"mainmap\" name=\"mainmap\">\n";
	foreach($lines as $key=>$line)
	{
		$ar = explode("id=\"",$line);  // id's have quotes
		$before = trim($ar[0]);
		$includes = trim($ar[1]);
		$ar2 = explode("\"",$includes);
		$id = trim($ar2[0]);
		if($id != 'G' && $id != '')
		{
			$ar = explode("coords=",$line);  // Coordinates have quotes
			$quotedCoords = trim($ar[1]);		
			$ar = explode("\"",$quotedCoords);
			$unquotedCoords = $ar[1];
		//	$jsFunction = "\" href=\"javascript:jump(&#39;". $id . "&#39;,&#39;" . $unquotedCoords ;
			$jsFunction = "\" href=\"javascript:jump('". $id . "','" . $unquotedCoords ;
			$finalMap = $finalMap . "<area id=\"". $id ."\" shape=\"rect\" coords=\"" . $unquotedCoords . $jsFunction . "&#39;)\" />\n";
		}
	}						
	
	
	$finalMap = $finalMap . "</map>";
//	echo "Final Map = $finalMap";
	//-------------- Save it all

	
	$sql = "UPDATE jos_categories set longtext= $finalMap WHERE alias= $algoAlias";
//	echo $finalMap;
	$ret = mysqli_query($link,$sql);
	if($ret){
	echo " File transferred to database jos_categories table.<br><br><br> $sql";
	}
	else{
	   echo "SOMETHING WENT WRONG!! Putting data in database.$sql";
	}
	echo "Below shows the image map created and stored in Joomla. See previous page for details.<br>
	<textarea cols=\"200\" rows=\"30\">$finalMap </textarea>";
	
}







function procRefs()
{
	$link = DatabaseConnection::psychopharm();
	
	if(!checkAlias($_POST['aliasRefs'])){
	    return;
	}	
	$target_path = "../uploads/";
	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
	if ($_FILES["uploadedfile"]["error"] > 0)
	{
		  echo "Error: " . $_FILES["uploadedfile"]["error"] . "<br />";
		  return;
	}
//	echo "got file.";
	if($_FILES["uploadedfile"]["type"] != 'text/plain')
	{
		echo "<br>File is not of the .txt type as required. It is ". $_FILES['uploadedfile']['type'];
		return;
	}
//	echo " File type ok<br>";
	
	if($_FILES["uploadedfile"]["size"] > 2000000 || $_FILES["uploadedfile"]["size"] == 0)
	{
	  	echo "File size is either 0 or too large (over 1 meg).";  
	}
	
	$ar = explode(".",$_FILES["uploadedfile"]["name"]);
	if($ar[1] != 'txt')
	{
	  	echo "File name extension is not 'txt'.";
	}	
	
//	 echo "File extension ok<br>";
	
	$inputFile = $_FILES["uploadedfile"]["tmp_name"];
	$algoAlias = $_POST['aliasRefs'];
//	echo $_FILES["uploadedfile"]["name"];
//	echo "<br>";
//	echo "<br><h2>References Processing Report</h2>References for the algorithm " .  $_POST['aliasRefs'] . "<br><br>";
	$outputTable = "$algoAlias" . "Refs";
	mysqli_query($link,"SET NAMES 'utf8'");
	$sql = "DROP TABLE IF EXISTS `$outputTable`;";
	mysqli_query($link,$sql, $db);
	$sql = "
		CREATE TABLE IF NOT EXISTS `$outputTable` (
	  `ref` varchar(800) NOT NULL,
	  `endNoteNum` int(11) NOT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;	";
	mysqli_query($link,$sql);
 //   echo "Created $outputTable table<br>";
	$lines = file("$inputFile", FILE_SKIP_EMPTY_LINES);
	$c = 0;
//	echo "Starting foreach<br>";
	foreach($lines as $key=>$line)
	{
//	    echo "inForeach $line<br>";
		if($c == 0)
		{
			$temp[0] = "nbnbnbnb\n";
			$c++;
			$temp[1] = $line;
		}
		else
			$temp[$c] = $line;
		$c++;
	}
	$lines = $temp;
	$lines[] = "Reference Type: xxx";
	$type = 'xxy';
	$refNum = '';
	$recNum = '';
	$title = '';
	$author = '';
	$year = '';
	$accNum = '';
	$pub = '';
	$journal = '';
	$vol == '';
	$pages = '';
	$ref == '';
	$count = 0;
	$abstract = 0;
	foreach($lines as $key=>$line)   // Each line has: typeName: the data
	{
        $remove[] = "'";
   //     $remove[] = "-"; // just as another example

        $line = str_replace( $remove, "&#39;	", $line );		//&#39;	    
		$ar = explode(": ",$line);

		$ar[0] = trim($ar[0]);
		$ar[1] = trim($ar[1]);
//		echo "#0 ref type= $ar[0] <br>";
//		echo "#1 value = $ar[1]; <br><br>";
//		echo " #2 type= $type <br>";
		switch ($ar[0])
		{
			case "Reference Type":
				if($type == 'xxy')  // first cycle. No data in the vars yet.
				{
					$type = $ar[1];
					break;
				}
				switch($type)  // runs when get to next item; it stores the item that has been completed
				{
					case 'Journal Article':
						if($accNum != '' && $abstract == 1){
							$abstr =  "<a id=\'a_$accNum\' href=\"javascript:abstract(\'$accNum\')\">Abstract</a><div id=\'div_$accNum\' class=\'hideIt\'></div>";
						}else{
							$abstr =  "(Abstract not available.)";
						}
						$ref = "$author: $title. $journal $year; $volume:$pages $abstr";
						break;
					default:
						$ref = "$author: $title. $publisher, $year";
						break;							
				}
//////				echo "$recNum: $ref<br>\n";
				$count++;
				$sql = "INSERT INTO `$outputTable` SET ref = '$ref', endNoteNum = $recNum";
//				echo "$recNum: $sql<br>";
//				echo "$sql <br>";
				mysqli_query($link,$sql);
				$recNum = '';
				$title = '';
				$author = '';
				$year = '';
				$accNum = '';
				$pub ='';				
				$type = $ar[1];
				$journal = '';
				$vol = '';
				$pages = '';
				$ref = '';
				$abstract = 0;
				break;
			case "Record Number":
				$recNum = $ar[1];
				break;
			case "Short Title":
				$arB = explode(":",$ar[1]);
				$title = $arB[0] . ": " . $arB[1];
				break;
			case "Author":
				$auAr = explode(", ",$ar[1]);
				$cnt = sizeof($auAr);
				switch($cnt)
				{
					case '0':
						$author = '';
						break;
					case '1':
						$author = "$auAr[0]";
						break;
					case '2':
						$author = "$auAr[0], $auAr[1]";
						break;
					case '3':
						$author = "$auAr[0], $auAr[1], $auAr[2]";
						break;
					default:
						$author = "$auAr[0], $auAr[1], $auAr[2] et. al.";
						break;
				}
			case "Year":
				$year = $ar[1];
				break;
			case "Accession Number":
				$accNum = $ar[1];
				break;
			case "Publisher":
				$publisher = $ar[1];
				break;
			case "Journal":
				$journal = $ar[1];
				break;
			case "Volume":
				$volume = $ar[1];
				break;
			case "Pages":
				$pages = $ar[1];
				break;
			case "Abstract":
				$abstract = 1;
				break;
		}
	}
	echo "<br><br>References processed. <br>$count references were processed.";
}

function checkAlias($alias)
{
    $result = false;
	switch ($alias){
		case "bpdep":
			$result = true;
			break;
		case "ptsd":
			$result =  true;
			break;
		case "schiz":
			$result =  true;
			break;
		case "anxiety":
			$result =  true;
			break;
		case "pdep":
			$result =  true;
			break;
		case "mania":
			$result =  true;
			break;
		case "gad":
			$result =  true;
			break;
		case "npdep":
			$result =  true;
			break;
		case "adhd":
			$result =  true;
			break;		default:
			$result =  false;
	}
		if(!$result){
		    echo "We don't have an algorithm whith the alias $alias.";
	    	return false;
	    }
	    else{
	        return true;
	    }
}




?>





