#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): __init__.py
# Version:       0.5.0.0
# Start date:    01-04-2014
# Last update:   01-04-2014
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
Simple organization module for easier imports.
"""

# ========
# Imports:
# ========

from analyzer import XMLAnalyser, ValidationFail
from errors import EXIT_CODES
from input_output import InputOutput
from tables_builder import TablesBuilder, NamesConflict
from tables_builder import BIT, INT, FLOAT, NVARCHAR, NTEXT

