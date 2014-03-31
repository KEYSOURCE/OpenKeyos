{assign var="paging_titles" value="Krifs, Work Time Stats"}
{assign var="paging_urls" value="/krifs"}
{include file="paging.html"}

<style>
{literal}
.btn_opt{
    border-top: 1px solid #7D796F;
    border-left: 1px solid #7D796F;
    border-right: 1px solid #454440;
    border-bottom: 1px solid #454440;
    background: #5D584B;
    padding: 3px 5px;
    font-size: 12px;
    color: white;
    text-shadow: 0 -1px 1px black;
}
{/literal}
</style>

<link rel="stylesheet" type="text/css" media="all" href="/javascript/jsdatepick/jsDatePick_ltr.min.css" />

<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script type="text/javascript" src="/javascript/jquery.min.js"></script>
<script type="text/javascript" src="/javascript/highcharts/highcharts.js"></script>
<script type="text/javascript" src="/javascript/highcharts/modules/exporting.js"></script>
<script type="text/javascript" src="/javascript/jsdatepick/jquery.1.4.2.js"></script>
<script type="text/javascript" src="/javascript/jsdatepick/jsDatePick.jquery.min.1.3.js"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
jQuery.noConflict();
window.onload = function(){
    new JsDatePick({
        useMode:2,
        target:"reportStartDate",
        dateFormat:"%d/%m/%Y",
        cellColorScheme:"greenish",
        yearsRange: [2000, 2100]
    });
    new JsDatePick({
        useMode:2,
        target:"reportEndDate",
        dateFormat:"%d/%m/%Y",
        cellColorScheme:"greenish",
        yearsRange: [2000, 2100]
    });
};

Highcharts.theme = { colors: ['#4572A7'] };// prevent errors in default theme
var highchartsOptions = Highcharts.getOptions();


function show_work_times(){
   var chart;
   jQuery(document).ready(function(){
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'work_time_container',
         defaultSeriesType: 'line'
      },
      title: {
         text: 'Daily Work Time'
      },
      subtitle: {
         text: 'Total time / Time included in IR\'s / Time NOT included in IR\'s'
      },
      xAxis: {
         categories: {/literal}{if $wt_days!=""}{$wt_days}{else}[]{/if}{literal} ,
         labels: {
            rotation: -45,
            align: 'right',
            style: {
                font: 'normal 12px Verdana, sans-serif'
            }
         }
      },
      yAxis: {
         title: {
            text: 'Minutes'
         }
      },
      tooltip: {
         enabled: false,
         formatter: function() {
            return '<b>'+ this.series.name +'</b><br/>'+
               this.x +': '+ this.y +'minutes';
         }
      },
      plotOptions: {
         line: {
            dataLabels: {
               enabled: true
            },
            enableMouseTracking: true
         }
      },
      series: [{
         name: 'Total work time in tickets',
         data: {/literal}{if $wt_totbill!=""}{$wt_totbill}{else}[]{/if}{literal}
      }, {
         name: 'Total time inclulded in IR\'s',
         data: {/literal}{if $wt_irbill!=""}{$wt_irbill}{else}[]{/if}{literal}
      },{
         name: 'Total time NOT inclulded in IR\'s',
         data: {/literal}{if $wt_nirbill!=""}{$wt_nirbill}{else}[]{/if}{literal}
      }]
   });
  });
}


show_work_times();
{/literal}
//]]>
</script>

<h1>Work Time Stats</h1>
<p class="error">{$error_msg}</p>

<form method="post" action="">
{$form_redir}

<table style="width: 98%; margin: 2em auto;">
    <tr>
        <td>
            <div id="work_time_container" style="height: 425px; width: 95%; min-width: 600px;" ></div>
        </td>
    </tr>
    <tr>
        <td>
            <div style="margin: auto; padding: auto; text-align: center;">
                <select name="customer" id="customer">
                        <option value="0">[All]</option>
                        {html_options options=$customers_list selected=$selected_customer}
                    </select>
                    <select name="user" id="user">
                        <option value="0">[All]</option>
                        {html_options options=$users_list selected=$selected_user}
                    </select>
                <label style="font-weight: bold;">From: </label><input type="text" name="reportStartDate" id="reportStartDate" value="{$start_date}" />
                &nbsp;&nbsp; - &nbsp;&nbsp;<label style="font-weight: bold;">To: </label><input type="text" name="reportEndDate" id="reportEndDate" value="{$end_date}" />
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="plot" value="Plot" class="btn_opt" />
            </div>
        </td>
    </tr>
</table>

</form>
