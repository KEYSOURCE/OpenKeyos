
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

// Removes all children nodes of the specified element
function clearAllChildren (e)
{
	if (e && e.childNodes)
	{
		// Do it in reverse order - it's faster on the display and looks better
		for (var e_idx=e.childNodes.length-1; e_idx>=0; e_idx--)
		{
			e.removeChild(e.childNodes[e_idx]);
		}
	}
}

/****************************************************************/
/* Notifications handling					*/
/****************************************************************/

var requester_notifs = false;		// The XML requester for making notification related requests
var last_elm_id_notifs = false;		// The ID of the last element for which a notificaion request is made

/** Makes a request to the server to mark one or more notifications as being read.
* @param	int			user_id		The ID of the user for which to mark the notifications as read
* @param	string			notifs_ids	String with the IDs of the notifications, separated by spaces
* @param	string			elm_id		The ID (DOM) of the element which to modify after the success
*							of the operation. Once the server reports completion, the background
*							color of the element will be changed to white.
*/
function markNotifsRead (user_id, notifs_ids, elm_id)
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
			last_elm_id_notifs = elm_id;
			
			if (!requester_notifs) alert ('Sorry, Ajax is not available.');
			else
			{
				ids_array = notifs_ids.split (' ');
				url = '/?cl=ajax&op=mark_notifications_unread&user_id='+user_id;
				for (i=0; i<ids_array.length; i++) url = url+'&notif_id[]='+ids_array[i];
				
				requester_notifs.open ('GET', url);
				requester_notifs.send ('');
				requester_notifs.onreadystatechange = stateNotifsReadHandler;
			}
			
		}
	}
	
	return false;
}

/** Handler function for notifications-related requester. When the server confirms
* marking the notifications as read, it will also update the display: changing
* background color of the element where the notification is displayed and
* updating the unread notification counter in the left-side menu 
*/
function stateNotifsReadHandler ()
{
	if (requester_notifs)
	{
		if (requester_notifs.readyState == 4)
		{
			try
			{
				if (requester_notifs.status == 200 && requester_notifs.responseXML)
				{
					result = requester_notifs.responseXML.getElementsByTagName('result')[0];
					result = result.firstChild.nodeValue;
					
					if (result=='ok')
					{
						// The change was ok. Update the display element of the notification, if present
						if (last_elm_id_notifs)
						{
							elm = document.getElementById (last_elm_id_notifs);
							elm.style.backgroundColor = 'white';
						}
						
						// If we have a number of remaining unread notifs, update the counters where needed
						notifs_unread = requester_notifs.responseXML.getElementsByTagName('unread_notifs')[0];
						if (notifs_unread)
						{
							notifs_unread = notifs_unread.firstChild.nodeValue;
							notifs_unread = parseInt (notifs_unread);
							
							if (notifs_unread > 0)
							{
								// Update the counter in the unread notifications indicator in the 
								// left-side menu, if present
								elm_main = document.getElementById ('main_elm_unread_notifs');
								if (elm_main)
								{
									clearAllChildren (elm_main);
									elm_main.appendChild (document.createTextNode (notifs_unread));
								}
							}
							else
							{
								// Hide the unread notifications indicator in the left-side menu, if present
								elm_main = document.getElementById ('link_new_notifs');
								if (elm_main) elm_main.style.display = 'none';
							}
							
						}
					}
					else alert ("ERROR: Server didn't confirm updating notification status");
				}
				else alert ('ERROR: Failed reading response from server.');
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
			}
			requester_notifs = false;
			last_elm_id_notifs = false;
		}
	}
	return true;
}

