{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer"}
{include file="paging.html"}

<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script type="text/javascript" src="/javascript/jquery.min.js"></script>
<script type="text/javascript" src="/javascript/highcharts/highcharts.js"></script>
<script type="text/javascript" src="/javascript/highcharts/modules/exporting.js"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[

//modified by victor
// The names of available tabs
var tabs = new Array ('computers', 'adusers', 'tickets', 'peripherals', 'internet', 'photos', 'users', 'recipients', 'infos', 'backupconsole', 'nagvis');

var subtabs = new Array('backup', 'antivirus');
var tickets_subtabs = new Array('tickets', 'interventions', 'krifstats', 'irstats');
var computers_subtabs = new Array('computers', 'compstats');
var adusers_subtabs = new Array('adusers', 'userstats');
active_tab = "{$active_tab}";
bChartsLoaded = false;
bChartsLoadedComps = false;
bKchartsLoaded = false;
bADchartsLoaded = false;
{literal}

/*****************************HIGHCHARTS STUFF ******************************************/
jQuery.noConflict();
Highcharts.theme = { colors: ['#4572A7'] };// prevent errors in default theme
var highchartsOptions = Highcharts.getOptions();

function ir_stats(){
    var chart;
        jQuery(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'irstat_container',
                    zoomType: 'xy'
                },
                title: {
                    text: 'Intervention Reports and Hours Billed'
                },
                subtitle: {
                    text: 'Monthly stats'
                },
                xAxis: [{
                    categories: {/literal}{if $ir_evo_months != ""}{$ir_evo_months}{else}[]{/if}{literal} ,
                    labels: {
                        rotation: -45,
                        align: 'right',
                        style: {
                            font: 'normal 13px Verdana, sans-serif'
                        }
                     }
                }],
                yAxis: [{ // Primary yAxis
                    labels: {
                        formatter: function() {
                            return this.value +' h';
                        },
                        style: {
                            color: '#89A54E'
                        }
                    },
                    title: {
                        text: 'Hours billed',
                        style: {
                            color: '#89A54E'
                        }
                    }
                }, { // Secondary yAxis
                    title: {
                        text: 'Interventions',
                        style: {
                            color: '#4572A7'
                        }
                    },
                    labels: {
                        formatter: function() {
                            return this.value;
                        },
                        style: {
                            color: '#4572A7'
                        }
                    },
                    opposite: true
                }],
                tooltip: {
                    formatter: function() {
                        return ''+
                            this.x +': '+ this.y +
                            (this.series.name == 'Hours billed' ? ' hours' : '');
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    x: 120,
                    verticalAlign: 'top',
                    y: 100,
                    floating: true,
                    backgroundColor: Highcharts.theme.legendBackgroundColor || '#FFFFFF'
                },
                series: [{
                    name: 'Closed Interventions',
                    color: '#4572A7',
                    type: 'column',
                    yAxis: 1,
                    data: {/literal}{if $ir_evo_closed != ""}{$ir_evo_closed}{else}[]{/if}{literal}

                }, {
                    name: 'Centralized Interventions',
                    color: '#A77245',
                    type: 'column',
                    yAxis: 1,
                    data: {/literal}{if $ir_evo_centralized != ""}{$ir_evo_centralized}{else}[]{/if}{literal}

                }, {
                    name: 'Open Interventions',
                    color: '#72A772',
                    type: 'column',
                    yAxis: 1,
                    data: {/literal}{if $ir_evo_open != ""}{$ir_evo_open}{else}[]{/if}{literal}

                }, {
                    name: 'Hours billed',
                    color: '#89A54E',
                    type: 'spline',
                    data: {/literal}{if $ir_evo_hours_billed != ""}{$ir_evo_hours_billed}{else}[]{/if}{literal}
                }]
            });


        });
}

function tickets_cmonth_evo(){
    var chart;
        jQuery(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'tickets_evo_cm',
                    defaultSeriesType: 'areaspline'
                },
                title: {
                    text: 'Last 15 days tickets activity'
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    verticalAlign: 'top',
                    x: 150,
                    y: 100,
                    floating: true,
                    borderWidth: 1,
                    backgroundColor: Highcharts.theme.legendBackgroundColor || '#FFFFFF'
                },
                xAxis: {
                    categories: {/literal}{if $tickets_lmevo_days!=""}{$tickets_lmevo_days}{else}[]{/if}{literal},
                    labels: {
                        rotation: -45,
                        align: 'right',
                        style: {
                            font: 'normal 13px Verdana, sans-serif'
                        }
                     }
                },
                yAxis: {
                    title: {
                        text: 'Tickets'
                    }
                },
                tooltip: {
                    formatter: function() {
                            return ''+
                            this.x +': '+ this.y +' tickets';
                    }
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.5
                    }
                },
                series: [{
                    name: 'Closed',
                    data: {/literal}{if $tickets_lmevo_closed!=""}{$tickets_lmevo_closed}{else}[]{/if} {literal}
                }, {
                    name: 'Not Closed with activity',
                    data: {/literal}{if $tickets_lmevo_notclosed!=""}{$tickets_lmevo_notclosed}{else}[]{/if}{literal}
                }, {
                    name: 'New',
                    data: {/literal}{if $tickets_lmevo_new!=""}{$tickets_lmevo_new}{else}[]{/if} {literal}
                } ]
            });


        });
}

function computers_evolution()
{
    var chart;
        jQuery(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'container',
                    defaultSeriesType: 'column'
                },
                title: {
                    text: 'Computers monthly evolution'
                },
                xAxis: {
                    categories: {/literal}{if $comps_evo_months!=""}{$comps_evo_months}{else}[]{/if}{literal},
                    labels: {
                        rotation: -45,
                        align: 'right',
                        style: {
                            font: 'normal 13px Verdana, sans-serif'
                        }
                     }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Machines'
                    }
                },
                legend: {
                    layout: 'vertical',
                    backgroundColor: Highcharts.theme.legendBackgroundColor || '#FFFFFF',
                    align: 'left',
                    verticalAlign: 'top',
                    x: 100,
                    y: 70,
                    floating: true,
                    shadow: true
                },
                tooltip: {
                    formatter: function() {
                        return ''+
                            this.x +': '+ this.y;
                    }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                    series: [{
                    name: 'Servers',
                    data: {/literal}{if $comps_evo_servers!=""}{$comps_evo_servers}{else}[]{/if}{literal}

                }, {
                    name: 'Workstations',
                    data: {/literal}{if $comps_evo_workstations!=""}{$comps_evo_workstations}{else}[]{/if}{literal}

                }, {
                    name: 'Other',
                    data: {/literal}{if $comps_evo_other!=""}{$comps_evo_other}{else}[]{/if}{literal}

                }]
            });


        });
}

function brands_chart(){
    var chart;
 jQuery(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'brands_holder',
         plotBackgroundColor: null,
         plotBorderWidth: null,
         plotShadow: false
      },
      title: {
         text: 'Computer brands distribution'
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ this.y;
         }
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
               enabled: true,
               color: Highcharts.theme.textColor || '#000000',
               connectorColor: Highcharts.theme.textColor || '#000000',
               formatter: function() {
                  return '<b>'+ this.point.name +'</b>: '+ this.y;
               }
            }
         }
      },
       series: [{
         type: 'pie',
         name: 'Brand share',
         data: {/literal}{if $brands_values!=""}{$brands_values}{else}[]{/if}{literal}
      }]
   });
});
}

function oses_chart(){
    var chart;
 jQuery(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'oses_holder',
         plotBackgroundColor: null,
         plotBorderWidth: null,
         plotShadow: false
      },
      title: {
         text: 'Operating systems distribution'
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ this.y;
         }
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
               enabled: true,
               color: Highcharts.theme.textColor || '#000000',
               connectorColor: Highcharts.theme.textColor || '#000000',
               formatter: function() {
                  return '<b>'+ this.point.name +'</b>: '+ this.y;
               }
            }
         }
      },
       series: [{
         type: 'pie',
         name: 'Operating system share',
         data: {/literal}{if $oses_values!=""}{$oses_values}{else}[]{/if}{literal}
      }]
   });
});
}

function adusers_stats(){
    var chart;
   jQuery(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'adusers_evo_container',
         defaultSeriesType: 'column',
         margin: [ 50, 50, 100, 80]
      },
      title: {
         text: 'AD Users Evolution'
      },
      xAxis: {
         categories: {/literal}{if $adevo_months!=""}{$adevo_months}{else}[]{/if}{literal},
         labels: {
            rotation: -45,
            align: 'right',
            style: {
                font: 'normal 13px Verdana, sans-serif'
            }
         }
      },
      yAxis: {
         min: 0,
         title: {
            text: 'N� users'
         }
      },
      legend: {
         enabled: false
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.x +'</b><br/>'+
                'N� users: '+ Highcharts.numberFormat(this.y, 1);
         }
      },
           series: [{
         name: 'AD Users',
         data: {/literal}{if $adevo_totusers!=""}{$adevo_totusers}{else}[]{/if}{literal},
         dataLabels: {
            enabled: true,
            rotation: -90,
            color: Highcharts.theme.dataLabelsColor || '#FFFFFF',
            align: 'right',
            x: -3,
            y: 10,
            formatter: function() {
               return this.y;
            },
            style: {
               font: 'normal 13px Verdana, sans-serif'
            }
         }         
      }]
   });
});
}


