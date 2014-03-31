{assign var="paging_titles" value="KLARA, Add Web Access Credentials"}
{assign var="paging_urls" value="/?cl=klara"}
{include file="paging.html"}

{literal}
<script type="text/javascript" language="JavaScript">
     var i=0;
     var ccnt = 0; 
     function mod_save(){
         var cnt = ccnt;
         var cr = "#cred_row_"+cnt;
         var username_hr = "#usr_in_"+cnt;
         var password_hr = "#pass_in_"+cnt;
         var notes_hr = "#notes_in_"+cnt;
          if(($("#m_cred_user").val()!="") && ($("#m_cred_pass").val() != "")){                 
                 var table_row = "<td>"+$("#m_cred_user").val()+"</td><td>"+$("#m_cred_pass").val()+"</td>";
                 table_row += "<td>"+$("#m_cred_notes").val()+"</td>";
                 table_row += "<td><a id='edt_"+cnt+"' href='#' onclick='edit_credential_row(this, "+cnt+")'>Edit</a></td>";   
                 table_row += "<td><a id='edt_"+cnt+"' href='#' onclick='delete_credential_row(this, "+cnt+")'>Delete</a></td>";    
                 $(username_hr+" input").val($("#m_cred_user").val());
                 $(password_hr+" input").val($("#m_cred_pass").val());
                 $(notes_hr+" input").val($("#m_cred_notes").val());
                 $("#m_cred_pass").val("");
                 $("#m_cred_user").val("");                   
                 $("#m_cred_notes").val("");              
                 $(cr).html(table_row);
                 $("#mod_cred").hide();                  
          }
     }
         
     function edit_credential_row(elem, cnt){  
         ccnt = cnt;
         var cr = "#cred_row_"+cnt;
         $('#mod_cred').show();
         var username_hr = "#usr_in_"+cnt;
         var password_hr = "#pass_in_"+cnt;
         var notes_hr = "#notes_in_"+cnt;

         $("#m_cred_user").val($(username_hr+" input").val());
         $("#m_cred_pass").val($(password_hr+" input").val());         
         $("#m_cred_notes").val($(notes_hr+" input").val());
             
         $("#mclx").click(function(){
             $("#m_cred_pass").val("");
             $("#m_cred_user").val("");
             $("#m_cred_notes").val("");                   
             $("#mod_cred").hide();
         });
                  
         $("#msvx").click(mod_save);        
     }  
     function delete_credential_row(elem, cnt){
         var cr = "#cred_row_"+cnt;         
         var username_hr = "#usr_in_"+cnt;
         var password_hr = "#pass_in_"+cnt;
         var notes_hr = "#notes_in_"+cnt;
               
        
         $(cr).html("");
         $(username_hr).html("");
         $(password_hr).html("");
         $(notes_hr).html("");
     }
     function add_credentials(){
         $("#add_cred").show();

         $("#clx").click(function(){
             $("#x_cred_pass").val("");
             $("#x_cred_user").val("");
             $("#x_cred_notes").val("");                   
             $("#add_cred").hide();                 
         });

         $("#svx").click(function(){                              
              if(($("#x_cred_user").val()!="") && ($("#x_cred_pass").val() != "")){
                   var user_input = "<div id='usr_in_"+i+"'><input type='hidden' name='webaccess[credentials]["+i+"][username]' value='"+$("#x_cred_user").val()+"' /></div>";
                   var pass_input = "<div id='pass_in_"+i+"'><input type='hidden' name='webaccess[credentials]["+i+"][password]' value='"+$("#x_cred_pass").val()+"' /></div>";
                   var notes_input = "<div id='notes_in_"+i+"'><input type='hidden' name='webaccess[credentials]["+i+"][notes]' value='"+$("#x_cred_notes").val()+"' /></div>";
                   var table_row = "<tr id='cred_row_"+i+"'><td>"+$("#x_cred_user").val()+"</td><td>"+$("#x_cred_pass").val()+"</td>";
                   table_row += "<td>"+$("#x_cred_notes").val()+"</td>";
                   table_row += "<td><a id='edt_"+i+"' href='#' onclick='edit_credential_row(this, "+i+")'>Edit</a></td>";   
                   table_row += "<td><a id='edt_"+i+"' href='#' onclick='delete_credential_row(this, "+i+")'>Delete</a></td></tr>";    
                   $("#x_cred_pass").val("");
                   $("#x_cred_user").val("");                   
                   $("#x_cred_notes").val("");                   
                   $("#hid_inputs").html($("#hid_inputs").html()+user_input+pass_input+notes_input);
                   $("#cred_list_body").html($("#cred_list_body").html()+table_row);
                   i++;
                   $("#add_cred").hide();                       

              } else {
                   $("#add_cred").hide();                       
              }
         });   
     }     
