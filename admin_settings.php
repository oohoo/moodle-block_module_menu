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


require(dirname(__FILE__) . '../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib.php');
require_once('settings_admin_form.php');

//Setup as am admin external page
admin_externalpage_setup('block_dd_content');

//get the current configuration data
$config_datas = dd_content_get_admin_config();

//create our header form
$mform = new settings_admin_form($config_datas);

//submission occured
if ($data = $mform->get_data()) {
    
    //process the data that was submitted into a data string
    $config_data_string = dd_content_process_settings_form($data);
    
    //save under data for the dd_content global config
    set_config("data", $config_data_string, 'block_dd_content');//save config
    
    //redirect
    redirect($CFG->wwwroot . "/blocks/dd_content/admin_settings.php");
    return;
}
 
/**
 * No Submit, Load Form
 */
$data = dd_content_create_mform_data($data, $config_datas);


// Set the initial values, for example the existing data loaded from the database.
$mform->set_data($data);

//output form
echo $OUTPUT->header();

//output form
$mform->display();

//display footer
echo $OUTPUT->footer();

?>
