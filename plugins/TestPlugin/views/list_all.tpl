{assign var="paging_titles" value="TestPlugin, List all"}
{assign var="paging_urls" value="/?cl=test_plugin"}
{*include file='./templates/paging.tpl'*}

<h1>Test Plugin List all items</h1>
<p class='error'>{$error_msg}</p>
<form name="frm_testPlugin_listall" method="POST" action="">
    {$form_redir}
    <table class="list" width="98%">
       <thead>
           <tr>
               <td>Id</td>
               <td>Name</td>
               <td>Value</td>
               <td>Date Added</td>
               <td>Last modification</td>
               <td>Modified by</td>
           </tr>
       </thead>
       <tbody>
           {foreach from=$items item=item}               
               <tr>
                   <td>{$item->id}</td>
                   <td>{$item->name}</td>
                   <td>{$item->value}</td>
                   <td>{$item->date_added|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
                   <td>{$item->date_last_modification|date_format:$smarty.const.DATE_TIME_FORMAT_SMARTY}</td>
                   <td>
                       {assign var=usr value=$item->usr_obj}
                       (#{$usr->id}){$usr->fname} {$usr->lname}
                   </td>
               </tr>
           {foreachelse}
               <tr>
                   <td colspan="6">[No items yet]</td>
               </tr>
           {/foreach}
       </tbody>
    </table>
</form>
