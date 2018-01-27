<?php /*
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

if (!isset($api)) exit();

require_once("engine/bo/TrackLogBo.php");

$data = array();

$now = getNow();
$nowDate = $now->format("Y-m-d H:i:s");

$connection = openConnection();

$trackLogBo = new TrackLogBo($connection, $config);

$filters = array();
$filters["tlo_from_datetime"] = $nowDate;
$filters["with_tracks"] = true;

$trackLogs = $trackLogBo->getByFilters($filters);

foreach($trackLogs as &$trackLog) {
    if (!json_encode($trackLog, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
	    $trackLog["tra_title"] = utf8_decode($trackLog["tra_title"]);
    }
    if (!json_encode($trackLog, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
    	$trackLog["tra_author"] = utf8_decode($trackLog["tra_author"]);
    }
    if (!json_encode($trackLog, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
	    $trackLog["tra_album"] = utf8_decode($trackLog["tra_album"]);
    }
//	$trackLog["tra_duration_time"] = $trackBo->getTimeString($trackLog["tra_duration"]);
}

//print_r($trackLogs);

$data["datetime"] = $now->format("Y-m-d H:i:s"); 
$data["tracks"] = $trackLogs;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>