/*
	ajax function for previewing the generated report for an intervention
*/
function pdf_preview(intervention_id, customer_id, view, show)
{
	if (requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		requester_notifs = getXmlRequester ();
			
		if (!requester_notifs) alert ('Sorry, Ajax is not available.');
		else
		{
			var prg = document.getElementById("progress");
			var init = document.getElementById("init");
			var ifrm = document.getElementById('pdf_view');
			init.style.display = "none"; //and it shall remain this way until the page is reloaded;
			prg.style.display = "block";
			prg.style.visibility = "visible";
			ifrm.style.display = "none";
			url = '/?cl=ajax&op=ir_pdf_preview&intervention_id='+intervention_id+'&customer_id='+customer_id+'&view='+view+'&show='+show;
			requester_notifs.open ('GET', url);
			requester_notifs.send ('');
			requester_notifs.onreadystatechange = pdf_previewHandler;			
		}
	}
	return false;	
}

function pdf_multiple_preview(irs, view, show)
{
	
	url = 'cl=ajax&op=ir_multiplepdf_preview&len='+irs.length;
	
	for (k=0; k<irs.length; k++)
	{
		url+='&id'+k+'='+irs[k][0]+'&cid'+k+'='+irs[k][1];
	}
	url += '&view='+view+'&show='+show;
	generate_from_multiple(url);
	
	return true;
}

function generate_from_multiple(url)
{
	if(requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		requester_notifs = getXmlRequester ();
			
		if (!requester_notifs) alert ('Sorry, Ajax is not available.');
		else
		{
			
			var prg = document.getElementById("progress");
			var init = document.getElementById("init");
			var ifrm = document.getElementById('pdf_view');
			init.style.display = "none"; //and it shall remain this way until the page is reloaded;
			prg.style.display = "block";
			prg.style.visibility = "visible";
			ifrm.style.display = "none";
			
			
			var params = url;
			var url = "index.php";
			requester_notifs.open("POST", url, true);
			
			//Send the proper header information along with the request
			requester_notifs.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			requester_notifs.setRequestHeader("Content-length", params.length);
			requester_notifs.setRequestHeader("Connection", "close");
			
			requester_notifs.onreadystatechange = pdf_previewHandler;
			requester_notifs.send (params);
			
		}
	}
	return false;
}

function pdf_previewHandler()
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
						var status = xml.getElementsByTagName('obb')[0].firstChild.nodeValue;
						if (status == "ok")
						{
							var pdf_url_path = xml.getElementsByTagName('name')[0].firstChild.nodeValue;
							var intervention_id = xml.getElementsByTagName('id')[0].firstChild.nodeValue;
							var ifrm = document.getElementById('pdf_view');
							var prg = document.getElementById("progress");
							prg.style.display = "none";
							ifrm.style.display = "block";
							ifrm.src = pdf_url_path;
						}
						else
						{
							ifrm.innerHTML = "The file could not be generated, please retry in a few minutes";
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



//
// ajax function for getting a new page of interventions
//
var req_interventions = false;
function getNewSetOfInterventions(customer_id, start, per_page, direction)
{
	if (req_interventions)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		req_interventions = getXmlRequester ();
			
		if (!req_interventions) alert ('Sorry, Ajax is not available.');
		else
		{
			var str = eval(start);
			var lim = eval(per_page);
			var dir = eval(direction);
			if(direction == -1 || direction == 1)
			{
				str += lim * dir;
			}
			
			url = '/?cl=ajax&op=get_new_ir_page&customer_id='+customer_id+'&start='+str+'&limit='+lim;
			req_interventions.open ('GET', url);
			req_interventions.send ('');
			req_interventions.onreadystatechange = getNewSetOfInterventions_handler;			
		}
	}
	return false;	
}

