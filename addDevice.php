<?php
	try {
        require_once('config.php');

		if(empty($_POST['ecid']))
			throw new Exception('Please provide an ECID.');
		if(empty($_POST['platform']))
			throw new Exception('Please provide a platform.');
		if(!ctype_xdigit($_POST['ecid']))
			throw new Exception('Invalid ECID.');

		if($aGlobalConfig['recaptcha']['enabled']) {
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query(array('secret' => $aGlobalConfig['recaptcha']['secretKey'], 'response' => $_POST['g-recaptcha-response'], 'remoteip' => $_SERVER['REMOTE_ADDR']))
				)
			);

			$result = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create($options));
			if ($result === FALSE) {
				throw new Exception('Server was unable to communicate with recaptcha.');
			} else {
				$correctCaptcha = json_decode($result, True)['success'];
				//$captchaErrors = json_decode($result, True)['error-codes']; // Works, but not really used right now.
				if(!$correctCaptcha) {
					throw new Exception('The captcha was incorrect.');
				}
			}
		}

		if(ctype_digit($_POST['ecid'])) { // Client provided a non-hex ECID. Convert it for them.
			$ecid = strtoupper(base_convert($_POST['ecid'], 10, 16));
		} elseif(ctype_xdigit($_POST['ecid'])) { // Client provided a hex ECID, just assign the variable.
			$ecid = strtoupper($_POST['ecid']);
		} else {
			throw new Exception('Invalid ECID.');
		}

		$conn = new mysqli($aGlobalConfig['database']['host'], $aGlobalConfig['database']['username'], $aGlobalConfig['database']['password'], $aGlobalConfig['database']['database'],$aGlobalConfig['database']['port']);


		if (mysqli_connect_errno())
			throw new Exception('Could not connect to database.');

		try {
			if(!$stmt = $conn->prepare("SELECT ecid FROM devices WHERE ecid = ?")) // Pretty sure PDO is secure enough for this. If not, would you mind contributing a fix? :)
				throw new Exception('First repared statement failed.');
			$stmt->bind_param("s", $ecid);

			if(!$stmt->execute())
				throw new Exception('First execution failed.');
			$stmt->bind_result($ecidFromQuery);

			while ($stmt->fetch()) {
				throw new Exception('This ECID is already in our database!');
			}

			if($ecid == $ecidFromQuery)
				throw new Exception('This ECID is already in our database!');

			$stmt->close();

		} catch (PDOException $e) {
			throw new Exception('SQL Error');
		}

		try {
			if(!$stmt2 = $conn->prepare("INSERT INTO devices (ecid, platform) VALUES (?, ?)")) // Pretty sure PDO is secure enough for this. If not, would you mind contributing a fix? :)
				throw new Exception('Second prepared statement failed.');

			$stmt2->bind_param("ss", $ecid, $_POST['platform']);
			$stmt2->execute();
			$stmt2->close();
			$conn->close();
		} catch (PDOException $e) {
			throw new Exception('SQL Error');
		}

		$webTSSRoot = realpath(dirname(__FILE__));

		if(!mkdir($webTSSRoot.'/tss/'.hexdec($ecid)))
			throw new Exception('Couldn\'t make user directory.');

		// Do initial scan for blobs.
		// TODO: Make this async

		// Big thanks to 1Conan!
		$signedVersionsURL = "https://api.ipsw.me/v2.1/firmwares.json/condensed";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$signedVersionsURL);
		curl_setopt($ch, CURLOPT_USERAGENT, "WebTSS - More info at https://github.com/gotkrypto76/WebTSS");
		$result = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($result, true);

		$firmwares = $data['devices'][$_POST['platform']]['firmwares'];
		$countFirmwares = count($firmwares);
        for($i = 0; $i < $countFirmwares; $i++) {
            $current = $firmwares[$i];
            if($current['signed'] == true) {
                $binary = $aGlobalConfig["tssbinary"];
                $command = $webTSSRoot."/bins/$binary -e ".hexdec($ecid).' -d '.basename($_POST['platform']).' -s --buildid '.$current['buildid'].' --save-path '.$webTSSRoot.'/tss/'.hexdec($ecid);
                @shell_exec($command);
            }
        }

		header('Location: '.str_replace("/".basename(__FILE__), "", $_SERVER['PHP_SELF']).'/tss/'.hexdec($ecid));
	} catch (Exception $e) {
		header("Location: index.php?e=".urlencode($e->getMessage()));
	}
?>
