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
define("ERROR_ROOT_ELEM", 50);
define("ERROR_CHARS_SUBST", 51);


# ##################################################################################################################### #
# ### GLOBAL VARIABLES ################################################################################################ #
# ##################################################################################################################### #

# Array which will hold values of processes parameters:
$PARAMS["input_fname"] = NULL;              # --input
$PARAMS["output_fname"] = NULL;             # --output

$PARAMS["ill_chars_substitute"] = NULL;     # -h
$PARAMS["generate_header"] = NULL;          # -n
$PARAMS["root_element"] = NULL;             # -r
$PARAMS["root_element_attr"] = NULL;        # Storage in case -r also has an anttribute specified.

$PARAMS["array_name"] = NULL;               # --array-name
$PARAMS["item_name"] = NULL;                # --item-name

$PARAMS["string_transform"] = NULL;         # -s
$PARAMS["number_transform"] = NULL;         # -i
$PARAMS["literals_transform"] = NULL;       # -l
$PARAMS["chars_translate"] = NULL;          # -c

$PARAMS["array_size"] = NULL;               # -a | --array-size
$PARAMS["index_items"] = NULL;              # -t | --index-items

$PARAMS["counter_init"] = NULL;             # --start

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
          if ($PARAMS["counter_init"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but no start value was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            if (is_numeric($value) == false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not a number!\n");
              exit(ERROR_PARAMS);               # FIXME: IS this correct return value?
            }

            if (intval($value) != floatval($value)) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not an integer!\n");
              exit(ERROR_PARAMS);               # FIXME: IS this correct return value?
            }

            if ($value < 0) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not a positive number!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            $PARAMS["counter_init"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Counter value already initialized!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #########
        case "-h" :
          if ($PARAMS["ill_chars_substitute"] == NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '-h' used, but no substitution string was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["ill_chars_substitute"] = $value;
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
            
            # Testing if given root element name and attribute (if specified) have correct format:
            if (preg_match('/^(?!XML|\p{P}|\p{N})[\p{L}\p{N}\.-_]+[[:space:]]*$/i', $value) === 1 ||
                preg_match('/^(?!XML|\p{P}|\p{N})[\p{L}\p{N}\.-_]+[[:space:]]+[\p{L}\p{N}\.-_]+="[^"]*"[[:space:]]*$/i', $value) === 1) {
              
              $PARAMS["root_element"] = $value;
            }
            else {
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid root element name or attribute!\n");
              exit(ERROR_ROOT_ELEM);
            }

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
          fwrite(STDERR, SCRIPT_NAME . ": Error: Unknown parameter '" . $param . "' used!\n");
          display_usage();
          exit(ERROR_PARAMS);
      }

    }

    # ###################################################################################################################
      
    # Splitting the content of root element if it contains any attributes:
    if (preg_match('/[\p{L}\p{N}\.-_]+="[^"]*"[[:space:]]*$/i', $PARAMS["root_element"], $match, PREG_OFFSET_CAPTURE) === 1) {
      $PARAMS["root_element_attr"] = chop($match[0][0]);
      $PARAMS["root_element"] = chop(substr($PARAMS["root_element"], 0, $match[0][1]));
    }
    
    # Additional testing of '--start' parameter:
    if ($PARAMS["counter_init"] == true && $PARAMS["index_items"] == NULL) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but '-t' or '--index-items' is missing!\n");
      exit(ERROR_PARAMS);
    }


    # Setting default values if needed:
    if ($PARAMS["input_fname"] == NULL) {
      $PARAMS["input_fname"] = STDIN;
    }

    if ($PARAMS["output_fname"] == NULL) {
      $PARAMS["output_fname"] = STDOUT;
    }

    if ($PARAMS["ill_chars_substitute"] == NULL) {
      $PARAMS["ill_chars_substitute"] = "-";
    }

    if ($PARAMS["array_name"] == NULL) {
      $PARAMS["array_name"] = "array";
    }

    if ($PARAMS["item_name"] == NULL) {
      $PARAMS["item_name"] = "item";
    }

    if ($PARAMS["counter_init"] == NULL) {
      $PARAMS["counter_init"] = 1;
    }

    # Setting other non-initialized values to false:
    foreach($PARAMS as $key => $value) {
      if ($value == NULL) {
        $PARAMS[$key] = false;
      }
    }

    return;
  }}}


# ##################################################################################################################### #
# ### START OF SCRIPT EXECUTION ####################################################################################### #
# ##################################################################################################################### #

  params_process();

//   var_dump($PARAMS);

  exit(0);
?>