function getNewSetOfInterventions_handler()
{
	if (req_interventions)
	{
		if (req_interventions.readyState == 4)
		{
			try
			{
				//alert(req_interventions.responseText);
				if(req_interventions.status==200 && req_interventions.responseXML)
				{
						var xml = req_interventions.responseXML;
						var status = xml.getElementsByTagName('obb')[0].firstChild.nodeValue;
						var ifrm = document.getElementById('div_ir_content');
						if (status == "ok")
						{
							new_html = "<table class='list' width='98%'><thead><tr><td width='1%'>ID</td>";
							new_html += '<td width="49%">Subject</td><td width="15%">Status</td><td width="10%">Created</td><td width="8%" align="right" nowrap="nowrap">Work time</td><td width="7%" align="right" nowrap="nowrap">Billable amount</td><td width="7%" align="right" nowrap="nowrap">TBB amount</td><td width="10%"> </td></tr></thead>';
							var interventions = xml.getElementsByTagName('intervention');
							for(i=0; i< interventions.length; i++)
							{
								var id = xml.getElementsByTagName('int_id')[i].firstChild.nodeValue;
								new_html += '<tr>';
								new_html += '<td><a href="/?cl=krifs&amp;op=intervention_edit&amp;id='+id+'">'+id+'</a></td>';
								new_html += '<td><a href="/?cl=krifs&amp;op=intervention_edit&amp;id='+id+'">'+xml.getElementsByTagName('int_subject')[i].firstChild.nodeValue+'</a></td>';
								new_html += '<td>'+xml.getElementsByTagName('int_status')[i].firstChild.nodeValue+'</td>';
								new_html += '<td>'+xml.getElementsByTagName('int_created')[i].firstChild.nodeValue+'</td>';
								new_html += '<td>'+xml.getElementsByTagName('int_work_time')[i].firstChild.nodeValue+'</td>';
								new_html += '<td>'+xml.getElementsByTagName('int_bill_amount')[i].firstChild.nodeValue+'</td>';
								new_html += '<td>'+xml.getElementsByTagName('int_tbb_amount')[i].firstChild.nodeValue+'</td>';
								new_html += '<td>Delete</td>';
								new_html += '</tr>';
							}
							new_html += '<tr><td colspan="8">generat din AJAX</td></tr>';
							new_html += '</table>';
							ifrm.innerHTML = new_html;
						}
						else
						{
							ifrm.innerHTML = "Could not load the requested page";
						}
				}
				else
					alert('ERROR: Failed reading response from server');
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
			}
			req_interventions = false;
		}
	}
	return true;
}

function generateImage(image, title,legend, perc_r, perc_o, perc_g, perc_gr )
{
	if (requester_notifs)
	{
		alert ('Another process already runs in the background. Please try again later');
	}
	else
	{
		requester_notifs = getXmlRequester ();
			
		if (!requester_notifs) alert ('Sorry, Ajax is not available.');
		else
		{
			
			url = 'cl=ajax&op=generate_graph&image='+image+'&r='+perc_r+'&o='+perc_o+'&g='+perc_g+'&gr='+perc_gr+'title'+title;
			for(i=0;i<legend.length;i++)
				url = url + '&leg'+i+'='+legend[i]; 
			url += url+'&cleg='+legend.length;
			
			
			
			var params = url;
			var url = "index.php";
			requester_notifs.open("POST", url, true);
			
			//Send the proper header information along with the request
			requester_notifs.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			requester_notifs.setRequestHeader("Content-length", params.length);
			requester_notifs.setRequestHeader("Connection", "close");
			
			requester_notifs.onreadystatechange = generateImage_handler;		
			requester_notifs.send (params);
		}
	}
	return false;
}

