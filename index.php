<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';

	$adminmode = !empty($_GET['id']) && $_GET['id'] == 1337;

	$title = "EVE";
	if ($adminmode)
		$title .= " Admin";
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<style type="text/css">
			.icon {
				background-repeat: no-repeat;
				background-position: center 5px;
				background-size: 64px 64px;
				padding-top: 75px;
			}
			.publictablecell {
				width: 33%;
			}
			.logintablecell {
				width: 100%;
			}
			.hoverimg {
				-webkit-transition: -webkit-filter 1s ease;
				transition: -webkit-filter 1s ease;
				-webkit-filter: grayscale(0.3) blur(5px);
				filter: grayscale(0.3) blur(5px);
				background-repeat: no-repeat;
				width: 128px;
				height: 128px;
				margin: 5px;
				background-size: 100%;
			}
			.hoverimg:hover {
				-webkit-filter: grayscale(0) blur(0px);
				filter: grayscale(0) blur(0px);
			}
			.hoverimg div {
				-webkit-transition: all 1s ease;
				transition: all 1s ease;
				text-align: center;
				color: #FFB000;
				opacity: 0;
			}
			.hoverimg:hover div {
				opacity: 0.9;
				transform: translateY(110px);
			}
			#rell { background-image: url(//image.eveonline.com/Character/90419497_128.jpg); }
			#karnis { background-image: url(//image.eveonline.com/Character/91572014_128.jpg); }
			@media (-webkit-min-device-pixel-ratio: 1.5) {
				#rell { background-image: url(//image.eveonline.com/Character/90419497_256.jpg); }
				#karnis { background-image: url(//image.eveonline.com/Character/91572014_256.jpg); }
			}
			@media (-webkit-min-device-pixel-ratio: 3) {
				#rell { background-image: url(//image.eveonline.com/Character/90419497_512.jpg); }
				#karnis { background-image: url(//image.eveonline.com/Character/91572014_512.jpg); }
			}
		</style>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')" style="text-align:center;">
<?php echo getPageselection(); ?>
		<div id="content">
			<h1>o7 <?php echo empty($_SERVER['HTTP_EVE_CHARNAME']) ? "Capsuleer" :$_SERVER['HTTP_EVE_CHARNAME']; ?>!</h1>

			<h2>Public Tools</h2>
			<div class="table" style="width:100%;">
				<a class="publictablecell cell icon" style="background-image: url(//image.eveonline.com/Type/34_64.png);" href="ore.php">
					Ore Chart
				</a>
				<a class="publictablecell cell icon" style="background-image: url(//image.eveonline.com/Type/16265_64.png);" href="ice.php">
					Ice Chart
				</a>
				<a class="publictablecell cell icon" style="background-image: url(//image.eveonline.com/Type/2398_64.png);" href="planet.php">
					PI Commodity Prices
				</a>
			</div>

			<h2>Login required</h2>
			<div class="table" style="width:100%;">
				<a class="logintablecell cell icon" style="background-image: url(//image.eveonline.com/Type/2014_64.png);" href="api/planet.php">
					Planetary Infrastructure Overview
				</a>
			</div>


			<br>
			<h2>Autor</h2>
			<div class="table" style="margin-left: auto; margin-right: auto;">
				<div class="cell hoverimg" id="rell" onclick="CCPEVE.showInfo(1377,90419497)">
					<div>Rell Silfani</div>
				</div>
				<div class="cell hoverimg" id="karnis" onclick="CCPEVE.showInfo(1377,91572014)">
					<div>Karnis Delvari</div>
				</div>
			</div>

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
