{assign var="paging_titles" value="KAWACS, Customer computers dashboard"}
{assign var="paging_urls" value="/kawacs"}
{include file="paging.html"}
<script language="JavaScript" type="text/javascript">
<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 9;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 124;
// -----------------------------------------------------------------------------
// -->
</script>
</head>

<body scroll="no">
<script language="JavaScript" type="text/javascript">
{literal}
//<![CDATA[
var hasProductInstall = DetectFlashVer(6, 0, 65);
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

if ( hasProductInstall && !hasRequestedVersion ) {
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
    var MMdoctitle = document.title;

	AC_FL_RunContent(
		"src", "playerProductInstall",
		"FlashVars", "MMredirectURL="+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+"",
		"width", "100%",
		"height", "100%",
		"align", "middle",
		"id", "CustomerComputersCharts",
		"quality", "high",
		"bgcolor", "#ffffff",
		"name", "CustomerComputersCharts",
		"allowScriptAccess","sameDomain",
		"type", "application/x-shockwave-flash",
		"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
} else if (hasRequestedVersion) {
	AC_FL_RunContent(
			"src", "flex/CustomerComputersCharts",
			"width", "100%",
			"height", "100%",
			"align", "middle",
			"id", "CustomerComputersCharts",
			"quality", "high",
			"bgcolor", "#ffffff",
			"name", "CustomerComputersCharts",
			"allowScriptAccess","sameDomain",
                        "flashvars", "customer_id={/literal}{$customer->id}{literal}",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {
    var alternateContent = 'Alternate HTML content should be placed here. '
  	+ 'This content requires the Adobe Flash Player. '
   	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a>';
    document.write(alternateContent);  // insert non-flash content
  }
//]]>
{/literal}
</script>
<h1>Customer computers dashboard</h1>
<p />
<font class="error">{$error_msg}</font>
<p />
<form method="POST" action="" name="filter">
{$form_redir}

{if !$customer}
	<!-- No customer selected -->
	Customer:<br>
	<select name="filter[customer_id]">
		<option value="">[Select]</option>
		{html_options options=$customers_list selected=$current_locked_customer_id}
	</select>
	
	<p>
	<input type="submit" name="select" value="Select">

{else}
    <div name="charts" style="width: 98%; height: 600px;">
	
  	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
			id="CustomerComputersCharts" width="100%" height="100%"
			codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
			<param name="movie" value="flex/CustomerComputersCharts.swf" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<param name="allowScriptAccess" value="sameDomain" />
			<embed src="flex/CustomerComputersCharts.swf" quality="high" bgcolor="#ffffff"
				width="100%" height="100%" name="CustomerComputersCharts" align="middle"
				play="true"
				loop="false"
				quality="high"
				allowScriptAccess="sameDomain"                                
				type="application/x-shockwave-flash"
                flashvars="customer_id={$customer->id}"
				pluginspage="http://www.adobe.com/go/getflashplayer">
			</embed>
	</object>
	
    </div>
{/if}

</form>