var groups_stat = new Array(true, true, true, true);
var agroups_stat = new Array(true, true, true, true);
function expand_group(id)
{
	var stat = groups_stat[id];
	groups_stat[id] = (!groups_stat[id]);
	var group = document.getElementById('group_'+id);

	img = document.getElementById('img_'+id);
	if (stat)
	{
		img.src = '/images/expand.gif';
	}
	else
	{
		img.src = '/images/collapse.gif';
	}

	if(stat)
	{
		group.style.display = 'none';
	}
	else
	{
		group.style.display = 'block';
	}
}

function expand_agroup(id)
{
	var stat = agroups_stat[id];
	agroups_stat[id] = (!agroups_stat[id]);
	var group = document.getElementById('agroup_'+id);

	img = document.getElementById('img_'+id);
	if (stat)
	{
		img.src = '/images/expand.gif';
	}
	else
	{
		img.src = '/images/collapse.gif';
	}

	if(stat)
	{
		group.style.display = 'none';
	}
	else
	{
		group.style.display = 'block';
	}
}


// Retrieve a cookie value by name
function getCookie (cookie_name)
{
	var nameEQ = cookie_name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

// Set the active tab
function showTab (tab_name)
{
	// Hide all tabs first. Also make sure the tab is in the list
	found = false;

	for (i=0; i<tabs.length; i++)
	{
		document.getElementById('tab_' + tabs[i]).style.display = 'none';
		document.getElementById('tab_head_' + tabs[i]).className = 'tab_inactive';
		if (tabs[i] == tab_name) found = true;
	}

	if (!found) tab_name = tabs[0];

	//alert(tab_name);

	document.getElementById('tab_'+tab_name).style.display = 'block';
	document.getElementById('tab_head_'+tab_name).className = '';

	document.cookie = 'customer_view_tab='+tab_name;

	return false;
}

function showTab1(tab_name)
{
	document.getElementById('active_tab').value = tab_name;
	document.forms['frm'].submit();
}


function showSubTab(subtab_name)
{
	found = false;

	for (i=0; i<subtabs.length; i++)
	{
		document.getElementById('subtab_' + subtabs[i]).style.display = 'none';
		document.getElementById('subtab_head_' + subtabs[i]).className = 'tab_inactive';
		if (subtabs[i] == subtab_name) found = true;
	}

	if (!found) subtab_name = subtabs[0];

	document.getElementById('subtab_'+subtab_name).style.display = 'block';
	document.getElementById('subtab_head_'+subtab_name).className = '';

	document.cookie = 'customer_view_subtab='+subtab_name;

	return false;
}

function showTicketsSubTab(subtab_name)
{
	found = false;

	for(i=0; i<tickets_subtabs.length; i++)
	{
		document.getElementById('subtab_'+tickets_subtabs[i]).style.display = 'none';
		document.getElementById('subtab_head_'+tickets_subtabs[i]).className = 'tab_inactive';
		if(tickets_subtabs[i] == subtab_name) found=true;
	}

	if(!found) subtab_name = tickets_subtabs[0];
	document.getElementById('subtab_'+subtab_name).style.display = 'block';
    document.getElementById('subtab_'+subtab_name).visibility = "visible";
	document.getElementById('subtab_head_'+subtab_name).className = '';

    if(bKchartsLoaded == false){
        tickets_cmonth_evo();
        ir_stats();
        bKchartsLoaded = true;
    }

	document.cookie = 'customer_view_ticket_subtab='+subtab_name;
	return false;
}




function showComputersSubTab(subtab_name)
{
    found = false;
    /*
    if(subtab_name == "computers" && bChartsLoadedComps == false){
        {/literal}
        {foreach from=$customer_computers item=computer}
            space_charts({$computer->id});
        {/foreach}
        {literal}
        bChartsLoadedComps = true;
    }
    */


    for(i=0; i<computers_subtabs.length; i++)
    {
        document.getElementById('subtab_'+computers_subtabs[i]).style.display = 'none';
        document.getElementById('subtab_head_'+computers_subtabs[i]).className = 'tab_inactive';
        if(computers_subtabs[i] == subtab_name) found=true;
    }

    if(!found) subtab_name = computers_subtabs[0];
    document.getElementById('subtab_'+subtab_name).style.display = 'block';
    document.getElementById('subtab_head_'+subtab_name).className = '';

    if(subtab_name == "compstats" && bChartsLoaded == false) {
        document.getElementById('subtab_'+subtab_name).visibility = "visible";
        {/literal}
        {if $bcount > 0}
            brands_chart();
        {else}
             document.getElementById("brands_holder").innerHTML = "No computer brands detected";
             document.getElementById("brands_holder").className = 'raphael_holder_none';
        {/if}
        {if $oscount > 0}
            oses_chart();
        {else}
             document.getElementById("oses_holder").innerHTML = "No operating systems detected";
             document.getElementById("oses_holder").className = 'raphael_holder_none';
        {/if}

        computers_evolution();
        {literal}
        bChartsLoaded = true;
    }


    document.cookie = 'customer_view_computers_subtab='+subtab_name;
    return false;
}

function showAdusersSubTab(subtab_name)
{
    found = false;

    for(i=0; i<adusers_subtabs.length; i++)
    {
        document.getElementById('subtab_'+adusers_subtabs[i]).style.display = 'none';
        document.getElementById('subtab_head_'+adusers_subtabs[i]).className = 'tab_inactive';
        if(adusers_subtabs[i] == subtab_name) found=true;
    }

    if(!found) subtab_name = adusers_subtabs[0];
    document.getElementById('subtab_'+subtab_name).style.display = 'block';
    document.getElementById('subtab_'+subtab_name).visibility = "visible";
    document.getElementById('subtab_head_'+subtab_name).className = '';

    if(bADchartsLoaded == false){
        adusers_stats();
        bADchartsLoaded = true;
    }
    
    document.cookie = 'customer_view_adusers_subtab='+subtab_name;
    return false;
}


//function goToIRPage(direction)
//{
//	//here we are going to make an ajax call to get the requred IR's
//	var customer_id = {/literal}{$filter.customer_id}{literal};
//	var per_page = {/literal}{$filter.limit}{literal};
//	start = document.getElementById('filter[start]').value;
//	getNewSetOfInterventions(customer_id, start, per_page, direction);
//}

function changeBackupImage(im_holder,title, legend, pr, po, pg, pgr)
{
	//alert(title);
	{/literal}
	generateImage(im_holder, title,legend,pr, po, pg, pgr );
	{literal}
}


function search_customer_to_merge_with(elem_name){
    var elem = document.getElementById(elem_name);
    elem.style.display='block';
}

function search_customers(f_filter, dn){
    filter = document.getElementById(f_filter).value;
    get_customers(filter, dn);
}

function check_key(s_field, f_filter, e, dn){
    var keyID = (window.event) ? event.keyCode : e.keyCode;
    if(keyID == 13){
        s_field.form.submit = false;
        search_customers(f_filter, dn);
        return false;
    }
    return true;
}

function change_search_input_style(elem){
    elem.style.color="#000";
    elem.style.fontStyle="normal";
    elem.style.fontSize = "12px";
    elem.style.fontWheight = "bold";
    elem.value = "";
}

function close_search(elem_name, filter_name){
    var elem = document.getElementById(elem_name);
    var filter_elem = document.getElementById(filter_name);

    elem.style.display = 'none';
    filter_elem.style.color="#333";
    filter_elem.style.fontStyle = 'italic';
    filter_elem.style.fontWeight='normal';
    filter_elem.style.fontSize = "10px";
    filter_elem.value = "Type customer ID or NAME";

}

function select_merged_customer(element_name){
    var elm = document.frm.elements[element_name];
    var cnt = elm.length;
    if(cnt){
        for(var i=0;i<cnt; i++){
            if(elm[i].checked){
                var selected_customer_id = elm[i].value;
                var selected_customer_name = document.getElementById(element_name+"_name_"+selected_customer_id).value;
                document.frm.elements['merge_with'].value = selected_customer_id;
                document.frm.elements['do_merge'].value="1";
                if(confirm("Are you shure you want to merge all data from customer: (#"+selected_customer_id+") "+selected_customer_name+" into this customer: {/literal}(#{$customer->id}) {$customer->name}{literal} ?")){
                    document.frm.submit();
                }
            }
        }
    }
    else{
         var selected_customer_id = elm.value;
         var selected_customer_name = document.getElementById(element_name+"_name_"+selected_customer_id).value;
         document.frm.elements['merge_with'].value = selected_customer_id;
         document.frm.elements['do_merge'].value="1";
         if(confirm("Are you shure you want to merge all data from customer: (#"+selected_customer_id+") "+selected_customer_name+" into this customer: {/literal}(#{$customer->id}) {$customer->name}{literal} ?")){
             document.frm.submit();
         }
    }
}

{/literal}

//]]>
</script>


<h1>{if $view_only}View{else}Edit{/if} Customer: #{$customer->id}: {$customer->name}</h1>

<p class="error">{$error_msg}</p>

<form name="frm" action="" method="POST">
{$form_redir}

<table class="list" width="95%">
	<thead>
	<tr>
		{*<td colspan="4">Customer ID: # {$customer->id}</td>*}
		<td>Contract type:</td>
		<td colspan="3" class="post_highlight">
			{assign var="contract_type" value=$customer->contract_type}
			{if $contract_type}{$CONTRACT_TYPES.$contract_type}
			{else}--
			{/if}
		</td>
	</tr>
	</thead>

	<tr>
		<td width="20%" class="highlight">Sub-type:</td>
		<td width="30%" class="post_highlight">
			{assign var="contract_sub_type" value=$customer->contract_sub_type}
			{if $contract_sub_type}{$CUST_SUBTYPES.$contract_sub_type}
			{else}--
			{/if}
		</td>
		<td width="20%" class="highlight">Price type:</td>
		<td width="30%" class="post_highlight">
			{assign var="price_type" value=$customer->price_type}
			{if $price_type}{$CUST_PRICETYPES.$price_type}
			{else}--
			{/if}
		</td>
	</tr>

	<tr>
		<td class="highlight">Active: </td>
		<td class="post_highlight">
			{if $view_only}{if $customer->active}Yes{else}No{/if}
			{else}
				<select name="customer[active]">
					<option value="0">No</option>
					<option value="1" {if $customer->active}selected{/if}>Yes</option>
				</select>
			{/if}
		</td>
		<td class="highlight">Country: </td>
		<td class="post_highlight">
			<select name="customer[Country_D]">
				<option value="1">--</option>
				{html_options options=$countries selected=$customer->Country_D}
			</select>
		</td>
	</tr>

	<tr>
		<td class="highlight">Kawacs: </td>
		<td class="post_highlight">
			{if $view_only}{if $customer->has_kawacs}Yes{else}No{/if}
			{else}
				<select name="customer[has_kawacs]">
					<option value="0">No</option>
					<option value="1" {if $customer->has_kawacs}selected{/if}>Yes</option>
				</select>
			{/if}
		</td>

		<td class="highlight">Krifs: </td>
		<td class="post_highlight">
			{if $view_only}{if $customer->has_krifs}Yes{else}No{/if}
			{else}
				<select name="customer[has_krifs]">
					<option value="0">No</option>
					<option value="1" {if $customer->has_krifs}selected{/if}>Yes</option>
				</select>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">On hold: </td>
		<td class="post_highlight">
			{if $view_only}{if $customer->onhold}Yes{else}No{/if}
			{else}
				<select name="customer[onhold]">
					<option value="0">No</option>
					<option value="1" {if $customer->onhold}selected{/if}>Yes</option>
				</select>
			{/if}
		</td>
		<td class="highlight">Suspend alert e-mails:</td>
		<td class="post_highlight">
			{if $view_only}{if $customer->no_email_alerts}Yes{else}No{/if}
			{else}
				<select name="customer[no_email_alerts]">
					<option value="0">No</option>
					<option value="1" {if $customer->no_email_alerts}selected{/if}>Yes</option>
				</select>
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">ERP ID:</td>
		<td class="post_highlight">
			{if $view_only}
				{$customer->erp_id}
			{else}
				<input type="text" name="customer[erp_id]" value="{$customer->erp_id}" size="15" />
			{/if}
		</td>
		<td class="highlight">SLA time: </td>
		<td class="post_highlight">
			{if $view_only}
				{$customer->sla_hours}
			{else}
				<input type="text" name="customer[sla_hours]" value="{$customer->sla_hours}" size="6" /> hours
			{/if}
		</td>
	</tr>
	<tr>
		<td class="highlight">Account Manager:</td>
		<td class="post_highlight">
			{assign var="am" value=$customer->account_manager}
			{if $view_only}
				{$ACCOUNT_MANAGERS.$am}
			{else}
				<select name="customer[account_manager]" />
					{html_options options=$ACCOUNT_MANAGERS selected=$am}
				</select>
			{/if}
		</td>
		<td class="highlight">&nbsp;</td>
		<td class="post_highlight">&nbsp;
		</td>
	</tr>
	<tr>
		<td>
			{if $customer_style}
				<a href="/?cl=customer&amp;op=customer_template_style_edit&amp;customer_id={$customer->id}">Edit template style &#0187;</a>
			{else}
				<a href="/?cl=customer&amp;op=customer_template_style_edit&amp;customer_id={$customer->id}">Define new template style &#0187;</a>
			{/if}
		</td>

		<td class="post_highlight" colspan="3">

		</td>
	</tr>
	<tr>
		<td>
			<a href="/?cl=customer&amp;op=create_computer_group&amp;customer_id={$customer->id}">Create new group of computers &#0187;</a>
			<br /><a href="/?cl=customer&op=set_nagvis_data&customer_id={$customer->id}&returl={$ret_url}">Set Nagvis data &#0187;</a>
		</td>

		<td class="post_highlight" colspan="3">

		</td>
	</tr>
	<tr>
		<td>
			{if !$view_only}<input type="submit" name="save" value="Save">{/if}
			<input type="submit" name="cancel" value="Close">
		</td>
		<td class="post_highlight" colspan="3">

		</td>
	</tr>
</table>
<p/>
<input type="hidden" name="active_tab" id="active_tab" value="">
<table class="tab_header"><tr>
	<td id="tab_head_computers" class="tab_inactive"><a href="#" onclick="return showTab1('computers');" style="width: 60px;">Computers</a></td>
	<td id="tab_head_adusers" class="tab_inactive"><a href="#" onclick="return showTab1('adusers');" style="width: 60px;">AD Users</a></td>
	<td id="tab_head_tickets" class="tab_inactive"><a href="#" onclick="return showTab1('tickets');" style="width: 90px;">Tickets and IR</a></td>
	<td id="tab_head_peripherals" class="tab_inactive"><a href="#" onclick="return showTab1('peripherals');" style="width: 60px;">Peripherals</a></td>
	<td id="tab_head_internet" class="tab_inactive"><a href="#" onclick="return showTab1('internet');" style="width: 60px;">Internet</a></td>
	<td id="tab_head_photos" class="tab_inactive"><a href="#" onclick="return showTab1('photos');" style="width: 60px;">Photos</a></td>
	<td id="tab_head_users" class="tab_inactive"><a href="#" onclick="return showTab1('users');">Users &amp; Contacts</a></td>
	<td id="tab_head_recipients" class="tab_inactive"><a href="#" onclick="return showTab1('recipients');" style="width: 80px;">Recipients</a></td>
	<td id="tab_head_infos" class="tab_inactive"><a href="#" onclick="return showTab1('infos');">More Information</a></td>
	<td id="tab_head_backupconsole" class="tab_inactive"><a href="#" onclick="return showTab1('backupconsole');" style="width: 70px;">Dashboards</a></td>
	<td id="tab_head_nagvis" class="tab_inactive"><a href="#" onclick="return showTab1('nagvis');"  style="width: 50px;">Nagvis</a></td>
</tr></table>
<!-- Modified by Victor -->
<!-- Tab with computers information -->
<div id="tab_tickets" class="tab_content" style="display:none;">

<table class="tab_header">
<tr>
	<td id="subtab_head_tickets" class="tab_inactive"><a href="#" onclick="return showTicketsSubTab('tickets');">Tickets</a></td>
	<td id="subtab_head_interventions" class="tab_inactive"><a href="#" onclick="return showTicketsSubTab('interventions');"  style="width: 150px;">Interventions Reports</a></td>
    <td id="subtab_head_krifstats" class="tab_inactive"><a href="#" onclick="return showTicketsSubTab('krifstats');"  style="width: 150px;">Statistics</a></td>
    <td id="subtab_head_irstats" class="tab_inactive"><a href="#" onclick="return showTicketsSubTab('irstats');"  style="width: 150px;">IR Statistics</a></td>

</tr></table>

<div id='subtab_tickets' class='tab_content' style="display: none;">
	<h2>Open Tickets</h2>
	<p/>

	[ <a href="/?cl=krifs&amp;op=manage_tickets&amp;customer_id={$customer->id}&amp;user_id=-1&amp;view=1&amp;escalated_only=0">Detailed List</a> ]
	[ <a href="/?cl=krifs&amp;op=ticket_add&amp;customer_id={$customer->id}">Create New Ticket</a> ]
	<p/>

	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="30">ID</td>
			<td>Subject</td>
			<td>Created</td>
			<td>Updated</td>
			<td>Status</td>
			<td>Assigned to</td>
		</tr>
		</thead>

		{foreach from=$customer_tickets item=ticket}
		<tr>
			<td nowrap="nowrap"><a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}">#{$ticket->id}</a></td>
			<td><a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}">{$ticket->subject|escape}</a></td>
			<td nowrap="nowrap">{$ticket->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td nowrap="nowrap">{$ticket->last_modified|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
			<td>
				{assign var="status" value=$ticket->status}
				{$TICKET_STATUSES.$status}
			</td>
			<td>
				{assign var="user_id" value=$ticket->assigned_id}
				{$users_list.$user_id}
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="5">[No open tickets]</td>
		</tr>
		{/foreach}
	</table>
	<p/>
</div>
<div id='subtab_interventions' class='tab_content' style="display: none;">
	<h2>Interventions Reports</h2>


	<table width="98%">
		<tr>
			<td width="50%">
				&nbsp;
			</td>
			<td width="50%" align="right">
			{if $tot_interventions > $filter.limit}
				{if $filter.start > 0}
					<a href="" onClick="document.getElementById('go').value ='prev'; document.forms['frm'].submit(); return false;">&#0171; Previous</a>
				{else}
					<font class="light_text">&#0171; Previous</font>
				{/if}
				<select name="filter[start]" onChange="document.getElementById('go').value='direct'; document.forms['frm'].submit()">
					{html_options options=$pages selected=$filter.start}
				</select>
				{if $filter.start + $filter.limit < $tot_interventions}
					<a href="" onClick="document.getElementById('go').value='next'; document.forms['frm'].submit(); return false;">Next &#0187;</a>
				{else}
					<font class="light_text">Next &#0187;</font>
				{/if}
			{/if}
		</td>
	</tr>
		</tr>
	</table>
	<input type="hidden" name="filter[limit]" value="{$filter.limit}" />
	<input type="hidden" id="go" name="go" value="" />


	<div id="div_ir_content">
	<table class="list" width="98%">
	<thead>
	<tr>
		<td width="1%">ID</td>
		<td {if $filter.customer_id > 0} width="49%" {else} width="29%" {/if}>Subject</td>
		{if !$filter.customer_id > 0}
			<td width="20%">Customer</td>
		{/if}
		<td width="15%">Status</td>
		<td width="10%">Created</td>
		<td width="8%" align="right" nowrap="nowrap">Work time</td>
		<td width="7%" align="right" nowrap="nowrap">Billable amount</td>
		<td width="7%" align="right" nowrap="nowrap">TBB amount</td>
		<td width="10%"> </td>
	</tr>
	</thead>

	{foreach from=$interventions item=intervention}
	<tr {if $intervention->tickets}class="no_bottom_border"{/if}>
		<td><a href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}"
		>{$intervention->id}</a>{if !$intervention->has_complete_info()}&nbsp;<font class="warning">!</font>{/if}</td>

		<td><a href="/?cl=krifs&amp;op=intervention_edit&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}">{$intervention->subject}</a>
		</td>

		{if !$filter.customer_id > 0}
			<td>
				{assign var="customer_id" value=$intervention->customer_id}
				<a href="/?cl=customer&amp;op=customer_edit&amp;id={$customer_id}">{$customers_list.$customer_id}</a>
			</td>
		{/if}

		<td>
			{assign var="status" value=$intervention->status}
			{$INTERVENTION_STATS.$status}
		</td>
		<td nowrap="nowrap">{$intervention->created|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>

		<td align="right">{$intervention->work_time|@format_interval_minutes}</td>
		<td align="right">{$intervention->bill_amount}</td>
		<td align="right">{$intervention->tbb_amount}</td>

		<td align="right" nowrap="nowrap">
			<a href="/?cl=krifs&amp;op=intervention_delete&amp;id={$intervention->id}{if $do_filter}&amp;do_filter=1{/if}"
				onclick="return confirm('Are you sure you want to delete this intervention report?');"
			>Delete &#0187;</a>
		</td>
	</tr>

	{if $intervention->tickets}
	<tr>
		<td> </td>
		<td {if $filter.customer_id > 0} colspan="8" {else} colspan="9" {/if}>
			<ul style="margin-top: 0px; margin-bottom: 2px">
				{foreach from=$intervention->tickets item=ticket}
				<li>
				<a href="/?cl=krifs&amp;op=ticket_edit&amp;id={$ticket->id}">#{$ticket->id}</a>:
				{$ticket->subject|escape}
				</li>
				{/foreach}
			</ul>
		</td>
	</tr>
	{/if}

	{foreachelse}
	<tr>
		<td class="light_text" {if $filter.customer_id > 0} colspan="9" {else} colspan="10" {/if}>[No matching intervention reports]</td>
	</tr>
	{/foreach}
</table>
</div>




</div>
<div id="subtab_krifstats" class="tab_content" style="display: none;">
    <h2>Krifs Statistics</h2>
    <p />
    <ul style="list-style: none; margin: 0; padding: 0; overflow: hidden;">
        <li>
            <div id="tickets_evo_cm" class="raphael_holder" style="height:410px; margin: 0 2em; clear:both; width: 90%; min-width: 600px"></div>  <br />
        </li>
    </ul>
</div>
<div id="subtab_irstats" class="tab_content" style="display: none;">
    <h2>Interventions Statistics</h2>
    <p />
    <ul style="list-style: none; margin: 0; padding: 0; overflow: hidden;">
        <li>
            <div id="irstat_container" class="raphael_holder" style="height:410px; margin: 0 2em; clear:both; width: 90%; min-width: 600px"></div>  <br />
        </li>

    </ul>
</div>
</div>
<!-- end modified by Victor -->

<!-- Tab with computers information -->
<div id="tab_computers" class="tab_content" style="display:none;">
<table class="tab_header">
<tr>
    <td id="subtab_head_computers" class="tab_inactive"><a href="#" onclick="return showComputersSubTab('computers');" >Computers</a></td>
    <td id="subtab_head_compstats" class="tab_inactive"><a href="#" onclick="return showComputersSubTab('compstats');" >Statistics</a></td>
</tr>
</table>
    <div id='subtab_computers' class='tab_content' style="display: none;">
        <h2>Computers and AD Users</h2>
        <p/>
        <table width="98%">
	        <tr>
		        <td width="50%">
			        <table class="list" width="95%">
				        <thead>
				        <tr>
					        <td>
						        Computers &nbsp;&nbsp;|&nbsp;&nbsp;
						        <a href="/?cl=kawacs&customer_id={$customer->id}">Detailed list &#0187;</a>
						        {if count($customer_computers) > 0}
						        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
						        <a href="/?cl=kawacs_removed&amp;op=remove_multi_computers&amp;customer_id={$customer->id}">Remove computers &#0187;</a>
						        {/if}
					        </td>
				        </tr>
				        </thead>

				        {foreach from=$customer_computers item=computer}
				        <tr>
					        <td>
						        <a href="/?cl=kawacs&op=computer_view&id={$computer->id}">#{$computer->id}: {$computer->netbios_name}</a>
						        {if $computer->roles}
							        :
							        {foreach from=$computer->roles key=role_id item=role_name name=computer_roles}
								        {$role_name}{if !$smarty.foreach.computer_roles.last}, {/if}
							        {/foreach}
						        {/if}
						        {if $computer->comments}
							        <br/>{$computer->comments|escape|nl2br}
						        {/if}
						        {if $computer->notifications}
							        <br/>
							        <ul style="margin-top: 0px; margin-bottom: 0px;">
							        {foreach from=$computer->notifications item=notification}
								        {assign var="level" value=$notification->level}
								        <li style="color: {$ALERT_COLORS.$level}">
								        {$notification->text|escape}</li>
							        {/foreach}
							        </ul>
						        {/if}

					        </td>
				        </tr>
				        {foreachelse}
				        <tr>
					        <td class="light_text">[No computers]</td>
				        </tr>
				        {/foreach}
			        </table>

			        {if count($removed_computers_list) > 0}
			        <p/>
			        <table class="list" width="95%">
				        <thead>
				        <tr>
					        <td>
						        Removed computers &nbsp;&nbsp;|&nbsp;&nbsp;
						        <a href="/?cl=kawacs_removed&amp;op=manage_computers&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
					        </td>
				        </tr>
				        </thead>

				        {foreach from=$removed_computers_list key=removed_computer_id item=removed_computer_name}
				        <tr>
					        <td>
						        <a href="/?cl=kawacs_removed&amp;op=computer_view&amp;id={$removed_computer_id}"
						        >#{$removed_computer_id}: {$removed_computer_name|escape}</a>
					        </td>
				        </tr>
				        {/foreach}
			        </table>
			        {/if}
		        </td>

		        <td width="50%">
			        <table class="list" width="100%">
				        <thead>
				        <tr>
					        <td>
						        Computer groups &nbsp;&nbsp;|&nbsp;&nbsp;
						        <a href="/?cl=customer&amp;op=view_computer_groups&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
					        </td>
				        </tr>
				        </thead>
				        {foreach from=$computer_groups item="group"}
				        <tr>
					        <td>
						        <a href="/?cl=customer&op=view_computer_groups&customer_id={$customer->id}&gid={$group->id}"
						        >#({$group->id}) {$group->title}
						        </a>
					        </td>
				        </tr>
				        {foreachelse}
				        <tr>
					        <td class="light_text">[No groups]</td>
				        </tr>
				        {/foreach}
				        {*
				        {foreach from=$customer_ad_users item=user}
				        <tr>
					        <td>
						        <a href="/?cl=kerm&op=ad_user_view&computer_id={$user->computer_id}&nrc={$user->nrc}"
						        >{$user->sam_account_name}:
							        {$user->display_name}
						        </a>,
						        {$user->email}
					        </td>
				        </tr>
				        {foreachelse}
				        <tr>
					        <td class="light_text">[No AD users]</td>
				        </tr>
				        {/foreach}
				        *}
			        </table>
		        </td>
	        </tr>
        </table>
        <p/>
    </div>
    <div id='subtab_compstats' class='tab_content' style="display: none;">
        <h2>Computers statistics</h2>
        <p />
        <ul style="list-style: none; margin: 0; padding: 0; overflow: hidden; width: 98%;">

            <li>
                <div id="container" class="raphael_holder" style="height:420px; margin: 0 2em; clear:both; width: 95%; min-width: 600px"></div>  <br />
            </li>
            <li style="margin: 0 auto; padding: 0;">
                <div id="brands_holder" class='raphael_holder' style="width: 95%; min-width: 600px; height: 420px;">&nbsp;</div>
            </li>
            <li style="margin: 0 auto; padding: 0;">
                <div id="oses_holder" class='raphael_holder' style="width: 95%; min-width: 600px; height: 420px;">&nbsp;</div>
            </li>

        </ul>
    </div>
</div>

<!-- Tab with the peripherals for this customer -->
<div id="tab_peripherals" class="tab_content" style="display:none;">
<table width="95%">
<tr>
	<td width="50%">
	<h2>Peripherals</h2>
	<p/>

	<table class="list" width="90%">
		<thead>
		<tr>
			<td>
			Peripherals &nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="/?cl=kawacs&amp;op=manage_peripherals&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="/?cl=kawacs_removed&amp;op=remove_multi_peripherals&amp;customer_id={$customer->id}">Remove Peripherals &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$peripherals_list key="peripheral_id" item=peripheral}
		<tr>
			<td><a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripheral|escape}</a></td>
		</tr>
		{foreachelse}
		<tr>
			<td class="light_text">[No peripherals]</td>
		</tr>
		{/foreach}
	</table>

	{if count($removed_peripherals_list)>0}
	<p/>
	<table class="list" width="90%">
		<thead>
		<tr>
			<td>
			Removed Peripherals
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="/?cl=kawacs_removed&amp;op=manage_peripherals&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$removed_peripherals_list key=removed_peripheral_id item=removed_peripheral_name}
		<tr>
			<td><a href="/?cl=kawacs_removed&amp;op=peripheral_view&amp;id={$removed_peripheral_id}">#{$removed_peripheral_id}: {$removed_peripheral_name|escape}</a></td>
		</tr>
		{/foreach}
	</table>
	{/if}

	</td>
	<td width="50%">
	<h2>AD Printers</h2>
	<p/>

	<table class="list" width="90%">
		<thead>
		<tr>
			<td>
			AD Printers
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="/?cl=kerm&amp;op=manage_ad_printers&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
			<a href="/?cl=kawacs_removed&amp;op=remove_multi_ad_printers&amp;customer_id={$customer->id}">Remove AD Printers &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$ad_printers_list key=ad_printer_id item=ad_printer_name}
		<tr>
			<td><a href="/?cl=kerm&amp;op=ad_printer_view&amp;id={$ad_printer_id}">{$ad_printer_name|escape}</a></td>
		</tr>
		{foreachelse}
		<tr>
			<td class="light_text">[No AD Printers]</td>
		</tr>
		{/foreach}
	</table>

	{if count($removed_ad_printers_list) > 0}
	<h2>Removed AD Printers</h2>
	<p/>
	<table class="list" width="90%">
		<thead>
		<tr>
			<td>
			Removed AD Printer
			&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="/?cl=kawacs_removed&amp;op=manage_ad_printers&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$removed_ad_printers_list key=removed_ad_printer_id item=removed_ad_printer_name}
		<tr>
			<td><a href="/?cl=kawacs_removed&amp;op=ad_printer_view&amp;id={$removed_ad_printer_id}">{$removed_ad_printer_name|escape}</a></td>
		</tr>
		{/foreach}
	</table>
	{/if}


	</td>
