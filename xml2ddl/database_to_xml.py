#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ==================================================================== #
#
# File (module): database_to_xml.py
# Version:       1.0.0.0
# Start date:    04-03-2014
# Last update:   05-04-2014
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
# More info @:
#       https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# ==================================================================== #

# ==================
# Module Doc-string:
# ==================
"""\
This module implements all necessary procedures to find reflexive,
symmetric and transitive closures with given relation database and to
find the cardinalities of the relations. It modifies the given instance
of TablesBuilder class database.
"""

__version__ = '1.0'
__all__ = ['BaseToXML']

# ========
# Imports:
# ========
import collections
import xml.etree.ElementTree as XML

from cardinality import *

# ===============
# Public Classes:
# ===============
class DBaseToXML(object):
    """\
    This class implements many internal auxiliary methods for database
    processing. It expects an instance of TablesBuilder class upon
    creation and provides one method - run() for processing the database
    in place.

    It uses Floyd-Warshall's algorithm (WFI) for finding transitive
    closure of the database and the shortest path in database graph.
    """
    def __init__(self, database):
        self._dbase = database

        self._mapping = database.get_mapping()
        self._matr_size = len(database)
        self._dist = self._square_matrix(self._matr_size, float('inf'))
        self._next = self._dict2D()
        self._rel_sorted = [[]]
        self._max_dist = 0

    def _dict2D(self):
        # Small hack for easy 2 dimensional dictionary.
        return collections.defaultdict(self._dict2D)

    def _square_matrix(self, size, init_value = 0):
        """
        Creates square matrix as 2 dimensional dictionary. It is
        initialized with given value or default value of zero.
        """
        matrix = self._dict2D()
        for i in range(0, size):
            for j in range(0, size):
                matrix[i][j] = init_value
        return matrix
    
    def _reflexive_closure(self):
        """\
        Creates a reflexive closure of the database and sets the
        cardinality of the relations to 1:1.
        """
        for table in self._dbase.values():
            table.set_relation(table)

    def _symmetric_closure(self):
        """\
        Creates a symmetric closure of the database.
        """
        for table in self._dbase.values():
            for dest_table in table.relations:
                dest_table.set_relation(table)

    def _transitive_closure(self):
        """\
        Creates a symmetric closure of database based on the result of
        previous Floyd-Warshall's algorithm run.
        """
        for i in range(0, self._matr_size):
            table = self._mapping[i]
            for j in range(0, self._matr_size):
                dist = self._dist[i][j]
                if dist > 1 and dist != float('inf'):
                    table.set_relation(self._mapping[j])
                # We're finding the max distance also for later use:
                if dist != float('inf') and dist > self._max_dist:
                    self._max_dist = dist

    def _dist_init(self):
        """\
        Initializes the matrix of distances with zeros for the tables
        themselves and ones for the adjacency tables.
        """
        for i in range(0, self._matr_size):
            self._dist[i][i] = 0
        for table in self._dbase.values():
            for dest_table in table.relations:
                self._dist[table.index][dest_table.index] = 1

    def _next_init(self):
        """\
        Initializes the matrix of next step (used for path retrieving)
        of Floyd-Warshall's algorithm.
        """
        for i in range(0, self._matr_size):
            for j in range(0, self._matr_size):
                if i == j or self._dist[i][j] == float('inf'):
                    self._next[i][j] = 0
                else:
                    self._next[i][j] = i

    def _WFI_execute(self):
        """\
        Runs the Floyd-Warshall's algorithm.
        """
        for k in range(0, self._matr_size):
            for i in range(0, self._matr_size):
                for j in range(0, self._matr_size):
                    if self._dist[i][k] + self._dist[k][j] < self._dist[i][j]:
                        self._dist[i][j] = self._dist[i][k] + self._dist[k][j]
                        self._next[i][j] = self._next[k][j]

    def _sort_relations(self):
        """\
        Sorts the relations into groups, so the cardinality can be added
        incrementally later.
        """
        self._rel_sorted = [[] for i in range(0, self._max_dist + 1)]
        for i in range(0, self._matr_size):
            for j in range(0, self._matr_size):
                dist = self._dist[i][j]
                if dist != float('inf'):
                    table_from = self._mapping[i]
                    table_to = self._mapping[j]
                    self._rel_sorted[dist].append((table_from, table_to))

    def _path(self, ix_A, ix_B):
        """\
        Returns the shortest path from table A to table B. It expects
        tables' indexes as an input.
        """
        # Safety feature, even though our tweaked algorithm should not
        # end up trying to get path of two unconnected tables.
        if self._dist[ix_A][ix_B] == float('inf'):
            return None

        step = self._next[ix_A][ix_B]
        if step == ix_A:
            return []
        else:
            return self._path(ix_A, step) + [step] + self._path(step, ix_B)

    def _penultimate_table(self, table_A, table_B):
        """\
        Returns the penultimate (second to last) table in the path
        between tables A and B.
        """
        path = self._path(table_A.index, table_B.index)
        return self._mapping[path.pop()]

    def _set_cardinality(self, table_A, table_B, table_C=None):
        """\
        Automatically sets the cardinality of relation for given tables.
        Two or three tables can be supplied. In case the third table is
        supplied, it is taken as an penultimate (second to last) step
        before reaching the table B.
        """
        if table_C == None:
            # We're using initial references to add the initial
            # cardinality (of reflexive or symmetric closure relations):
            if table_A == table_B:
                table_A.set_relation(table_A, Card_1to1())
            elif (table_B in table_A.references and 
                    table_A in table_B.references):
                table_A.set_relation(table_B, Card_NtoM())
            elif table_B in table_A.references:
                table_A.set_relation(table_B, Card_Nto1())
            else:
                table_A.set_relation(table_B, Card_1toN())
        else:
            # We're using previously generated cardinalities to derive
            # new ones:
            AtoC_card = table_A.relations[table_C]
            CtoB_card = table_C.relations[table_B]

            if AtoC_card != CtoB_card:
                table_A.set_relation(table_B, Card_NtoM())
            elif AtoC_card == Card_Nto1():
                table_A.set_relation(table_B, Card_Nto1())
            else:
                table_A.set_relation(table_B, Card_1toN())

    def _add_cardinality(self):
        """\
        Adds cardinality to symmetric and transitive relations.
        """
        # Reflexive closure:
        for (table_A, table_B) in self._rel_sorted[0]:
            self._set_cardinality(table_A, table_B)

        # Symmetric closure relations:
        for (table_A, table_B) in self._rel_sorted[1]:
            self._set_cardinality(table_A, table_B)
        
        # Transitive closure relations:
        for i in range(2, self._max_dist + 1):
            for (table_A, table_B) in self._rel_sorted[i]:
                table_C = self._penultimate_table(table_A, table_B)
                self._set_cardinality(table_A, table_B, table_C)

    def _build_xml_tree(self):
        """\
        Builds an XML tree representation of the generated database
        relations and its cardinalities.
        """
        # Dictionaries for element attributes:
        table_attrs = dict()
        rel_attrs = dict()

        root_elem = XML.Element("tables")
        root_elem.text = "\n    "
        root_elem.tail = "\n"

        for table in sorted(self._dbase.values()):
            table_attrs["name"] = table.name
            table_elem = XML.SubElement(root_elem, "table", table_attrs)
            table_elem.text = "\n        "
            table_elem.tail = "\n    "

            for (relation_to, card) in table.relations.items():
                rel_attrs["to"] = relation_to.name
                rel_attrs["relation_type"] = str(card)
                rel_elem = XML.SubElement(table_elem, "relation", rel_attrs)
                rel_elem.tail = "\n        "
            rel_elem.tail = "\n    "

        table_elem.tail = "\n"
        self._dbase.xml_repr = XML.ElementTree(root_elem)

    def run(self):
        """
        Runs the SQL to XML conversion.
        """
        self._symmetric_closure()
        self._dist_init()
        self._next_init()
        self._WFI_execute()
        self._transitive_closure()
        self._reflexive_closure()
        self._sort_relations()
        self._add_cardinality()
        self._build_xml_tree()

# ===================
# Internal functions:
# ===================
def _main():
    """\
    The behavior of the DBaseToXML is tested directly within xtd.py main
    module.
    """
    pass

if __name__ == '__main__':
    import sys
    status = _main()
    sys.exit(status)

