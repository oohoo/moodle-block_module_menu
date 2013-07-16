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

//ERRORS ARE SUPRESSED - Change if developing or issues arise
error_reporting(0);

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
    case "search"://filter currently displayed options
       dd_content_search($json);
        break;  
    
    //operation provided was invalid... do nothing!
    default:
        break;
}

/**
 * Searches and outputs the menu options the dd content instances based on the given json data
 * 
 * @global moodle_database $DB
 * @param string $json The json that contains the state of the dd content instance
 */
function dd_content_search($json) {

    //get block instance from db
    $dd_content = get_block_instance($json->blockid);
    
    //outputs the module options that match the provided search string
    $dd_content->generate_mod_options($json->include_name, $json->search); 
}

/**
 * Updates the dd content instances based on the given json data
 * 
 * @global moodle_database $DB
 * @param string $json The json that contains the state of the dd content instance
 */
function dd_content_update($json) {

    //get block instance from db
    $dd_content = get_block_instance($json->blockid);
    
    //get instance config
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
 * Retrieves the instance of the block based on the given blockid
 * 
 * @global moodle_database $DB
 * @param int $blockid
 * @return block_base Instance of block
 */
function get_block_instance($blockid) {
    global $DB;
    
    //get block instance from db
    $block_instance = $DB->get_record('block_instances', array('id'=>$blockid));
    
    //create the block object instance
    $dd_content = block_instance('dd_content', $block_instance);
    return $dd_content;
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
