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

function getHourFraction(time) {
    var times = time.split(":");
    
    return times[0] * 1. + times[1] / 60. + (times.length > 2 ? times[2] / 3600. : 0);
}

function getRoundedHourMinute(time) {
    time = getHourFraction(time);
    time = Math.round(time * 60);
    
    var minutes = time % 60;
    var hours = ((time - minutes) / 60) % 24;

	var string = ((hours < 10) ? "0" : "") + hours + ":" + ((minutes < 10) ? "0" : "") + minutes;

	return string;
}

function getDuration(time) {
    var seconds = time % 60;
    time = (time - seconds) / 60;
    var minutes = time % 60;
    time = (time - minutes) / 60;
    var hours = time / 60;

	var string = ((hours > 0) ? ((hours < 10) ? "0" : "") + hours + ":" : "") + ((minutes < 10) ? "0" : "") + minutes + ":" + ((seconds < 10) ? "0" : "") + seconds;

	return string;
}

function setNowPosition() {
    var now = new Date();
    
    var position = now.getHours() + now.getMinutes() / 60;
    var day = now.getDay();
    
    if (day == 0) {
        day = 6
    }
    else {
        day--;
    }
    
    $(".now").css({
        left: "calc(" + day + " * 100% / 7 + 10px)",
        top: "calc(" + position + " * 30px)"
    }).attr("title", "Maintenant - " + getRoundedHourMinute(now.getHours() + ":" + now.getMinutes()));

    setTimeout(setNowPosition, 30000);
}

function setCurrentTrack() {

    $.post("api.php?method=do_getCurrent", function(data) {

        var track = "Piste actuelle : Aucune";

        if (data.tracks.length > 0) {
            track = "Piste actuelle : ";
            track += "<em>" + data.tracks[0].tra_title + "</em>";
            track += " - <span>" + data.tracks[0].tra_author + "</span>";
            track += " - " + getDuration(data.tracks[0].tra_duration);
        }

        $("#currentTrack").html(track);
    }, "json");

    setTimeout(setCurrentTrack, 30000);
}

$(function() {
    setNowPosition();
    setCurrentTrack();
})