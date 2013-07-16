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
var dd_content_sections = $('li[id^="section-"]');

/*
 * General Page Init
 */
$(function() {
    dd_content_init_help_dialogs();//init help dialogs
    dd_content_init_menu();//init the menu instances
    dd_content_init_sections();//setup instances to show/hide landing pad
    dd_content_init_dragable();//setup dragable elements in menu instances
    dd_content_hide_dropdowns();//hide the dropdowns
    dd_content_init_dropable(".dd_content_landing_pad");//init the landing pages
    dd_content_init_filters();//init the filters dropdown listeners
});

/**
 * A function that hides the add activites/resources dropdowns
 */
function dd_content_hide_dropdowns() {
    $(".section_add_menus").hide();
}

/**
 * Initalization for the various filter based functionality
 */
function dd_content_init_filters() {    
    
    //when a filter dropdown is changed
    $(".dd_content_filter_select").change(function() {
       //find new filter based on selected option
       var selected_filter = $(this).children("option").filter(":selected");
       
       //run a search to filter module options
       dd_content_search(selected_filter);
    });
    
    //on click of reset link - revert to the default filtering
    $("a.dd_content_filter_reset").click(function() {

        //sending hidden to search ajax call since it contains the default value
        dd_content_search(this);
        $(".dd_content_search").val("");
    }); 
        
    
    
}

/**
 * Saves the orientation state for the dd content instance
 * 
 * @param {int} blockid ID of the block to be updates
 * @param {string} orientation vert, horiz, or none
 */
