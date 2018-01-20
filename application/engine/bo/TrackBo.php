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

class TrackBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "tracks";
	var $ID_FIELD = "tra_id";

	function __construct($pdo, $config) {
		$this->config = $config;

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new TrackBo($pdo, $config);
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


	function getByUrl($url) {
		$filters = array("tra_url" => $url);

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByProgramParameters($parameters, $noReplayTime) {
		if (!is_array($parameters)) {
			$parameters = json_decode($parameters, true);
		}

		$filters = array();

		if (isset($parameters["track"])) {
			$filters["tra_id"] = $parameters["track"];
		}
		else if (isset($parameters["filter"])) {
			if (isset($parameters["filter"]["genre"])) {
				$filters["tra_like_genres"] = $parameters["filter"]["genre"];
			}
		}

		$noReplayInterval = "P0DT" . $noReplayTime . "H";

		$filters["tlo_from_datetime"] = getNow()->sub(new DateInterval($noReplayInterval))->format("Y-m-d H:i:s");

		$tracks = $this->getByFilters($filters);
		
		return $tracks;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->select($this->TABLE);
		$queryBuilder->setDistinct();
		$queryBuilder->addSelect($this->TABLE . ".*");

		if ($filters && isset($filters["tra_id"])) {
			$args["tra_id"] = $filters["tra_id"];
			$queryBuilder->where("tra_id = :tra_id");
		}

		if ($filters && isset($filters["tra_url"])) {
			$args["tra_url"] = $filters["tra_url"];
			$queryBuilder->where("tra_url = :tra_url");
		}

		if ($filters && isset($filters["tra_like_genres"])) {
			$args["tra_like_genres"] = strtolower("%" . $filters["tra_like_genres"] . "%");
			$queryBuilder->where("LOWER(tra_genres) LIKE :tra_like_genres");
		}

		if ($filters && isset($filters["tlo_from_datetime"])) {
			$queryBuilder->join("track_logs", "tlo_track_id = tra_id", "tlo", "LEFT");
			
			$args["tlo_from_datetime"] =$filters["tlo_from_datetime"];
			$queryBuilder->where(" (tlo_datetime < :tlo_from_datetime OR tlo_datetime IS NULL) ");
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