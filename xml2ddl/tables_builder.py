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
Provides all necessary classes (Table, TableBuilder) and theirs methods for
creating internal representation of SQL tables.
"""

# ========
# Imports:
# ========
import functools
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
        return (self._order == other._order)

    def __lt__(self, other):
        return (self._order < other._order)

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
        return (self._order == other._order)

    def __lt__(self, other):
        return (self._order < other._order)

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
        return (self._order == other._order)

    def __lt__(self, other):
        return (self._order < other._order)

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
        return (self._order == other._order)

    def __lt__(self, other):
        return (self._order < other._order)

    def __repr__(self):
        return 'NVARCHAR'


@functools.total_ordering
class NTEXT(metaclass=Singleton):
    """\
    Class representing NTEXT type of SQL. Use of logical comparison operators is
        Adds one column of given name and data type into the table. In case the
        given name would conflict with already existing foreign key, the
        NamesConflict exception is raised. If the column already exists this
        method raises a KeyError exception.
        
        If you want to change the data type without raising an exception, use
        the update() method.
    allowed. Apply str() or repr() functions to retrieve the textual
    representation of the type.
    """
    _order = 4

    def __eq__(self, other):
        return (self._order == other._order)

    def __lt__(self, other):
        return (self._order < other._order)

    def __repr__(self):
        return 'NTEXT'


class Table(object):
    """\
    Container class representing one SQL table. Requires table name upon
    instantiation and using str() function on the instance returns appropriate
    string representing the table in SQL syntax.
    """
    value_str = "value"
    value_type = None

    def __init__(self, table_name):
        self.name = table_name.lower()
        self.pkey = "PRK_%s_ID" % table_name.lower()
        self.attrs = dict()             # Table's declarations from attributes.
        self.fkeys = dict()             # Table's foreign keys.

    def set_value_type(self, data_type):
        if data_type is None:
            pass
        elif self.value_type is None or self.value_type < data_type:
            self.value_type = data_type

    def set_attr(self, attr_name, data_type):
        """\
        Sets the column of given attribute name to a higher data type, if the
        data type needs updating, or creates a new column for the given
        attribute name, if it doesn't exist yet.

        In case the attribute name is value, then the data type of .value_type
        is updated if needed.
        """
        name = attr_name.lower()

        if name in self.attrs:
            if self.attrs[name] < data_type:
                self.attrs[name] = data_type 
        elif name in self.fkeys or name == self.pkey.lower():
            raise NamesConflict
        elif name == self.value_str:
            if self.value_type is None or self.value_type < data_type:
                self.value_type = data_type
        else:
            self.attrs[name] = data_type

    def set_fkey(self, foreign_key, data_type=INT()):
        """\
        Sets the foreign key of given name to a new data type. (Default is
        'INT'.) If the foreign key of given name doesn't exist, it is created.
        """
        fkey = foreign_key.lower()

        if fkey in self.fkeys:
            if self.fkeys[fkey] != data_type:
                self.fkeys[fkey] = data_type
        elif fkey == self.pkey.lower() or fkey == self.value_str\
             or fkey in self.attrs:
            raise NamesConflict
        else:
            self.fkeys[fkey] = data_type

    def rename_attr(self, attr_name, attr_name_new):
        """\
        Renames declaration created for attribute of given name to new one.
        Raises a KeyError in case the given name declaration does not exist.
        """
        self.attrs[attr_name_new.lower()] = self.attrs.pop(attr_name.lower())

    def rename_fkey(self, fkey, fkey_new):
        """\
        Renames the foreign key of given name to new one. Raises a KeyError in
        case the foreign key of given name does not exist.
        """
        self.fkeys[fkey_new.lower()] = self.fkeys.pop(fkey.lower())

    def remove_attr(self, attr_name):
        """\
        Removes declaration created for attribute of given name. Raises a
        KeyError in case the given name declaration does not exist.
        """
        del(self.attrs[attr_name.lower()])

    def remove_fkey(self, fkey):
        """\
        Removes a foreign key of given name. Raises a KeyError in case the
        foreign key of given name does not exist.
        """
        del(self.fkeys[fkey.lower()])

    def reset_attrs(self):
        """\
        Removes all declarations created from attributes.
        """
        self.attrs.clear()

    def reset_fkeys(self):
        """\
        Removes all foreign keys.
        """
        self.fkeys.clear()

    def reset_value_type(self):
        """\
        Resets the type of 'value' column.
        """
        self.value_type = None

    def reset_all(self):
        """\
        Resets the table into state identical to when the table was
        instantiated.
        """
        self.attrs.clear()
        self.fkeys.clear()
        self.value_type = None

    def _rename_table(self, table_name_new):
        """\
        Allows renaming the table.
        """
        self.name = table_name_new
        self.pkey = "PRK_%s_ID" % table_name_new.lower()
        
    def __repr__(self):
        table_head = "CREATE TABLE %s(\n" % self.name
        table_pkey = "  {0} ".format(self.pkey).ljust(40) + "INT PRIMARY KEY,\n"

        if not self.value_type and not self.attrs and not self.fkeys:
            return table_head + table_pkey.rstrip(",\n") + "\n);\n"   
        
        # Value column:
        if self.value_type:
            table_body = "\n  {0} ".format(self.value_str).ljust(41)\
                         + "%s,\n" % str(self.value_type)
        else:
            table_body = "\n"

        table_tail = "\n);\n"
        
        # Attributes columns:
        for (attr_name, d_type) in sorted(self.attrs.items()):
            table_body += "  {0} ".format(attr_name).ljust(40)\
                          + "%s,\n" % str(d_type)
        
        if self.attrs and self.fkeys:
            table_body += "\n"
        
        # Foreign keys columns:
        for (fkey, fk_type) in sorted(self.fkeys.items()):
            table_body += "  {0} ".format(fkey).ljust(40)\
                          + "%s,\n" % str(fk_type)

        return table_head + table_pkey + table_body.rstrip(",\n") + table_tail 


class TablesBuilder(object):
    """\
    Wrapping class for storing all Table() instances of one "database". The
    created tables are stored inside an dictionary, which can be accessed via
    .tables attribute.
    """
    def __init__(self):
        self._tables = dict()

    def __iter__(self):
        return iter(self._tables)

    def __getitem__(self, item):
        return self._tables[item]
    
    def create_table(self, table_name):
        """\
        Creates table of given name. Raises a KeyError in case the table already
        exists or None type was supplied as a table_name.
        """
        if table_name is None or table_name in self._tables:
            raise KeyError
        else:
            self._tables[table_name] = Table(table_name)
            return self._tables[table_name]

    def get_table(self, table_name):
        """\
        This method returns the table of given name from dictionary or creates
        one, if it doesn't exists. In case None type was supplied as table_name,
        then raises a KeyError exception.
        """
        if table_name is not None and table_name not in self._tables:
            self._tables[table_name] = Table(table_name)
        return self._tables[table_name]

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

    def rename_table(self, table_name, table_name_new):
        """\
        Renames the existing table if it exists to a new name. Raises a KeyError
        if the wrong names were supplied.
        """
        self._tables[table_name]._rename_table(table_name_new)
        self._tables[table_name_new] = self._tables.pop(table_name)

    def remove_table(self, table_name):
        """\
        Removes table of given name completely. Raises a KeyError if the table
        doesn't exist.
        """
        del self._tables[table_name]

    def write(self, file, encoding='us-ascii', xml_declaration=None,
              method='xml'):
        """\
        Polymorphic method as ElementTree.write() method. Prints the content of
        tables in dictionary and separates them with empty line.
        """
        for (table_name, table) in sorted(self._tables.items()):
            file.write(str(table) + "\n")

# ===================
# Internal functions:
# ===================
def _main():
    """\
    Testing the combination of module defined class' methods and correctness of
    the output.
    """

    table = Table("table")
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

    if bit != bit or int != int or float != float or nvarchar != nvarchar\
       or ntext != ntext or nvarchar != ntext:
        print("Failed ordering!")

    bit2 = BIT()

    if bit > int > float > nvarchar > ntext or bit2 != bit2: 
        print("Failed ordering!")
    
    return 0

if __name__ == '__main__':
    import sys
    status = _main()
    sys.exit(status)

