#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): params.py
# Version:       0.7.0.0.
# Start date:    01-04-2014
# Last update:   04-04-2014
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
This script converts a given XML file into a SQL commands for creating tables,
which can hold the data stored within the input. See the help of the script for
more information.
"""

# ========
# Imports:
# ========
import os
import sys
from errors import EXIT_CODES
from parameters import Parameters
from input_output import InputOutput
from tables_builder import NamesConflict
from analyser import XMLAnalyser, ValidationFail
from database_to_xml import DBaseToXML

def main():
    """\
    Wrapping main function for invocation purposes only.
    """
    # Getting script parameters:
    settings = Parameters().process()

    # Initializing Input-Output interface:
    interface = InputOutput(settings)

    # Reading and parsing the script input:
    input_tree, valid_tree = interface.read_trees()

    # Initializing the script analyser:
    analyser = XMLAnalyser(settings, input_tree, valid_tree)

    # Running the analysis:
    try:
        database = analyser.run()
    except NamesConflict:
        prog = os.path.basename(sys.argv[0])
        msg = "collisions between attribute and element names detected"
        sys.stderr.write("%s: ERROR: %s\n" % (prog, msg))
        sys.exit(EXIT_CODES["error_names_conflict"])
    except ValidationFail:
        prog = os.path.basename(sys.argv[0])
        msg = "database structure can't store the validation file data"
        sys.stderr.write("%s: ERROR: %s\n" % (prog, msg))
        sys.exit(EXIT_CODES["error_validation_fail"])

    # Converting the result of analysis to another XML representation if
    # requested:
    if settings.g:
        DBaseToXML(database).run()

    interface.write(database)
    return EXIT_CODES["no_error"]

if __name__ == '__main__':
    status = main()
    sys.exit(status)

