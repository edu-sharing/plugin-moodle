// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_edusharing
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// build yui with the command: shifter

/**
 * @module moodle-atto_edusharing-button
 */

/**
 * Atto text editor edusharing plugin.
 *
 * @namespace M.atto_edusharing
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_edusharing';

var TEMPLATE = '<div id="edusharing_hint"> ' +
    '<img src="" id="edusharing_hint_logo">' +
    '<img id="edusharing_hint_help" src="' + M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/pix/help_en.gif">' +
    '{{get_string "hint1" component}}<br/><br/>{{get_string "hint2" component}}' +
    '<div style="clear: both"></div>' +
    '<div id="edusharing_hint_skip"><input type="checkbox" id="edusharing_hint_check" name="edusharing_hint_check" value="dontshow" />' +
    '{{get_string "skipHint" component}}</div>' +
    '<div class="edusharing_center edusharing_hint_buttons"><a id="edusharing_hint_close" class="btn btn-secondary" href="#">' +
    '{{get_string "cancel" component}}</a> ' +
    '<button id="edusharing_open_repo" class="btn btn-primary">{{get_string "openRepo" component}}</button></div>' +
    '</div> ' +
    '<form id="edusharing_form" class="atto_form" style="display: none">' +
    '<input id="edusharing_object_url" name="edusharing_object_url" type="hidden" value="" />' +
    '<input id="edusharing_resid" name="edusharing_resid" type="hidden" value="" />' +
    '<input id="edusharing_mimetype" name="edusharing_mimetype" type="hidden" value="" />' +
    '<input id="edusharing_mediatype" name="edusharing_mediatype" type="hidden" value="" />' +
    '<input id="edusharing_preview_url" name="edusharing_preview_url" type="hidden" value="" />' +
    '<input id="edusharing_version" name="edusharing_version" type="hidden" value="" />' +
    '<input id="edusharing_ratio" name="edusharing_ratio" type="hidden" value="" />' +
    '<h2>{{get_string "title" component}}</h2>' +
    '<input id="edusharing_title" name="edusharing_title" value="" maxlength="25"/>' +
    '<i id="edusharing_title_pencil" class="icon fa fa-pencil fa-fw " aria-hidden="true"></i> ' +
    '<span style="position:absolute; color: rgba(0, 0, 0, 0); display:inline-block; font-size:2em;"' +
    'id="edusharing_title_helper" name="edusharing_title_helper" value=""></span> ' +
    '<img src="" id="edusharing_preview"> ' +
    '<div id="edusharing_hint_directory" style="display:none">' +
    '{{get_string "directoryHint" component}}' +
    '</div>' +
    '<br/><input type="checkbox" id="edusharing_version_latest" name="edusharing_version_latest" checked />' +
    '<label for="edusharing_version_latest" class="edusharing_label_inline" id="edusharing_version_latest_label">' +
    '{{get_string "alwaysShowLatestVersion" component}}</label> ' +
    '<div id="edusharing_wrapper_caption" class="edusharing_form_wrapper">' +
    '<h2>{{get_string "subtitle" component}}</h2> ' +
    '<input id="edusharing_caption" name="edusharing_caption" value="" />' +
    '</div>' +
    '<div id="edusharing_wrapper_alignment" class="edusharing_form_wrapper">' +
    '<h2>{{get_string "alignment" component}}</h2> ' +
    '<div class="edusharing_wrapper_alignment_radiowrapper">' +
    '<input type="radio" id="edusharing_alignment_left" name="edusharing_alignment" value="left">' +
    '<label for="edusharing_alignment_left" class="edusharing_label_inline">' +
    '{{get_string "alignmentLeft" component}}</label> ' +
    '</div>' +
    '<div class="edusharing_wrapper_alignment_radiowrapper">' +
    '<input type="radio" id="edusharing_alignment_right" name="edusharing_alignment" value="right">' +
    '<label for="edusharing_alignment_right" class="edusharing_label_inline">' +
    '{{get_string "alignmentRight" component}}</label> ' +
    '</div>' +
    '<div class="edusharing_wrapper_alignment_radiowrapper">' +
    '<input type="radio" id="edusharing_alignment_none" name="edusharing_alignment" value="none" checked="checked">' +
    '<label for="edusharing_alignment_none" class="edusharing_label_inline">' +
    '{{get_string "alignmentNone" component}}</label> ' +
    '</div>' +
    '</div>' +
    '<div id="edusharing_wrapper_dimensions" class="edusharing_form_wrapper">' +
    '<h2>{{get_string "dimensions" component}}</h2>' +
    '<div style="float:left;margin-right: 20px;">' +
    '<label for="edusharing_width" class="edusharing_label_block">{{get_string "dimensionsWidth" component}}</label>' +
    '<input type="number" id="edusharing_width" name="edusharing_width" value="" maxlength="4" length="4" />&nbsp;px' +
    '</div>' +
    '<div>' +
    '<label for="edusharing_height" class="edusharing_label_block">{{get_string "dimensionsheight" component}}</label>' +
    '<input type="number" id="edusharing_height" name="edusharing_height" value="" maxlength="4" length="4"/>&nbsp;px' +
    '</div>' +
    '</div>' +
    '<div id="edusharing_wrapper_buttons" class="edusharing_form_wrapper">' +
    '<a id="edusharing_dialog_cancel" class="btn" href="#">{{get_string "cancel" component}}</a>' +
    '<button id="edusharing_submit" class="btn btn-primary">{{get_string "insert" component}}</button> ' +
    '</div>' +
    '</form>';

Y.namespace('M.atto_edusharing').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * Initialize the button
     *
     * @method Initializer
     */
    initializer: function () {
        // If we don't have the capability to view then give up.
        if (this.get('disabled')) {
            return;
        }
        this.addButton({
            icon: 'icon',
            iconComponent: 'atto_edusharing',
            buttonName: 'icon',
            callback: this._displayDialogue,
            callbackArgs: 'icon',
            tags: '.edusharing_atto',
            tagMatchRequiresAll: false
        });

        // Attach a submit listener to the form.
        var form = this.get('host').textarea.ancestor('form');
        if (form) {
            form.on('submit', this.eduSubmit, this);
        }

        this.getExistingObjects();

        var that = this;
        window.addEventListener("message", function (event) {
            if (event.data.event == "APPLY_NODE") {
                var node = event.data.data;
                window.win.close();
                that.updateDialog(node);
            }
        }, false);
    },

    getUrlVars: function (query) {
        var vars = {};
        if (!query.startsWith('?')) {
            query = '?' + query;
        }
        var parts = query.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
            vars[key] = value;
        });
        return vars;
    },

    getExistingObjects: function () {
        var content = this.get('host').textarea.get('value');
        var container = document.createElement('div');
        container.innerHTML = content;
        var nodes = [container];
        while (0 < nodes.length) {
            var node = nodes.shift();
            // Is ELEMENT_NODE?
            if (1 == node.nodeType) {

                if (Y.one(node).hasClass('edusharing_atto')) {

                    if (node.nodeName.toLowerCase() == 'img') {
                        var query = node.getAttribute('src');
                    } else {
                        var query = node.getAttribute('href');
                    }

                    var href = new URL(query);
                    const searchParams = href.searchParams;

                    var object = new edusharingObject(this.get('courseid'));
                    if (object.importNode(searchParams)) {
                        this.get('existingObjects')[object.id] = object;
                    } else {
                        alert('error_importing_node - 1');
                        node.parentNode.removeChild(node);
                    }
                }
            }

            // Stack child-nodes for further examination.
            if (node.hasChildNodes()) {
                child = 0;
                while (child < node.childNodes.length) {
                    nodes.push(node.childNodes.item(child));
                    child++;
                }
            }
        }
    },

    updateDialog: function (node, update) {
        Y.one('#edusharing_form').set('style', 'display:block');
        Y.one('#edusharing_hint').set('style', 'display:none');

        if (node.isDirectory) {
            Y.one('#edusharing_wrapper_dimensions').set('style', 'display:none');
            Y.one('#edusharing_version_latest').set('style', 'visibility:hidden');
            Y.one('#edusharing_version_latest_label').set('style', 'visibility:hidden');
            Y.one('#edusharing_wrapper_alignment').set('style', 'visibility:hidden');
            Y.one('#edusharing_hint_directory').set('style', 'display:block');
        } else if (this.getType(node.mediatype) == "ref") {
            Y.one('#edusharing_wrapper_dimensions').set('style', 'visibility:hidden');
        } else {
            var width = Math.round(node.properties['ccm:width']) || 600;
            var height = Math.round(node.properties['ccm:height']) || 400;
            Y.one('#edusharing_width').set('value', width);
            Y.one('#edusharing_height').set('value', height);
            Y.one('#edusharing_ratio').set('value', width / height);
        }

        Y.one('#edusharing_title').set('value', node.title || node.name);
        Y.one('#edusharing_caption').set('value', node.caption || '');
        Y.one('#edusharing_object_url').set('value', node.objectUrl);
        Y.one('#edusharing_mimetype').set('value', node.mimetype);
        Y.one('#edusharing_mediatype').set('value', node.mediatype);
        Y.one('#edusharing_preview_url').set('value', node.preview.url);
        Y.one('#edusharing_preview').set('src', node.preview.url);
        Y.one('#edusharing_version').set('value', node.properties['cclom:version']);

        if (update) {
            Y.one('#edusharing_resid').set('value', node.resid);
            Y.one('#edusharing_version_latest').set('style', 'display:none');
            Y.one('#edusharing_version_latest_label').set('style', 'display:none');
            Y.one('#edusharing_submit').setContent(M.util.get_string('update', COMPONENTNAME));
            if (node.alignment) {
                Y.one('#edusharing_alignment_none').set('checked', false);
                Y.one('#edusharing_alignment_' + node.alignment).set('checked', true);
            } else {
                Y.one('#edusharing_alignment_none').set('checked', true);
                Y.one('#edusharing_alignment_right').set('checked', false);
                Y.one('#edusharing_alignment_left').set('checked', false);
            }
        }
    },

    eduSubmit: function () {
        var content = this.get('host').textarea.get('value');
        var container = document.createElement('div');
        container.innerHTML = content;

        var nodes = [container];

        while (0 < nodes.length) {
            var node = nodes.shift();
            if (node.nodeType == 1) {

                if (Y.one(node).hasClass('edusharing_atto')) {

                    if (node.nodeName.toLowerCase() == 'img') {
                        var query = node.getAttribute('src');
                    } else {
                        var query = node.getAttribute('href');
                    }

                    var href = new URL(query);
                    const searchParams = href.searchParams;

                    var resource_id = searchParams.get('resourceId');
                    if (resource_id && this.get('existingObjects')[resource_id]) {
                        delete this.get('existingObjects')[resource_id];
                    } else {
                        var object = new edusharingObject(this.get('courseid'));
                        if (!object.importNode(searchParams)) {
                            alert('error_importing_node - 2');
                        }
                        if (!object.link(node)) {
                            alert('error_setting_usage');
                            node.parentNode.removeChild(node);
                        }

                        if (node.nodeName.toLowerCase() == 'img') {
                            node.setAttribute('src', this.getPreviewUrl(object.id) + '&' + searchParams.toString());
                        } else {
                            node.setAttribute('href', this.getPreviewUrl(object.id) + '&' + searchParams.toString());
                        }
                    }
                }
            }

            // Stack child-nodes for further examination.
            if (node.hasChildNodes()) {
                child = 0;
                while (child < node.childNodes.length) {
                    nodes.push(node.childNodes.item(child));
                    child++;
                }
            }
        }

        // The remaining known objects can be deleted.
        for (var resource_id in this.get('existingObjects')) {
            var remainder = this.get('existingObjects')[resource_id];
            if (!remainder.unlink(node)) {
                alert('error_deleting_usage');
            }
        }

        this.get('host').textarea.set('value', container.innerHTML);
    },

    getQueryStringFromParams: function (params) {
        var out = [];
        for (var key in params) {
            out.push(key + '=' + params[key]);
        }
        return out.join('&');
    },

    getPreviewUrl: function (resourceId) {
        var previewUrl = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/preview.php';
        previewUrl += '?resourceId=' + resourceId;
        return previewUrl;
    },

    getSelectedElement: function () {
        var element = this.get('host').getSelectedNodes()._nodes[0];
        if (element && element.nodeType && element.nodeType == 3) {
            element = element.parentElement;
        }

        if (typeof element === 'undefined') {
            return '';
        }

        if (Y.one(element).hasClass('edusharing_atto')) {
            return element;
        }

        return '';
    },

    handleUpdate: function () {
        var selectedElement;
        var node = [];

        selectedElement = this.getSelectedElement();

        if (selectedElement) {

            if (selectedElement.nodeName.toLowerCase() == 'img') {
                var query = selectedElement.getAttribute('src');
            } else {
                var query = selectedElement.getAttribute('href');
            }

            var href = new URL(query);
            const searchParams = href.searchParams;

            if (searchParams.get('mediatype') == 'folder') {
                node.isDirectory = true;
            }
            if (searchParams.get('resourceId')) {
                node.resid = searchParams.get('resourceId');
            }
            node.title = searchParams.get('title');
            node.caption = searchParams.get('caption');
            node.properties = [];
            if (searchParams.get('width')) {
                node.properties['ccm:width'] = selectedElement.attributes.width.value;
            }
            if (selectedElement.attributes.height) {
                node.properties['ccm:height'] = selectedElement.attributes.height.value;
            }
            node.objectUrl = searchParams.get('object_url');
            node.mimetype = searchParams.get('mimetype');
            node.mediatype = searchParams.get('mediatype');
            node.preview = [];

            if (this.getType(node.mediatype) == 'content') {
                node.preview.url = selectedElement.attributes.src.value;
            } else {
                node.preview.url = selectedElement.attributes.href.value;
            }

            if (selectedElement.attributes.src) {
                node.preview.url = selectedElement.attributes.src.value;
            } else {
                node.preview.url = selectedElement.attributes.href.value;
            }
            node.properties['cclom:version'] = searchParams.get('window_version');
            node.alignment = selectedElement.style.float;

            this.updateDialog(node, true);

            return true;
        }
        return false;
    },

    /**
     * Display the edusharing Dialogue
     *
     * @method _displayDialogue
     */
    _displayDialogue: function (e, clickedicon) {
        e.preventDefault();

        var width = 800;
        var height = 600;

        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            height: height + 'px',
            focusAfterHide: clickedicon
        });
        // Dialog doesn't detect changes in width without this.
        // If you reuse the dialog, this seems necessary.
        if (dialogue.width !== width + 'px') {
            dialogue.set('width', width + 'px');
        }

        // Append buttons to iframe.
        var buttonform = this._getFormContent(clickedicon);
        var bodycontent = Y.Node.create('<div></div>');
        bodycontent.append(buttonform);

        // Set to bodycontent.
        dialogue.set('bodyContent', bodycontent);

        var update = this.handleUpdate();

        if (!update) {
            Y.one('#edusharing_hint_logo').setAttribute('src', this.get('repourl') + '/assets/images/logo.svg');
            var that = this;
            YUI().use('cookie', function (Y) {
                if (Y.Cookie.get("edusharing_hint_hide")) {
                    // Open repository if user checked up.
                    that.open_repo();
                    YUI().use('node', function (Y) {
                        Y.one('#edusharing_hint_check').setAttribute('checked', 'checked');
                    });
                } else {
                    YUI().use('node', function (Y) {
                        Y.one('#edusharing_hint_check').removeAttribute('checked');
                    });
                }
            });
        }

        dialogue.show();
        this.markUpdated();
    },

    /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getFormContent: function (clickedicon) {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                component: COMPONENTNAME,
                clickedicon: clickedicon,
            }));

        this._form = content;
        this._form.one('#edusharing_submit').on('click', this._doInsert, this);
        this._form.one('#edusharing_hint_check').setAttribute('checked', 'checked');
        this._form.one('#edusharing_hint_check').on('change', this.edusharing_hint_check_change, this);
        this._form.one('#edusharing_open_repo').on('click', this.open_repo, this);
        this._form.one('#edusharing_hint_close').on('click', this.closeDialog, this);
        this._form.one('#edusharing_dialog_cancel').on('click', this.closeDialog, this);
        this._form.one('#edusharing_width').on('change', this.recalculateDimensions, this);
        this._form.one('#edusharing_width').on('keyup', this.recalculateDimensions, this);
        this._form.one('#edusharing_height').on('change', this.recalculateDimensions, this);
        this._form.one('#edusharing_height').on('keyup', this.recalculateDimensions, this);
        this._form.one('#edusharing_title').on('keyup', this.recalculateTitleWidth, this);

        return content;
    },

    recalculateTitleWidth: function () {
        Y.one('#edusharing_title_helper').setContent(Y.one('#edusharing_title').get('value'));
        Y.one('#edusharing_title').setStyle('width', (Y.one('#edusharing_title_helper').get('offsetWidth') + 10) + 'px');
    },

    closeDialog: function (e) {
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();
    },

    open_repo: function () {
        var url = this.get('repourl') + '/components/search?reurl=WINDOW&applyDirectories=true&ticket=' + this.get('ticket');
        window.win = window.open(url);
    },

    recalculateDimensions: function (e) {
        if (e._currentTarget.id == 'edusharing_height') {
            Y.one('#edusharing_width').set('value', Math.round(Y.one('#edusharing_height')
                .get('value') * Y.one('#edusharing_ratio').get('value')));
        } else {
            Y.one('#edusharing_height').set('value', Math.round(Y.one('#edusharing_width')
                .get('value') / Y.one('#edusharing_ratio').get('value')));
        }
    },

    edusharing_hint_check_change: function (e) {
        YUI().use('cookie', function (Y) {
            if (e.target._stateProxy.checked) {
                Y.Cookie.set("edusharing_hint_hide", true, {expires: new Date("January 12, 2025")});
            } else {
                Y.Cookie.remove("edusharing_hint_hide");
            }
        });
    },

    /**
     * Inserts the users input onto the page.
     * @private
     */

    _doInsert: function (e) {
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();
        this.editor.focus();

        var node = this.getNode();

        // Update.
        if (node.resid) {
            var selectedElement = this.getSelectedElement();
            selectedElement.setAttribute('title', node.title);

            var style = '';
            if (node.alignment != 'none') {
                style = 'float:' + node.alignment;
            }
            if (node.mediatype == 'folder') {
                style = 'display:block;';
            }
            selectedElement.setAttribute('style', style);
            if (this.getType(node.mediatype) == 'ref') {
                const url = new URL(selectedElement.attributes.href.value);
                selectedElement.innerHTML = node.title;
                url.searchParams.set('title', node.title);
                url.searchParams.set('caption', node.caption);
                selectedElement.setAttribute('href', url.toString());
            } else {
                const url = new URL(selectedElement.attributes.src.value);
                url.searchParams.set('title', node.title);
                url.searchParams.set('caption', node.caption);
                url.searchParams.set('width', node.width);
                url.searchParams.set('height', node.height);
                selectedElement.setAttribute('alt', node.title);
                selectedElement.setAttribute('width', node.width);
                selectedElement.setAttribute('height', node.height);
                selectedElement.setAttribute('src', url.toString());
            }

        } else {
            // Insert.
            var style = '';
            if (node.alignment != 'none') {
                style = 'float:' + node.alignment + ';';
            }
            if (node.mediatype == 'folder') {
                style = 'display:block;';
            }
            var version = '0';
            if (false == node.showlatest && node.version != 'undefined') {
                version = node.version;
            }
            var insert = 'class="edusharing_atto" ' +
                'style="' + style + '" ' +
                'title="' + node.title + '" ' +
                'contenteditable="false" ';

            var url = node.previewurl +
                '&caption=' + node.caption +
                '&object_url=' + node.objecturl +
                '&mediatype=' + node.mediatype +
                '&mimetype=' + node.mimetype +
                '&window_version=' + version +
                '&title=' + node.title;

            var url = new URL(node.previewurl);
            url.searchParams.set('caption', node.caption);
            url.searchParams.set('object_url', node.objecturl);
            url.searchParams.set('mediatype', node.mediatype);
            url.searchParams.set('mimetype', node.mimetype);
            url.searchParams.set('window_version', version);
            url.searchParams.set('title', node.title);

            if (node.type == 'ref') {
                insert = '&nbsp;<a ' + insert + ' href="' + url.toString() + '">' + node.title + '</a>&nbsp;';
            } else {
                insert += 'src="' + url.toString() +
                    '&width=' + node.width +
                    '&height=' + node.height + '"';
                insert = '<img alt="' + node.title + '" width="' + node.width + '" height="' + node.height + '" ' + insert + ' />';
            }
            this.get('host').insertContentAtFocusPoint(insert);
        }
        this.markUpdated();
    },

    getType: function (mediatype) {
        var type = "ref";
        switch (true) {
            case (mediatype.indexOf('image') > -1):
            case (mediatype.indexOf('video') > -1):
            case (mediatype.indexOf('h5p') > -1):
            case (mediatype.indexOf('learningapps') > -1):
            case (mediatype.indexOf('youtube') > -1):
            case (mediatype.indexOf('vimeo') > -1):
            case (mediatype.indexOf('folder') > -1):

                type = "content";
                break;
        }
        return type;
    },

    getNode: function () {
        var n = {};
        n.resid = Y.one('#edusharing_resid').get('value');
        n.title = Y.one('#edusharing_title').get('value');
        n.caption = Y.one('#edusharing_caption').get('value');
        n.width = Y.one('#edusharing_width').get('value');
        n.height = Y.one('#edusharing_height').get('value');
        n.previewurl = Y.one('#edusharing_preview_url').get('value');
        n.objecturl = Y.one('#edusharing_object_url').get('value');
        n.mimetype = Y.one('#edusharing_mimetype').get('value');
        n.mediatype = Y.one('#edusharing_mediatype').get('value');
        n.showlatest = Y.one('#edusharing_version_latest').get('checked');
        n.version = Y.one('#edusharing_version').get('value');
        if (n.version == 'undefined') {
            n.version = '0';
        }
        n.alignment = Y.one('input[name=edusharing_alignment]:checked').get('value');
        n.type = this.getType(n.mediatype);
        if (n.mediatype == 'folder') {
            n.showlatest = true;
            n.width = 500;
        }
        return n;

    }
}, {
    ATTRS: {
        disabled: {
            value: false
        },
        repourl: {
            value: ''
        },
        courseid: {
            value: ''
        },
        ticket: {
            value: ''
        },
        existingObjects: {
            value: []
        }
    }
});


