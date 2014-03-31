__author__ = 'victor'

import os
import commands
import json
import tarfile

class GenerateOSKPackage:
    def __init__(self, plugin_key, plugin_reg):
        self.plugin_key = plugin_key
        self.plugin_reg = plugin_reg
        self.existing_plugins = []
        self.plugin_path = ""

    def _check_existing_plugin(self):
        cmd = "php %s %s" % (os.path.join(os.getcwd(), "get_plugin_reg.php"), self.plugin_reg)
        status, out = commands.getstatusoutput(cmd)
        if(status == 0):
            #we have a json - convert into python hash
            self.existing_plugins = json.loads(out)
            print self.existing_plugins.keys()
            print self.plugin_key
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


    def make_tarfile(self):
        if self._check_existing_plugin():
            package_file_path = os.path.join(os.getcwd(), '%s.tgz' % self.plugin_key)
            with tarfile.open(package_file_path, "w:gz") as tar:
                tar.add(self.plugin_path, arcname=os.path.basename(self.plugin_path))