</tr>
</table>
<p/>
</div>

<!-- Tab with Internet related information -->
<div id="tab_internet" class="tab_content" style="display:none;">

	<h2>Customer Internet Contracts</h2>
	<p/>
	[ <a href="/?cl=klara&amp;op=manage_access&amp;customer_id={$customer->id}">KLARA Access Information</a> ]
	[ <a href="/?cl=kawacs&amp;op=manage_monitored_ips&amp;customer_id={$customer->id}">Internet Monitoring</a> ]

	<p/>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="20%">Contracts</td>
			<td>
				<a href="/?cl=klara&amp;op=customer_internet_contract_add&amp;&customer_id={$customer->id}&amp;returl={$ret_url}">Add contract &#0187;</a>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<a href="/?cl=klara&amp;op=manage_customer_internet_contracts&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$customer_internet_contracts item=contract}
		<tr>
			<td><a href="/?cl=klara&amp;op=customer_internet_contract_edit&amp;id={$contract->id}&amp;returl={$ret_url}">Contract details &#0187;</a></td>
			<td>

				<a href="/?cl=klara&amp;op=provider_edit&amp;id={$contract->provider->id}&amp;returl={$ret_url}">{$contract->provider->name}</a> :
				{$contract->provider_contract->name}
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td> </td><td class="light_text">[No contracts defined]</td>
		</tr>
		{/foreach}
	</table>
	<p/>
