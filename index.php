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
			.hoverimg {
				-webkit-filter: grayscale(1) blur(5px);
				filter: grayscale(1) blur(5px);
				background-repeat: no-repeat;
				position: absolute;
				top: 0px;
				width: 128px;
				height: 128px;
			}
			.hoverimg:hover {
				-webkit-filter: grayscale(0) blur(0px);
				filter: grayscale(0) blur(0px);
			}
			.hoverimg div {
				text-align: center;
				color: #FFB000;
				opacity: 0;
			}
			.hoverimg:hover div {
				opacity: 0.9;
				transform: translateY(110px);
			}
		</style>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')" style="text-align:center;">
<?php echo getPageselection(); ?>
		<div id="content">
			<h1>o7 <?php echo empty($_SERVER['HTTP_EVE_CHARNAME']) ? "Capsuleer" :$_SERVER['HTTP_EVE_CHARNAME']; ?>!</h1>

			<h2>Public Tools</h2>
			<div class="table" style="width:100%;">
				<div class="cell" style="width: 33%;">
					<a href="ore.php">
						<img src="//image.eveonline.com/Type/34_64.png" alt="Ore"><br>
						Ore Chart
					</a>
				</div>
				<div class="cell" style="width: 33%;">
					<a href="ice.php">
						<img src="//image.eveonline.com/Type/16265_64.png" alt="Ice"><br>
						Ice Chart
					</a>
				</div>
				<div class="cell" style="width: 33%;">
					<a href="planet.php">
						<img src="//image.eveonline.com/Type/2398_64.png" alt="Metal"><br>
						PI Commodity Prices
					</a>
				</div>
			</div>

			<h2>Login required</h2>
			<div class="table" style="width:100%;">
				<div class="cell" style="width: 100%;">
					<a href="api/planet.php">
						<img src="//image.eveonline.com/Type/2014_64.png" alt="Planet"><br>
						Planetary Infrastructure Overview
					</a>
				</div>
			</div>


			<br>
			<h2>Autor</h2>
			<div class="table" style="width: 100%; height: 128px;">
				<div class="cell" style="width: 50%; position: relative;">
					<div style="background-image: url(//image.eveonline.com/Character/90419497_128.jpg); right: 5px;" class="hoverimg" onclick="CCPEVE.showInfo(1377,90419497)">
						<div>Rell Silfani</div>
					</div>
				</div>
				<div class="cell" style="width: 50%; position: relative;">
					<div style="background-image: url(//image.eveonline.com/Character/91572014_128.jpg); left: 5px;" class="hoverimg" onclick="CCPEVE.showInfo(1377,91572014)">
						<div>Karnis Delvari</div>
					</div>
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
