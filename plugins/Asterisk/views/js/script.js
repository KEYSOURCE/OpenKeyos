$(document).ready(function(){
    if(missing_caller_id){
        $('#search_caller_id_lnk').fancybox({
            'scrolling'         : 'no',
            'titleShow'         : false,
            'autoDimensions'    : true,
            'showCloseButton'   : true,
            'centerOnScroll'    : true,
            'overlayOpacity'    : 0.9,
            'overlayColor'      : '#000',            
            'onClosed'		: function() {
                //do something here
            }

        }).trigger('click');
    } else {
        $('#search_caller_id_lnk').fancybox({
            'scrolling'         : 'no',
            'titleShow'         : false,
            'autoDimensions'    : true,
            'showCloseButton'   : true,
            'centerOnScroll'    : true,
            'overlayOpacity'    : 0.9,
            'overlayColor'      : '#000',
            'onClosed'		: function() {
                //do something here
            }

        });
        $('#search_caller_id_lnk').show();
        if(!caller_detected){
            $('#lnk_caller_unknown').fancybox({
                'scrolling'         : 'no',
                'titleShow'         : false,                
                'autoDimensions'    : true,
                'showCloseButton'   : true,
                'centerOnScroll'    : true,
                'overlayOpacity'    : 0.9,
                'overlayColor'      : '#000',
                'onComplete'        : function(){
                    
                },
                'onClosed'		: function() {
                    
                }

            }).trigger('click');
        }
    }
    submitForm = 0;
    $("#frm_search_caller").bind("submit", function() {
        if(submitForm==0) {

            $.fancybox.showActivity();
            
            return false;
        }
        else {
                return true;
        }
    });
              
    
    $('#search_caller_id_lnk').click(function(){
        $('#div_caller_id').show();
        return false;
    });
    $('#lnk_caller_unknown').click(function(){
        $('caller_unknown_actions').show();
        return false;
    })
    
    $('#ticket_sel').change(function(){
       if($(this).val() > 0){
           $('#action_title').text('Update ticket');
           $('#krifs_editor_lnk').attr('href', '/?cl=krifs&op=ticket_edit&id='+$(this).val());
           $('#krifs_editor_lnk').text('KRIFS editor update ticket');
           $('#ticket_comments_div').html("<p>Loading ticket's comments</p><img src='"+ajax_loader+"' style='margin: auto;'>");
           $('#ticket_comments_div').show();
       } else {
           $('#action_title').text('Create new ticket');
           $('#krifs_editor_lnk').attr('href', '/?cl=krifs&op=ticket_add&customer_id='+$('#customer_id').val());
           $('#krifs_editor_lnk').text('KRIFS editor new ticket');
           $('#ticket_comments_div').html("");
           $('#ticket_comments_div').hide();
       }
       $.getJSON(load_stuff_fn, {'action':'load_comments', 'ticket_id':$(this).val()}, function(data){
           comments_html = "<div id='comments_list' style='margin: 20px 0;'>";
           comments_html +="<label stlye='float:left; color: black; margin-top: 10px;'>Comments list for ticket: <b>(#"+data.ticket_id+") "+base64_decode(data.ticket_subject)+"</b></label><br />";
           comments_html +="<label style='float: left; color: black;'>Status: <b>"+data.ticket_status+"</b></label><br />";
           comments_html +="<label style='float: left; color: black;'>Last comment on: <b>"+data.last_comment.created+"</b> by <a href='/?cl=user&op=user_edit&id="+data.last_comment.user_id+"'>"+base64_decode(data.last_comment.user_name)+"</a></label>";
           comments_html += "<a href='#' id='show_hide_comments_lnk' style='float: right; margin-right: 10px;'>Hide comments</a></div><div id='comments_list_body'>";
           
           $.each(data.comments, function(){
               comment  = "<div id='comment_"+this.id+"' style='width: 98%;'>";
               comment += "<div style='background: #ccc;'>";               
               comment += " <span style='float: left; width: 60%; background: #ccc;'>Added by: <a href='/?cl=user&op=user_edit&id="+this.user_id+"'>"+base64_decode(this.user_name)+"</a> on "+this.created+"</span>";
               comment += " <span style='float: right; width: 40%; background: #ccc;'>Assigned to: <a href='/?cl=user&op=user_edit&id="+this.assigned_id+"'>"+base64_decode(this.assigned_name)+"</a></span>";
               comment += "</div>";
               comment += "<div style='clear: both'></div>";
               comment += "<div style='margin-top: 5px auto; border: 1px solid #999;'>"+base64_decode(this.comment)+"</div>";
               comment += "</div><p style='border-bottom: 0px solid black; width: 98%; margin: 5px auto;' />";
               comments_html += comment;
           });
           comments_html += "</div>";
           $('#ticket_comments_div').html(comments_html);           
           $('#actions').css({'height': 'auto !important'});
           $('#show_hide_comments_lnk').click(function(){
              if($('#comments_list_body').is(':visible')){
                  $('#comments_list_body').hide('slow');
                  $(this).text('Show comments');
              } else {                  
                  $('#comments_list_body').show('slow');
                  $(this).text('Hide comments');
              }
           });
       }); 
       //$('#ticket_stat').removeAttr('disabled')
    });
    
    $('#ticket_stat').change(function(){
       $.getJSON(load_stuff_fn, {'action':'load_tickets', 'customer_id':$('#customer_id').val(), 'status':$(this).val()}, function(data){
           opts = "<option value='0'>[Create new ticket]</option>";
           $.each(data, function(){
              opts += "<option value='"+this.id+"'>#"+this.id+" "+base64_decode(this.subject)+"</option>";
           });
           $('#ticket_sel').html(opts);
       });
    });
    
    $('#computers_sel').change(function(){
        if($(this).val() > 0){
            html = "<a target='_blank' style='float: left; margin: 5px;' href='/?cl=kawacs&op=computer_view&id="+$(this).val()+"'>View computer</a>";
            //html += "<a style='float: left; margin: 5px;' href='/?cl=krifs&op=ticket_add&'>Attach computer to new KRIFS ticket</a>";
            $('#computers_redirect_container').html(html);
        } else {
            $('#computers_redirect_container').html();
        }
    });
    
    $('#cu_customer_id').change(function(){        
        if($(this).val() > 0){
            $('#users_select_cell').html("<img src='"+ajax_loader+"' style='margin-left: 10px;' />");
            $('#users_hid').show('slow');
            $.getJSON(load_stuff_fn, {'action':'load_users', 'customer_id':$(this).val()}, function(data){
                
                html = '<select name="cu_user_id" id="cu_user_id"><option value="0">[Select user]</option>';
                $.each(data, function(k,v){                    
                    html += "<option value='"+k+"'>"+base64_decode(v)+"</option>";
                });
                html += "</select>";
                $('#users_select_cell').html(html);                
                $('#cu_user_id').change(function(){
                    if($(this).val() > 0){
                        $('#phones_list_cell').html("<img src='"+ajax_loader+"' style='margin-left: 10px;' />");
                        $('#phones_hid').show('slow');
                        $.getJSON(load_stuff_fn, {'action':'load_user_phones', 'user_id':$(this).val()}, function(data){
                            html = "<table style='width: 98%'><tr><td colspan='2'><label style='font-weight: bold;'>User's phones:</label><br /></td></tr>";
                            if((data != null) && (data.length > 0)){                                
                                $.each(data, function(){                                    
                                    html+="<tr style='border-bottom: 1px solid;'>";
                                    html+="<td>"+this.number+"</td>";
                                    html+="<td>"+base64_decode(this.type)+"</td>";
                                    html+="<td>"+base64_decode(this.comment)+"</td>";
                                    html+="</tr>";
                                });
                            } else {
                                html += "<tr><td colspan='3'>This user has no phones registered</td></tr>";
                            }
                            html += "</table><p />";
                            $('#phones_list_cell').html(html);
                            $('#phone_add_hid').show('slow');
                        });
                    } else {
                        $('#phones_hid').hide('slow');
                        $('#phone_add_hid').hide('slow');
                    }
                });
            });
        } else {
          $('#users_hid').hide('slow');  
        }
    });
    
    $('#cu_cusr_customer_id').change(function(){
        if($(this).val() > 0){    
            if($(this).val() != 6) {
                $('#cusr_type').val(user_type_customer);
            } else {
                $('#cusr_type').val(user_type_keysource);
            }
            $('#create_new_user_title').text('Create new user for customer (#'+$(this).val()+') '+$('#cu_cusr_customer_id option[value="'+$(this).val()+'"]').text());
        } else {
            $('#create_new_user_title').text('Create new KeySource user');
            $('#cusr_type').val(user_type_keysource);
        }
    });
   
    //GENERATE PASSWORD ON CLICK
    $("#btn_gen_auto_pass").click(function(){
        var pass = generate_password(8);
        $('#pass_txt').val(pass);
        $('#pass_confirm_txt').val(pass);
        $('#pass_confirm_txt').trigger('blur');
    });
    
    $("#frm_create_new_user").submit(function(){       
        //check that everything is ok with the submitted data
        //1. check that first name and last name are not empty
        $('#fname_err').text('');
        $('#lname_err').text('');
        ret = true;
        if(isEmpty($('#cusr_fname').val())){
            $('#fname_err').css('color', 'red');
            $('#fname_err').text('First name is mandatory');
            $('#create_new_user_submit').attr('disabled', 'disabled');
            ret = false;
        }
        if(isEmpty($('#cusr_lname').val())){
            $('#lname_err').css('color', 'red');
            $('#lname_err').text('Last name is mandatory');
            $('#create_new_user_submit').attr('disabled', 'disabled');
            ret = false;
        }
        if(!isValidEmail($('#cusr_email').val())){
            $('#eml_err').css('color', 'red');
            $('#eml_err').text('Invalid email detected');
            $('#create_new_user_submit').attr('disabled', 'disabled');
            ret = false;
        }
          
        if(isEmpty($('#pass_txt').val())){
            $('#confirm_err').css('color', 'red');
            $('#confirm_err').text('Password cannot be empty');
            $('#create_new_user_submit').attr('disabled', 'disabled');
            ret = false;
        }        
        if($('#pass_txt').val() != $('#pass_confirm_txt').val()){
            $('#confirm_err').css('color', 'red');
            $('#confirm_err').text('Passwords do not match');
            $('#create_new_user_submit').attr('disabled', 'disabled');
            ret = false;
        }
        
        $('#login_err').css('color', 'black');
        $('#login_err').text('  checking username...');
        $.ajax({
            type: 'POST',
            url : load_stuff_fn, 
            dataType: 'json',
            data: {'action':'check_username', 'username':$('#cusr_login').val()}, 
            async: false,
            success: function(data){            
                if(data!=null){             
                    if(data.status==0){
                        $('#login_err').css('color', 'red');
                        $('#login_err').text('This username is already in use');
                        $('#create_new_user_submit').attr('disabled', 'disabled');
                        ret = false;
                    } else {
                        $('#login_err').css('color', 'green  ');
                        $('#login_err').text('Username available');
                        $('#create_new_user_submit').removeAttr('disabled');                                               
                    }
                }
            }
        });        
        
        return ret;
    });
    
    $('input[name="customer_on_hold"]').change(function(){
        if($('input[name="customer_on_hold"]:checked').val() == 1){
            $('#onhold_auth_code').show();
        } else {
            $('#onhold_auth_code').hide();
        }
    });
});

