<?php
/**
 * *************************************************************************
 * *                     Drag & Drop Content                              **
 * *************************************************************************
 * @package blocks                                                        **
 * @subpackage dd_content                                                 **
 * @name Drag & Drop Content                                              **
 * @copyright oohoo.biz                                                   **
 * @link http://oohoo.biz                                                 **
 * @author Dustin Durand                                                  **
 * @license                                                               **
 * http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later               **
 * *************************************************************************
 * ************************************************************************ */

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/blocks/dd_content/lib.php");

/**
 * The form used for administrator settings for the dd_content block
 * 
 */
class settings_admin_form extends moodleform {
    
    //The configuration data for block dd content
    private $config_data;
    
    /**
     * General Constructor
     * 
     * @param array $config_data block dd content
     */
    function __construct($config_data) {
        $this->config_data = $config_data;
        parent::__construct();
        load_config_forms_js(); 
        }
        
    /**
     * Form Definition
     */
    function definition() {
        $mform =& $this->_form; // Don't forget the underscore! 
        dd_content_setup_settings_form($this, $mform, count($this->config_data));
    }
    
}

?>
