#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ==================================================================== #
#
# File (module): cardinality.py
# Version:       1.0.0.0
# Start date:    03-04-2014
# Last update:   03-34-2014
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
Simple singleton classes for representation of the database relation
cardinality. They return the string representation of the cardinality
when str() or repr() functions are used.
"""
__version__ = '1.0'

# Provided classes:
__all__ = [
    'Card_None',
    'Card_1to1',
    'Card_1toN',
    'Card_Nto1',
    'Card_NtoM',
]

# ========
# Imports:
# ========
from singleton import Singleton

# ===============
# Public Classes:
# ===============
class Card_None(metaclass=Singleton):
    """No cardinality - epsilon relation."""
    def __repr__(self):
        return 'ε'


class Card_1to1(metaclass=Singleton):
    """Reflexivity - one-to-one cardinality."""
    def __repr__(self):
        return '1:1'


class Card_1toN(metaclass=Singleton):
    """One-to-many cardinality."""
    def __repr__(self):
        return '1:N'


class Card_Nto1(metaclass=Singleton):
    """Many-to-one cardinality."""
    def __repr__(self):
        return 'N:1'


class Card_NtoM(metaclass=Singleton):
    """Many-to-many cardinality."""
    def __repr__(self):
        return 'N:M'

# ===================
# Internal functions:
# ===================
def _main():
    pass

if __name__ == '__main__':
    import sys
    status = _main()
    sys.exit(status)

