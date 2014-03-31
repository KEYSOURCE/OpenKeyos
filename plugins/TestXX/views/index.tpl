
{assign var="paging_titles" value="TestXX"}
{include file="paging.html"}

{literal}
    <script type='text/javascript'>
    //<![CDATA[
    $(document).ready(function(){
       //your ready code here
    });
    //]]>
    </script>
{/literal}

<h1>TestXX index action title</h1>
<p>
<font class="error">{$error_msg}</font>
<p>
<form action="" method="POST" name="index_frm">
{$form_redir}
Your custom code goes here
</form>
