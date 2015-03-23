<?php
	require $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	$title = "About";
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
	</head>
	<body>
<?php echo getPageselection($title); ?>
		<div id="content">
			<h2>Contacts</h2>
				<ul>
					<li>Edgar Toll
						<ul>
							<li>Ingame
								<ul>
									<li>Rell Silfani</li>
									<li>Karnis Delvari</li>
								</ul>
							</li>
							<li>E-Mail: edjopato@gmail.com</li>
						</ul>
					</li>
				</ul>
			<h2>Thanks to</h2>
				<ul>
					<li><a class="external" target="_blank" href="http://ccpgames.com">CCP Games</a> for <a class="external" target="_blank" href="http://eveonline.com">EVE Online</a></li>
					<li><a class="external" target="_blank" href="https://skilled-minority.com/">Skilled-Minority</a> for hosting this website</li>
					<li><a class="external" target="_blank" href="https://zkillboard.com/">zKillboard</a> for some ideas in the design and the OAuth handling</li>
					<li><a class="external" target="_blank" href="https://www.fuzzwork.co.uk/">Fuzzwork</a> for the data dump convertion</li>
					<li><a class="external" target="_blank" href="http://evemaps.dotlan.net/">DOTLAN</a></li>
					<li><a class="external" target="_blank" href="https://tripwire.eve-apps.com/?system=">Tripwire</a></li>
					<li><a class="external" target="_blank" href="http://wh.pasta.gg/">Wormhol.es</a></li>
					<li>Tester, Bughunter and Sources of Ideas
						<ul>
							<li>Jatsu Enaka</li>
							<li>Eve Tobermory</li>
							<li>X'or'X</li>
						</ul>
					</li>
				</ul>
<?php echo getFooter(); ?>
		</div>
	</body>
</html>
