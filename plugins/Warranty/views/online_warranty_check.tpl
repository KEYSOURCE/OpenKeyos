{assign var="paging_titles" value="KAWACS, Online Warranty Check"}
{assign var="paging_urls" value="/kawacs"}
{*include file="paging.html"*}

<script type="text/javascript" language="JavaScript">
    //<![CDATA[
    {literal}
    function find_sel_warranty(section){
        var at_least_one_select = false;
        $('.ax_ocheck_'+section).each(function(){
            if($(this).is(':checked')){
                at_least_one_select = true;
                var cid = $(this).val();
                var sn = $('#csn_'+cid).text();
                var brand = $('#cb_'+cid).text();
                find_warranty(cid, sn, brand);
            }
        });
        if(at_least_one_select){
            $('#achanges_all').show();
	    $('#achanges_all').click(function(){
		var inh = "";
                $('.ax_ocheck_'+section).each(function(){
                    if($(this).is(':checked')){
			var cid = $(this).val();
			var sn = $('#csn_'+cid).text();
			var brand = $('#cb_'+cid).text();
			inh += "<input type='hidden' name='warranties_to_save[]' value='"+cid+"' />";
			inh += "<input type='hidden' name='warranty["+cid+"][sn]' value='"+sn+"' />";
			inh += "<input type='hidden' name='warranty["+cid+"][product]' value='"+$('#cprd_'+cid).text()+"' />";
                    }
		});
		$("#sv_hid").html(inh);
                $('#frm_filt').submit();
            });
        }
    }
    function find_warranty(computer_id, computer_sn, computer_brand){
        $("#progress_"+computer_id).html("<img src='/images/ajax-loader.gif' height='15px' width='15px' />");
        $.getJSON("{/literal}{$base_plugin_url}{literal}check_warranties.php", {sn : computer_sn, brand: computer_brand}, function(data){
            if(data.StartDate != '' && data.StartDate != undefined)
                $('#ws_'+computer_id).html("<input type='text' name='warranty["+computer_id+"][start_date]' value="+data.StartDateFormat+" />");
            else
                $('#ws_'+computer_id).html("<input type='text' name='warranty["+computer_id+"][start_date]' value='' />");
            if(data.EndDate != '' && data.EndDate != undefined)
                $('#we_'+computer_id).html("<input type='text' name='warranty["+computer_id+"][end_date]' value="+data.EndDateFormat+" />");
            else
                $('#we_'+computer_id).html("<input type='text' name='warranty["+computer_id+"][end_date]' value='' />");
            $('#level_'+computer_id).hide();
            $('#srv_levels_'+computer_id).show();
            $('#package_'+computer_id).hide();
            $('#srv_packages_'+computer_id).show();

            if(data.StartDate != '' && data.EndDate != '' && data.StartDate != undefined && data.EndDate != undefined){
                $("#progress_"+computer_id).html("<img src='/images/Blue_check.png' height='15px' width='15px' />");
            } else {
                $("#progress_"+computer_id).html("<img src='/images/supprimer.gif' height='15px' width='15px' />");
            }
            $("#of_"+computer_id).text("Save warranty");
            $("#of_"+computer_id).click(function(){
                var inh = "<input type='hidden' name='warranties_to_save[]' value='"+computer_id+"' />";
                inh += "<input type='hidden' name='warranty["+computer_id+"][sn]' value='"+computer_sn+"' />";
                inh += "<input type='hidden' name='warranty["+computer_id+"][product]' value='"+$('#cprd_'+computer_id).text()+"' />";
                inh += "<input type='hidden' name='warranty["+computer_id+"][days_remaining]' value='"+data.DaysLeft+"' />";
                $("#sv_hid").html(inh);
                $('#frm_filt').submit();
            });
        });
    }
    function change_alert_sel(elm, section){
        $('.chk_alert_'+section).each(function(){
            var vv = $(elm).is(':checked');
            $(this).attr('checked', vv);
        });
    }

    function change_sp_sel(elm, section){
        $('.sp_select_'+section).each(function(){
            $(this).val($(elm).val());
        });
    }

    function change_sl_sel(elm, section){
        $('.sl_select_'+section).each(function(){
            $(this).val($(elm).val());
        });
    }

    function change_ocheck_sel(elm, section){
        $('.ax_ocheck_'+section).each(function(){
            var vv = $(elm).is(':checked');
            $(this).attr('checked', vv);
        });
    }

    function tgl(elm, section){
	var ish = true;
	$(".for_hidden_"+section).each(function(){
	    if($(this).is(':hidden')){
		$(this).show();
		ish = false;
	    } else {
		$(this).hide();
		ish = true;
	    }
	});
	if(ish){
	    $(elm).css("background-image", "url('/images/expand.gif')");
	} else {
	    $(elm).css("background-image", "url('/images/collapse.gif')");
	}
    }
    //]]>
    {/literal}
