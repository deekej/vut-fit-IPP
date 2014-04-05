#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ==================================================================== #
#
# File (module): errors.py
# Version:       1.0.0.0
# Start date:    22-03-2014
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
# More info @:
#       https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# ==================================================================== #

# ==================
# Module doc-string:
# ==================
"""\
Implements everything corresponding to erroneous program behaviour. For
now, only exit codes are specified for all modules.
"""

__version__ = '1.0'
__all__ = ['EXIT_CODES']

# ==========
# Constants:
# ==========
EXIT_CODES = {
    "no_error": 0,
    "warning": 100,
    "error_parameters": 1,
    "error_open_read": 2,
    "error_open_write": 3,
    "error_wrong_format": 4,
    "error_names_conflict": 90,
    "error_validation_fail": 91,
    "error_failed_output": 101,
    "error_keyboard_interrupt": 130,
}

# ===================
# Internal functions:
# ===================
def _main():
    pass

if __name__ == '__main__':
    import sys
    status = _main()
    sys.exit(status)

