<?php
#PADDING LINE...
#JSN:xkaspa34

# #################################################################################################################### #
# File:          jsn.php
# Version:       1.0.0.1
# Start date:    23-02-2014
# Last update:   09-03-2014
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
# Description:   Script for converting JSON format (RFC 4627) into XML (1.0) format.
#
# More info @:   https://www.fit.vutbr.cz/study/courses/index.php?id=9384 
#
# File encoding: en_US.utf8 (United States)
#
# {{{ }}} NOTE:  This scripts uses marks for manual folding in VIM. If you dislike it or your IDE uses it's own folding
#                method, you can remove them by running this command in bash: sed -ie 's/{{{/{/; s/}}}/}/' this_filename
# #################################################################################################################### #

# #################################################################################################################### #
# ### CONSTANTS ###################################################################################################### #
# #################################################################################################################### #

# Turn on debugging messages?
define("DEBUG", true);

define("SCRIPT_NAME", $argv[0]);
define("XML_HEADER", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
#<? # <-- Small hack to restore proper VIM colorization, which I broke with hacking VIM configuration files.

# ERROR codes:
define("NO_ERROR", 0);
define("WARNING", 100);
define("ERROR_PARAMS", 1);
define("ERROR_OPEN_READ", 2);
define("ERROR_OPEN_WRITE", 3);
define("ERROR_FORMAT", 4);
define("ERROR_XML_NAME", 50);
define("ERROR_CHARS_SUBST", 51);
define("ERROR_INTERNAL", 101);
define("ERROR_WRITE_OUTPUT", 102);
define("ERROR_CLOSE", 109);
define("ERROR_JSON_UNKNOWN", 110);
define("ERROR_JSON_DEPTH", 111);
define("ERROR_JSON2XML", 120);

# Taken from official XML 1.0 Standard/Document (Fifth Edition) @ http://www.w3.org/TR/REC-xml/#sec-common-syn
define("XML_NameStartChar", '_:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}');
define("XML_NameChar", XML_NameStartChar . '-.\\-0-9\\xB7\\x{0300}-\\x{036F}\\x{203F}-\\x{2040}');


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

$PARAMS["array_name"] = NULL;               # --array-name

$PARAMS["item_name"] = NULL;                # --item-name

$PARAMS["string_transform"] = NULL;         # -s
$PARAMS["number_transform"] = NULL;         # -i
$PARAMS["literals_transform"] = NULL;       # -l
$PARAMS["problem_chars_translate"] = NULL;  # -c

$PARAMS["array_size"] = NULL;               # -a | --array-size
$PARAMS["index_items"] = NULL;              # -t | --index-items

$PARAMS["counter_init"] = NULL;             # --start

$PARAMS["padding"] = NULL;                  # --padding
# $PARAMS["flattening"] = NULL;               # --flattening
# $PARAMS["error_recovery"] = NULL;           # --error-recovery

$PARAMS["offset_size"] = NULL;              # --offset-size


# #################################################################################################################### #
# ~~~ SCRIPT AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #
  
  # Displays the help page of the script.
  function display_help()
  {{{
    # TERM. WIDTH: |                                                                               |
    fwrite(STDOUT, "Usage: php " . SCRIPT_NAME . " [OPTIONS]\n" .
                   "-------------------------------------------------------------------------------\n" .
                   "      Script for converting JSON format (RFC 4627) into XML (1.0) format.\n" .
                   "-------------------------------------------------------------------------------\n" .
                   "OPTIONS:    (long version:)\n" .
                   "              --help\n" .
                   "                  Displays this help page.\n\n" .
                   "              --input=filename\n" .
                   "                  Input file in JSON format. STDIN is used if not specified.\n\n" .
                   "              --output=filename\n" .
                   "                  Output file in XML format. STDOUT is used if not specified.\n\n" .
                   "  -h=subst\n" .
                   "                  'subst' - string to be used for substitution of every\n" .
                   "                  character that is not allowed by XML standard.\n\n" .
                   "  -n\n" .
                   "                  Do not generate the XML header:\n" .
                   "                  <?xml version=\"1.0\" encoding=\"UTF-8\"?>\n\n" .
                   "  -r=root-element\n" .
                   "                  Name of XML root element to be used. If not specified,\n" .
                   "                  none will be used.\n\n" .
                   "              --array-name=array-element\n" .
                   "                  'array-element' - the name to be used for JSON arrays.\n" .
                   "                  (Default value is 'array'.)\n\n" .
                   "              --item-name=item-element\n" .
                   "                  'item-name' - the name to be used for items of JSON arrays.\n\n" .
                   "  -s\n" .
                   "                  JSON strings will be transformed into text elements.\n" .
                   "                  By default the elements attributes are used.\n\n" .
                   "  -i\n" .
                   "                  JSON numbers will be transformed into text elements.\n" .
                   "                  By default the elements attributes are used.\n\n" .
                   "  -l\n" .
                   "                  Values of literals 'true', 'false' and 'null' will be\n" .
                   "                  transformed to <true />, <false />, <null /> respectively.\n\n" .
                   "  -c\n" .
                   "                  Activates transformation of XML problematic characters.\n" .
                   "                  < > & \" ' characters will be transformed into: \$lt; \$gt;\n" .
                   "                  \$amp; \$quot; \$apos; respectively.\n\n" .
                   "  -a          --array-size\n" .
                   "                  Adding 'size' attribute to every array from JSON format,\n" .
                   "                  specifying the size of the array.\n\n" .
                   "  -t          --index-items\n" .
                   "                  Adding 'index' attribute to every array's item from JSON\n" .
                   "                  format, specifying the actual index of array's item.\n\n" .
                   "              --start=N\n" .
                   "                  Allows to change starting value for --index-items parameter.\n" .
                   "                  The default value is N = 1. --index-items has to be used.\n\n" .
                   "              --padding\n" .
                   "                  Inputs zeroes into index values from left side, so all the\n" .
                   "                  index values has same minimal width.\n\n".
                   "              --offset-size=N\n".
                   "                  Specifies the number of white spaces used for indentation.\n".
                   "                  By default the indentation is done by 4 space characters.\n\n".
                   "This is the result of the 1st school project @ BUT FIT, IPP course, 2014.\n\n" .
                   "Just like the GNU software, this script is provided 'as it is', without any\n" .
                   "warranty or guarantees. You're free to use it and distribute it under GPLv2.\n\n" .
                   "Author:       Dee'Kej\n" .
                   "Contact:      deekej@linuxmail.org\n" .
                   "Websites:     http://www.fit.vutbr.cz/\n" .
                   "              https://github.com/deekej\n" .
                   "              https://bitbucket.org/deekej\n");
    
    return;
  }}}
  

  # All script initialization (with additional testing if needed).
  function script_init()
  {{{
    global $RET_VAL;

    if (DEBUG == true) {
      # Enabling all warnings for debugging purposes.
      ini_set("display_errors", "On");
      ini_set("error_reporting", E_ALL);
    }
    else {
      ini_set("display_errors", "On");
      ini_set("error_reporting", E_ALL);
    }

    # Setting UTF-8 as encoding for string & regex operations:
    if (mb_internal_encoding("UTF-8") == false || mb_regex_encoding("UTF-8") == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Warning: Failed to set internal or regular expression encoding for multibyte characters,\n");
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
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid XML element name for '--array-name' parameter!\n");
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
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid XML element name for '--item-name' parameter!\n");
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
              exit(ERROR_PARAMS);
            }

            if (intval($value) != floatval($value)) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not an integer!\n");
              exit(ERROR_PARAMS);
            }

            if ($value < 0) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' value is not a positive number!\n");
              exit(ERROR_PARAMS);
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
            if ($value === NULL) {
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
              fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid XML element name for 'root element'!\n");
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
              exit(ERROR_PARAMS);
            }

            if (intval($value) != floatval($value)) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' value is not an integer!\n");
              exit(ERROR_PARAMS);
            }

            if ($value < 0) {
              fwrite(STDERR, SCRIPT_NAME . ": Error: '--offset-size' value is not a positive number!\n");
              exit(ERROR_PARAMS);
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
          if ($PARAMS["problem_chars_translate"] === NULL) {
            $PARAMS["problem_chars_translate"] = true;
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
        # case "--flattening" :
        #   if ($PARAMS["flattening"] === NULL) {
        #     $PARAMS["flattening"] = true;
        #   }
        #   else {
        #     fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '--flattening' parameter!\n");
        #     exit(ERROR_PARAMS);
        #   }
        #
        #  break;


        # #######################
        # case "--error-recovery" :
        #   if ($PARAMS["error_recovery"] === NULL) {
        #     $PARAMS["error_recovery"] = true;
        #   }
        #   else {
        #     fwrite(STDERR, SCRIPT_NAME . ": Error: Duplication of '--error-recovery' parameter!\n");
        #     exit(ERROR_PARAMS);
        #   }
        #
        #   break;


        # #######
        default :
          fwrite(STDERR, SCRIPT_NAME . ": Error: Unknown parameter '" . $param . "' used!\n");
          exit(ERROR_PARAMS);
      }

    }

    # ###################################################################################################################

    # Additional testing of '--start' parameter:
    if ($PARAMS["counter_init"] !== NULL && $PARAMS["index_items"] === NULL) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: '--start' used, but '-t' or '--index-items' is missing!\n");
      exit(ERROR_PARAMS);
    }

    # Additional testing of '--padding' parameter:
    if ($PARAMS["padding"] == true && $PARAMS["index_items"] === NULL) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: '--padding' used, but '-t' or '--index-items' is missing!\n");
      exit(ERROR_PARAMS);
    }


    # Setting default values with additional testing:
    if ($PARAMS["array_name"] === NULL) {
      $PARAMS["array_name"] = "array";
    }

    if ($PARAMS["item_name"] === NULL) {
      $PARAMS["item_name"] = "item";
    }
    

    # Setting default values if needed:
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


    # Setting other non-initialized values to false:
    foreach ($PARAMS as $key => $value) {
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

    if ($file_ptr === false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Specified output file can't be created/rewritten!\n");
      exit(ERROR_OPEN_WRITE);
    }

    $PARAMS["output_file"] = $file_ptr;        # Backing up the file pointer.

    return;
  }}}


  # Callback function, currently for sanity purposes only. In case of opening file with fopen(), appropriate fclose()
  # equivalent would go here. Currently this functions does nothing. If needed, it should be registered with
  # register_shutdown_function().
  function close_input_file()
  {{{
    return;                       # file_get_contents() function does not need opened file descriptor, nothing to do.
  }}}

  
  # Callback function for closing already opened file. This should be registered with register_shutdown_function().
  function close_output_file()
  {{{
    global $PARAMS, $RET_VAL;

    if ($PARAMS["output_file"] != STDOUT && fclose($PARAMS["output_file"]) == false) {
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
# ~~~ INDENT AUXILIARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #
  
  # Generates string of white spaces of specified length. This string is then used by offset_string() function.
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
  function offset_string()
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

  # Classifies the value from the multidimensional object returned by json_decode(). Returns string classifying the
  # item.
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

      if (is_object($item)) {
        if (count((array) $item) != 0) {
          return "JSON_OBJECT";
        }
        else {
          return "JSON_EMPTY_OBJECT";
        }
      }
      else if (is_array($item)) {
        if (count($item) != 0) {
          return "JSON_ARRAY";
        }
        else {
          return "JSON_EMPTY_ARRAY";
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

  # Function tests if given string is a valid XML element name. Returns TRUE if the string is valid, FALSE otherwise.
  function xml_validate_name($string)
  {{{
    # NOTE: Previous & not completely valid solution:
    # return (preg_match('/^(?!XML|\p{P}|\p{N})[_\p{L}\p{N}\.-]+[[:space:]]*$/ui', $string) === 1 ||
    #         preg_match('/^(?!XML|\p{P}|\p{N})[_\p{L}\p{N}\.-]+[[:space:]]+[\p{L}\p{N}\.-_]+="[^"]*"[[:space:]]*$/ui', $string) === 1);
    
    try {
      new DOMElement($string);
      return true;
    }
    
    # In case the name is invalid, exception is raised and caught here:
    catch (DOMException $exception) {
      return false;
    }
    
  }}}


# #################################################################################################################### #

  # Function which will find illegal characters for XML element name and replace them with specified replacement. If the
  # string after replacement is invalid, it exits with error. Otherwise the 'repaired' string is returned.
  function illegal_chars_subst($string)
  {{{
    global $PARAMS;
    
    # Replace starting 'XML' text in any letter case or character, which is not XML_NameStartChar:
    $string = preg_replace('/^([Xx][Mm][Ll]|[^' . XML_NameStartChar . '])/ux', $PARAMS["ill_chars_substitute"], $string);

    # Replace invalid characters from rest of the string:
    $string = preg_replace('/[^' . XML_NameChar . ']/ux', $PARAMS["ill_chars_substitute"], $string);

    if (xml_validate_name($string) == false) {
      fwrite(STDERR, SCRIPT_NAME . ": Error: Invalid XML name after substitution of invalid characters!\n");
      exit(ERROR_CHARS_SUBST);
    }
    
    return $string;
  }}}

  
  # Function which finds the problematic XML characters in given string & replaces them with appropriate
  # & metacharacters, if requested by '-c' script parameter. Returns given or modified string.
  function problematic_chars_subst($string)
  {{{
    global $PARAMS;

    if ($PARAMS["problem_chars_translate"] == false) {
      return $string;
    }

    $string = mb_ereg_replace("&", "&amp;", $string);
    $string = mb_ereg_replace("<", "&lt;", $string);
    $string = mb_ereg_replace(">", "&gt;", $string);
    $string = mb_ereg_replace("\"", "&quot;", $string);
    $string = mb_ereg_replace("'", "&apos;", $string);

    return $string;
  }}}


  # Prints XML string in requested format.
  function xml_print_string($name, $string)
  {{{
    global $PARAMS;

    $string = problematic_chars_subst($string);

    if ($PARAMS["string_transform"] == true) {
      output_write(offset_string() . "<" . $name . ">" . $string . "</" . $name . ">");
    }
    else {
      output_write(offset_string() . "<" . $name . " value=\"" . $string . "\" />");
    }

    return;
  }}}

  
  # Prints XML number in requested format.
  function xml_print_number($name, $value)
  {{{
    global $PARAMS;
    
    if ($PARAMS["number_transform"] == true) {
      output_write(offset_string() . "<" . $name . ">" . $value . "</" . $name . ">");
    }
    else {
      output_write(offset_string() . "<" . $name . " value=\"" . $value . "\" />");
    }

    return;
  }}}

  
  # Prints XML boolean value in requested format.
  function xml_print_boolean($name, $value)
  {{{
    global $PARAMS, $PLUNGE;

    if ($PARAMS["literals_transform"] == true) {
      output_write(offset_string() . "<" . $name . ">");
      
      $PLUNGE++;                # Adjusting indentation.
      output_write(offset_string() . "<" . var_export($value, true) . " />");
      $PLUNGE--;                # Restoring indentation.

      output_write(offset_string() . "</" . $name . ">");
    }
    else {
      output_write(offset_string() . "<" . $name . " value=\"" . var_export($value, true) . "\" />");
    }

    return;
  }}}


  # Prints XML null value in requested format.
  function xml_print_null($name)
  {{{
    global $PARAMS, $PLUNGE;

    if ($PARAMS["literals_transform"] == true) {
      output_write(offset_string() . "<" . $name . ">");

      $PLUNGE++;                  # Adjusting indentation.
      output_write(offset_string() . "<null />");
      $PLUNGE--;                  # Restoring indentation.

      output_write(offset_string() . "</" . $name . ">");
    }
    else {
      output_write(offset_string() . "<" . $name . " value=\"null\" />");
    }

    return;
  }}}


  # Prints empty XML element with given name.
  function xml_print_name_single($name)
  {{{
    output_write(offset_string() . "<" . $name . " />");

    return;
  }}}


  # Prints starting or ending XML element with given name.
  function xml_print_name($name, $start)
  {{{
    if ($start == true) {
      output_write(offset_string() . "<" . $name . ">");
    }
    else {
      output_write(offset_string() . "</" . $name . ">");
    }

    return;
  }}}


  # Prints XML array's name on single line.
  function xml_print_empty_array()
  {{{
    global $PLUNGE, $PARAMS;
    
    $PLUNGE++;
    output_write(offset_string() . "<" . $PARAMS["array_name"] . " />");
    $PLUNGE--;

    return;
  }}}

  
# #################################################################################################################### #

  # Function, which generates index values for array items. It returns string to be used, depending on specified script
  # parameters.
  function generate_index($index, $array_size)
  {{{
    global $PARAMS;

    if ($PARAMS["index_items"] == false) {
      return "";                        # Nothing to do, return empty string - it will not change the array item format.
    }

    # Getting new index value, depending on initial counter value:
    $index_new = $index + $PARAMS["counter_init"];
    
    # Filling used counters with zeroes, if requested:;
    if ($PARAMS["padding"] == true) {
      $max_num_width = mb_strwidth(strval($array_size + $PARAMS["counter_init"]));    # Getting maximum width of number.
      $format = "%0" . $max_num_width . "u";                                          # Generating format string.
      $index_new = sprintf($format, $index_new);                                      # Updating the index string.
    }
    
    return " index=\"" . $index_new . "\"";
  }}}


  # Prints XML array item containing string. Uses the name specified during script invocation, if needed.
  function xml_print_item_string($index, $string)
  {{{
    global $PARAMS;

    $string = problematic_chars_subst($string);

    if ($PARAMS["string_transform"] == true) {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . ">" . $string . "</" . $PARAMS["item_name"] . ">");
    }
    else {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . " value=\"" . $string . "\" />");
    }

    return;
  }}}


  # Prints XML array item containing number.
  function xml_print_item_number($index, $value)
  {{{
    global $PARAMS;
    
    if ($PARAMS["number_transform"] == true) {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . ">" . $value . "</" . $PARAMS["item_name"] . ">");
    }
    else {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . " value=\"" . $value . "\" />");
    }

    return;
  }}}


  # Prints XML array item containing boolean value.
  function xml_print_item_boolean($index, $value)
  {{{
    global $PARAMS, $PLUNGE;

    if ($PARAMS["literals_transform"] == true) {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . ">");

      $PLUNGE++;                  # Adjusting indentation.
      output_write(offset_string() . "<" . var_export($value, true) . " />");
      $PLUNGE--;                  # Restoring indentation.

      output_write(offset_string() . "</" . $PARAMS["item_name"] . ">");
    }
    else {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . " value=\"" . var_export($value, true) . "\" />");
    }

    return;
  }}}

  
  # Prints XML array item containing null value.
  function xml_print_item_null($index)
  {{{
    global $PARAMS, $PLUNGE;

    if ($PARAMS["literals_transform"] == true) {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . ">");

      $PLUNGE++;                  # Adjusting indentation.
      output_write(offset_string() . "<null />");
      $PLUNGE--;                  # Restoring indentation.

      output_write(offset_string() . "</" . $PARAMS["item_name"] . ">");
    }
    else {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . " value=\"null\" />");
    }

    return;
  }}}


  # Prints XML array item's name. The item contain nothing.
  function xml_print_item_single($index)
  {{{
    global $PARAMS;

    output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . " />");

    return;
  }}}


  # Prints XML array start or end item element name.
  function xml_print_item($index, $start)
  {{{
    global $PARAMS;

    if ($start == true) {
      output_write(offset_string() . "<" . $PARAMS["item_name"] . $index . ">");
    }
    else {
      output_write(offset_string() . "</" . $PARAMS["item_name"] . ">");
    }

    return;
  }}}
  