</div>

<!-- Table with customer photos -->
<div id="tab_photos" class="tab_content" style="display:none;">

	<h2>Customer Photos</h2>
	<p/>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td width="20%">Subject</td>
			<td colspan="2">
				<a href="/?cl=customer&amp;op=customer_photo_add&amp;&customer_id={$customer->id}&amp;returl={$ret_url}">Add photo &#0187;</a>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<a href="/?cl=customer&amp;op=manage_customers_photos&amp;customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>
		</thead>

		{foreach from=$customer_photos item=photo}
		<tr>
			<td><a href="/?cl=customer&amp;op=customer_photo_edit&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->subject|escape|nl2br}</a></td>
			<td>
				<a href="/?cl=customer&amp;op=customer_photo_view&amp;id={$photo->id}&amp;returl={$ret_url}">{$photo->get_thumb_tag()}</a>
			</td>
			<td>
				{if $photo->object_class}
					Linked to
					{if $photo->object_class==$smarty.const.PHOTO_OBJECT_CLASS_COMPUTER}
						Computer:
						{assign var="computer_id" value=$photo->object_id}
						<a href="/?cl=kawacs&amp;op=computer_view&amp;id={$computer_id}">{$computers_list.$computer_id}</a>
					{elseif $photo->object_class==$smarty.const.PHOTO_OBJECT_CLASS_PERIPHERAL}
						Peripheral:
						{assign var="peripheral_id" value=$photo->object_id}
						<a href="/?cl=kawacs&amp;op=peripheral_edit&amp;id={$peripheral_id}">{$peripherals_list.$peripheral_id}</a>
					{/if}
					<br/>
				{/if}
				{$photo->comments|escape|nl2br}
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td> </td><td class="light_text" colspan="2">[No photos uploaded]</td>
		</tr>
		{/foreach}
	</table>
	<p/>
