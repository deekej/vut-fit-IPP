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
from collections import defaultdict

from tables_builder import TablesBuilder
from tables_builder import BIT
from tables_builder import INT
from tables_builder import FLOAT
from tables_builder import NVARCHAR
from tables_builder import NTEXT

# ==================
# Exception Classes:
# ==================
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
    _BIT_list = ['0', '1', 'true', 'false']
    _parents_map = dict()
    _columns_count = defaultdict(int)
    _subelem_count = defaultdict(int)

    def __init__(self, settings, input_tree, valid_tree=None):
        self._etc = settings.etc
        self._ignore_attr = settings.a
        self._highest_elem_only = settings.b

        self._input_tree = input_tree
        self._valid_tree = valid_tree
        
        # Database initialization:
        self._dbase = TablesBuilder()

        if valid_tree is not None:
            self._dbase_valid = TablesBuilder()

    def get_result(self):
        """\
        Returns the result of previous run or None if the analyser has not been
        run yet.
        """
        if self._dbase:
            return self._dbase
        else:
            return None

    def run(self):
        """\
        Runs the XML analysis and database generator. If validation tree was
        supplied, then this method analysis it too and compares the databases.
        It raises an NotIdentical exception if they are not the same.
        """
        self._run(self._input_tree, self._dbase)

        if self._valid_tree is not None:
            self._run(self._valid_tree, self._dbase_valid)
            self._compare()

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

    def _classify_attr(self, string):
        """\
        Internal method for classifying the type of attribute.
        """
        if string is None:
            return None
        elif len(string) == 0 or string.isspace()\
             or string.lower() in self._BIT_list:
            return BIT()

        try:
            if float(string).is_integer():
                return INT()
            else:
                return FLOAT()
        except ValueError:
            return NVARCHAR()

    def _classify_cont(self, string):
        """\
        Internal method for classifying the content of an element.
        """
        if string is None or string.isspace() or len(string) == 0:
            return None
        elif string.lower() in self._BIT_list:
            return BIT()

        try:
            if float(string).is_integer():
                return INT()
            else:
                return FLOAT()
        except ValueError:
            return NTEXT()

    def _run(self, tree, dbase):
        """\
        Calls appropriate internal analysis method.
        """
        if self._highest_elem_only:
            self._run_b(tree, dbase)
        elif self._etc is None:
            self._run_default(tree, dbase)
        else:
            self._run_etc(tree, dbase)

    def _run_b(self, tree, dbase):
        tree_iter = tree.iter()     # Getting the XML tree iterator.
        next(tree_iter)             # Skipping the root element.
        
        # Parents mapping initialization:
        for elem in list(tree.getroot()):
            self._parents_map[elem] = None

        # Iterating over all XML elements except the root:
        for elem in tree_iter:
            self._columns_count.clear()         # Reseting columns counter.
            self._subelem_count.clear()         # Reseting children counter.
            table = dbase.get_table(elem.tag)   # Getting table.

            text_type = self._classify_cont(elem.text)
            tail_type = self._classify_cont(elem.tail)

            table.set_value_type(text_type)     # Setting actual value type.
           
            if not self._ignore_attr: 
                for (attr_name, attr_value) in elem.attrib.items():
                    attr_type = self._classify_attr(attr_value)
                    table.set_attr(attr_name, attr_type)

            # Adding parents mapping and references to children:
            for child in list(elem):
                self._parents_map[child] = elem
                table.set_fkey(child.tag + "_ID")

            # If the tail is not empty, then the text of parent wasn't parsed
            # before other sub-elements were. Updating the parent if necessary:
            if tail_type:
                parent = self._parents_map[elem]
                if parent is not None:
                    parent_table = dbase.get_table(parent.tag)
                    parent_table.set_value_type(tail_type) 

    def _run_default(self, tree, dbase):
        tree_iter = tree.iter()     # Getting the XML tree iterator.
        next(tree_iter)             # Skipping the root element.
        
        # Parents mapping initialization:
        for elem in list(tree.getroot()):
            self._parents_map[elem] = None

        # Iterating over all XML elements except the root:
        for elem in tree_iter:
            self._columns_count.clear()         # Reseting columns counter.
            self._subelem_count.clear()         # Reseting children counter.
            table = dbase.get_table(elem.tag)   # Getting table.

            text_type = self._classify_cont(elem.text)
            tail_type = self._classify_cont(elem.tail)

            table.set_value_type(text_type)     # Setting actual value type.
           
            if not self._ignore_attr:
                for (attr_name, attr_value) in elem.attrib.items():
                    attr_type = self._classify_attr(attr_value)
                    table.set_attr(attr_name, attr_type)

            # Adding parents mapping and counting occurrences of sub-elements:
            for child in list(elem):
                self._parents_map[child] = elem
                self._subelem_count[child.tag] += 1
            
            # Adding foreign keys:
            for (child_name, count) in self._subelem_count.items():
                if count == 1:
                    table.set_fkey(child_name + "_ID")
                else:
                    for i in range(1, count + 1):
                        table.set_fkey(child_name + str(i) + "_ID")

            # If the tail is not empty, then the text of parent wasn't parsed
            # before other sub-elements were. Updating the parent if necessary:
            if tail_type:
                parent = self._parents_map[elem]
                if parent is not None:
                    parent_table = dbase.get_table(parent.tag)
                    parent_table.set_value_type(tail_type)

    def _run_etc(self, tree, dbase):
        tree_iter = tree.iter()     # Getting the XML tree iterator.
        next(tree_iter)             # Skipping the root element.
        
        # Parents mapping initialization:
        for elem in list(tree.getroot()):
            self._parents_map[elem] = None

        # Iterating over all XML elements except the root:
        for elem in tree_iter:
            self._columns_count.clear()         # Reseting columns counter.
            self._subelem_count.clear()         # Reseting children counter.
            table = dbase.get_table(elem.tag)   # Getting table.

            text_type = self._classify_cont(elem.text)
            tail_type = self._classify_cont(elem.tail)

            table.set_value_type(text_type)     # Setting actual value type.
           
            if not self._ignore_attr: 
                for (attr_name, attr_value) in elem.attrib.items():
                    attr_type = self._classify_attr(attr_value)
                    table.set_attr(attr_name, attr_type)

            # Adding parents mapping and counting occurrences of sub-elements:
            for child in list(elem):
                self._parents_map[child] = elem
                self._subelem_count[child.tag] += 1
            
            # Adding foreign keys:
            for (child_name, count) in self._subelem_count.items():
                if count > self._etc:
                    child_table = dbase.get_table(child_name)
                    child_table.set_fkey(elem.tag + "_ID")
                else:
                    for i in range(1, count + 1):
                        table.set_fkey(child_name + str(i) + "_ID")

            # If the tail is not empty, then the text of parent wasn't parsed
            # before other sub-elements were. Updating the parent if necessary:
            if tail_type:
                parent = self._parents_map[elem]
                if parent is not None:
                    parent_table = dbase.get_table(parent.tag)
                    parent_table.set_value_type(tail_type) 

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
    except NamesConflict:
        sys.stderr.write("Conflicting names detected!\n")
        sys.exit(90)
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