function generateImage_handler()
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
					var status = xml.getElementsByTagName('obb')[0].firstChild.nodeValue;
					if (status == "ok")
					{
						im_holder = xml.getElementsByTagName('image')[0].firstChild.nodeValue;
						outfile = xml.getElementsByTagName('outfile')[0].firstChild.nodeValue;
						
						var _bk_im = document.getElementById(im_holder);
						
						_bk_im.src = outfile;
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

var req_kbcat = false;

function getKbSubcategories(parent_cat)
{
	if (req_kbcat) 
	{
		alert("Another process already runs in the background. Please try again later!");
	}
	else
	{
		req_kbcat = getXmlRequester();
		if(!req_kbcat) alert("Sorry, AJAX is not available on your browser");
		else
		{
			var pcat = eval(parent_cat);
			var url = "/?cl=ajax&op=get_kb_subcategories&pcat="+pcat;
			req_kbcat.open('GET', url);
			req_kbcat.send('');
			req_kbcat.onreadystatechange = getKbSubcategories_Handler;			
		}
	}
	return false;
}
function getKbSubcategories_Handler()
{	
	if (req_kbcat)
	{		
		if (req_kbcat.readyState == 4)
		{
			try
			{
				if (req_kbcat.status == 200 && req_kbcat.responseXML) 
				{
					xml = req_kbcat.responseXML;
					var container = document.getElementById('cat_select_container');
					var items = xml.getElementsByTagName('item');
					var parent_id = xml.getElementsByTagName('parent_id')[0].firstChild.nodeValue;
					var parent_name = xml.getElementsByTagName('parent_title')[0].firstChild.nodeValue;
					var new_html = "<input type='hidden' name='kbCatParent' id='kbCatParent' value='"+parent_id+"' /><b>"+parent_name+" </b>&nbsp;&nbsp;";
					if (items.length > 0) 
					{
						new_html += "<select name='kbcat[hasParent]' id='kbcat[hasParent]' onchange='getSubcategories()'>";
						new_html += "<option value='-1'>[Select sub-category]</option>";
						for (i = 0; i < items.length; i++) {
							var id = xml.getElementsByTagName('id')[i].firstChild.nodeValue;
							var title = xml.getElementsByTagName('title')[i].firstChild.nodeValue;
							new_html += "<option label='" + title + "' value='" + id + "'>" + title + "</option>";
						}
						new_html += "</select>";
					}
					new_html += "&nbsp;&nbsp;<input type='button' onClick='pickOtherCategory()' value='Reset categories'>";
					container.innerHTML = new_html;
				}
				else 
				{
					alert('ERROR: Failed reading response from server');
				}
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
				req_kbcat = false;
			}
			req_kbcat = false;
		}
	}
	return true;
}

var req_kbart = false;

function getKbSubarticles(parent_art)
{
	if (req_kbart) 
	{
		alert("Another process already runs in the background. Please try again later!");
	}
	else
	{
		req_kbart = getXmlRequester();
		if(!req_kbart) alert("Sorry, AJAX is not available on your browser");
		else
		{
			var part = eval(parent_art);
			var url = "/?cl=ajax&op=get_kb_subarticles&part="+part;
			req_kbart.open('GET', url);
			req_kbart.send('');
			req_kbart.onreadystatechange = getKbSubarticles_Handler;			
		}
	}
	return false;
}

function getKbSubarticles_Handler()
{
	if (req_kbart)
	{		
		if (req_kbart.readyState == 4)
		{
			try
			{
				if (req_kbart.status == 200 && req_kbart.responseXML) 
				{
					xml = req_kbart.responseXML;
					var container = document.getElementById('art_select_container');
					var items = xml.getElementsByTagName('item');
					var parent_id = xml.getElementsByTagName('parent_id')[0].firstChild.nodeValue;
					var parent_name = xml.getElementsByTagName('parent_title')[0].firstChild.nodeValue;
					var new_html = "<input type='hidden' name='kbArtParent' id='kbArtParent' value='"+parent_id+"' /><b>"+parent_name+" </b>&nbsp;&nbsp;";
					if (items.length > 0) 
					{
						new_html += "<select name='kbart[hasParent]' id='kbart[hasParent]' onchange='getSubarticles()'>";
						new_html += "<option value='-1'>[Select sub-article]</option>";
						for (i = 0; i < items.length; i++) {
							var id = xml.getElementsByTagName('id')[i].firstChild.nodeValue;
							var title = xml.getElementsByTagName('title')[i].firstChild.nodeValue;
							new_html += "<option label='" + title + "' value='" + id + "'>" + title + "</option>";
						}
						new_html += "</select>";
					}
					new_html += "&nbsp;&nbsp;<input type='button' onClick='pickOtherArticle()' value='Reset articles'>";
					container.innerHTML = new_html;
				}
				else 
				{
					alert('ERROR: Failed reading response from server');
				}
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
				req_kbart = false;
			}
			req_kbart = false;
		}
	}
	return true;
}


var request_dell = false;

function checkDellWarranties(service_tag)
{
	//service tags should be retrieved from the computer_items table	
	if(request_dell)
	{
		alert("Another process runs in background. Please try again later");
	}
	else{
		
		request_dell = getXmlRequester();		
		if(!request_dell) alert("Sorry, AJAX is not available on your browser");
		else{
			var strServiceTag = service_tag				
			//var strURL = "http://supportapj.dell.com/support/topics/topic.aspx/ap/shared/support/my_systems_info/en/details?c=in&cs=inbsd1&l=en&s=bsd&ServiceTag="+strServiceTag+"&~tab=1";
			var strURL = "http://supportapj.dell.com/support/topics/topic.aspx/ap/shared/support/my_systems_info/en/details?c=in&cs=inbsd1&l=en&s=gen&~tab=1&ServiceTag="+strServiceTag;
			alert(strURL);
			request_dell.open("GET", strURL, true);					
			request_dell.onreadystatechange = checkDellWarranties_Handler;
			//request_dell.setRequestHeader("Content-Type", "text/html");
			request_dell.send('');
			alert(request_dell.responseText);
		}
	}
}
function trim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}

