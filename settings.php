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

if ($hassiteconfig) { // needs this condition or there is error on login page
    
    /**
     * Add an external admin page to the blocks section for the dd_content block
     */
    $ADMIN->add('blocksettings', new admin_externalpage('block_dd_content', get_string('pluginname','block_dd_content'), "$CFG->wwwroot/blocks/dd_content/admin_settings.php"));
}


?>
