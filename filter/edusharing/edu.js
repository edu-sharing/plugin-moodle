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
 * @package    filter_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function getJQueryCodeSoThatMoodleLikesIt($) {
        $.ajaxSetup({ cache: false });
        
        function renderEsObject(esObject, wrapper) {
            var url = esObject.attr("data-url");
            if (typeof wrapper == 'undefined')
                var wrapper = esObject.parent();
            $.get(url, function(data) {
                wrapper.html('').append(data).css({height: 'auto', width: 'auto'});
                if (data.toLowerCase().indexOf('data-view="lock"') >= 0)
                    setTimeout(function() { renderEsObject(esObject, wrapper);}, 1111);
            });
            esObject.removeAttr("data-type");
        }
        
        $("div[data-type='esObject']:near-viewport(400)").each(function() {
            renderEsObject($(this));
        })
        
        $(window).scroll(function() {
            $("div[data-type='esObject']:near-viewport(400)").each(function() {
                renderEsObject($(this));
            })
        });
}

if (typeof require == 'undefined') {
    $(document).ready(function() {
        getJQueryCodeSoThatMoodleLikesIt($);
    });
} else {
    require(['jquery'], function($) {
        $(document).ready(function() {
            getJQueryCodeSoThatMoodleLikesIt($);
        });
    });
}

    
    
