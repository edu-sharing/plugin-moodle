YUI.add('moodle-atto_edusharing-button', function (Y, NAME) {



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

    var TEMPLATE = ' \
        <div id="edusharing_hint"> \
            {{get_string "hint" component}} \
            <div class="edusharing_center edusharing_hint_buttons"><a id="edusharing_hint_close" class="btn" href="#">{{get_string "cancel" component}}</a> \
            <button id="edusharing_open_repo" class="btn btn-primary">{{get_string "openRepo" component}}</button></div> \
            <input type="checkbox" id="edusharing_hint_check" name="edusharing_hint_check" value="dontshow" /> {{get_string "skipHint" component}} \
        </div> \
        <form id="edusharing_form" class="atto_form" style="display: none"> \
            <input id="edusharing_object_url" name="edusharing_object_url" type="hidden" value="" /> \
            <input id="edusharing_resid" name="edusharing_resid" type="hidden" value="" /> \
            <input id="edusharing_mimetype" name="edusharing_mimetype" type="hidden" value="" />\
            <input id="edusharing_mediatype" name="edusharing_mediatype" type="hidden" value="" /> \
            <input id="edusharing_preview_url" name="edusharing_preview_url" type="hidden" value="" /> \
            <input id="edusharing_version" name="edusharing_version" type="hidden" value="" /> \
            <input id="edusharing_ratio" name="edusharing_ratio" type="hidden" value="" /> \
            <h2>{{get_string "title" component}}</h2> \
            <input id="edusharing_title" name="edusharing_title" value="" maxlength="25"/><i id="edusharing_title_pencil" class="icon fa fa-pencil fa-fw " aria-hidden="true"></i> \
            <span style="position:absolute; color: rgba(0, 0, 0, 0); display:inline-block; font-size:2em;" id="edusharing_title_helper" name="edusharing_title_helper" value=""></span> \
            <img src="" id="edusharing_preview"> \
            <div id="edusharing_hint_directory" style="display:none"> \
            {{get_string "directoryHint" component}}\
            </div>\
            <br/><input type="checkbox" id="edusharing_version_latest" name="edusharing_version_latest" />\
            <label for="edusharing_version_latest" class="edusharing_label_inline" id="edusharing_version_latest_label"> {{get_string "alwaysShowLatestVersion" component}}</label> \
            <div id="edusharing_wrapper_alignment" class="edusharing_form_wrapper"> \
                <h2>{{get_string "subtitle" component}}</h2> \
                <input id="edusharing_caption" name="edusharing_caption" value="" /> \
            </div>\
            <div id="edusharing_wrapper_alignment" class="edusharing_form_wrapper"> \
                <h2>{{get_string "alignment" component}}</h2> \
                <div class="edusharing_wrapper_alignment_radiowrapper"> \
                    <input type="radio" id="edusharing_alignment_left" name="edusharing_alignment" value="left"> \
                    <label for="edusharing_alignment_left" class="edusharing_label_inline">{{get_string "alignmentLeft" component}}</label> \
                </div>\
                <div class="edusharing_wrapper_alignment_radiowrapper"> \
                    <input type="radio" id="edusharing_alignment_right" name="edusharing_alignment" value="right">\
                    <label for="edusharing_alignment_right" class="edusharing_label_inline">{{get_string "alignmentRight" component}}</label> \
                </div>\
                <div class="edusharing_wrapper_alignment_radiowrapper">\
                    <input type="radio" id="edusharing_alignment_none" name="edusharing_alignment" value="none" checked="checked">\
                    <label for="edusharing_alignment_none" class="edusharing_label_inline">{{get_string "alignmentNone" component}}</label> \
                </div>\
            </div> \
            <div id="edusharing_wrapper_dimensions" class="edusharing_form_wrapper"> \
                <h2>{{get_string "dimensions" component}}</h2>\
                <div style="float:left;margin-right: 20px;">\
                    <label for="edusharing_width">{{get_string "dimensionsWidth" component}}</label>\
                    <input type="number" id="edusharing_width" name="edusharing_width" value="" maxlength="4" length="4" /> px\
                </div> \
                <div>\
                    <label for="edusharing_height">{{get_string "dimensionsheight" component}}</label> \
                    <input type="number" id="edusharing_height" name="edusharing_height" value="" maxlength="4" length="4"/> px\
                </div> \
            </div> \
            <div id="edusharing_wrapper_buttons" class="edusharing_form_wrapper"> \
                <a id="edusharing_dialog_cancel" class="btn" href="#">{{get_string "cancel" component}}</a>\
                <button id="edusharing_submit" class="btn btn-primary">{{get_string "insert" component}}</button> \
            </div> \
        </form>';

    Y.namespace('M.atto_edusharing').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

        /**
         * Initialize the button
         *
         * @method Initializer
         */
        initializer: function() {
            // If we don't have the capability to view then give up.
            if (this.get('disabled')){
                return;
            }
            this.addButton({
                icon: 'icon',
                iconComponent: 'atto_edusharing',
                buttonName: 'icon',
                callback: this._displayDialogue,
                callbackArgs: 'icon',
                tags: '.edusharing',
                tagMatchRequiresAll: false // ???????????????
            });

            // Attach a submit listener to the form.
            var form = this.get('host').textarea.ancestor('form');
            if (form) {
                form.on('submit', this.eduSubmit, this);
            }

            this.getExistingObjects();

            var that = this;
            window.addEventListener("message", function(event) {
                if(event.data.event=="APPLY_NODE"){
                    var node = event.data.data;
                    window.win.close();
                    that.updateDialog(node);
                }
            }, false);
        },

        getExistingObjects: function() {
            var content = this.get('host').textarea.get('value');
            var container = document.createElement('div');
            container.innerHTML = content;
            var nodes = [ container ];
            while (0 < nodes.length) {
                var node = nodes.shift();
                // is ELEMENT_NODE?
                if (1 == node.nodeType) {
                    // traverse attributes
                    if (node.getAttribute('es:resource_id')) {
                        var object = new edusharingObject(this.get('courseid'));
                        if (object.importNode(node)) {
                            this.get('existingObjects')[object.id] = object;
                        } else {
                            alert('error_importing_node');
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
        },

        updateDialog: function(node, update) {
            Y.one('#edusharing_form').set('style', 'display:block');
            Y.one('#edusharing_hint').set('style', 'display:none');

            if(node.isDirectory) {
                Y.one('#edusharing_wrapper_dimensions').set('style', 'visibility:hidden');
                Y.one('#edusharing_version_latest').set('style', 'visibility:hidden');
                Y.one('#edusharing_version_latest_label').set('style', 'visibility:hidden');
                Y.one('#edusharing_wrapper_alignment').set('style', 'visibility:hidden');
                Y.one('#edusharing_hint_directory').set('style', 'display:block');
            } else if(this.getType(node.mediatype) == "ref") {
                Y.one('#edusharing_wrapper_dimensions').set('style', 'visibility:hidden');
            } else {
                var width = node.properties['ccm:width'] || 600;
                var height = node.properties['ccm:height'] || 400;
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

            if(update) {
                Y.one('#edusharing_resid').set('value', node.resid);
                Y.one('#edusharing_version_latest').set('style', 'display:none');
                Y.one('#edusharing_version_latest_label').set('style', 'display:none');
                Y.one('#edusharing_submit').setContent(M.util.get_string('update', COMPONENTNAME));
                if(node.alignment) {
                    Y.one('#edusharing_alignment_none').set('checked', false);
                    Y.one('#edusharing_alignment_' + node.alignment).set('checked', true);
                } else {
                    Y.one('#edusharing_alignment_none').set('checked', true);
                    Y.one('#edusharing_alignment_right').set('checked', false);
                    Y.one('#edusharing_alignment_left').set('checked', false);
                }
            }
        },

        //submit section
        eduSubmit: function() {
            var content = this.get('host').textarea.get('value');
            var container = document.createElement('div');
            container.innerHTML = content;

            var nodes = [ container ];
            while (0 < nodes.length) {
                var node = nodes.shift();
                // is ELEMENT_NODE?
                if (1 == node.nodeType) {
                    // traverse attributes
                    if (node.getAttribute('es:object_url')) {
                        var resource_id = node.getAttribute('es:resource_id');
                        if (this.get('existingObjects')[resource_id]) {
                            delete this.get('existingObjects')[resource_id];
                        } else {
                            var object = new edusharingObject(this.get('courseid'));
                            if ( ! object.importNode(node)) {
                                alert('error_importing_node');
                            }
                            if (!object.link(node)) {
                                alert('error_setting_usage');
                                node.parentNode.removeChild(node); ///does not work
                            }
                            node.setAttribute('es:resource_id', object.id);
                            if(this.getType(node.getAttribute('es:mediatype')) == 'content')
                                node.setAttribute('src', this.getPreviewUrl(object.id));
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
            for ( var resource_id in this.get('existingObjects')) {
                var remainder = this.get('existingObjects')[resource_id];
                if (!remainder.unlink(node))
                    alert('error_deleting_usage');
            }

            this.get('host').textarea.set('value', container.innerHTML);
        },

        getPreviewUrl: function(resourceId) {

            var previewUrl = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/preview.php';
            previewUrl += '?resourceId=' + resourceId;
            previewUrl += '&sesskey=' +  M.cfg.sesskey;
            return previewUrl;
        },

        getSelectedElement: function() {

            var selectedElement = '';
            var selection = this.get('host').getSelection();

            //content element
            if(selection[0].startContainer.childNodes.length > 0) {
                selection[0].startContainer.childNodes.forEach(function(element) {
                    if(element.attributes['es:object_url'])
                        selectedElement = element;
                });
            }

            //ref element
            if(selection[0].startContainer.parentElement) {
                if(selection[0].startContainer.parentElement.attributes['es:object_url'])
                    selectedElement = selection[0].startContainer.parentElement;
            }

            return selectedElement;
        },

        handleUpdate: function() {
            var selectedElement;
            var node = [];

            selectedElement = this.getSelectedElement();

            if(selectedElement) {
                if(selectedElement.attributes['es:mediatype'].value == 'folder')
                    node.isDirectory = true;
                node.resid = selectedElement.attributes['es:resource_id'].value;
                node.title = selectedElement.attributes.title.value;
                node.caption = selectedElement.attributes['es:caption'].value;
                node.properties = [];
                if(selectedElement.attributes.width)
                    node.properties['ccm:width'] = selectedElement.attributes.width.value;
                if(selectedElement.attributes.height)
                    node.properties['ccm:height'] = selectedElement.attributes.height.value;
                node.objectUrl = selectedElement.attributes['es:object_url'].value;
                node.mimetype = selectedElement.attributes['es:mimetype'].value;
                node.mediatype = selectedElement.attributes['es:mediatype'].value;
                node.preview =[];

                if(selectedElement.currentSrc)
                    node.preview.url = selectedElement.currentSrc;
                else
                    node.preview.url = this.getPreviewUrl(selectedElement.attributes['es:resource_id'].value);

                node.properties['cclom:version'] = selectedElement.attributes['es:window_version'].value;
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
        _displayDialogue: function(e, clickedicon) {
            e.preventDefault();

            var width=800;
            var height=600;

            var dialogue = this.getDialogue({
                headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
                width: width + 'px',
                height: height + 'px',
                focusAfterHide: clickedicon
            });
            //dialog doesn't detect changes in width without this
            // if you reuse the dialog, this seems necessary
            if(dialogue.width !== width + 'px'){
                dialogue.set('width', width+'px');
            }

            //append buttons to iframe
            var buttonform = this._getFormContent(clickedicon);
            var bodycontent =  Y.Node.create('<div></div>');
            bodycontent.append(buttonform);

            //set to bodycontent
            dialogue.set('bodyContent', bodycontent);

            var update = this.handleUpdate();

            if(!update) {
                if(Y.Cookie.get("edusharing_hint_hide")) {
                    //open repository if user checked up
                    this.open_repo();
                    Y.one('#edusharing_hint_check').setAttribute('checked', 'checked');
                } else {
                    Y.one('#edusharing_hint_check').removeAttribute('checked');
                }
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
        _getFormContent: function(clickedicon) {
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

        recalculateTitleWidth: function() {
            Y.one('#edusharing_title_helper').setContent(Y.one('#edusharing_title').get('value'));
            Y.one('#edusharing_title').setStyle('width',(Y.one('#edusharing_title_helper').get('offsetWidth') + 10) + 'px');
        },

        closeDialog: function(e) {
            e.preventDefault();
            this.getDialogue({
                focusAfterHide: null
            }).hide();
        },

        open_repo: function() {
            var url = this.get('repourl') + '/components/search?reurl=WINDOW&applyDirectories=true&ticket=' + this.get('ticket');
            window.win = window.open(url);
        },

        recalculateDimensions: function(e) {
            if(e._currentTarget.id == 'edusharing_height') {
                Y.one('#edusharing_width').set('value', Math.round(Y.one('#edusharing_height').get('value') * Y.one('#edusharing_ratio').get('value')));
            } else {
                Y.one('#edusharing_height').set('value', Math.round(Y.one('#edusharing_width').get('value') / Y.one('#edusharing_ratio').get('value')));
            }
        },

        edusharing_hint_check_change: function(e) {
            YUI().use('cookie', function(Y) {
                if(e.target._stateProxy.checked)
                    Y.Cookie.set("edusharing_hint_hide", true, { expires: new Date("January 12, 2025") });
                else
                    Y.Cookie.remove("edusharing_hint_hide");
            });
        },

        /**
         * Inserts the users input onto the page
         * @private
         */
        _doInsert : function(e){
            e.preventDefault();
            this.getDialogue({
                focusAfterHide: null
            }).hide();
            this.editor.focus();

            var node = this.getNode();

            //update
            if(node.resid) {
                var selectedElement = this.getSelectedElement();
                selectedElement.setAttribute('title', node.title);
                selectedElement.setAttribute('alt', node.title);
                selectedElement.setAttribute('es:caption', node.caption);
                var style = '';
                if (node.alignment != 'none')
                    style = 'float:' + node.alignment;
                selectedElement.setAttribute('style', style);
                if(this.getType(node.mediatype) == 'ref') {
                    selectedElement.innerHTML = node.title;
                } else {
                    selectedElement.setAttribute('width', node.width);
                    selectedElement.setAttribute('height', node.height);
                }
            } else {
            //insert
                var style = '';
                if (node.alignment != 'none')
                    style = 'float:' + node.alignment + ';';
                var version = '0';
                if (false == node.showlatest)
                    version = node.version;
                var insert = ' \
                class="edusharing" \
                style="' + style + '" \
                alt="' + node.title + '" \
                title="' + node.title + '" \
                es:caption="' + node.caption + '" \
                xmlns:es="http://www.edu-sharing.net/editor/" \
                es:object_url="' + node.objecturl + '" \
                es:mediatype="' + node.mediatype + '" \
                es:mimetype="' + node.mimetype + '" \
                es:window_float="' + node.alignment + '" \
                es:window_version="' + version + '" \
                es:repotype="ALFRESCO" \
            ';
                if (node.type == 'ref') {
                    insert = '<a ' + insert + '>' + node.title + '</a>';
                } else {
                    insert += '\
                    src="' + node.previewurl + '" \
                    width="' + node.width + '" \
                    height="' + node.height + '" \
                    ';
                    insert = '<img ' + insert + ' />';
                }
                this.get('host').insertContentAtFocusPoint(insert);
            }
            this.markUpdated();
        },

        getType: function(mediatype) {
            var type="ref";
            switch(true) {
                case (mediatype.indexOf('image') > -1):
                case (mediatype.indexOf('video') > -1):
                case (mediatype.indexOf('h5p') > -1):
                case (mediatype.indexOf('learningapps') > -1)://check
                case (mediatype.indexOf('youtube') > -1)://check
                case (mediatype.indexOf('vimeo') > -1)://check
                case (mediatype.indexOf('folder') > -1)://check
                    type = "content";
                    break;
            }
            return type;
        },

        getNode: function() {
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
            n.alignment = Y.one('input[name=edusharing_alignment]:checked').get('value');
            n.type = this.getType(n.mediatype);
            return n;

        }
    }, { ATTRS: {
            disabled: {
                value: false
            },
            repourl: {
                value:''
            },
            courseid: {
                value:''
            },
            ticket: {
                value:''
            },
            existingObjects: {
                value:[]
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

    edusharingObject.prototype.importNode = function importNode(node) {

        var name = node.getAttribute('title');
        if ( ! name ) {
            return false;
        }
        this.name = name;

        var object_url = node.getAttribute('es:object_url');
        if ( ! object_url ) {
            return false;
        }
        this.object_url = object_url;

        var resource_id = node.getAttribute('es:resource_id');
        if ( resource_id ) {
            this.id = resource_id;
        }

        var object_version = node.getAttribute('es:window_version');
        if ( object_version ) {
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
        // helper-url

        var helper_url = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/insert.php?sesskey=' + M.cfg.sesskey;

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
                        alert('invalid data');
                        return false;
                    }

                    // import received data
                    if ( ! data.id ) {
                        alert('no resource id');
                        return false;
                    }

                    object.id = data.id;

                    return true;
                },

                // request failed
                failure: function(transId, o, args) {
                    console.log(o.responseText);
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
        // tell moodle about deleted object
        var helper_url = M.cfg.wwwroot + '/lib/editor/atto/plugins/edusharing/delete.php?sesskey=' + M.cfg.sesskey;

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
                    alert('error deleting object' + ' ' + object.id + '.');
                    return false;
                }
            }
        };

        return Y.io(helper_url, config);
    };



}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
