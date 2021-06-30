<?php
    $ramsesVersion = "0.0.1-dev";
	$installed = !file_exists("install/index.php");

	if (!isset($_SESSION["sessionToken"])) $_SESSION["sessionToken"] = "";
	if (!isset($_SESSION["userRole"])) $_SESSION["userRole"] = "standard";
	if (!isset($_SESSION["userUuid"])) $_SESSION["userUuid"] = "";
	if (!isset($_SESSION["login"])) $_SESSION["login"] = false;


	// server should keep session data for AT LEAST  sessionTimeout
	ini_set('session.gc_maxlifetime', $sessionTimeout);

	// each client should remember their session id for EXACTLY sessionTimeout
	session_set_cookie_params($sessionTimeout);

	session_start();

	$now = time();
	if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after'])
	{
		// this session has worn out its welcome; kill it and start a brand new one
		$_SESSION["sessionToken"] = "";
		$_SESSION["userRole"] = "standard";
		$_SESSION["userUuid"] = "";
		$_SESSION["login"] = false;
		session_unset();
		session_destroy();
		session_start();
	}
	else
	{
		// either new or old, it should live at most for sessionTimeout
		$_SESSION['discard_after'] = $now + $sessionTimeout;
	}

	//add the "_" after table prefix if needed
	setupTablePrefix();

	//build table names
	$applicationfiletypeTable = $tablePrefix . "applicationfiletype";
	$applicationsTable = $tablePrefix . "applications";
	$assetgroupsTable = $tablePrefix . "assetgroups";
	$assetsTable = $tablePrefix . "assets";
	$colorspacesTable = $tablePrefix . "colorspaces";
	$filetypesTable = $tablePrefix . "filetypes";
	$pipesTable = $tablePrefix . "pipes";
	$projectassetgroupTable = $tablePrefix . "projectassetgroup";
	$projectsTable = $tablePrefix . "projects";
	$sequencesTable = $tablePrefix . "sequences";
	$shotsTable = $tablePrefix . "shots";
	$statesTable = $tablePrefix . "states";
	$statusTable = $tablePrefix . "status";
	$stepapplicationTable = $tablePrefix . "stepapplication";
	$stepsTable = $tablePrefix . "steps";
	$stepuserTable = $tablePrefix . "stepuser";
	$templateassetgroupsTable = $tablePrefix . "templateassetgroups";
	$templatestepsTable = $tablePrefix . "templatesteps";
	$usersTable = $tablePrefix . "users";
	$pipefileTable = $tablePrefix . "pipefile";
	$pipefilepipeTable = $tablePrefix . "pipefilepipe";
	$shotassetTable = $tablePrefix . "shotasset";
	$scheduleTable = $tablePrefix . "schedule";
?>