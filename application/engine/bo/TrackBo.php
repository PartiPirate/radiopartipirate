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

	function delete($track) {
 		if (!isset($track[$this->ID_FIELD]) || !$track[$this->ID_FIELD]) return;

		$args = array();
		$args["tra_id"] = $track["tra_id"];
		
		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);
		$queryBuilder->delete($this->TABLE);

		$queryBuilder->where("tra_id = :tra_id");
		
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

		if (isset($parameters["music"])) {
			if ($parameters["music"] == "no" || $parameters["music"] == "false") return array();
		}
		else if (isset($parameters["track"])) {
			$filters["tra_id"] = $parameters["track"];
		}
		else if (isset($parameters["filter"])) {

			if (isset($parameters["filter"]["genre"])) {
				$filters["tra_genres"] = $parameters["filter"]["genre"];
			}
			
			if (isset($parameters["filter"]["genres"])) {
				$filters["tra_like_genres"] = $parameters["filter"]["genres"];
			}

			if (isset($parameters["filter"]["maxDuration"])) {
				$filters["tra_max_duration"] = $parameters["filter"]["maxDuration"];
			}

			if (isset($parameters["filter"]["minDuration"])) {
				$filters["tra_min_duration"] = $parameters["filter"]["minDuration"];
			}

		}