</div>

<!-- Tab with users and contacts -->
<div id="tab_users" class="tab_content" style="display:none;">
	<h2>Users and Contacts</h2>
	<p/>
	<table width="98%" class="list">

		<!-- Users and contacts -->
		<tr class="head">
			<td width="120">Users</td>
			<td>
				<a href="/?cl=user&op=user_add&customer_id={$customer->id}&ret=customer">Add user &#0187;</a>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<a href="/?cl=user&op=manage_users&customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>

		{foreach from=$customer_users item=user}
		<tr>
			<td> </td>
			<td>
				<a href="/?cl=user&op=user_edit&id={$user->id}&ret=customer&customer_id={$customer->id}">#{$user->id}: {$user->fname} {$user->lname}</a>
				({$user->login}) &nbsp;&nbsp;&nbsp; E-mail: {$user->email}
			</td>
		</tr>
		{foreachelse}
			<tr><td></td><td>[No users defined]</td></tr>
		{/foreach}


		<tr class="head">
			<td width="120">Contacts</td>
			<td>
				<a href="/?cl=customer&op=customer_contact_add&customer_id={$customer->id}&returl={$returl}">Add contact &#0187;</a>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<a href="/?cl=customer&op=manage_customers_contacts&customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>

		{foreach from=$customer_contacts item=contact}
		<tr>
			<td> </td>
			<td>
				<a href="/?cl=customer&op=customer_contact_edit&id={$contact->id}&returl={$returl}">{$contact->fname} {$contact->lname}</a>
				&nbsp;&nbsp;&nbsp; E-mail: <a href="mailto:{$contact->email}">{$contact->email}</a>
				{foreach from=$contact->phones item=phone}
				<br/>&nbsp;&nbsp;&nbsp; Tel: {$phone->phone}
				{/foreach}
			</td>
		</tr>
		{foreachelse}
			<tr><td></td><td>[No contacts defined]</td></tr>
		{/foreach}
	</table>
	<p/>
