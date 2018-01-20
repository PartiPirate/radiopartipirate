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
require_once("engine/bo/TemplateProgramBo.php");
require_once("engine/bo/TrackBo.php");
require_once("engine/bo/TrackLogBo.php");

$now = getNow();
$day = $now->format("w");
$time = $now->format("H:i:s");
$date = $now->format("Y-m-d");

$connection = openConnection();

$templateProgramBo = new TemplateProgramBo($connection, $config);
$exceptionalProgramBo = new ExceptionalProgramBo($connection, $config);
$trackBo = new TrackBo($connection, $config);
$trackLogBo = new TrackLogBo($connection, $config);

$filters = array();
$filters["epr_day"] = $date;
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
	$trackRandomIndex = mt_rand(0 , count($tracks) - 1);

	$data["track"] = $tracks[$trackRandomIndex];
	
	$log = array();
	$log["tlo_datetime"] = $data["datetime"];
	$log["tlo_track_id"] = $data["track"]["tra_id"];
	
	$trackLogBo->save($log);
}
//print_r($data);

echo json_encode($data, JSON_NUMERIC_CHECK);
?>