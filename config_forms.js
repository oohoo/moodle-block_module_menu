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

/**
 * This js file is specific to code loaded for the config forms
 */

//config forms init
$(function() {
   
   //convert multi-selects into select2
   $("[id^=id_config_filter_mods_],[id^=config_filter_mods_]").select2({
          //displayed when no values are present
    placeholder: dd_content_php['filter_content_placeholder'],
    
   });
   
   
   //force the select 2's to take 80% of the width
   $(".select2-container").css('width', '80%');
    
});


