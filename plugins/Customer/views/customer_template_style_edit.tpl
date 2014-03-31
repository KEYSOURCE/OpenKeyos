{assign var="customer_id" value=$customer->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Edit Template"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id"}
{include file="paging.html"}

<script type="text/javascript" src="/javascript/color_functions.js"></script>		
<script type="text/javascript" src="/javascript/js_color_picker_v2.js"></script>

<h1>Edit Customer Template Style</h1>

<p class="error">{$error_msg}</p>

<form action="" enctype="multipart/form-data" method="POST" name="tpl_edit_frm">
{$form_redir}
<table class="list" width="98%">
	<thead>
		<tr>
			<td width="30%" nowrap>Template item</td>
			<td width="70%"  class="post_highlight" nowrap>Value</td>
		</tr>
	</thead>
	<tr>
		<td class="highlight" width="30%" nowrap>Default font size</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->default_font_size}" size="2" id="customer_template[default_font_size]" name="customer_template[default_font_size]" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Default background color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->default_bg_color}"  id="customer_template[default_bg_color]" name="customer_template[default_bg_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->default_bg_color};" onClick="showColorPicker(this,document.getElementById('customer_template[default_bg_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Header text decoration</td>
		<td class="post_highlight" width="70%" nowrap>			
			<select id="customer_template[header_text_decoration]" name="customer_template[header_text_decoration]">
				<option value="none" {if $customer_template->header_text_decoration == "none"}selected="selected"{/if}>none</option>
				<option value="underline" {if $customer_template->header_text_decoration == "underline"}selected="selected"{/if}>underline</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Header text color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->header_text_color}"  id="customer_template[header_text_color]" name="customer_template[header_text_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->header_text_color};" onClick="showColorPicker(this,document.getElementById('customer_template[header_text_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Header text border color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->header_text_border_color}"  id="customer_template[header_text_border_color]" name="customer_template[header_text_border_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->header_text_border_color};" onClick="showColorPicker(this,document.getElementById('customer_template[header_text_border_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Top header frame background color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->topheader_bg_color}"  id="customer_template[topheader_bg_color]" name="customer_template[topheader_bg_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->topheader_bg_color};" onClick="showColorPicker(this,document.getElementById('customer_template[topheader_bg_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Top header menu text color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->topheader_menu_text_color}"  id="customer_template[topheader_menu_text_color]" name="customer_template[topheader_menu_text_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->topheader_menu_text_color};" onClick="showColorPicker(this,document.getElementById('customer_template[topheader_menu_text_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Menu text color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->menu_text_color}"  id="customer_template[menu_text_color]" name="customer_template[menu_text_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->menu_text_color};" onClick="showColorPicker(this,document.getElementById('customer_template[menu_text_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Table header background color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->table_header_bg_color}"  id="customer_template[table_header_bg_color]" name="customer_template[table_header_bg_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->table_header_bg_color};" onClick="showColorPicker(this,document.getElementById('customer_template[table_header_bg_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Table headlight background color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->table_highlight_bg_color}"  id="customer_template[table_highlight_bg_color]" name="customer_template[table_highlight_bg_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->table_highlight_bg_color};" onClick="showColorPicker(this,document.getElementById('customer_template[table_highlight_bg_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Left menu background color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->left_menu_bg_color}"  id="customer_template[left_menu_bg_color]" name="customer_template[left_menu_bg_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->left_menu_bg_color};" onClick="showColorPicker(this,document.getElementById('customer_template[left_menu_bg_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Left menu text color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->left_menu_text_color}"  id="customer_template[left_menu_text_color]" name="customer_template[left_menu_text_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->left_menu_text_color};" onClick="showColorPicker(this,document.getElementById('customer_template[left_menu_text_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Tab header text color</td>
		<td class="post_highlight" width="70%" nowrap>
			<input type="text" value="{$customer_template->tab_header_text_color}"  id="customer_template[tab_header_text_color]" name="customer_template[tab_header_text_color]" />
			<div style="width: 10px; height: 10px; border: 1px solid black; background-color: {$customer_template->tab_header_text_color};" onClick="showColorPicker(this,document.getElementById('customer_template[tab_header_text_color]'));" />
		</td>
	</tr>
	<tr>
		<td class="highlight" width="30%" nowrap>Display logo <p><b>Notice:</b><br />Only image format accepted is .gif<br />Maximum dimensions of the logo should be 240px x 55px.</p></td>
		<td>
			<input type="file" name="photo_file" value="Choose file..." />
			<img src="{$photo_file}" width="240" height="55" />
		</td>
	</tr>
</table>
<p />
<input type="submit" name="save" value="Save">
<input type="submit" name="close" value="Close">
</form>
