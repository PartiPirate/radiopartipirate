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

require_once("engine/bo/ExceptionalProgramBo.php");
require_once("engine/bo/JingleBo.php");
require_once("engine/bo/TemplateProgramBo.php");
require_once("engine/bo/TrackBo.php");
require_once("engine/bo/TrackLogBo.php");

define("LAST_HOURS_JINGLE", sys_get_temp_dir() . "/LAST_HOURS_JINGLE", true);
define("JINGLE_AFTER_MINUTE", 57, true);
define("JINGLE_BEFORE_MINUTE", 3, true);

$now = getNow();
$day = $now->format("w");
$time = $now->format("H:i:s");
$date = $now->format("Y-m-d");

$connection = openConnection();

$minutes = intval($now->format("i"));

if ($minutes > JINGLE_AFTER_MINUTE || $minutes < JINGLE_BEFORE_MINUTE) {

	$hours = intval($now->format("H"));
	if ($minutes > JINGLE_AFTER_MINUTE) {
		$hours++;
	}
	if ($hours == 0) $hours = 24;

	$label = "H" . ($hours < 10 ? "0" : "") . $hours;
//	echo $label;
//	echo "<br>";

	$last = file_get_contents(LAST_HOURS_JINGLE);
//	echo $last;
//	echo "<br>";


	if ($last != $label) {
		$jingleBo = new JingleBo($connection, $config);

		$ĵingles = $jingleBo->getByFilters(array("jin_type" => "hours", "jin_data" => $label));
//		shuffle($ĵingles);
//		echo count($ĵingles) . " jingles<br>";

		if (count($ĵingles)) {
			$jingle = $ĵingles[(rand() % count($ĵingles))];
//			$jingle = $ĵingles[0];

			$jingleTrack = array("tra_id" => -1, "tra_title" => "Jingle $label", "tra_author" => $jingle["jin_author"], "tra_album" => "Jingle", "tra_free" => 1);
			$jingleTrack["tra_url"] = $jingle["jin_url"];

			$data = array();
			$data["track"] = $jingleTrack;
			echo json_encode($data, JSON_NUMERIC_CHECK);

			file_put_contents(LAST_HOURS_JINGLE, $label);

			exit();
		}
	}
}

$templateProgramBo = new TemplateProgramBo($connection, $config);
$exceptionalProgramBo = new ExceptionalProgramBo($connection, $config);
$trackBo = new TrackBo($connection, $config);
$trackLogBo = new TrackLogBo($connection, $config);

$filters = array();
$filters["epr_date"] = $date;
$filters["epr_between_time"] = $time;
$program = $exceptionalProgramBo->getByFilters($filters);

if (!count($program)) {
	$filters = array();
	$filters["tpr_day"] = $day;
	$filters["tpr_between_time"] = $time;
	$program = $templateProgramBo->getByFilters($filters);
}

$data["datetime"] = $now->format("Y-m-d H:i:s");
$data["date"] = $date;
$data["day"] = $day;
$data["time"] = $time;

if (!count($program)) {
	echo json_encode($data, JSON_NUMERIC_CHECK);
	return;
}

$program = $program[0];

$data["program"] = $program;

$tracks = $trackBo->getByProgramParameters($program["pen_parameters"], 4);

$data["numberOfTracks"] = count($tracks);
$duration = 0;
foreach($tracks as $track) {
	$duration += $track["tra_duration"];
}
$data["durationOfTracks"] = $duration;

if (count($tracks)) {
	$trackRandomIndex = mt_rand(0 , count($tracks) * count($tracks) -1);
	$trackRandomIndex = sqrt($trackRandomIndex);
	$trackRandomIndex = count($tracks) - floor($trackRandomIndex) - 1;

	$track = $tracks[$trackRandomIndex];
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

	$data["track"] = $track;

	$log = array();
	$log["tlo_datetime"] = $data["datetime"];
	$log["tlo_track_id"] = $data["track"]["tra_id"];
	
	$trackLogBo->save($log);
}
//print_r($data);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>