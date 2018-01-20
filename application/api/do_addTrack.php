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

require_once("engine/bo/TrackBo.php");

$data = array();

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

$track = $trackBo->getByUrl($_REQUEST["url"]);

if ($track) {
	$data["status"] = false;
    echo json_encode($data, JSON_NUMERIC_CHECK);
    return;
}

$track = array();
$track["tra_url"] = $_REQUEST["url"];

//echo "<br>\n";

// Search song
//$search = "https://www.allmusic.com/search/songs/" . urlencode($_REQUEST["title"]. " - " . $_REQUEST["author"]);
$search = "https://www.allmusic.com/search/songs/" . urlencode($_REQUEST["title"]);
//echo $search . "<br>\n";

$content = file_get_contents($search);

$content = substr($content, strpos($content, "<h4>Song</h4>"));
$content = substr($content, strpos($content, "href=\"") + 6);
$index = strpos($content, "\"");
$url = substr($content, 0, $index);

// Read data song

//echo $url . "<br>\n";

$content = file_get_contents($url);

$artistContent = substr($content, strpos($content, "song-artist"));
$artistContent = substr($artistContent, strpos($artistContent, "href"));
$artistContent = substr($artistContent, strpos($artistContent, ">"));
$artistContent = substr($artistContent, 1, strpos($artistContent, "<") - 1);
//echo "Artist : $artistContent <br>\n";

$titleContent = substr($content, strpos($content, "song-title"));
$titleContent = substr($titleContent, strpos($titleContent, ">"));
$titleContent = trim(substr($titleContent, 1, strpos($titleContent, "<") - 1));
//echo "Title : $titleContent <br>\n";

$genresContent = substr($content, strpos($content, "<h4>Song Genres</h4>"), strpos($content, "<div class=\"bottom\">") - strpos($content, "<h4>Song Genres</h4>"));
$genresContent = substr($genresContent, strpos($genresContent, "<div class=\"middle\">") + 20);

$genres = array();
while (strpos($genresContent, "genre") !== false) {
    $genresContent = substr($genresContent, strpos($genresContent, "genre"));
    $genresContent = substr($genresContent, strpos($genresContent, ">"));
    $index = strpos($genresContent, "(");
    $genres[] = trim(substr($genresContent, 1, $index - 1));
    $genresContent = substr($genresContent, $index);
}

if (strpos($genres[0], "Would you like") !== false) {
    $genres = "";
}
else {
    $genres = implode(", ", $genres);
}

//echo "Genres : $genres <br>\n";

//$genresContent = substr($genresContent, strpos($genresContent, ">"));
//$genresContent = trim(substr($genresContent, 1, strpos($genresContent, "<") - 1));
//echo $genresContent . "<br>\n";

$albumContent = substr($content, strpos($content, "inAlbum"));
$albumContent = substr($albumContent, strpos($albumContent, "title") + 7);
$albumContent = trim(substr($albumContent, 0, strpos($albumContent, "\"")));
//echo "Album : $albumContent <br>\n";

//echo $content . "<br>\n";

/*
$track["tra_title"] = $_REQUEST["title"];
$track["tra_author"] = $_REQUEST["author"];
if (isset($_REQUEST["genres"])) $track["tra_genres"] = $_REQUEST["genres"];
if (isset($_REQUEST["album"])) $track["tra_album"] = $_REQUEST["album"];
*/

if (!$titleContent || strpos(strtolower($_REQUEST["title"]), strtolower($titleContent)) === false) {
    $titleContent = $_REQUEST["title"];
    $artistContent = $_REQUEST["author"];
    $genres = "";
    $albumContent = "";
    if (isset($_REQUEST["genres"])) $genres = $_REQUEST["genres"];
    if (isset($_REQUEST["album"])) $albumContent = $_REQUEST["album"];
}

$track["tra_title"] = $titleContent;
$track["tra_author"] = $artistContent;
$track["tra_album"] = $albumContent;
$track["tra_genres"] = $genres;
$track["tra_duration"] = $_REQUEST["duration"];
if (isset($_REQUEST["free"])) $track["tra_free"] = $_REQUEST["free"];

$trackBo->save($track);

$data["status"] = true;
$data["track"] = $track;

echo json_encode($data, JSON_NUMERIC_CHECK);
?>