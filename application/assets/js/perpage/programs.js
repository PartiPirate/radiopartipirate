/*
	Copyright 2018 Cédric Levieux, Parti Pirate

	This file is part of Radio Parti Pirate.

    Radio Parti Pirate is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Radio Parti Pirate is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Radio Parti Pirate.  If not, see <http://www.gnu.org/licenses/>.
*/

/* global $ */

function updateTable(selector) {
    $.get(window.location.href, {}, function(data) {
        var selected = $(data).find(selector);
        $(selector).children().remove();
        $(selector).append(selected.children());

    }, "html");
}

function addProgramerHandlers() {
    $("#program-entries").on("click", ".to-add-programer-btn", function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        $(".programer #pen_id").val(0);
        $(".programer #pen_day").val(0);
        $(".programer #pen_program_entry_id").val(0);
        $(".programer #pen_start").val("00:00:00");
        $(".programer #pen_start").data("min", "00:00:00");
        $(".programer #pen_start").data("max", "23:59:59");
        $(".programer #pen_end").val("23:59:59");
        $(".programer #pen_end").data("min", "00:00:00");
        $(".programer #pen_end").data("max", "23:59:59");

        $(".splitter").hide();
        $(".exceptionaler").hide();
        $(".programer-deleter").hide();
        $(".programer").show();
    });

    $("#program-entries").on("click", ".to-update-programer-btn", function() {
        var programerEntry = $(this).parents("tr").data("json");

        $(".programer #pen_id").val(programerEntry.pen_id);
        $(".programer #pen_day").val(programerEntry.pen_day);
        $(".programer #pen_program_entry_id").val(programerEntry.pen_program_entry_id);
        $(".programer #pen_start").val(programerEntry.pen_start);
        $(".programer #pen_start").data("min", "00:00:00");
        $(".programer #pen_start").data("max", programerEntry.pen_end);
        $(".programer #pen_end").val(programerEntry.pen_end);
        $(".programer #pen_end").data("min", programerEntry.pen_start);
        $(".programer #pen_end").data("max", "23:59:59");

        $(".program-deleter").hide();
        $(".programer").show();
    });

    $(".update-programer-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.pen_id = $(".programer #pen_id").val();
        myform.pen_start = $(".programer #pen_start").val();
        myform.pen_end = $(".programer #pen_end").val();
        myform.pen_day = $(".programer #pen_day").val();
        myform.pen_program_entry_id = $(".programer #pen_program_entry_id").val();

        $(".programer").hide();

        $.post(action, myform, function(data) {
            updateTable("#program-entries");
        }, "json");
    });

    $("#program-entries").on("click", ".to-delete-program-btn", function() {
        var programerEntry = $(this).parents("tr").data("json");

        $(".program-deleter #pen_id").val(programerEntry.pen_id);

        $(".programer").hide();
        $(".program-deleter").show();
    });

    $(".delete-program-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.pen_id = $(".program-deleter #pen_id").val();

        $(".program-deleter").hide();

        $.post(action, myform, function(data) {
            updateTable("#program-entries");
        }, "json");
    });
}

$(function() {
    addProgramerHandlers();
})