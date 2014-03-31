__author__ = 'victor'

import MySQLdb as mdb
import sys

class DBWrapper:
    def __init__(self, username, password, host, database):
        self.username = username
        self.password = password
        self.host = host
        self.database = database
        self.connection = None

    def _connect(self):
        if not self.connection:
            try:
                self.connection = mdb.connect(self.host, self.username, self.password, self.database);
            except mdb.Error, e:
                print "Error %d: %s" % (e.args[0], e.args[1])
                sys.exit(1)

    def disconnect(self):
        if self.connection:
            self.connection.close()

    def fetch_field(self, query):
        try:
            if not self.connection:
                self._connect()
            cur = self.connection.cursor()
            cur.execute(query);
            row = cur.fetchone()
            if row:
                return row[0]
        except mdb.MySQLError, e:
            print "Error %d: %s" % (e.args[0], e.args[1])


    def fetch_array(self, query):
        try:
            if not self.connection:
                self._connect()
            cur = self.connection.cursor()
            cur.execute(query)
            rowset = cur.fetchall()
            if rowset:
                return rowset
        except mdb.MySQLError, e:
            print "Error %d: %s" % (e.args[0], e.args[1])