# #################################################################################################################### #
# ~~~ PRIMARY FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
# #################################################################################################################### #

  # Main function for processing given object (generated by json_decode() function) into appropriate XML format.
  # It uses recursive algorithm for printing any plunged values.
  function json2xml($item)
  {{{
    global $PLUNGE, $PARAMS;

    $ret_val = json_classify($item);
    
    # JSON object was given to function:
    if ($ret_val == "JSON_OBJECT") {

      # Iterating through the whole object:
      foreach ($item as $key => $value) {
        $name = illegal_chars_subst($key);        # Substituting illegal characters.

        switch (json_classify($value)) {
          case "JSON_STRING" :
            xml_print_string($name, $value);
            break;

          case "JSON_FLOAT" :
            $value = intval(floor($value));       # Rounding the float value downwards, printing it below.

          case "JSON_INTEGER" :
            xml_print_number($name, $value);
            break;

          case "JSON_BOOLEAN" :
            xml_print_boolean($name, $value);
            break;

          case "JSON_NULL" :
            xml_print_null($name);                # Printing only item name.
            break;

          case "JSON_EMPTY_ARRAY" :
            xml_print_name($name, true);          # Printing starting element name.
            xml_print_empty_array();              # Printing only name of an array on single line.
            xml_print_name($name, false);         # Printing ending element name.
            break;

          case "JSON_EMPTY_OBJECT" :
            xml_print_name_single($name);
            break;

          case "JSON_OBJECT" :
          case "JSON_ARRAY" :
            xml_print_name($name, true);          # Printing starting element name.

            $PLUNGE++;                            # Adjusting indentation.
            json2xml($value);                     # Recursive call to this function.
            $PLUNGE--;                            # Restoring indentation.

            xml_print_name($name, false);         # Printing ending element name.
            break;

          default :
            fwrite(STDERR, SCRIPT_NAME . ": Error: Unhandled return value of json_classify() in json2xml() function!\n");
            exit(ERROR_JSON2XML);
            break;
        }
      }
    }
    # JSON array was given to the function - we use different functions for output:
    else if ($ret_val == "JSON_ARRAY") {
      $array_size = count($item);

      if ($PARAMS["array_size"] == true) {
        output_write(offset_string() . "<" . $PARAMS["array_name"] . " size=\"" . $array_size . "\">");
      }
      else {
        output_write(offset_string() . "<" . $PARAMS["array_name"] . ">");
      }
      
      $PLUNGE++;                                        # Adjusting indentation.

      foreach ($item as $key => $value) {
        $index = generate_index($key, $array_size);

        switch (json_classify($value)) {
          case "JSON_STRING" :
            xml_print_item_string($index, $value);
            break;

          case "JSON_FLOAT" :
            $value = intval(floor($value));             # Rounding the float value downwards, printing it below.

          case "JSON_INTEGER" :
            xml_print_item_number($index, $value);
            break;

          case "JSON_BOOLEAN" :
            xml_print_item_boolean($index, $value);
            break;

          case "JSON_NULL" :
            xml_print_item_null($index);
            break;

          case "JSON_EMPTY_ARRAY" :
            xml_print_item($index, true);               # Printing starting element name.
            xml_print_empty_array();                    # Printing only name of an array on single line.
            xml_print_item($index, false);              # Printing ending element name.
            break;

          case "JSON_EMPTY_OBJECT" :
            xml_print_item_single($index);              # Printing only item name.
            break;

          case "JSON_OBJECT" :
          case "JSON_ARRAY" :
            xml_print_item($index, true);               # Printing starting element name.

            $PLUNGE++;                                  # Adjusting indentation.
            json2xml($value);                           # Recursive call to this function.
            $PLUNGE--;                                  # Restoring indentation.
            
            xml_print_item($index, false);              # Printing ending element name.
            break;

          default :
            fwrite(STDERR, SCRIPT_NAME . ": Error: Unhandled return value of json_classify() in json2xml() function!\n");
            exit(ERROR_JSON2XML);
            break;
        }
      }
      
      $PLUNGE--;                                        # Restoring indentation.
      output_write(offset_string() . "</" . $PARAMS["array_name"] . ">");
    }
    else if ($ret_val == "JSON_EMPTY_ARRAY") {
      xml_print_empty_array();                          # Printing only name of an array on single line.
    }
    else if ($ret_val == "JSON_EMPTY_OBJECT") {
      return;
    }
    # Getting here indicates wrong use of this function or some serious error:
    else {
      fwrite(STDERR, SCRIPT_NAME . ": Internal Error: Improper use of json2xml() function!\n");
      exit(ERROR_INTERNAL);
    }
    
    return;
  }}}


