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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/blocks/dd_content/lib.php");

/**
 * Provides the main server functionality for the the dd content block
 */
class block_dd_content extends block_base {

    /*
     * Initaliation function for the block
     */
    public function init() {

        //set title
        $this->title = get_string('dd_content', 'block_dd_content');

    }
    
    /**
     * Runs the moment that an instance is created
     * 
     * In our case we are taking the instance config, and converting the serialized
     * data information back into an stdclass object
     */
    public function specialization() {
        //run parent method
        parent::specialization();
        
        //get instance config
        $config = $this->config;
        
        //if instance config exists and the data property is present, deserialize
        //the data property(contains filter info) back into an object
        if($config && isset($config->data)) {
            $config->data = dd_content_deserialize($config->data);
        }
        
    }

    /**
     * Outputs the main content for the dd content content for the block
     * 
     * This includes:
     *  -the actual oritentation options content in the block
     *  -the menu details output at the top of the page
     * 
     * @global object $CFG
     * @global moodle_page $PAGE
     * @return object standard block object containing content
     */
    public function get_content() {
        global $CFG, $PAGE;
        
        $content = '';//No Content
        
        //if in edit mode: output a header and the orientation menu in the block
        if ($PAGE->user_is_editing()) {
            
            $this->load_jQuery();//loads jquery         

            //oritentation header
            $content .= html_writer::start_tag('h4', array('class' => 'dd_content_block'));
                $content .= get_string('editing_block_display', 'block_dd_content');
            $content .= html_writer::end_tag('h4');

            //orientation options
             $content .=  html_writer::start_tag("div", array('class'=>'dd_content_position'));
                //horiz orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/dd_content/pix/horiz.png", 'id'=>'dd_content_horz_btn', 'class'=>'dd_content_btn'));
                //vert orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/dd_content/pix/vert.png", 'id'=>'dd_content_vert_btn', 'class'=>'dd_content_btn'));
                //vert orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/dd_content/pix/bottom.png", 'id'=>'dd_content_bot_btn', 'class'=>'dd_content_btn'));
                //no menus
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/dd_content/pix/none.png", 'id'=>'dd_content_none_btn', 'class'=>'dd_content_btn'));
             $content .=  html_writer::end_tag("div");
            
             //search menu
             $content .=  html_writer::start_tag("div", array('class'=>'dd_content_position'));
                   $text = get_string('editing_block_search', 'block_dd_content');
                   $content .= html_writer::empty_tag('input', array('class' => 'dd_content_search', 'type'=>'text', 'value'=>$text, 'empty'=> '1'));
             $content .=  html_writer::end_tag("div");
             
             //filter dropdowns
             $content .=  html_writer::start_tag("div", array('class'=>'dd_content_position'));
                   $content .= $this->generate_filter_dropdowns();
             $content .=  html_writer::end_tag("div");
             
             //get the default filter string
             $default = $this->get_default_filter_search();
             //do not want to send null as the search default
             $cleaned_default = ($default == null) ? "" :  $default;
             
             //filter reset
             $content .=  html_writer::start_tag("div", array('class'=>'dd_content_position dd_content_filter_reset'));
                   //reset will be a link
                   $content .= html_writer::start_tag('a', array('class' => 'dd_content_filter_reset', 'value'=>$cleaned_default));
                        $content .= get_string('reset');
                   $content .= html_writer::end_tag('a');
             $content .=  html_writer::end_tag("div");
             
        }
        
        //create object to return content
        $this->content = new stdClass;
        //assign block text
        $this->content->text = $content;

        //output the elements that will be located at top of the page
        $this->output_dd_content();


        return $this->content;
    }

    
    
    /**
     * This function faciliates the output of the dd content contents that are located
     * at the top of the page
     */
    private function output_dd_content() {
        //a div that will contain the dd content building blocks, but is ALWAYS hidden
        echo html_writer::start_tag("div", array('id' => 'dd_content_wrapper', 'class' => 'dd_content_wrapper', 'style' => 'display:none'));
            //horiz menu instance
            $this->generate_dd_content('dd_content_horiz_menu_wrapper', 'ui-icon-triangle-1-w', 'ui-icon-triangle-1-e');
            //vert menu instance
            $this->generate_dd_content('dd_content_vert_menu_wrapper', 'ui-icon-triangle-1-n','ui-icon-triangle-1-s', true, "vert");
            //bot menu instance
            $this->generate_dd_content('dd_content_bot_menu_wrapper', 'ui-icon-triangle-1-w', 'ui-icon-triangle-1-e');
            //landing pad template
            $this->generate_landing_pad();
  
        echo html_writer::end_tag("div");
    }