</div>

<!-- Tab with notification recipients  -->
<div id="tab_recipients" class="tab_content" style="display:none;">
	<h2>Notifications recipients</h2>
	<p/>
	<table width="98%" class="list">
		<tr class="head">
			<td colspan="2">Keysource recipients</td>
			<td>&nbsp;</td>
			<td colspan="2">Customer recipients</td>
		</tr>

		{assign var="customer_id" value=$customer->id}
		{foreach from=$customer_notif_recips item=recips key=class_id name="notif_classes"}
		<tr>
			<td width="20%">
			<a href="/?cl=user&op=notification_customer_recipients_edit&customer_id={$customer_id}&class_id={$class_id}&ret=customer">{$NOTIF_OBJ_CLASSES.$class_id}</a>
			</td>
			<td width="30%">
				{foreach from=$recips item=recip_id}
					{if $default_recipients.$customer_id.$class_id == $recip_id}<b>{/if}
					{$users_list.$recip_id}<br>
					{if $default_recipients.$customer_id.$class_id == $recip_id}</b>{/if}
				{foreachelse}
					[Default recipients]
				{/foreach}
			</td>

			{if $smarty.foreach.notif_classes.first}

				<td rowspan="{$customer_notif_recips|@count}"> </td>
				<td width="20%" rowspan="{$customer_notif_recips|@count}">
				<a href="/?cl=user&op=notification_customer_recipients_customers_edit&customer_id={$customer_id}&ret=customer">Alert recipients</a>
				</td>
				<td width="30%" rowspan="{$customer_notif_recips|@count}">
					{if $recipients_customers.$customer_id}
						{foreach from=$recipients_customers.$customer_id item=user_id}
							{if $default_recipients_customers.$customer_id == $user_id}<b>{/if}

							{$users_list_customer.$user_id} ({$user_id})<br>

							{if $default_recipients_customers.$customer_id == $user_id}</b>{/if}
						{/foreach}
					{elseif $customers_users_list.$customer_id}
						{foreach from=$customers_users_list.$customer_id key=user_id item=user_name name=not_assigned}
							<!-- Show only the first in the list, because this is the user who will get notifications -->
							{if $smarty.foreach.not_assigned.first}
								<i>(Not assigned) {$user_name} ({$user_id})</i><br>
							{/if}
						{/foreach}
					{else}
						<font class="light_text">[<i>no users available</i>]</font>
					{/if}
				</td>
			{/if}
		</tr>
		{/foreach}
	</table>
	<p/>
	<h2>Tickets Default CC Recipients</h2>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td>CC Recipients</td>
		</tr>
		</thead>
		{foreach from=$cc_recipients item=cc_recipient}
			<tr><td>{$cc_recipient->get_name()|escape}</td></tr>
		{foreachelse}
			<tr><td class="light_text">[No default CC recipients defined]</td></tr>
		{/foreach}
	</table>
	<p/>


	<h2>Assigned Users and Groups</h2>
	<table width="98%" class="list">
		<thead>
		<tr>
			<td colspan="2">Users and groups who have this customer on their list of assigned customers.</td>
		</tr>
		</thead>

		<tr>
			<td width="120">Users and groups:</td>
			<td>
				{foreach from=$assigned_users item=user_name key=user_id}
					{$user_name}<br>
				{foreachelse}
					[None]
				{/foreach}
			</td>
		</tr>
	</table>
	<p/>
</div>

<!-- Tab with additional information and link to detailed pages -->
<div id="tab_infos" class="tab_content" style="display:none;">
	<h2>More Information</h2>
	<p>Use the links below to access more detailed information for this customer:</p>

	<table width="800">
		<tr><td width="200">
			<b>KAWACS:</b>
			<ul>
				<li><a href="/?cl=kawacs&customer_id={$customer->id}">Computers &#0187;</a></li>
				<li><a href="/?cl=kawacs&op=manage_peripherals&customer_id={$customer->id}">Peripherals &#0187;</a></li>
				<li><a href="/?cl=warranties&op=manage_warranties&customer_id={$customer->id}">Warranties &#0187;</a></li>
			</ul>

			<b>KRIFS:</b>
			<ul>
				<li><a href="/?cl=krifs&op=manage_tickets&customer_id={$customer->id}">Tickets &#0187;</a></li>
				<li><a href="/?cl=krifs&op=manage_interventions&customer_id={$customer->id}">Intervention Reports &#0187;</a></li>
			</ul>

		</td><td width="200">
			<b>KLARA:</b>
			<ul>
				<li><a href="/?cl=klara&op=manage_customer_internet_contracts&customer_id={$locked_customer->id}">Internet contracts &#0187;</a></li>
				<li><a href="/?cl=klara&op=manage_access&customer_id={$customer->id}">KLARA access info &#0187;</a></li>
				<li><a href="/?cl=klara&op=manage_access_phones&customer_id={$locked_customer->id}">KLARA access phones &#0187;</a></li>
			</ul>

			<b>Other:</b>
			<ul>
				<li><a href="/?cl=kalm&op=manage_licenses&customer_id={$customer->id}">Software licenses &#0187;</a></li>
				<li><a href="/?cl=user&op=manage_users&customer_id={$customer->id}">Users &#0187;</a></li>
				<li><a href="/?cl=customer&op=manage_customers_contacts&customer_id={$customer->id}">Contacts &#0187;</a></li>
				<li><a href="/?cl=customer&op=manage_customers_photos&customer_id={$customer->id}">Customer photos &#0187;</a></li>
				<li><a href="/?cl=customer&op=manage_locations&customer_id={$customer->id}">Customer locations &#0187;</a></li>
			</ul>

		</td>
        <td with="400">
            <b>Delete or Merge</b>
            <ul>
                <li><a href="/?cl=customer&op=customer_delete&id={$customer->id}" onclick="return confirm('Are you shure you want to permanently delete customer (#{$customer->id}) {$customer->name} ?');">Delete customer &#0187;</a></a></li>
                <li>
                    <input type="hidden" name="merge_with" id="merge_with" value=""></input>
                    <input type="hidden" name="do_merge" id="do_merge" value="0"></input>
                    <a href="#" onclick="search_customer_to_merge_with('div_merged_customer');">Merge a customer into this one &#0187;</a><br />
                    <div name="div_merged_customer" id="div_merged_customer" style="display: none; width: 100%; margin-top: -2px; margin-left: -2px;">
                        <div>
                            <table>
                                <tbody>
                                    <tr>
                                        <td class="headlight" width="100%"><b>Search:</b></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text" size="50" style="border: 1px solid black; color: #333; font-style: italic;"
                                                name="f_filter_mcid" id="f_filter_mcid"
                                                onkeypress="return check_key(this, 'f_filter_mcid', event, 'merged_customer')"
                                                onfocus="change_search_input_style(this);"
                                                value="type customer ID or NAME)" >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div id="search_results_merged_customer" style="display: none;"></div>
                            <div style="display: block;"><a href="#" onclick="close_search('div_merged_customer', 'f_filter_mcid');">Close</a></div>
                        </div>
                    </div>
                </li>
            </ul>
        </td>
        </tr>
	</table>
	<p/>

	<h2>Customer comments</h2>
	<table width="98%" class="list">
		<tr class="head">
			<td width="20%">Comments</td>
			<td>
				<a href="/?cl=customer&op=customer_comment_add&customer_id={$customer->id}&ret=customer">Add comment &#0187;</a>
				&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
				<a href="/?cl=customer&op=manage_customers_comments&customer_id={$customer->id}">Detailed list &#0187;</a>
			</td>
		</tr>

		{foreach from=$customer_comments item=comment}
		<tr>
			<td> </td>
			<td>
				<a href="/?cl=customer&op=customer_comment_edit&id={$comment->id}&ret=customer&customer_id={$customer->id}">{$comment->subject}</a><br/>
			</td>
		</tr>
		{foreachelse}
			<tr><td> </td><td>[No comments defined]</td></tr>
		{/foreach}
	</table>
	<p/>
</div>

