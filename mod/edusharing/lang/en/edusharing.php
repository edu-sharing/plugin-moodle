<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings for edusharing
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['searchrec'] = 'Search the edu-sharing repository ...';
$string['uploadrec'] = 'Upload to edu-sharing repository ...';
$string['pagewindow'] = 'In-page display';
$string['newwindow'] = 'Display in new window';
$string['display'] = 'Display';
$string['show_course_blocks'] = 'Show course blocks';

// modulename seems to be used in admin-panels
// pluginname seems to be used in course-view
$string['modulename'] = $string['pluginname'] = 'edu-sharing resource';
$string['modulenameplural'] = 'edu-sharing';
$string['edusharing'] = 'edu-sharing';
$string['pluginadministration'] = 'edu-sharing';
// mod_form.php
$string['edusharingname'] = 'Title';

$string['object_url_fieldset'] = 'edu-sharing Learning-object';
$string['object_url'] = 'Object';
$string['object_url_help'] = 'Please use the buttons below to select an object from repository. Its object-ID will be inserted here automatically.';

$string['object_version_fieldset'] = 'Object-versioning';
$string['object_version'] = 'Use ..';
$string['object_version_help'] = 'Select which Object-version to use.';
$string['object_version_use_exact'] = 'Use selected object-version.';
$string['object_version_use_latest'] = 'Use latest object-version';

$string['object_display_fieldset'] = 'Object-display options';
$string['force_download'] = 'Force download';
$string['force_download_help'] = 'Force object-download.';
$string['object_display_fieldset_help'] = '';

$string['show_course_blocks'] = 'Show course-blocks';
$string['show_course_blocks_help'] = 'Show course-blocks in target-window.';

$string['window_allow_resize'] = 'Allow resizing';
$string['window_allow_resize_help'] = 'Allow resizing of target-window.';

$string['window_allow_scroll'] = 'Allow scrolling';
$string['window_allow_scroll_help'] = 'Allow scrolling in target-window.';

$string['show_directory_links'] = 'Show directory-links';
$string['show_directory_links_help'] = 'Show directory-links.';

$string['show_menu_bar'] = 'Show menu-bar';
$string['show_menu_bar_help'] = 'Show menu-bar in target-window.';

$string['show_location_bar'] = 'Show location-bar';
$string['show_location_bar_help'] = 'Show location-bar in target-window.';

$string['show_tool_bar'] = 'Show tool-bar';
$string['show_tool_bar_help'] = 'Show toolbar in target-window.';

$string['show_status_bar'] = 'Show status-bar';
$string['show_status_bar_help'] = 'Show status-bar in target-window.';

$string['window_width'] = 'Display-width';
$string['window_width_help'] = 'Width of target-window.';

$string['window_height'] = 'Display-height';
$string['window_height_help'] = 'Height for target-window.';

// general error message
$string['exc_MESSAGE'] = 'An error occured utilizing the edu-sharing.net network.';

// beautiful exceptions
$string['exc_SENDACTIVATIONLINK_SUCCESS'] = 'Successfully sent activation-link.';
$string['exc_APPLICATIONACCESS_NOT_ACTIVATED_BY_USER'] = 'Access not activated by user.';
$string['exc_COULD_NOT_CONNECT_TO_HOST'] = 'Could not connect to host.';
$string['exc_INTEGRITY_VIOLATION'] = 'Integrity violation.';
$string['exc_INVALID_APPLICATION'] = 'Invalid application.';
$string['exc_ERROR_FETCHING_HTTP_HEADERS'] = 'Error fetching HTTP-headers.';
$string['exc_NODE_DOES_NOT_EXIST'] = 'Node does not exist anymore.';
$string['exc_ACCESS_DENIED'] = 'Access denied.';
$string['exc_NO_PERMISSION'] = 'Insufficient permissions.';
$string['exc_UNKNOWN_ERROR'] = 'Unknown error.';

// metadata
$string['connectToHomeRepository'] = 'Connect to Home Reposiory';
$string['conf_linktext'] = 'Connect moodle to home repository:';
$string['conf_btntext'] = 'Connect';
$string['conf_hinttext'] = 'This will open a new window where you can load the repository metadata and register the plugin with the repository.';
$string['appProperties'] = 'Application Properties';
$string['homerepProperties'] = 'Home Repository Properties';
$string['authparameters'] = 'Authentication Parameters';
$string['guestProperties'] = 'Guest properties';
$string['save'] = 'Save changes';
$string['emptyForDefault'] = 'empty for default';
$string['filter_not_authorized'] = 'You are not authorized to access the requested content.';

// auth parameters
$string['convey_global_groups_yes'] = 'Convey cohorts';
$string['convey_global_groups_no'] = 'Do not convey cohorts';

$string['soaprequired'] = 'The PHP extension soap must be activated.';

$string['error_missing_authwsdl'] = 'No "authenticationwebservice_wsdl" configured.';
$string['error_authservice_not_reachable'] = 'not reachable. Cannot utilize edu-sharing network.';
$string['error_invalid_ticket'] = 'Invalid ticket. Cannot utilize edu-sharing network.';
$string['error_auth_failed'] = 'Cannot utilize edu-sharing network because authentication failed.';
$string['error_load_course'] = 'Cannot load course from database.';
$string['error_missing_usagewsdl'] = 'No "usagewebservice_wsdl" configured.';
$string['error_load_resource'] = 'Cannot load resource from database.';
$string['error_get_object_id_from_url'] = 'Cannot get object id from url.';
$string['error_get_repository_id_from_url'] = 'Cannot get repository id from url.';
$string['error_detect_course'] = 'Cannot detect course id';
$string['error_loading_memento'] = 'Error loading temporary object.';
$string['error_set_soap_headers'] = 'Cannot set SOAP headers - ';
$string['error_get_app_properties'] = 'Cannot load plugin config';
$string['error_encrypt_with_repo_public'] = 'Cannot encrypt data.';
