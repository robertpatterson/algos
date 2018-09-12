 /* JavaScript Document
By Robert Patterson
copyright Mental Health Connections Inc. 2010
psychopharm.mobi

When program starts the common info array is loaded and the title page from it is displayed.
When an algo is selected the first time all of its data is gotton form server and put in its array.
When any page or explanatory text is requested jump() tries each array to find the right alias and handles displaying it.
The navigate object keeps track of pages displayed so one can move back and forth among trhe pages previously displayed.
*/
	$(document).ready(function($){	                  
	    $("#my-menu").mmenu({
	          "extensions": [
	        	  "pagedim-black"
	          ],
	      "slidingSubmenus": false,
	      "autoHeight": true ,
	      "panels zoom": true //,
	 //     "dropdown": true
	    });         
	    var $menu = $('#my-menu');
	    var $icon = $("#my-icon");
	    var API = $menu.data( "mmenu" );

	    $icon.on( "click", function() {
	    	API.open();
	    });
	    API.bind( "open:finish", function() {
	    	setTimeout(function() {
	    		$icon.addClass( "is-active" );
	    	}, 100);
	    });	
	    API.bind( "close:finish", function() {
	    	setTimeout(function() {
	    		$icon.removeClass( "is-active" );
	    	}, 100);
	    }); 
	    $('#npdep').click(function(){
	    	API.close();
	    })
	    getCommonInfo();

	    
//		$("#copyrightSpan").addClass("fontsize8");	    
	    
	    
	    
	});
   
// ------------- Toggle button -----------------------	
	  var $hamburger = $("my-icon");  //.hamburger");
	  $hamburger.on("click", function(e) {
		  alert("clicked");
	    $hamburger.toggleClass("is-active");
	    alert("clicked");
	  });	
  
/*
function navButton(dir)
{
	closeWin('explainWin');	
	switch(dir)
	{
			case 'inHTML':
				showCommonInfo('title','leftPanel');
				break;
			case '-':   // From a flowchart back button
				var title = Gbl.currentAlgo + 'title';
				showPage(title);
				break;
	}
}
*/

function algoChoice(algo){  // From mmenu.	
	if(Gbl.currentAlgo != algo){  // Requesting a new algo so download all of it.
		var url = Gbl.algoURL + Gbl.serverLocation + '/php/algoAj.php?JSON={"task":"getAlgo","algo":"' + algo + '"}';
		$.getJSON(url, function(algoObj){
			Gbl.imageAr = algoObj[1];
			Gbl.algoAr = algoObj[2];
			var title = algoObj[0].algoAlias + 'title';
			showPage(title);
			window.Gbl.currentAlgo = algoObj[0].algoAlias;
			showFlowchart();
	    });	
		Gbl.chartToDisplay = algo + '-1';   
	}
	else{  // Get next page of the current algo	
		var pageAlias = Gbl.currentAlgo + 'title';
		showPage(pageAlias);
	} 		
}


// ----------------------------------Universal displayer - node explanations, flowcharts and common info pages --------------------


function jump(source,coords) {
// --- See if alias is for a node explanation. ------------------------------------
	if(coords == 'n/a')
	{
		showPage(source);
		return;
	}
	if(Gbl.algoAr)
	{
		var win = document.getElementById('explainWin');
		if(win.className == "showIt")
		{
			win.className = 'hideIt';
			return;
		}		
		if(showExplanation(source,coords, win))
			return ;
	}

//----- See if a common info page is requested so show in left panel ---------------------------------------------	

	if(showCommonInfo(source,'leftPanel'))
		return;
}

function showExplanation(source,coords, win){  // The popups for the flowchart nodes 
	var c = 0;
	while(c < Gbl.algoAr.length)
	{
// -----Node clicked so show explanation test in right panel ------------------------------------------------
		if( source == Gbl.algoAr[c].alias)   // Each alias is essentially a page - a flowchart with imap and text
		{
//			//put title and text. Set it at correct height in page opposite the node
			win.className = "showIt";
			var ar = coords.split(',');
			var vert = parseInt(ar[3],10) + 20;
			var left = parseInt(ar[0],10) - 100;
			if(left < 10)
				left = 10;
			win.style.top = vert + 'px';
			win.style.left = left + 'px';
			document.getElementById('explainHeadline').innerHTML =  Gbl.algoAr[c].title;
			document.getElementById('explainTextSpan').innerHTML =  Gbl.algoAr[c].introtext;
			return true;
		}		
		c++;
	}
}