    /**
     * This method outputs a set of server-side information for use by the browser side
     * scripts.
     * 
     * @global object $COURSE
     * @global object $CFG
     */
    private function dd_content_inline_js() {
        global $COURSE, $CFG;
        
        echo "<script>";
           //avoid possible overwriting
           echo "if(typeof dd_content_php == 'undefined') var dd_content_php = new Array();"; //global js object
        
           //server/course info
           echo "dd_content_php['course'] = $COURSE->id ;"; //course id
           echo "dd_content_php['wwwroot'] = '$CFG->wwwroot';"; //server address
           
           //LANGS
           echo "dd_content_php['invalid_section_id'] = '".get_string('invalid_section_id','block_dd_content')."';"; 
           echo "dd_content_php['ajax'] = '$CFG->wwwroot/blocks/dd_content/ajax_controller.php';";
           echo "dd_content_php['orientation'] = '".$this->get_menu_oritentation()."';";
           echo "dd_content_php['search_empty'] = '".get_string('editing_block_search','block_dd_content')."';";
           
           echo "</script>";
    }
    
    /**
     * Returns the current orientation of the menu for the dd content instance
     * 
     * If the value has never been set then its horiz by default
     */
    private function get_menu_oritentation() {
        //get instance config
        $data = $this->config;
        
        //if an orientation exists return it
        if(isset($this->config) && isset($data->orientation)) {
            return $data->orientation;
        } else {//if never been set - default to horiz
            return "horiz";
        }
        
    }
    
    /**
     * Generates the landing pad template that will be cloned for use as the droppable location for
     * our draggable modules.
     * 
     * @global object $OUTPUT
     */
    private function generate_landing_pad() {
        global $OUTPUT, $CFG;
        //get text for landing page
        $text = get_string('landing_pad_text','block_dd_content');
        
        //create landing pad div
        echo html_writer::start_tag("div", array('class' => 'dd_content_landing_pad', 'style'=>'display:none'));
         
            echo html_writer::start_tag("div", array('class' => 'dd_content_landing_pad_add'));
                //same icon as the moodle drag/drop add files in sections
                echo html_writer::empty_tag("img", array('alt'=>$text,'src'=>$OUTPUT->pix_url('t/addfile'),'class' => 'dd_content_landing_pad_icon'));
                //landing page message
                echo html_writer::start_tag("span", array('class' => 'dd_content_landing_pad_text'));
                    echo $text;
                echo html_writer::end_tag("span");
                
            echo html_writer::end_tag("div");
                
            echo html_writer::start_tag("div", array('class' => 'dd_content_landing_pad_loading', 'style'=>'display:none'));
                //same icon as the moodle drag/drop add files in sections
                echo html_writer::empty_tag("img", array('alt'=>$text,'src'=>"$CFG->wwwroot/blocks/dd_content/pix/loading.gif",'class' => 'dd_content_landing_pad_icon'));
                //landing page message
                echo html_writer::start_tag("span", array('class' => 'dd_content_landing_pad_text'));
                    echo get_string('loading','block_dd_content');
                echo html_writer::end_tag("span");
                
            echo html_writer::end_tag("div");
            
        echo html_writer::end_tag("div");
    }
    
    /**
     * Generates an unqiue instance of the dd content with given properties.
     * 
     * @param string $wrapper_id An UNQIUE id that will be used as the html id 
     * @param string $icon_t_l_class An jquery icon class to be used as the top/left icon
     * @param string $icon_b_r_class An jquery icon class to be used as the bottom/right icon
     * @param bool $include_name On true the name is included, on false its not included
     */
    private function generate_dd_content($wrapper_id, $icon_t_l_class, $icon_b_r_class, $include_name = true) {
        
        //create main wrapper with given unique id
        echo html_writer::start_tag("div", array('id' => $wrapper_id));
              
            //left/top icon
            echo html_writer::start_tag("div", array('class' => 'dd_content_left'));
                 //jquery based icon
                 echo html_writer::start_tag("span", array('class' => 'ui-icon '.$icon_t_l_class));
                 echo html_writer::end_tag("span");
            echo html_writer::end_tag("div");

            //main menu container
            echo html_writer::start_tag("div", array('class' => 'dd_content_container'));
                //get all mod information
            
            $this->generate_mod_options($include_name);
                
            echo html_writer::end_tag("div");

            //create right icon
            echo html_writer::start_tag("div", array('class' => 'dd_content_right'));
                //jquery based icon
                echo html_writer::start_tag("span", array('class' => 'ui-icon '.$icon_b_r_class));
                echo html_writer::end_tag("span");
            echo html_writer::end_tag("div");
            
            
        echo html_writer::end_tag("div");
    }
    
