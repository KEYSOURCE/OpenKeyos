
// Returns an XML requester to be used in Ajax, depending on the browser type
// If Ajax is not supported, returns false
function getXmlRequester ()
{
	ret = false;
	try
	{
		// This should work for any browser except IE
		ret = new XMLHttpRequest();
	}
	catch (error)
	{
		// Either the browser is IE or it doesn't support Ajax
		try
		{
			ret = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (error)
		{
			ret = false;
		}
	}
	
	return ret;
}
var requester_notifs = false;

function assetsChangeCat(asset_id, elm_id)
{
	if (requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		elm = document.getElementById (elm_id);
		if (elm)
		{
			requester_notifs = getXmlRequester ();
			var cat_id = elm.value;
			
			if (!requester_notifs) alert ('Sorry, Ajax is not available.');
			else
			{
				url = '/?cl=ajax&op=asset_change_category&id='+asset_id+'&cat_id='+cat_id;
				//url = '/?cl=kams&op=asset_edit&id='+asset_id+'&cat_id='+cat_id;
				requester_notifs.open ('GET', url);
				requester_notifs.send ('');
				requester_notifs.onreadystatechange = changeCategoryHandler;
			}			
		}
	}
	
	return false;
}

function associatedAssetsChange(customer_id, category)
{
	if(requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		requester_notifs = getXmlRequester();
		if(!requester_notifs) alert('Sorry, Ajax is not available.');
		else
		{
			url = '/?cl=ajax&op=asset_change_category_add&cat_id='+category+'&customer='+customer_id;
			requester_notifs.open('GET', url);
			requester_notifs.send('');
			requester_notifs.onreadystatechange = changeCategoryHandler;
		}
	}
	return false;
}

function financialInfoCurrency(currency_id)
{
	if(requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		requester_notifs = getXmlRequester();
		if(!requester_notifs) alert('Sorry, Ajax is not available.');
		else
		{
			url = '/?cl=ajax&op=financial_infos_change_currency&currency='+currency_id;
			requester_notifs.open('GET', url);
			requester_notifs.send('');
			requester_notifs.onreadystatechange = changeCurrencyHandler;
		}
	}
	return false;
}

function changeCategoryHandler()
{
	if (requester_notifs)
	{
		if (requester_notifs.readyState == 4)
		{
			try
			{
				//alert(requester_notifs.responseText);
				if(requester_notifs.status==200 && requester_notifs.responseXML)
				{
					var xml = requester_notifs.responseXML;
					var managed = xml.getElementsByTagName('managed')[0].firstChild.nodeValue;
					var category = xml.getElementsByTagName('category')[0].firstChild.nodeValue;
					//alert("managed: "+managed+' category: '+category);
					if(managed == '0')
					{
						var sel_asset_type = document.getElementById('sel_asset_type');
						var assoc_ids_display = document.getElementById('assoc_ids_display');
						sel_asset_type.value = 0;
						assoc_ids_display.style.display = 'none';
					}
					else
					{
						var sel_asset_type = document.getElementById('sel_asset_type');
						var assoc_ids_display = document.getElementById('assoc_ids_display');
						var assoc_select = document.getElementById("assoc_id_sel");
						var assoc_name = document.getElementById("assoc_name");
						var dlink = document.getElementById("asset_view");
						
						sel_asset_type.value = 1;
						assoc_ids_display.style.display = 'block';
						
						
						var category_name = xml.getElementsByTagName('category_name')[0].firstChild.nodeValue;
						
						
						var items = xml.getElementsByTagName('assoc_list')[0];
						var inside = "<!-- <option value=''>[Select an associated item]</option> -->";
						for(i=0 ; i<items.childNodes.length; i++)
						{
							var item_id = xml.getElementsByTagName('id')[i].firstChild.nodeValue;
							var item_name = xml.getElementsByTagName('name')[i].firstChild.nodeValue;
						
							inside += "<option label='"+item_name+"' value='"+item_id+"'>"+item_name+"</option>";
						}
						assoc_select.innerHTML = inside;
						
						var func = "cl=kawacs&op=computer_view";
						switch(category_name)
						{
							case 'Computer': 
								func = "/?cl=kawacs&op=computer_view&id="+xml.getElementsByTagName('id')[0].firstChild.nodeValue;
								break;
							case 'Peripheral':
								func = "/?cl=kawacs&op=peripheral_edit&id="+xml.getElementsByTagName('id')[0].firstChild.nodeValue;
								break;
							case "AD_Printer":
		  						var printer_id = xml.getElementsByTagName('id')[0].firstChild.nodeValue;
		  						var ppid = printer_id.split('_');
		  						for(i=0; i<ppid.length;i++)
		  						{	
		  							comp_id = ppid[0];
		  							pid = ppid[1];
		  							nrc = ppid[2];	
		  						}
								func = "/?cl=kerm&op=ad_printer_view&computer_id="+comp_id+'&nrc='+nrc;
								break;
						}
						
						dlink.href = func;
						assoc_name.innerHTML = "Associated "+category_name;
					}
				}
				else
					alert('ERROR: Failed reading response from server');
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
			}
			requester_notifs = false;
		}
	}
	return true;
}

function changeCurrencyHandler()
{
	if (requester_notifs)
	{
		if (requester_notifs.readyState == 4)
		{
			try
			{
				var pvalue_sy = document.getElementById("currency_sy1");
				var wvalue_sy =	document.getElementById("currency_sy2");
				pvalue_sy.innerHTML = requester_notifs.responseText;
				wvalue_sy.innerHTML = requester_notifs.responseText;
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
			}
			requester_notifs = false;
		}
	}
}