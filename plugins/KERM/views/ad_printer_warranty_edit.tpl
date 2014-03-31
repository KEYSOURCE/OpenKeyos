{assign var="paging_titles" value="KERM, AD Printers, Edit Warranty"}
{assign var="paging_urls" value="/?cl=kerm, /?cl=kerm&op=manage_ad_printers"}
{include file="paging.html"}

<h1>Edit Warranty</h1>
<p/>
<font class="error">{$error_msg}</font>
<p/>

<form action="" method="POST">
{$form_redir}

<table width="98%" class="list">
	<thead>
	<tr>
		<td width="20%">Canonical name</td>
		<td>{$ad_printer->canonical_name}</td>
	</tr>
	</thead>

	<tr>
		<td>Asset No.:</td>
		<td>{$ad_printer->asset_no}</td>
	</tr>
	<tr>
		<td>Serial number:</td>
		<td>
			<input type="text" name="sn" value="{$ad_printer->sn}" size="30">
		</td>
	</tr>
	<tr>
		<td>Warranty starts:</td>
		<td>
			{if $ad_printer->warranty_starts > 0}
				{assign var="time" value=$ad_printer->warranty_starts}
			{else}
				{assign var="time" value="0000--"}
			{/if}
				
			{html_select_date 
				field_array="warranty_starts"
				start_year="-10"
				end_year="+10" 
				time=$time
				year_empty="--" month_empty="--" day_empty="--"
			}
		</td>
	</tr>
	<tr>
		<td>Warranty ends:</td>
		<td>
			{if $ad_printer->warranty_ends > 0}
				{assign var="time" value=$ad_printer->warranty_ends}
			{else}
				{assign var="time" value="0000--"}
			{/if}
				
			{html_select_date 
				field_array="warranty_ends"
				start_year="-10"
				end_year="+10" 
				time=$time
				year_empty="--" month_empty="--" day_empty="--"
			}
		</td>
	</tr>
	<tr>
		<td>Service package:</td>
		<td>
			<select name="service_package_id">
				<option value="">[Select]</option>
				{html_options options=$service_packages_list selected=$ad_printer->service_package_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Service level:</td>
		<td>
			<select name="service_level_id">
				<option value="">[Select]</option>
				{html_options options=$service_levels_list selected=$ad_printer->service_level_id}
			</select>
		</td>
	</tr>
	<tr>
		<td>Contract number:</td>
		<td><input type="text" name="contract_number" value="{$ad_printer->contract_number|escape}" size="30" /></td>
	</tr>
	<tr>
		<td>Hardware product ID:</td>
		<td><input type="text" name="hw_product_id" value="{$ad_printer->hw_product_id|escape}" size="30" /></td>
	</tr>
	<tr>
		<td>Product number:</td>
		<td><input type="text" name="product_number" value="{$ad_printer->product_number|escape}" size="30" /></td>
	</tr>
</table>
<p/>

<input type="submit" name="save" value="Save">
<input type="submit" name="cancel" value="Close">

</form>