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
require_once("engine/bo/TrackBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/FormUtils.php");

session_start();

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);

$tracks = $trackBo->getByFilters(array("with_last_broadcast" => true, "with_number_of_broadcasts" => true));

?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
table tbody tr:nth-child(even) {
    background: #CCC
}

table tbody tr:nth-child(odd) {
    background: #FFF
}
        
    </style>
	<link rel="stylesheet" type="text/css" media="all" href="assets/css/datatables.min.css" />
	<script src="assets/js/jquery-3.3.1.min.js"></script>
	<script src="assets/js/datatables.min.js"></script>
</head>    
<body>
<img src="https://media.discordapp.net/attachments/361533120060850178/370296433431150602/banniereradioPPV18.png" style="width: 100px;"><br>

<a href="track.php">Ajouter une piste</a><br>
<br>

<table id="tracks" class="display" cellspacing="0">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Album</th>
            <th>Genres</th>
            <th>Durée</th>
            <th style="width: 140px;">Dernière diffusion</th>
            <th style="">Nombre diffusions</th>
            <th>Actions</th>
        </tr>        
    </thead>
    <tbody>
<?php   
        $duration = 0;

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
                <a href="track.php?id=<?php echo $track["tra_id"]; ?>">Éditer</a>
                <a href="do_deleteTrack.php?id=<?php echo $track["tra_id"]; ?>">Supprimer</a>
            </td>
        </tr>
<?php   }    ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Album</th>
            <th>Genres</th>
            <th>Durée</th>
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

<script>

/* global $ */

$(function() {
    $('#tracks tfoot th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Chercher dans '+title+'" />' );
    } );
 
    // DataTable
    var table = $('#tracks').DataTable({
        "order": [[6, "asc"], [0, "asc"]],
        "language": {
            "decimal":        ",",
            "emptyTable":     "Aucune piste disponible",
            "info":           "De _START_ à _END_ des _TOTAL_ pistes",
            "infoEmpty":      "De 0 à 0 des 0 pistes",
            "infoFiltered":   "(filtrées à partir des _MAX_ pistes)",
            "infoPostFix":    "",
            "thousands":      " ",
            "lengthMenu":     "Montrer _MENU_ pistes",
            "loadingRecords": "Chargement...",
            "processing":     "Traitement...",
            "search":         "Recherche:",
            "zeroRecords":    "Aucune piste trouvée",
            "paginate": {
                "first":      "Première",
                "last":       "Dernière",
                "next":       "Suivante",
                "previous":   "Précédente"
            },
            "aria": {
                "sortAscending":  ": activer pour ordonner la colonne de manière ascendante",
                "sortDescending": ": activer pour ordonner la colonne de manière descendante"
            }
        }
    });
 
    // Apply the search
    table.columns().every( function () {
        var that = this;
 
        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );    
    
});
</script>

</body>    
</html>