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

    // ========= CREATE ==========
    if (isset($_GET["createApplication"]))
    {
        $reply["accepted"] = true;
        $reply["query"] = "createApplication";

        $name = $_GET["name"] ?? "";
		$shortName = $_GET["shortName"] ?? "";
        $executableFilePath = $_GET["executableFilePath"] ?? "";
		$uuid = $_GET["uuid"] ?? "";

        if (strlen($shortName) > 0)
        {
            // Only if admin
            if ( isProjectAdmin() )
            {
                $qString = "INSERT INTO " . $tablePrefix . "applications (`name`,`shortName`,`executableFilePath`,`uuid`) VALUES ( :name , :shortName , :executableFilePath , ";
                $values = array('name' => $name,'shortName' => $shortName, 'uuid' => $uuid, 'executableFilePath' => $executableFilePath);

                if (strlen($uuid) > 0)
                {
                    $qString = $qString . ":uuid";
                    $values['uuid'] = $uuid;
                }
                else 
                {
                    $qString = $qString . "uuid()";
                }

                $qString = $qString . " ) ON DUPLICATE KEY UPDATE shortName = VALUES(shortName), name = VALUES(name), extensions = VALUES(extensions), executableFilePath = VALUES(executableFilePath), removed = 0;";

                $rep = $db->prepare($qString);
                $rep->execute($values);
                $rep->closeCursor();         
    
                $reply["message"] = "Application \"" . $shortName . "\" created.";
                $reply["success"] = true;
            }
            else
            {
                $reply["message"] = "Insufficient rights, you need to be Project Admin to create applications.";
                $reply["success"] = false;
            }
        }
        else
        {
            $reply["message"] = "Invalid request, missing values";
            $reply["success"] = false;
        }
    }

    // ========= UPDATE ==========
	else if (isset($_GET["updateApplication"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "updateApplication";

		$name = $_GET["name"] ?? "";
		$shortName = $_GET["shortName"] ?? "";
        $executableFilePath = $_GET["executableFilePath"] ?? "";
		$uuid = $_GET["uuid"] ?? "";

		if (strlen($shortName) > 0 AND strlen($uuid) > 0)
		{
			// Only if admin
            if ( isProjectAdmin() )
            {
				$qString = "UPDATE " . $tablePrefix . "applications
				SET
					`name`= :name ,
					`shortName`= :shortName,
                    `executableFilePath`= :executableFilePath
				WHERE
					uuid= :uuid ;";
				$values = array('name' => $name,'shortName' => $shortName,'executableFilePath' => $executableFilePath, 'uuid' => $uuid);

                $rep = $db->prepare($qString);
				
                $rep->execute($values);
                $rep->closeCursor();

				$reply["message"] = "Application \"" . $shortName . "\" updated.";
				$reply["success"] = true;
			}
			else
            {
                $reply["message"] = "Insufficient rights, you need to be Project Admin to update application information.";
                $reply["success"] = false;
            }
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}
	}

	// ========= REMOVE ==========
	else if (isset($_GET["removeApplication"]))
	{
		$reply["accepted"] = true;
		$reply["query"] = "removeApplication";

		$uuid = $_GET["uuid"] ?? "";

		if (strlen($uuid) > 0)
		{
			//only if project admin
			if (isProjectAdmin())
			{
				$rep = $db->prepare("UPDATE " . $tablePrefix . "applications SET removed = 1 WHERE uuid= :uuid ;");
				$rep->execute(array('uuid' => $uuid));
				$rep->closeCursor();

				$reply["message"] = "Application " . $uuid . " removed.";
				$reply["success"] = true;
			}
			else
            {
                $reply["message"] = "Insufficient rights, you need to be Project Admin to remove applications.";
                $reply["success"] = false;
            }
		}
		else
		{
			$reply["message"] = "Invalid request, missing values";
			$reply["success"] = false;
		}
	}

    // ========= GET ==========
    else if (isset($_GET["getApplications"]))
    {
        $reply["accepted"] = true;
        $reply["query"] = "getApplications";
        
        $rep = $db->prepare("SELECT `name`,`shortName`,`executableFilePath`,`uuid` FROM " . $tablePrefix . "applications WHERE removed = 0;");
        $rep->execute();

        $applications = Array();

        while ($a = $rep->fetch())
        {
            $application = Array();
			$application['name'] = $a['name'];
			$application['shortName'] = $a['shortName'];
			$application['uuid'] = $a['uuid'];
			$application['executableFilePath'] = $a['executableFilePath'];

			$applications[] = $application;
        }

        $rep->closeCursor();

		$reply["content"] = $applications;
		$reply["message"] = "Application list retrieved.";
		$reply["success"] = true;
    }
?>