function checkDellWarranties_Handler()
{
	if(request_dell)
	{
		alert(request_dell.readyState);
		if(request_dell.readyState == 4)
		{
			try{
				alert(request_dell.status);
				alert(request_dell.statusText);
				if(request_dell.status==200 && request_dell.responseText)
				{
					alert(request_dell.responseText);
					var strDetails = "\"Service Tag\",\"System Type\",\"Ship Date\",\"Dell IBU\", \"Description\",\"Provider\",\"Start Date\",\"End Date\", \"Days Left\"";
					var arrHeadings = new Array("Service Tag:", "Days Left");
					var strPageText = request_dell.responseText;
					for(keyHeading in arrHeadings)
					{
						var strHeading = arrHeadings[keyHeading];						
						var intSummaryPos = (strPageText.toLowerCase()).indexOf(strHeading.toLowerCase())
						if(intSummaryPos > 0)
						{
							//asta inseamna ca am gasit ce ne trebuia in raspuns
							var intSummaryTableStart = (strPageText.toLowerCase()).lastIndexOf('<table', inSummaryPos);
							var intSummaryTableEnd = (strPageText.toLowerCase()).indexOf('</table>', intSummaryTableEnd)+8;
							var strInfoTable = strPageText.substring(intSummaryTableStart, intSummaryTableEnd-intSummaryTableStart);
							strInfoTable = ((strInfoTable.replace("\r\n", "")).replace("\r", "")).replace("\n", "");
							var arrCells = new Array()
							arrCells = (strInfoTable.toLowerCase()).split('</td>');
							for (var i=0; i<arrCells.length; i++)
							{
								arrCells[i] = trim(arrCells[i]);
								var intOpenTag = arrCells[i].indexOf('<');
								while(intOpenTag > 0)
								{
									var intCloseTag = arrCells[i].indexOf('>', intOpenTag)+1;
									var strNewCell = "";
									if(intOpenTag>1)
									{
										strNewCell+=trim(arrCells[i].substring(0, intOpenTag-1));									
									}
									if(intCloseTag<arrCells[i].length)
										strNewCell+=trim(arrCells[i].substring(intCloseTag))
									arrCells[i] = trim(strNewCell).replace(" &nbsp;&nbsp;&nbsp;&nbsp;change service tag","");
									intOpenTag = arrCells[i].indexOf('<');
								}
							}
							
							if(arrCells[0].toLowerCase() == "Service Tag:".toLowerCase())
							{
								var strCurrentTag = ""
								for(var intField = 0; intField<arrCells.length; intField+=2)
								{
									if(strCurrentTag == "")
										strCurrentTag = "\""+arrCells[intField]+"\"";
									else
										strCurrentTag += ",\""+arrCells[intField]+"\"";									
								}
							}
							else if(arrCells[0].toLowerCase() == "Description".toLowerCase())
							{
								for(var intField=4; intField<arrCells.length;i++)
									strCurrentTag += ",\""+arrCells[intField]+"\"";
							}
						}
						else{
							strCurrentTag = "\""+strServiceTag+"\",\"No warranty information found.\"";
						}
					}
					strDetails+="\n"+strCurrentTag;
					alert(strDetails);
				}
				else{
					alert("Failed reading response from the server");
				}
			}
			catch (error)
			{
				alert ('ERROR: Internal browser error: ' + error);
				request_dell = false;
			}
			request_dell = false;
		}
		
	}
	return true;
}

