<?php
    require_once ('config.php');
    require_once ('functions.php');
    	
	$webTSSRoot = realpath(dirname(__FILE__));
	$mysqli = new mysqli($aGlobalConfig['database']['host'], $aGlobalConfig['database']['username'], $aGlobalConfig['database']['password'], $aGlobalConfig['database']['database'],$aGlobalConfig['database']['port']);
	
	if ($mysqli->connect_error) {
        cronPrint($mysqli->connect_error);
		cronPrint('Database configuration invalid. Abandoning further checks.');
    	die();
	}
	    
	$tssPermissions = substr(sprintf('%o', fileperms($webTSSRoot.'/tss')), -4);
	$webtssPermissions = substr(sprintf('%o', fileperms($webTSSRoot)), -4);
	$binsPermissions = substr(sprintf('%o', fileperms($webTSSRoot.'/bins')), -4);
	
	$runningUser = trim(shell_exec("whoami"));
	cronPrint("Script is running under \"".$runningUser."\". Is this the web server user?");
	cronPrint("This user's primary group is \"". trim(shell_exec("id -g -n ".$runningUser))."\". Does this group have access to \"".$webTSSRoot."\"?");
	// tss directory check.
	if(!is_dir($webTSSRoot.'/tss'))
		cronPrint('Please create the directory "'.$webTSSRoot.'/tss'.'"..');

	if(!is_writable($webTSSRoot.'/tss')) 
		cronPrint('Can\'t write to "'.$webTSSRoot.'/tss"..');
	
	if(!is_readable($webTSSRoot.'/tss'))
		cronPrint('Can\'t read "'.$webTSSRoot.'/tss"..');

	// base directory check.
	if(!is_dir($webTSSRoot))
		cronPrint('This is really bad.');

	if(!is_writable($webTSSRoot)) 
		cronPrint('Can\'t write to "'.$webTSSRoot.'"..');
	
	if(!is_readable($webTSSRoot))
		cronPrint('Can\'t read "'.$webTSSRoot.'"..');
			
	// bins directory check.	
	if(!is_dir($webTSSRoot.'/bins'))
		cronPrint('Please create the directory "'.$webTSSRoot.'/bins"..');
		
	if(!is_readable($webTSSRoot.'/bins'))
		cronPrint('Can\'t read "'.$webTSSRoot.'/bins"..');
			
	if(!is_writable($webTSSRoot.'/bins'))
		cronPrint('Can\'t read "'.$webTSSRoot.'/bins"..');

	// MySQLi check
	if(!$stmt = $mysqli->prepare("SELECT ecid FROM devices"))
		cronPrint('There might be a problem with your database configuration.');

	if(!$stmt->execute())
		cronPrint('Unable to execute SQL statement.');

	
	if(!$stmt->bind_result($ecid))
		cronPrint('Unable to bind result of SQL statement.');

	while ($stmt->fetch()) {
		cronPrint("Successfully able to loop through database content.");
		break;
	}

	$stmt->close();
	$mysqli->close();
	
	//Tsschecker test
    $binary = $aGlobalConfig["tssbinary"];
    $binarypath = $webTSSRoot."/bins/$binary";
    cronPrint("Detected tss binary for operating system is: $binarypath");
    
	// (Can I check the version without creating a python script for it?)
	if(!file_exists($binarypath))
		cronPrint("Could not find tsschecker at $binarypath");
	
	if(!is_executable($binarypath))
		cronPrint("tsschecker is not executable! Please fix it by running chmod +x $binarypath");
	
	cronPrint("The permissions of '".$webTSSRoot.'/tss'."' are ".$tssPermissions.". Is this enough to write and read?");
	cronPrint("The permissions of '".$webTSSRoot.'/bins'."' are ".$binsPermissions.". Is this enough to write and read?");
	cronPrint("The permissions of '".$webTSSRoot."' are ".$webtssPermissions.". Is this enough to write and read?");	
	
	cronPrint('verifyWorkingEnviroment.php exit.');
	