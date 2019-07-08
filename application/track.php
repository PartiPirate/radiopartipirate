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
require_once("engine/utils/SessionUtils.php");

session_start();

if (!SessionUtils::getUserId($_SESSION)) {
    exit();
}

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$trackId = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

if (!$trackId)
{
    $track = array();
    $track["tra_id"] = 0;
    $track["tra_title"] = "";
    $track["tra_author"] = "";
    $track["tra_album"] = "";
    $track["tra_genres"] = "";
    $track["tra_url"] = "";
    $track["tra_duration"] = 0;

    $searchTrack = null;
}
else {
    $track = $trackBo->getById($trackId);
    $searchTrack = $trackBo->onlineSearch($track["tra_title"]);
}

if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
    $track["tra_title"] = utf8_decode($track["tra_title"]);
}
if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
	$track["tra_author"] = utf8_decode($track["tra_author"]);
}
if (!json_encode($track, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) {
    $track["tra_album"] = utf8_decode($track["tra_album"]);
}
$track["tra_duration_time"] = $trackBo->getTimeString($track["tra_duration"]);

?><?php if (!isset($_REQUEST["ajax"])) { ?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">

</head>    
<body>
<img src="https://media.discordapp.net/attachments/361533120060850178/370296433431150602/banniereradioPPV18.png" style="width: 100px;"><br>

<a href="tracks.php">Liste des pistes</a><br>
<br>
<?php } ?>
<form action="do_saveTrack.php" method="post">
         <input type="hidden" name="id"    value="<?=$track["tra_id"]?>">
Titre :  <input type="value" name="title"  value="<?=$track["tra_title"]?>">  <?php if ($searchTrack && $track["tra_title"]  != $searchTrack["tra_title"])  { echo $searchTrack["tra_title"];  } ?> <br>
Auteur : <input type="value" name="author" value="<?=$track["tra_author"]?>"> <?php if ($searchTrack && $track["tra_author"] != $searchTrack["tra_author"]) { echo $searchTrack["tra_author"]; } ?> <br>
Album :  <input type="value" name="album"  value="<?=$track["tra_album"]?>">  <?php if ($searchTrack && $track["tra_album"]  != $searchTrack["tra_album"])  { echo $searchTrack["tra_album"];  } ?> <br>
Genres : <input type="value" name="genres" value="<?=$track["tra_genres"]?>"> <?php if ($searchTrack && $track["tra_genres"] != $searchTrack["tra_genres"]) { echo $searchTrack["tra_genres"]; } ?> <br>
URL :    <input type="value" name="url"    value="<?=$track["tra_url"]?>"> <br>
Durée :  <?=$track["tra_duration_time"]?> <br>
Start :  <input type="value" name="start"  value="<?=$track["tra_start_time"]?>"> <br>
Finish : <input type="value" name="finish" value="<?=$track["tra_finish_time"]?>"> <br>
    <button id="save-track-button" type="submit">Mise à jour</button>

<?php 
    $embedUrl = $track["tra_url"];
    $embedUrl = str_replace("/watch?v=", "/embed/", $embedUrl);
?>
<div>
<iframe width="500" height="200" src="<?=$embedUrl?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
</div>

</form>
<?php   if (!isset($_REQUEST["ajax"])) { ?>
</body>    
</html>
<?php   } ?>