</script>
{/literal}    

<h1>Define WebAccess Credentials</h1>
<p class="error">
    {$error_msg}
</p>

<form name="webacc_frm" method="POST" action="">
{$form_redir}
<table class="list">
    <thead>
        <tr>
            <td colspan="2">Set new WebAccess information</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>URL:</td>
            <td><input type="text" name="webaccess[uri]" value="{$webaccess.uri}" size='100' /></td>
        </tr>
        <tr>
            <td>Comments:</td>
            <td>
                <textarea cols="100" rows="10" name="webaccess[comments]">{$webaccess.comments}</textarea>
            </td>
        </tr>
    </tbody>
</table> 
<table class='list' name="cred_tbl" id="cred_tbl">
    <thead>
       <tr>
           <td colspan="2">
               <a href="#" name="add_cred_lnk" id="add_cred_lnk" onclick="add_credentials();">Add credentials for this site</a>
           </td>
       </tr>
    </thead>
    <tbody name="cred_list_body" id="cred_list_body">
        <tr style="display: none;"><td colspan = "2">&nbsp;</td></tr>
    </tbody>
</table>

<div id="add_cred" style="display:none; width: 400px; height: 200px; border: 0px solid black;">
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; border: 0px solid black;">
              <span style="font-weight: bold; color: black; ">Username: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <input style="width: 190px;" type="text" name="x_cred_user" id="x_cred_user" value = "" />
          </div>
     </div>
     <div style="clear: both;"></div>
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; display: inline; border: 0px solid black;"> 
              <span style="font-weight: bold; color: black;">Password: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <input style="width: 190px;" style="width: 200px;" type="text" name="x_cred_pass" id="x_cred_pass" value = "" />
          </div>
     </div>
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; display: inline; border: 0px solid black;"> 
              <span style="font-weight: bold; color: black;">Notes: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <textarea style="width: 190px;" style="width: 200px;" name="x_cred_notes" id="x_cred_notes" ></textarea>
          </div>
     </div>
     <div style="clear: both;"></div>
     <div style="display: inline; margin_top: 20px;">
          <a href="#" style="float: left;" name="clx" id="clx">Close</a>
          <a href="#" style="float: right;" name="svx" id="svx">Save</a>
     </div>
</div>
<div id="mod_cred" style="display:none; width: 400px; height: 200px; border: 0px solid black;">
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; border: 0px solid black;">
              <span style="font-weight: bold; color: black; ">Username: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <input style="width: 190px;" type="text" name="m_cred_user" id="m_cred_user" value = "" />
          </div>
     </div>
     <div style="clear: both;"></div>
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; display: inline; border: 0px solid black;"> 
              <span style="font-weight: bold; color: black;">Password: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <input style="width: 190px;" style="width: 200px;" type="text" name="m_cred_pass" id="m_cred_pass" value = "" />
          </div>
     </div>
     <div style="display: inline;">
          <div style="width: 150px; text-align: right; float: left; display: inline; border: 0px solid black;"> 
              <span style="font-weight: bold; color: black;">Notes: </span>
          </div>
          <div style="width: 240px; text-align: left; float: right; border: 0px solid black;">
              <textarea style="width: 190px;" style="width: 200px;" name="m_cred_notes" id="m_cred_notes" ></textarea>
          </div>
     </div>
     <div style="clear: both;"></div>
     <div style="display: inline; margin_top: 20px;">
          <a href="#" style="float: left;" name="mclx" id="mclx">Close</a>
          <a href="#" style="float: right;" name="msvx" id="msvx" >Save</a>
     </div>
</div>            
<div name="hid_inputs" id="hid_inputs" style="display: block;" ></div>
<p />
<div style="clear: both;" ></div>
<input type="submit" name="save" value="Save" />
<input type="submit" name="cancel" value="Cancel" />
</form>
