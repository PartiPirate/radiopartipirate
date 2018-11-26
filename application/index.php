<?php /*
	Copyright 2016 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("header.php");
require_once("engine/bo/ExceptionalProgramBo.php");
require_once("engine/bo/TemplateProgramBo.php");

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$templateProgramBo = new TemplateProgramBo($connection, $config);
$exceptionalProgramBo = new ExceptionalProgramBo($connection, $config);

$templates = $templateProgramBo->getByFilters(array());

function getHourFraction($time) {
    $times = explode(":", $time);
    
    return $times[0] + $times[1] / 60. + $times[2] / 3600.;
}

function getRoundedHourMinute($time) {
    $time = getHourFraction($time);
    $time = round($time * 60);
    
    $minutes = $time % 60;
    $hours = (($time - $minutes) / 60) % 24;

	$string = (($hours < 10) ? "0" : "") . $hours . ":" . (($minutes < 10) ? "0" : "") . $minutes;

	return $string;
}

$now = getNow();

if (isset($_REQUEST["startDate"])) {
    $startWeek = new DateTime($_REQUEST["startDate"]);
}
else {
    $startWeek = getNow();
}

if ($startWeek->format("w") == 0) {
    $days = 6;
}
else {
    $days = $startWeek->format("w") - 1;
}

if ($now->format("w") == 0) {
    $nowDay = 6;
}
else {
    $nowDay = $now->format("w") - 1;
}
$nowTime = $now->format("H:i:s");


$startWeek = $startWeek->sub(new DateInterval("P" . $days . "D"));
$startWeekString = $startWeek->format("Y-m-d");

$prevWeek = new DateTime($startWeekString); 
$prevWeek = $prevWeek->sub(new DateInterval("P7D")); 
$prevWeek = $prevWeek->format("Y-m-d");
$nextWeek = new DateTime($startWeekString); 
$nextWeek = $nextWeek->add(new DateInterval("P7D")); 
$nextWeek = $nextWeek->format("Y-m-d");

?>
<link rel="stylesheet" type="text/css" media="all" href="assets/css/radio.css" />
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li class="active"><?php echo lang("breadcrumb_index"); ?> </a></li>
	</ol>

<div id="semaine">
    <a href="?startDate=<?php echo $prevWeek; ?>">&lt;- Précédente</a>
    Semaine du <?php echo $startWeek->format("d/m/Y"); ?>
    <a href="?startDate=<?php echo $nextWeek; ?>">Suivante -&gt;</a>
</div>

<div id="tableau">
<div id="program-header" style="width: 100%; height: calc(40px); position: relative;">
    <div class="day" style="position: absolute; 
        left: calc(0 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Lundi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P0D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(1 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Mardi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P1D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(2 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Mercredi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P2D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(3 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Jeudi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P3D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(4 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Vendredi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P4D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(5 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Samedi <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P5D")); echo $day->format("d/m/Y"); ?></div>
    <div class="day" style="position: absolute; 
        left: calc(6 * 100% / 7); top: 0px; width: calc(100% / 7); height: 40px; 
        border: 1px solid black;
    ">Dimanche <?php $day = new DateTime($startWeekString); $day = $day->add(new DateInterval("P6D")); echo $day->format("d/m/Y"); ?></div>
</div>
<div id="program" style="width: calc(7 * calc(100% / 7)); height: calc(24 * 30px); position: relative;">
<?php   foreach($templates as $template) { 
    $template["tpr_day"] = $template["tpr_day"] ? $template["tpr_day"] - 1 : 6; 
?>
    <div class="template <?php if ($template["pen_class"]) echo $template["pen_class"];?>" data-id="<?php echo $template["tpr_id"]; ?>" 
    data-start="<?php echo getHourFraction($template["tpr_start"]); ?>"
    data-end="<?php echo getHourFraction($template["tpr_end"]); ?>"
    style="position: absolute;
        left: calc(<?php echo $template["tpr_day"]; ?> * 100% / 7); 
        top: calc(<?php echo getHourFraction($template["tpr_start"]); ?> * 30px); 
        width: calc(100% / 7);
        height: calc(<?php echo getHourFraction($template["tpr_end"]) - getHourFraction($template["tpr_start"]); ?> * 30px); 
        border: 1px solid black;
    " title="<?php echo $template["pen_title"]; ?> : <?php echo getRoundedHourMinute($template["tpr_start"]); ?> -> <?php echo getRoundedHourMinute($template["tpr_end"]); ?>">
        <?php echo getRoundedHourMinute($template["tpr_start"]); ?> -> <?php echo getRoundedHourMinute($template["tpr_end"]); ?><br>
        <?php echo $template["pen_title"]; ?>
    </div>
<?php   }   ?>

<?php
        for($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
            $day = new DateTime($startWeekString); 
            $day = $day->add(new DateInterval("P".$dayIndex."D")); 
            $exceptionals = $exceptionalProgramBo->getByFilters(array("epr_date" => $day->format("Y-m-d")));
            foreach($exceptionals as $exceptional) { 
?>
    <div class="exceptional <?php if ($exceptional["pen_class"]) echo $exceptional["pen_class"];?>" data-id="<?php echo $exceptional["epr_id"]; ?>" 
    data-start="<?php echo getHourFraction($exceptional["epr_start"]); ?>"
    data-end="<?php echo getHourFraction($exceptional["epr_end"]); ?>"
    style="position: absolute;
        left: calc(<?php echo $dayIndex; ?> * 100% / 7); 
        top: calc(<?php echo getHourFraction($exceptional["epr_start"]); ?> * 30px); 
        width: calc(100% / 7);
        height: calc(<?php echo getHourFraction($exceptional["epr_end"]) - getHourFraction($exceptional["epr_start"]); ?> * 30px); 
        border: 1px solid black;
    " title="<?php echo $exceptional["pen_title"]; ?> : <?php echo getRoundedHourMinute($exceptional["epr_start"]); ?> -> <?php echo getRoundedHourMinute($exceptional["epr_end"]); ?>">
        <?php echo getRoundedHourMinute($exceptional["epr_start"]); ?> -> <?php echo getRoundedHourMinute($exceptional["epr_end"]); ?><br>
        <?php echo $exceptional["pen_title"]; ?>
    </div>
<?php       }   
        }
?>
<?php    if (true) { ?>
    <div class="now"
    style="
        left: calc(<?php echo $nowDay; ?> * 100% / 7 + 10px); 
        top: calc(<?php echo getHourFraction($nowTime); ?> * 30px); 
    "
    title="Maintenant - <?php echo getRoundedHourMinute($nowTime); ?>"></div>
<?php    }   ?>
</div>

<div id="currentTrack">
</div>

</div>

</div>

<div class="lastDiv"></div>

<script type="text/javascript">
</script>
<?php include("footer.php");?>
<!--
<script src="assets/js/perpage/program.js"></script>
-->
</body>
</html>
