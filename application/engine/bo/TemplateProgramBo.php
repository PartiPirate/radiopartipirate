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

class TemplateProgramBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "template_programs";
	var $ID_FIELD = "tpr_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TemplateProgramBo($pdo, $config);
	}

	function create(&$templateProgram) {
		return BoHelper::create($templateProgram, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($templateProgram) {
		return BoHelper::update($templateProgram, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$templateProgram) {
 		if (!isset($templateProgram[$this->ID_FIELD]) || !$templateProgram[$this->ID_FIELD]) {
			$this->create($templateProgram);
		}

		$this->update($templateProgram);
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
		$queryBuilder->join("program_entries", "pen.pen_id = tpr_program_entry_id", "pen", "left");
		$queryBuilder->addSelect("pen.*");

		if ($filters && isset($filters["tpr_day"])) {
			$args["tpr_day"] = $filters["tpr_day"];
			$queryBuilder->where("tpr_day = :tpr_day");
		}

		if ($filters && isset($filters["tpr_between_time"])) {
			$args["tpr_between_time"] = $filters["tpr_between_time"];
			$queryBuilder->where(":tpr_between_time BETWEEN tpr_start AND tpr_end ");
		}

		$queryBuilder->orderASCBy("tpr_day");
		$queryBuilder->orderASCBy("tpr_start");

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