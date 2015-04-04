<?php
	require $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';

	if (isset($_GET['logout'])) {
		session_destroy();
		session_start();
	}

	$title = "Accountsettings";

	$message = "";
	if (isset($_SESSION["characterName"])) {
		// Already logged in
	} elseif (empty($_GET['code']) || empty($_GET['state'])) {
		$message = "Please log in\n";
	} else {
		OAuth::eveSSOLoginToken($_GET['code'], $_GET['state']);
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection($title); ?>
		<div id="content">
<?php
			echo $message;

			if (!empty($_SESSION['characterID'])) {
				$characterID = $_SESSION['characterID'];

				echo "\t\t\t<h1>API Details</h1>\n";
				$requiredAPIAccessMask = 33554435;
				if (!empty($_GET['keyID']) && !empty($_GET['vCode'])) {
					$keyID = !empty($_GET['keyID']) ? (int)htmlspecialchars($_GET['keyID']) : (int)$keyID;
					$vCode = !empty($_GET['vCode']) ? htmlspecialchars($_GET['vCode']) : $vCode;

					$apiquery = "SELECT * FROM eve.api WHERE characterID=$characterID";
					$result= $mysqli->query($apiquery);
					if ($row = $result->fetch_object()) {
						if ($keyID != $row->keyID || $vCode != $row->vCode) {
							$query = "DELETE FROM eve.api WHERE characterID=$characterID";
							$mysqli->query($query);
						}
					}
					$result->close();
					$result = $mysqli->query($apiquery);
					if ($result->num_rows == 0) {
						$query = "INSERT INTO eve.api (characterID, keyID, vCode) VALUES (".$characterID.",".$keyID.",'".mysql_real_escape_string($vCode)."')";
						$mysqli->query($query);
					}
					$result->close();
				}

				$result = $mysqli->query("SELECT keyID, vCode, accessMask FROM eve.api WHERE characterID=".$characterID);

				if ($row = $result->fetch_object()) {
					$keyID = (int) $row->keyID;
					$vCode = $row->vCode;
					$accessMask = (int) $row->accessMask;
				}
				$result->close();


				echo "\t\t\t".'<form action="'.$_SERVER['PHP_SELF'].'" name="args" method="get">';
				echo "\t\t\t".'<table class="invis">'."\n";
				echo "\t\t\t".'<tr><td>Key ID:</td><td><input name="keyID" type="textbox" value="'.$keyID.'" size="7" /> from <a href="https://api.eveonline.com/" class="external" target="_blank">api.eveonline.com</a></td></tr>'."\n";
				echo "\t\t\t".'<tr><td>Verification Code:</td><td><input name="vCode" type="textbox" value="'.$vCode.'" size="80" /></td></tr>'."\n";
				echo "\t\t\t".'<tr><td></td><td><input type="submit" value="Save"/></td></tr>'."\n";
				echo "\t\t\t"."</table>\n";
				echo "\t\t\t".'</form><br>'."\n";
				echo "\t\t\t".'Create an API: ';
				echo '<a target="_blank" class="external" href="https://support.eveonline.com/api/Key/CreatePredefined/'.$requiredAPIAccessMask.'">Basic API</a> ';
				echo '<a target="_blank" class="external" href="https://support.eveonline.com/api/Key/CreatePredefined/268435455">Everything API</a>&nbsp;<br>';


				echo "\t\t\t<br>\n";
				echo "\t\t\t<strong>Already want to leave?</strong><br>\n";
				echo "\t\t\t".'<button type="button" onclick="parent.location=\'?logout=true\'">Logout</button>'."\n";
			}
?>
			<br><br>
			Fly safe<?php if (!empty($_SERVER['HTTP_EVE_SHIPNAME'])) {echo " <b>".$_SERVER['HTTP_EVE_SHIPNAME']."</b>";} ?> o/
<?php echo getFooter(); ?>
		</div>
	</body>
</html>
