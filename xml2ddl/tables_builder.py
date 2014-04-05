#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): tables_builder.py
# Version:       1.2.0.0
# Start date:    23-03-2014
# Last update:   31-03-2014
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
Provides all necessary classes (Table, TableBuilder) and theirs methods for+č+íříéřěř
creating internal representation of SQL tables.
"""

# ========
# Imports:
# ========
import functools
from collections import defaultdict

from cardinality import *
from singleton import Singleton

# ===========
# Exceptions:
# ===========
class NamesConflict(Exception):
    """\
    Exception for representing conflicting names of column names and names of 
    foreign keys.
    """
    pass

# ===============
# Public Classes:
# ===============
@functools.total_ordering
class BIT(metaclass=Singleton):
    """\
    Class representing BIT type of SQL. Use of logical comparison operators is
    allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 1

    def __eq__(self, other):
        return self._order == other._order

    def __lt__(self, other):
        return self._order < other._order

    def __repr__(self):
        return 'BIT'


@functools.total_ordering
class INT(metaclass=Singleton):
    """\
    Class representing INT type of SQL. Use of logical comparison operators is
    allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 2

    def __eq__(self, other):
        return self._order == other._order

    def __lt__(self, other):
        return self._order < other._order

    def __repr__(self):
        return 'INT'


@functools.total_ordering
class FLOAT(metaclass=Singleton):
    """\
    Class representing FLOAT type of SQL. Use of logical comparison operators is
    allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 3

    def __eq__(self, other):
        return self._order == other._order

    def __lt__(self, other):
        return self._order < other._order

    def __repr__(self):
        return 'FLOAT'


@functools.total_ordering
class NVARCHAR(metaclass=Singleton):
    """\
    Class representing NVARCHAR type of SQL. Use of logical comparison operators
    is allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 4

    def __eq__(self, other):
        return self._order == other._order

    def __lt__(self, other):
        return self._order < other._order

    def __repr__(self):
        return 'NVARCHAR'


@functools.total_ordering
class NTEXT(metaclass=Singleton):
    """\
    Class representing NTEXT type of SQL. Use of logical comparison operators is
    allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 4

    def __eq__(self, other):
        return self._order == other._order

    def __lt__(self, other):
        return self._order < other._order

    def __repr__(self):
        return 'NTEXT'