function inhibit_all_actions_impl_divs(){
    $("#actions_impl_div div").each(function(){
        $(this).hide('slow');
    });
}


function add_to_existing_user(){
    inhibit_all_actions_impl_divs();
    $('#sel_action_title').text('Action: Add number to existing user');
    $('#add_to_existing_user_div').show('slow');
}
function create_new_user(){
    inhibit_all_actions_impl_divs();
    $('#sel_action_title').text('Action: Create new user');
    $('#create_new_user_div').show('slow');
    $('#subscription_frm').show('slow');    
    $('#cusr_fname').blur(function(){       
       sel_cust = $('#cu_cusr_customer_id').val();
       if(sel_cust!=0){
           ftl_cust = $('#cu_cusr_customer_id option[value="'+sel_cust+'"]').text().substring(0,2);
       } else {
           ftl_cust = 'KS';
       }
       if($('#cusr_lname').val()==''){
           est_login = ftl_cust + '.' + $(this).val();            
       } else {
           est_login = ftl_cust + '.' + $(this).val().substring(0,1) + $('#cusr_lname').val();
       }
       $('#cusr_login').val(est_login); 
    });
    
    $('#cusr_lname').blur(function(){
       sel_cust = $('#cu_cusr_customer_id').val();
       if(sel_cust!=0){
           ftl_cust = $('#cu_cusr_customer_id option[value="'+sel_cust+'"]').text().substring(0,2);
       } else {
           ftl_cust = 'KS';
       }
       if($('#cusr_fname').val()==''){
           est_login = ftl_cust + '.' + $(this).val();            
       } else {
           est_login = ftl_cust + '.' + $('#cusr_fname').val().substring(0,1) + $(this).val();
       }
       $('#cusr_login').val(est_login); 
    });       
    $('#pass_confirm_txt').blur(function(){
        if($('#pass_txt').val() != $('#pass_confirm_txt').val()){
            $('#confirm_err').css('color', 'red');
            $('#confirm_err').text('Passwords do not match');
            $('#create_new_user_submit').attr('disabled', 'disabled');
        } else {
            $('#confirm_err').css('color', 'green');
            $('#confirm_err').text('Passwords match');
            $('#create_new_user_submit').removeAttr('disabled');
        }
    });
    $('#cusr_email').blur(function(){
       //check if the email is in correct format
       if(!isValidEmail($(this).val())){
           $('#eml_err').css('color', 'red');
           $('#eml_err').text('Invalid email detected');
           $('#create_new_user_submit').attr('disabled', 'disabled');
       } else {
           $('#eml_err').text("");
           $('#create_new_user_submit').removeAttr('disabled', 'disabled');
       }
    });
    $('#cusr_login').blur(function(){
       //check username unicity
       $('#login_err').css('color', 'black');
       $('#login_err').text('  checking username...');
       $.getJSON(load_stuff_fn, {'action':'check_username', 'username':$(this).val()}, function(data){
           if(data!=null){             
               if(data.status==0){
                   $('#login_err').css('color', 'red');
                   $('#login_err').text('This username is already in use');
                   $('#create_new_user_submit').attr('disabled', 'disabled');
               } else {
                   $('#login_err').css('color', 'green  ');
                   $('#login_err').text('Username avavilable');
                   $('#create_new_user_submit').removeAttr('disabled');
               }
           }
       });
    });
    
}

