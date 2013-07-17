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
class block_dd_content_edit_form extends block_edit_form {

    
    /*
     * General Constructor
     */
    public function __construct($actionurl, $block, $page) {
        parent::__construct($actionurl, $block, $page);
        load_config_forms_js(); 
    }
    
    /**
     * Form Definition
     */
    protected function specific_definition($mform) {
        $config = $this->block->get_settings_config_data();//get instance data
        $mform = & $this->_form; // Don't forget the underscore! 
        
        //load mform elements
        dd_content_setup_settings_form($this, $mform, count($config->data));
    }

    /**
     * Overriding display to allow last minutes addition of default values
     */
    public function display() {
        //get instance config data
        $config = $this->block->get_settings_config_data();

        //create defaults object
        $data = dd_content_create_mform_data(null, $config->data);

        /*
         * This is done to eliminate errors, that seem to stem from code that is now unused.
         * They seem to have no effect on the form, but will cause errors is not included.
         */
        $blockfields = array('showinsubcontexts', 'pagetypepattern', 'subpagepattern', 'parentcontextid',
            'defaultregion', 'defaultweight', 'visible', 'region', 'weight');

        //add elements with no defaults
        foreach ($blockfields as $blockfield) {
            $data->$blockfield = null;
        }

        //add defaults to form
        $this->set_data($data);

        //display form
        parent::display();
    }

}

?>
