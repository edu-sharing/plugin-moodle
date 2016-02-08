<?php



/**
 * @package    filter
 * @subpackage edusharing
 * @copyright  2012 M.Hupfer <hupfer@metaventis.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configmulticheckbox('filter_edusharing/formats',
            get_string('settingformats', 'filter_edusharing'),
            get_string('settingformats_desc', 'filter_edusharing'),
            array(FORMAT_MOODLE => 1), format_text_menu()));
}
