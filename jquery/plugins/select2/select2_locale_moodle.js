/**
**************************************************************************
**                                Chairman                              **
**************************************************************************
* @package mod                                                          **
* @subpackage chairman                                                  **
* @name Chairman                                                        **
* @copyright oohoo.biz                                                  **
* @link http://oohoo.biz                                                **
* @author Raymond Wainman                                               **
* @author Patrick Thibaudeau                                            **
* @author Dustin Durand                                                 **
* @license                                                              **
http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later                **
**************************************************************************
**************************************************************************/

/**
 * Select2 Moodle Interface for translation.
 * 
 */
(function ($) {
    "use strict";

    $.extend($.fn.select2.defaults, {
        formatNoMatches: function () { return ""+dd_content_php['select2_no_matches']; },
        formatInputTooShort: function (input, min) { var n = min - input.length; return ""+dd_content_php['select2_enter'] + n + dd_content_php['select2_additional_chars'] + (n == 1 ? "" : dd_content_php['select2_plural_extension']); },
        formatInputTooLong: function (input, max) { var n = input.length - max; return ""+dd_content_php['select2_remove_chars'] + n + dd_content_php['select2_chars'] + (n == 1 ? "" : dd_content_php['select2_plural_extension']); },
        formatSelectionTooBig: function (limit) { return ""+dd_content_php['select2_only_select'] + limit + dd_content_php['select2_item'] + (limit == 1 ? "" : dd_content_php['select2_plural_extension']); },
        formatLoadMore: function (pageNumber) { return ""+dd_content_php['select2_loading_more']; },
        formatSearching: function () { return ""+dd_content_php['select2_searching']; }
    });
})(jQuery);