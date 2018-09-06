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

(function() {
    // Load language pack
    tinymce.PluginManager.requireLangPack();

    tinymce.create('tinymce.plugins.edusharing', {

        /**
         * Initializes the plugin,
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(editor, url) {

            // store references to the available context-menu-items
            this.contextMenuItems = {
                    insert: null,
                    edit: null
            };

            this.url = url;

            // base-url to dialogs
            this.dialog_url = url + '/../../../../edusharing/dialog/';

            // keep edu-sharing object-references
            this.objects = {};

            // add button in toolbar
            editor.addButton('edusharing', {
                title : 'edu-sharing',
                cmd : 'edusharing_button',
                image : editor.getParam('moodle_wwwroot') + '/lib/editor/edusharing/images/edusharing.png'
            });

            // the command when toobar-button gets pressed
            editor.addCommand('edusharing_button', function(ui, value) {
                var node = editor.selection.getNode();
                if ( node ) {
                    if ( editor.plugins.edusharing.handles(node) ) {
                        editor.execCommand('edusharing_edit_dialog', ui, value);
                    } else {
                        editor.execCommand('edusharing_insert_dialog', ui, value);
                    }
                }
            });

            editor.addCommand('edusharing_insert_dialog', function() {
                var dialog = {
                        // course-id possibly not required here as current
                        // server-session should have it
                        file:    editor.plugins.edusharing.dialog_url + 'insert.php?sesskey=' + editor.getParam('moodle_sesskey'),
                        width:    document.documentElement.clientWidth * 0.8,
                        height:    document.documentElement.clientHeight * 0.8,
                        inline:    1,
                        maximizable: false,
                        resizable: false,
                        statusbar: false
                    };

                var params = {
                    plugin_url: editor.plugins.edusharing.url,
                    editor: editor,
                    course_id: editor.getParam('course_id'),
                    moodle_wwwroot:    editor.getParam('moodle_wwwroot'),
                    edusharing_namespace_uri: editor.getParam('edusharing_namespace_uri'),
                    edusharing_namespace_prefix: editor.getParam('edusharing_namespace_prefix')
                };

                editor.windowManager.open(dialog, params);
            });

            editor.addCommand('edusharing_edit_dialog', function(ui, value) {
                var currentNode = editor.selection.getNode();

                if (!editor.plugins.edusharing.handles(currentNode) || currentNode.getAttribute('es:mediatype') == 'directory') {
                    return false;
                }

                var query_params = {
                    window_width : currentNode.getAttribute('width'),
                    window_height : currentNode.getAttribute('height'),
                    title : currentNode.getAttribute('title'),
                    frameborder : currentNode.getAttribute('es:frameborder'),
                    window_float: currentNode.getAttribute('es:window_float'),
                    window_versionshow: currentNode.getAttribute('es:window_versionshow'),
                    mimetype: currentNode.getAttribute('es:mimetype'),
                    mediatype: currentNode.getAttribute('es:mediatype'),
                    prev_src: currentNode.getAttribute('src'),
                    window_version: currentNode.getAttribute('es:window_version'),
                    repotype: currentNode.getAttribute('es:repotype')
                };
                
                var dialog = {
                    file : editor.plugins.edusharing.dialog_url + 'edit.php'
                        + '?window_width=' + encodeURIComponent(query_params.window_width)
                        + '&window_height=' + encodeURIComponent(query_params.window_height)
                        + '&title=' + encodeURIComponent(query_params.title)
                        + '&frameborder=' + encodeURIComponent(query_params.frameborder)
                        + '&window_float=' + encodeURIComponent(query_params.window_float)
                        + '&window_versionshow=' + encodeURIComponent(query_params.window_versionshow)
                        + '&mimetype=' + encodeURIComponent(query_params.mimetype)
                        + '&mediatype=' + encodeURIComponent(query_params.mediatype)
                        + '&prev_src=' + encodeURIComponent(query_params.prev_src)
                        + '&window_version=' + encodeURIComponent(query_params.window_version)
                        + '&repotype=' + encodeURIComponent(query_params.repotype)
                        + '&sesskey=' + editor.getParam('moodle_sesskey'),
                    width:    editor.getParam('edusharing_dialog_width'),
                    height:    editor.getParam('edusharing_dialog_height'),
                    inline : 1,
                    resizable: false,
                    statusbar: false
                };

                var params = {
                    plugin_url : editor.plugins.edusharing.url,
                    editor : editor,
                    course_id : editor.getParam('course_id'),
                    moodle_wwwroot : editor.getParam('moodle_wwwroot'),
                    edusharing_namespace_uri : editor.getParam('edusharing_namespace_uri'),
                    edusharing_namespace_prefix : editor.getParam('edusharing_namespace_prefix')
                };
                editor.windowManager.open(dialog, params);
            });

            // Fires before the initialization of the editor.
//            editor.onPreInit.add(function(editor) {
//                // add extra parameters needed to display
//                // iframes
//                return true;
//            });

            // Fires after the initialization of the editor is
            // done.
            editor.onInit.add(function(editor) {
                if (editor && editor.plugins.contextmenu) {
                    editor.plugins.contextmenu.onContextMenu.add(function(sender, menu) {

                        editor.plugins.edusharing.contextMenuItems.insert= menu.add({
                            title : 'edusharing.insert',
                            cmd : 'edusharing_insert_dialog',
                            icon : 'media'
                        });

                        editor.plugins.edusharing.contextMenuItems.edit = menu.add({
                            title : 'edusharing.edit',
                            cmd : 'edusharing_edit_dialog',
                            icon : 'media'
                        });
                    });
                }

                // Array to store all remaining nodes to traverse. By pushing
                // the nodes to an array we avoid a recursive function call.
                var container = document.createElement('div');
                container.innerHTML = editor.getContent();

                var nodes = [ container ];
                while (0 < nodes.length) {

                    var node = nodes.shift();

                    // is ELEMENT_NODE?
                    if (1 == node.nodeType) {
                        // traverse attributes
                        if (node.getAttribute('es:resource_id')) {
                            var object = new edusharingObject(editor.getParam('edusharing_course_id'));
                            
                            if (object.importNode(node)) {
                                editor.plugins.edusharing.objects[object.id] = object;                            
                                
                               if (node.getAttribute('src')) {
                                    previewUrl = editor.documentBaseURI.source + '/lib/editor/edusharing/preview.php';
                                    previewUrl += '?';
                                    previewUrl += 'resourceId=' + node.getAttribute('es:resource_id');
                                    previewUrl += '&';
                                    previewUrl += 'sesskey=' + editor.getParam('moodle_sesskey');
                                    node.setAttribute('src', previewUrl);
                                }
                                
                                if (node.getAttribute('es:window_float')) {                                    
                                    node.setAttribute('style', tinymce.plugins.edusharing.getStyle(node.getAttribute('es:window_float')) );                                    
                                }
                            } else {
                                alert(editor.getParam('edusharing_lang').error_importing_node);
                                node.parentNode.removeChild(node);
                            }
                        }
                    }

                    // stack child-nodes for further examination
                    if (node.hasChildNodes()) {
                        child = 0;
                        while (child < node.childNodes.length) {
                            nodes.push(node.childNodes.item(child));
                            child++;
                        }
                    }
                }

                editor.setContent(container.innerHTML);
                editor.save();
                return true;
            });

            // Fires when a contextmenu event is intercepted
            // inside the editor.
            editor.onContextMenu.add(function(editor, event) {

                if ( ! editor.plugins.edusharing.handles(event.target) ) {
                    editor.plugins.edusharing.contextMenuItems.insert.setActive(true);
                    editor.plugins.edusharing.contextMenuItems.edit.setDisabled(true);

                    return false;
                }

                editor.selection.select(event.target);

                editor.plugins.edusharing.contextMenuItems.insert.setDisabled(true);
                editor.plugins.edusharing.contextMenuItems.edit.setActive(true);

                return true;
            });

            // Fires when a form submit event is
            // intercepteditor.
            editor.onSubmit.add(function(editor, o) {

                /*
                 * moodle uses a submit-button for cancellation, but sets global
                 * var "skipClientValidation" to "true", so we use this to
                 * return early on cancellation
                 */
                if (typeof skipClientValidation != 'undefined') {
                    if (skipClientValidation) {
                        return true;
                    }
                }

                // the base-url for requests
                var url = editor.getParam('moodle_wwwroot');

                // Array to store all remaining nodes to traverse. By pushing
                // the nodes to an array we avoid a recursive function call.
                var container = document.createElement('div');
//                container.innerHTML = o.content;
                container.innerHTML = editor.getContent();

                var nodes = [ container ];
                while (0 < nodes.length) {

                    var node = nodes.shift();

                    // is ELEMENT_NODE?
                    if (1 == node.nodeType) {
                        // traverse attributes
                        if (node.getAttribute('es:object_url')) {
                            var resource_id = node.getAttribute('es:resource_id');
                            if (editor.plugins.edusharing.objects[resource_id]) {
                                // save object
                                var object = editor.plugins.edusharing.objects[resource_id];

                                // only required updates
                                if ( object.changed(node) ) {
                                    if (!object.update(node, editor, url)) {
                                        alert(editor.getParam('edusharing_lang').error_saving_object);
                                    }
                                }

                                // remove from list
                                delete editor.plugins.edusharing.objects[resource_id];
                            } else {
                                var object = new edusharingObject(editor.getParam('edusharing_course_id'));
                                if ( ! object.importNode(node)) {
                                    alert(editor.getParam('edusharing_lang').error_importing_node);
                                }
                                if (!object.link(node, editor, url)) {
                                    alert(editor.getParam('edusharing_lang').error_setting_usage);
                                    node.parentNode.removeChild(node);
                                }

                                node.setAttribute('es:resource_id', object.id);
                            }
                        }
                    }

                    // stack child-nodes for further examination
                    if (node.hasChildNodes()) {
                        child = 0;
                        while (child < node.childNodes.length) {
                            nodes.push(node.childNodes.item(child));
                            child++;
                        }
                    }
                }

                // the remaining known objects can be deleted
                for ( var resource_id in editor.plugins.edusharing.objects) {
                    var remainder = editor.plugins.edusharing.objects[resource_id];
                    if ( remainder.unlink(node, editor, url)) {
                       // guess node has been removed already?!? (s. Jira DESPLUGMO-5) node.parentNode.removeChild(node);
                    }
                    else {
                        alert(editor.getParam('edusharing_lang').error_deleting_usage);
                    }
                }

                editor.setContent(container.innerHTML);
                editor.save();

                return true;
            });

            // Fires when the user changes node location using the mouse or
            // keyboard.
            editor.onNodeChange.add(function(editor, control_manager, element) {

                if (!editor.plugins.edusharing.handles(element)) {
                    control_manager.setActive('edusharing', false);
                    control_manager.setDisabled('edusharing', true);
                }

                control_manager.setActive('edusharing', true);
                control_manager.setDisabled('edusharing', false);

                return true;
            });

            // Fires when the editor instance is removed from
            // page.
//            editor.onRemove.add(function(editor) {
//                return true;
//            });

            // Fires when the editor is activateditor.
//            editor.onActivate.add(function(editor) {
//                return true;
//            });

            // Fires when the editor is deactivateditor.
//            editor.onDeactivate.add(function(editor) {
//                return true;
//            });

            // Fires before the initialization of the editor.
//            editor.onBeforeRenderUI.add(function(editor, cm) {
//                return true;
//            });

            // Fires after the rendering has completeditor.
//            editor.onPostRender.add(function(editor, cm) {
//                return true;
//            });

            // Fires when something in the body of the editor is
            // clickeditor.
//            editor.onClick.add(function(editor, e) {
//                return true;
//            });

            // Fires when a registered event is intercepteditor.
//            editor.onEvent.add(function(editor, e) {
//                return true;
//            });

            // Fires when a mouseup event is intercepted inside
            // the editor.
//            editor.onMouseUp.add(function(editor, e) {
//                return true;
//            });

            // Fires when a mousedown event is intercepted
            // inside the editor.
//            editor.onMouseDown.add(function(editor, e) {
//                return true;
//            });

            // Fires when a dblclick event is intercepted inside
            // the editor.
//            editor.onDblClick.add(function(editor, e) {
//                return true;
//            });

            // Fires when a keydown event is intercepted inside
            // the editor.
//            editor.onKeyDown.add(function(editor, e) {
//                return true;
//            });

            // Fires when a keydown event is intercepted inside
            // the editor.
//            editor.onKeyUp.add(function(editor, e) {
//                return true;
//            });

            // Fires when a keypress event is intercepted inside
            // the editor.
//            editor.onKeyPress.add(function(editor, e) {
//                return true;
//            });

            // Fires when the editor contents gets saved for example when the
            // save method is executeditor.
//            editor.onSaveContent.add(function(editor, e) {
//                return true;
//            });

            // Fires when a form reset event is intercepteditor.
//            editor.onReset.add(function(editor, e) {
//                return true;
//            });

            // Fires when a paste event is intercepted inside
            // the editor.
//            editor.onPaste.add(function(editor, e) {
//                return true;
//            });

            // Fires when the Serializer does a preProcess on
            // the contents.
//            editor.onPreProcess.add(function(editor, o) {
//                return true;
//            });

            // Fires when the Serializer does a postProcess on
            // the contents.
//            editor.onPostProcess.add(function(editor, o) {
//                return true;
//            });

            // Fires before new contents is added to the editor.
//            editor.onBeforeSetContent.add(function(editor, o) {
//                return true;
//            });

            // Fires before contents is extracted from the
            // editor using for
            // example getContent.
//            editor.onBeforeGetContent.add(function(editor, o) {
//                return true;
//            });

            // Fires after the contents has been added to the editor using for
            // example onSetContent.
//            editor.onSetContent.add(function(editor, o) {
//                return true;
//            });

            // Fires after the contents has been extracted from the editor using
            // for example getContent.
//            editor.onGetContent.add(function(editor, o) {
//                return true;
//            });

            // Fires when the editor gets loaded with contents for example when
            // the load method is executeditor.
//            editor.onLoadContent.add(function(editor, o) {
//                return true;
//            });

            // Fires when a new undo level is added to the editor.
//            editor.onChange.add(function(editor, l) {
//                return true;
//            });

            // Fires before a command gets executed for example "Bold".
//            editor.onBeforeExecCommand.add(function(editor, cmd, ui, val) {
//                return true;
//            });

            // Fires after a command is executed for example "Bold".
//            editor.onExecCommand.add(function(editor, cmd, ui, val) {
//                return true;
//            });

            // Fires when the contents is undo:editor.
//            editor.onUndo.add(function(editor, level) {
//                return true;
//            });

            // Fires when the contents is redo:editor.
//            editor.onRedo.add(function(editor, level) {
//                return true;
//            });

            // Fires when visual aids is enabled/disableditor.
//            editor.onVisualAid.add(function(editor, e, s) {
//                return true;
//            });

            // Fires when the progress throbber is shown above the editor.
//            editor.onSetProgressState.add(function(editor, b) {
//                return true;
//            });

        },

        createControl : function(n, cm) {
            return null;
        },

        /**
         * Parse given url to refresh url-param "ticket" with value of ticket
         *
         * @param string url         the url containing the tiket-param
         * @param string ticket     the new ticket-value
         *
         * @return string
         */
        refreshTicketParam : function(url, ticket) {

            if ( ! url ) {
                return false;
            }

            var url_helper = document.createElement('a');
            url_helper.href = url;

            var search = '';
            var query_params = url_helper.search.substr(1).split('&');
            for ( var i = 0; i < query_params.length; i++) {

                if (query_params[i].substring(0, 7) == 'ticket=') {
                    query_params[i] = 'ticket=' + ticket;
                }

                if ( 0 < query_params[i].length ) {
                    if ( 0 < i ) {
                        search = search.concat('&');
                    }

                    search = search.concat(query_params[i]);
                }
            }

            // set modified query-params
            url_helper.search = search;

            return url_helper.href;
        },

        /**
         * Test if given node is an edu-sharing-node.
         *
         * @param DOMElement node
         *
         * @return bool
         */
        handles: function(node) {

            // only an <object>-node can possibly be edu-sharing-node
            if ( 1 != node.nodeType ) {
                return false;
            }

            // no object-url->not our node
            if (!node.getAttribute('es:object_url')) {
                return false;
            }

            return true;
        },

        /**
         * Return information about the plugin
         */
        getInfo : function() {
            return {
                longname : 'edu-sharing Embed Plugin',
                author : 'metaVentis GmbH',
                authorurl : 'http://www.edu-sharing.net',
                infourl : 'http://www.edu-sharing.net',
                version : "3"
            };
        }

    });

    // Register plugin
    tinymce.PluginManager.add('edusharing', tinymce.plugins.edusharing);
})();

// seems to be required for YUI to function properly
YUI().use('io', 'json', function(Y) {
});

tinymce.plugins.edusharing.getStyle = function(float) {
    switch(float) {
        case 'left' : var style = 'display: block; float: left; margin: 5px 5px 5px 0;'; break;
        case 'right' : var style = 'display: block; float: right; margin: 5px 0 5px 5px;'; break;
        case 'inline' : var style = 'display: inline-block; margin: 0 5px;'; break;
        case 'none' :
        default: var style = 'display: block; float: none; margin: 5px 0;'; break;
    }
    return style;
}