/**
 * Define object-structure
 *
 * @param int course_id
 */
function edusharingObject(course_id) {
    this.name = '';
    this.object_url = '';
    this.course = course_id;
    this.id = '';
    this.object_version = '0';
}

/**
 * Read object-data from node when editor loads content.
 *
 * @param DOMElement node
 *
 * @return bool
 */

edusharingObject.prototype.importNode = function importNode(searchParams) {

    var name = searchParams.get('title');
    if (!name) {
        return false;
    }
    this.name = name;

    var object_url = searchParams.get('object_url');
    if (!object_url) {
        return false;
    }
    this.object_url = object_url;

    var resource_id = searchParams.get('resourceId');
    if (resource_id) {
        this.id = resource_id;
    }

    var object_version = searchParams.get('window_version');
    if (object_version) {
        this.object_version = object_version;
    }
    return true;
};


/**
 * Link this object.
 *
 * @param node
 *
 * @return bool
 */
edusharingObject.prototype.link = function link(node) {
    // Helper-url.

    var helper_url = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/insert.php?sesskey=' + M.cfg.sesskey;

    // Bind object for context.
    var object = this;

    // Post data to ensure usage gets set.
    var Y = YUI().use('io', 'json');

    // Request-configuration.
    var config = {
        // POST data.
        method: 'POST',
        // Synchronous (blocking) request.
        sync: true,
        // Transmit data from form.
        data: Y.JSON.stringify(object),
        // Default arguments for callbacks.
        arguments: {},

        // Setup event-handling.
        on: {
            /*
             * Request returned successfully
             *
             * Parse response. Create new element. Append it.
             */
            success: function (transId, o, args) {
                try {
                    var data = Y.JSON.parse(o.responseText);
                } catch (exception) {
                    alert('invalid data');
                    return false;
                }

                // Import received data.
                if (!data.id) {
                    alert('no resource id');
                    return false;
                }

                object.id = data.id;

                return true;
            },

            // Request failed.
            failure: function (transId, o, args) {
                alert('error setting usage');
                return false;
            }
        }
    };

    return Y.io(helper_url, config);
};

/**
 * Unlink/delete this object.
 *
 * @param node
 *
 * @return bool
 */
edusharingObject.prototype.unlink = function unlink(node) {
    // Tell moodle about deleted object.
    var helper_url = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/delete.php?sesskey=' + M.cfg.sesskey;

    // Bind object for context.
    var object = this;

    var Y = YUI().use('io', 'json', 'json-stringify');

    var config = {
        method: 'POST',
        // Synchronous (blocking) request.
        sync: true,
        headers: {'Content-Type': 'application/json'},
        data: Y.JSON.stringify(object),

        arguments: {
            object: object,
            node: node
        },

        on: {
            success: function (transId, o, args) {
                return true;
            },

            failure: function (transId, o, args) {
                alert('error deleting object' + ' ' + object.id + '.');
                return false;
            }
        }
    };

    return Y.io(helper_url, config);
};
