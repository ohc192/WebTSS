<?php
require_once('config.php');
require_once('functions.php');

if(!$aGlobalConfig['cron']['enableWebAccess'])
		die("The website administrator has disabled cron triggers through the URL.");
	
cronPrint('WebTSS cron running..');

        $webTSSRoot = realpath(dirname(__FILE__));
        $mysqli = new mysqli($aGlobalConfig['database']['host'], $aGlobalConfig['database']['username'], $aGlobalConfig['database']['password'], $aGlobalConfig['database']['database'],$aGlobalConfig['database']['port']);
        $tssPermissions = substr(sprintf('%o', fileperms($webTSSRoot.'/tss')), -4);
        $binary = $aGlobalConfig["tssbinary"];
	
	if(!is_dir($webTSSRoot.'/tss'))
		die('Please create the directory "'.$webTSSRoot.'/tss'.'"..');

	if(!is_writable($webTSSRoot.'/tss')) 
		die('Can\'t write to "'.$webTSSRoot.'/tss"..');
	
	if(!is_readable($webTSSRoot.'/tss'))
		die('Can\'t read "'.$webTSSRoot.'/tss"..');
		
	if(!is_dir($webTSSRoot.'/bins'))
		die('Please create the directory "'.$webTSSRoot.'/bins"..');
		
	if(!is_readable($webTSSRoot.'/bins'))
		die('Can\'t read "'.$webTSSRoot.'/bins"..');
	
	if(!is_executable($webTSSRoot."/bins/$binary"))
		die('TSSChecker is not executable. Please fix this with chmod +x '.$webTSSRoot."/bins/$binary");
	
	if (mysqli_connect_errno())
		die('Can\'t connect to the database..');

	if ($stmt = $mysqli->prepare("SELECT ecid, platform FROM devices")) {

		/* execute statement */
		$stmt->execute();

		/* bind result variables */
		$stmt->bind_result($ecid, $platform);

		/* fetch values */
		while ($stmt->fetch()) {
			cronPrint("Working on $ecid ($platform)..");

			$command = $webTSSRoot.'/bins/tsschecker -e '.hexdec($ecid).' -d '.basename($platform).' -s -l --save-path '.$webTSSRoot.'/tss/'.hexdec($ecid);
		
			if(!is_dir($webTSSRoot.'/tss/'.hexdec($ecid))) {
				cronPrint("Creating \"".$webTSSRoot.'/tss/'.hexdec($ecid)."\"..");
				mkdir($webTSSRoot.'/tss/'.hexdec($ecid));
			}
		
			if(!is_readable($webTSSRoot.'/tss/'.hexdec($ecid)) || !is_writable($webTSSRoot.'/tss/'.hexdec($ecid))) {
				cronPrint('Can\'t read and/or write to "'.$webTSSRoot.'/tss/'.hexdec($ecid).'"..');
			} else {
				// Big thanks to 1Conan!
				$signedVersionsURL = "https://api.ipsw.me/v2.1/firmwares.json/condensed"; 
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL,$signedVersionsURL);
				$result = curl_exec($ch);
				curl_close($ch);
				$data = json_decode($result, true);

				$firmwares = $data['devices'][$platform]['firmwares'];
				$countFirmwares = count($firmwares);
				for($i = 0; $i < $countFirmwares; $i++) {
					$current = $firmwares[$i];
					if($current['signed'] == true) {
						$command = $webTSSRoot."/bins/$binary -e ".hexdec($ecid).' -d '.basename($platform).' -s --buildid '.$current['buildid'].' --save-path '.$webTSSRoot.'/tss/'.hexdec($ecid);
						cronPrint("Running \"".$command."\"..");
						@shell_exec($command);
					}	
				}
			}
		}

		/* close statement */
		$stmt->close();
	}

	/* close connection */
	$mysqli->close();
	cronPrint('Cron finished.'); 
?>
