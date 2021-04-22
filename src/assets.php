<?php
    /*
		Ramses: Rx Asset Management System
        
        This program is licensed under the GNU General Public License.

        Copyright (C) 20202-2021 Nicolas Dufresne and Contributors.

        This program is free software;
        you can redistribute it and/or modify it
        under the terms of the GNU General Public License
        as published by the Free Software Foundation;
        either version 3 of the License, or (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
        See the GNU General Public License for more details.

        You should have received a copy of the *GNU General Public License* along with this program.
        If not, see http://www.gnu.org/licenses/.
	*/

	// ========= CREATE ASSET ==========
	if (isset($_GET["createAsset"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "createAsset";

		$name = $_GET["name"] ?? "";
		$shortName = $_GET["shortName"] ?? "";
		$assetGroupUuid = $_GET["assetGroupUuid"] ?? "";
		$tags = $_GET["tags"] ?? "";
		$uuid = $_GET["uuid"] ?? "";

		if (strlen($shortName) > 0)
		{
			// Only if lead
            if ( isLead() )
            {
				$qString = "INSERT INTO {$assetsTable} (`name`, `shortName`, `assetGroupId`, `tags`, `uuid`)
				VALUES (
					:name,
					:shortName,
					(SELECT {$assetgroupsTable}.`id` FROM {$assetgroupsTable} WHERE `uuid` = :assetGroupUuid ),
					:tags,";
				
				$values = array( 'name' => $name,'shortName' => $shortName, 'assetGroupUuid' => $assetGroupUuid, 'tags' => $tags);

				if (strlen($uuid) > 0)
				{
					$qString = $qString . ":uuid ";
					$values['uuid'] = $uuid;
				}
				else
				{
					$qString = $qString . "uuid() ";
				}

				$qString = $qString . ") ON DUPLICATE KEY UPDATE shortName = VALUES(shortName), name = VALUES(name), tags = VALUES(tags), removed = 0;";

				$rep = $db->prepare($qString);
				$ok = $rep->execute($values);
				$rep->closeCursor();

				if ($ok) $reply["message"] = "Asset \"" . $shortName . "\" added.";
				else $reply["message"] = $rep->errorInfo();

				$reply["success"] = $ok;
			}
			else
            {
                $reply["message"] = "Insufficient rights, you need to be Lead to create assets.";
                $reply["success"] = false;
            }
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}
	}
	
	// ========= UPDATE ASSET ==========
	else if (isset($_GET["updateAsset"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "updateAsset";

		$name = $_GET["name"] ?? "";
		$shortName = $_GET["shortName"] ?? "";
		$tags = $_GET["tags"] ?? "";
		$assetGroupUuid = $_GET["assetGroupUuid"] ?? "";
		$uuid = $_GET["uuid"] ?? "";

		if (strlen($shortName) > 0 AND strlen($uuid) > 0)
		{
			// Only if lead
            if ( isLead() )
            {
				$qString = "INSERT INTO {$assetsTable} (`name`, `shortName`, `tags`, `uuid`, `assetGroupId`)
				VALUES(
					:name ,
					:shortName,
					:tags,
					:uuid,
					(SELECT {$assetgroupsTable}.`id` FROM {$assetgroupsTable} WHERE `uuid` = :assetGroupUuid )
				)
				AS newAsset
				ON DUPLICATE KEY UPDATE
					`name` = newAsset.`name`,
					`tags` = newAsset.`tags`,
					`assetGroupId` = newAsset.`assetGroupId`,
					`removed` = 0;
				UPDATE {$assetsTable}
				SET `shortName` = :shortName
				WHERE `uuid` = :uuid;";

				$values = array('name' => $name,'shortName' => $shortName, 'tags' => $tags, 'assetGroupUuid' => $assetGroupUuid, 'uuid' => $uuid);

                $rep = $db->prepare($qString);
                $rep->execute($values);
                $rep->closeCursor();

				$reply["message"] = "Asset \"" . $shortName . "\" updated.";
				$reply["success"] = true;
			}
			else
            {
                $reply["message"] = "Insufficient rights, you need to be Lead to update asset information.";
                $reply["success"] = false;
            }
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}

	}

	// ========= REMOVE ASSET ==========
	else if (isset($_GET["removeAsset"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "removeAsset";

		$uuid = $_GET["uuid"] ?? "";

		if (strlen($uuid) > 0)
		{
			//only if admin
			if (isLead())
			{
				$rep = $db->prepare("UPDATE " . $tablePrefix . "assets SET removed = 1 WHERE uuid= :uuid ;");
				$rep->execute(array('uuid' => $uuid));
				$rep->closeCursor();

				$reply["message"] = "Asset " . $uuid . " removed.";
				$reply["success"] = true;
			}
			else
            {
                $reply["message"] = "Insufficient rights, you need to be Lead to remove assets.";
                $reply["success"] = false;
            }
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}
	}

	// ========= SET STATUS ==========
	else if (isset($_GET["setAssetStatus"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "setAssetStatus";

		$uuid = $_GET["uuid"] ?? "";
		$assetUuid = $_GET["assetUuid"] ?? "";
		$completionRatio = $_GET["completionRatio"] ?? -1;
		$userUuid = $_GET["userUuid"] ?? $_SESSION["userUuid"];
		$stateUuid = $_GET["stateUuid"] ?? "";
		$comment = $_GET["comment"] ?? "";
		$version = $_GET["version"] ?? 1;
		$stepUuid = $_GET["stepUuid"] ?? 1;

		if (strlen($assetUuid) > 0 && strlen($userUuid) > 0 && strlen($stateUuid) > 0 && strlen($stepUuid) > 0 )
		{
			$qString = "INSERT INTO {$statusTable} (
				`uuid`,
				`completionRatio`,
				`userId`,
				`stateId`,
				`comment`,
				`version`,
				`stepId`,
				`assetId`
				)
				VALUES(
					:uuid ,
					:completionRatio,
					SELECT {$usersTable}.`id` FROM {$usersTable} WHERE {$usersTable}.`uuid` = :userUuid,
					SELECT {$statesTable}.`id` FROM {$statesTable} WHERE {$statesTable}.`uuid` = :stateUuid,
					:comment,
					:version,
					SELECT {$stepsTable}.`id` FROM {$stepsTable} WHERE {$stepsTable}.`uuid` = :stepUuid,
					SELECT {$assetsTable}.`id` FROM {$assetsTable} WHERE {$assetsTable}.`uuid` = :assetUuid
				)
				AS newStatus
				ON DUPLICATE KEY UPDATE
					`comment` = newStatus.`comment`,
					`completionRatio` = newStatus.`completionRatio`,
					`version` = newStatus.`version`,
					`removed` = 0;";

			$rep = $db->prepare($qString);
			$rep->execute(array(
				'uuid' => $uuid,
				'completionRation' => $completionRatio,
				'userUuid' => $userUuid,
				'stateUuid' => $stateUuid,
				'comment' => $comment,
				'stepUuid' => $stepUuid,
				'version' => $version,
				'assetUuid' => $assetUuid
			));
			$rep->closeCursor();

			$reply["message"] = "Asset status updated.";
			$reply["success"] = true;
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}
	}
?>