<!-- Added by Victor -->
<div id="tab_backupconsole" class="tab_content" style="display:none;">

<table class="tab_header">
<tr>
	<td id="subtab_head_backup" class="tab_inactive"><a href="#" onclick="return showSubTab('backup');">Backup</a></td>
	<td id="subtab_head_antivirus" class="tab_inactive"><a href="#" onclick="return showSubTab('antivirus');">Antivirus</a></td>
</tr></table>

<div id='subtab_backup' class='tab_content' style="display: none">
	<h2>Backup statuses</h2>
	<p>The backup statuses of your computers:</p>

	<div id="dhtmlgoodies_xpPane">
	{if $count_r!=0}
	<div onclick="expand_group(0)" style="cursor: hand;">
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: red;"><img src="/images/collapse.gif" width="10" height="11" id="img_0"></td>
		<td>Computers reporting backup error	   [{$count_r} computers]</td>
	</tr>
	</table>
	</div>
	<div class="dhtmlgoodies_panel" id='group_0'>
		<div>
		<table class="list" width="100%">
		<tr class='head'>
			<td style="width: 10%;">ID</td>
			<td style="width: 40%;">Name</td>
			<td style="width: 20%;">Profile</td>
			<td style="width: 30%;">Status</td>
			<!-- <td style="width: 35%;">Backup Reports</td> -->
		</tr>
		{foreach from=$computers_red item=computerr}
		<tr>
				{assign var=stats value=$computerr->backup_status()}
				{assign var=citem value=$stats[0]}
				{assign var=status value=$citem->get_specific_value('Status')}
				{assign var=message value=$citem->get_specific_value('Message')}
				{assign var=td value='<td style="color: red;">'}
				{$td}{$computerr->id}</td>
				{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerr->id}">{$computerr->netbios_name}</a></td>
				<td>{assign var=pid value=$computerr->profile_id}{$profiles[$pid]}</td>
				{$td}<a style="color: red; text-decoration: none;"  href='/?cl=kawacs&op=computer_view_item&id={$computerr->id}&item_id={$citem->itemdef->id}'>{$status}</a></td>
			</tr>
		{/foreach}
		</table>
		</div>
	</div>
	{/if}
	{if $count_o!=0}
	<div onclick="expand_group(1)" style="cursor: hand;" >
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: orange;"><img src="/images/collapse.gif" width="10" height="11" id="img_1"></td>
		<td>Computers reporting tape related backup error	   [{$count_o} computers]</td>
	</tr>
	</table>
	</div>
	<div class="dhtmlgoodies_panel" id='group_1'>
		<div>
		<table class="list" width="100%">
		<tr class='head'>
			<td style="width: 10%;">ID</td>
			<td style="width: 40%;">Name</td>
			<td style="width: 20%;">Profile</td>
			<td style="width: 30%;">Status</td>
			<!-- <td style="width: 35%;">Backup Reports</td> -->
		</tr>
		{foreach from=$computers_orange item=computero}
		<tr>
				{assign var=stats value=$computero->backup_status()}
				{assign var=citem value=$stats[0]}
				{assign var=status value=$citem->get_specific_value('Status')}
				{assign var=message value=$citem->get_specific_value('Message')}
				{assign var=td value='<td style="color: orange;">'}
				{$td}{$computero->id}</td>
				{$td}<a href="/?cl=kawacs&op=computer_view&id={$computero->id}">{$computero->netbios_name}</a></td>
				<td>{assign var=pid value=$computero->profile_id}{$profiles[$pid]}</td>
				{$td}<a style="color: orange; text-decoration: none;"  href='/?cl=kawacs&op=computer_view_item&id={$computero->id}&item_id={$citem->itemdef->id}'>{$status}</a></td>
			</tr>
		{/foreach}
		</table>
		</div>
	</div>
	{/if}
	{if $count_g != 0}
	<div onclick="expand_group(2)" style="cursor: hand;">
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: green;"><img src="/images/collapse.gif" width="10" height="11" id="img_2"></td>
		<td>Computers reporting backup success	   [{$count_g} computers]</td>
	</tr>
	</table>
	</div>
	<div class="dhtmlgoodies_panel" id='group_2'>
		<div>
		<table class="list" width="100%">
		<tr class='head'>
			<td style="width: 10%;">ID</td>
			<td style="width: 40%;">Name</td>
			<td style="width: 20%;">Profile</td>
			<td style="width: 30%;">Status</td>
			<!-- <td style="width: 35%;">Backup Reports</td> -->
		</tr>
		{foreach from=$computers_green item=computerv}
		<tr>
				{assign var=stats value=$computerv->backup_status()}
				{assign var=citem value=$stats[0]}
				{assign var=status value=$citem->get_specific_value('Status')}
				{assign var=message value=$citem->get_specific_value('Message')}
				{assign var=td value='<td style="color: green;">'}
				{$td}{$computerv->id}</td>
				{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerv->id}">{$computerv->netbios_name}</a></td>
				<td>{assign var=pid value=$computerv->profile_id}{$profiles[$pid]}</td>
				{$td}<a style="color: green; text-decoration: none;"  href='/?cl=kawacs&op=computer_view_item&id={$computerv->id}&item_id={$citem->itemdef->id}'>{$status}</a></td>
			</tr>
		{/foreach}
		</table>
		</div>
	</div>
	{/if}
	{if $count_gr!=0}
		<div onclick="expand_group(3)" style="cursor: hand;">
		<table class="list" width="100%">
		<tr class='head'>
			<td style="width: 10px; background-color: gray;"><img src="/images/collapse.gif" width="10" height="11" id="img_3"></td>
			<td>Computers not reporting backup status	   [{$count_gr} computers]</td>
		</tr>
		</table>
		</div>
		<div class="dhtmlgoodies_panel" id='group_3'>
			<div>
			<table class="list" width="100%">
			<tr class='head'>
				<td style="width: 10%;">ID</td>
				<td style="width: 40%;">Name</td>
				<td style="width: 20%;">Profile</td>
				<td style="width: 30%;">Status</td>
				<!-- <td style="width: 35%;">Backup Reports</td> -->
			</tr>
			{foreach from=$computers_grey item=computerg}
			<tr>
					{assign var=stats value=$computerg->backup_status()}
					{assign var=citem value=$stats[0]}
					{assign var=status value=$citem->get_specific_value('Status')}
					{assign var=message value=$citem->get_specific_value('Message')}
					{assign var=td value='<td style="color: gray;">'}
					{$td}{$computerg->id}</td>
					{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerg->id}">{$computerg->netbios_name}</a></td>
					<td>{assign var=pid value=$computerg->profile_id}{$profiles[$pid]}</td>
					{$td}<a style="color: gray; text-decoration: none;"  href='/?cl=kawacs&op=computer_view_item&id={$computerg->id}&item_id={$citem->itemdef->id}'>{if $status!=""}{$status}{else} no status {/if}</a></td>
				</tr>
			{/foreach}
			</table>
			</div>
		</div>
	{/if}
	{if $count_r==0 && $count_o==0 && $count_g==0 && $count_gr==0}
		<p>
		[This customer doesn't have any computers with backup reporting in the profile]
		</p>
	{/if}
	{*{if $count_o!=0 or $count_r!=0 or $count_g!=0 or $count_gr!=0}
	<div>
	<h2>Overall backup status</h2>
	<p>The overall backup status of your computers: </p>
	<table class="list" width="100%">
		 <tr>
			<td>
			<img name="_backup" id="_backup" src="" width="600" height="400">
			<script language="JavaScript" type="text/javascript">
			//<![CDATA
			setTimeout("changeBackupImage('_backup','Overall backup status', {literal}new Array('Backup error', 'Tape error', 'Success', 'Not reporting'){/literal}, {$perc_red}, {$perc_orange}, {$perc_green}, {$perc_grey})", 0);
			//]>
			</script>
			</td>
			<!--
			<td>
				<table class="list" width="100%">
					<thead>
						<tr>
							<td style="width:50px">Interval</td>
							<td>
								<select name="filter[interval][month_start]">
								<option value="-1">This month</option>
								{html_options options=$months_interval}
								</select>
								&nbsp;-&nbsp;
								<select name="filter[interval][month_end]">
								<option value="-1">This month</option>
								{html_options options=$months_interval}
								</select>
							</td>
						</tr>
					</thead>
					<tr>
						<td colspan="2">
							<input type="radio" name="filter[backup][report]" value="bkp_sizes">Backup sizes evolution</input><br />
							<input type="radio" name="filter[backup][report]" value="bkp_age">Backup ages evolution</input>
						</td>
					</tr>
				</table>
			</td>
			-->
		 </tr>
	</table>
	</div>
	{/if}*}
  </div>
</div>
<div id='subtab_antivirus' class='tab_content' style="display: none">
	<h2>Antivirus statuses</h2>
	<p>The antivirus statuses of the antivirus programmes installed on your computers</p>


	<div>
{if $acount_r>0}
<div onclick="expand_agroup(0)" style="cursor: hand;">
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: red;"><img src="/images/collapse.gif" width="10" height="11" id="img_0"></td>
		<td>Antivirus updates older than 1 week	   [{$acount_r} computers]</td>
	</tr>
	</table>
</div>
<div id='agroup_0'>
<table class="list" width="100%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$acomputers_red item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: red;">'}
		{$td}{$computerr->id}</td>
		{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerr->id}">{$computerr->netbios_name}</a></td>
		<td>{assign var=pid value=$computerr->profile_id}{$aprofiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $acount_o>0}
<div onclick="expand_agroup(1)" style="cursor: hand;">
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: orange;"><img src="/images/collapse.gif" width="10" height="11" id="img_1"></td>
		<td>Antivirus updates older than 1 day	   [{$acount_o} computers]</td>
	</tr>
	</table>
</div>
<div id='agroup_1'>
<table class="list" width="100%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$acomputers_orange item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: orange;">'}
		{$td}{$computerr->id}</td>
		{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerr->id}">{$computerr->netbios_name}</a></td>
		<td>{assign var=pid value=$computerr->profile_id}{$aprofiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $acount_g>0}
<div onclick="expand_agroup(2)" style="cursor: hand;">
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10px; background-color: green;"><img src="/images/collapse.gif" width="10" height="11" id="img_2"></td>
		<td>Antivirus updates are up to date	   [{$acount_g} computers]</td>
	</tr>
	</table>
</div>
<div id='agroup_2'>
<table class="list" width="100%">
<tr class='head'>
	<td style="width: 10%;">ID</td>
	<td style="width: 40%;">Name</td>
	<td style="width: 20%;">Profile</td>
	<td style="width: 30%;">Last udate</td>
	<!-- <td style="width: 35%;">Backup Reports</td> -->
</tr>
{foreach from=$acomputers_green item=computer}
<tr>
		{assign var=computerr value=$computer.computer}
		{assign var=stats value=$computer.av_infos}
		{assign var=td value='<td style="color: green;">'}
		{$td}{$computerr->id}</td>
		{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerr->id}">{$computerr->netbios_name}</a></td>
		<td>{assign var=pid value=$computerr->profile_id}{$aprofiles[$pid]}</td>
		{$td}
		{foreach from=$stats item=valobj}
			{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
		{/foreach}
		</td>
	</tr>
{/foreach}
</table>
</div>
{/if}

{if $acount_gr>0}
	<div onclick="expand_agroup(3)" style="cursor: hand;">
		<table class="list" width="100%">
		<tr class='head'>
			<td style="width: 10px; background-color: gray;"><img src="/images/collapse.gif" width="10" height="11" id="img_3"></td>
			<td>Computer not reporting antivirus status	   [{$acount_gr} computers]</td>
		</tr>
		</table>
	</div>
	<div id='agroup_3'>
	<table class="list" width="100%">
	<tr class='head'>
		<td style="width: 10%;">ID</td>
		<td style="width: 40%;">Name</td>
		<td style="width: 20%;">Profile</td>
		<td style="width: 30%;">Last udate</td>
		<!-- <td style="width: 35%;">Backup Reports</td> -->
	</tr>
	{foreach from=$acomputers_gray item=computer}
	<tr>
			{assign var=computerr value=$computer.computer}
			{assign var=stats value=$computer.av_infos}
			{assign var=td value='<td style="color: gray;">'}
			{$td}{$computerr->id}</td>
			{$td}<a href="/?cl=kawacs&op=computer_view&id={$computerr->id}">{$computerr->netbios_name}</a></td>
			<td>{assign var=pid value=$computerr->profile_id}{$aprofiles[$pid]}</td>
			{$td}
			{foreach from=$stats item=valobj}
				{$valobj->value[23]} -> Last update: {$valobj->value[24]|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}<br />
			{/foreach}
			</td>
		</tr>
	{/foreach}
	</table>
	</div>
{/if}
	{*{if $acount_r!=0 or $acount_o!=0 or $acount_g!=0 or $acount_gr!=0}
	<div>
	<h2>Overall antivirus status</h2>
	<p>The overall antivirus updates statuses of your computers: </p>
	<table class="list" width="100%">
		 <tr>
			<td>
			<img name="_antivir" id="_antivir" src="" width="600" height="400">
			<script language="JavaScript" type="text/javascript">
			//<![CDATA
			setTimeout("changeBackupImage('_antivir','Overall antivirus updates status', {literal}new Array('Older than 1 week', 'Older than 1 day', 'Up to date', 'Not reporting'){/literal}, {$aperc_red}, {$aperc_orange}, {$aperc_green}, {$aperc_grey})", 3000);
			//]>
			</script>
			</td>
			<!--
			<td>
				<table class="list" width="100%">
					<thead>
						<tr>
							<td style="width:50px">Interval</td>
							<td>
								<select name="filter[interval][month_start]">
								<option value="-1">This month</option>
								{html_options options=$months_interval}
								</select>
								&nbsp;-&nbsp;
								<select name="filter[interval][month_end]">
								<option value="-1">This month</option>
								{html_options options=$months_interval}
								</select>
							</td>
						</tr>
					</thead>
					<tr>
						<td colspan="2">
							<input type="radio" name="filter[av][report]" value="av_age">Antivirus ages evolution</input>
						</td>
					</tr>
				</table>
			</td>
			-->
		 </tr>
	</table>
	</div>
	{/if}*}
</div>
</div>
</div>

<!-- end added by Victor -->
<div id="tab_adusers" class="tab_content" style="display:none;">
    <table class="tab_header">
    <tr>
        <td id="subtab_head_adusers" class="tab_inactive"><a href="#" onclick="return showAdusersSubTab('adusers');" >AD Users</a></td>
        <td id="subtab_head_userstats" class="tab_inactive"><a href="#" onclick="return showAdusersSubTab('userstats');" >Statistics</a></td>
    </tr>
    </table>
    <div id='subtab_adusers' class='tab_content' style="display: none;"> 
        <h2>AD Users logged on workstations</h2>
        <p />
        <table class="list" width="98%">
        <thead>
	        <tr>
		        <td>Computer id</td>
		        <td>Computer name</td>
		        <td>AD User</td>
		        <td>Logged user</td>
		        <td>Last report</td>
	        </tr>
        </thead>
        {foreach from=$computers_users item="comp_user"}
        {assign var="comp_id" value=$comp_user.computer_id}
        {assign var="last_rep" value=$comp_user.reported}
        {assign var="netbios_name" value=$comp_user.netbios}
        {assign var="user" value=$comp_user.ad_user}
        <tr>
	        <td><a href="/?cl=kawacs&op=computer_view&id={$comp_id}">{$comp_id}</a></td>
	        <td><a href="/?cl=kawacs&op=computer_view&id={$comp_id}">{$netbios_name}</a></td>
	        <td>
	        {if $user!=false}
		        <a href="/?cl=kerm&op=ad_user_view&computer_id={$user->computer_id}&nrc={$user->nrc}">{$user->sam_account_name}: {$user->display_name}</a>, {$user->email}
		        {else}
		        --
		        {/if}
	        </td>
	        <td>{$comp_user.logged_user}</td>
	        <td>{$last_rep|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
        </tr>
        {foreachelse}
        <tr>
	        <td colspan="5">[No computers usage stats yet]</td>
        </tr>
        {/foreach}
        </table>
    </div>
    <div id='subtab_userstats' class='tab_content' style="display: none; width: 95%;"> 
        <h2>Statistics</h2>
        <p/>
        <div style="width: 98%;"  id="adusers_evo_container">&nbsp;</div>
    </div>
    
</div>

<div id="tab_nagvis" class="tab_content" style="display:none;">
	{if $nagvis->url}
        <iframe src="{$nagvis->protocol}{$nagvis->username}:{$nagvis->password}@{$nagvis->url}" style="width: 100%; height: 700px; border: none;">
	</iframe>
	{else}
	No Nagvis data is set.
	{/if}
</div>

<p/>
</form>

<script language="JavaScript" type="text/javascript">
//<![CDATA
// Check what was the last selected tab, if any
document.cookie = 'customer_view_tab={$active_tab}';
if (!(last_tab = getCookie('customer_view_tab'))) last_tab = tabs[0];
showTab (last_tab);
if (!(last_subtab = getCookie('customer_view_subtab'))) last_subtab = subtabs[0];
showSubTab(last_subtab);

if (!(last_ticket_subtab = getCookie('customer_view_ticket_subtab'))) last_ticket_subtab = tickets_subtabs[0];
showTicketsSubTab(last_ticket_subtab);

if (!(last_computers_subtab = getCookie('customer_view_computers_subtab'))) last_computers_subtab = computers_subtabs[0];
showComputersSubTab(last_computers_subtab);

if ( ! (last_adusers_subtab = getCookie('customer_view_adusers_subtab'))) last_adusers_subtab = adusers_subtabs[0];
showAdusersSubTab(last_adusers_subtab);
//]]>
</script>
