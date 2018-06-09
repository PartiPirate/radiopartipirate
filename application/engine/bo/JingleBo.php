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

class JingleBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "jingles";
	var $ID_FIELD = "jin_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new JingleBo($pdo, $config);
	}

	function create(&$jingle) {
		return BoHelper::create($jingle, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($jingle) {
		return BoHelper::update($jingle, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$jingle) {
 		if (!isset($jingle[$this->ID_FIELD]) || !$jingle[$this->ID_FIELD]) {
			$this->create($jingle);
		}

		$this->update($jingle);
	}

	function delete($jingle) {
 		if (!isset($jingle[$this->ID_FIELD]) || !$jingle[$this->ID_FIELD]) return;

		$args = array();
		$args["jin_id"] = $jingle["jin_id"];
		
		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->delete($this->TABLE);

		$queryBuilder->where("jin_id = :jin_id");
		
		$query = $queryBuilder->constructRequest();

//		echo showQuery($query, $args);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
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

		if ($filters && isset($filters["jin_id"])) {
			$args["jin_id"] = $filters["jin_id"];
			$queryBuilder->where("jin_id = :jin_id");
		}

		if ($filters && isset($filters["jin_url"])) {
			$args["jin_url"] = $filters["jin_url"];
			$queryBuilder->where("jin_url = :jin_url");
		}

		if ($filters && isset($filters["jin_data"])) {
			$args["jin_data"] = $filters["jin_data"];
			$queryBuilder->where("jin_data = :jin_data");
		}

		if ($filters && isset($filters["jin_type"])) {
			$args["jin_type"] = $filters["jin_type"];
			$queryBuilder->where("jin_type = :jin_type");
		}


		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		print_r($filters);
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
					else {
						$results[$index][$field] = $results[$index][$field];
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