    /**
     * Generates and returns the html for the filter dropdowns
     * 
     * @return string the html needed for the filter dropdowns
     */
    private function generate_filter_dropdowns() {
        $html = '';//no html
        
        //instance filter dropdown
        $instance_config = $this->get_settings_config_data();//get instance config
        if(isset($instance_config) && isset($instance_config->data)//if config exists, and data has been saved
                && count($instance_config->data) > 0) {//there is actually a filter saved
            
            $text = get_string('instance_filters', 'block_dd_content');//get label text
            $html .= $this->generate_filter_dropdown($text, $instance_config->data);//generate dropdown
        }

        //global filter dropdown
        $global_config_data = dd_content_get_admin_config();//grab global block config DATA (not the config itself)
        if(count($global_config_data) > 0) {//if a filter exists
            
            $text = get_string('global_filters', 'block_dd_content');//get global filter label
            $html .= $this->generate_filter_dropdown($text, $global_config_data);//create dropdown
        }
        
        
        return $html;
    }
    
    /**
     * Generates the html for a filter dropdown given an array of filter records and a label
     * 
     * @param string $label The label to be put above the dropdown
     * @param array $filters An array of objects(records) that contain the name, mods, and default
     * @return string The html for the filter dropdown
     */
    private function generate_filter_dropdown($label, $filters) {
        $html = '';
        
        //generate label
        $html .= html_writer::start_tag("h4", array('class'=>'dd_content_block'));
            $html .= $label;
        $html .= html_writer::end_tag("h4");    
        
        //start select
        $html .= html_writer::start_tag("select", array('class'=>'dd_content_filter_select'));
        
        //always create the all option
        $html .= html_writer::start_tag("option", array('value'=>''));//an empty string always returns all options
                $html .= get_string('all');
        $html .= html_writer::end_tag("option");
        
        //itterate through each filter and add as an option
        $filter_count = 0;//keep count of number of filters
        foreach($filters as $filter) {
            $value = $this->get_filter_search($filter);//the comma delim list of mod names it the value
            $display = $filter->name;//the name of filter
            
            
            $options = array('value'=>$value);//add value to option
            if($filter->default == 1)//set selected if default
               $options['selected']='SELECTED';
            
            //add filter as select option
            $html .= html_writer::start_tag("option", $options);
                $html .= $display;
            $html .= html_writer::end_tag("option");
            
            //inc number of filters
            $filter_count++;
        }
        
        //end select
        $html .= html_writer::end_tag("select");
        
        //if no filters are present (other than all) don't return any html
        //aka - no dropdown
        if($filter_count == 0) return '';
        
        //return resulting html
        return $html;
        
    }
    
    /**
     * Outputs the module options
     * 
     * If search is null, then a default is used (if present).
     * Note: Instance Default > Global Default > No Filter
     * 
     * @param bool $include_name if true the name is included
     */
    public function generate_mod_options($include_name, $search = null) {
        
        if($search === null)//if no search is present, use the default - if there is one
           $search = $this->get_default_filter_search();
        
        //get the information for all mods that match search
        $mods = $this->get_mods_information($search);

        //for each element - create the module option
        foreach ($mods as $mod) {
            $this->create_mod_option($mod, $include_name);
        }
    }
    
