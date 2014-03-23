#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): params.py
# Version:       1.0.0.1
# Start date:    19-03-2014
# Last update:   22-03-2014
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
Provides the class for processing the script parameters and storing them. The
processed parameters are returned by Parameters.process() method and can be
accessed independently via Parameters.result public attribute.
"""

# ========
# Imports:
# ========
import argparse
import sys
from errors import EXIT_CODES


# ===============
# Public Classes:
# ===============
class Parameters(object):
    """\
    This class processes the script parameters according to project assignment
    specifications. It is a wrapper class for argparse module usage.
    """

    _help_description = """\
This script generates a set of SQL commands which allow creation of appropriate
SQL database tables. These commands correspond to the script input, which is in
XML format, and create such table structure that can contain the data stored in
the script input. The behaviour of script can be altered with parameters below.\
"""
    _help_epilog = """\
This is the result of the 2nd school project @ BUT FIT, IPP course, 2014.

Author:     Dee'Kej (deekej@linuxmail.org)
Version:    0.1.0.0
Websites:   https://www.fit.vutbr.cz/
            https://github.com/deekej
            https://bitbucket.org/deekej"""

    result = None

    def _natural_num(self, string):
        """\
        Tests if the given string argument represents a natural number
        (number >= 0).
        """
        value = int(string)
        if value >= 0:
            return value
        else:
            message = "%r is not a natural number" % string
            raise argparse.ArgumentTypeError(message)

    def __init__(self):
        """\
        Initializes this class by preparing parser arguments so it can be called
        later with Parameters.process() method.
        """
        self._parser = _ArgumentParser(
            description=self._help_description,
            epilog=self._help_epilog,
            add_help=False,
            formatter_class=argparse.RawDescriptionHelpFormatter,
        )

        self._parser.add_argument(
            '--help',
            help='show this help message and exit',
            action=_HelpAction,
        )
        self._parser.add_argument(
            '--input',
            default=sys.stdin,
            metavar='filename',
            help='input XML file (default: stdin)',
        )
        self._parser.add_argument(
            '--output',
            default=sys.stdout,
            metavar='filename',
            help='output file in format specified by script parameters\
                  (default: stdout)'
        )
        self._parser.add_argument(
            '--isvalid',
            dest='valid',
            metavar='filename',
            help='file for examination with the script input'
        )
        self._parser.add_argument(
            '--header',
            metavar='string',
            help='string that will be used as a header for output file'
        )
        self._parser.add_argument(
            '-a',
            action='store_true',
            help='disables columns generating from XML attributes'
        )
        self._parser.add_argument(
            '-g',
            action='store_true',
            help='changes the output format into XML representation of SQL'
        )
        
        # --etc & -b parameters are mutually exclusive.
        mutex_group = self._parser.add_mutually_exclusive_group()

        mutex_group.add_argument(
            "--etc",
            type=self._natural_num,
            metavar='N',
            help='maximum number of columns which can be created'
        )
        mutex_group.add_argument(
            "-b",
            action="store_true",
            help='only the highest data type will be used for conflicting\
            sub-elements',
        )

    def process(self):
        """Runs the parameters processing itself."""
        self.result = self._parser.parse_args()
        
        # Acting same as Unix conventions require, the '-' is std{in,out}:
        if self.result.input == "-":
            self.result.input = sys.stdin

        if self.result.output == "-":
            self.result.output = sys.stdout

        if self.result.valid == "-":
            self.result.valid = sys.stdin
        
        # Two different files can't be read from stdin simultaneously:
        if self.result.valid == sys.stdin and self.result.input == sys.stdin:
            message = "both --input and --isvalid arguments can't use stdin"
            self._parser.error(message)

        return self.result


# =================
# Internal classes:
# =================
class _ArgumentParser(argparse.ArgumentParser):
    """Subclass of ArgumentParser used for redefinition of some methods."""

    def error(self, message):
        """Exit status has been changed to '1', otherwise same as super class."""
        self.print_usage(sys.stderr)
        self.exit(EXIT_CODES["error_parameters"],
                  ('%s: ERROR: %s\n') % (self.prog, message))


class _HelpAction(argparse._HelpAction):
    """Redefined internal class from argparse so the help action doesn't exit."""

    def __call__(self, parser, namespace, values, option_string):
        """Displays the help page when only --help parameter was used."""
        if len(sys.argv) == 2:
            parser.print_help()
        else:
            message = "argument --help is not allowed with other arguments"
            raise argparse.ArgumentError(None, message)


# ===================
# Internal functions:
# ===================
def _main():
    from pprint import pprint
    settings = Parameters().process()
    print("----------------------------------------------------")
    print("The dictionary of Parameters class with the results:")
    print("----------------------------------------------------")
    pprint(vars(settings))
    return 0

if __name__ == '__main__':
    status = _main()
    sys.exit(status)

