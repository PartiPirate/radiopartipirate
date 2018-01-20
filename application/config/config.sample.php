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

if(!isset($config)) {
	$config = array();
}

$config["administrator"] = array();
$config["administrator"]["login"] = "r00t";
$config["administrator"]["password"] = "r00t";

$config["database"] = array();
$config["database"]["dialect"] = "mysql";
$config["database"]["host"] = "127.0.0.1";
$config["database"]["port"] = 3306;
$config["database"]["login"] = "root";
$config["database"]["password"] = "r00t";
$config["database"]["database"] = "radio";
$config["database"]["prefix"] = "";

?>
