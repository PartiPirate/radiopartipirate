<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate
	
	This file is part of Congressus.
	
	Congressus is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	Congressus is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class PersonaeThemeSource {

    function getGroupKey() {
        return "dlp_themes";
    }

    function getGroupKeyLabel() {
        return array("key" => "dlp_themes", "label" => lang("notice_themes"), "selectable" => true);
    }

    function getGroupOptions() {
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;

        $themeBo = ThemeBo::newInstance($connection, $config);
        $themes = $themeBo->getThemes(array("with_group_information" => true));

		echo "		<!-- Themes -->\n";
		echo "			<option class=\"dlp_themes\" value=\"0\" >Veuillez choisir un theme</option>\n";

        $groupLabel = null;

		foreach($themes as $listTheme) {
			if ($listTheme["gro_label"] != $groupLabel) {
				if ($groupLabel) {
					echo "  </optgroup>\n";
				}
				$groupLabel = $listTheme["gro_label"];
				echo "  <optgroup class=\"dlp_themes\" label=\"" . $groupLabel . "\">\n";
			}

			echo "    <option class=\"dlp_themes\"";
			echo "      value=\"" . $listTheme["the_id"] . "\">";
			echo		$listTheme["the_label"];
			echo "    </option>\n";
		}

		if ($groupLabel) {
			echo "  </optgroup>\n";
		}

    }

    function getGroupLabel($groupId) {
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;

        $themeBo = ThemeBo::newInstance($connection, $config);
        $themes = $themeBo->getThemes(array("with_group_information" => true, "the_id" => $groupId, "with_deleted" => true));

        if (count($themes)) return $themes[0]["the_label"];

        return null;
    }

    function updateNotice($meeting, &$notice, &$pings, &$usedPings) {
		require_once("engine/bo/FixationBo.php");
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;
        global $now;

		$fixationBo = FixationBo::newInstance($connection, $config);
        $themeBo = ThemeBo::newInstance($connection, $config);

		$theme = $themeBo->getTheme($notice["not_target_id"], true);

		$notice["not_label"] = $theme["the_label"];
		$notice["not_people"] = array();
		$notice["not_children"] = array();

		if ($theme["the_delegate_only"] == "1") {
			// We get all eligibles, because eligible is the only persons with voting rights (if the theme as a voting power)
			// In most cases eligibles and voters are the same
			$members = array();

			foreach($config["modules"]["groupsources"] as $groupSourceKey) {
				$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
	        	$groupKeyLabel = $groupSource->getGroupKeyLabel();
	
	        	if ($groupKeyLabel["key"] != $theme["the_eligible_group_type"]) continue;

	        	$members = $groupSource->getNoticeMembers(array("not_target_id" => $theme["the_eligible_group_id"]));
			}

			foreach($members as $member) {
				if (!$member["id_adh"]) continue;
				$people = array("mem_id" => $member["id_adh"]);
				$people["mem_nickname"] = htmlspecialchars(utf8_encode($member["pseudo_adh"] ? $member["pseudo_adh"] : $member["nom_adh"] . ' ' . $member["prenom_adh"]), ENT_SUBSTITUTE);
				$people["mem_power"] = $theme["the_voting_power"];
				$people["mem_voting"] = $notice["not_voting"];
				$people["mem_noticed"] = 1;
				$people["mem_present"] = 0;
				$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
				$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
	
				GroupSourceFactory::fixPing($pings, $usedPings, $people, $member, $now);
	
				$notice["not_people"][] = $people;
			}
		}
		else {
			$fixationMembers = $fixationBo->getFixations(array("fix_id" => $theme["the_current_fixation_id"], "with_fixation_members" => true));

			foreach($fixationMembers as $fixationMember) {
				if (!$fixationMember["id_adh"]) continue;
				$people = array("mem_id" => $fixationMember["id_adh"]);
				$people["mem_nickname"] = htmlspecialchars(utf8_encode($fixationMember["pseudo_adh"] ? $fixationMember["pseudo_adh"] : $fixationMember["nom_adh"] . ' ' . $fixationMember["prenom_adh"]), ENT_SUBSTITUTE);
				$people["mem_power"] = $fixationMember["fme_power"];
	//			$people["mem_power"] = $theme["the_voting_power"];
				$people["mem_voting"] = $notice["not_voting"];
				$people["mem_noticed"] = 1;
				$people["mem_present"] = 0;
				$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
				$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
	
				GroupSourceFactory::fixPing($pings, $usedPings, $people, $fixationMember, $now);
	
				$notice["not_people"][] = $people;
			}
		}

	}

    function getNoticeMembers($notice) {
		require_once("engine/bo/FixationBo.php");
        require_once("engine/bo/ThemeBo.php");
        global $config;
        global $connection;

		$fixationBo = FixationBo::newInstance($connection, $config);
        $themeBo = ThemeBo::newInstance($connection, $config);

		$theme = $themeBo->getTheme($notice["not_target_id"], true);
		$fixationMembers = $fixationBo->getFixations(array("fix_id" => $theme["the_current_fixation_id"], "with_fixation_members" => true));

		return $fixationMembers;
    }
    
    function addMotionNoticeVoters($queryBuilder, $filters) {
        global $config;
		//  personae theme

        $personaeDatabase = "";

        if (isset($config["personae"]["db"]) && $config["personae"]["db"]) {
            $personaeDatabase = $config["personae"]["db"];
            $personaeDatabase .= ".";
        }

		$queryBuilder->join($personaeDatabase."dlp_themes",					"t.the_id = not_target_id AND not_target_type = 'dlp_themes'",					"t", "left");
		$queryBuilder->join($personaeDatabase."dlp_fixations",				"tf.fix_id = t.the_current_fixation_id AND tf.fix_theme_type = 'dlp_themes'",	"tf", "left");

		if (isset($filters["vot_member_id"])) {
			$queryBuilder->join($personaeDatabase."dlp_fixation_members",	"tfm.fme_fixation_id = tf.fix_id AND tfm.fme_member_id = :vot_member_id",		"tfm", "left");
		}
		else {
			$queryBuilder->join($personaeDatabase."dlp_fixation_members",	"tfm.fme_fixation_id = tf.fix_id",												"tfm", "left");
		}

		if (false) {
			$queryBuilder->addSelect("tfm.fme_power", "ta_vote_power");
		}
		else {
			$queryBuilder->addSelect("t.the_voting_power", "ta_vote_power");
		}
		
		$queryBuilder->addSelect("ta.id_adh", "ta_id_adh");

		$userSource = UserSourceFactory::getInstance($config["modules"]["usersource"]);
		$userSource->upgradeQuery($queryBuilder, $config, "tfm.fme_member_id", "ta");
    }

    function getMaxVotepower($motion) {
    	return $motion["ta_vote_power"];
    }

    function getVoterNotNull() {
    	return "(ta.id_adh IS NOT NULL)";
    }
}

?>