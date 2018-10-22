
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
    var FLAVORCONTROL = 'edusharing_flavor';
    var LOGNAME = 'atto_edusharing';

    var CSS = {
            INPUTSUBMIT: 'atto_media_urlentrysubmit',
            INPUTCANCEL: 'atto_media_urlentrycancel',
            FLAVORCONTROL: 'flavorcontrol',
            EDUSHARING_HINT_CHECK: 'edusharing_hint_check'

        },
        SELECTORS = {
            FLAVORCONTROL: '.flavorcontrol'
        };

    var TEMPLATE = '' +
        '<div id="edusharing_hint">' +
        'Wählen Sie ein edu-sharing Objekt aus dem Repositorium um es im Kurs anzuzeigen. Klicken Sie dazu auf den Button und anschließend auf bla...' +
        '<button id="edusharing_open_repo">open repo</button>'+
        '<br/><br/><input type="checkbox" id="{{CSS.EDUSHARING_HINT_CHECK}}" name="{{CSS.EDUSHARING_HINT_CHECK}}" value="dontshow" /> Beim nächsten mal direkt zur Suche springen' +
        '</div>' +
        '<form id="edusharing_form" class="atto_form" style="display: none">' +
        '<input id="edusharing_object_url" name="edusharing_object_url" type="hidden" value="" />' +
        '<input id="edusharing_mimetype" name="edusharing_node_id" type="hidden" value="" />' +
        '<input id="edusharing_mimetype" name="edusharing_mimetype" type="hidden" value="" />' +
        '<input id="edusharing_mediatype" name="edusharing_mediatype" type="hidden" value="" />' +
        '<input id="edusharing_preview_url" name="edusharing_preview_url" type="hidden" value="" />' +
        '<input id="edusharing_title" name="edusharing_title" type="hidden" value="" />' +
        '<input id="edusharing_width" name="edusharing_width" type="hidden" value="" />' +
        '<input id="edusharing_height" name="edusharing_height" type="hidden" value="" />' +
        '<input id="edusharing_version" name="edusharing_version" type="hidden" value="" />' +
        '<button class="{{CSS.INPUTSUBMIT}}">{{get_string "insert" component}}</button>' +
        '</form>';

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
                callbackArgs: 'icon'
            });

            //var contentOld = this.get('host').textarea.get('value');
            //window.console.log('alt:');
            //window.console.log(contentOld);

            // Attach a submit listener to the form.
            var form = this.get('host').textarea.ancestor('form');
            if (form) {
                form.on('submit', this.eduSubmit, this);
            }

            var that = this;
            window.addEventListener("message", function(event) {
                if(event.data.event=="APPLY_NODE"){
                    var node = event.data.data;
                    console.log(node);
                    window.win.close();
                    that.updateDialog(node);
                }
            }, false);
        },

        checkFunc: function() {
            alert('checkFunc');
        },

        updateDialog: function(node) {
            Y.one('#edusharing_form').set('style', 'display:block');
            Y.one('#edusharing_hint').set('style', 'display:none');
        },

        //submit section
        eduSubmit: function() {
            var host = this.get('host');
            window.console.log('neu:');
            window.console.log(this.get('host').textarea.get('value'));
        },

        /**
         * Display the edusharing Dialogue
         *
         * @method _displayDialogue
         * @private
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
                    elementid: this.get('host').get('elementid'),
                    CSS: CSS,
                    FLAVORCONTROL: FLAVORCONTROL,
                    component: COMPONENTNAME,
                    defaultflavor: this.get('defaultflavor'),
                    clickedicon: clickedicon,
                }));

            this._form = content;
            this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._doInsert, this);
            this._form.one('#' + CSS.EDUSHARING_HINT_CHECK).setAttribute('checked', 'checked');
            this._form.one('#' + CSS.EDUSHARING_HINT_CHECK).on('change', this.edusharing_hint_check_change, this);
            this._form.one('#edusharing_open_repo').on('click', this.open_repo, this);

            // @todo handle object update
            if(Y.Cookie.get("edusharing_hint_hide")) {
                //open repository if user checked up
                this.open_repo();
                this._form.one('#' + CSS.EDUSHARING_HINT_CHECK).setAttribute('checked', 'checked');
            } else {
                this._form.one('#' + CSS.EDUSHARING_HINT_CHECK).removeAttribute('checked');
            }

            return content;
        },

        open_repo: function() {

            // @todo get ticket
            window.win = window.open('http://localhost:8080/edu-sharing/components/search?reurl=WINDOW&ticket=');
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
         * @method _getDialogueContent
         * @private
         */
        _doInsert : function(e){
            e.preventDefault();
            this.getDialogue({
                focusAfterHide: null
            }).hide();

            this.editor.focus();
            this.get('host').insertContentAtFocusPoint(Y.one('#edusharing_node_id').get('value'));


            //for updating objects
            // Replace the content in a Node
            //Y.one("#hello").setHTML("<h1>Hello, <em>World</em>!</h1>");

            this.markUpdated();

        }
    }, { ATTRS: {
            disabled: {
                value: false
            },

            usercontextid: {
                value: null
            },
            defaultflavor: {
                value: ''
            }
        }
    });


