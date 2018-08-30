


<?php
/*
Feb 2010 Mental Health Connections, Inc. mhc@mhc.com
 Written by Robert Patterson
*/
require_once('../common/class/DatabaseConnection.inc.php');
//require 'useDatabases.php';

include 'jsonHandlers.php';
$jsonString = $_GET['JSON'];
$jsonString = stripslashes($jsonString);
$json = new Services_JSON();
$jsonObj = $json->decode($jsonString);  //'{"name":"Tammy","Age":"19"}'); //$json);

$refList = '';
$refShell = '<a id="refLabel" href="javascript:showRefDiv()">Show References</a><div id="refDiv"  class="screens_hideIt">';
switch ($jsonObj->task)
{
	case "getAlgo":
		getAlgo();
		break;
	case "getCommonInfo":
		getCommonInfo();
		break;
	case "registrationPage_2":
		registrationPage_2();
		break;
	case "getAbstract":
		getAbstract();
		break;
}



function assembleRefs($introtext)  // One article
{
//	echo $introtext;
	global $refList;
	global $refShell;
	$refList = $refShell;
	$ar = str_getcsv($introtext,'#'); // Break into array around the # signs.
//	$ar = preg_split("/#/",$introtext); // Break into array around the # signs.
	if(sizeof($ar) < 2)
		return $introtext;
	$c = 1;
	$finString = '';
	$count = 0;
	$maxCnt = sizeof($ar) -1;
//	echo "\nAR LEN = ".sizeof($ar). "\n". $introtext;
	$finStr = $ar[0];
	$introtext2 = '';
	while($count < $maxCnt)  // Each piece of the article broken around the # signs.
	{
		$upperCnt = $count + 1;
		$num = (int) $ar[$upperCnt];
		$len = strlen($num);
		if($len > 4 || $num == 0)
		{
			$num = 9999;
		}
//		echo "<br>in counter";
		addToRefList($num,$c);
		$ar[$upperCnt] = substr_replace($ar[$upperCnt],$c,0,$len);  // Replace with the sequence number
		$c++;
		$count++; 
		$finStr = $finStr.$ar[$upperCnt];	
	}
	foreach($ar as $piece)
	{
		$introtext2 = $introtext2 . $piece;
	}
	$introtext2 = $introtext2 . $refList .'</div>';
//	echo "\n ARTICLE = $introtext2\n";
	return $introtext2; //$ar[0].$ar[1].$ar[2]; //";$finString;
}

function addToRefList($num,$sequenceNum)
{
//	echo "<br>Number in addRef = $num <br>sequnceNum=$sequenceNum \n";
	$link = DatabaseConnection::psychopharm();
	global $refList;
	global $json;
	global $jsonObj;
	mysqli_query($link,"SET NAMES 'utf8'");
	$table = $jsonObj->algo . 'Refs';
	$sql = "SELECT ref from `$table` WHERE $num = endNoteNum";
//	echo "\n$sql";
	$res = mysqli_query($link,$sql);
	$rowCount = mysqli_num_rows($res);
	if($rowCount == 0)
	{
		$refList = $refList . "<br>$sequenceNum. EndNote ID=$num is unknown.";
		return;
	}
	if($rowCount > 1)
	{
		$refList = $refList . "<br>$sequenceNum. More than one ref with EndNote ID=$num.";
		return;
	}
	$obj = mysqli_fetch_object($res);
	$refList = $refList . '<br>' . $sequenceNum . '. '. $obj->ref;
}

function getAlgo()  // Send all the pages of one algo
{
	$link = DatabaseConnection::algo();
	global $json;
	global $jsonObj;
	$algo = $jsonObj->algo;
	$sql = "SELECT description, title
		FROM jos_categories
		WHERE `jos_categories`.alias = '$algo'";
	$res = mysqli_query($link,$sql);
	while($obj = mysqli_fetch_object($res))
	{
		$encodable[] = $obj;   // The alias, image and imap. Each one added successively to array
	}
	$sql = "SELECT jos_content.title, jos_content.ordering, introtext, jos_content.alias
		FROM jos_content, jos_categories
		WHERE `jos_categories`.id = `jos_content`.catid AND jos_categories.alias = '$algo'
";	
	$res = mysqli_query($link,$sql);
	while($obj = mysqli_fetch_object($res))
	{
		$introtext = assembleRefs($obj->introtext);
		$obj->introtext = $introtext;		
		$encodable2[] = $obj;   // The text about the node
	}
	$a = array("task"=>"algo","algoAlias"=>"$algo");
	$t = array($a,$encodable,$encodable2); 
//	$t = array($a,$encodable2);	
	$encod =  $json->encode($t);
	echo "$encod"; 
	


}

function getCommonInfo()  // Send all the common info pages
{
	$link = DatabaseConnection::algo();
	global $json;
	global $jsonObj;
//	global $db;
	$sql = "SELECT alias, introtext
		FROM jos_content
		WHERE catid = 9";
//	echo "$sql\n";
	$res = mysqli_query($link,$sql);
 //   echo "res is: $res";
	$rowCount = mysqli_num_rows($res);
//	echo "rowcount = $rowCount\n";
	while($obj = mysqli_fetch_object($res))
	{
//	NEED A REF LIST for common info to use this	$introtext = assembleRefs($obj->introtext);
//		$obj->introtext = $introtext;		
		$encodable[] = $obj;   // The text about the node
	}
	$a = array("task"=>"commonInfo");
	$t = array($a,$encodable); 
	$encod = $json->encode($t);	
	echo "$encod"; 
}

// http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=19398192&retmode=text&rettype=abstract


function getAbstract()
{
	global $jsonObj;
	/**
	* Initialize the cURL session
	*/
	$ch = curl_init();
	/**
	* Set the URL of the page or file to download.
	*/

//    $url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=19398192&retmode=text&rettype=abstract';	
	
	
	$url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=' . $jsonObj->pmid . '&retmode=text&rettype=abstract';
	
	
//	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=' . $jsonObj->pmid . '&retmode=XML&rettype=abstract';
//	echo $url;
	curl_setopt($ch, CURLOPT_URL,$url);
	/**
	* Ask cURL to return the contents in a variable
	* instead of simply echoing them to the browser.
	*/
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	/**
	* Execute the cURL session
	*/
	$resp = curl_exec ($ch);
	$xml=simplexml_load_string($resp);
	$abtxt = "{\"task\":\"getAbstract\",\"pmid\":\"$jsonObj->pmid\",\"abstract\":\"<b>Abstract</b><br>";
	if($xml->PubmedArticle->MedlineCitation->Article->Abstract->AbstractText == null){
		 $abTx = '\"No abstract available on PubMed.\"';
	}
	else{
		foreach($xml->PubmedArticle->MedlineCitation->Article->Abstract->AbstractText as $para):
			$abtxt .= "$para <br>";
		endforeach;
	}
	$abtxt .= "\"}";	
	curl_close ($ch);
	echo $abtxt; 
}







?>