function showPage(pageAlias){
	var c = 0;
	while(c < Gbl.algoAr.length)
	{
		if( pageAlias == Gbl.algoAr[c].alias)   // Each alias is essentially a page - a flowchart with imap and text
		{
			$('#leftPanel').html(Gbl.algoAr[c].introtext); // Put the text in the proper panel
		}		
		c++;
	}
	
}


function showFlowchart()  //findChartByAlias(source)  // Used to set up a page w flowchart image and image map
{
	var alias = Gbl.currentAlgo;
	var i = 0;
	while(i < Gbl.imageAr.length)
	{
//		document.getElementById('leftPanel').innerHTML = '<h2>Click (or tap) any box to see details.</h2><br><img id="flowchartImg" alt="Flowchart image" USEMAP="#mainmap" ismap>';
		$('#leftPanel').append('<span>Click (or tap) any box to see details.</span><br><img id="flowchartImg" alt="Flowchart image" USEMAP="#mainmap" ismap>');
			document.getElementById('myMap').innerHTML = Gbl.imageAr[i].description;
			document.getElementById('flowchartImg').src = Gbl.algoURL + Gbl.serverLocation + '/images/' + alias + '.gif';
			document.getElementById('explainWin').className = "hideIt";
			return true;
	}
	return false;
}


function showCommonInfo(page,destination)  // First title page, osser bio, title page of each algo
{
	$('#my-menu').data( "mmenu").close();
	if(destination == 'title'){   // It wants to start an algo so get all the pages now.
		algoChoice(page);		// get 1 algo from server. 'page' is the joomla alias
		return;
	}	
	var c = 0;
	while(c < Gbl.commonInfoAr[1].length)
	{

		if( page == Gbl.commonInfoAr[1][c].alias)   // Find the page (alias). Each alias is a page
		{
			if(destination == 'leftPanel')
			{
				document.getElementById('leftPanel').innerHTML = Gbl.commonInfoAr[1][c].introtext; // Put the text in the proper panel
				if(page != 'title'){
					document.getElementById('navButtons').className = "showIt";
					Gbl.currentPage = page;
				}					
		//		else {
			//		document.getElementById('navButtons').className = "hideIt";
		//		}
			}
			if(destination == 'bio' || destination == 'about')
			{
				document.getElementById('explainWin').className = "showIt";
				document.getElementById('explainWin').style.top = '75px';
				document.getElementById('explainWin').style.left = '15px';
				$('#explainHeadline').text(''); //Gbl.algoAr[c].title;
				$('#explainTextSpan').html(Gbl.commonInfoAr[1][c].introtext); // Put the text
			}
			return;
		}		
		c++;
	}
}




//------------------------------------------------------ Ajax - Get all of one algorithm ---------------------------------------------

function getCommonInfo(){
	var url = Gbl.algoURL + Gbl.serverLocation + '/php/algoAj.php?JSON={"task":"getCommonInfo"}';
	$.getJSON(url, function(data,status){
 	   Gbl.commonInfoAr = data;  //jsonParse(resp);
  	   showCommonInfo('title','leftPanel');  
  });  
} 


function callBackJSON()
{
	if(xmlHttp.readyState == 4)
	{
			doCallBackJSON(xmlHttp.responseText);  
	}
}

function doCallBackJSON(resp)
{
	var jsonObj = jsonParse(resp);
	switch(jsonObj[0].task)
	{
		case 'algo':		
			algoObj = jsonParse(resp);  // All data for the requested page
			Gbl.imageAr = algoObj[1];
			Gbl.algoAr = algoObj[2];
			$title = jsonObj[0].algoAlias + 'title';
			showPage($title);
			Gbl.currentAlgo = jsonObj[0].algoAlias;
			break;
		case 'getAbstract':
			var pmid = jsonObj[0].pmid;
			var divPmid = 'div_' + pmid;
			document.getElementById(divPmid).innerHTML = jsonObj[0].abstract;
			pmid = 'a_' + pmid;
			document.getElementById(divPmid).className = 'refBox';
			document.getElementById(pmid).innerHTML = "Hide abstract";
			break;
		default:
			Gbl.commonInfoAr = jsonParse(resp);
			showCommonInfo('title','leftPanel');
	}	
}

