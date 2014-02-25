<?php
#PADDING LINE...
#JSN:xkaspa34

# ##################################################################################################################### #
# File:          jsn.php
# Version:       0.0.1.0
# Start date:    23-02-2014
# Last update:   25-02-2014
#
# Course:        IPP (summer semester, 2014)
# Project:       Script for converting of JSON format to XML format, written in PHP scripting language (version 5).
#
# Author:        David Kaspar (aka Dee'Kej), 3BIT
# 
# Faculty:       Faculty of Information Technologies,
#                Brno University of Technologies,
#                Czech Republic
#
# E-mail:        xkaspa34@stud.fit.vutbr.cz
#
# Description:   TODO
#
# More info @:   https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# {{{ }}} NOTE:  TODO
# ##################################################################################################################### #

# ##################################################################################################################### #
# ### CONSTANTS ####################################################################################################### #
# ##################################################################################################################### #

define("SCRIPT_NAME", $argv[0]);

# ERROR codes:
define("NO_ERROR", 0);
define("ERROR_PARAMS", 1);
define("ERROR_READ", 2);
define("ERROR_WRITE", 3);
define("ERROR_FORMAT", 4);


# ##################################################################################################################### #
# ### GLOBAL VARIABLES ################################################################################################ #
# ##################################################################################################################### #

# Array which will hold values of processes parameters:
$PARAMS["input_fname"] = NULL;              # --input
$PARAMS["output_fname"] = NULL;             # --output

$PARAMS["chars_substitute"] = NULL;         # -h
$PARAMS["chars_translate"] = NULL;          # -c

$PARAMS["generate_header"] = NULL;          # -n
$PARAMS["root_element"] = NULL;             # -r

$PARAMS["array_name"] = NULL;               # --array-name
$PARAMS["array_size"] = NULL;               # --array-size | -a
$PARAMS["item_name"] = NULL;                # --item-name

$PARAMS["string_transform"] = NULL;         # -s
$PARAMS["number_transform"] = NULL;         # -i
$PARAMS["literals_transform"] = NULL;       # -l

$PARAMS["index_items"] = NULL;              # --index-items | -t
$PARAMS["start_counter"] = NULL;            # --start

$PARAMS["padding"] = NULL;                  # --padding
$PARAMS["flattening"] = NULL;               # --flattening
$PARAMS["error_recovery"] = NULL;           # --error-recovery


# ##################################################################################################################### #
# ### FUNCTIONS ####################################################################################################### #
# ##################################################################################################################### #
  
  # TODO: Write function description.
  function display_help()
  {{{
    # TODO: Write text of help.
  }}}
  
  # TODO: Write function description.
  function display_usage()
  {{{
    # TODO: Write text of usage.
  }}}

  # Function for testing & processing parameters given to the script. Exits with error message in case of wrong
  # parameter / wrongly used parameter. It also initializes the default values for unused parameters. No return value.
  function params_process()
  {{{
    # Global variables used in this function:
    global $argv, $argc;
    global $PARAMS;
    
    # Possibly testing all arguments in $argv:
    for ($index = 1; $index < $argc; $index++) {

      # Trying to find '=' character in actual parameter, if any:
      $param = strstr($argv[$index], "=", true);

      if ($param == false) {
        $param = $argv[$index];                                     # No '=' found, reassigning value.
        $value = NULL;
      }
      else {
        $value = substr(strstr($argv[$index], "=", false), 1);      # '=' found, getting the value (without '=' char).
      }
      
      # Testing the parameter in appropriate match & setting the global variable if required:
      switch($param) {

        case "--help" :
     // case "-help" :
          if ($argc > 2) {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid parameters combination!\n");
            exit(ERROR_PARAMS);
          }
          else {
            display_usage();
            display_help();
            exit(NO_ERROR);
          }

          break;

        # ##############
        case "--input" :
     // case "-input" :
          if ($PARAMS["input_fname"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--input' used, but no file name was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            $PARAMS["input_fname"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Input file already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;
        
        # ###############
        case "--output" :
     // case "-output" :
          if ($PARAMS["output_fname"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--output' used, but no file name was specfified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["output_fname"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Output file already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ###################
        case "--array-name" :
     // case "-array-name" :
          if ($PARAMS["array_name"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--array-name' used, but no array name was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["array_name"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Array name already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ##################
        case "--item-name" :
     // case "-item-size :
          if ($PARAMS["item_name"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--item-name' used, but no item name was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["item_name"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Item name already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ##############
        case "--start" :
     // case "-start" :
          if ($PARAMS["start_counter"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but no start value was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            # FIXME: Convert $value from string to integer?
            $PARAMS["start_counter"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Start counter already initialized!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #########
        case "-h" :
          if ($PARAMS["chars_substitute"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '-h' used, but no substitution string was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["chars_substitute"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Substitution string already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #########
        case "-r" :
          if ($PARAMS["root_element"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '-r' used, but no root element name was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["root_element"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Root element name already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;
        
        # ###################
        case "--array-size" :
     // case "-array-size" :
        case "-a" :
          if ($PARAMS["array_size"] == NULL) {
            $PARAMS["array_size"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-a' or '--array-size' parameters used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ####################
        case "--index-items" :
     // case "-index-items" :
        case "-t" :
          if ($PARAMS["index_items"] == NULL) {
            $PARAMS["index_items"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-t' or '--index-items' parameters used!\n");
            exit(ERROR_PARAMS);
          }

          break;
        
        # #########
        case "-n" :
          if ($PARAMS["generate_header"] == NULL) {
            $PARAMS["generate_header"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-n' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #########
        case "-s" :
          if ($PARAMS["string_transform"] == NULL) {
            $PARAMS["string_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-s' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;
        
        # #########
        case "-i" :
          if ($PARAMS["number_transform"] == NULL) {
            $PARAMS["number_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-i' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #########
        case "-l" :
          if ($PARAMS["literals_transform"] == NULL) {
            $PARAMS["literals_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-l' parameter used!\n");
            exit(ERROR_PARAMS);
          }
          
          break;

        # #########
        case "-c" :
          if ($PARAMS["chars_translate"] == NULL) {
            $PARAMS["chars_translate"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '-c' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ################
        case "--padding" :
          if ($PARAMS["padding"] == NULL) {
            $PARAMS["padding"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '--padding' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # ###################
        case "--flattening" :
          if ($PARAMS["flattening"] == NULL) {
            $PARAMS["flattening"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '--flattening' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #######################
        case "--error-recovery" :
          if ($PARAMS["error_recovery"] == NULL) {
            $PARAMS["error_recovery"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplicit '--error-recovery' parameter used!\n");
            exit(ERROR_PARAMS);
          }

          break;

        # #######
        default :
          fwrite(STDERR, SCRIPT_NAME. ": Error: Unknown parameter '" . $param . "' used!\n");
          display_usage();
          exit(ERROR_PARAMS);
      }

    }

    # TODO: setting unused values to false.
    # TODO: Additional testing.

    return;
  }}}


# ##################################################################################################################### #
# ### START OF SCRIPT EXECUTION ####################################################################################### #
# ##################################################################################################################### #

  params_process();

  exit(0);
?>
