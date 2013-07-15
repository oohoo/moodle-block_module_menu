<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
 * @package mod                                                          **
 * @subpackage chairman                                                  **
 * @name Chairman                                                        **
 * @copyright oohoo.biz                                                  **
 * @link http://oohoo.biz                                                **
 * @author Dustin Durand                                                 **
 * @license                                                              **
 * http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later              **
 * *************************************************************************
 * ************************************************************************ */

/**
 * This script handles the various ajax functionality used by this dd content block
 */

//ERRORS ARE SUPRESSED - Change if development required or issues arrise
//error_reporting(0);

require_once('../../config.php');

$json_string = required_param('dd_content_json', PARAM_RAW);//json data used in the ajax calls
$operation = required_param('operation', PARAM_TEXT);//the ajax operation to be completed
$json = json_decode($json_string);//convert json data to an object

//set page url
$PAGE->set_url("/ajax_controller.php");

// On failure we will always return an empty json element
try {
    require_course_login($json->course, false, NULL, false, true);
} catch (Exception $e) {
    $empty = array();
    return json_encode($empty);
}


//determine what to do during this ajax call
switch($operation) {
    case "update"://update/save current layout of the course menu
      dd_content_update($json);
        break;
    
    
    //operation provided was invalid... do nothing!
    default:
        break;
}

/**
 * Updates the dd content instances based on the given json data
 * 
 * @global moodle_database $DB
 * @param string $json The json that contains the state of the dd content instance
 */
function dd_content_update($json) {
    global $DB;

    //get block instance from db
    $block_instance = $DB->get_record('block_instances', array('id'=>$json->blockid));
    
    //create the block object instance
    $dd_content = block_instance('dd_content', $block_instance);
    $config = $dd_content->config;
    
    //if config doesn't exist - create it
    if(!isset($config)) {
        $config = new stdClass();
    }
    
    //update orientation in config
    $config->orientation = $json->orientation;
    
    //update config!
    $dd_content->instance_config_save($config);
    
}

/**
 * Generates an empty json string
 * @return string
 */
function empty_json() {
    $empty = array();
    return json_encode($empty);
}

?>