var customers_requester  = false;

function get_customers(filter, dn){
     if(customers_requester){
         alert("Another process already runs in the background, please try again later");
     }
     else{
         customers_requester = getXmlRequester();
         if(!customers_requester) alert ("Sorry, Ajax is not available");
         else{
             url = "/?cl=ajax&op=search_customers&f_filter="+filter+"&dn="+dn;
             customers_requester.open('GET', url);             
             customers_requester.send('');
             customers_requester.onreadystatechange = get_customers_Handler;
         }
     }
}

function get_customers_Handler(){
    if(customers_requester){
        if(customers_requester.readyState == 4){            
            try{
                if(customers_requester.status == 200 && customers_requester.responseXML){                    
                    var xmlDoc = customers_requester.responseXML.documentElement;
                    result = xmlDoc.getElementsByTagName("status")[0].childNodes[0].nodeValue;
                    if(result == 'ok'){
                        var dn = xmlDoc.getElementsByTagName('dn')[0].childNodes[0].nodeValue;
                        
                        var display_elem = document.getElementById('search_results_'+dn);
                        display_elem.innerHTML == '';
                        
                        var ixh = '';
                        ixh += "<table width='100%'><thead><tr>";
                        ixh += "<th width='15%'>Id</th>";
                        ixh += "<th width='60%'>Name</th>";
                        ixh += "<th width='15%'>ERP ID</th>";
                        ixh += "<th>&nbsp;</th>";
                        ixh += "</tr></thead><tbody>";
                        var customers_list = xmlDoc.getElementsByTagName("customer");
                        for(var i=0; i<customers_list.length;i++){
                            var customer = customers_list[i];
                            ixh += "<tr>";
                            
                            var cid = customer.getElementsByTagName('id')[0].childNodes[0].nodeValue;
                            var name = "--"
                            if(customer.getElementsByTagName('name')[0].childNodes.length > 0)
                                name = customer.getElementsByTagName('name')[0].childNodes[0].nodeValue;
                            var erp_id='';
                            if(customer.getElementsByTagName('erp_id')[0].childNodes.length > 0)
                                erp_id = customer.getElementsByTagName('erp_id')[0].childNodes[0].nodeValue;
                            ixh += "<td>"+cid+"</td>";
                            ixh += "<td>"+name+"<input type='hidden' name='"+dn+"_name_"+cid+"' id='"+dn+"_name_"+cid+"' value='"+name+"'></td>";
                            ixh += "<td>"+erp_id+"</td>";                            
                            if(i==0){
                                ixh += "<td><input type='radio' name='"+dn+"' id='"+dn+"' value='"+cid+"' checked='checked'></input></td>"
                            }
                            else
                            {
                                ixh += "<td><input type='radio' name='"+dn+"' id='"+dn+"' value='"+cid+"'></input></td>"
                            }
                            ixh += "</tr>";                                                              
                        }
                        ixh += "</tbody></table><br />";
                        ixh += "<input type='submit' value='Merge with selected' name='select_"+dn+"_id' id='select_"+dn+"_id' onclick='select_"+dn+"(\""+dn+"\")' ></input>";                        
                        display_elem.innerHTML += ixh;
                        display_elem.style.display='block';
                        
                    }
                    else{
                        aler('ERROR: The server did not confirm this resultset');
                    }
                } else         alert('ERROR: Failed reading response from server');                
            }
            catch(error){
                alert('ERROR: Internal browser error: ' + error);
            }
            customers_requester = false;
        }
    }
    return true;
}


