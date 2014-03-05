<?php
#PADDING LINE...
#JSN:xkaspa34

# #################################################################################################################### #
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
# #################################################################################################################### #

# #################################################################################################################### #
# ### CONSTANTS ###################################################################################################### #
# #################################################################################################################### #

define("SCRIPT_NAME", $argv[0]);
define("XML_HEADER", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
#<? #NOTE: <-This is simple workaround for VIM syntax highlight, because I have broken it.

# ERROR codes:
define("NO_ERROR", 0);
define("WARNING", 100);
define("ERROR_PARAMS", 1);
define("ERROR_OPEN_READ", 2);
define("ERROR_OPEN_WRITE", 3);
define("ERROR_FORMAT", 4);
define("ERROR_XML_NAME", 50);
define("ERROR_CHARS_SUBST", 51);
define("ERROR_CLOSE", 101);
define("ERROR_WRITE_OUTPUT", 102);
define("ERROR_JSON_UNKNOWN", 110);
define("ERROR_JSON_DEPTH", 111);


# #################################################################################################################### #
# ### GLOBAL VARIABLES ############################################################################################### #
# #################################################################################################################### #

# Global return value. In case of unrecoverable error the script ends,
# in other cases it sets this variable. (e.g. for warnings)
$RET_VAL = NO_ERROR;

# Actual plunge for indentation purposes of XML output.
$PLUNGE = 0;

# Array which will hold values of processes parameters:
$PARAMS["input_file"] = NULL;               # --input
$PARAMS["output_file"] = NULL;              # --output

$PARAMS["ill_chars_substitute"] = NULL;     # -h
$PARAMS["generate_header"] = NULL;          # -n

$PARAMS["root_element"] = NULL;             # -r
$PARAMS["root_element_attr"] = NULL;        # Storage in case -r also has an attribute specified.

$PARAMS["array_name"] = NULL;               # --array-name
$PARAMS["array_name_attr"] = NULL;          # Storage in case --array-name has an attribute specified.

$PARAMS["item_name"] = NULL;                # --item-name
$PARAMS["item_name_attr"] = NULL;           # Storage in case --item-name has an attribute specified.

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

$PARAMS["offset_size"] = NULL;              # --offset-size


# #################################################################################################################### #
# ~~~ SCRIPT AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #

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
  

  # All script initialization (with additional testing if needed).
  function script_init()
  {{{
    global $RET_VAL;

    # Enabling all warnings for debugging purposes. TODO: CHANGE BEFORE SUBMITTING THE FINISHED SCRIPT.
    ini_set('error_reporting', E_ALL);

    # Setting UTF-8 as encoding for string & regex operations:
    if (mb_internal_encoding("UTF-8") == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Warning: Failed to set internal encoding for multibyte characters,\n");
      fwrite(STDERR, "\t\t\tthe output result might not be valid.\n");
      $RET_VAL = WARNING;
    }

    return;
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
      $param = mb_strstr($argv[$index], "=", true);

      if ($param == false) {
        $param = $argv[$index];                                       # No '=' found, reassigning value.
        $value = NULL;
      }
      else {
        $value = mb_substr(mb_strstr($argv[$index], "=", false), 1);  # '=' found, getting the value (without '=' char).
      }
      
      # Testing the parameter in appropriate match & setting the global variable if required:
      switch($param) {

        case "--help" :
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
          if ($PARAMS["input_file"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--input' used, but no file name was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            $PARAMS["input_file"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Input file already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;
        

        # ###############
        case "--output" :
          if ($PARAMS["output_file"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--output' used, but no file name was specified!\n");
              exit(ERROR_PARAMS);
            }

            $PARAMS["output_file"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Output file already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # ###################
        case "--array-name" :
          if ($PARAMS["array_name"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--array-name' used, but no array name was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            if (xml_validate_name($value) == false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid array element name or attribute!\n");
              exit(ERROR_XML_NAME);
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
          if ($PARAMS["item_name"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--item-name' used, but no item name was specified!\n");
              exit(ERROR_PARAMS);
            }

            if (xml_validate_name($value) == false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid item element name or attribute!\n");
              exit(ERROR_XML_NAME);
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
          if ($PARAMS["counter_init"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but no start value was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            if (is_numeric($value) == false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not a number!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            if (intval($value) != floatval($value)) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not an integer!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            if ($value < 0) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not a positive number!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            $PARAMS["counter_init"] = intval($value);
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Counter value already initialized!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #########
        case "-h" :
          if ($PARAMS["ill_chars_substitute"] === NULL) {
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
          if ($PARAMS["root_element"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '-r' used, but no root element name was specified!\n");
              exit(ERROR_PARAMS);
            }
            
            if (xml_validate_name($value) === false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid root element name or attribute!\n");
              exit(ERROR_XML_NAME);
            }
            
            $PARAMS["root_element"] = $value;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Root element name already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;

        
        # ####################
        case "--offset-size" :
          if ($PARAMS["offset_size"] === NULL) {
            if ($value == NULL) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' used, but no offset value was specified!\n");
              exit(ERROR_PARAMS);
            }

            if (is_numeric($value) == false) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' value is not a number!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            if (intval($value) != floatval($value)) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' value is not an integer!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            if ($value < 0) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' value is not a positive number!\n");
              exit(ERROR_PARAMS);               # FIXME: Is this correct return value?
            }

            $PARAMS["offset_size"] = intval($value);
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Offset size already specified!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # ###################
        case "--array-size" :
        case "-a" :
          if ($PARAMS["array_size"] === NULL) {
            $PARAMS["array_size"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-a' or '--array-size' parameters!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # ####################
        case "--index-items" :
        case "-t" :
          if ($PARAMS["index_items"] === NULL) {
            $PARAMS["index_items"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-t' or '--index-items' parameters!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #########
        case "-n" :
          if ($PARAMS["generate_header"] === NULL) {
            $PARAMS["generate_header"] = false;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-n' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #########
        case "-s" :
          if ($PARAMS["string_transform"] === NULL) {
            $PARAMS["string_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-s' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;
        

        # #########
        case "-i" :
          if ($PARAMS["number_transform"] === NULL) {
            $PARAMS["number_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-i' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #########
        case "-l" :
          if ($PARAMS["literals_transform"] === NULL) {
            $PARAMS["literals_transform"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-l' parameter!\n");
            exit(ERROR_PARAMS);
          }
          
          break;


        # #########
        case "-c" :
          if ($PARAMS["chars_translate"] === NULL) {
            $PARAMS["chars_translate"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '-c' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # ################
        case "--padding" :
          if ($PARAMS["padding"] === NULL) {
            $PARAMS["padding"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '--padding' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # ###################
        case "--flattening" :
          if ($PARAMS["flattening"] === NULL) {
            $PARAMS["flattening"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '--flattening' parameter!\n");
            exit(ERROR_PARAMS);
          }

          break;


        # #######################
        case "--error-recovery" :
          if ($PARAMS["error_recovery"] === NULL) {
            $PARAMS["error_recovery"] = true;
          }
          else {
            fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '--error-recovery' parameter!\n");
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

    # Additional testing of '--start' parameter:
    if ($PARAMS["counter_init"] == true && $PARAMS["index_items"] === NULL) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but '-t' or '--index-items' is missing!\n");
      exit(ERROR_PARAMS);
    }

    if ($PARAMS["padding"] == true && $PARAMS["index_items"] === NULL) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: '--padding' used, but '-t' or '--index-items' is missing!\n");
      exit(ERROR_PARAMS);
    }


    # Setting default values if needed, or doing additional processing:
    if ($PARAMS["input_file"] === NULL) {
      $PARAMS["input_file"] = STDIN;
    }

    if ($PARAMS["output_file"] === NULL) {
      $PARAMS["output_file"] = STDOUT;
    }

    if ($PARAMS["ill_chars_substitute"] === NULL) {
      $PARAMS["ill_chars_substitute"] = "-";
    }

    if ($PARAMS["counter_init"] === NULL) {
      $PARAMS["counter_init"] = 1;
    }

    if ($PARAMS["offset_size"] === NULL) {
      $PARAMS["offset_size"] = 4;
    }

    if ($PARAMS["generate_header"] === NULL) {
      $PARAMS["generate_header"] = true;
    }

    if ($PARAMS["array_name"] === NULL) {
      $PARAMS["array_name"] = "array";
    }
    else {
      xml_name_attr_split("array_name");                # Attribute separation if needed.
    }

    if ($PARAMS["item_name"] === NULL) {
      $PARAMS["item_name"] = "item";
    }
    else {
      xml_name_attr_split("item_name");                 # Attribute separation if needed.
    }

    if ($PARAMS["root_element"] !== NULL) {
      xml_name_attr_split("root_element");              # Attribute separation if needed.
    }


    # Setting other non-initialized values to false:
    foreach($PARAMS as $key => $value) {
      if ($value === NULL) {
        $PARAMS[$key] = false;
      }
    }


    return;
  }}}


# #################################################################################################################### #
# ~~~ I/O AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #
  
  # Wrapping function for opening input file. This function does not actually opens the file, it only test the file's
  # existence & permissions due to read function used later, which opens and reads the file by itself.
  function open_input_file()
  {{{
    global $PARAMS;

    if ($PARAMS["input_file"] == STDIN) {
      $PARAMS["input_file"] = "php://stdin";   # Small hack - assigning 'path' to STDIN for file_get_contents().
      return;
    }
    
    if (is_readable($PARAMS["input_file"]) == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Specified input file doesn't exist or is not readable!\n");
      exit(ERROR_OPEN_READ);
    }

    return;
  }}}

  
  # Wrapping function for opening specified output file, if any.
  function open_output_file()
  {{{
    global $PARAMS;

    if ($PARAMS["output_file"] == STDOUT) {
      return;                                   # Already opened stream, nothing to do.
    }

    $file_ptr = @fopen($PARAMS["output_file"], "w");   # The '@' suppresses the error/warning messages of PHP.

    if ($file_ptr == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Specified output file can't be created/rewritten!\n");
      exit(ERROR_OPEN_WRITE);
    }

    $PARAMS["output_file"] = $file_ptr;        # Backing up the file pointer.

    return;
  }}}


  # Callback function for sanity purposes only. In case of opening file with fopen(), appropriate fclose() equivalent
  # would go here. Currently this functions does nothing. If needed, it should be registered with
  # register_shutdown_function().
  function close_input_file()
  {{{
    return;                       # file_get_contents() function does not need opened file descriptor, nothing to do.
  }}}

  
  # Callback function for closing already opened file. This should be registered with register_shutdown_function().
  function close_output_file()
  {{{
    global $PARAMS, $RET_VAL;

    if ($PARAMS["output_file"] != STDOUT && fclose($PARAMS["output_file"] == false)) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Failed to close output file, result may not be valid!\n");
      $RET_VAL = ERROR_CLOSE;     # This is the callback function upon exit. We can't call exit before complete cleanup.
    }
    
    return;
  }}}


# #################################################################################################################### #
# ~~~ I/O FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #

  # Reads the whole content of the specified file into string, which it returns.
  # It also work if input file is STDIN stream.
  function read_file_content()
  {{{
    global $PARAMS;

    $string = @file_get_contents($PARAMS["input_file"]);   # The '@' suppresses error/warning messages of PHP.

    if ($string === false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Could not read the content of the input file!\n");
      exit(ERROR_OPEN_READ);
    }

    return $string;
  }}}

  # Wrapping function for writing content to output. It adds the trailing '\n' character & tests if the output was
  # successful. It ends the script upon error.
  function output_write($content)
  {{{
    global $PARAMS;

    if (fwrite($PARAMS["output_file"], $content . "\n") == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Failed to write to output file!\n");
      exit(ERROR_OUTPUT_WRITE);
    }

    return;
  }}}


# #################################################################################################################### #
# ~~~ STRING AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #
  
  # Generates string of white spaces of specified length. This string is then used by get_offset_string() function.
  function offset_string_init()
  {{{
    global $PARAMS;

    $string = "";

    for ($i = 0; $i < intval($PARAMS["offset_size"]); $i++) {
      $string .= " ";
    }

    define("OFFSET_STRING", $string);     # Making generated string a constant so it can't be accidentally changed.

    return;
  }}}

  
  # Generates appropriate string of white spaces based upon already created OFFSET_STRING. It uses the global variable
  # $PLUNGE, which specifies actual number of offsets. Returns string which can be concatenated with XML element, thus
  # generating complete line with appropriate XML element offset.
  function get_offset_string()
  {{{
    global $PLUNGE;

    $string = "";

    for ($i = 0; $i < $PLUNGE; $i++) {
      $string .= OFFSET_STRING;
    }
    
    return $string;
  }}}


# #################################################################################################################### #
# ~~~ JSON FORMAT AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #

  # Classifies the value from the multidimensional array returned by json_decode(). Returns string classifying the
  # value.
  function json_classify($item)
  {{{
    if (is_scalar($item)) {

      if (is_string($item)) {
        return "JSON_STRING";
      }
      else if (is_int($item)) {
        return "JSON_INTEGER";
      }
      else if (is_float($item)) {
        return "JSON_FLOAT";
      }
      else if (is_bool($item)) {
        return "JSON_BOOLEAN";
      }
      else {
        return "JSON_ERROR";
      }

    }
    else {

      if (is_array($item)) {
        
        # We're assuming that json_decode() function will always
        # return array with string or integer types as keys.
        foreach($item as $key => $value) {
          if (is_string($key)) {
            return "JSON_OBJECT";
          }
          else {
            return "JSON_ARRAY";
          }
        }

      }
      else if (is_null($item)) {
        return "JSON_NULL";
      }
      else {
        return "JSON_ERROR";
      }

    }
  }}}


# #################################################################################################################### #
# ~~~ XML FORMAT AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #

  # Function for testing if given string has a proper XML name for an element. Returns TRUE if the string is valid, FALSE
  # otherwise. It also accepts the name and the attribute, which can be used in element name.
  function xml_validate_name($string)
  {{{
    return (preg_match('/^(?!XML|\p{P}|\p{N})[\p{L}\p{N}\.-_]+[[:space:]]*$/ui', $string) === 1 ||
            preg_match('/^(?!XML|\p{P}|\p{N})[\p{L}\p{N}\.-_]+[[:space:]]+[\p{L}\p{N}\.-_]+="[^"]*"[[:space:]]*$/ui', $string) === 1);
  }}}


  # Function for splitting value of parameter of given name into XML name & XML attribute in case an attribute is found.
  function xml_name_attr_split($param_name)
  {{{
    global $PARAMS;

    if (preg_match('/[\p{L}\p{N}\.-_]+="[^"]*"[[:space:]]*$/ui', $PARAMS[$param_name], $match, PREG_OFFSET_CAPTURE) === 1) {
      $PARAMS[$param_name . "_attr"] = chop($match[0][0]);
      $PARAMS[$param_name] = chop(substr($PARAMS[$param_name], 0, $match[0][1]));
    }

    return;
  }}}


# #################################################################################################################### #
# ### START OF SCRIPT EXECUTION ###################################################################################### #
# #################################################################################################################### #
  
  script_init();
  params_process();

  var_dump($PARAMS);


  open_input_file();
  register_shutdown_function("close_input_file");

  open_output_file();
  register_shutdown_function("close_output_file");

  $string = read_file_content();

  # Checking the encoding:
  if (mb_check_encoding($string, "UTF-8") == false) {
    fwrite(STDERR, SCRIPT_NAME . ": Error: The input file has not 'UTF-8' encoding!\n");
    exit(ERROR_FORMAT);
  }


  $json_array = json_decode($string, true);

  switch (json_last_error()) {
    case JSON_ERROR_NONE :
      # No ERROR, proceed.
      break;

    case JSON_ERROR_DEPTH :
      fwrite(STDERR, SCRIPT_NAME . ": Error: The maximum stack depth for JSON format decoding has been exceede!\n");
      exit(ERROR_JSON_DEPTH);

    case JSON_ERROR_STATE_MISMATCH :
      fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid or malformed JSON format!\n");
      exit(ERROR_FORMAT);

    case JSON_ERROR_CTRL_CHAR :
      fwrite(STDERR, SCRIPT_NAME . ": Error: Unexpected control character found!\n");
      exit(ERROR_FORMAT);

    case JSON_ERROR_SYNTAX :
      fwrite(STDERR, SCRIPT_NAME . ": Error: Wrong JSON syntax - malformed JSON format!\n");
      exit(ERROR_FORMAT);

    default :
      fwrite(STDERR, SCRIPT_NAME . ": Error: Unknown error of json_decode() function!\n");
      exit(ERROR_JSON_UNKNOWN);
  }

  if ($PARAMS["generate_header"] == true) {
    output_write(XML_HEADER);
  }

  offset_string_init();

  exit($RET_VAL);
?>
