<?php /*
	Copyright 2019 Cédric Levieux, Parti Pirate

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
	$data["status"] = true;

    $track["tra_deleted"] = 1;
    $trackBo->update($track);
}
else {
	$data["status"] = false;
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>