    /**
     * Returns the default filter for the current dd_content
     * 
     * If an instance default is set - it takes first presedence.
     * If no instance default is set, then the global default takes presendence.
     * 
     * If no default is set, this function returns null.
     * 
     * @return string on default found, null on no default
     */
    private function get_default_filter_search() {
        
        //get instance config
        $instance_config = $this->get_settings_config_data();
        
        //if there is saved filters
        if(isset($instance_config) && isset($instance_config->data))
            $search = $this->get_default_search($instance_config->data);//get default
        
        //if a default for instance is found return it
        if($search != null) return $search;
        
        //get global config data for dd_content blocks
        $global_config_data = dd_content_get_admin_config();
        
        //tty to get a default from global
        $search = $this->get_default_search($global_config_data);
        
        //if a default is found return it
        if($search != null) return $search;

        //no default is found - return null
        return null;
        
    }
    
    /**
     * Attempts to find the FIRST filter with the default set in a given 
     * array of filters
     * 
     * @param array $filters array of filter records that contain name, mods, default properties
     * @return default string on success, null on no default found
     */
    private function get_default_search($filters) {
        if(!is_array($filters)) return null;//if not an array - return null
        if(count($filters) === 0) return null;//no filters - return null
        
        //for each filter look for the first that has default set
        foreach($filters as $filter) {
            if($filter->default == 1) {
                return $this->get_filter_search($filter);//return its filter string
            }
        }
        
        //no filters are set to default
        return null;
    }
    
    /**
     * Takes a filter record (contains name, mods, default properties) and returns
     * that filter's search string.
     * 
     * @param object $filter filter record
     * @return string search string
     */
     private function get_filter_search($filter) {
        $names = array();//assume no mod names
        $modnames = get_module_types_names(); //get all the names avaliable
        $mods = $filter->mods;//get all mods in this filter

        //go through each mod in the filter and and keep its name
        foreach ($mods as $mod) {
            array_push($names, $modnames[$mod]);
        }

        //convert list of names into a comma deliminated string of names
        return implode(",", $names);
    }

    /**
     * Returns a set of all the module information
     * 
     * @global object $COURSE
     * @return array of modules metadata info
     */
    private function get_mods_information($search) {
        global $COURSE;
        $modnames = get_module_types_names(); //get all the names avaliable for this course
        
        //start with no names
        $filtered_names = array();

        //if the search is null, or empty then assume no filtering
        if (!empty($search)) {
            
            //generate the search regex pattern based on search string
            $pattern = $this->create_search_pattern($search);
            
            //go through all the possible modules possible
            foreach ($modnames as $modname=>$name) {
                
                //for each one that matches our search - add it to our filtered names list
                if (preg_match($pattern, $name))
                    $filtered_names[$modname] = $name;
            }
        } else {//no filtering - use all mods
            $filtered_names = $modnames;
        }

        //get module details for all desired modules
        $modules = get_module_metadata($COURSE, $filtered_names); //get all metadata for the given names

        //return module info
        return $modules;
    }
    
    /**
     * Converts a filter's search string into a regex pattern that can be used
     * to match module names.
     * 
     * The search string is broken up by commas(comma delimited) and converted to
     * regex or's. 
     * ex: A,B,C => /A|B|C/i (A or B or C) - case insensitive
     * 
     * @param string $search filter search string (comma delimited)
     * @return string the regex pattern for the given search string
     */
    private function create_search_pattern($search) {
        $pattern = '/';//start regex with inital identifier
        
        //break string into pieces based on comma delimited
        $groupings = explode(",", $search);
        
        $is_first = true;//identify the first itteration
        foreach($groupings as $grouping) {
            //on the first itteration - don't add the or operator
            if(!$is_first) $pattern .= "|";
            
            $is_first = false;//not the first itteration anymore
            $pattern .= preg_quote(trim($grouping));//add substring piece to patter
        }
        
        $pattern .= '/i';//end identifier and make case insensitive
        
        //return generated regex pattern
        return $pattern;
    }