//--------------Diplay Bios ----------------------------------------

function displayBios(list){
	if($("#authorBiosSpan").html() != ""){
		$("#authorBiosSpan").html("");
		$("#caret").html('<i class="fas fa-caret-down"></i>');
		return false;
	}
	var authText = '';
	var nl = "";
	var n = 0;  // list items
	while(n < list.length)   // List of authors comes from server
	{
		var c = 0;
		while(c < Gbl.commonInfoAr[1].length)  // go thru the general info
		{
			if( list[n] == Gbl.commonInfoAr[1][c].alias)   // Find the authors aliases in the general info.
			{
				//Show name and make span for bio text
				authText = authText + '<span  class="subsubhead">' + nl + 
						"<a href='#' onclick = 'showBioText(\"" +  list[n] + "\")'>" + 
						Gbl.commonInfoAr[1][c].title +
						'<span id="auth' + c + '"><i class="fas fa-caret-down"></i></span></a></span>' + 
						"<span id = '" + list[n] + "'></span>";
				break;
			}
			c++;
		}
		n++;
		nl = "<br>";
	}
	$("#caret").html('<i class="fas fa-caret-up"></i>');
	authText += "</span><br>";
	$("#authorBiosSpan").html(authText);
//	$("#auth" +c).html('<i class="fas fa-caret-"></i>');
	return false;		
}


function showBioText(alias){  // author's alias
	var c = 1;
	// Close author area
	var al = "#" + alias;
	// CLOSE BIO
	if($(al).html() != ""){  // delete this author's bio text
		$(al).html("");

		
		while(c < Gbl.commonInfoAr[1].length)   // Go thru the authors		
		{
			//alert("BOO" + alias + c);
			if( alias == Gbl.commonInfoAr[1][c].alias)   // Find the authors aliases in the general info.
			{		
		
				//alert(c + alias);
				$("#auth" + c).html('<i class="fas fa-caret-down"></i>');
			}
			c++;
		//	return false;
		}
		return false;
	}
	if($("#" + Gbl.openBio).length){
		$("#" + Gbl.openBio).html("");		
	} 
	var c = 0;
	while(c < Gbl.commonInfoAr[1].length)   // Go thru the authors
	{
		if( alias == Gbl.commonInfoAr[1][c].alias)   // Find the author by alias.
		{		 
					
			$(al).html(Gbl.commonInfoAr[1][c].introtext);
			$("#auth" +c).html('<i class="fas fa-caret-up"></i>');
			Gbl.openBio = alias;
			break;
		}
		c++;
	}
}

//<a http="#" onclick('showbiotext("dnobio")')>David N. Osser, M.D.<i class="fas fa-caret-down"></i></a>

//--------------------------------------------------------- Utilities ----------------------------------------------------

//-------------------------------------------- JSON parse and JSLON stringify

window.jsonParse=function(){var r="(?:-?\\b(?:0|[1-9][0-9]*)(?:\\.[0-9]+)?(?:[eE][+-]?[0-9]+)?\\b)",k='(?:[^\\0-\\x08\\x0a-\\x1f"\\\\]|\\\\(?:["/\\\\bfnrt]|u[0-9A-Fa-f]{4}))';k='(?:"'+k+'*")';var s=new RegExp("(?:false|true|null|[\\{\\}\\[\\]]|"+r+"|"+k+")","g"),t=new RegExp("\\\\(?:([^u])|u(.{4}))","g"),u={'"':'"',"/":"/","\\":"\\",b:"\u0008",f:"\u000c",n:"\n",r:"\r",t:"\t"};function v(h,j,e){return j?u[j]:String.fromCharCode(parseInt(e,16))}var w=new String(""),x=Object.hasOwnProperty;return function(h,
j){h=h.match(s);var e,c=h[0],l=false;if("{"===c)e={};else if("["===c)e=[];else{e=[];l=true}for(var b,d=[e],m=1-l,y=h.length;m<y;++m){c=h[m];var a;switch(c.charCodeAt(0)){default:a=d[0];a[b||a.length]=+c;b=void 0;break;case 34:c=c.substring(1,c.length-1);if(c.indexOf("\\")!==-1)c=c.replace(t,v);a=d[0];if(!b)if(a instanceof Array)b=a.length;else{b=c||w;break}a[b]=c;b=void 0;break;case 91:a=d[0];d.unshift(a[b||a.length]=[]);b=void 0;break;case 93:d.shift();break;case 102:a=d[0];a[b||a.length]=false;
b=void 0;break;case 110:a=d[0];a[b||a.length]=null;b=void 0;break;case 116:a=d[0];a[b||a.length]=true;b=void 0;break;case 123:a=d[0];d.unshift(a[b||a.length]={});b=void 0;break;case 125:d.shift();break}}if(l){if(d.length!==1)throw new Error;e=e[0]}else if(d.length)throw new Error;if(j){var p=function(n,o){var f=n[o];if(f&&typeof f==="object"){var i=null;for(var g in f)if(x.call(f,g)&&f!==n){var q=p(f,g);if(q!==void 0)f[g]=q;else{i||(i=[]);i.push(g)}}if(i)for(g=i.length;--g>=0;)delete f[i[g]]}return j.call(n,
o,f)};e=p({"":e},"")}return e}}();


