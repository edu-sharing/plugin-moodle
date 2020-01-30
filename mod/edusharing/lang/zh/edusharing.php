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
 * zh strings for edusharing
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['searchrec'] = '在edu-sharing资源库中搜索 ...';
$string['uploadrec'] = '上传到edu-sharing资源库 ...';
$string['pagewindow'] = '在页面中显示';
$string['newwindow'] = '在新窗口中显示';
$string['display'] = '显示';
$string['show_course_blocks'] = '显示课程板块';

// modulename seems to be used in admin-panels
// pluginname seems to be used in course-view
$string['modulename'] = $string['pluginname'] = 'edu-sharing 资源';
$string['modulenameplural'] = 'edu-sharing';
$string['edusharing'] = 'edu-sharing';

// mod_form.php
$string['edusharingname'] = '标题';

$string['object_url_fieldset'] = 'edu-sharing 学习对象';
$string['object_url'] = '对象';
$string['object_url_help'] = '请使用下方按钮从资源库中选择一个对象。对象ID将会被自动插入此处';

$string['object_version_fieldset'] = '对象版本';
$string['object_version'] = '使用 ..';
$string['object_version_help'] = '选择使用的对象版本';
$string['object_version_use_exact'] = '使用选择的对象版本';
$string['object_version_use_latest'] = '使用最新的对象版本';

$string['object_display_fieldset'] = '对象显示选项';
$string['force_download'] = '强制下载';
$string['force_download_help'] = '强制对象下载';
$string['object_display_fieldset_help'] = '';

$string['show_course_blocks'] = '显示课程板块';
$string['show_course_blocks_help'] = '在目标窗口中显示课程板块';

$string['window_allow_resize'] = '允许调整尺寸';
$string['window_allow_resize_help'] = '允许调整目标窗口尺寸';

$string['window_allow_scroll'] = '允许滚动';
$string['window_allow_scroll_help'] = '允许在目标窗口中滚动';

$string['show_directory_links'] = '显示目录链接';
$string['show_directory_links_help'] = '显示目录链接';

$string['show_menu_bar'] = '显示菜单栏';
$string['show_menu_bar_help'] = '在目标窗口显示菜单栏';

$string['show_location_bar'] = '显示位置栏';
$string['show_location_bar_help'] = '在目标窗口显示位置栏';

$string['show_tool_bar'] = '显示工具栏';
$string['show_tool_bar_help'] = '在目标窗口显示工具栏';

$string['show_status_bar'] = '显示状态栏';
$string['show_status_bar_help'] = '在目标窗口显示状态栏';

$string['window_width'] = '显示宽度';
$string['window_width_help'] = '目标窗口宽度';

$string['window_height'] = '显示高度';
$string['window_height_help'] = '目标窗口的高度';

// general error message
$string['exc_MESSAGE'] = '在连接edu-sharing.net时发生网络错误';

// beautiful exceptions
$string['exc_SENDACTIVATIONLINK_SUCCESS'] = '成功发送激活链接';
$string['exc_APPLICATIONACCESS_NOT_ACTIVATED_BY_USER'] = '访问未被用户激活';
$string['exc_COULD_NOT_CONNECT_TO_HOST'] = '连接主机失败';
$string['exc_INTEGRITY_VIOLATION'] = '完整性冲突';
$string['exc_INVALID_APPLICATION'] = '无效应用';
$string['exc_ERROR_FETCHING_HTTP_HEADERS'] = '获取HTTP头文件失败';
$string['exc_NODE_DOES_NOT_EXIST'] = '节点不存在';
$string['exc_ACCESS_DENIED'] = '访问被拒';
$string['exc_NO_PERMISSION'] = '权限不够';
$string['exc_UNKNOWN_ERROR'] = '未知错误';

// metadata
$string['conf_linktext'] = 'Connect moodle to home repository:';
$string['conf_btntext'] = 'Connect';
$string['conf_hinttext'] = 'This will open a new window where you can load the repository metadata and register the plugin with the repository';
$string['filter_not_authorized'] = 'You are not authorized to access the requested content.';
$string['connectToHomeRepository'] = 'Connect to Home Reposiory';
$string['conf_linktext'] = 'To connect moodle to home repository click here';
$string['appProperties'] = 'Application Properties';
$string['homerepProperties'] = 'Home Repository Properties';
$string['authparameters'] = 'Authentication Parameters';
$string['guestProperties'] = 'Guest properties';
$string['save'] = 'Save changes';
$string['emptyForDefault'] = 'empty for default';

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
$string['error_get_app_properties'] = 'No app-properties found.';
$string['error_encrypt_with_repo_public'] = 'Cannot encrypt data.';
