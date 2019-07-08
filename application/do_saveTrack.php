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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/database.php");
require_once("engine/bo/TrackBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/FormUtils.php");

session_start();

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$trackId = intval($_REQUEST["id"]);

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

$track = $trackBo->getById($trackId);

if (!$track) {
    $track = array();
}

$track["tra_title"] = ($_REQUEST["title"]);
$track["tra_author"] = ($_REQUEST["author"]);
$track["tra_album"] = ($_REQUEST["album"]);
$track["tra_genres"] = ($_REQUEST["genres"]);
$track["tra_url"] = $_REQUEST["url"];

$track["tra_start_time"] = null;
$track["tra_finish_time"] = null;

if (isset($_REQUEST["start"]) && $_REQUEST["start"]) {
    $track["tra_start_time"] = $_REQUEST["start"];
}
if (isset($_REQUEST["finish"]) && $_REQUEST["finish"]) {
    $track["tra_finish_time"] = $_REQUEST["finish"];
}

$trackBo->save($track);

header('Location: track.php?id=' . $track["tra_id"]);

?>