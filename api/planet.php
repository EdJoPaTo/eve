<?php
	require $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
	$title = "Planetary Infrastructure";

	$characterID = !empty($_SESSION['characterID']) ? (int) $_SESSION['characterID'] : 0;

	$host = 'localhost';
	$user = 'eto';
	$password = 'eto';
	$database = 'eve';

	if (!empty($characterID)) {
		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$result = mysql_query("SELECT keyID, vCode, accessMask, planetsCachedUntil FROM api WHERE characterID=".$characterID);
		$num = mysql_num_rows($result);

		if ($num == 1) {
			$keyID = (int) mysql_result($result, 0, 'keyID');
			$vCode = mysql_result($result, 0, 'vCode');
			$cachedUntil = (int) mysql_result($result, 0, 'planetsCachedUntil');
			$accessMask = (int) mysql_result($result, 0, 'accessMask');
		}
		mysql_close();
	}

	function mysqlselectquerycolumntoarray($result, $column)
	{
		$a = array();
		$num = mysql_numrows($result);
		for ($i = 0; $i < $num; $i++) {
			$a[] = mysql_result($result, $i, $column);
		}
		return $a;
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s e", $cachedUntil); ?>">
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection($title, "//image.eveonline.com/Type/2014_64.png"); ?>
		<div id="content">
<?php
			if (!empty($_SERVER['HTTP_EVE_CHARNAME']) && strpos($_SERVER['HTTP_EVE_SHIPNAME'], $_SERVER['HTTP_EVE_CHARNAME']) !== FALSE)
			{
				echo "\t\t\t".'<font style="color:red;">Your Shipname contains your InGame Name! You are obtainable with the Directional Scanner!</font><br>'."\n";
				echo "\t\t\t".'If you already changed your Shipname ignore this until you switched it.<br><br>'."\n\n";
			}

			if ($characterID == 0) {
				echo "Please log in";
			} elseif (empty($keyID) || empty($vCode)) {
				echo 'Please provide me your API Informations in the <a href="/eveauth.php">Settings</a>.';
			} else {
				$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
				mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

				$selectpinsquery = "SELECT * FROM planetindustrypins WHERE ownerID=$characterID";
	//			$selectpinsquery .= " AND planetID=40176640";
				$selectpinsquery .= " ORDER BY planetID";

				$pinswithoutroutequery = "SELECT planetName, planetroutesbypins.typeName FROM planetroutesbypins, planets WHERE routeID IS NULL AND planetroutesbypins.ownerID=planets.ownerID AND planetroutesbypins.planetID=planets.planetID AND EXISTS (SELECT * FROM planetindustrypins WHERE pinID=planetroutesbypins.pinID) AND planetroutesbypins.ownerID=$characterID";

				$productionwithstoragequery = "SELECT planetproductions.ownerID, planetproductions.planetID, planetproductions.typeID, planetproductions.typeName, planetproductions.productionPerHour, coalesce(planetstorage.quantity, planetstorage.quantity, 0) as inStorage
				FROM planetproductions LEFT JOIN planetstorage
					ON planetproductions.ownerID=planetstorage.ownerID AND planetproductions.planetID=planetstorage.planetID AND planetproductions.typeID=planetstorage.typeID
				WHERE planetproductions.ownerID=$characterID";

				$queries = array(
					"Planets" => "SELECT * FROM planets WHERE ownerID=$characterID",
					"Planetpins" => "SELECT * FROM planetpins WHERE ownerID=$characterID",
					"Planetlinks" => "SELECT * FROM planetlinks WHERE ownerID=$characterID",
					"Planetroutes" => "SELECT * FROM planetroutes WHERE ownerID=$characterID",
					"Industry Pins without Route" => $pinswithoutroutequery,
					"Pinroutes" => "SELECT * FROM planetroutesbypins WHERE ownerID=$characterID",
					"planetproductions" => "SELECT * FROM planetproductions WHERE ownerID=$characterID",
					"planetstorage" => "SELECT * FROM planetstorage WHERE ownerID=$characterID",
					"planetproductions with storages" => $productionwithstoragequery
				);

				$result = mysql_query("SELECT * FROM planetindustrypins WHERE ownerID=$characterID");
				$requiredAPIAccessMask = 3;

				if ($characterID != 0 && ($cachedUntil == 0 || $cachedUntil < time())) {
					echo "API Data Update pending... This may take up to a minute.<br><br>\n\n";
				}

				if ($characterID == 0 || $cachedUntil == 0 || ($accessMask & $requiredAPIAccessMask) != $requiredAPIAccessMask) {
					// Be silent
				} elseif (mysql_numrows($result) == 0) {
					echo "You really should get a planetary infrastructure!<br><br>\n\n";
				} else {
					$result = mysql_query($pinswithoutroutequery);
					$num = mysql_numrows($result);
					if ($num > 0) {
						echo '<span style="color:red; font-size:150%">Some Pins are not routed:</span>'."\n";
						printmysqlselectquerytable($result);
					}

					echo "<h2>Extractor Units</h2>";
					$result = mysql_query("SELECT planetName, typeName, expiryTime FROM planetpins, planets WHERE planetpins.planetID=planets.planetID AND typeName LIKE '%Extractor%' AND planetpins.ownerID=$characterID");
					$num = mysql_numrows($result);
		//			printmysqlselectquerytable($result);
					echo '<div class="table hoverrow bordered">'."\n";
					echo '<div class="headrow">'."\n";
					echo '<div class="cell">Planet</div>'."\n";
					echo '<div class="cell">Extractor</div>'."\n";
					echo '<div class="cell">Finish Time</div>'."\n";
					echo '</div>'."\n";
					for ($i = 0; $i < $num; $i++) {
						echo '<div class="row">'."\n";
						echo '<div class="cell">'.mysql_result($result, $i, 'planetName')."</div>";
						echo '<div class="cell">'.mysql_result($result, $i, 'typeName')."</div>";
						$expiryTime = mysql_result($result, $i, 'expiryTime');
						echo '<div class="cell"';
						if ($expiryTime < time()) { echo ' style="color:red;"'; }
						echo ">";
						echo gmdate('d.m.Y H:i:s', $expiryTime)."</div>";
						echo "</div>\n";
					}
					echo '</div>'."\n";

					echo "<h2>Planeten</h2>\n";
					$planetresult = mysql_query("SELECT * FROM planets WHERE ownerID=".$characterID);
		//			echo "<strong>Planets</strong>";
		//			printmysqlselectquerytable($planetresult);

					$planetnum=mysql_numrows($planetresult);
					for ($i = 0; $i < $planetnum; $i++) {
						$planetID = mysql_result($planetresult, $i, 'planetID');
						$planetName = mysql_result($planetresult, $i, 'planetName');
						$planetTypeID = mysql_result($planetresult, $i, 'planetTypeID');
						$planetTypeName = mysql_result($planetresult, $i, 'planetTypeName');
						$lastUpdate = mysql_result($planetresult, $i, 'lastUpdate');
						echo '<h3><img src="//image.eveonline.com/Type/'.$planetTypeID.'_32.png">'." $planetName - $planetTypeName</h3>\n";
						echo 'last update: '.gmdate('d.m.Y H:i:s e', $lastUpdate)."<br>\n";
						echo '<div style="display: table;">'."\n";
						$result = mysql_query("SELECT * FROM ($productionwithstoragequery) bla WHERE planetID=".$planetID);
						echo mysql_error();
						//printmysqlselectquerytable($result);
						if (mysql_numrows($result) > 0) {
							echo '<div style="display: table-cell; padding: 2px;">'."\n";

							$result = mysql_query("SELECT * FROM ($productionwithstoragequery) bla WHERE planetID=$planetID AND productionPerHour>0");
							$num = mysql_numrows($result);
							if ($num > 0) {
								echo '<strong style="color:green;">Produces</strong><br>'."\n";
	//							printmysqlselectquerytable($result);
								echo '<div class="table hoverrow bordered">'."\n";
								echo '<div class="headrow">'."\n";
								echo '<div class="cell">Item</div>'."\n";
								echo '<div class="cell">Produces per Hour</div>'."\n";
								echo '<div class="cell">in Storage</div>'."\n";
								echo "</div>\n";
								for ($j = 0; $j < $num; $j++) {
									$typeName = mysql_result($result, $j, 'typeName');
									$productionPerHour = mysql_result($result, $j, 'productionPerHour');
									$inStorage = mysql_result($result, $j, 'inStorage');

									echo '<div class="row">'."\n";
									echo '<div class="cell">';
									echo $typeName;
									echo "</div>\n";
									echo '<div class="cell">';
									echo $productionPerHour;
									echo "</div>\n";
									echo '<div class="cell">';
									echo $inStorage;
									echo "</div>\n";
									echo "</div>\n";
								}
								echo "</div><br>\n";
							}
							$result = mysql_query("SELECT * FROM ($productionwithstoragequery) bla WHERE planetID=$planetID AND productionPerHour<0");
							$num = mysql_numrows($result);
							if ($num > 0) {
								echo '<strong style="color:red;">Needs</strong><br>'."\n";
	//							printmysqlselectquerytable($result);
								echo '<div class="table hoverrow bordered">'."\n";
								echo '<div class="headrow">'."\n";
								echo '<div class="cell">Item</div>'."\n";
								echo '<div class="cell">Needs per Hour</div>'."\n";
								echo '<div class="cell">in Storage</div>'."\n";
								echo '<div class="cell">depletes</div>'."\n";
								echo "</div>\n";
								for ($j = 0; $j < $num; $j++) {
									$typeName = mysql_result($result, $j, 'typeName');
									$productionPerHour = 0 - mysql_result($result, $j, 'productionPerHour');
									$inStorage = mysql_result($result, $j, 'inStorage');

									echo '<div class="row">'."\n";
									echo '<div class="cell">';
									echo $typeName;
									echo "</div>\n";
									echo '<div class="cell">';
									echo $productionPerHour;
									echo "</div>\n";
									echo '<div class="cell">';
									echo $inStorage;
									echo "</div>\n";
									$depletes = $lastUpdate + round(60.0 * 60.0 * $inStorage / $productionPerHour);
									echo '<div class="cell ';
									if ($depletes < time())
										echo ' worstvalue';
									echo '">';
									echo gmdate('d.m.Y H:i e', $depletes);
									echo "</div>\n";
									echo "</div>\n";
								}
								echo "</div><br>\n";
							}
							
							echo "</div><br>\n";
						}

						$result = mysql_query("SELECT typeName as Item, quantity as Quantity FROM planetstorage WHERE planetID=$planetID");
						echo mysql_error();
						if (mysql_numrows($result) > 0) {
							echo '<div style="display: table-cell; padding: 2px;">'."\n";
							echo '<strong>Stuff in Storage</strong><br>'."\n";
							printmysqlselectquerytable($result);
							echo "</div>\n";
						}
						echo "</div>\n";
					}
				}

				if (false) {
					echo "<h2>DEBUG</h2>\n";

					foreach ($queries as $key => $value) {
						echo "<strong>$key</strong>";
						$display = $value;
						$display = str_replace("SELECT", "<br>\nSELECT", $display);
						$display = str_replace("FROM", "<br>\nFROM", $display);
						$display = str_replace("ON", "<br>\nON", $display);
						$display = str_replace("WHERE", "<br>\nWHERE", $display);
						$display = str_replace("GROUP BY", "<br>\nGROUP BY", $display);
						$display = str_replace("ORDER BY", "<br>\nORDER BY", $display);
						echo $display."<br>\n";
						printmysqlselectquerytable(mysql_query($value));
					}

					$result = mysql_query("SELECT * FROM api WHERE characterID=".$characterID);
					printmysqlselectquerytable($result);

					$result = mysql_query("SELECT * FROM planets WHERE ownerID=".$characterID);
					printmysqlselectquerytable($result);

					$num=mysql_numrows($result);
					for ($i = 0; $i < $num; $i++) {
						$planetID = mysql_result($result, $i, 'planetID');
						$planetName = mysql_result($result, $i, 'planetName');
						echo "<h3>$planetName</h3>\n";

						echo "<strong>Pins</strong>\n";
						printmysqlselectquerytable(mysql_query("SELECT * FROM planetpins WHERE ownerID=".$characterID." AND planetID=".$planetID));
						echo "<strong>Links</strong>\n";
						printmysqlselectquerytable(mysql_query("SELECT * FROM planetlinks WHERE ownerID=".$characterID." AND planetID=".$planetID));
						echo "<strong>Routes</strong>\n";
						printmysqlselectquerytable(mysql_query("SELECT * FROM planetroutes WHERE ownerID=".$characterID." AND planetID=".$planetID));
					}
				}

				mysql_close();
			}
?>
			<br><br>
<?php
			if (!empty($characterID)) {
				echo 'cached until: '.gmdate('d.m.Y H:i:s e', $cachedUntil)."<br>\n";
			}
?>
			Fly safe<?php if (!empty($_SERVER['HTTP_EVE_SHIPNAME'])) {echo " <b>".$_SERVER['HTTP_EVE_SHIPNAME']."</b>";} ?> o/
<?php echo getFooter(); ?>
		</div>
	</body>
</html>