function add_to_customer_contact(){
   inhibit_all_actions_impl_divs();
   $('#sel_action_title').text('Action: Add number to existing customer contact');
   $('#add_to_existing_customer_contact_div').show('slow');
   
   //get the customer contacts
   $('#cu_cc_customer_id').change(function(){
      if($(this).val() > 0){
          //get the contacts list for this customer, but first put the loader
          $('#cc_select_cell').html("<img src='"+ajax_loader+"' style='margin-left: 10px;' />");
          $('#customer_contacts_hid').show('slow');
          $.getJSON(load_stuff_fn, {'action':'load_customer_contacts', 'customer_id':$(this).val()}, function(data){
              html = '<select name="cu_contact_id" id="cu_contact_id"><option value="0">[Select contact]</option>';
              $.each(data, function(k,v){                    
                  html += "<option value='"+k+"'>"+base64_decode(v)+"</option>";              
              });
              html += "</select>";
              $('#cc_select_cell').html(html);
              $('#cu_contact_id').change(function(){
                  if($(this).val() > 0){
                      $('#cc_phones_list_cell').html("<img src='"+ajax_loader+"' style='margin-left: 10px;' />");
                      $('#cc_phones_hid').show('slow');
                      $.getJSON(load_stuff_fn, {'action':'load_contact_phones', 'contact_id':$(this).val()}, function(data){
                          html = "<table style='width: 98%'><tr><td colspan='2'><label style='font-weight: bold;'>Contacts's phones:</label><br /></td></tr>";
                          if((data != null) && (data.length > 0)){                                
                              $.each(data, function(){                                    
                                  html+="<tr style='border-bottom: 1px solid;'>";
                                  html+="<td>"+this.number+"</td>";
                                  html+="<td>"+base64_decode(this.type)+"</td>";
                                  html+="<td>"+base64_decode(this.comment)+"</td>";
                                  html+="</tr>";
                              });
                            } else {
                                html += "<tr><td colspan='3'>This user has no phones registered</td></tr>";
                            }
                            html += "</table><p />";
                            $('#cc_phones_list_cell').html(html);
                            $('#cc_phone_add_hid').show('slow');
                        });
                    } else {
                        $('#cc_phones_hid').hide('slow');
                        $('#cc_phone_add_hid').hide('slow');
                    }
                });
          });
      } else {
          //hide the contacts if they were displayed
          $('#customer_contacts_hid').hide('slow');
      }
   });
}

