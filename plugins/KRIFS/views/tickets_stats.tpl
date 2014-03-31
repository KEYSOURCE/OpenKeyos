{assign var="paging_titles" value="Krifs, Tickets Stats"}
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
    color: white;
    text-shadow: 0 -1px 1px black;
}
{/literal}
</style>
 
<script language="JavaScript" type="text/javascript" src="/javascript/ajax.js"></script>
<script type="text/javascript" src="/javascript/jquery.min.js"></script>
<script type="text/javascript" src="/javascript/highcharts/highcharts.js"></script>
<script type="text/javascript" src="/javascript/highcharts/modules/exporting.js"></script>

<script language="JavaScript" type="text/javascript">
//<![CDATA[
{literal}
jQuery.noConflict();

Highcharts.theme = {
   colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'],
   chart: {
      backgroundColor: {
         linearGradient: [0, 0, 500, 500],
         stops: [
            [0, 'rgb(255, 255, 255)'],
            [1, 'rgb(240, 240, 255)']
         ]
      }
,
      borderWidth: 2,
      plotBackgroundColor: 'rgba(255, 255, 255, .9)',
      plotShadow: true,
      plotBorderWidth: 1
   },
   title: {
      style: { 
         color: '#000',
         font: 'bold 16px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   subtitle: {
      style: { 
         color: '#666666',
         font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
      }
   },
   xAxis: {
      gridLineWidth: 1,
      lineColor: '#000',
      tickColor: '#000',
      labels: {
         style: {
            color: '#000',
            font: '11px Trebuchet MS, Verdana, sans-serif'
         }
      },
      title: {
         style: {
            color: '#333',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'

         }            
      }
   },
   yAxis: {
      minorTickInterval: 'auto',
      lineColor: '#000',
      lineWidth: 1,
      tickWidth: 1,
      tickColor: '#000',
      labels: {
         style: {
            color: '#000',
            font: '11px Trebuchet MS, Verdana, sans-serif'
         }
      },
      title: {
         style: {
            color: '#333',
            fontWeight: 'bold',
            fontSize: '12px',
            fontFamily: 'Trebuchet MS, Verdana, sans-serif'
         }            
      }
   },
   legend: {
      itemStyle: {         
         font: '9pt Trebuchet MS, Verdana, sans-serif',
         color: 'black'

      },
      itemHoverStyle: {
         color: '#039'
      },
      itemHiddenStyle: {
         color: 'gray'
      }
   },
   labels: {
      style: {
         color: '#99b'
      }
   }
};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

function show_chart_tickets(){
    var chart;
        jQuery(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'tstats_containter',
                    defaultSeriesType: 'column'
                },
                title: {
                    text: 'Last {/literal}{$nodays}{literal} days tickets activity'
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
                    categories: {/literal}{if $tickets_lmevo_days!=""}{$tickets_lmevo_days}{else}[]{/if}{literal} ,
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
function show_user_tickets(){
var chart;
jQuery(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'user_tstats_container',
         zoomType: 'xy'
      },
      title: {
         text: 'Tickets activity for last {/literal}{$nodays1}{literal} days'
      },
      subtitle: {
         text: 'Daily view'
      },
      xAxis: [{
         categories:{/literal}{if $tickets_utevo_days!=""}{$tickets_utevo_days}{else}[]{/if}{literal},
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
               return this.value +' minutes';
            },
            style: {
               color: '#89A54E'
            }
         },
         title: {
            text: 'Tickets billable time',
            style: {
               color: '#89A54E'
            }
         },
         opposite: true
         
      }, { // Secondary yAxis
         gridLineWidth: 0,
         title: {
            text: 'No details created',
            style: {
               color: '#4572A7'
            }
         },
         labels: {
            formatter: function() {
               return this.value +' details';
            },
            style: {
               color: '#4572A7'
            }
         }
         
      }, { // Tertiary yAxis
         gridLineWidth: 0,
         title: {
            text: 'IR billable time',
            style: {
               color: '#AA4643'
            }
         },
         labels: {
            formatter: function() {
               return this.value +' minutes';
            },
            style: {
               color: '#AA4643'
            }
         },
         opposite: true
      }],
      tooltip: {
         formatter: function() {
            var unit = {
               'No details created': 'details',
               'Tickets Billable Time': 'minutes',
               'IR Billable Time': 'minutes'
            }[this.series.name];
            
            return ''+
               this.x +': '+ this.y +' '+ unit;
         }
      },
      legend: {
         layout: 'vertical',
         align: 'left',
         x: 120,
         verticalAlign: 'top',
         y: 80,
         floating: true,
         backgroundColor: Highcharts.theme.legendBackgroundColor || '#FFFFFF'
      },
      series: [{
         name: 'No details created',
         color: '#4572A7',
         type: 'column',
         yAxis: 1,
         data: {/literal}{if $tickets_utevo_details!=""}{$tickets_utevo_details}{else}[]{/if}{literal}
      
      }, {
         name: 'IR Billable Time',
         type: 'spline',
         color: '#AA4643',
         yAxis: 2,
         data: {/literal}{if $tickets_utevo_irbill!=""}{$tickets_utevo_irbill}{else}[]{/if}{literal},
         marker: {
            enabled: false
         },
         dashStyle: 'shortdot'               
      
      }, {
         name: 'Tickets Billable Time',
         color: '#89A54E',
         type: 'spline',
         data: {/literal}{if $tickets_utevo_tdbill!=""}{$tickets_utevo_tdbill}{else}[]{/if}{literal}
      }]
   });
});
}

