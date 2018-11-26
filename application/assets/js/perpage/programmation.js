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

function addSplitterHandlers() {
    $("#periodic").on("click", ".to-split-btn", function() {
        var templateEntry = $(this).parents("tr").data("json");
        $(".splitter #tpr_id").val(templateEntry.tpr_id);
        $(".splitter #tpr_end").val(templateEntry.tpr_end);
        $(".splitter #tpr_end").data("min", templateEntry.tpr_start);
        $(".splitter #tpr_end").data("max", templateEntry.tpr_end);

        $(".exceptionaler").hide();
        $(".templater").hide();
        $(".template-deleter").hide();
        $(".splitter").show();
    });
    
    $(".splitter .split-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.tpr_id = $(".splitter #tpr_id").val();
        myform.tpr_end = $(".splitter #tpr_end").val();

        $(".splitter").hide();

        $.post(action, myform, function(data) {
            updateTable("#periodic");
        }, "json");
    });
}

function addTemplaterHandlers() {
    $("#periodic").on("click", ".to-add-template-btn", function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        $(".templater #tpr_id").val(0);
        $(".templater #tpr_day").val(0);
        $(".templater #tpr_program_entry_id").val(0);
        $(".templater #tpr_start").val("00:00:00");
        $(".templater #tpr_start").data("min", "00:00:00");
        $(".templater #tpr_start").data("max", "23:59:59");
        $(".templater #tpr_end").val("23:59:59");
        $(".templater #tpr_end").data("min", "00:00:00");
        $(".templater #tpr_end").data("max", "23:59:59");

        $(".splitter").hide();
        $(".exceptionaler").hide();
        $(".template-deleter").hide();
        $(".templater").show();
    });

    $("#periodic").on("click", ".to-update-template-btn", function() {
        var templateEntry = $(this).parents("tr").data("json");

        $(".templater #tpr_id").val(templateEntry.tpr_id);
        $(".templater #tpr_day").val(templateEntry.tpr_day);
        $(".templater #tpr_program_entry_id").val(templateEntry.tpr_program_entry_id);
        $(".templater #tpr_start").val(templateEntry.tpr_start);
        $(".templater #tpr_start").data("min", "00:00:00");
        $(".templater #tpr_start").data("max", templateEntry.tpr_end);
        $(".templater #tpr_end").val(templateEntry.tpr_end);
        $(".templater #tpr_end").data("min", templateEntry.tpr_start);
        $(".templater #tpr_end").data("max", "23:59:59");

        $(".splitter").hide();
        $(".exceptionaler").hide();
        $(".template-deleter").hide();
        $(".templater").show();
    });

    $(".update-template-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.tpr_id = $(".templater #tpr_id").val();
        myform.tpr_start = $(".templater #tpr_start").val();
        myform.tpr_end = $(".templater #tpr_end").val();
        myform.tpr_day = $(".templater #tpr_day").val();
        myform.tpr_program_entry_id = $(".templater #tpr_program_entry_id").val();

        $(".templater").hide();

        $.post(action, myform, function(data) {
            updateTable("#periodic");
        }, "json");
    });

    $("#periodic").on("click", ".to-delete-template-btn", function() {
        var templateEntry = $(this).parents("tr").data("json");

        $(".template-deleter #tpr_id").val(templateEntry.tpr_id);

        $(".splitter").hide();
        $(".templater").hide();
        $(".exceptionaler").hide();
        $(".template-deleter").show();
    });

    $(".delete-template-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.tpr_id = $(".template-deleter #tpr_id").val();

        $(".template-deleter").hide();

        $.post(action, myform, function(data) {
            updateTable("#periodic");
        }, "json");
    });
}

function addExceptionalerrHandlers() {

    var exceptionalParent = null;

    $("#futures,#pasts").on("click", ".to-add-exceptional-btn", function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        exceptionalParent = $(this).parents("#futures,#pasts");

        $(".exceptionaler #epr_id").val(0);
        $(".exceptionaler #epr_date").val(0);
        $(".exceptionaler #epr_program_entry_id").val(0);
        $(".exceptionaler #epr_start").val("00:00:00");
        $(".exceptionaler #epr_start").data("min", "00:00:00");
        $(".exceptionaler #epr_start").data("max", "23:59:59");
        $(".exceptionaler #epr_end").val("23:59:59");
        $(".exceptionaler #epr_end").data("min", "00:00:00");
        $(".exceptionaler #epr_end").data("max", "23:59:59");

        $(".splitter").hide();
        $(".templater").hide();
        $(".template-deleter").hide();
        $(".exceptionaler").show();
    });

    $("#futures,#pasts").on("click", ".to-update-exceptional-btn", function() {
        var exceptionalEntry = $(this).parents("tr").data("json");
        exceptionalParent = $(this).parents("#futures,#pasts");

        $(".exceptionaler #epr_id").val(exceptionalEntry.epr_id);
        $(".exceptionaler #epr_date").val(exceptionalEntry.epr_date);
        $(".exceptionaler #epr_program_entry_id").val(exceptionalEntry.epr_program_entry_id);
        $(".exceptionaler #epr_start").val(exceptionalEntry.epr_start);
        $(".exceptionaler #epr_start").data("min", "00:00:00");
        $(".exceptionaler #epr_start").data("max", exceptionalEntry.epr_end);
        $(".exceptionaler #epr_end").val(exceptionalEntry.epr_end);
        $(".exceptionaler #epr_end").data("min", exceptionalEntry.epr_start);
        $(".exceptionaler #epr_end").data("max", "23:59:59");

        $(".splitter").hide();
        $(".templater").hide();
        $(".template-deleter").hide();
        $(".exceptionaler").show();
    });

    $(".update-exceptional-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.epr_id = $(".exceptionaler #epr_id").val();
        myform.epr_start = $(".exceptionaler #epr_start").val();
        myform.epr_end = $(".exceptionaler #epr_end").val();
        myform.epr_date = $(".exceptionaler #epr_date").val();
        myform.epr_program_entry_id = $(".exceptionaler #epr_program_entry_id").val();

        $(".exceptionaler").hide();

        $.post(action, myform, function(data) {
            updateTable("#" + exceptionalParent.attr("id"));
        }, "json");
    });

    $("#futures,#pasts").on("click", ".to-delete-exceptional-btn", function() {
        var exceptionalEntry = $(this).parents("tr").data("json");
        exceptionalParent = $(this).parents("#futures,#pasts");

        $(".exceptional-deleter #epr_id").val(exceptionalEntry.epr_id);

        $(".splitter").hide();
        $(".templater").hide();
        $(".template-deleter").hide();
        $(".exceptionaler").hide();
        $(".exceptional-deleter").show();
    });

    $(".delete-exceptional-btn").click(function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = $(this).parents("form"); 
        var action = form.attr("action");
        var myform = {};
        myform.epr_id = $(".exceptional-deleter #epr_id").val();

        $(".exceptional-deleter").hide();

        $.post(action, myform, function(data) {
            updateTable("#" + exceptionalParent.attr("id"));
        }, "json");
    });
}

$(function() {
    addSplitterHandlers();
    addTemplaterHandlers();
    addExceptionalerrHandlers();
})