//		print_r($filters);

		$noReplayInterval = "P0DT" . $noReplayTime . "H";

		$filters["tlo_from_datetime"] = getNow()->sub(new DateInterval($noReplayInterval))->format("Y-m-d H:i:s");

		$filters["with_number_of_broadcasts"] = true;

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

		if ($filters && isset($filters["tra_max_duration"])) {
			$args["tra_max_duration"] = $filters["tra_max_duration"];
			$queryBuilder->where("tra_duration <= :tra_max_duration");
		}

		if ($filters && isset($filters["tra_min_duration"])) {
			$args["tra_min_duration"] = $filters["tra_min_duration"];
			$queryBuilder->where("tra_duration >= :tra_min_duration");
		}

		if ($filters && isset($filters["tra_like_genres"])) {
			if (is_array($filters["tra_like_genres"])) {
				if (count($filters["tra_like_genres"])) {
					$separator = "";
					$whereClause = " ( ";

					foreach($filters["tra_like_genres"] as $index => $genre) {
						$genre = strtolower("%" . $filters["tra_like_genres"][$index] . "%");
						$whereClause .= $separator . " LOWER(tra_genres) LIKE :tra_like_genres_$index ";
						$args["tra_like_genres_$index"] = $genre;
						$separator = " OR ";
						
					}
	
					$whereClause .= " ) ";

					$queryBuilder->where($whereClause);
				}
			}
			else {
				$args["tra_like_genres"] = strtolower("%" . $filters["tra_like_genres"] . "%");
				$queryBuilder->where("LOWER(tra_genres) LIKE :tra_like_genres");
			}
		}

		if ($filters && isset($filters["tra_genres"])) {
			if (is_array($filters["tra_genres"])) {
				if (count($filters["tra_genres"])) {
					$separator = "";
					$whereClause = " ( ";

					foreach($filters["tra_genres"] as $index => $genre) {
						$genre = strtolower($filters["tra_genres"][$index]);
						$whereClause .= $separator . " LOWER(tra_genres) = :tra_genres_$index ";
						$args["tra_genres_$index"] = $genre;
						$separator = " OR ";
						
					}
	
					$whereClause .= " ) ";

					$queryBuilder->where($whereClause);
				}
			}
			else {
				$args["tra_genres"] = strtolower($filters["tra_genres"]);
				$queryBuilder->where("LOWER(tra_genres) ) :tra_genres");
			}
		}

		if ($filters && isset($filters["tlo_from_datetime"])) {
			$queryBuilder->join("track_logs", "tlo_track_id = tra_id", "tlo", "LEFT");
			
			$args["tlo_from_datetime"] =$filters["tlo_from_datetime"];
			$queryBuilder->where(" ((SELECT MAX(tlo_datetime) FROM track_logs WHERE tlo_track_id = tra_id ) < :tlo_from_datetime OR tlo_datetime IS NULL) ");
		}

		if ($filters && isset($filters["with_last_broadcast"])) {
			$queryBuilder->addSelect(" (SELECT MAX(tlo_datetime) FROM track_logs WHERE tlo_track_id = tra_id )  ", "tra_last_broadcast");
		}

		if ($filters && isset($filters["with_number_of_broadcasts"])) {
			$queryBuilder->addSelect(" (SELECT COUNT(tlo_track_id) FROM track_logs WHERE tlo_track_id = tra_id )  ", "tra_number_of_broadcasts");
			$queryBuilder->orderASCBy("tra_number_of_broadcasts");
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
	
	public function onlineSearch($title) {
		// Search song
		//$search = "https://www.allmusic.com/search/songs/" . urlencode($_REQUEST["title"]. " - " . $_REQUEST["author"]);
		$search = "https://www.allmusic.com/search/songs/" . urlencode($title);
		//echo $search . "<br>\n";
		
		$content = file_get_contents($search);
		
		$content = substr($content, strpos($content, "<h4>Song</h4>"));
		$content = substr($content, strpos($content, "href=\"") + 6);
		$index = strpos($content, "\"");
		$url = substr($content, 0, $index);
		
		// Read data song
		
		//echo $url . "<br>\n";
		
		if (strpos($url, "//cdn-gce") === false) {
			$content = file_get_contents($url);
			
			$artistContent = substr($content, strpos($content, "song-artist"));
			$artistContent = substr($artistContent, strpos($artistContent, "href"));
			$artistContent = substr($artistContent, strpos($artistContent, ">"));
			$artistContent = substr($artistContent, 1, strpos($artistContent, "<") - 1);
			//echo "Artist : $artistContent <br>\n";
			
			$titleContent = substr($content, strpos($content, "song-title"));
			$titleContent = substr($titleContent, strpos($titleContent, ">"));
			$titleContent = trim(substr($titleContent, 1, strpos($titleContent, "<") - 1));
			//echo "Title : $titleContent <br>\n";
			
			$genresContent = substr($content, strpos($content, "<h4>Song Genres</h4>"), strpos($content, "<div class=\"bottom\">") - strpos($content, "<h4>Song Genres</h4>"));
			$genresContent = substr($genresContent, strpos($genresContent, "<div class=\"middle\">") + 20);
			
			$genres = array();
			while (strpos($genresContent, "genre") !== false) {
			    $genresContent = substr($genresContent, strpos($genresContent, "genre"));
			    $genresContent = substr($genresContent, strpos($genresContent, ">"));
			    $index = strpos($genresContent, "(");
			    $genres[] = trim(substr($genresContent, 1, $index - 1));
			    $genresContent = substr($genresContent, $index);
			}
			
			if (strpos($genres[0], "Would you like") !== false) {
			    $genres = "";
			}
			else {
			    $genres = implode(", ", $genres);
			}
			
			//echo "Genres : $genres <br>\n";
			
			//$genresContent = substr($genresContent, strpos($genresContent, ">"));
			//$genresContent = trim(substr($genresContent, 1, strpos($genresContent, "<") - 1));
			//echo $genresContent . "<br>\n";
			
			$albumContent = substr($content, strpos($content, "inAlbum"));
			$albumContent = substr($albumContent, strpos($albumContent, "title") + 7);
			$albumContent = trim(substr($albumContent, 0, strpos($albumContent, "\"")));
			//echo "Album : $albumContent <br>\n";
			
			//echo $content . "<br>\n";

			$track = array();
	
			$track["tra_title"] = $title;
			$track["tra_author"] = $artistContent;
			$track["tra_album"] = $albumContent;
			$track["tra_genres"] = $genres;

			return $track;
		}
		
		
		return null;
	}
	
	function getTimeString($duration) {
		$seconds = $duration % 60;
		$duration = ($duration - $seconds) / 60;

		$minutes = $duration % 60;
		$duration = ($duration - $minutes) / 60;

		$hours = $duration;

		$string = (($hours < 10) ? "0" : "") . $hours . ":" . (($minutes < 10) ? "0" : "") . $minutes . ":" . (($seconds < 10) ? "0" : "") . $seconds;

		return $string;
	}
}