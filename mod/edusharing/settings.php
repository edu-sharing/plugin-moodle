<?php
// This file is part of edu-sharing
//
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the edu-sharing settings
 *
 * @package edusharing
 * @copyrigth 2012 metaVentis GmbH
 * @author M.Hupfer
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $CFG;

if ($ADMIN->fulltree) {
        $str_txt = get_string('conf_linktext', 'edusharing');

        $str = '<h4 class="main"><a href="'.$CFG->wwwroot.'/mod/edusharing/import_metadata.php?sesskey='.$USER->sesskey.'" target="_blank">'.$str_txt.'</a></h4>';
        $settings->add(new admin_setting_heading('edusharing', 'edu-sharing', $str));
}
