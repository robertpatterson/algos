

<?php


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
		default:
			echo "No task specified.";
	}
}
else
{
	echo "What task?";
}

function procRefs()
{
	$link = DatabaseConnection::psychopharm();
	$target_path = "../uploads/";
	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
	if ($_FILES["uploadedfile"]["error"] > 0)
	{
		  echo "Error: " . $_FILES["uploadedfile"]["error"] . "<br />";
		  return;
	}
	if($_FILES["uploadedfile"]["type"] != 'text/plain')
	{
		echo "<br>File is not of the .txt type as required. It is ". $_FILES['uploadedfile']['type'];
		return;
	}
	if($_FILES["uploadedfile"]["size"] > 1000000 || $_FILES["uploadedfile"]["size"] == 0)
	{
	  	echo "File size is either 0 or too large (over 1 meg).";  
	}
	if($_FILES["uploadedfile"]["name"] != 'refs.txt')
	{
	  	echo "File name is incorrect. 03";
	}
	$inputFile = $_FILES["uploadedfile"]["tmp_name"];
	$algoAlias = $_POST['alias'];
	$ret = checkAlias($algoAlias);
	if(!$ret)
	{
		echo "We don't have an algorithm whith the alias $algoAlias.";
		return;
	}
	echo "<br><h2>References Processing Report</h2>References for the algorithm $algoAlias.<br><br>";
	$outputTable = "$algoAlias" . "Refs";
//	$db = mysqli_connect('localhost','mhc','theminmhc');
//	mysqli_select_db('psychopharm',$db);
	mysqli_query($link,"SET NAMES 'utf8'");
	$sql = "DROP TABLE IF EXISTS `$outputTable`;";
	mysqli_query($link,$sql);
	$sql = "
		CREATE TABLE IF NOT EXISTS `$outputTable` (
	  `ref` varchar(500) NOT NULL,
	  `endNoteNum` int(11) NOT NULL
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;	";
	mysqli_query($link,$sql);
	$lines = file("$inputFile", FILE_SKIP_EMPTY_LINES);
	$lines[] = "Reference Type: xxx";
	$type = '';
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
	foreach($lines as $key=>$line)
	{
		$ar = explode(": ",$line);
		$ar[0] = trim($ar[0]);
		$ar[1] = trim($ar[1]);
		switch ($ar[0])
		{
			case "Reference Type":
				if($type == '')
				{
					$type = $ar[1];
					break;
				}
				switch($type)
				{
					case 'Journal Article':
						$ref = "$author: $title. $journal $year; $volume:$pages <a id=\'a_$accNum\' href=\"javascript:abstract(\'$accNum\')\">Abstract</a><div id=\'div_$accNum\' class=\'refBox\'></div>";
						break;
					default:
						$ref = "$author: $title. $publisher, $year";
						break;							
				}
				echo "$ref<br>\n";
				$count++;
				$sql = "INSERT INTO $outputTable SET ref = '$ref', endNoteNum = '$recNum'";
				mysqli_query($link,$sql);
//				echo "$recNum $sql<br>";
				$recNum = '';
				$title = '';
				$author = '';
				$year = '';
				$accNum = '';
				$pub ='';				
				$type = $ar[1];
				$journal = '';
				$vol == '';
				$pages = '';
				$ref == '';
				break;
			case "Record Number":
				$recNum = $ar[1];
				break;
			case "Short Title":
				$title = $ar[1];
				break;
			case "Author":
				$auAr = explode("; ",$ar[1]);
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
			case "Journal":
				$journal = $ar[1];
			case "Volume":
				$volume = $ar[1];
			case "Pages":
				$pages = $ar[1];
		}
	}
	echo "<br><br>$count references were processed.";
}

function checkAlias($alias)
{
	switch ($alias)
	{
		case "bpdep":
			return true;
		case "ptsd":
			return true;
		default:
			return false;
	}
}

function mapProc()
{
	$link = DatabaseConnection::psychopharm();
	$target_path = "../uploads/";
	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
	if ($_FILES["uploadedfile"]["error"] > 0)
	{
		  echo "Error: " . $_FILES["uploadedfile"]["error"] . "<br />";
		  return;
	}
	if($_FILES["uploadedfile"]["type"] != 'text/plain')
	{
		echo "<br>File is not of the .txt type as required. It is ". $_FILES['uploadedfile']['type'];
		return;
	}
	if($_FILES["uploadedfile"]["size"] > 5000 || $_FILES["uploadedfile"]["size"] == 0)
	{
	  	echo "File size is either 0 or too large (over 5000 characters).";  
	}
	$checkName = $_FILES["uploadedfile"]["name"];
	$ar = explode(".",$checkName);
	if($ar[1] != 'cmapx')
	{
	  	echo "File name is incorrect. 04";
	}
	$inputFile = $_FILES["uploadedfile"]["tmp_name"];
	$algoAlias = $_POST['alias'];
	$ret = checkAlias($algoAlias);
	if(!$ret)
	{
		echo "We don't have an algorithm whith the alias $algoAlias.";
		return;
	}
	echo "<br><h2>Flowchart Processing Report</h2><br><br>";
//	$db = mysqli_connect('localhost','mhc','theminmhc');
//	mysqli_select_db('psychopharm',$db);
	mysqli_query($link,"SET NAMES 'utf8'");
	// -- process the map
	
	$lines = file("$inputFile", FILE_SKIP_EMPTY_LINES);
	$finalMap = "<map id=\"mainmap\" name=\"mainmap\">\n";
	foreach($lines as $key=>$line)
	{
		$ar = explode("id=",$line);  // id's do not have quotes
//		$before = trim($ar[0]);
		$includes = trim($ar[1]);
		$ar2 = explode(" ",$includes);
		$id = trim($ar[0]);
		
		$ar = explode("coords=",$line);  // Coordinates have quotes
//		$before = trim($ar[0]);
		$quotedCoords = trim($ar[1]);

/*		$ar = explode("href=",$line);
//		$before = trim($ar[0]);
		$includes = trim($ar[1]);
		$ar2 = explode(" ",$includes);
		$id = trim($ar[0]);
*/		
		$ar = explode("\"",$quotedCoords);
		$unquotedCoords = $ar[1];
		$finalMap = $finalMap . "<area id=$id shape=\"rect\" coords=$quotedCoords href=\"javascript: jump('$id',$unquotedCoords)\" />\n";
	}						
	
	
	
	//-------------- Save it all
	$sql = "insert into imaps set algoAlias='$algoAlias', original='$inputFile', final='$finalMap'";
	mysqli_query($link,$sql);
	echo $finalMap;

}
?>





