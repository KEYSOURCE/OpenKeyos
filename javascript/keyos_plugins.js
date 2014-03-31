$(document).ready(function(){
    $.each(main_modules, function(){                
        if($('#menu_'+this['name']+'_div').length == 0){                        
            html = $('#plugins_menu_container').html();
            menu_holder = '<div id="menu_'+this['name']+'_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;"></div>'
            $('#plugins_menu_container').html(html + menu_holder);
        }
        if(($('#menu_'+this['name']+'_div').length != 0)  && ($('#menu_'+this.name).length == 0)){
            new_top_menu = '<td class="menu_separ">&nbsp;</td>';
            new_top_menu += '<td id="menu_'+this['name']+'" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"><a href="'+this['uri']+'">'+this['display_name']+'</a></td>';
            $("#mm_top_last").before(new_top_menu);                        
        }                    
    });
    
    $.each(main_customer_modules, function(){                
        if($('#menu_'+this['name']+'_div').length == 0){              
            html = $('#customer_plugins_menu_container').html();
            menu_holder = '<div id="menu_'+this['name']+'_div" class="menu" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" style="width: 220px; display: none;"></div>'            
            $('#customer_plugins_menu_container').html(html + menu_holder);
        }
        if(($('#menu_'+this['name']+'_div').length != 0) && ($('#menu_'+this.name).length == 0)) {
            new_top_menu = '<td class="menu_separ">&nbsp;</td>';
            new_top_menu += '<td id="menu_'+this['name']+'" onMouseOver="showMenu(this.id);" onMouseOut="hideMenu(this.id);" class="menu_top_item"><a href="'+this['uri']+'">'+this['display_name']+'</a></td>';            
            $("#mmc_top_last").before(new_top_menu);                        
        }
    });
    
    $.each(menu, function(){
        if($('#menu_'+this['module']+'_div').length > 0){
            if(this['module'] == this['submenu_of']){
                html = $('#menu_'+this['module']+'_div').html();
                submenu = '';
                if(this['add_separator_before']){
                    submenu += '<div class="menusepar">&nbsp;</div>';
                }
                submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';                           
                if(this['insert_before'] != undefined){
                    if($('#menu_'+this['insert_before']).length > 0){
                        if($('#menu_'+this.name).length == 0){
                            $('#menu_'+this['insert_before']).before(submenu);
                        }
                    }
                }
                else if(this['insert_after'] != undefined){                                
                    if($('#menu_'+this['insert_after']).length > 0){
                        if($('#menu_'+this.name).length == 0){
                            $('#menu_'+this['insert_after']).after(submenu);
                        }
                    }
                } else {           
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['module']+'_div').html(html + submenu);
                    }
                }

            } else {                           
                hrf = $('#menu_'+this['submenu_of']).attr('href');
                tx = $('#menu_'+this['submenu_of']).text();
                $('#menu_'+this['submenu_of']).replaceWith('<a href="'+hrf+'" id="menu_'+this['submenu_of']+'" onMouseOver="showSubMenu(this.id, \'menu_'+this['module']+'\');" onMouseOut="hideSubMenu(this.id, \'menu_'+this['module']+'\');">'+tx+'</a>');                            
                //tx = $('#menu_'+this['submenu_of']).text();
                //$('#menu_'+this['submenu_of']).text(tx + " &#0187;");
                if($('#menu_'+this['submenu_of']+'_div').length > 0){
                    html = $('#menu_'+this['submenu_of']+'_div').html();
                    submenu = '';
                    if(this['add_separator_before']){
                        submenu += '<div class="menusepar">&nbsp;</div>';
                    }
                    submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';                           
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['submenu_of']+'_div').html(html + submenu); 
                    }
                } else {
                    //add the div first
                    container_div = '<div id="menu_'+this['submenu_of']+'_div" class="menu" onMouseOver="showSubMenu(this.id, \'menu_'+this['module']+'\');" onMouseOut="hideSubMenu(this.id, \'menu_'+this['module']+'\');" style="display: none; width:240px;"><div>';
                    hh = $('#menu_'+this['module']+'_div').html();
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['module']+'_div').html(hh + container_div);
                    }
                    html = $('#menu_'+this['submenu_of']+'_div').html();
                    submenu = '';
                    if(this['add_separator_before']){
                        submenu += '<div class="menusepar">&nbsp;</div>';
                    }
                    submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';                           
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['submenu_of']+'_div').html(html + submenu); 
                    }
                }
            }
        } 
    });
    
    $.each(menu_customer, function(){
        if($('#menu_'+this['module']+'_div').length > 0){
            if(this['module'] == this['submenu_of']){
                html = $('#menu_'+this['module']+'_div').html();
                submenu = '';
                if(this['add_separator_before']){
                    submenu += '<div class="menusepar">&nbsp;</div>';
                }
                submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';                           
                if(this['insert_before'] != undefined){
                    if($('#menu_'+this['insert_before']).length > 0){
                        if($('#menu_'+this.name).length == 0){
                            $('#menu_'+this['insert_before']).before(submenu);
                        }
                    }
                }
                else if(this['insert_after'] != undefined){                                
                    if($('#menu_'+this['insert_after']).length > 0){
                        if($('#menu_'+this.name).length == 0){
                            $('#menu_'+this['insert_after']).after(submenu);
                        }
                    }
                } else {    
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['module']+'_div').html(html + submenu);
                    }
                }

            } else {                           
                hrf = $('#menu_'+this['submenu_of']).attr('href');
                tx = $('#menu_'+this['submenu_of']).text();
                $('#menu_'+this['submenu_of']).replaceWith('<a href="'+hrf+'" id="menu_'+this['submenu_of']+'" onMouseOver="showSubMenu(this.id, \'menu_'+this['module']+'\');" onMouseOut="hideSubMenu(this.id, \'menu_'+this['module']+'\');">'+tx+'</a>');                            
                //tx = $('#menu_'+this['submenu_of']).text();
                //$('#menu_'+this['submenu_of']).text(tx + " &#0187;");
                if($('#menu_'+this['submenu_of']+'_div').length > 0){
                    html = $('#menu_'+this['submenu_of']+'_div').html();
                    submenu = '';
                    if(this['add_separator_before']){
                        submenu += '<div class="menusepar">&nbsp;</div>';
                    }
                    submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';   
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['submenu_of']+'_div').html(html + submenu); 
                    }
                } else {
                    //add the div first
                    container_div = '<div id="menu_'+this['submenu_of']+'_div" class="menu" onMouseOver="showSubMenu(this.id, \'menu_'+this['module']+'\');" onMouseOut="hideSubMenu(this.id, \'menu_'+this['module']+'\');" style="display: none; width:240px;"><div>';
                    hh = $('#menu_'+this['module']+'_div').html();
                    $('#menu_'+this['module']+'_div').html(hh + container_div);
                    html = $('#menu_'+this['submenu_of']+'_div').html();
                    submenu = '';
                    if(this['add_separator_before']){
                        submenu += '<div class="menusepar">&nbsp;</div>';
                    }
                    submenu += '<a id="menu_'+this['name']+'" href="'+this['uri']+' ">'+this['display_name']+'</a>';  
                    if($('#menu_'+this.name).length == 0){
                        $('#menu_'+this['submenu_of']+'_div').html(html + submenu); 
                    }
                }
            }
        } 
    });
});
