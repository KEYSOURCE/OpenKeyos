#!/usr/bin/python
__author__ = 'victor'
'''
   this app relies on the following python packages:
   - python-mysqldb
   - python-simplejson
'''
import ConfigParser
import os
from frl.GenerateOSKPlugin import GenerateOSKPlugin
from frl.model.GenerateOSKModel import GenerateOSKModel
from frl.pkg.GenerateOSKPackage import GenerateOSKPackage

if __name__ == "__main__":
    import sys
    conf_file = os.path.join(os.getcwd(), 'osk.ini')
    if not os.path.exists(conf_file):
        print("Missing configuration file!")
        sys.exit()

    config = ConfigParser.ConfigParser()
    config.read(os.path.join(os.getcwd(), "osk.ini"))

    plugins_folder = config.get('plugin', 'plugins_folder')
    plugins_reg = config.get('plugin', 'plugins_reg')
    if(len(sys.argv) < 1):
        print "please specify the operation to perform"
        exit(1)
    else:
        if (str(sys.argv[1]) == "--generate_plugin") or (str(sys.argv[1]) == "-gp") or (str(sys.argv[1]) == "-plugin"):
            if len(sys.argv) < 3:
                print "Not enough arguments - must specify the plugin key and the plugin name"
                exit(1)
            gp = GenerateOSKPlugin(str(sys.argv[2]), str(sys.argv[3]), plugins_folder, plugins_reg)
            gp.generate_plugin_structure()
        elif (str(sys.argv[1]) == "--generate_model") or (str(sys.argv[1]) == "-model") or (str(sys.argv[1]) == "-m"):
            if len(sys.argv) < 5:
                print "Not enough arguments - must specify the model table name"
                exit(1)
            db_username = config.get('db', 'username')
            db_password = config.get('db', 'password')
            db_database = config.get('db', 'database')
            db_host = config.get('db', 'host')

            mg = GenerateOSKModel(sys.argv[2], sys.argv[3], sys.argv[4], db_host, db_username, db_password, db_database, plugins_folder, plugins_reg)
            mg.create_model()

            #implement here the model generation
        elif (str(sys.argv[1]) == "--create-package") or (str(sys.argv[1]) == "-package" or (str(sys.argv[1]) == "-pkg")):
            if len(sys.argv) < 3:
                print "Not enough arguments - you should specify the plugin key"
                exit(1)

            gpkg = GenerateOSKPackage(str(sys.argv[2]), plugins_reg)
            gpkg.make_tarfile()

        else:
            print "Invalid option!!! Should print help here!"
            exit(1)

