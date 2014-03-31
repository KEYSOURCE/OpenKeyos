__author__ = 'victor'

from frl.db.DBWrapper import DBWrapper
import re
import commands
import os
import json
from frl.template_ignore_invalid import TemplateIgnoreInvalid

class GenerateOSKModel:
    def __init__(self, plugin_key, table_name, model_name, db_host, db_user, db_pass, db_name, plugins_folder, plugins_reg):
        self.plugin_key = plugin_key
        self.table_name = table_name
        self.model_name = model_name
        self.db_user = db_user
        self.db_host = db_host
        self.db_user = db_user
        self.db_pass = db_pass
        self.db_name = db_name
        self.plugins_folder = plugins_folder
        self.plugins_reg = plugins_reg
        self.model_file_name = "%s.php" % self.convert_camel_case(self.model_name)
        self.dbw = DBWrapper(db_user, db_pass, db_host, db_name)


    def check_existent_plugin(self):
        cmd = "php %s %s" % (os.path.join(os.getcwd(), "get_plugin_reg.php"), self.plugins_reg)
        status, out = commands.getstatusoutput(cmd)
        if(status == 0):
            #we have a json - convert into python hash
            self.existing_plugins = json.loads(out)
            if self.plugin_key in self.existing_plugins.keys():
                preg = self.existing_plugins[self.plugin_key]

                self.plugin_path = preg['plugin_dir']
                if not os.path.exists(self.plugin_path):
                    print "This plugin doesn't exist"
                    return False
            else:
                print "This is not a valid plugin key"
                return False
        else:
            print "Command %s : \r\nfinished with status: %s" % (str(cmd), str(status))
            print "Command output: \r\n%s" % str(out)
            return False
        return True

    def create_model(self):
        if self.check_existent_plugin():
            models_dir = os.path.join(self.plugin_path, "model")
            if not os.path.exists(models_dir):
                os.makedirs(models_dir, 0755)
            if os.path.exists(models_dir):
                self.model_file_path = os.path.join(models_dir, self.model_file_name)
                #if not os.path.exists(self.model_file_path):
                model_contents = self.create_model_code()
                with open(self.model_file_path, "w") as mfile:
                    mfile.write(model_contents)
                    print "Generated model file: %s" % self.model_file_path
                init_file = os.path.join(self.plugin_path, "init.php")
                self.load_plugin_init_file()
                pinit = self._extract_php_array(self.plugin_init, init_file)
                pinit_content = '''<?php
$plugin_init = $pi;
'''
                tpl = TemplateIgnoreInvalid(pinit_content)
                pinit_content = tpl.substitute({'pi': pinit})

                with open(init_file, "w") as infl:
                    infl.write(pinit_content)


    def get_db_table_structure(self):
        q = "desc tickets";
        table_struct = self.dbw.fetch_array(q)
        fields = [x[0] for x in table_struct]
        if not 'id' in fields:
            print "The model table must contain the id field as primary key"
            return False
        return fields

    def get_all_table_names(self):
        q = "show tables"
        tnames = self.dbw.fetch_array(q)
        table_names = [x[0] for x in tnames]
        return tnames

    def convert_camel_case(self, name):
        s1 = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', name)
        return re.sub('([a-z0-9])([A-Z])', r'\1_\2', s1).lower()

    def check_existing_define(self, df, fpath):
        datafile = file(fpath)
        found = False
        for line in datafile:
            if df in line:
                found = True
                break
        return found

    def _extract_php_array(self, dct, pas=0):
        ret = " array(\n"
        if type(dct) is dict:
            for pp in dct.keys():
                if (type(dct[pp]) is dict) or (type(dct[pp]) is list):
                    ret += str("\t" * (pas+1)) + "'" + str(pp) + "' => " + self._extract_php_array(dct[pp], pas+1) + ", \n"
                else:
                    ret += str("\t" * (pas+1)) + "'" + str(pp) + "' => '" + str(dct[pp]) + "', \n"
        elif type(dct) is list:
            for ppx in dct:
                if (type(ppx) is dict) or (type(ppx) is list):
                    ret += str("\t" * (pas+1)) + self._extract_php_array(ppx, pas+1) + ", \n"
                else:
                    ret += str("\t" * (pas+1)) + "'" + str(ppx) + "', \n"

        ret += str(("\t" * pas) + ")")
        return ret

    def load_plugin_init_file(self):
        init_file = os.path.join(self.plugin_path, "init.php")
        cmd = "php %s %s" % (os.path.join(os.getcwd(), "get_plugin_init.php"), init_file)
        print "CMD: %s" % cmd
        status, out = commands.getstatusoutput(cmd)
        if(status == 0):
            self.plugin_init = json.loads(out)
            print(self.plugin_init)
            if "MODELS" in self.plugin_init.keys():
                if type(self.plugin_init['MODELS']) is list:
                    self.plugin_init['MODELS'] = {}
                self.plugin_init['MODELS'][self.model_name] = "__DIR__ . '/model/%s'" % self.model_file_name
            else:
                self.plugin_init['MODELS'] = {self.model_name : "__DIR__ . '/model/%s'" % self.model_file_name}
            return True
        return False


    def create_model_code(self):
        tname_define_name = "TBL_%s" % (self.convert_camel_case(self.table_name).upper())
        tname_define = "define('%s', '%s');\n" % (tname_define_name, self.table_name)
        local_db_conf_file_path = os.path.join(os.path.join(self.plugin_path, "config"), 'local_db.php')
        if not self.check_existing_define(tname_define, local_db_conf_file_path):
            with open(local_db_conf_file_path, "a") as ldb_file:
                ldb_file.write(tname_define)

        model_code ='''<?php

class $mod_name extends PluginModel{

'''
        tpl = TemplateIgnoreInvalid(model_code)
        model_code = tpl.substitute({'mod_name': self.model_name})
        model_code = model_code.replace("$&", "$")
        table_names = self.get_all_table_names()
        fields = self.get_db_table_structure()
        if fields:
            for fld in fields:
                model_code += "\tpublic $%s;\n" % fld

                if fld[:-3] == "_id":
                    #we might have a foreign key
                    oname = fld[:len(fld-3)]
                    if oname in table_names:
                        #we have a foreign key
                        model_code += "//%s object\n" % (oname)
                        model_code += "\tpublic $%s;\n" % (oname + "_obj")
            model_code += "\n\tpublic $fields=array('%s');\n" % "', '".join(fields)
        model_code += "\n\tpublic $table = %s;\n" % tname_define_name

        model_code_pp = '''

    /**
	* Constructor. Also loads a activity information if an ID is provided
	* @param	int	$&id		The id of the object to load
	*/
    public function __construct($&id = null){
        if($&id){
            $&this->id = $&id;
            $&this->load_data();
        }
    }

    public function load_data(){
        if($&this->id){
            parent::load_data();
            if($&this->id){
                //add your object initialization code here
            }
        }
    }

    /** Checks if the object data is valid */
	function is_valid_data ()
	{
		$&ret = true;
		//your validation code goes here
		//if (!$&this->name) {
		//    error_msg ('Please specify the object name.');
		//    $&ret = false;
		//}
		return $&ret;
	}

	/** Checks if the object can be deleted */
	function can_delete ()
	{
		$&ret = false;
		if ($&this->id)
		{
			$&ret = true;
			// Check if this object can be deleted - or it has other dependencies that have to be treated first
		}
		return $&ret;
	}

	/** Save the object in the database */
	function save_data ()
	{
		// prepare data to be saved - if there's the case
		parent::save_data ();
		if ($&this->id){
		    //now we have the object saved in the database
		    //if there's a need to do some additional operations for objects depending on this object's id
		    //you can do it here
		}
	}

    public static function get_all_items($&filter=array(), $&count=0){
        $&ret = array();
        $&query_norm = "SELECT id ";
        $&query_cnt = "SELECT count(id) as cnt ";
        $&query = " FROM " . $tdef_name;

        if(isset($&filter['order_by'])){
            $&query .= ' ORDER BY '.$&filter['order_by'].' ';
            $&query .= isset($&filter['order_dir']) ? $&filter['order_dir'] : 'desc';
        }
        if(isset($&filter['max_records'])){
            if(isset($&filter['start_record'])){
                $&query .= ' LIMIT ' . $&filter['start_record'] . ", " . $&filter['max_records'];
            } else {
                $&query .= ' LIMIT ' . $&filter['max_records'];
            }
        }

        $&count = Db::db_fetch_field($&query_cnt . $&query);

        $&ids = Db::db_fetch_vector($&query_norm . $&query);
        foreach($&ids as $&id){
            $&ret[] = new $class_name($&id);
        }
        return $&ret;
    }
}
'''
        tpl = TemplateIgnoreInvalid(model_code_pp)
        model_code_pp = tpl.substitute({'class_name': self.model_name, 'tdef_name' : tname_define_name})
        model_code_pp = model_code_pp.replace("$&", "$")

        return model_code + model_code_pp