# #################################################################################################################### #
# ### START OF THE SCRIPT EXECUTION ################################################################################## #
# #################################################################################################################### #
  
  script_init();
  params_process();

  # ################################################################################################################## #

  open_input_file();
  register_shutdown_function("close_input_file");       # Registering function to be called at any exit.

  open_output_file();
  register_shutdown_function("close_output_file");      # Registering function to be called at any exit.

  $string = read_file_content();                        # Reading the whole file content into a string.
  
  # This test is necessary, because json_decode() returns empty object also for empty file.
  if (strlen($string) == 0) {
    fwrite(STDERR, SCRIPT_NAME . ": Error: The empty input is not a valid JSON format!\n");
    exit(ERROR_FORMAT);
  }

  # Checking the encoding:
  if (mb_check_encoding($string, "UTF-8") == false) {
    fwrite(STDERR, SCRIPT_NAME . ": Error: The input file has not 'UTF-8' encoding!\n");
    exit(ERROR_FORMAT);
  }

  # ################################################################################################################## #

  $json_object = json_decode($string);                  # Decoding file content string into an object.

  # Any error during decoding?
  switch (json_last_error()) {
    case JSON_ERROR_NONE :
      # No ERROR, proceed.
      break;

    case JSON_ERROR_DEPTH :
      fwrite(STDERR, SCRIPT_NAME . ": Error: The maximum stack depth for JSON format decoding has been exceeded!\n");
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
  
  # ################################################################################################################## #

  if ($PARAMS["generate_header"] == true) {
    output_write(XML_HEADER);
  }

  # If the size of properly generated object is 0, then the input JSON file had only one empty object: {} or []
  if (is_object($json_object) && count((array) $json_object) == 0) {
    if ($PARAMS["root_element"] != false) {
      output_write("<" . $PARAMS["root_element"] . " />");  # Single root element.
    }

    exit($RET_VAL);                                         # Nothing more to do.
  }

  if ($PARAMS["root_element"] != false) {
    output_write("<" . $PARAMS["root_element"] . ">");
    $PLUNGE++;
  }

  # ################################################################################################################## #

  offset_string_init();         # Initialization of offset string used for indentation.
  json2xml($json_object);        # Format converting.

  # ################################################################################################################## #

  if ($PARAMS["root_element"] != false) {
    output_write("</" . $PARAMS["root_element"] . ">");
  }

  exit($RET_VAL);
?>

