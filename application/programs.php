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
require_once("engine/bo/ProgramEntryBo.php");
require_once("engine/bo/TrackBo.php");

if (!SessionUtils::getUserId($_SESSION)) {
    exit();
}


xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$connection = openConnection();

$trackBo = new TrackBo($connection, $config);
$programEntryBo = new ProgramEntryBo($connection, $config);
$programEntries = $programEntryBo->getByFilters(array());

foreach($programEntries as $programIndex => $programEntry) {
    $tracks = $trackBo->getByProgramParameters($programEntry["pen_parameters"]);
    
    $programEntries[$programIndex]["pen_number_of_tracks"] = count($tracks);
}

//print_r($programEntries);

?>
<div class="container theme-showcase" role="main">

<div id="program-entries">
    <table style="width: 100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Classe</th>
                <th>Parametres</th>
                <th>Nombre de pistes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    
    <?php
        foreach($programEntries as $programEntry) {
    ?>
            <tr data-json="<?php echo str_replace("\"", "&quot;", json_encode($programEntry)); ?>">
                <td style="width: 10%;"><?php echo $programEntry["pen_id"]; ?></td>
                <td style="width: 20%;"><?php echo $programEntry["pen_title"]; ?></td>
                <td style="width: 10%;"><?php echo $programEntry["pen_class"]; ?></td>
                <td style="width: 30%;"><?php echo $programEntry["pen_parameters"]; ?></td>
                <td style="width: 10%;"><?php echo $programEntry["pen_number_of_tracks"]; ?></td>
                <td>Editer <button data-id="<?php echo $programEntry["pen_id"]; ?>" class="btn btn-sm btn-danger to-delete-program-btn">Supprimer</button></td>
            </tr>
    <?php        
        }
    ?>
    
        </tbody>
    </table>
</div>

<div class="program-deleter" style="display: none;">
    <form action="do_deleteProgramEntry.php">
        <input id="pen_id" type="numeric" value="">
        <button class="btn btn-sm btn-danger delete-program-btn">Supprimer</button>
    </form>
</div>
    
</div>
<div class="lastDiv"></div>

<script type="text/javascript">
</script>
<?php include("footer.php");?>
</body>
</html>
