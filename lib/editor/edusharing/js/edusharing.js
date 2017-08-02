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
 * @package    editor_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Define object-structure
 *
 * @param int course_id
 */

function edusharingObject(course_id) {
    // store object's properties
    this.name = '';
    this.object_url = '';
    this.course = course_id;

    this.mimetype = '';
    this.mediatype = '';

    this.id = '';
    this.resourcetype = '';
    this.resourceversion = '';

    this.window_width = '';
    this.window_height = '';
    this.window_float = '';
    this.window_versionshow = '';
    this.window_version = '';
    this.repotype = '';

}

/**
 * Read object-data from node when editor loads content.
 *
 * @param DOMElement node
 *
 * @return bool
 */

edusharingObject.prototype.importNode = function importNode(node) {
    var object_url = node.getAttribute('es:object_url');
    if ( ! object_url ) {
        return false;
    }

    this.object_url = object_url;

    var resource_id = node.getAttribute('es:resource_id');
    if ( resource_id ) {
        this.id = resource_id;
    }
    
    var resourceversion = node.getAttribute('es:resourceversion');
    if (resourceversion) {
        this.resourceversion = resourceversion;
    }

    var name = node.getAttribute('alt');
    if ( name ) {
        this.name = name;
    }

    var window_width = node.getAttribute('width');
    if ( window_width ) {
        this.window_width = window_width;
    }

    var window_height = node.getAttribute('height');
    if ( window_height ) {
        this.window_height = window_height;
    }

    var window_float = node.getAttribute('es:window_float');
    if ( window_float ) {
        this.window_float = window_float;
    }
    
    var window_versionshow = node.getAttribute('es:window_versionshow');
    if ( window_versionshow ) {
        this.window_versionshow = window_versionshow;
    }
    
        var window_version = node.getAttribute('es:window_version');
    if ( window_version ) {
        this.window_version = window_version;
    }
    
    var repotype = node.getAttribute('es:repotype');
    if ( repotype ) {
        this.repotype = repotype;
    }

    return true;
};








/**
 * Test if
 *
 * @param DOMElement node
 *
 * @return bool
 */
edusharingObject.prototype.changed = function changed(node) {
    // if width changed we have to save it
    if ( this.window_width != node.getAttribute('width') ) {
        return true;
    }

    // if height changed we have to save it
    if ( this.window_height != node.getAttribute('height') ) {
        return true;
    }

    // if title changed we have to save it
    if ( this.name != node.getAttribute('alt') ) {
        return true;
    }
    
    // if window_float changed we have to save it
    if ( this.window_float != node.getAttribute('es:window_float') ) {
        return true;
    }
    
    // if window_vesionShow changed we have to save it
    if ( this.window_versionshow != node.getAttribute('es:window_versionshow') ) {
        return true;
    }
    
        // if window_vesion changed we have to save it
    if ( this.window_version != node.getAttribute('es:window_version') ) {
        return true;
    }
    
    if ( this.repotype != node.getAttribute('es:repotype') ) {
        return true;
    }

    return false;
};

/**
 * Link this object.
 *
 * @param tinyMCE editor
 *
 * @return bool
 */
edusharingObject.prototype.link = function link(node, editor, moodle_wwwroot) {
    // helper-url
    var helper_url = moodle_wwwroot + '/lib/editor/edusharing/helpers/insert.php?sesskey=' + editor.getParam('moodle_sesskey');

    // bind object for context
    var object = this;

    // post data to ensure usage gets set
    var Y = YUI().use('io', 'json');

    // request-configuration
    var config = {
        // POST data
        method: 'POST',
        // synchronous (blocking) request
        sync    : true,
        // transmit data from form
        data : Y.JSON.stringify(object),
        // default arguments for callbacks
        arguments : {},

        // setup event-handling
        on: {
            /*
             * request returned successfully
             *
             * Parse response. Create new element. Append it.
             */
            success: function(transId, o, args) {
                try
                {
                    var data = Y.JSON.parse(o.responseText);
                }
                catch (exception) {
                    alert(editor.getParam('edusharing_lang').error_invalid_data);
                    return false;
                }

                // import received data
                if ( ! data.id ) {
                    alert(editor.getParam('edusharing_lang').error_no_resource_id);
                    return false;
                }

                object.id = data.id;

                return true;
            },

            // request failed
            failure: function(transId, o, args) {
                console.log(o.responseText);
                alert(editor.getParam('edusharing_lang').error_setting_usage);
                return false;
            }
        }
    };

    return Y.io(helper_url, config);
};

/**
 * Update this oject properties.
 *
 * @param tinyMCE editor
 * @param string moodle_wwwroot
 *
 * @return bool
 */
edusharingObject.prototype.update = function update(node, editor, moodle_wwwroot) {
    // update object-properties from node-data
    if ( node.hasAttribute('width') ) {
        this.window_width = node.getAttribute('width');
    }

    if ( node.hasAttribute('height') ) {
        this.window_height = node.getAttribute('height');
    }

    if ( node.hasAttribute('title') ) {
        this.name = node.getAttribute('title');
    }
    
    if ( node.hasAttribute('es:window_float') ) {
        this.window_float = node.getAttribute('es:window_float');
    }
    
    if ( node.hasAttribute('es:window_versionshow') ) {
        this.window_versionshow = node.getAttribute('es:window_versionshow');
    }
    
    if ( node.hasAttribute('es:window_version') ) {
        this.window_version = node.getAttribute('es:window_version');
    }
    
    if ( node.hasAttribute('es:repotype') ) {
        this.repotype = node.getAttribute('es:repotype');
    }

    // moodle-helper to update object
    var helper_url = moodle_wwwroot + '/lib/editor/edusharing/helpers/update.php?sesskey=' + editor.getParam('moodle_sesskey');

    // bind object
    var object = this;

    var Y = YUI().use('io', 'json-stringify');

    var config = {
        method    : 'POST',
        // synchronous (blocking) request
        sync    : true,
        headers    : {    'Content-Type' : 'application/json'    },
        data    : Y.JSON.stringify(object),

        arguments : {
            object: object
        },

        on: {
            success: function(transId, o, args) {
                return true;
            },

            failure: function(transId, o, args) {
                alert(editor.getParam('edusharing_lang').error_updating_object + ' ' + object.id + '.');
                return false;
            }
        }
    };

    return Y.io(helper_url, config);
};

/**
 * Unlink/delete this object.
 *
 * @param tinyMCE editor
 * @param string moodle_wwwroot
 *
 * @return bool
 */
edusharingObject.prototype.unlink = function unlink(node, editor, moodle_wwwroot) {
    // tell moodle about deleted object
    var helper_url = moodle_wwwroot + '/lib/editor/edusharing/helpers/delete.php?sesskey=' + editor.getParam('moodle_sesskey');

    // bind object for context
    var object = this;

    var Y = YUI().use('io', 'json', 'json-stringify');

    var config = {
        method    : 'POST',
        // synchronous (blocking) request
        sync    : true,
        headers    : {    'Content-Type': 'application/json'    },
        data    : Y.JSON.stringify(object),

        arguments : {
            object: object,
            node:    node
        },

        on: {
            success: function(transId, o, args) {
                return true;
            },

            failure: function(transId, o, args) {
                alert(editor.getParam('edusharing_lang').error_deleting_object + ' ' + object.id + '.');
                return false;
            }
        }
    };

    return Y.io(helper_url, config);
};

