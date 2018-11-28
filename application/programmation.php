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
require_once("engine/bo/TemplateProgramBo.php");
require_once("engine/bo/ExceptionalProgramBo.php");
require_once("engine/bo/ProgramEntryBo.php");

if (!SessionUtils::getUserId($_SESSION)) {
    exit();
}


xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$connection = openConnection();

$programEntryBo = new ProgramEntryBo($connection, $config);
$programEntries = $programEntryBo->getByFilters(array());

$templateProgramBo = new TemplateProgramBo($connection, $config);
$templatePrograms = $templateProgramBo->getByFilters(array());

$exceptionalProgramBo = new ExceptionalProgramBo($connection, $config);
$exceptionalPrograms = $exceptionalProgramBo->getByFilters(array());

//print_r($programEntries);
$now = getNow();
$now = $now->format("Y-m-d");

?>
<div class="container theme-showcase" role="main">

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#periodic" aria-controls="periodic" role="tab" data-toggle="tab">Périodiques</a></li>
    <li role="presentation"><a href="#futures" aria-controls="futures" role="tab" data-toggle="tab">Exceptionnels</a></li>
    <li role="presentation"><a href="#pasts" aria-controls="pasts" role="tab" data-toggle="tab">Exceptionnels passés</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="periodic">

<table style="width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Jour</th>
            <th>Horaire de départ</th>
            <th>Horaire de fin</th>
            <th>Titre du programme</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    foreach($templatePrograms as $templateProgram) {
?>
        <tr data-json="<?php echo str_replace("\"", "&quot;", json_encode($templateProgram)); ?>">
            <td style="width: 15%;"><?php echo $templateProgram["tpr_id"]; ?></td>
            <td style="width: 10%;"><?php echo lang("day_" . $templateProgram["tpr_day"]); ?></td>
            <td style="width: 10%;"><?php echo $templateProgram["tpr_start"]; ?></td>
            <td style="width: 10%;"><?php echo $templateProgram["tpr_end"]; ?></td>
            <td style="width: 15%;"><a href="programs.php?id=<?php echo $templateProgram["tpr_program_entry_id"]; ?>"><?php echo $templateProgram["pen_title"]; ?></a></td>
            <td><button data-id="<?php echo $templateProgram["tpr_id"]; ?>" class="btn btn-sm btn-default to-update-template-btn">Editer</button> <button data-id="<?php echo $templateProgram["tpr_id"]; ?>" class="btn btn-sm btn-default to-split-btn">Split</button> <button data-id="<?php echo $templateProgram["tpr_id"]; ?>" class="btn btn-sm btn-danger to-delete-template-btn">Supprimer</button> <a href="programs.php?id=<?php echo $templateProgram["tpr_program_entry_id"]; ?>&action=edit" class="btn btn-sm btn-default">Editer le programme</a></td>
        </tr>
<?php        
    }
?>

    </tbody>
</table>

    <button class="btn btn-sm btn-success to-add-template-btn">Ajouter</button>

    </div>
    <div role="tabpanel" class="tab-pane" id="futures">

<table style="width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Horaire de départ</th>
            <th>Horaire de fin</th>
            <th>Titre du programme</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    foreach($exceptionalPrograms as $exceptionalProgram) {
        if ($exceptionalProgram["epr_date"] < $now) continue;
?>
        <tr data-json="<?php echo str_replace("\"", "&quot;", json_encode($exceptionalProgram)); ?>">
            <td style="width: 15%;"><?php echo $exceptionalProgram["epr_id"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_date"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_start"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_end"]; ?></td>
            <td style="width: 15%;"><a href="programs.php?id=<?php echo $exceptionalProgram["epr_program_entry_id"]; ?>"><?php echo $exceptionalProgram["pen_title"]; ?></a></td>
            <td><button data-id="<?php echo $exceptionalProgram["epr_id"]; ?>" class="btn btn-sm btn-default to-update-exceptional-btn">Editer</button> <button data-id="<?php echo $exceptionalProgram["epr_id"]; ?>" class="btn btn-sm btn-danger to-delete-exceptional-btn">Supprimer</button> <a href="programs.php?id=<?php echo $exceptionalProgram["epr_program_entry_id"]; ?>&action=edit" class="btn btn-sm btn-default">Editer le programme</a></td>
        </tr>
<?php        
    }
