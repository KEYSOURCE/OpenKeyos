__author__ = 'victor'
from string import Template
import re

class TemplateIgnoreInvalid(Template):
    # override pattern to make sure `invalid` never matches
    pattern = r"""
    %(delim)s(?:
      (?P<escaped>%(delim)s) |   # Escape sequence of two delimiters
      (?P<named>%(id)s)      |   # delimiter and a Python identifier
      {(?P<braced>%(id)s)}   |   # delimiter and a braced identifier
      (?P<invalid>^$)            # never matches (the regex is not multilined)
    )
    """ % dict(delim=re.escape(Template.delimiter), id=Template.idpattern)
