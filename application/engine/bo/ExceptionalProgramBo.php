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

class ExceptionalProgramBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "exceptional_programs";
	var $ID_FIELD = "epr_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new ExceptionalProgramBo($pdo, $config);
	}

	function create(&$exceptionalProgram) {
		return BoHelper::create($exceptionalProgram, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($exceptionalProgram) {
		return BoHelper::update($exceptionalProgram, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$exceptionalProgram) {
 		if (!isset($exceptionalProgram[$this->ID_FIELD]) || !$exceptionalProgram[$this->ID_FIELD]) {
			$this->create($exceptionalProgram);
		}

		$this->update($exceptionalProgram);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select($this->TABLE);
		$queryBuilder->setDistinct();
		$queryBuilder->addSelect($this->TABLE . ".*");
		$queryBuilder->join("program_entries", "pen.pen_id = epr_program_entry_id", "pen", "left");
		$queryBuilder->addSelect("pen.*");

		if ($filters && isset($filters["epr_id"])) {
			$args["epr_id"] = $filters["epr_id"];
			$queryBuilder->where("epr_id = :epr_id");
		}

		if ($filters && isset($filters["epr_date"])) {
			$args["epr_date"] = $filters["epr_date"];
			$queryBuilder->where("epr_date = :epr_date");
		}

		if ($filters && isset($filters["epr_between_time"])) {
			$args["epr_between_time"] = $filters["epr_between_time"];
			$queryBuilder->where(":epr_between_time BETWEEN epr_start AND epr_end ");
		}

		$queryBuilder->where("epr_deleted = 0");

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$index][$field]);
					}
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $results;
	}
}