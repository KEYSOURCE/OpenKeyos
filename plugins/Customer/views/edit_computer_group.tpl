{assign var="customer_id" value=$group->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Computer groups"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id, /?cl=customer&op=view_computer_groups&customer_id=$customer_id"}
{include file="paging.html"}

{literal}
<script language="JavaScript">

function selectAllMembers ()
{
	frm = document.forms['frmCreateCompsGroup']
	mbr_list = frm.elements['computer_group[computers_list][]']
	
	for (i=0; i<mbr_list.options.length; i++)
	{
		mbr_list.options[i].selected = true
	}
}

function addMember ()
{
	frm = document.forms['frmCreateCompsGroup']
	mbr_list = frm.elements['computer_group[computers_list][]']
	computers_list = frm.elements['available_computers']
	
	if (computers_list.selectedIndex >= 0)
	{
		opt = new Option (computers_list.options[computers_list.selectedIndex].text, computers_list.options[computers_list.selectedIndex].value, false, false)
		
		mbr_list.options[mbr_list.options.length] = opt
		computers_list.options[computers_list.selectedIndex] = null
	}
}

function removeMember ()
{
	frm = document.forms['frmCreateCompsGroup']
	mbr_list = frm.elements['computer_group[computers_list][]']
	computers_list = frm.elements['available_computers']
	
	if (mbr_list.selectedIndex >= 0)
	{
		opt = new Option (mbr_list.options[mbr_list.selectedIndex].text, mbr_list.options[mbr_list.selectedIndex].value, false, false)
		
		computers_list.options[computers_list.options.length] = opt
		mbr_list.options[mbr_list.selectedIndex] = null
	}
}

</script>
{/literal}


<p class="error">{$error_msg}</p>
<h1>Edit computers group (#{$group->id}) {$group->title}</h1>
<p />
<form name="frmCreateCompsGroup" action="" method="post" onSubmit="selectAllMembers(); return true;">
{$form_redir}
<table class="list" width="98%">
    <thead>
            <tr>
                    <td colspan="2">Edit group (#{$group->id}){$group->title}</td>
            </tr>
    </thead>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Title</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[title]" id="computer_group[title]" value="{$group->title}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Description</td>
        <td class="post_highlight" style="width: 70%;" nowrap><textarea name="computer_group[description]" cols="100" rows="10" id="computer_group[description]">{$group->description|nl2br}</textarea></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Address</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[address]" id="computer_group[address]" value="{$group->address}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Email</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[email]" id="computer_group[email]" value="{$group->email}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Phone 1</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[phone1]" id="computer_group[phone1]" value="{$group->phone1}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Phone 2</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[phone2]" id="computer_group[phone2]" value="{$group->phone2}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Fax</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[fax]" id="computer_group[fax]" value="{$group->fax}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Yahoo! messenger id</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[yim]" id="computer_group[yim]" value="{$group->yim}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Skype id</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[skype_im]" id="computer_group[skype_im]" value="{$group->skype_im}"></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Country</td>
        <td class="post_highlight" style="width: 70%;" nowrap>
            <select name="computer_group[country]">
                    {assign var="country_id" value=$group->country}
                    <option value="1" selected="selected">--</option>
                    {html_options options=$countries_list selected=$country_id} 
            </select>
        </td>
    </tr>
    
</table>
<p />
<div style="width: 70%; margin-right: 15%; margin-left: 15%">
<table class="list" width="100%">
    <thead>
        <tr>
            <td colspan="2" style="text-align: center;">Add computers to this group</td>
        </tr>
        <tr>
		<td style="text-align:center;">Current computers list</td>
		<td style="text-align:center;">Available computers</td>
	</tr>
    </thead>
    <tr>
        <td width="50%" style="text-align: center;">
                <select name="computer_group[computers_list][]" id="computer_group[computers_list][]" size=20 style="width: 200px;" multiple onDblClick="removeMember();">
                        {foreach from=$group->computers_list item=computer_id}
                                <option value="{$computer_id}">{$customer_computers.$computer_id}</option>
                        {/foreach}
                </select>
        </td>
        
        <td width="50%" style="text-align: center;">
                <select name="available_computers" size=20  style="width: 200px;" multiple onDblClick="addMember();">
                        {foreach from=$customer_computers key=computer_id item=computer_name}
                                {if !in_array($computer_id, $group->computers_list)}
                                        <option value="{$computer_id}">{$computer_name}</option>
                                {/if}
                        {/foreach}                                
                </select>
        
        </td>
    </tr>
</table>
</div>
<p />
<input type="submit" name="save" value="Save">
<input type="submit" name="close" value="Close">
</form>