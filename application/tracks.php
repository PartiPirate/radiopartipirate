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

include_once("header.php");
require_once("engine/bo/TrackBo.php");

if (!SessionUtils::getUserId($_SESSION)) {
    exit();
}


xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

$tracks = $trackBo->getByFilters(array("with_last_broadcast" => true, "with_number_of_broadcasts" => true));

?>    <style>
table tbody tr:nth-child(even) {
    background: #CCC
}

table tbody tr:nth-child(odd) {
    background: #FFF
}
        
    </style>
	<link rel="stylesheet" type="text/css" media="all" href="assets/css/radio.css" />
	<link rel="stylesheet" type="text/css" media="all" href="assets/css/datatables.min.css" />
	<script src="assets/js/datatables.min.js"></script>

<a href="track.php?id=0" class="add-track-link">Ajouter une piste</a><br>
<br>

<table id="tracks" class="display hover order-column" cellspacing="0">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Album</th>
            <th>Genres</th>
            <th>Durée</th>
            <th>Début (en s)</th>
            <th>Fin (en s)</th>
            <th style="width: 140px;">Dernière diffusion</th>
            <th style="">Nombre diffusions</th>
            <th>Actions</th>
        </tr>        
    </thead>
    <tbody>
<?php   
/*        $duration = 0;

        foreach($tracks as $index => $track) {    
            $duration += $track["tra_duration"];
?>
        <tr>
            <td><?php echo utf8_encode($track["tra_title"]); ?></td>
            <td><?php echo utf8_encode($track["tra_author"]); ?></td>
            <td><?php echo utf8_encode($track["tra_album"]); ?></td>
            <td><?php echo utf8_encode($track["tra_genres"]); ?></td>
            <td style="text-align: right;"><?php echo $trackBo->getTimeString($track["tra_duration"]); ?></td>
            <td style="text-align: right;"><?php echo $track["tra_last_broadcast"]; ?></td>
            <td style="text-align: right;"><?php echo $track["tra_number_of_broadcasts"]; ?></td>
            <td style="text-align: right;">
                <a href="track.php?id=<?php echo $track["tra_id"]; ?>" class="modify-link">Éditer</a>
                <a href="do_deleteTrack.php?id=<?php echo $track["tra_id"]; ?>">Supprimer</a>
            </td>
        </tr>
<?php   }    */?>
    </tbody>
    <tfoot>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Album</th>
            <th>Genres</th>
            <th>Durée</th>
            <th>Début (en s)</th>
            <th>Fin (en s)</th>
            <th>Dernière diffusion</th>
            <th>Nombre diffusions</th>
            <th>Actions</th>
        </tr>
<!--
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th><?php echo $trackBo->getTimeString($duration); ?></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>        
-->
    </tfoot>
</table>

<div id="track-div"></div>

<div class="lastDiv"></div>

<script type="text/javascript">
</script>
<?php include("footer.php");?>
</body>
</html>