@functools.total_ordering
class Table(object):
    """\
    Container class representing one SQL table. Requires table name upon
    instantiation and using str() function on the instance returns appropriate
    string representing the table in SQL syntax.
    """
    _value_str = "value"
    _value_type = None
    
    # Allowed data types of cardinality:
    _cardinality = [
        None,
        Card_None(),
        Card_1to1(),
        Card_1toN(),
        Card_Nto1(),
        Card_NtoM(),
    ]

    def __init__(self, table_name, index):
        self.name = table_name.lower()
        self.index = index      # Table's index for the WFI algorithm.

        self.references = []    # Database references from table.
        self.relations = defaultdict(None) # Relations to other tables.

        self._pkey = "PRK_%s_ID" % table_name.lower()
        self._attrs = dict()    # Table's declarations from attributes.
        self._fkeys = dict()    # Table's foreign keys.

    def __hash__(self):
        """\
        The index value and table name is used for the hash combined
        with logical XOR. Index value should always be unique and the
        self.name should be changed only when the table is renamed. If
        that happens, the new instance of the same values, but different
        name is created and the old one is deleted. If properly used,
        there shouldn't arise any collision.
        """
        return hash(self.index) ^ hash(self.name)   # TODO: TEST ME!

    # Comparison for SQL tables equality (==) used by decorator:
    def __eq__(self, other):
        """\
        Comparison for SQL tables equality (==) used by class decorator.
        """
        return str(self) == str(other)

    
    def __lt__(self, other):
        """\
        Comparison for SQL tables membership (<) used by class
        decorator. It represents that current table has less
        declarations than the 'other' table.
        """
        # Equality is tested in method above; names must be same though:
        if str(self) == str(other) or self.name != other.name:
            return False
        
        # Value type testing:
        if self._value_type is not None:
            if (other._value_type is None or
                    self._value_type > other._value_type):
                return False
        
        # Attributes columns presence and type testing:
        for column in self._attrs:
            if (column not in other._attrs or
                    self._attrs[column] > other._attrs[column]):
                return False

        # Foreign keys presence and type testing:
        for fkey in self._fkeys:
            if (fkey not in other._fkeys or self._fkeys[fkey] >
                    other._fkeys[fkey]):
                return False

        return True    # This table is a sub-table of the 'other' table.

    def set_value_type(self, data_type):
        """\
        Sets current type of column 'value', if it has higher data type.
        """
        if data_type is None:
            pass
        elif self._value_type is None or self._value_type < data_type:
            self._value_type = data_type

    def set_attr(self, attr_name, data_type):
        """\
        Sets the column of given attribute name to a higher data type, if the
        data type needs updating, or creates a new column for the given
        attribute name, if it doesn't exist yet.

        In case the attribute name is value, then the data type of ._value_type
        is updated if needed.
        """
        name = attr_name.lower()

        if name in self._attrs:
            if self._attrs[name] < data_type:
                self._attrs[name] = data_type 
        elif name in self._fkeys or name == self._pkey.lower():
            raise NamesConflict
        elif name == self._value_str:
            if self._value_type is None or self._value_type < data_type:
                self._value_type = data_type
        else:
            self._attrs[name] = data_type

    def set_fkey(self, foreign_key, data_type=INT()):
        """\
        Sets the foreign key of given name to a new data type. (Default is
        'INT'.) If the foreign key of given name doesn't exist, it is created.
        """
        fkey = foreign_key.lower()

        if fkey in self._fkeys:
            if self._fkeys[fkey] != data_type:
                self._fkeys[fkey] = data_type
        elif (fkey == self._pkey.lower() or fkey == self._value_str or
                fkey in self._attrs):
            raise NamesConflict
        else:
            self._fkeys[fkey] = data_type

    def set_reference(self, table):
        """\
        Sets the reference from current to other table.
        """
        if table not in self.references:
            self.references.append(table)

    def set_relation(self, table, rel_card=None):
        """\
        Sets the relation from current to other table. Optional argument
        can be supplied to set the relation's cardinality.

        In case the optional argument has not valid cardinality, the
        TypeError exception is raised.
        """
        if rel_card in self._cardinality:
            self.relations[table] = rel_card
        else:
            raise TypeError

    def rename_attr(self, attr_name, attr_name_new):
        """\
        Renames declaration created for attribute of given name to new one.
        Raises a KeyError in case the given name declaration does not exist.
        """
        self._attrs[attr_name_new.lower()] = self._attrs.pop(attr_name.lower())

    def rename_fkey(self, fkey, fkey_new):
        """\
        Renames the foreign key of given name to new one. Raises a KeyError in
        case the foreign key of given name does not exist.
        """
        self._fkeys[fkey_new.lower()] = self._fkeys.pop(fkey.lower())

    def remove_attr(self, attr_name):
        """\
        Removes declaration created for attribute of given name. Raises a
        KeyError in case the given name declaration does not exist.
        """
        del(self._attrs[attr_name.lower()])

    def remove_fkey(self, fkey):
        """\
        Removes a foreign key of given name. Raises a KeyError in case the
        foreign key of given name does not exist.
        """
        del(self._fkeys[fkey.lower()])

    def reset_attrs(self):
        """\
        Removes all declarations created from attributes.
        """
        self._attrs.clear()

    def reset_fkeys(self):
        """\
        Removes all foreign keys.
        """
        self._fkeys.clear()

    def reset_value_type(self):
        """\
        Resets the type of 'value' column.
        """
        self._value_type = None

    def reset_all(self):
        """\
        Resets the table into state identical to when the table was
        instantiated.
        """
        self._attrs.clear()
        self._fkeys.clear()
        self._value_type = None

    def _rename_table(self, table_name_new):
        """\
        Allows renaming the table.
        """
        self.name = table_name_new
        self._pkey = "PRK_%s_ID" % table_name_new.lower()
    
    # str() can produce string of table's SQL representation:
    def __str__(self):
        table_head = "CREATE TABLE %s(\n" % self.name
        table_pkey = "  {0} ".format(self._pkey).ljust(40) + "INT PRIMARY KEY,\n"

        if not self._value_type and not self._attrs and not self._fkeys:
            return table_head + table_pkey.rstrip(",\n") + "\n);\n"   
        
        # Value column:
        if self._value_type:
            table_body = "\n  {0} ".format(self._value_str).ljust(41) \
                         + "%s,\n" % str(self._value_type)
        else:
            table_body = "\n"

        table_tail = "\n);\n"
        
        # Attributes columns:
        for (attr_name, d_type) in sorted(self._attrs.items()):
            table_body += "  {0} ".format(attr_name).ljust(40) \
                          + "%s,\n" % str(d_type)
        
        if self._attrs and self._fkeys:
            table_body += "\n"
        
        # Foreign keys columns:
        for (fkey, fk_type) in sorted(self._fkeys.items()):
            table_body += "  {0} ".format(fkey).ljust(40) \
                          + "%s,\n" % str(fk_type)

        return table_head + table_pkey + table_body.rstrip(",\n") + table_tail
    
    # For internal use only:
    def __repr__(self):
        return "TABLE: %s (index: %d)" % (self.name, self.index)


