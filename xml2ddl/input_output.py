#!/usr/bin/env python3.2
# -*- coding: utf-8 -*-
#XTD:xkaspa34

# ============================================================================= #
#
# File (module): input_output.py
# Version:       0.4.0.0
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
# More info @:   https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# ============================================================================= #

# ==================
# Module doc-string:
# ==================
"""\
Contains public class InputOutput as an interface for any Input/Output
operations. It is meant to be a kind of interface for wrapping all of the
functionality for the xtd.py script.
"""

# ========
# Imports:
# ========
import atexit
import os
import sys
import xml.etree.ElementTree as XML
from errors import EXIT_CODES


# ===============
# Public Classes:
# ===============
class InputOutput(object):
    """Interface for Input/Output operations."""

    # Initial values of file descriptors
    _fd_input = sys.stdin
    _fd_valid = None
    _fd_output = sys.stdout

    # Initial values of parsed XML trees.
    _input_tree = None
    _valid_tree = None
    # _output_tree = None # NOTE: _output_tree will be probably used later.

    def __init__(self, settings):
        """\
        Initializes the interface by opening all necessary files. It expects
        settings to be an instance of the Parameters class (parameters.py
        module). It also registers closing of the opened files at any exit.
        """
            
        self._xml_output = settings.g
        self._output_header = settings.header

        atexit.register(self._close)    # Making sure the files will be closed.

        # Trying to open specified files in text mode and with UTF-8 encoding:
        if settings.input != sys.stdin:
            try:
                self._fd_input = open(
                    file=settings.input, 
                    mode='rt',
                    encoding='utf-8',
                    errors='strict',
                )
            except IOError:
                self._error(EXIT_CODES["error_open_read"])

        if settings.valid != None:
            if settings.valid == sys.stdin:
                self._fd_valid = sys.stdin      # stdin doesn't need opening.
            else:
                try:
                    self._fd_valid = open(
                        file=settings.valid,
                        mode='rt',
                        encoding='utf-8',
                        errors='strict',
                    )
                except IOError:
                    self._error(EXIT_CODES["error_open_read"])

        if settings.output != sys.stdout:
            try:
                self._fd_output = open(
                    file=settings.output,
                    mode='wt',
                    encoding='utf-8',
                    errors='strict',
                )
            except IOError:
                self._error(EXIT_CODES["error_open_write"])

    def read_trees(self):
        """\
        Returns 2 ElementTree instances containing parsed XML input file and
        validation file. It parses the files in case it has not been parsed yet.
        """
        if self._input_tree:
            return self._input_tree, self._valid_tree

        try:
            self._input_tree = XML.parse(self._fd_input)

            if self._fd_valid:
                self._valid_tree = XML.parse(self._fd_valid)
        except (ValueError, XML.ParseError):
            self._error(EXIT_CODES["error_wrong_format"])
        except KeyboardInterrupt:
            self._exit(EXIT_CODES["error_keyboard_interrupt"])
        return self._input_tree, self._valid_tree

    def write(self, content):
        """\
        Prints the output of given content by calling its polymorphic write
        method. It can add a header output in case it was required.
        """
        try:
            if self._output_header:
                if self._xml_output:
                    # Adding header as a comment into ElementTree object, which
                    # will be printed below:
                    pass                    # TODO: Adding XML comment later.
                else:
                    # Printing header in required format.
                    self._fd_output.write("--%s\n\n" % self._output_header)

            content.write(
                file=self._fd_output,
                encoding='utf-8',
                xml_declaration=False,      # NOTE: Might change.
                method='xml',               # NOTE: Might change.
            )
        except IOError:
            self._error(EXIT_CODES["error_failed_output"])

    def _close(self):
        """Closes all non-std{in,out} files opened before."""
        if self._fd_input != sys.stdin:
            self._fd_input.close()

        if self._fd_output != sys.stdout:
            self._fd_output.close()

        if self._fd_valid != None and self._fd_valid:
            self._fd_input.close()

    def _exit(self, status=EXIT_CODES["no_error"], message=None):
        """Exits with corresponding exit status code and message."""
        if message:
            sys.stderr.write(message)
        sys.exit(status)

    def _error(self, status, message=None):
        """\
        Wrapping function for exiting upon error. It prepares the error message
        if not specified and calls the _exit() method with specified exit status
        code and error message.
        """ 
        if message:
            self._exit(status, message)
        else:
            prog = os.path.basename(sys.argv[0])
            self._exit(status, ("%s: %s\n" % (prog, str(sys.exc_info()[1]))))


# =================
# Internal classes:
# =================


# ===================
# Internal functions:
# ===================
def _main():
    from parameters import Parameters
    from pprint import pprint

    settings = Parameters().process()
    io = InputOutput(settings)
    input_tree, valid_tree = io.read_trees()

    # Dumping the whole tree:
    XML.dump(input_tree)
    print("\n----------------------\n")
    
    # Displaying root and some following elements:
    pprint(vars(input_tree))
    print("\n----------------------\n")
    pprint(vars(input_tree._root))
    print("\n----------------------\n")
    pprint(input_tree._root._children)
    print("\n----------------------\n")
    pprint(vars(input_tree._root._children[0]))
    print("\n----------------------\n")
    pprint(input_tree.getroot())
    print("\n----------------------\n")
    pprint(valid_tree)
    return 0

if __name__ == '__main__':
    status = _main()
    sys.exit(status)

