#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): analyzer.py
# Version:       0.0.0.1
# Start date:    29-03-2014
# Last update:   30-03-2014
#
# Course:        IPP (summer semester, 2014)
# Project:       Script for converting XML format into DDL (SQL) format,
#                written in Python 3.2.3.
#
# Author:        David Kaspar (aka Dee'Kej), 3BIT
# 
# Faculty:       Faculty of Information Technologies,
#                Brno University of Technologies,
#                Czech Republic
#
# E-mail:        xkaspa34@stud.fit.vutbr.cz
#
# Description:   See the module doc-string.
#
# More info @:   https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# ============================================================================= #

# ==================
# Module doc-string:
# ==================
"""\
"""

# ========
# Imports:
# ========
from tables_builder import TablesBuilder

# ==================
# Exception Classes:
# ==================
class InvalidXML(Exception):
    """\
    An error exception raised when unexpected content was find behind the XML
    element. There's no specification how to handle this yet.
    """
    pass

class ValidationFail(Exception):
    """\
    An error exception when the generated database structure from given
    validation file is not same as the database structure from input file.
    """
    pass

# ===============
# Public Classes:
# ===============
class XMLAnalyser(object):
    """\
    Doc-string.
    """
    _int_type = ['0', '1', 'true', 'false']

    def __init__(self, settings, input_tree, valid_tree=None):
        self._etc = settings.etc
        self._ignore_attr = settings.a
        self._highest_elem_only = settings.b

        self._input_tree = input_tree
        self._valid_tree = valid_tree

        self._dbase = TablesBuilder()

        if valid_tree is not None:
            self._dbase_valid = TablesBuilder()

    def run(self):
        self._run(self._input_tree, self._dbase)

        if self._valid_tree is not None:
            self._run(self._valid_tree, self._dbase_valid)
            self._compare()

        return self._dbase

    def get_result(self):
        return self._dbase

    def _compare(self):
        """\
        Compares the input tree generated database with the database generated
        from the validation tree and raises an exception if they're not same.
        """
        if self._dbase.keys() != self._dbase_valid.keys():
            raise NotIdentical

        for key in self._dbase:
            if str(self._dbase[key]) != str(self._dbase_valid[key]):
                raise NotIdentical

    def _classify_attr(self, str):
        if len(str) == 0 or str.isspace() or str.lower() in self._int_type:
            return 'BIT'
        elif str.isnumeric():
            try:
                int(str)
                return 'INT'
            except ValueError:
                return 'FLOAT'
        else:
            return 'NVARCHAR'

    def _classify_text(self, string):
        if len(str) == 0 or str.isspace() or str.lower() in self._int_type:
            return 'BIT'
        elif str.isnumeric():
            try:
                int(str)
                return 'INT'
            except ValueError:
                return 'FLOAT'
        else:
            return 'NTEXT'
        
    def _run(self, tree, dbase):
        tree_iter = tree.iter()
        next(tree_iter)
        for element in tree_iter:
            pprint(element)
            pprint(vars(element))


# ===================
# Internal functions:
# ===================
def _main():
    settings = Parameters().process()
    interface = InputOutput(settings)
    input_tree, valid_tree = interface.read_trees()
    analyser = XMLAnalyser(settings, input_tree, valid_tree)
    
    try:
        result = analyser.run()
    except InvalidXML:
        sys.stderr.write("InvalidXML exception catched!\n")
        sys.exit(4)
    except ValidationFail:
        sys.stderr.write("ValidationFail exception catched!\n")
        sys.exit(91)

    interface.write(result)
    return 0

if __name__ == '__main__':
    import sys
    from pprint import pprint
    from parameters import Parameters
    from input_output import InputOutput
    from errors import EXIT_CODES
    status = _main()
    sys.exit(status)

