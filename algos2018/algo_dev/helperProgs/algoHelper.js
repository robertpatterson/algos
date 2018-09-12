


Gbl = {
	qsParm: new Array(),
}

//----------------------------------------------------------------------------------------------

function init()
{
	qs();
	document.getElementById('alias').value =  Gbl.qsParm["alias"];
	document.getElementById('aliasMaps').value =  Gbl.qsParm["alias"];
}


function qs() 
{
        var query = window.location.search.substring(1);
        var parms = query.split('&');
        for (var i=0; i<parms.length; i++) 
        {
                var pos = parms[i].indexOf('=');
                if (pos > 0) 
                {
                        var key = parms[i].substring(0,pos);
                        var val = parms[i].substring(pos+1);
                        Gbl.qsParm[key] = val;
                }
        }
}

//----------------------------------------------------------------------------------------------



function checkForm()
{
	if(document.getElementById('alias').value == '')
	{
		alert('Please entrer the alias.');
		return false;
	}
	if(document.getElementById('uploadedfile').value == '')
	{
		alert('Please enter the file to process.');
		return false;
	}
//	alert('returnig true');
	return true;
}

function checkForm2()
{
	if(document.getElementById('aliasMaps').value == '')
	{
		alert('Please entrer the alias.');
		return false;
	}
	if(document.getElementById('uploadedfile2').value == '')
	{
		alert('Please enter the file to process.');
		return false;
	}
//	alert('returnig true');
	return true;
}

function checkGIFUpload()
{
	if(document.getElementById('aliasGIF').value == '')
	{
		alert('Please entrer the alias.');
		return false;
	}
	if(document.getElementById('gifFile').value == '')
	{
		alert('Please enter the file to upload.');
		return false;
	}
	return true;
}

function help()
{
	window.open('http://psychopharm.mobi/dev/html/AlgoTechHelp.html',"","");	
}