function update_orientation(blockid, orientation) {
    var json = {
        course: dd_content_php['course'],
        orientation: orientation,
        blockid: blockid
    };
    
    var json_string = JSON.stringify(json);
    
    $.ajax({
      url: dd_content_php['ajax'],//url for ajax calls
      data: {
          dd_content_json:json_string,
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
function dd_content_init_sections() {
    //get template landing pad
    var landing_pad = $('.dd_content_landing_pad');

    //for each section
    $(dd_content_sections).each(function(index, element) {
        //add a clone of the landing pad to the end of the section
        $(element).find('.content').append(landing_pad.clone());
        
        //Turn the section itself into a droppable area
        $(element).droppable({
            accept: ".dd_content_mod_wrap",//this class is allowed to be dropped onto this dropable
            tolerance: "pointer",
            //whenever a draggable module enters the section area - show the landing pad
            over: function(event, ui) {
                $(this).find(".dd_content_landing_pad").show(200);
            },
            //whenever a draggable module exits the section area - hide the landing pad
            out: function(event, ui) {
                $(this).find(".dd_content_landing_pad").hide(200);
            },
           drop: function(event, ui) {
            dd_content_dropped_event(this, event, ui);
        }
        });
    });

}

/**
 * Faciliates the initalization of the various menu instances for the block
 */
function dd_content_init_menu() {
    dd_content_init_hover_scroll_horiz();//initalize horizontal menus
    dd_content_init_hover_scroll_vert();//initalize vertical menu
    
    //determine which menu to load
    dd_content_change_menu(dd_content_php['orientation']);
    
    //initalize the orientation settings within the block
    //(change between menu instances)
    dd_content_init_block_settings();
}

/**
 * Converts the elements, determined by the given selector, into landing pads (droppables)
 * for the dd content options to be dropped into.
 * 
 *  This includes the code to transfer to a module's creation page, when a module draggable is dropped into one
 *  of these landing pads.
 * 
 * @param {string} selector The selector that determines which dd_content elements become draggable menu options
 */
function dd_content_init_dropable(selector) {
    
    //get all elements that match selector and make them droppable
    $(selector).droppable({
        accept: ".dd_content_mod_wrap",//this class is allowed to be dropped onto this dropable
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
            dd_content_dropped_event(this, event, ui);
        }
    });

}

/**
 * Given an event and ui objects from a jquery dropped event that occurs with 
 * an dd content option - this function redirects page
 * to the instance creation for that module 
 * 
 * @param {object} droppable
 * @param {object} event
 * @param {object} ui
 */
function dd_content_dropped_event(droppable, event, ui) {
    var draggable = ui.draggable;//get the module draggable

    
    $(draggable).draggable( "option", "revert", false );
    ui.helper.data('dropped', true);

    $(droppable).find(".dd_content_landing_pad_add").hide();
    $(droppable).find(".dd_content_landing_pad_loading").show();

    //find the immediate li parent - contains the section id
    var li_section_wrapper = $(droppable).closest("[id^='section-']");

    //get the section id in the format: "section-#"
    var section = li_section_wrapper.attr("id");

    //Attempt to parse the actual number from the section id
    var patt = /section-([\d]*)/;
    var matches = patt.exec(section);

    //no there are no matches, something in moodle has changed, or this
    //course format isn't going to work!
    if (matches.length < 2) {
        console.log(dd_content_php['invalid_section_id']);
        return;
    }

    //create the URL to module's creation page
    var add_module_url = dd_content_php['wwwroot'];//server address
    add_module_url += "/course/modedit.php";
    add_module_url += "?add=" + $(draggable).attr("modname");//module name is used as identifer to module type
    add_module_url += "&type=";//no idea
    add_module_url += "&course=" + dd_content_php['course'];//course id
    add_module_url += "&section=" + matches[1];//which section that draggable was dropped into
    add_module_url += "&return=0";//no idea
    add_module_url += "&sr=";//no idea

    //redirect browser
    window.location.href = add_module_url;
}

/**
 * Initalizes the dd content elements to become draggable
 */
function dd_content_init_dragable() {
    //get all dd content elements and makes the draggable
    $( ".dd_content_mod_wrap" ).draggable({ 
        revert: true,//when dropped, animate back to origin
        helper: "clone",//clone html when dragging
        refreshPositions: true,//since the sections' size change(adding landing pads) - always update positions
        
        //on stop - rehide the landing pads and make their borders all dashed!
        //This is done since some of the events arn't called on drop - leaving a section for example
        stop: function(event, ui) {
            
            if(!ui.helper.data('dropped')) {
                $("#page .dd_content_landing_pad").hide(200); 
                $("#page .dd_content_landing_pad").css('border-style', 'dashed');
                $(dd_content_sections).find(".dd_content_landing_pad_add").show();
                $(dd_content_sections).find(".dd_content_landing_pad_loading").hide();
            }
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
function dd_content_get_block_id_from_element(element) {
    
    var div_container = $(element).parents('div[id^=inst]');
    var text_id = div_container.attr("id");
    //Attempt to parse the actual number from the section id
    var patt = /([\d]*)$/;
    var matches = patt.exec(text_id);

    //no there are no matches, something in moodle has changed, or this
    //course format isn't going to work!
    if (matches.length < 2) {
        console.log(dd_content_php['invalid_block_id']);
        return;
    }
    
    return matches[1];
    
}

/**
 * Initalizes the orientation menu that is located within the actual block itself
 */
function dd_content_init_block_settings() {
    
    //when horz button pressed, change to horizontal menu
    $("#dd_content_horz_btn").click(function() {
        dd_content_change_menu('horiz');
        
        var blockid = dd_content_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'horiz');//ajax update
    });
    
    //when vert button pressed, change to vert menu
    $("#dd_content_vert_btn").click(function() {
        dd_content_change_menu('vert');
        
        var blockid = dd_content_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'vert');//ajax update
    });
    
    //when none button pressed, show no menu
    $("#dd_content_none_btn").click(function() {
        dd_content_change_menu('none');
        
        var blockid = dd_content_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'none');//ajax update
    });
    
        //when bottom button pressed, show no menu
    $("#dd_content_bot_btn").click(function() {
        dd_content_change_menu('bot');
        
        var blockid = dd_content_get_block_id_from_element(this);//get id of the block
        update_orientation(blockid, 'bot');//ajax update
    });
    
    //when search menu is entered
    //remove "search" internal label is empty
    $(".dd_content_search").focusin(function() {
        
        if($(this).attr("empty") === '1') {//if set as empty
            $(this).attr("empty", "0");//set as non-empty
            $(this).val("");//remove search label
            $(this).addClass("dd_content_active_search");//add class to make text black
        }
    });
    
    $(".dd_content_search").focusout(function() {//when leaving the textbox
        var content = $.trim($(this).val());//clean text
        if(!content || content==='') {//if empty
            $(this).attr("empty", "1");//set as empty
            $(this).val(content);
            dd_content_search(this);
            $(this).val(dd_content_php['search_empty']);//add search label
            $(this).removeClass("dd_content_active_search");//remove active search class
        } else {
            $(this).attr("empty", "0");//search isn't empty
            $(this).addClass("dd_content_active_search");//make sure it has active class
            dd_content_search(this);
        }
    });
    
    //detect enter button for search
    $(".dd_content_search").keypress(function(event){
 
        //get which key was pressed
	var keycode = (event.keyCode ? event.keyCode : event.which);
	//if it was the enter key - do search
        if(keycode == '13') {
                dd_content_search(this);
	}
 
});
    
}

/**
 * Uses an ajax call to filter the options based on the given search criteria
 * 
 * @param {object/string} element_selector An html dom object within the block instance that has the search text as its value
 */
function dd_content_search(element_selector) {
    var container = $(".dd_content_container");
    
    //removes some listeners
    dd_content_remove_help_dialogs();
    $( ".dd_content_mod_wrap" ).draggable('destroy');
    
    //create loading image element
    var img = $("<img/>", {'class':'dd_content_search_icon', 'src': dd_content_php['wwwroot']+'/blocks/dd_content/pix/loading_large.gif'});
    
    //remove menu options
    container.children().remove();
   
    //add loading image
    container.append(img);
    
    //get search textfield
    var dd_content_search = $(element_selector);
    //get block instance id
    var blockid = dd_content_get_block_id_from_element(dd_content_search);//get id of the block
    
    //assume its a horizontal or bottom menu with a name
    var include_name = 1;
    var active_menu = $(".dd_content_btn.active");//get active oritentation button
    
    //if the vertical menu is the one active, then reset include name to be no
    if(active_menu.length > 0 && active_menu.hasClass("dd_content_vert_btn"))
        include_name = 0;
    
    //setup state for the ajax search
    var json = {
        course: dd_content_php['course'],//course #
        search: dd_content_search.attr("value"),//search text
        blockid: blockid,//blockid
        include_name: include_name//whether to include text or not
    };

    //convert json to string
    var json_string = JSON.stringify(json);
    
    //ajax call
    $.ajax({
      url: dd_content_php['ajax'],//url for ajax calls
      data: {
          dd_content_json:json_string,
          operation:"search"
      }//data to be sent
  
      //when ajax has completed
       }).done(function (data) {
           container.children().remove();//remove loading icon
           container.append(data);//add response
           dd_content_init_help_dialogs();//re-initalize dialogs
           dd_content_init_dragable();//covnert to draggable options
       });
    
}

/**
 * Changes the instance of menu that is currently being showed
 * 
 * @param {string} type vert for vertical menu, horiz for horizontal menu, else no menu
 */
function dd_content_change_menu(type) {
    //grab menu instances for convience
    var hori_menu = $("#dd_content_horiz_menu_wrapper");
    var vert_menu = $("#dd_content_vert_menu_wrapper");
    var bot_menu = $("#dd_content_bot_menu_wrapper");
    
    //attach both instances back to our hidden div containing the dd content building blocks
    $("#dd_content_wrapper").append(hori_menu);
    $("#dd_content_wrapper").append(vert_menu);
    $("#dd_content_wrapper").append(bot_menu);
    
    //get the main moodle page
    var page = $('#page');
    
    //make none of the orientation menu's buttons active
    $(".dd_content_btn").removeClass('active');
    
    
    //set which new active menu instance
    if(type === 'vert') {//vertical menu
        $(page).append(vert_menu);
        $("#dd_content_vert_btn").addClass('active');//make vert button active
    } else if(type === 'horiz') {//horizontal menu
        $(page).append(hori_menu);
        $("#dd_content_horz_btn").addClass('active');//make horiz button active
    } else if(type === 'bot') {//horizontal menu
        $(page).append(bot_menu);
        $("#dd_content_bot_btn").addClass('active');//make horiz button active
    } else {//no menu
        $("#dd_content_none_btn").addClass('active');//make no menu active
    }
    
}

/**
 * Initalizes the hovering based scroll functionality for the horiz menu
 */
function dd_content_init_hover_scroll_horiz() {
    //hovering on the left side of horiz menu
    $("#dd_content_horiz_menu_wrapper .dd_content_left, #dd_content_bot_menu_wrapper .dd_content_left").hover(
     
      //enter the scroll left element
      function(event) {
        
        //get content
        var container = $(this).siblings(".dd_content_container");

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
         var container = $(this).siblings(".dd_content_container");
         //stop the scrolling
          $(container).stop();
    });
    
   //hovering on the right side of horiz menu
    $("#dd_content_horiz_menu_wrapper .dd_content_right, #dd_content_bot_menu_wrapper .dd_content_right").hover(
     
     //enter right element
     function(event) {
        //get container
        var container = $(this).siblings(".dd_content_container");
        
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
        var container = $(this).siblings(".dd_content_container");
        //stop animation
        $(container).stop();
    });
}

/**
 * Initalizes the hovering based scroll functionality for the vert menu
 */
function dd_content_init_hover_scroll_vert() {
    //scroll up on hover init
    $("#dd_content_vert_menu_wrapper .dd_content_left").hover(
     
      //enter scroll up
      function(event) {
      
        //get container
        var container = $(this).siblings(".dd_content_container");
        
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
         var container = $(this).siblings(".dd_content_container");
        //stop scroll animation  
        $(container).stop();
    });
    
        //scroll down on hover init
    $("#dd_content_vert_menu_wrapper .dd_content_right").hover(
       
    //enter scroll down element    
    function(event) {
        //get container
        var container = $(this).siblings(".dd_content_container");
        
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
        var container = $(this).siblings(".dd_content_container");
        
        //stop scroll animation
        $(container).stop();
    });
}

function dd_content_remove_help_dialogs() {
//find the help button for this option
    var help_button = $('.dd_content_help');
    //find the help content for this option
    var help_content = $('.dd_content_help_dialog');
    
    $(help_content).dialog("destroy");
    $(help_button).unbind('click');
}

/**
 * Initalizes the help dialogs for each module, including the help buttons
 * on the module options 
 */
function dd_content_init_help_dialogs() {

        //for all of the module options
        $( ".dd_content_mod_wrap" ).each(function(index, element) {
       
       //find the help button for this option
        var help_button = $(this).find('.dd_content_help');
        //find the help content for this option
        var help_content = $(this).find('.dd_content_help_dialog');
        
        //turn the help content into a dialog
        $(help_content).dialog({
           autoOpen: false,
           width: 800
        });
        
        //connect this help button with this help dialog
        $(help_button).click(function() {
            
            //get full width of window
            var fullwidth = $(window).width();
            
            //dialog will be 85% of the full window size
            var width = 0.85 * fullwidth;
            
            //need to determine the leftover space: window width - size of dialog: then half on each side!
            var leftoffset = (fullwidth - width) / 2;
            
            //always have dialog 200 below top of viewpane
            var topoffset = 200;
            
            //set our custom width
            $(help_content).dialog('option', 'width', width);
            
            //set our dynamic position
            $(help_content).dialog('option', 'position',  [leftoffset, topoffset]);
            
            $(help_content).dialog("open");//open dialog when button clicked
        });
        
    });
}