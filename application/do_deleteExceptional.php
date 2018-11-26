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
require_once("engine/bo/ExceptionalProgramBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/FormUtils.php");

session_start();

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$programId = intval($_REQUEST["epr_id"]);

$connection = openConnection();

$exceptionalProgramBo = new ExceptionalProgramBo($connection, $config);

//echo $programId;

$program = array();
if ($programId) {
    $program = $exceptionalProgramBo->getById($programId);
}

if (!$program) {
    echo json_encode(array("ko" => "ko"));
    exit();
}

foreach($program as $key => $value) {
    if (substr($key, 0, 3) != "epr") unset($program[$key]);
}

$program["epr_deleted"] = 1;

//print_r($program);

$exceptionalProgramBo->save($program);

echo json_encode(array("ok" => "ok"));

?>