class TablesBuilder(object):
    """\
    Wrapping class for storing all Table() instances of one "database". The
    created tables are stored inside an dictionary, which can be accessed via
    .tables attribute.
    """
    _index_act = 0

    def __init__(self):
        self._tables = dict()
        self._index_mapping = defaultdict(None)
        self.xml_repr = None

    def __len__(self):
        return len(self._tables)

    def __contains__(self, item):
        return item in self._tables

    def __getitem__(self, item):
        return self._tables[item]

    def __iter__(self):
        return iter(self._tables)
    
    def __delitem__(self, table_name):
        # Removes table of given name and removes it from the
        # table->tables mapping.
        table_index = self._tables[table_name].index
        del self._index_mapping[table_index]
        del self._tables[table_name]

    # ====================
    # Polymorphic methods:
    # ====================
    def items(self):
        """\
        Wrapping method for returning content of tables dictionary.
        """
        return self._tables.items()

    def keys(self):
        """\
        Wrapping method for returning the dictionary keys.
        """
        return self._tables.keys()
    
    def values(self):
        """\
        Wrapping method for returning a view on the database's tables.
        """
        return self._tables.values()

    # =================
    # Standard methods:
    # =================
    def create_table(self, table_name):
        """\
        Creates table of given name. Raises a KeyError in case the table already
        exists or None type was supplied as a table_name.
        """
        if table_name is None or table_name in self._tables:
            raise KeyError
        else:
            table = Table(table_name, self._index_act)
            self._tables[table_name] = table
            self._index_mapping[self._index_act] = table
            self._index_act += 1
            return table

    def get_table(self, table_name):
        """\
        This method returns the table of given name from dictionary or creates
        one, if it doesn't exists. In case None type was supplied as table_name,
        then raises a KeyError exception.
        """
        if table_name is not None and table_name not in self._tables:
            self.create_table(table_name)
        return self._tables[table_name]

    def get_mapping(self):
        """Doc-string.""" # FIXME
        return self._index_mapping

    def rename_table(self, table_name, table_name_new):
        """\
        Renames the existing table if it exists to a new name. Raises a KeyError
        if the wrong names were supplied.
        """
        self._tables[table_name]._rename_table(table_name_new)
        self._tables[table_name_new] = self._tables.pop(table_name)

    def remove_table(self, table_name):
        """\
        Removes table of given name completely. Raises a KeyError if the
        table doesn't exist.

        WARNING: Be aware of removing tables, because some other tables'
        references or relations might be still referencing the table.
        """
        self.__delitem__(table_name)

    def __str__(self):
        """\
        str() will produce a string representing the SQL representation
        of the database.
        """
        result = ""
        for table in self._tables.values():
            result += str(table) + "\n"

        return result