function set_time_period(days){
      var nd = document.getElementById('nodays');
      if (days!=0){
        nd.value= days;        
      }
      //else{      
      //  nd.value = document.getElementById('days_report').value; 
      //}
      return true;
}
function set_time_period1(days){
      var nd = document.getElementById('nodays1');
      if (days!=0){
        nd.value= days;        
      }
      //else{      
      //  nd.value = document.getElementById('days_report').value; 
      //}
      return true;
}

show_chart_tickets();
show_user_tickets();
{/literal}
//]]>
</script>

<h1>Tickets statistics</h1>
<p class="error">{$error_msg}</p>
<form method="post" action="">
{$form_redir}
<input type="hidden" name="nodays" id="nodays" value="{$nodays}" />
<input type="hidden" name="nodays1" id="nodays1" value="{$nodays1}" />    
<table style="width: 98%">
    <tr>
        <td>
            <div id="tstats_containter" style="width: 95%; height: 421px; min-width: 600px; margin: 0 auto;" />     <br />
        </td>
    </tr>
    <tr>
        <td>
            <div id="options" style="text-align: center; margin: 2em auto; padding: auto; width: 95%; height: auto;">
                    <input type="submit" name="lw" value="Last week" class="btn_opt" onclick="return set_time_period(7);" />
                    <input type="submit" name="ltw" value="Last two weeks" class="btn_opt" onclick="return set_time_period(15);" />
                    <input type="submit" name="lmonth" value="Last month" class="btn_opt" onclick="return set_time_period(30);" />
                    <select name="customer" id="customer" onchange="submit();">
                        <option value="0">[All]</option>
                        {html_options options=$customers_list selected=$sel_cust}
                    </select>
                    &nbsp;&nbsp;            
            </div>
        </td>
    </tr>
    
</table>
<table style="width: 98%">
    <tr>
        <td>
            <div id="user_tstats_container" style="width: 95%; height: 421px; min-width: 600px; margin: 0 auto;" />     <br />
        </td>
    </tr>
    <tr>
        <td>
            <div id="options_c2" style="text-align: center; margin: 2em auto; padding: auto; width: 95%; height: auto;">
                    <input type="submit" name="lw1" value="Last week" class="btn_opt" onclick="return set_time_period1(7);" />
                    <input type="submit" name="ltw1" value="Last two weeks" class="btn_opt" onclick="return set_time_period1(15);" />
                    <input type="submit" name="lmonth1" value="Last month" class="btn_opt" onclick="return set_time_period1(30);" />
                    <select name="customer1" id="customer1" onchange="submit();">
                        <option value="0">[All]</option>
                        {html_options options=$customers_list selected=$selected_cust1}
                    </select>  
                    <select name="user1" id="user1" onchange="submit();">
                        <option value="0">[All]</option>
                        {html_options options=$users_list selected=$user_sel1}
                    </select>                  
            </div>
        </td>
    </tr>
    
</table>
</form>