    /**
     * Generates a dd content option for a given module
     * 
     * @global moodle_database $DB
     * @param object $mod A list of stdClass objects containing metadata about each module
     * @param bool $include_name on true the name is output, otherwise its included only as an image alt
     */
    private function create_mod_option($mod, $include_name = true, $additional_class = '') {
        global $DB, $OUTPUT;

        $names = get_module_types_names();//get all module full names
        $name = $names[$mod->name];//get this modules full name

        //outer wrapper the dd content option - modname is used as an identifer in the later JS
        echo html_writer::start_tag("div", array('modname' => $mod->name, 'class' => 'dd_content_mod_wrap ' . $additional_class));
            //move indicating icon
            echo html_writer::start_tag("span", array('class' => 'dd_content_mod_mov_icon ui-icon ui-icon-arrow-4'));
            echo html_writer::end_tag("span");

            //actual module information dic
            echo html_writer::start_tag("div", array('class' => 'dd_content_mod'));

                //container for module icon and help
                echo html_writer::start_tag("div", array('class' => 'dd_content_img_hlp'));
                    //mod icon
                    echo $OUTPUT->pix_icon('icon', $name, $mod->name, array('class' => 'dd_content_icon'));
                    //help DIV
                    echo html_writer::start_tag("div", array('class' => 'dd_content_help'));
                        echo get_string('help');
                    echo html_writer::end_tag("div");
                echo html_writer::end_tag("div");

            echo html_writer::end_tag("div");
            
            //conditionally add module name at bottom of the option
            if ($include_name) {
                echo html_writer::start_tag("div", array('class' => 'dd_content_mod_name'));
                echo $name;
                echo html_writer::end_tag("div");
            }

            //if the module has help included, we add a hidden DIV to contain the text (loaded into dialog later)
            if (isset($mod->help)) {
                echo html_writer::start_tag("div", array('title' => '', 'class' => 'dd_content_help_dialog', 'style' => 'display:none'));
                    echo format_text($mod->help, $format = FORMAT_MOODLE);
                echo html_writer::end_tag("div");
            }

        echo html_writer::end_tag("div");
    }

    /**
     * No Instance Config
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * No Custom Config
     */
    function has_config() {
        return true;
    }

    /**
     * Lets moodle know this block is always empty when not editing, and should be visible
     * when editing.
     * 
     * @global moodle_page $PAGE
     * @return boolean
     */
    public function is_empty() {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {//if editing - show block
            return true;
        } else {//if not editing - don't show
            return false;
        }
    }

    /**
     * ONLY 1 Instance per course-view
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Only show on the course-view
     */
    public function applicable_formats() {
        return array('course-view' => true);
    }

    /**
     * Loads jQuery based on if its moodle 2.5 or pre-moodle 2.5
     * @global moodle_page $PAGE
     * 
     */
    function load_jQuery() {
        global $PAGE, $DB, $COURSE;

        $this->dd_content_inline_js(); //some inline JS for php info

        if (moodle_major_version() >= '2.5') {//use moodle's built in if > moodle 2.5
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('migrate');
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
        } else {//need to include jquery if pre moodle 2.5
            //More Ugly Stuff to make it slightly more 2.4 friendly with course menu format...   
            if ($COURSE->format != 'course_menu') {
                $PAGE->requires->js("/blocks/dd_content/jquery/core/jquery-ui.min.js");
                $PAGE->requires->css("/blocks/dd_content/jquery/core/themes/base/jquery.ui.all.css");
            }
        }

        $PAGE->requires->js("/blocks/dd_content/dd_content.js");
    }

    /**
     * Returns the instance configuration for a given block instance
     * 
     * @return \stdClass
     */
    function get_settings_config_data() {
        $config = $this->config;//get instance config

        //if config doesn't exist - create it
        if (!isset($config)) {
            $config = new stdClass();
        }

        //if data doesn't exist = make it an empty array
        if (!isset($config->data))
            $config->data = array();

        //return config
        return $config;
    }

    /**
     * Saved the given data into the instance config
     * 
     * Can save current instances of the config, along with properly formatted
     * mform post submissions (edit_form.php/settings_admin_form.php).
     * 
     * @param object $data
     * @param type $nolongerused 
     */
    function instance_config_save($data, $nolongerused = false) {

        //If the is_form_submission or config_is_form_submission property is set in the object,
        //then this is a valid form submission that will be saved to the config
        if (isset($data->is_form_submission) || isset($data->config_is_form_submission)) {
            
            //process form data submission into a data string
            $data_string = dd_content_process_settings_form($data);

            $config_data = new stdClass();//create a config object
            $config_data->data = $data_string;//set the data to be our processed string
            
            //add any non-submission fields back into config based on existing config
            //ex: orientation
            dd_content_add_non_standard_form_data($this, $config_data);
            parent::instance_config_save($config_data);
        } else {//In the case its not set, then we are updating the configuration
            //in specialization its unserialized - needs to be re-serialized before savint
            $data->data = dd_content_serialize($data->data);
            parent::instance_config_save($data);
        }
    }
    
}

?>