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

class TrackLogBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "track_logs";
	var $ID_FIELD = "tlo_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TrackLogBo($pdo, $config);
	}

	function create(&$track) {
		return BoHelper::create($track, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($track) {
		return BoHelper::update($track, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$track) {
 		if (!isset($track[$this->ID_FIELD]) || !$track[$this->ID_FIELD]) {
			$this->create($track);
		}

		$this->update($track);
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
		$queryBuilder->addSelect("tracks.*");

		if ($filters && isset($filters["with_tracks"])) {
			$queryBuilder->join("tracks", "tra_id = tlo_track_id");
		}

		if ($filters && isset($filters["tlo_from_datetime"])) {
			$args["tlo_from_datetime"] = $filters["tlo_from_datetime"];
			$queryBuilder->where("tlo_datetime <= :tlo_from_datetime");
			$queryBuilder->where("DATE_ADD(tlo_datetime, INTERVAL tra_duration SECOND) >= :tlo_from_datetime");
		}

		$queryBuilder->orderDESCBy("tlo_datetime");

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
						$results[$index][$field] = utf8_decode($results[$index][$field]);
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