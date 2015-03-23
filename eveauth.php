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

				$host = 'localhost';
				$user = 'eto';
				$password = 'eto';
				$database = 'eve';

				$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
				mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

				echo "\t\t\t<h1>API Details</h1>\n";
				$requiredAPIAccessMask = 33554435;
				if (!empty($_GET['keyID']) && !empty($_GET['vCode'])) {
					$keyID = !empty($_GET['keyID']) ? (int)htmlspecialchars($_GET['keyID']) : (int)$keyID;
					$vCode = !empty($_GET['vCode']) ? htmlspecialchars($_GET['vCode']) : $vCode;

					$query = "SELECT * FROM api WHERE characterID=$characterID";
					$result= mysql_query($query);
					echo mysql_error();
					$num = mysql_numrows($result);
					if ($num > 0) {
						if ($keyID != mysql_result($result, 0, 'keyID') || $vCode != mysql_result($result, 0, 'vCode')) {
							$query = "DELETE FROM api WHERE characterID=$characterID";
							mysql_query($query);
							$num = 0;
						}
					}
					if ($num == 0) {
						$query = "INSERT INTO api (characterID, keyID, vCode) VALUES (".$characterID.",".$keyID.",'".mysql_real_escape_string($vCode)."')";
						mysql_query($query);
					}
				}

				$result = mysql_query("SELECT keyID, vCode, accessMask FROM api WHERE characterID=".$characterID);
				$num = mysql_num_rows($result);

				if ($num == 1) {
					$keyID = (int) mysql_result($result, 0, 'keyID');
					$vCode = mysql_result($result, 0, 'vCode');
					$accessMask = (int) mysql_result($result, 0, 'accessMask');
				}


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
