<?php
	require 'config.php';
	require 'functions.php';
	
	$webTSSRoot = realpath(dirname(__FILE__));
	$mysqli = new mysqli($aGlobalConfig['database']['host'], $aGlobalConfig['database']['username'], $aGlobalConfig['database']['password'], $aGlobalConfig['database']['database']);
	$tssPermissions = substr(sprintf('%o', fileperms($webTSSRoot.'/tss')), -4);
	$webtssPermissions = substr(sprintf('%o', fileperms($webTSSRoot)), -4);
	$binsPermissions = substr(sprintf('%o', fileperms($webTSSRoot.'/bins')), -4);
	
	cronPrint("Script is running under \"".shell_exec("whoami")."\". Is this the web server user?");
	
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
	
	//Python check
	// (Can I check the version without creating a python script for it?)
	if(!file_exists($aGlobalConfig['cron']['python2.7Location']))
		cronPrint('Could not find python at '.$aGlobalConfig['cron']['python2.7Location']);
	
	cronPrint("The permissions of '".$webTSSRoot.'/tss'."' are ".$tssPermissions.". Is this enough to write and read?");
	cronPrint("The permissions of '".$webTSSRoot.'/bins'."' are ".$binsPermissions.". Is this enough to write and read?");
	cronPrint("The permissions of '".$webTSSRoot."' are ".$webtssPermissions.". Is this enough to write and read?");	
	
	cronPrint('verifyWorkingEnviroment.php exit.');
	