function sendJSON(params,altSource)
{
	var url = '';		
	       url = 'http://psychopharm.mobi/' + Gbl.serverLocation + '/php/algoAj.php';		
	if(params)
		url += '?JSON=' + params;
	createXMLHttpRequest()
	xmlHttp.open("GET",url,true);
	xmlHttp.onreadystatechange = callBackJSON;
	xmlHttp.send(null);
}


function createXMLHttpRequest()
{
	if(window.ActiveXObject){
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else if(window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}		
}



function wait(type){
	document.body.style.cursor = type;
}

function expandIt(id){
	var cdH = document.getElementById(id);
	if(cdH.className=='showIt')
		cdH.className='hideIt';
	else
		cdH.className='showIt';	
}


function showRefDiv(){
	var refDiv = document.getElementById('refDiv');
	if(refDiv.className == 'screens_hideIt')
		refDiv.className = 'screens_showIt';
	else
		refDiv.className = 'screens_hideIt';
}

function abstract(pmid)
{
	var aPMID = 'a_' + pmid;
	if(document.getElementById(aPMID).innerHTML == 'Hide abstract'){
		document.getElementById(aPMID).innerHTML = 'Abstract';
		pmid = 'div_' + pmid;
		document.getElementById(pmid).className = 'hideIt';
	}
	else{		
		var url = Gbl.algoURL + Gbl.serverLocation + '/php/algoAj.php?JSON={"task":"getAbstract","pmid":"' + pmid + '"}'; //&callback=?';
		$.getJSON(url, function(abstrObj){
	//		alert(url);
			var pmid = abstrObj.pmid;
			var divPmid = 'div_' + pmid;
			document.getElementById(divPmid).innerHTML = abstrObj.abstract;
			pmid = 'a_' + pmid;
			document.getElementById(divPmid).className = 'refBox';
			document.getElementById(pmid).innerHTML = "Hide abstract";

	    });		
	}
}
/*
function developer(type)
{
	var wo;
	switch(type)
	{
		case 'help':
            wo= 'http://psychopharm.mobi/html/AlgoTechHelp.html';
			window.open(wo,"","");	
			break;
		case 'helpers':
			wo= 'http://psychopharm.mobi/dev/helperProgs/algoHelper.html?alias=' + Gbl.currentAlgo;
			window.open(wo,"","");	
			break;
	}
}


function showDevBox(checked)
{
	if(!checked)
	{
		document.getElementById('devFieldset').className = "hideIt";
		return;
	}
	document.getElementById('devFieldset').className = "showIt";
}
*/
function closeWin(name)
{
	document.getElementById(name).className="hideIt";
}

function cursorSet()
{
	document.body.style.cursor = 'pointer';	
}
/*
function getIFrameDoc(iFramex)
{
//alert(iFramex);
	var iFrame = document.getElementById(iFramex);
	var iFrameDoc;
	if(iFrame.contentDocument)
		iFrameDoc = iFrame.contentDocument;
	else
	{
		if(iFrame.contentWindow)
			iFrameDoc = iFrame.contentWindow.document;
		else
		{
			if(iFrame.document)
				iFrameDoc = iFrame.document;
		}
	}
	return iFrameDoc;
}
*/
function showHide(id)
{
	var cdH = document.getElementById(id);
	if(cdH.className=='showIt')
		cdH.className='hideIt';
	else
		cdH.className='showIt';	
}