</script>


<h1>Online Warranty Check</h1>

<p class="error">{$error_msg}</p>

<form action="" method="POST" name="frm_filt" id="frm_filt">
{$form_redir}
    <b>Customer:</b>
    <select name="filter[customer_id]"  onchange="$('#frm_filt').submit();">
		<option value="">[Select customer]</option>
		{html_options options=$customers_list selected=$filter.customer_id}
    </select>
{if $filter.customer_id}
<div id="sv_hid"></div>
<table class="list" width="98%">
    <thead>
        <tr>
                <td width="20">ID</td>
                <td>Computer</td>
                <td>Computer Brand</td>
                <td>Serial number</td>
                <td>Warranty starts</td>
                <td>Warranty ends</td>
                <td>Service level</td>
                <td>Service package</td>
                <td>Raise alert</td>
                <td>Product</td>
                <td colspan="2">
                    <a href="#" id='achanges_all' style="display: none; text-decoration: none;">Save selected warranties</a>
                </td>
        </tr>
    </thead>
    <tbody>
        {if @count|$comp_warranties_unknown > 0}
        <tr style="background-color: #eee;">
            <td style="margin: auto; background-image: url('/images/collapse.gif'); background-repeat: no-repeat; background-position: center;" onclick="tgl(this, 'unknown')"></td>
            <td colspan="5" style="font-weight: bold; font-size: +2;">Computers without warranty information</td>
            <td style="font-weight: bold; font-size: +2;">
                <select id="oa_slevel" onchange="change_sl_sel(this, 'uk');">
                    <option value='-1'>[Select package]</option>
                    {html_options options=$service_levels_list}
                </select>
            </td>
            <td style="font-weight: bold; font-size: +2;">
                <select id="oa_spackage" onchange="change_sp_sel(this, 'uk');">
                    <option value='-1'>[Select package]</option>
                    {html_options options=$service_packages_list}
                </select>
            </td>
            <td style="font-weight: bold; font-size: +2;"><input type="checkbox" onchange="change_alert_sel(this, 'uk')" value="0" /></td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;">
                <span id='progress_all_uk' style="height: 22px;"></span>
                <a id="of_all_uk" href="#" onclick="find_sel_warranty('uk')" style="text-decoration: none;">Check selected warranties online</a>
            </td>
            <td style="font-weight: bold; font-size: +2;">
                <input type="checkbox" onchange="change_ocheck_sel(this, 'uk')" value="0" />
            </td>
        </tr>
        {*<div id='for_hidden_unknown'>*}
        {foreach from=$comp_warranties_unknown item=compw key=ci}
        <tr class="for_hidden_unknown">
            {assign var='cainfo' value=$comp_additional_infos.$ci}
            <td>{$ci}</td>
            <td>{$computers_list.$ci}</td>
            <td id="cb_{$ci}">{$cainfo.computer_brand}</td>
            <td id="csn_{$ci}">{$cainfo.computer_sn}</td>
            <td id='ws_{$ci}'>
                {if $compw->warranty_starts}
                    {$compw->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td id='we_{$ci}'>
                {if $compw->warranty_ends}
                    {$compw->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td>
                <div id="level_{$ci}">
                {if $compw->service_level_id}
                        {assign var="service_level_id" value=$compw->service_level_id}
                        {$service_levels_list.$service_level_id}
                {else}-{/if}
                </div>
                <div id="srv_levels_{$ci}" style="display: none;">
                    <select class='sl_select_uk' name="warranty[{$ci}][service_level_id]" id="warranty[{$ci}][service_level]">
                        <option value='-1'>[Select level]</option>
                        {html_options options=$service_levels_list selected=$service_level_id}
                    </select>
                </div>
            </td>
            <td>
                <div id="package_{$ci}">
                {if $compw->service_package_id}
                        {assign var="service_package_id" value=$compw->service_package_id}
                        {$service_packages_list.$service_package_id}
                {else}-{/if}
                </div>
                <div id="srv_packages_{$ci}" style="display: none;">
                    <select class='sp_select_uk' name="warranty[{$ci}][service_package_id]" id="warranty[{$ci}][service_package]">
                        <option value='-1'>[Select package]</option>
                        {html_options options=$service_packages_list selected=$service_package_id}
                    </select>
                </div>
            </td>
            <td><input class='chk_alert_uk' type="checkbox" name="warranty[{$ci}][raise_alert]" id="ra_{$ci}" value="{$compw->raise_alert}" /></td>
            <td id="cprd_{$ci}">{$cainfo.computer_model|escape}</td>
            <td>
                <span id='progress_{$ci}' style="height: 22px;"></span>
                <a style="text-decoration: none;" id="of_{$ci}" href="#" onclick="find_warranty('{$ci}', '{$cainfo.computer_sn}', '{$cainfo.computer_brand}')">Find warranty</a>
            </td>
            <td>
                <input type="checkbox" class='ax_ocheck_uk' id="ax_ocheck_{$ci}" value="{$ci}" />
            </td>
        </tr>
        {/foreach}
        <tr><td colspan="12">&nbsp;</td></tr>
        {*</div>*}
        {/if}
        {if @count|$comp_warranties_eow > 0}
        <tr style="background-color: #eee;">
            <td style="margin: auto; background-image: url('/images/collapse.gif'); background-repeat: no-repeat; background-position: center;" onclick="tgl(this, 'eow')"></td>
            <td colspan="5" style="font-weight: bold; font-size: +2;">Computers with expired warranties</td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;"><input type="checkbox" onchange="change_alert_sel(this, 'eow')" value="0" /></td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;">
                <span id='progress_all_eow' style="height: 22px;"></span>
                <a id="of_all_eow" href="#" onclick="find_sel_warranty('eow')" style="text-decoration: none;">Check selected warranties online</a>
            </td>
            <td style="font-weight: bold; font-size: +2;">
                <input type="checkbox" onchange="change_ocheck_sel(this, 'eow')" value="0" />
            </td>
        </tr>
        {*<div id='for_hidden_eow'>*}
        {foreach from=$comp_warranties_eow item=compw key=ci}
        <tr class="for_hidden_eow">
            {assign var='cainfo' value=$comp_additional_infos.$ci}
            <td>{$ci}</td>
            <td>{$computers_list.$ci}</td>
            <td id="cb_{$ci}">{$cainfo.computer_brand}</td>
            <td id="csn_{$ci}">{$cainfo.computer_sn}</td>
            <td id='ws_{$ci}'>
                {if $compw->warranty_starts}
                    {$compw->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td id='we_{$ci}'>
                {if $compw->warranty_ends}
                    {$compw->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td>
                <div id="level_{$ci}">
                {if $compw->service_level_id}
                        {assign var="service_level_id" value=$compw->service_level_id}
                        {$service_levels_list.$service_level_id}
                {else}-{/if}
                </div>
                <div id="srv_levels_{$ci}" style="display: none;">
                    <select class='sl_select_eow' name="warranty[{$ci}][service_level_id]" id="warranty[{$ci}][service_level]">
                        <option value='-1'>[Select level]</option>
                        {html_options options=$service_levels_list selected=$service_level_id}
                    </select>
                </div>
            </td>
            <td>
                <div id="package_{$ci}">
                {if $compw->service_package_id}
                        {assign var="service_package_id" value=$compw->service_package_id}
                        {$service_packages_list.$service_package_id}
                {else}-{/if}
                </div>
                <div id="srv_packages_{$ci}" style="display: none;">
                    <select class='sp_select_eow' name="warranty[{$ci}][service_package_id]" id="warranty[{$ci}][service_package]">
                        <option value='-1'>[Select package]</option>
                        {html_options options=$service_packages_list selected=$service_package_id}
                    </select>
                </div>
            </td>
            <td><input class='chk_alert_eow' type="checkbox" name="warranty[{$ci}][raise_alert]" id="ra_{$ci}" value="{$compw->raise_alert}" /></td>
            <td id="cprd_{$ci}">{$cainfo.computer_model|escape}</td>
            <td>
                <span id='progress_{$ci}' style="height: 22px;"></span>
                <a style="text-decoration: none;" id="of_{$ci}" href="#" onclick="find_warranty('{$ci}', '{$cainfo.computer_sn}', '{$cainfo.computer_brand}')">Find warranty</a>
            </td>
            <td>
                <input type="checkbox" class='ax_ocheck_eow' id="ax_ocheck_{$ci}" value="{$ci}" />
            </td>
        </tr>
        {/foreach}
        <tr><td colspan="12">&nbsp;</td></tr>
        {*</div>*}
        {/if}
	{if @count|$comp_warranties_active > 0}
        <tr style="background-color: #eee;">
            <td style="margin: auto; background-image: url('/images/collapse.gif'); background-repeat: no-repeat; background-position: center;" onclick="tgl(this, 'active')"></td>
            <td colspan="5" style="font-weight: bold; font-size: +2;">Computers with active warranties</td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;"><input type="checkbox" onchange="change_alert_sel(this, 'active')" value="0" /></td>
            <td style="font-weight: bold; font-size: +2;"></td>
            <td style="font-weight: bold; font-size: +2;">
                <span id='progress_all_active' style="height: 22px;"></span>
                <a id="of_all_active" href="#" onclick="find_sel_warranty('active')" style="text-decoration: none;">Check selected warranties online</a>
            </td>
            <td style="font-weight: bold; font-size: +2;">
                <input type="checkbox" onchange="change_ocheck_sel(this, 'active')" value="0" />
            </td>
        </tr>
	{*<div id='for_hidden_active'>*}
        {foreach from=$comp_warranties_active item=compw key=ci}
        <tr class="for_hidden_active">
            {assign var='cainfo' value=$comp_additional_infos.$ci}
            <td>{$ci}</td>
            <td>{$computers_list.$ci}</td>
            <td id="cb_{$ci}">{$cainfo.computer_brand}</td>
            <td id="csn_{$ci}">{$cainfo.computer_sn}</td>
            <td id='ws_{$ci}'>
                {if $compw->warranty_starts}
                    {$compw->warranty_starts|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td id='we_{$ci}'>
                {if $compw->warranty_ends}
                    {$compw->warranty_ends|date_format:$smarty.const.DATE_FORMAT_SMARTY}
		{else}-
                {/if}
            </td>
            <td>
                <div id="level_{$ci}">
                {if $compw->service_level_id}
                        {assign var="service_level_id" value=$compw->service_level_id}
                        {$service_levels_list.$service_level_id}
                {else}-{/if}
                </div>
                <div id="srv_levels_{$ci}" style="display: none;">
                    <select class='sl_select_active' name="warranty[{$ci}][service_level_id]" id="warranty[{$ci}][service_level]">
                        <option value='-1'>[Select level]</option>
                        {html_options options=$service_levels_list selected=$service_level_id}
                    </select>
                </div>
            </td>
            <td>
                <div id="package_{$ci}">
                {if $compw->service_package_id}
                        {assign var="service_package_id" value=$compw->service_package_id}
                        {$service_packages_list.$service_package_id}
                {else}-{/if}
                </div>
                <div id="srv_packages_{$ci}" style="display: none;">
                    <select class='sp_select_active' name="warranty[{$ci}][service_package_id]" id="warranty[{$ci}][service_package]">
                        <option value='-1'>[Select package]</option>
                        {html_options options=$service_packages_list selected=$service_package_id}
                    </select>
                </div>
            </td>
            <td><input class='chk_alert_active' type="checkbox" name="warranty[{$ci}][raise_alert]" id="ra_{$ci}" value="{$compw->raise_alert}" /></td>
            <td id="cprd_{$ci}">{$cainfo.computer_model|escape}</td>
            <td>
                <span id='progress_{$ci}' style="height: 22px;"></span>
                <a style="text-decoration: none;" id="of_{$ci}" href="#" onclick="find_warranty('{$ci}', '{$cainfo.computer_sn}', '{$cainfo.computer_brand}')">Find warranty</a>
            </td>
            <td>
                <input type="checkbox" class='ax_ocheck_active' id="ax_ocheck_{$ci}" value="{$ci}" />
            </td>
        </tr>
        {/foreach}
        <tr><td colspan="12">&nbsp;</td></tr>
        {*</div>*}
        {/if}
    <tbody>
</table>
{/if}
</form>
