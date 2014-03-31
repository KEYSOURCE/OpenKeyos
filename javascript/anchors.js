
function AnchorPosition_getPageOffsetLeft(el){var ol=el.offsetLeft;while((el=el.offsetParent) != null){ol += el.offsetLeft;}return ol;}
function AnchorPosition_getWindowOffsetLeft(el){return AnchorPosition_getPageOffsetLeft(el)-document.body.scrollLeft;}
function AnchorPosition_getPageOffsetTop(el){var ot=el.offsetTop;while((el=el.offsetParent) != null){ot += el.offsetTop;}return ot;}
function AnchorPosition_getWindowOffsetTop(el){return AnchorPosition_getPageOffsetTop(el)-document.body.scrollTop;}


function getAnchorPosition (anchorname)
{
	var useWindow=false;
	var coordinates=new Object();
	var x=0,y=0;
	var use_gebi=false, use_css=false, use_layers=false;
	if (document.getElementById)
	{
		use_gebi=true;
	}
	else if (document.all)
	{
		use_css=true;
	}
	else if (document.layers)
	{
		use_layers=true;
	}
	
	if (use_gebi && document.all)
	{
		x=AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y=AnchorPosition_getPageOffsetTop(document.all[anchorname]);
	}
	else if(use_gebi)
	{
		var o=document.getElementById(anchorname);
		x=AnchorPosition_getPageOffsetLeft(o);
		y=AnchorPosition_getPageOffsetTop(o);
	}
	else if(use_css)
	{
		x=AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y=AnchorPosition_getPageOffsetTop(document.all[anchorname]);
	}
	else if(use_layers)
	{
		var found=0;
		for (var i=0; i<document.anchors.length; i++)
		{
			if (document.anchors[i].name == anchorname)
			{
				found=1;break;
			}
		}
		
		if (found==0)
		{
			coordinates.x=0;
			coordinates.y=0;
			return coordinates;
		}
		x=document.anchors[i].x;
		y=document.anchors[i].y;
	}
	else
	{
		coordinates.x=0;
		coordinates.y=0;
		return coordinates;
	}
	
	isOpera = (navigator.userAgent.indexOf ('Opera') != -1)
	if (document.all && !isOpera)
	{
		// Internet Explorer workaround
		y = y-1;
	}
	coordinates.x=x;
	coordinates.y=y;
	return coordinates;
}


function getAnchorWindowPosition (anchorname)
{
	var coordinates = getAnchorPosition(anchorname);
	var x=0;
	var y=0;
	
	if (document.getElementById)
	{
		if (isNaN(window.screenX))
		{
			x=coordinates.x-document.body.scrollLeft+window.screenLeft;
			y=coordinates.y-document.body.scrollTop+window.screenTop;
		}
		else
		{
			x=coordinates.x+window.screenX+(window.outerWidth-window.innerWidth)-window.pageXOffset;
			y=coordinates.y+window.screenY+(window.outerHeight-24-window.innerHeight)-window.pageYOffset;
		}
	}
	else if(document.all)
	{
		x=coordinates.x-document.body.scrollLeft+window.screenLeft;
		y=coordinates.y-document.body.scrollTop+window.screenTop;
	}
	else if(document.layers)
	{
		x=coordinates.x+window.screenX+(window.outerWidth-window.innerWidth)-window.pageXOffset;
		y=coordinates.y+window.screenY+(window.outerHeight-24-window.innerHeight)-window.pageYOffset;
	}
	coordinates.x=x;
	coordinates.y=y;
	return coordinates;
}

function showMenu (anchor_name)
{
	anchor_name = anchor_name.replace (/_div/gi, '');
	dv = document.getElementById (anchor_name + '_div')
	if (dv)
	{
		coord = getAnchorPosition (anchor_name);
		dv.style.top = (coord.y + 22) + 'px';
		dv.style.left = (coord.x - 4) + 'px';
		dv.style.display = 'block';
		dv.style.visibility = 'visible';
		
		if (IS_IE)
		{
			shim = document.getElementById('DivShim');
			dv.style.zIndex = 1001;
			
			shim.style.width = dv.offsetWidth;
			shim.style.height = dv.offsetHeight;
			shim.style.top = dv.style.top;
			shim.style.left = dv.style.left;
			shim.style.zIndex = 1000;
			shim.style.display = "block";
			shim.style.background = "white";
			shim.style.visibility = 'visible';
		}
	}
}

function hideMenu (anchor_name)
{
	anchor_name = anchor_name.replace (/_div/gi, '');
	dv = document.getElementById (anchor_name + '_div')
	if (dv)
	{
		dv.style.visibility = 'hidden';
		
		if (IS_IE)
		{
			shim = document.getElementById('DivShim');
			shim.style.visibility = 'hidden';
			shim.style.display = 'none';
		}
	}
}

function showSubMenu (anchor_name, parent_name)
{
	anchor_name = anchor_name.replace (/_div/gi, '');
	showMenu (parent_name);
	dv = document.getElementById (anchor_name + '_div')
	if (dv)
	{
		coord = getAnchorPosition (anchor_name);
		coord_parent = getAnchorPosition (parent_name);
		dv.style.top = (coord.y - 5 - coord_parent.y) + 'px';
		dv.style.left = (coord.x + 40 - coord_parent.x) + 'px';
		
		dv.style.display = 'block';
		dv.style.visibility = 'visible';
		
		if (IS_IE)
		{
			shim = document.getElementById('DivShim');
			dv.style.zIndex = 1001;
			
			shim.style.width = dv.offsetWidth;
			shim.style.height = dv.offsetHeight;
			shim.style.top = dv.style.top;
			shim.style.left = dv.style.left;
			shim.style.zIndex = 1000;
			shim.style.display = "block";
			shim.style.background = "white";
			shim.style.visibility = 'visible';
		}
		
		dv_anchor = document.getElementById (anchor_name);
		dv_anchor.className = 'activated_menu';
	}
}

function hideSubMenu (anchor_name, parent_name)
{
	anchor_name = anchor_name.replace (/_div/gi, '');
	dv = document.getElementById (anchor_name + '_div')
	if (dv)
	{
		dv.style.visibility = 'hidden';
		
		if (IS_IE)
		{
			shim = document.getElementById('DivShim');
			shim.style.visibility = 'hidden';
			shim.style.display = 'none';
		}
		
		dv_anchor = document.getElementById (anchor_name);
		dv_anchor.className = '';
	}
}

