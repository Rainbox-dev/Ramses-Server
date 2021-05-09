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

    if (isset($_GET["ping"]))
    {
        $reply["accepted"] = true;
        $reply["query"] = "ping";
        $reply["content"]["version"] = $ramsesVersion;
        if ($installed)
        {
            $reply["content"]["installed"] = true;
            $reply["success"] = true;
            $reply["message"] = "Server ready.";
        }
        else
        {
            $reply["content"]["installed"] = false;
            $reply["success"] = false;
            $reply["message"] = "The server is not installed!";
        }
    }

    else if (isset($_GET["init"]))
	{
        $reply["accepted"] = true;
		$reply["query"] = "init";

        // The reply is completed in corresponding categories

		$reply["message"] = "Initial data retrieved.";
		$reply["success"] = true;
    }
?>