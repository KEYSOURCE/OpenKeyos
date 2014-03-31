<?php
/**
 * install the platform - should setup all the terms in the config init file
 * and save the config
 */

ini_set('display_errors', 1);
require_once __DIR__ . '/install_lib.php';
if(file_exists(abs_path("install/generated/.install"))){
    $ff = file_get_contents(abs_path("install/generated/.install"));
    if(is_numeric($ff)) {
        $step = (int)($ff);
        if($step==1){
            if(file_exists(abs_path('config.ini'))) $config_vars = parse_ini_file(abs_path('config.ini'));
            else parse_ini_file(abs_path("install/default_config.ini"));
        }
        if($step >1){
            $config_vars = parse_ini_file(abs_path('config.ini'));
        }
        if($step == 2){
            require_once abs_path("install/db.php");
            define('DB_HOST', $config_vars['db_host']);
            define('DB_USER', $config_vars['db_user']);
            define('DB_PWD', $config_vars['db_password']);
            define('DB_NAME', $config_vars['db_name']);
            $existing_tables = db::db_fetch_vector("show tables");
        }
    }
} else {
    $default_config_file = joinPaths(__DIR__, "default_config.ini");
    $config_vars = parse_ini_file($default_config_file);
    $step = 1;


    $generated_writable = true;
    if(!is_writable(abs_path("install/generated"))){
        $generated_writable = false;
        $step = 0;
    }
}
if(isset($_POST) and !empty($_POST)){
    if(isset($_POST['submit']) and $_POST['submit'] == 'Save settings' and $step==1){
        $frm_vars = array_diff_key($_POST, array('submit'=>'Save settings'));
        $rem_vars = array_diff_key($config_vars, $frm_vars);
        $new_config = array_merge($frm_vars, $rem_vars);
        $config_file_name = abs_path("config.ini");
        $gen_error = "";
        $gen_succ = "";
        try{
            if(write_ini_file($new_config, $config_file_name, FALSE)){
                $gen_succ = "Configuration file generated";
                $step+=1;
            } else {
                $backup_config_file = abs_path("install/generated/config.ini");

                if(write_ini_file($new_config, $backup_config_file, FALSE)){
                    $gen_error = "Configuration file generation error:<br />";
                    $gen_error .= "Could not write the file " . $config_file_name . "<br />The file " . $backup_config_file . " was generated, please place this file in this location: " . dirname($config_file_name);
                    if($handle = fopen(abs_path("install/generated/.install"), "w")){
                        $next_step = $step+1;
                        fwrite($handle, (string)$next_step);
                        fclose($handle);
                    }
                }
                else{
                    $gen_error = "Configuration file generation error:<br />Could not write the file " . $config_file_name;
                }
            }

        } catch(\Exception $e){
            $gen_error = "Configuration file generation error:<br />" . $e->getCode() . " :: " . $e->getMessage();
        }
    }
    elseif(isset($_POST['clean']) and $_POST['clean'] == 'Wipe all and install' and $step==2){
        if(isset($_POST['db_script'])){
            $db_file = $_POST['db_script'];
            if(!file_exists($db_file)){
                //bogus file given - use our own
                $db_file = abs_path("install/db_schema.sql");
            }

            $res = install_clean_database($db_file, $config_vars['db_host'], $config_vars['db_user'], $config_vars['db_password'], $config_vars['db_name']);
            if(!$res){
                $gen_error = "An error occurred while trying to install the database";
            }   else {
                $gen_succ = "Database successfully installed!";
                $step+=1;
                if($handle = fopen(abs_path("install/generated/.install"), "w")){
                    $next_step = $step;
                    fwrite($handle, (string)$next_step);
                    fclose($handle);
                }
            }
        }
    }
    elseif(isset($_POST['skip']) and $_POST['skip'] == 'Skip install' and $step==2){
        //skip the installation process
        //delete the .install file if one exists
        $res = unlink(abs_path('install/generated/.install'));
        if( ! $res){
            $gen_error = "Please delete the following file: " . abs_path('install/generated/.install') . "</br>";
            $rr = chmod(abs_path("install"), 0400);
            if(! $rr){
                $gen_error .= "Before continuing, please make sure you change the permissions for the install folder to 0400, or delete it!";
            }
        } else {
            $step += 1;
            if($handle = fopen(abs_path("install/generated/.install"), "w")){
                $next_step = $step;
                fwrite($handle, (string)$next_step);
                fclose($handle);
            }
            //header('Location: ' . get_base_url_install());
        }
    }
    elseif(isset($_POST['update']) and $_POST['update'] == 'Update database' and $step==2){
        apply_database_update($config_vars['db_host'], $config_vars['db_user'], $config_vars['db_password'], $config_vars['db_name']);
        $step += 1;
        if($handle = fopen(abs_path("install/generated/.install"), "w")){
            $next_step = $step;
            fwrite($handle, (string)$next_step);
            fclose($handle);
        }
        //header('Location: ' . get_base_url_install());
    }
    elseif(isset($_POST['create']) and $_POST['create'] == 'Create' and $step==3){
        if(set_customer($_REQUEST['manager_customer'])){
            @unlink(abs_path("install/generated/.install"));
            header('Location: ' . get_base_url_install());
        }
    }



}


