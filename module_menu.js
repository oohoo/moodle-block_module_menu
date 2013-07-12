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

//A global variable containing the sections. This is done for efficiency, and
//should be used as read-only.
var module_menu_sections = $('li[id^="section-"]');

/*
 * General Page Init
 */
$(function() {
    module_menu_init_help_dialogs();//init help dialogs
    module_menu_init_menu();//init the menu instances
    module_menu_init_sections();//setup instances to show/hide landing pad
    module_menu_init_dragable();//setup dragable elements in menu instances
    module_menu_hide_dropdowns();//hide the dropdowns
    module_menu_init_dropable(".module_menu_landing_pad");//init the landing pages
});

/**
 * A function that hides the add activites/resources dropdowns
 */
function module_menu_hide_dropdowns() {
    $(".section_add_menus").hide();
}

/**
 * Saves the orientation state for the module menu instance
 * 
 * @param {int} blockid ID of the block to be updates
 * @param {string} orientation vert, horiz, or none
 */
function update_orientation(blockid, orientation) {
    var json = {
        course: module_menu_php['course'],
        orientation: orientation,
        blockid: blockid
    };
    
    var json_string = JSON.stringify(json);
    
    $.ajax({
      url: module_menu_php['ajax'],//url for ajax calls
      data: {
          module_menu_json:json_string,
          operation:"update"
      },//data to be sent
  
      //when ajax has completed
       })
}

/**
 * Initializes the sections
 *  -Adds landing pads to the section
 *  -makes the section a droppable
 *      This is done so we can determine when a draggable module is within a section, which
 *      allows us to show the landing pad to the user
 */
function module_menu_init_sections() {
    //get template landing pad
    var landing_pad = $('.module_menu_landing_pad');

    //for each section
    $(module_menu_sections).each(function(index, element) {
        //add a clone of the landing pad to the end of the section
        $(element).find('.content').append(landing_pad.clone());
        
        //Turn the section itself into a droppable area
        $(element).droppable({
            tolerance: "pointer",
            //whenever a draggable module enters the section area - show the landing pad
            over: function(event, ui) {
                $(this).find(".module_menu_landing_pad").show(200);
            },
            //whenever a draggable module exits the section area - hide the landing pad
            out: function(event, ui) {
                $(this).find(".module_menu_landing_pad").hide(200);
            }
        });
    });

}

/**
 * Faciliates the initalization of the various menu instances for the block
 */
function module_menu_init_menu() {
    module_menu_init_hover_scroll_horiz();//initalize horizontal menu
    module_menu_init_hover_scroll_vert();//initalize vertical menu
    
    //determine which menu to load
    module_menu_change_menu(module_menu_php['orientation']);
    
    //initalize the orientation settings within the block
    //(change between menu instances)
    module_menu_init_block_settings();
}

/**
 * Converts the elements, determined by the given selector, into landing pads (droppables)
 * for the module menu options to be dropped into.
 * 
 *  This includes the code to transfer to a module's creation page, when a module draggable is dropped into one
 *  of these landing pads.
 * 
 * @param {string} selector The selector that determines which module_menu elements become draggable menu options
 */
function module_menu_init_dropable(selector) {
    
    //get all elements that match selector and make them droppable
    $(selector).droppable({
        accept: ".module_menu_mod_wrap",//this class is allowed to be dropped onto this dropable
        tolerance: "pointer",//use the pointer to determine if the item is inside it
        
        //when entering the landing pad -> change border to solid
        over: function() {
            $(this).css('border-style', 'solid');
        },
        
        //when leaving the landing pad -> change back to the dashed border
        out: function() {
            $(this).css('border-style', 'dashed');
        },
        
        //when a module draggable is dropped into a landing pad:
        //  -determine that modules specific name, the course, and the section and
        //   create a link to that module's creation page with the appropriate params
        drop: function(event, ui) {
            var draggable = ui.draggable;//get the module draggable
            
            //find the immediate li parent - contains the section id
            var li_section_wrapper = $(this).parents("[id^='section-']");
            
            //get the section id in the format: "section-#"
            var section = li_section_wrapper.attr("id");
            
            //Attempt to parse the actual number from the section id
            var patt=/section-([\d]*)/;
            var matches = patt.exec(section);
            
            //no there are no matches, something in moodle has changed, or this
            //course format isn't going to work!
            if(matches.length < 2) {
                console.log(module_menu_php['invalid_section_id']);
                return;
            }
            
            //create the URL to module's creation page
            var add_module_url = module_menu_php['wwwroot'];//server address
            add_module_url += "/course/modedit.php";
            add_module_url += "?add=" + $(draggable).attr("modname");//module name is used as identifer to module type
            add_module_url += "&type=";//no idea
            add_module_url += "&course=" + module_menu_php['course'];//course id
            add_module_url += "&section=" + matches[1];//which section that draggable was dropped into
            add_module_url += "&return=0";//no idea
            add_module_url += "&sr=";//no idea
            
            //redirect browser
            window.location.href = add_module_url;
        }
    });

}