function create_new_customer_contact(){
  //create new customer contact
  inhibit_all_actions_impl_divs();
  $('#sel_action_title').text('Action: Create new customer contact');
  $('#create_new_contact_div').show('slow'); 
  //first name is mandatory do let's verify if it's completed correctly
  $('#ccont_fname').blur(function(){
      if(isEmpty($(this).val())){
          $('#cfname_err').css({'color':'red'});
          $('#cfname_err').text('First name is mandatory');
          $('#create_new_contact_submit').attr('disabled', 'disabled');
      } else {
          $('#cfname_err').text('');
          $('#create_new_contact_submit').removeAttr('disabled');
      }
  });
  $('#ccont_lname').blur(function(){
      if(isEmpty($(this).val())){
          $('#clname_err').css({'color':'red'});
          $('#clname_err').text('Last name is mandatory');
          $('#create_new_contact_submit').attr('disabled', 'disabled');
      } else {
          $('#clname_err').text('');
          $('#create_new_contact_submit').removeAttr('disabled');
      }
  });
  $('#ccont_email').blur(function(){
      if(!isValidEmail($(this).val())){
          $('#cemail_err').css({'color':'red'});
          $('#cemail_err').text('Incorect email format');
          $('#create_new_contact_submit').attr('disabled', 'disabled');
      } else {
          $('#cemail_err').text('');
          $('#create_new_contact_submit').removeAttr('disabled');
      }
  });
  $('#ccont_phone_number').blur(function(){
      if(isEmpty($(this).val())){
          $('#ccont_phone_number_err').css({'color':'red'});
          $('#ccont_phone_number_err').text('Phone number is mandatory');
          $('#create_new_contact_submit').attr('disabled', 'disabled');
      } else {
          $('#ccont_phone_number_err').text('');
          $('#create_new_contact_submit').removeAttr('disabled');
      }
  });
  $('#cu_ccont_customer_id').change(function(){
      if($(this).val() > 0){
          $('#contact_subscription_frm').show('slow');
          $('#create_new_contact_title').text('Create new contact for customer (#'+$(this).val()+') '+$('#cu_ccont_customer_id option[value="'+$(this).val()+'"]').text());
      } else {
          $('#create_new_contact_title').text('Create new contact');
          $('#contact_subscription_frm').hide('slow');
      }
  });
  $("#frm_create_new_contact").submit(function(){
     //check that all the data is ok if we can submit this form
     //first clear all the err fields
     $('#ccont_fname_err').text('');
     $('#ccont_lname_err').text('');
     $('#ccont_email_err').text('');
     $('#ccont_phone_number_err').text('');
     ret = true;
     if(isEmpty($('#ccont_fname').val())){
         $('#cfname_err').css({'color':'red'});
         $('#cfname_err').text('First name is mandatory');
         ret = false;
     }
     if(isEmpty($('#ccont_lname').val())){
         $('#clname_err').css({'color':'red'});
         $('#clname_err').text('Last name is mandatory');
         ret = false;
     }
     if(!isValidEmail($('#ccont_email').val())){
         $('#cemail_err').css({'color':'red'});
         $('#cemail_err').text('Incorect email format');
         ret = false;
     }
     if(isEmpty($('#ccont_phone_number').val())){
         $('#ccont_phone_number_err').css({'color':'red'});
         $('#ccont_phone_number_err').text('Phone number is mandatory');
         ret = false;
     }
     if(!ret) $('#create_new_contact_submit').attr('disabled', 'disabled');
     return ret;
  });
}