# ===================
# Internal functions:
# ===================
def _main():
    """\
    Testing the combination of module defined class' methods and correctness of
    the output.
    """

    table = Table("table", 0)
    table.set_value_type(None)
    table.set_attr("value1", BIT())
    table.set_attr("value2", NVARCHAR())
    table.set_value_type(BIT())
    table.set_attr("value3", FLOAT())
    table.set_attr("value4", NTEXT())
    table.set_fkey("table2")
    table.set_fkey("table3")
    table.set_fkey("table4")

    sys.stdout.write(str(table) + "\n")

    table.set_value_type(FLOAT())
    table.rename_attr("value2", "new_value2")
    table.rename_attr("value3", "new_value3")
    table.rename_fkey("table2", "new_table2")
    table._rename_table("new_table")
    table.set_value_type(BIT())

    sys.stdout.write(str(table) + "\n")

    table.remove_attr("value4")
    table.remove_fkey("table4")

    sys.stdout.write(str(table) + "\n")

    table.reset_attrs()
    table.reset_fkeys()
    table.reset_value_type()

    sys.stdout.write(str(table) + "\n")

    database = TablesBuilder()
    database.create_table("database_table1")
    database.create_table("database_table2")
    database.create_table("database_table3")

    database.get_table("database_table2").set_attr("column_value", INT())
    database["database_table3"].set_attr("column_value", INT())

    for (table_name, table) in sorted(database.items()):
        sys.stdout.write(str(table) + "\n")

    database.rename_table("database_table3", "renamed_table")

    database.remove_table("database_table2")
    database.remove_table("database_table1")

    for (table_name, table) in database.items():
        sys.stdout.write(str(table) + "\n")
    sys.stdout.write(str(database._tables["renamed_table"]) + "\n")
    sys.stdout.write(str(database.get_table("renamed_table")) + "\n")
    sys.stdout.write(str(database.get_table("missing_table")) + "\n")

    print("------------------------\n")

    # Trying the same (polymorphic) call as for ElementTree.write() method:
    database.write(
        file=sys.stdout,
        encoding='utf-8',
        xml_declaration=False,
        method='xml',
    )

    print("------------------------\n")

    table_cmp1 = database["renamed_table"]
    table_cmp2 = database["missing_table"]
    table_cmp3 = Table("renamed_table", 1)

    table_cmp3.set_attr("column_value", FLOAT())
    table_cmp3.set_value_type(NTEXT())

    if table_cmp1 <= table_cmp2:
        print("different tables: YES!")
    else:
        print("different tables: NO!")

    if table_cmp1 <= table_cmp3:
        print("adequate tables: YES!\n")
    else:
        print("adequate tables: NO!\n")

    print("------------------------\n")
    
    bit = BIT()
    int = INT()
    float = FLOAT()
    nvarchar = NVARCHAR()
    ntext = NTEXT()

    print(str(bit))
    print(str(int))
    print(str(float))
    print(str(nvarchar))
    print(str(ntext))

    if bit < int < float < nvarchar == ntext:
        print("Successful ordering!")
    else:
        print("Failed ordering!")

    if (bit != bit or int != int or float != float or nvarchar != nvarchar or
            ntext != ntext or nvarchar != ntext):
        print("Failed ordering!")

    bit2 = BIT()

    if bit > int > float > nvarchar > ntext or bit2 != bit2: 
        print("Failed ordering!")
    
    return 0

if __name__ == '__main__':
    import sys
    status = _main()
    sys.exit(status)

