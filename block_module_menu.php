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

/**
 * Provides the main server functionality for the the module menu block
 */
class block_module_menu extends block_base {

    /*
     * Initaliation function for the block
     */
    public function init() {
        global $PAGE;

        //set title
        $this->title = get_string('module_menu', 'block_module_menu');

    }

    /**
     * Outputs the main content for the module menu content for the block
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
            $content .= html_writer::start_tag('h4', array('class' => 'module_menu_block'));
                $content .= get_string('editing_block_display', 'block_module_menu');
            $content .= html_writer::end_tag('h4');

            //orientation options
             $content .=  html_writer::start_tag("div", array('id'=>'module_menu_position'));
                //horiz orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/module_menu/pix/horiz.png", 'id'=>'module_menu_horz_btn', 'class'=>'module_menu_btn'));
                //vert orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/module_menu/pix/vert.png", 'id'=>'module_menu_vert_btn', 'class'=>'module_menu_btn'));
                //vert orientation
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/module_menu/pix/bottom.png", 'id'=>'module_menu_bot_btn', 'class'=>'module_menu_btn'));
                //no menus
                $content .=  html_writer::empty_tag("img", array('src'=>"$CFG->wwwroot/blocks/module_menu/pix/none.png", 'id'=>'module_menu_none_btn', 'class'=>'module_menu_btn'));
             $content .=  html_writer::end_tag("div");
            }
        
        //create object to return content
        $this->content = new stdClass;
        //assign block text
        $this->content->text = $content;

        //output the elements that will be located at top of the page
        $this->output_module_menu();


        return $this->content;
    }

    /**
     * This function faciliates the output of the module menu contents that are located
     * at the top of the page
     */
    private function output_module_menu() {
        //a div that will contain the module menu building blocks, but is ALWAYS hidden
        echo html_writer::start_tag("div", array('id' => 'module_menu_wrapper', 'class' => 'module_menu_wrapper', 'style' => 'display:none'));
            //horiz menu instance
            $this->generate_module_menu('module_menu_horiz_menu_wrapper', 'ui-icon-triangle-1-w', 'ui-icon-triangle-1-e');
            //vert menu instance
            $this->generate_module_menu('module_menu_vert_menu_wrapper', 'ui-icon-triangle-1-n','ui-icon-triangle-1-s', false, "vert");
            //bot menu instance
            $this->generate_module_menu('module_menu_bot_menu_wrapper', 'ui-icon-triangle-1-w', 'ui-icon-triangle-1-e');
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
    private function module_menu_inline_js() {
        global $COURSE, $CFG;
        
        echo "<script>console.log(module_menu_php);";
           
           echo "if(typeof module_menu_php == 'undefined') var module_menu_php = new Array();"; //global js object
        
           echo "module_menu_php['course'] = $COURSE->id ;"; //course id
           echo "module_menu_php['wwwroot'] = '$CFG->wwwroot';"; //server address
           
           //LANGS
           echo "module_menu_php['invalid_section_id'] = '".get_string('invalid_section_id','block_module_menu')."';"; 
           echo "module_menu_php['ajax'] = '$CFG->wwwroot/blocks/module_menu/ajax_controller.php';";
           echo "module_menu_php['orientation'] = '".$this->get_menu_oritentation()."';";
           
           echo "</script>";
    }
    
    /**
     * Returns the current orientation of the menu for the module menu instance
     * 
     * If the value has never been set then its horiz by default
     */
    private function get_menu_oritentation() {
        $data = $this->config;
        
        if(isset($this->config) && isset($data->orientation)) {
            return $data->orientation;
        } else {
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
        $text = get_string('landing_pad_text','block_module_menu');
        
        //create landing pad div
        echo html_writer::start_tag("div", array('class' => 'module_menu_landing_pad', 'style'=>'display:none'));
         
            echo html_writer::start_tag("div", array('class' => 'module_menu_landing_pad_add'));
                //same icon as the moodle drag/drop add files in sections
                echo html_writer::empty_tag("img", array('alt'=>$text,'src'=>$OUTPUT->pix_url('t/addfile'),'class' => 'module_menu_landing_pad_icon'));
                //landing page message
                echo html_writer::start_tag("span", array('class' => 'module_menu_landing_pad_text'));
                    echo $text;
                echo html_writer::end_tag("span");
                
            echo html_writer::end_tag("div");
                
            echo html_writer::start_tag("div", array('class' => 'module_menu_landing_pad_loading', 'style'=>'display:none'));
                //same icon as the moodle drag/drop add files in sections
                echo html_writer::empty_tag("img", array('alt'=>$text,'src'=>"$CFG->wwwroot/blocks/module_menu/pix/loading.gif",'class' => 'module_menu_landing_pad_icon'));
                //landing page message
                echo html_writer::start_tag("span", array('class' => 'module_menu_landing_pad_text'));
                    echo get_string('loading','block_module_menu');
                echo html_writer::end_tag("span");
                
            echo html_writer::end_tag("div");
            
        echo html_writer::end_tag("div");
    }
    
    /**
     * Generates an unqiue instance of the module menu with given properties.
     * 
     * @param string $wrapper_id An UNQIUE id that will be used as the html id 
     * @param string $icon_t_l_class An jquery icon class to be used as the top/left icon
     * @param string $icon_b_r_class An jquery icon class to be used as the bottom/right icon
     * @param bool $include_name On true the name is included, on false its not included
     */
    private function generate_module_menu($wrapper_id, $icon_t_l_class, $icon_b_r_class, $include_name = true) {
        
        //create main wrapper with given unique id
        echo html_writer::start_tag("div", array('id' => $wrapper_id));
              
            //left/top icon
            echo html_writer::start_tag("div", array('class' => 'module_menu_left'));
                 //jquery based icon
                 echo html_writer::start_tag("span", array('class' => 'ui-icon '.$icon_t_l_class));
                 echo html_writer::end_tag("span");
            echo html_writer::end_tag("div");

            //main menu container
            echo html_writer::start_tag("div", array('class' => 'module_menu_container'));
                //get all mod information
                $mods = $this->get_mods_information();

                //for each element - create the object
                foreach ($mods as $mod) {
                    $this->create_mod_option($mod, $include_name);
                }
                
            echo html_writer::end_tag("div");

            //create right icon
            echo html_writer::start_tag("div", array('class' => 'module_menu_right'));
                //jquery based icon
                echo html_writer::start_tag("span", array('class' => 'ui-icon '.$icon_b_r_class));
                echo html_writer::end_tag("span");
            echo html_writer::end_tag("div");
            
            
        echo html_writer::end_tag("div");
    }

    /**
     * Returns a set of all the module information
     * 
     * @global object $COURSE
     * @return array of modules metadata info
     */
    private function get_mods_information() {
        global $COURSE;
        $modnames = get_module_types_names();//get all the names avaliable for this course
        $modules = get_module_metadata($COURSE, $modnames);//get all metadata for the given names

        return $modules;
    }

    /**
     * Generates a module menu option for a given module
     * 
     * @global moodle_database $DB
     * @param object $mod A list of stdClass objects containing metadata about each module
     * @param bool $include_name on true the name is output, otherwise its included only as an image alt
     */
    private function create_mod_option($mod, $include_name = true, $additional_class = '') {
        global $DB, $OUTPUT;

        $names = get_module_types_names();//get all module full names
        $name = $names[$mod->name];//get this modules full name

        //outer wrapper the module menu option - modname is used as an identifer in the later JS
        echo html_writer::start_tag("div", array('modname' => $mod->name, 'class' => 'module_menu_mod_wrap ' . $additional_class));
            //move indicating icon
            echo html_writer::start_tag("span", array('class' => 'module_menu_mod_mov_icon ui-icon ui-icon-arrow-4'));
            echo html_writer::end_tag("span");

            //actual module information dic
            echo html_writer::start_tag("div", array('class' => 'module_menu_mod'));

                //container for module icon and help
                echo html_writer::start_tag("div", array('class' => 'module_menu_img_hlp'));
                    //mod icon
                    echo $OUTPUT->pix_icon('icon', $name, $mod->name, array('class' => 'module_menu_icon'));
                    //help DIV
                    echo html_writer::start_tag("div", array('class' => 'module_menu_help'));
                        echo get_string('help');
                    echo html_writer::end_tag("div");
                echo html_writer::end_tag("div");

            echo html_writer::end_tag("div");
            
            //conditionally add module name at bottom of the option
            if ($include_name) {
                echo html_writer::start_tag("div", array('class' => 'module_menu_mod_name'));
                echo $name;
                echo html_writer::end_tag("div");
            }

            //if the module has help included, we add a hidden DIV to contain the text (loaded into dialog later)
            if (isset($mod->help)) {
                echo html_writer::start_tag("div", array('title' => '', 'class' => 'module_menu_help_dialog', 'style' => 'display:none'));
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
        return false;
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

        $this->module_menu_inline_js();//some inline JS for php info
        
        if (moodle_major_version() >= '2.5') {//use moodle's built in if > moodle 2.5
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('migrate');
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
        } else {//need to include jquery if pre moodle 2.5
            
         //More Ugly Stuff to make it slightly more 2.4 friendly with course menu format...   
         if($COURSE->format != 'course_menu'){        
            $PAGE->requires->js("/blocks/module_menu/jquery/core/jquery-ui.min.js");
            $PAGE->requires->css("/blocks/module_menu/jquery/core/themes/base/jquery.ui.all.css");
            }
        }
        
        $PAGE->requires->js("/blocks/module_menu/module_menu.js");
    }

}

?>