/**
 * Initalizes the module menu elements to become draggable
 */
function module_menu_init_dragable() {
    //get all module menu elements and makes the draggable
    $( ".module_menu_mod_wrap" ).draggable({ 
        revert: true,//when dropped, animate back to origin
        helper: "clone",//clone html when dragging
        refreshPositions: true,//since the sections' size change(adding landing pads) - always update positions
        
        //on stop - rehide the landing pads and make their borders all dashed!
        //This is done since some of the events arn't called on drop - leaving a section for example
        stop: function(event, ui) {
           $("#page .module_menu_landing_pad").hide(200); 
           $("#page .module_menu_landing_pad").css('border-style', 'dashed');
        }
    });
}

/**
 * Given an html element within a block, this function will determine the id of
 * that block instance.
 * 
 * @param {object} element An html dom object within the block instance
 * @returns {string} The instance id (its numeric but returned as a string) -> "12"
 */
function module_menu_get_block_id_from_element(element) {
    
    var div_container = $(element).parents('div[id^=inst]');
    var text_id = div_container.attr("id");
    //Attempt to parse the actual number from the section id
    var patt = /([\d]*)$/;
    var matches = patt.exec(text_id);

    //no there are no matches, something in moodle has changed, or this
    //course format isn't going to work!
    if (matches.length < 2) {
        console.log(module_menu_php['invalid_block_id']);
        return;
    }
    
    return matches[1];
    
}

/**
 * Initalizes the orientation menu that is located within the actual block itself
 */
function module_menu_init_block_settings() {
    
    //when horz button pressed, change to horizontal menu
    $("#module_menu_horz_btn").click(function() {
        module_menu_change_menu('horiz');
        
        var blockid = module_menu_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'horiz');//ajax update
    });
    
    //when vert button pressed, change to vert menu
    $("#module_menu_vert_btn").click(function() {
        module_menu_change_menu('vert');
        
        var blockid = module_menu_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'vert');//ajax update
    });
    
    //when none button pressed, show no menu
    $("#module_menu_none_btn").click(function() {
        module_menu_change_menu('none');
        
        var blockid = module_menu_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'none');//ajax update
    });
}

/**
 * Changes the instance of menu that is currently being showed
 * 
 * @param {string} type vert for vertical menu, horiz for horizontal menu, else no menu
 */
function module_menu_change_menu(type) {
    //grab menu instances for convience
    var hori_menu = $("#module_menu_horiz_menu_wrapper");
    var vert_menu = $("#module_menu_vert_menu_wrapper");
    
    //attach both instances back to our hidden div containing the module menu building blocks
    $("#module_menu_wrapper").append(hori_menu);
    $("#module_menu_wrapper").append(vert_menu);
    
    //get the main moodle page
    var page = $('#page');
    
    //make none of the orientation menu's buttons active
    $(".module_menu_btn").removeClass('active');
    
    
    //set which new active menu instance
    if(type === 'vert') {//vertical menu
        $(page).append(vert_menu);
        $("#module_menu_vert_btn").addClass('active');//make vert button active
    } else if(type === 'horiz') {//horizontal menu
        $(page).append(hori_menu);
        $("#module_menu_horz_btn").addClass('active');//make horiz button active
    } else {//no menu
        $("#module_menu_none_btn").addClass('active');//make no menu active
    }
    
}

