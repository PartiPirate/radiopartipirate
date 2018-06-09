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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("engine/bo/TrackBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/FormUtils.php");

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

$tracks = $trackBo->getByFilters(array("with_last_broadcast" => true, "with_number_of_broadcasts" => true, "tra_min_duration" => 3600));
$tracks = $trackBo->getByFilters(array("with_last_broadcast" => true, "with_number_of_broadcasts" => true));

foreach($tracks as &$track) {
    if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
	    $track["tra_title"] = utf8_decode($track["tra_title"]);
    }
    if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
    	$track["tra_author"] = utf8_decode($track["tra_author"]);
    }
    if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
	    $track["tra_album"] = utf8_decode($track["tra_album"]);
    }
    if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
    	$track["tra_genres"] = utf8_decode($track["tra_genres"]);
    }
	$track["tra_duration_time"] = $trackBo->getTimeString($track["tra_duration"]);
}

$data = array();
$data["status"] = "ok";
$data["tracks"] = $tracks;

//print_r($data);

echo json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
?>