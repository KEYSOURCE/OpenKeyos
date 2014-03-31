{assign var="customer_id" value=$customer->id}
{assign var="paging_titles" value="Customers, Manage Customers, Edit Customer, Create computers group"}
{assign var="paging_urls" value="/?cl=customer, /?cl=customer, /?cl=customer&op=customer_edit&id=$customer_id"}
{include file="paging.html"}

<p class="error">{$error_msg}</p>
<h1>Create computers group</h1>

<form name="frmCreateCompsGroup" action="" method="post">
{$form_redir}
<table class="list" width="98%">
    <thead>
            <tr>
                    <td colspan="2">Create new group for customer (#{$customer->id}){$customer->name}</td>
            </tr>
    </thead>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Title</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[title]" id="computer_group[title]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Description</td>
        <td class="post_highlight" style="width: 70%;" nowrap><textarea name="computer_group[description]" cols="100" rows="10" id="computer_group[description]" value=""></textarea></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Address</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[address]" id="computer_group[address]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Email</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[email]" id="computer_group[email]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Phone 1</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[phone1]" id="computer_group[phone1]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Phone 2</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[phone2]" id="computer_group[phone2]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Fax</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[fax]" id="computer_group[fax]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Yahoo! messenger id</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[yim]" id="computer_group[yim]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Skype id</td>
        <td class="post_highlight" style="width: 70%;" nowrap><input size="100" type="text" name="computer_group[skype_im]" id="computer_group[skype_im]" value=""></td>
    </tr>
    <tr>
        <td class="highlight" style="width: 30%;" nowrap>Country</td>
        <td class="post_highlight" style="width: 70%;" nowrap>
            <select name="computer_group[country]">
                    <option value="1" selected="selected">--</option>
                    {html_options options=$countries_list} 
            </select>
        </td>
    </tr>
    
</table>
</p>
<input type="submit" name="save" value="Save">
<input type="submit" name="close" value="Close">
</form>