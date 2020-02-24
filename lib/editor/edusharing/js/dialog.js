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

tinyMCEPopup.requireLangPack();

var edusharingDialog = {

    editor: null,

    init: function(editor) {
        this.editor = editor;
    },

    /**
     * Insert tag for newly inserted object
     *
     * @param HTMLFormElement form
     */
    on_click_insert: function(form) {
        var selectedNode = this.editor.selection.getNode();

        // Create and append new element-node

        node = null;
        showPreview = false;

        var mimeHelper = form.mimetype.value.substr(0, 6).toLowerCase();

        if (form.mediatype.value == 'tool_object' || mimeHelper == 'audio/' || mimeHelper == 'video/' || mimeHelper == 'image/' || form.repotype.value == 'YOUTUBE' || form.mediatype.value == 'directory') {
            showPreview = true;
        }


        if (showPreview) {
            node = document.createElement('img');
            node = selectedNode.appendChild(node);
            node.setAttribute('src', this.preview_url(form));
        } else {
            node = document.createElement('a');
            node = selectedNode.appendChild(node);
            node.innerHTML = form.title.value;
        }

        // Fill node
        var uri = this.editor.getParam('edusharing_namespace_uri');
        var prefix = this.editor.getParam('edusharing_namespace_prefix');

        node.setAttribute('xmlns:' + prefix, uri);

        node.setAttribute(prefix + ':object_url', form.object_url.value);

        node.setAttribute('class', 'edusharing');

        node.setAttribute('alt', form.title.value);
        node.setAttribute('title', form.title.value);
        node.setAttribute('standby', 'Please be patient while loading contents.');

        var width = form.window_width.value;
        if (width == 0) {
width = '';
}
        if (form.mediatype.value == 'directory') {
 width = '200';
}
        node.setAttribute('width', width);
        var height = form.window_height.value;
        if (height == 0) {
 height = '';
}
        node.setAttribute('height', height);

        node.setAttribute(prefix + ':window_version', form.window_version.value);
        node.setAttribute(prefix + ':repotype', form.repotype.value);

        // Get window_float radio value and set style accordingly
        var window_floats = form.window_float;
        for (var i = 0, length = window_floats.length; i < length; i++) {
            if (window_floats[i].checked) {
                node.setAttribute(prefix + ':window_float', window_floats[i].value);
                node.setAttribute('style', tinymce.plugins.edusharing.getStyle(window_floats[i].value));
                break;
            }
        }

        var window_versionshows = form.window_versionshow;
        for (var i = 0, length = window_versionshows.length; i < length; i++) {
            if (window_versionshows[i].checked) {
                node.setAttribute(prefix + ':window_versionshow', window_versionshows[i].value);
                break;
            }
        }

        node.setAttribute(prefix + ':mimetype', form.mimetype.value);
        node.setAttribute(prefix + ':mediatype', form.mediatype.value);

    tinyMCEPopup.editor.execCommand('mceRepaint');

        tinyMCEPopup.close();
    },

    /**
     * Populate edited form-data to tag
     *
     */
    on_click_update: function(form) {

        var node = this.editor.selection.getNode();
        var prefix = this.editor.getParam('edusharing_namespace_prefix');
        // Fill values from dialog box into object
        node.setAttribute('alt', form.title.value);
        node.setAttribute('title', form.title.value);

        var width = form.window_width.value;
        if (width == 0) {
width = '';
}
        node.setAttribute('width', width);
        var height = form.window_height.value;
        if (height == 0) {
height = '';
}
        node.setAttribute('height', height);

        var window_floats = form.window_float;
        for (var i = 0, length = window_floats.length; i < length; i++) {
            if (window_floats[i].checked) {
                node.setAttribute(prefix + ':window_float', window_floats[i].value);
                node.setAttribute('style', tinymce.plugins.edusharing.getStyle(window_floats[i].value));
                break;
            }
        }

        var window_versionshows = form.window_versionshow;
        for (var i = 0, length = window_versionshows.length; i < length; i++) {
            if (window_versionshows[i].checked) {
                node.setAttribute(prefix + ':window_versionshow', window_versionshows[i].value);
                break;
            }
        }
        node.setAttribute(prefix + ':window_version', form.window_version.value);
        node.setAttribute(prefix + ':repotype', form.repotype.value);

        try {
            node.innerHTML = form.title.value;
        } catch (e) {

        }

        tinyMCEPopup.editor.execCommand('mceRepaint');

        tinyMCEPopup.close();
    },

    on_click_cancel: function() {
        tinyMCEPopup.editor.execCommand('mceRepaint');

        tinyMCEPopup.close();
    },

    /**
     * Helper method to build preview-url from populated form-data
     *
     * @param HTMLFormElement form
     */
    preview_url: function(form) {

        // Splitting object-url to get object-id
        var object_url_parts = form.object_url.value.split('/');
        var object_id = object_url_parts[3];

                var remoterepo = object_url_parts[2];

        var preview_url = form.preview_url.value;

        preview_url = preview_url.concat('?nodeId=' + object_id);
        preview_url = preview_url.concat('&repoId=' + remoterepo);
        preview_url = preview_url.concat('&ticket=' + form.edu_ticket.value);

        return preview_url;
    }

};

tinyMCEPopup.onInit.add(edusharingDialog.init, edusharingDialog);
