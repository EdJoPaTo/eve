<?php
	require 'classes/OAuth.php';
	require 'classes/Util.php';
	require 'myfunctions.php';
	require 'ssoDetails.php';

	if (isset($_GET['logout'])) {
		session_destroy();
		session_start();
	}

	$title = "Accountsettings";

	$message = "";
	if (isset($_SESSION["characterName"])) {
		$message = "Already logged in";
	} elseif (empty($_GET['code']) || empty($_GET['state'])) {
		$message = "Please log in via EVE SSO\n";
	} else {
		OAuth::eveSSOLoginToken($_GET['code'], $_GET['state']);
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')" style="text-align:center;">
<?php echo getPageselection($title); ?>
		<div id="content">
<?php
			echo $message;

			if (!empty($_SESSION['characterID']))
			{
				echo "\t\t\t".'<button type="button" onclick="parent.location=\'?logout=true\'">Logout</button>'."\n";
			}
?>
			<br><br>
			If you have an idea or a bug: <button type="button" onclick="CCPEVE.sendMail(90419497)">Send me an ingame message</button><br>
			Or: <button type="button" onclick="CCPEVE.startConversation(90419497)">start a conversation</button><br>
			Thanks :)
			<br><br>
			Fly safe<?php if (!empty($_SERVER['HTTP_EVE_SHIPNAME'])) {echo " <b>".$_SERVER['HTTP_EVE_SHIPNAME']."</b>";} ?> o/
<?php echo getFooter(); ?>
		</div>
	</body>
</html>
