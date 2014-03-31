__author__ = 'victor'

import os
import os.path
import json
import commands
from template_ignore_invalid import TemplateIgnoreInvalid
import re

class GenerateOSKPlugin:
    '''
    plugin_key is the refference for this plugin
    plugin_name should be the same as the folder of the plugin
    '''
    def __init__(self, plugin_key, plugin_name, plugins_folder, plugins_reg):
        self.plugin_key = plugin_key
        self.plugin_name = plugin_name.replace(" ", "_")
        self.plugins_folder = plugins_folder
        self.plugins_reg = plugins_reg
        self.existing_plugins = {}

    '''
    Make sure that this plugin doesn't exist - if it does it cannot be created
    '''
    def check_existent_plugin(self):
        if os.path.exists(os.path.join(self.plugins_folder, self.plugin_name)):
            print "Another plugin with this name already exists!"
            return True

        #load registered plugins
        cmd = "php %s %s" % (os.path.join(os.getcwd(), "get_plugin_reg.php"), self.plugins_reg)
        status, out = commands.getstatusoutput(cmd)
        if(status == 0):
            #we have a json - convert into python hash
            self.existing_plugins = json.loads(out)
            if self.plugin_key in self.existing_plugins.keys():
                print "A plugin with this key already exists: %s" % self.plugin_key
                print self.existing_plugins[self.plugin_key]
            else:
                #print self.existing_plugins
                print "Module can be generated"
                return False
        else:
            print "Command %s : \r\nfinished with status: %s" % (str(cmd), str(status))
            print "Command output: \r\n%s" % str(out)
            return True
        return False


    def generate_plugin_structure(self):
        if not self.check_existent_plugin():
            self.plugin_path = os.path.join(self.plugins_folder, self.plugin_name)
            r, e = os.path.splitext(self.plugins_reg)
            self.existing_plugins[self.plugin_key] = {'plugin_name': self.plugin_name, 'plugin_dir': self.plugin_path, 'plugin_desc': '', 'plugin_version': '1.0', 'plugin_creator': '', 'plugin_creator_URI': ''}
            self.generate_php_array_from_hash(self.existing_plugins, r+"_modif"+e)
            os.makedirs(self.plugin_path, 0755)
            if os.path.exists(self.plugin_path):
                #create the config folder
                os.makedirs(os.path.join(self.plugin_path, "controller"), 0755)
                os.makedirs(os.path.join(self.plugin_path, "views"), 0755)
                os.makedirs(os.path.join(self.plugin_path, "model"), 0755)
                os.makedirs(os.path.join(self.plugin_path, "config"), 0755)
                os.makedirs(os.path.join(self.plugin_path, "strings"), 0755)
                self.generate_controller()
                self.generate_plugin_init()
                self.generate_module_config()



    def generate_plugin_init(self):
        controller_folder_path = os.path.join(self.plugin_path, "controller")
        controller_file_path = os.path.join(controller_folder_path, ("%s_controller.php" % self.convert_camel_case(self.plugin_name.replace("_", ""))).lower())
        init_file_path = os.path.join(self.plugin_path, 'init.php')
        init_contents = "<?php\n"
        init_contents += "$plugin_init = array(\n"
        init_contents += "\t'MODELS' => array(\n\t),\n"
        init_contents += "\t'CONTROLLERS' => array(\n"
        init_contents += "\t\t'" + self.plugin_key + "' => array(\n"
        init_contents += "\t\t\t'class' => '" + self.plugin_name.replace("_", "") + "Controller', \n"
        init_contents += "\t\t\t'friendly_name' => '" + self.plugin_name.replace("_", "") + "', \n"
        init_contents += "\t\t\t'file' => '" + controller_file_path + "', \n"
        init_contents += "\t\t\t'default_method' => 'index', \n"
        init_contents += "\t\t\t'requries_acl' => False, \n"
        init_contents += "\t\t), \n"
        init_contents += "\t),\n"
        init_contents += "\t'VIEWS' => __DIR__.'/views',\n"
        init_contents += "\t'STRINGS' => array(\n\t),\n"
        init_contents += "\t'IS_MAIN_MODULE' => FALSE,\n"
        init_contents += "\t'MAIN_MENU_MODULE' => array(\n"
        init_contents += "\t\t'name' => '" + self.plugin_key + "',\n"
        init_contents += "\t\t'display_name' => '" + self.plugin_name + "',\n"
        init_contents += "\t\t'uri' => '/" + self.plugin_key + "',\n"
        init_contents += "\t),\n"
        init_contents += "\t'MENU' => array(\n\t),\n"
        init_contents += ");\n"
        init_contents += "return $plugin_init;"
        with open(init_file_path, "w") as init_file:
            init_file.write(init_contents)



    def generate_php_array_from_hash(self, plugins_dict, reg_file_path):
        pp  = "<?php\n\n"
        pp += "$GLOBALS['PLUGINS'] = "
        pp += self._extract_php_array(plugins_dict)
        pp += ";"

        with open(reg_file_path, "w") as rg_file:
            rg_file.write(pp)


    def _extract_php_array(self, plugins_dict, pas=0):
        ret = " array(\n"
        if type(plugins_dict) is dict:
            for pp in plugins_dict.keys():
                if (type(plugins_dict[pp]) is dict) or (type(plugins_dict[pp]) is list):
                    ret += ("\t" * (pas+1)) + "'" + pp + "' => " + self._extract_php_array(plugins_dict[pp], pas+1) + ", \n"
                else:
                    ret += ("\t" * (pas+1)) + "'" + pp + "' => '" + str(plugins_dict[pp]) + "', \n"
        elif type(plugins_dict) is list:
            for ppx in plugins_dict:
                if (type(ppx) is dict) or (type(ppx) is list):
                    ret += ("\t" * (pas+1)) + self._extract_php_array(ppx, pas+1) + ", \n"
                else:
                    ret += ("\t" * (pas+1)) + "'" + str(ppx) + "', \n"

        ret += ("\t" * pas) + ")"
        return ret

    def convert_camel_case(self, name):
        s1 = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', name)
        return re.sub('([a-z0-9])([A-Z])', r'\1_\2', s1).lower()

    def generate_controller(self):
        controller_mk = '''
<?php

class $controller_class extends PluginController{
    protected $&plugin_name = '$pname';

    function __construct(){
        parent::__construct();
    }

    public function index(){
        $&tpl = 'index.tpl';
        //the body of the function here
        $&items = array();

        $&this->assign('items', $&items);
        $&this->assign('error_msg', error_msg());
        $&this->set_form_redir('index_submit');
        $&this->display($&tpl);
    }

    public function list_all_submit(){
        //process data here
        return $&this->mk_redir('index');
    }
}
'''
        tpl = TemplateIgnoreInvalid(controller_mk)
        controller_mk = tpl.substitute({'controller_class': self.plugin_name.replace("_", "")+"Controller", 'pname': self.plugin_name})
        controller_mk = controller_mk.replace("$&", "$")
        controller_folder_path = os.path.join(self.plugin_path, "controller")
        if os.path.exists(controller_folder_path):
            controller_file_path = os.path.join(controller_folder_path, ("%s_controller.php" % self.convert_camel_case(self.plugin_name.replace("_", ""))).lower())
            if not os.path.exists(controller_file_path):
                with open(controller_file_path, "w") as cf_file:
                    cf_file.write(controller_mk)
        views_folder_path =  os.path.join(self.plugin_path, "views")
        if os.path.exists(views_folder_path):
            index_view = os.path.join(views_folder_path, "index.tpl")
            with open(index_view, "w") as idx_file:
                index_content = '''
{assign var="paging_titles" value="$pname"}
{include file="paging.html"}

{literal}
    <script type='text/javascript'>
    //<![CDATA[
    $&(document).ready(function(){
       //your ready code here
    });
    //]]>
    </script>
{/literal}

<h1>$pname index action title</h1>
<p>
<font class="error">{$&error_msg}</font>
<p>
<form action="" method="POST" name="index_frm">
{$&form_redir}
Your custom code goes here
</form>
'''
                idx_tpl = TemplateIgnoreInvalid(index_content)
                index_content = idx_tpl.substitute({'pname': self.plugin_name})
                index_content = index_content.replace("$&", "$")
                idx_file.write(index_content)


    def generate_module_config(self):
        module_config_cont = '''
<?php

$&router = array(
    'routes' => array(
        '$plugin_key' => array(
            'route' => '/$plugin_key[/]*:op[/]*:id[/]*',
            'target' => array('cl' => '$plugin_key'),
            'methods' => array('GET', 'POST'),
            'filters' => array(
                    'op' => '([a-zA-Z][a-zA-Z0-9_-]*)',
                    'id' => '([0-9]*)',
            ),
        ),
    ),
);
'''
        tpl = TemplateIgnoreInvalid(module_config_cont)
        module_config_cont = tpl.substitute({'plugin_key': self.plugin_key})
        module_config_cont = module_config_cont.replace("$&", "$")

        config_folder_path = os.path.join(self.plugin_path, "config")
        if not os.path.exists(config_folder_path):
            os.makedirs(config_folder_path, 0755)
        if os.path.exists(config_folder_path):
            module_config_file_path = os.path.join(config_folder_path, 'module.config.php')
            with open(module_config_file_path, 'w') as mc_file:
                mc_file.write(module_config_cont)
            local_db_path = os.path.join(config_folder_path, "local_db.php")
            local_const_path = os.path.join(config_folder_path, "local.php")

            with open(local_db_path, "w") as ldb_file:
                ldb_file.write("<?php\n\n//write the database related constants here")

            with open(local_const_path, "w") as lconst_file:
                lconst_file.write("<?php\n\n//moduel constants go here")