?>

    </tbody>
</table>

    <button class="btn btn-sm btn-success to-add-exceptional-btn">Ajouter</button>

    </div>
    <div role="tabpanel" class="tab-pane" id="pasts">

<table style="width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Horaire de départ</th>
            <th>Horaire de fin</th>
            <th>Titre du programme</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    foreach($exceptionalPrograms as $exceptionalProgram) {
        if ($exceptionalProgram["epr_date"] >= $now) continue;
?>
        <tr data-json="<?php echo str_replace("\"", "&quot;", json_encode($exceptionalProgram)); ?>">
            <td style="width: 15%;"><?php echo $exceptionalProgram["epr_id"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_date"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_start"]; ?></td>
            <td style="width: 10%;"><?php echo $exceptionalProgram["epr_end"]; ?></td>
            <td style="width: 15%;"><a href="programs.php?id=<?php echo $exceptionalProgram["epr_program_entry_id"]; ?>"><?php echo $exceptionalProgram["pen_title"]; ?></a></td>
            <td><button data-id="<?php echo $exceptionalProgram["epr_id"]; ?>" class="btn btn-sm btn-default to-update-exceptional-btn">Editer</button> <button data-id="<?php echo $exceptionalProgram["epr_id"]; ?>" class="btn btn-sm btn-danger to-delete-exceptional-btn">Supprimer</button> <a href="programs.php?id=<?php echo $exceptionalProgram["epr_program_entry_id"]; ?>&action=edit" class="btn btn-sm btn-default">Editer le programme</a></td>
        </tr>
<?php        
    }
?>

    </tbody>
</table>

    </div>
</div>

<div class="splitter" style="display: none;">
    <form action="do_split.php">
        <input id="tpr_id" type="numeric" value="">
        <input id="tpr_end" type="text" value="" data-max="" data-min="">
        <button class="btn btn-sm btn-success split-btn">Split</button>
    </form>
</div>

<div class="template-deleter" style="display: none;">
    <form action="do_deleteTemplate.php">
        <input id="tpr_id" type="numeric" value="">
        <button class="btn btn-sm btn-danger delete-template-btn">Supprimer</button>
    </form>
</div>

<div class="templater" style="display: none;">
    <form action="do_updateTemplate.php">
        <input id="tpr_id" type="numeric" value="">
        <select id="tpr_day">
<?php
        for($index = 0; $index < 7; $index++) {
?>        <option value="<?php echo $index; ?>"><?php echo lang("day_" . $index); ?></option>   <?php         
        }
?>        
        </select>
        <input id="tpr_start" type="text" value="00:00:00" data-max="00:00:00" data-min="23:59:59">
        <input id="tpr_end" type="text" value="23:59:59" data-max="00:00:00" data-min="23:59:59">

        <select id="tpr_program_entry_id">
<?php
        foreach($programEntries as $programEntry) {
?>        <option value="<?php echo $programEntry["pen_id"]; ?>"><?php echo $programEntry["pen_title"]; ?></option>   <?php         
        }
?>        
        </select>
        
        <button class="btn btn-sm btn-success update-template-btn">Mettre à jour</button>
    </form>
</div>

<div class="exceptional-deleter" style="display: none;">
    <form action="do_deleteExceptional.php">
        <input id="epr_id" type="numeric" value="">
        <button class="btn btn-sm btn-danger delete-exceptional-btn">Supprimer</button>
    </form>
</div>

<div class="exceptionaler" style="display: none;">
    <form action="do_updateExceptional.php">
        <input id="epr_id" type="numeric" value="">
        <input id="epr_date" type="date" value="">
        <input id="epr_start" type="text" value="00:00:00" data-max="00:00:00" data-min="23:59:59">
        <input id="epr_end" type="text" value="23:59:59" data-max="00:00:00" data-min="23:59:59">

        <select id="epr_program_entry_id">
<?php
        foreach($programEntries as $programEntry) {
?>        <option value="<?php echo $programEntry["pen_id"]; ?>"><?php echo $programEntry["pen_title"]; ?></option>   <?php         
        }
?>        
        </select>
        
        <button class="btn btn-sm btn-success update-exceptional-btn">Mettre à jour</button>
    </form>
</div>
    
</div>
<div class="lastDiv"></div>

<script type="text/javascript">
</script>
<?php include("footer.php");?>
</body>
</html>