function create_new_customer(){
  inhibit_all_actions_impl_divs();
  $('#sel_action_title').text('Action: Create new customer');
  $('#create_new_customer_div').show('slow');
  
  $('#frm_create_new_customer').submit(function(){  
     //the name is mandatory
     $('#ccust_name_err').text('');
     if(isEmpty($('#ccust_name').val())){
         $('#ccust_name_err').css({'color': 'red'});
         $('#ccust_name_err').text('Name is mandatory');
         return false;
     } else {
         $.ajax({
            type: 'POST',
            url : load_stuff_fn, 
            dataType: 'json',
            data: {'action':'create_new_customer', 
                    'customer':{
                        'name':$('#ccust_name').val(),
                        'has_kawacs':$('#ccust_has_kawacs').val(),
                        'has_krifs':$('#ccust_has_krifs').val(),
                        'sla_hours':$('#ccust_sl_hours').val(),
                        'account_manager':$('#ccust_account_manager').val()
                    }
                }, 
            async: false,
            success: function(data){               
                //get the customer id that was returned
                customer_id = data.id;
                //should reload the customers list
                options = "<option value='0'>[Select customer]</option>";
                $.each(data.customers, function(k,v){
                    options += "<option value='"+k+"'>"+base64_decode(v)+"</option>";
                });
                $('#cu_cusr_customer_id').html(options)
                //select the value on the user subscription form
                $('#cu_cusr_customer_id').val(customer_id);
                $('#cu_cusr_customer_id').trigger('change');
                //$('#cu_cusr_customer_id').attr('disabled', 'disabled');
                create_new_user();
            }
         }); 
     }
     return false;
  });
}

function isEmpty(str) {
    if (!str || str == null || str == '' || str.replace(' ','') == '') return true;
    return false;
}
function isValidEmail(str) {
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    return reg.test(str);
}
function generate_password(length, special){
    var iteration = 0;
    var password = "";
    var randomNumber;
    if(special == undefined){
        var special = false;
    }
    while(iteration < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
        if(!special){
        if ((randomNumber >=33) && (randomNumber <=47)) {continue;}
        if ((randomNumber >=58) && (randomNumber <=64)) {continue;}
        if ((randomNumber >=91) && (randomNumber <=96)) {continue;}
        if ((randomNumber >=123) && (randomNumber <=126)) {continue;}
        }
        iteration++;
        password += String.fromCharCode(randomNumber);
    }
    return password;
}
function addslashes(str) {
    str=str.replace(/\\/g,'\\\\');
    str=str.replace(/\'/g,'\\\'');
    str=str.replace(/\"/g,'\\"');
    str=str.replace(/\0/g,'\\0');
    return str;
}
function stripslashes(str) {
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    str=str.replace(/\\0/g,'\0');
    str=str.replace(/\\\\/g,'\\');
    return str;
}