#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): tables_builder.py
# Version:       1.0.0.0
# Start date:    23-03-2014
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
# Description:   See the Singleton class doc-string.
#
# More info @:   https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# ============================================================================= #

# ===============
# Public Classes:
# ===============
class Singleton(type):
    """\
    Singleton meta-class implementation so it can be used in other modules for
    creating Singletons easily.
    """
    _instances = dict()

    def __call__(cls, *args, **kwargs):
        if cls not in cls._instances:
            cls._instances[cls] = super(Singleton, cls).__call__(*args, **kwargs)
        return cls._instances[cls]


if __name__ == '__main__':
    pass