function generate_config_form($config_vars){
    echo "<div style='width: 70%; margin:auto; overflow: hidden; border: 0px solid black;'>";
    echo "<form id='frm_config' method='POST' action='" . $_SERVER['PHP_SELF'] . "'>";
    echo '<table class="list" width="100%">
            <thead>
                <tr class="head">
                    <td>Setting name</td>
                    <td>Setting value</td>
                    <td>Observations</td>
                </tr>
            </thead>
         ';

    echo "<tr class='head'><td colspan=3>Base URL</td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Base url:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='base_url' id='base_url' value='" . get_base_url_install() . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";

    echo "<tr class='head'><td colspan=2>Database server settings</td><td><input style='border: 1px solid black;' type='button' id='btn_db_test' name='btn_db_test' value='Test database settings' /><span id='err_db'></span></td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Database host:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='db_host' id='db_host' value='" . $config_vars['db_host'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Database user:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='db_user' id='db_user' value='" . $config_vars['db_user'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Database password:</td>";
    echo "<td class='post_highlight'><input type='password' style='width: 200px;' name='db_password' id='db_password' value='" . $config_vars['db_password'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Database name:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='db_name' id='db_name' value='" . $config_vars['db_name'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";

    echo "<tr class='head'><td colspan=2>Smarty settings</td><td><input style='border: 1px solid black;' type='button' id='btn_smarty_test' name='btn_smarty_test' value='Test smarty settings'><span id='err_smarty'></span></td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Smarty cache dir:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='smarty_cache' id='smarty_cache' value='" . $config_vars['smarty_cache'] . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Smarty config dir:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='smarty_configs' id='smarty_configs' value='" . $config_vars['smarty_configs'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Smarty compile dir:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='smarty_compile' id='smarty_compile' value='" . $config_vars['smarty_compile'] . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";
    echo "<tr class='head'><td colspan=2>Caching settings</td><td><input style='border: 1px solid black;' type='button' id='btn_cache_test' name='btn_cache_test' value='Test cache settings'><span id='err_cache'></span></td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Use caching:</td>";
    echo "<td class='post_highlight'><select name='use_caching' id='use_caching'>";
    if($config_vars['use_caching']){
        echo "<option value='1' selected='selected'>Yes</option>";
        echo "<option value='0' >No</option>";
    } else {
        echo "<option value='1'>Yes</option>";
        echo "<option value='0' selected='selected'>No</option>";
    }
    echo "</select></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Cache engine</td>";
    echo "<td class='post_highlight'><select name='cache_engine' id='cache_engine'>";
    if($config_vars['use_caching'] == 'redis'){
        echo "<option value='redis' selected='selected'>Redis</option>";
        echo "<option value='Memcache'>Memcache</option>";
    }
    elseif($config_vars['use_caching'] == 'memcache'){
        echo "<option value='redis' >Redis</option>";
        echo "<option value='Memcache' selected='selected'>Memcache</option>";
    }
    else{
        echo "<option value='redis' selected='selected'>Redis</option>";
        echo "<option value='memcache'>Memcache</option>";
    }
    echo "</select></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Cache key prefix:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='cache_key_prefix' id='cache_key_prefix' value='" . $config_vars['cache_key_prefix'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Cache default TTL:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='cache_default_ttl' id='cache_default_ttl' value='" . $config_vars['cache_default_ttl'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Cache server:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='cache_default_server' id='cache_default_server' value='" . $config_vars['cache_default_server'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Cache server port:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='cache_default_port' id='cache_default_port' value='" . $config_vars['cache_default_port'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";
    echo "<tr class='head'><td colspan=3>Logging</td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Log level:</td>";
    echo "<td class='post_highlight'><select name='log_level' id='log_level'>";
    if($config_vars['log_level'] == 1)
        echo "<option value='1' selected='selected'>Errors only (recommended in production)</option>";
    else
        echo "<option value='1'>Errors only (recommended in production)</option>";

    if($config_vars['log_level'] == 2)
        echo "<option value='2' selected='selected'>Trace</option>";
    else
        echo "<option value='2'>Trace</option>";

    if($config_vars['log_level'] == 3)
        echo "<option value='3' selected='selected'>Log debug info</option>";
    else
        echo "<option value='3'>Log debug info</option>";

    echo "</select></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Show errors in front end:</td>";
    echo "<td class='post_highlight'><select name='front_end_errors' id='front_end_errors'>";
    if($config_vars['front_end_errors']){
        echo "<option value='1' selected='selected'>Yes</option>";
        echo "<option value='0' >No</option>";
    } else {
        echo "<option value='1'>Yes</option>";
        echo "<option value='0' selected='selected'>No</option>";
    }
    echo "</select></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";

    echo "<tr class='head'><td colspan=3>E-mailing - OpenKeyOS generated emails</td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Sender name:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='sender_name' id='sender_name' value='" . $config_vars['sender_name'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td class='highlight'>Sender email:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='sender_email' id='sender_email' value='" . $config_vars['sender_email'] . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";

    echo "<tr class='head'><td colspan=2>Useful paths</td><td><!-- <input style='border: 1px solid black;' type='button' id='btn_paths_test' name='btn_paths_test' value='Test paths'><span id='err_paths'></span> --></td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Java HOME:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='java_home' id='java_home' value='" . abs_path($config_vars['java_home']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>XSLT processor:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='xsltproc' id='xsltproc' value='" . abs_path($config_vars['xsltproc']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>FOP parser:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='fop_parser' id='fop_parser' value='" . abs_path($config_vars['fop_parser']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Zip:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='path_to_zip' id='path_to_zip' value='" . abs_path($config_vars['path_to_zip']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Unzip:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='path_to_unzip' id='path_to_unzip' value='" . abs_path($config_vars['path_to_unzip']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>md5sum:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='path_to_md5sum' id='path_to_md5sum' value='" . abs_path($config_vars['path_to_md5sum']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='3'>&nbsp;</td></tr>";

    echo "<tr class='head'><td colspan=2>OpenKeyOS paths</td><td><!--<input style='border: 1px solid black;' type='button' id='btn_dirs_test' name='btn_dirs_test' value='Test directories'><span id='err_dirs'>--></td></tr>";
    echo "<tr>";
    echo "<td class='highlight'>Monitor items files - upload directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_monitor_items_file' id='dir_monitor_items_file' value='" . abs_path($config_vars['dir_monitor_items_file']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>KRIFS module - upload directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_upload_krifs' id='dir_upload_krifs' value='" . abs_path($config_vars['dir_upload_krifs']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>KLARA module - upload directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_upload_klara' id='dir_upload_klara' value='" . abs_path($config_vars['dir_upload_klara']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Customers module - upload directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_upload_customer' id='dir_upload_customer' value='" . abs_path($config_vars['dir_upload_customer']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>KALM module - upload diretory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_upload_kalm' id='dir_upload_kalm' value='" . abs_path($config_vars['dir_upload_kalm']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>SNMP mib files - upload directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_upload_mibs' id='dir_upload_mibs' value='" . abs_path($config_vars['dir_upload_mibs']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Intervestion Reports exports directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_export_xml_interventions' id='dir_export_xml_interventions' value='" . abs_path($config_vars['dir_export_xml_interventions']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Timesheets exports directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_export_xml_timesheets' id='dir_export_xml_timesheets' value='" . abs_path($config_vars['dir_export_xml_timesheets']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>Mremote files exports</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_export_xml_mremote' id='dir_export_xml_mremote' value='" . abs_path($config_vars['dir_export_xml_mremote']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>KAWACS agent deployer:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_agent_deployer' id='dir_agent_deployer' value='" . abs_path($config_vars['dir_agent_deployer']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>OpenKeyOS temporary directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_keyos_temp' id='dir_keyos_temp' value='" . abs_path($config_vars['dir_keyos_temp']) . "'></td>";
    echo "<td>This folder must be writable by the web server</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='highlight'>3rd party apps directory:</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='dir_keyos_external' id='dir_keyos_external' value='" . abs_path($config_vars['dir_keyos_external']) . "'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='2'>&nbsp;</td><td><input type='submit' name='submit' id='submit' value='Save settings'></td></tr>";

    echo "</table>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
    echo "<div style='clear: both; height: 50px;'></div>";
}
function install_database($db_file, $existing_tables = null){
    echo "<div style='width: 70%; margin:auto; overflow: hidden; border: 0px solid black;'>";
    echo "<form id='frm_config' method='POST' action='" . $_SERVER['PHP_SELF'] . "'>";
    echo '<table class="list" width="100%">
            <thead>
                <tr class="head">
                    <td>Install database</td>
                    <td>Database sql file</td>
                    <td>Observations</td>
                </tr>
            </thead>
         ';

    echo "<tr>";
    echo "<td class='highlight'>Database file</td>";
    echo "<td class='post_highlight'><input type='hidden' style='width: 200px;' name='db_script' id='db_script' value='" . $db_file . "'><span>" . $db_file . "</span></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='2'>&nbsp;</td><td><input type='submit' name='update' id='update' value='Update database'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='skip' value='Skip install'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='clean' value='Wipe all and install'></td></tr>";

    if($existing_tables and is_array($existing_tables) and !empty($existing_tables)){
        echo "<tr><td colspan='3'>&nbsp;</td></tr>";
        echo "<tr><td colspan='3' style='color: orange; font-weight: bold;'>Another version of the database was detected</td></tr>";
        echo "<tr><td colspan='2'>The installed database already contains these tables:</td><td>";
        foreach($existing_tables as $tbl){
            echo $tbl . "</br />";
        }
        echo "</td></tr>";
    }

    echo "</table>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
    echo "<div style='clear: both; height: 50px;'></div>";
}

function default_settings(){
    echo "<div style='width: 70%; margin:auto; overflow: hidden; border: 0px solid black;'>";
    echo "<form id='frm_def_setting' method='POST' action='" . $_SERVER['PHP_SELF'] . "'>";
    echo '<table class="list" width="100%">
            <thead>
                <tr class="head">
                    <td>Setting</td>
                    <td>Value</td>
                    <td>&nbsp;</td>
                </tr>
            </thead>
         ';

    echo "<tr>";
    echo "<td class='highlight'>Manager Customer Name</td>";
    echo "<td class='post_highlight'><input type='text' style='width: 200px;' name='manager_customer' id='manager_customer' value='Your Company'></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr><td colspan='2'>&nbsp;</td><td><input type='submit' name='create' id='create' value='Create'></td></tr>";

    echo "</table>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
    echo "<div style='clear: both; height: 50px;'></div>";
}
?>



<html>
    <head>
        <title>Open KeyOS Installer</title>
        <script type="text/javascript" src="../javascript/jquery.min.js"></script>
        <script type="text/javascript">
            //<![CDATA[
            function placeErrorMessage(e, name, message){

                var div = $("<div></div>");
                div.css({top: e.offset().top , left: e.offset().left + e.width() + 10, zIndex: 10000, position: 'absolute', minWidth:'300px', minHeight:'50px'});
                div.attr({id: name, class: 'err_bubble'});
                div.html(message);
                $("body").append(div);
                $("body").not(div).click(function(){
                   div.hide();
                });
            }

            function placeSuccessMessage(e, name, message){
                var div = $("<div></div>");
                div.css({top: e.offset().top , left: e.offset().left + e.width() + 10, zIndex: 10000, position: 'absolute', minWidth:'300px', minHeight:'50px'});
                div.attr({id: name, class: 'succ_bubble'});
                div.html(message);
                $("body").append(div);
                $("body").not(div).click(function(){
                    div.hide();
                });
            }

            $(document).ready(function(){
                <?php
                if(!$generated_writable){
                ?>
                   msg = "Please make the directory: <?php echo abs_path('install/generated'); ?> writable.";
                   placeErrorMessage($('#err_gen'), 'gen_err_msg', msg);
                <?php
                }
                ?>
               <?php
                    if(isset($gen_error)){
               ?>
                    msg = "<?php echo  $gen_error; ?>";
                    placeErrorMessage($('#err_gen'), 'gen_err_msg', msg);
               <?php
                    }
               ?>

                <?php
                     if(isset($gen_succ)){
                ?>
                    msg = "<?php echo  $gen_succ; ?>";
                    placeSuccessMessage($('#err_gen'), 'gen_err_msg', msg);
                <?php
                     }
                ?>

               if($('#btn_db_test').length){
                   $('#btn_db_test').click(function(){
                       $.ajax({
                            url: '/install/settings_test.php',
                            method: 'post',
                            data: {'test_type':'database',
                                'db_host':$('#db_host').val(),
                                'db_user':$('#db_user').val(),
                                'db_password':$('#db_password').val(),
                                'db_name':$('#db_name').val()
                            },
                            dataType: 'json',
                            success: function(data){
                                //alert(data.result);
                                if(data.result == "ok"){
                                    success_message = "Database connection established successfully";
                                    placeSuccessMessage($('#err_db'), 'db_connect_msg', success_message);
                                } else {
                                    error_message = '<b>Database connection error</b><p />'  +  data.observations;
                                    placeErrorMessage($('#err_db'), 'db_connect_msg', error_message);
                                }
                            }
                       });

                   });
               }

               if($('#btn_smarty_test').length){
                    $('#btn_smarty_test').click(function(){
                        $.ajax({
                            url: '/install/settings_test.php',
                            method: 'post',
                            data: {'test_type':'smarty',
                                'smarty_cache_dir':$('#smarty_cache').val(),
                                'smarty_config_dir':$('#smarty_configs').val(),
                                'smarty_compile_dir':$('#smarty_compile').val()
                            },
                            dataType: 'json',
                            success: function(data){
                                //alert(data.result);
                                if(data.result == "ok"){
                                    success_message = "Smarty directories are setup ok";
                                    placeSuccessMessage($('#err_smarty'), 'smarty_check_msg', success_message);
                                } else {
                                    error_message = '<b>Smarty directories check returned the following problems:</b><p />'  +  data.observations;
                                    placeErrorMessage($('#err_smarty'), 'smarty_check_msg', error_message);
                                }
                            }
                        });

                    });
                }

                if($('#btn_cache_test').length){
                    $('#btn_cache_test').click(function(){
                        $.ajax({
                            url: '/install/settings_test.php',
                            method: 'post',
                            data: {
                                'test_type': 'cache',
                                'use_caching': $('#use_caching').val(),
                                'cache_engine': $('#cache_engine').val(),
                                'cache_default_ttl': $('#cache_default_ttl').val(),
                                'cache_default_server': $('#cache_default_server').val(),
                                'cache_default_port': $('#cache_default_port').val()
                            },
                            dataType: 'json',
                            success: function(data){
                                if(data.result == "ok"){
                                    success_message = "Chaching is set up correctly!";
                                    placeSuccessMessage($('#err_cache'), 'cache_check_msg', success_message);
                                } else {
                                    error_message = "<b>There is an error in your cache settings. Please correct the following problems before continuing:</b> <p />" + data.observations;
                                    placeErrorMessage($('#err_cache'), 'cache_check_msg', error_message);
                                }

                            }

                        });
                    });
                }
            });
            //]]>
        </script>
        <style type="text/css">@import url('/main.css');</style>
        <style type="text/css">
            .err_bubble{
                font-size: 12px;
                font-style: italic;
                margin-left: 10px;
                color: red;
                float: left;
                padding: 5px 10px;
                border: 1px solid red;
                -moz-border-radius: 5px 5px 5px 5px;
                -webkit-border-radius: 5px 5px 5px 5px;
                border-radius: 5px 5px 5px 5px;
                background-color: #ebeddf;
                -moz-box-shadow: 0px 2px 3px 2px #888;
                -webkit-box-shadow: 0px 2px 3px 2px #888;
                box-shadow: 0px 2px 3px 2px #888;
            }

            .succ_bubble{
                font-size: 12px;
                font-style: italic;
                margin-left: 10px;
                color: green;
                float: left;
                padding: 5px 10px;
                border: 1px solid green;
                -moz-border-radius: 5px 5px 5px 5px;
                -webkit-border-radius: 5px 5px 5px 5px;
                border-radius: 5px 5px 5px 5px;
                background-color: #ebeddf;
                -moz-box-shadow: 0px 2px 3px 2px #888;
                -webkit-box-shadow: 0px 2px 3px 2px #888;
                box-shadow: 0px 2px 3px 2px #888;
            }
        </style>
    </head>
    <body>
        <table class="topheader">
            <tr>
                <td colspan="3" style="text-align: right;">
                    <div class="logo"></div>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p>
            <h1>Open Keyos Install</h1>
        <p>&nbsp;</p>
        <div id="err_gen" name="err_gen"></div>
        <?php
//            echo "STEP: " . $step;
            if($step == 0){
                echo "Please make the directory: " . abs_path('install/generated') . " writable.";
            }
            elseif($step==1){
                generate_config_form($config_vars);
            }
            elseif($step==2){
                $db_file = abs_path("install/db_schema.sql");
                install_database($db_file, $existing_tables);
            }
            elseif($step==3){
                //echo "afaslkdjf haskdljf haklsdjhf kasdjh faksldfj haksdjfh ksdjf haksdfj hasdklfjh qewoqiwuerpo iweurqwer lsdfjsdfg blwefq fasdoi!";
                default_settings();
            }
            else{
                echo "STEP: " . $step;
                header('Location: ' . $config_vars['base_url']);
            }
        ?>
    </body>
</html>


