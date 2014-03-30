#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): tables_builder.py
# Version:       1.0.0.0
# Start date:    23-03-2014
# Last update:   25-03-2014
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

# ===============
# Public Classes:
# ===============

class Singleton(type):
    """\
    Singleton meta-class implementation for other SQL types defined in this
    module.
    """
    _instances = dict()

    def __call__(cls, *args, **kwargs):
        if cls not in cls._instances:
            cls._instances[cls] = super(Singleton, cls).__call__(*args, **kwargs)
        return cls._instances[cls]


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

    def __init__(self, table_name):
        self.name = table_name
        self.decls = dict()             # Table's declarations.
        self.fkeys = dict()             # Table's foreign keys.

    def add_column(self, column_name, data_type):
        """Adds one declaration (column) into the table."""
        self.decls[column_name] = data_type

    def add_foreign_key(self, foreign_key, data_type=INT()):
        """\
        Adds one foreign key into the table. (Default type of key is
        'INT'.)"""
        self.fkeys[foreign_key] = data_type

    def edit_column(self, column_name, column_name_new, data_type_new):
        """Allows changing the whole declaration (column)."""
        self.decls[column_name] = data_type_new
        self.decls[column_name_new] = self.decls.pop(column_name)

    def edit_column_name(self, column_name, column_name_new):
        """Allows renaming the declaration (column)."""
        self.decls[column_name_new] = self.decls.pop(column_name)

    def edit_column_type(self, column_name, data_type_new):
        """Allows changing the type of declaration (column)."""
        self.decls[column_name] = data_type_new
    
    def edit_foreign_key(self, foreign_key, foreign_key_new):
        """Allows renaming the foreign key."""
        self.fkeys[foreign_key_new] = self.fkeys.pop(foreign_key)
    
    def remove_column(self, column_name):
        """Remove declaration (column) of given name."""
        del(self.decls[column_name])

    def remove_foreign_key(self, foreign_key):
        """Removes foreign key of given name."""
        del(self.fkeys[foreign_key])

    def remove_all_columns(self):
        """Removes all declarations (columns)."""
        self.decls.clear()

    def remove_all_foreign_keys(self):
        """Removes all foreign keys."""
        self.fkeys.clear()

    def reset(self):
        """Removes all declarations (columns) and foreign keys."""
        self.decls.clear()
        self.fkeys.clear()

    def _edit_table_name(self, table_name_new):
        """Allows renaming the table."""
        self.name = table_name_new
        
    def __repr__(self):
        table_head = "CREATE TABLE %s(\n" % self.name
        table_pkey = "  PRK_{0}_ID".format(self.name).ljust(30)\
                     + "INT PRIMARY KEY,\n"

        if not self.decls and not self.fkeys:
            return table_head + table_pkey + ");\n"   

        table_body = "\n"
        table_tail = "\n);\n"

        for (col_name, d_type) in sorted(self.decls.items()):
            table_body += "  {0}".format(col_name).ljust(30)\
                          + "%s,\n" % str(d_type)
        
        if self.decls and self.fkeys:
            table_body += "\n"

        for (fkey, fk_type) in sorted(self.fkeys.items()):
            table_body += "  {0}".format(fkey).ljust(30)\
                          + "%s,\n" % str(fk_type)

        return table_head + table_pkey + table_body.rstrip(",\n") + table_tail 


class TablesBuilder(object):
    """\
    Wrapping class for storing all Table() instances of one "database". The
    created tables are stored inside an dictionary, which can be accessed via
    .tables attribute.
    """
    def __init__(self):
        self.tables = dict()
    
    def create_table(self, table_name):
        """\
        Creates table of given name. Raises a KeyError in case the table already
        exists.
        """
        if table_name in self.tables:
            raise KeyError
        else:
            self.tables[table_name] = Table(table_name)
            return self.tables[table_name]

    def get_table(self, table_name):
        """\
        This method acts in same way as get() method of dictionary - returns the
        table of given name from dictionary or creates one, if it doesn't
        exists.
        """
        if table_name not in self.tables:
            self.tables[table_name] = Table(table_name)
        return self.tables[table_name]

    def items(self):
        """\
        Wrapping method for returning content of items() method of dictionary.
        """
        return self.tables.items()

    def rename_table(self, table_name, table_name_new):
        """\
        Renames the given table to new name and the key in dictionary also.
        """
        self.tables[table_name]._edit_table_name(table_name_new)
        self.tables[table_name_new] = self.tables.pop(table_name)

    def remove_table(self, table_name):
        """Removes table of given name completely."""
        del self.tables[table_name]

    def write(self, file, encoding='us-ascii', xml_declaration=None,
              method='xml'):
        """\
        Polymorphic method as ElementTree.write() method. Prints the content of
        tables in dictionary and separates them with empty line.
        """
        for (table_name, table) in self.tables.items():
            file.write(str(table) + "\n")


# ===================
# Internal functions:
# ===================
def _main():
    """Simple module-testing function."""

    table = Table("table")
    table.add_column("value", BIT())
    table.add_column("value2", NVARCHAR())
    table.add_column("value3", FLOAT())
    table.add_column("value4", NTEXT())
    table.add_foreign_key("table2")
    table.add_foreign_key("table3")
    table.add_foreign_key("table4")

    sys.stdout.write(str(table) + "\n")

    table.edit_column("value", "new_value", "NEW_BIT")
    table.edit_column_name("value2", "new_value2")
    table.edit_column_type("value3", "NEW_NVCHAR")
    table.edit_foreign_key("table2", "new_table2")
    table._edit_table_name("new_table")

    sys.stdout.write(str(table) + "\n")

    table.remove_column("value4")
    table.remove_foreign_key("table4")

    sys.stdout.write(str(table) + "\n")

    table.remove_all_columns()
    table.remove_all_foreign_keys()

    sys.stdout.write(str(table) + "\n")

    database = TablesBuilder()
    database.create_table("database_table1")
    database.create_table("database_table2")
    database.create_table("database_table3")

    database.get_table("database_table3").add_column("column_value", "INT")

    for (table_name, table) in database.items():
        sys.stdout.write(str(table) + "\n")

    database.rename_table("database_table3", "renamed_table")

    database.remove_table("database_table2")
    database.remove_table("database_table1")

    for (table_name, table) in database.items():
        sys.stdout.write(str(table) + "\n")
    sys.stdout.write(str(database.tables["renamed_table"]) + "\n")
    sys.stdout.write(str(database.get_table("renamed_table")) + "\n")
    sys.stdout.write(str(database.get_table("missing_table")) + "\n")

    print("\n------------------------\n")

    # Trying the same (polymorphic) call as for ElementTree.write() method:
    database.write(
        file=sys.stdout,
        encoding='utf-8',
        xml_declaration=False,
        method='xml',
    )
    
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