/**
 * Initalizes the hovering based scroll functionality for the horiz menu
 */
function module_menu_init_hover_scroll_horiz() {
    //hovering on the left side of horiz menu
    $("#module_menu_horiz_menu_wrapper .module_menu_left").hover(
     
      //enter the scroll left element
      function(event) {
        
        //get content
        var container = $(this).siblings(".module_menu_container");

        //get the total amount scrolled away from the left
        var scroll_left = $(container).scrollLeft();
        
        //1 sec per 100 pxs
        //#pxs / 100px/sec * 1000 ms/sec
        var time = (scroll_left) / 250 * 1000;
        
        //animate movement to 0 from left in above time
        $(container).animate({scrollLeft: 0}, time);
        
       //exit the scroll left element
    }, function(){
         //get container that scrolling is occuring on
         var container = $(this).siblings(".module_menu_container");
         //stop the scrolling
          $(container).stop();
    });
    
   //hovering on the right side of horiz menu
    $("#module_menu_horiz_menu_wrapper .module_menu_right").hover(
     
     //enter right element
     function(event) {
        //get container
        var container = $(this).siblings(".module_menu_container");
        
        //get full width of container including hidden
        var full_width = $(container).get(0).scrollWidth;
        
        //get total amount scrolled left
        var scroll_left = $(container).scrollLeft();
        
         //1 sec per 100 pxs
        //#pxs / 100px/sec * 1000 ms/sec
        var time = (full_width - scroll_left) / 250 * 1000;
        
        //start scroll right
        $(container).animate({scrollLeft: full_width}, time);
        
        //leave scroll right animation
    }, function(){
        //get container
        var container = $(this).siblings(".module_menu_container");
        //stop animation
        $(container).stop();
    });
}

/**
 * Initalizes the hovering based scroll functionality for the vert menu
 */
function module_menu_init_hover_scroll_vert() {
    //scroll up on hover init
    $("#module_menu_vert_menu_wrapper .module_menu_left").hover(
     
      //enter scroll up
      function(event) {
      
        //get container
        var container = $(this).siblings(".module_menu_container");
        
        //get the total scrolled from top
        var scroll_up = $(container).scrollTop();
        
        //1 sec per 100 pxs
        //#pxs / 100px/sec * 1000 ms/sec
        var time = (scroll_up) / 250 * 1000;
        
        //animate towards top
        $(container).animate({scrollTop: 0}, time);
        
      //leave scroll up element
    }, function(){
        //get container
         var container = $(this).siblings(".module_menu_container");
        //stop scroll animation  
        $(container).stop();
    });
    
        //scroll down on hover init
    $("#module_menu_vert_menu_wrapper .module_menu_right").hover(
       
    //enter scroll down element    
    function(event) {
        //get container
        var container = $(this).siblings(".module_menu_container");
        
        //get full height of container including hidden
        var full_height = $(container).get(0).scrollHeight;
        
        //get total amount scrolled down
        var scroll_up = $(container).scrollTop();
        
         //1 sec per 100 pxs
        //#pxs / 100px/sec * 1000 ms/sec
        var time = (full_height - scroll_up) / 250 * 1000;
        
        //animate towards bottom
        $(container).animate({scrollTop: full_height}, time);
       
       //exit scroll up
    }, function(){
        //get container
        var container = $(this).siblings(".module_menu_container");
        
        //stop scroll animation
        $(container).stop();
    });
}

/**
 * Initalizes the help dialogs for each module, including the help buttons
 * on the module options 
 */
function module_menu_init_help_dialogs() {

        //for all of the module options
        $( ".module_menu_mod_wrap" ).each(function(index, element) {
       
       //find the help button for this option
        var help_button = $(this).find('.module_menu_help');
        //find the help content for this option
        var help_content = $(this).find('.module_menu_help_dialog');
        
        //turn the help content into a dialog
        $(help_content).dialog({
           autoOpen: false 
        });
        
        //connect this help button with this help dialog
        $(help_button).click(function() {
            $(help_content).dialog("open");//open dialog when button clicked
        });
        
    });
}