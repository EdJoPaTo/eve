<?php
	include './myfunctions.php';
	include './evefunctions.php';

	$title = "PI Commodity Prices";

	$systems = array(
		'Jita' => 30000142,
		'Hek' => 30002053,
		'Amarr' => 30002187,
		'Rens' => 30002510,
		'Dodixie' => 30002659
	);

	$system = !empty($_GET['system']) ? ucfirst(strtolower(htmlspecialchars($_GET['system']))) : "Jita";
	if (empty($systems[$system])) { $system = "Jita"; }
	$systemid = $systems[$system];

	$pricetype = !empty($_GET['pricetype']) ? strtolower(htmlspecialchars($_GET['pricetype'])) : "bestcase";
	if ($pricetype != "buy" && $pricetype != "sell") { $pricetype = "bestcase"; }

	$schematicID = !empty($_GET['schematic']) ? (int)htmlspecialchars($_GET['schematic']) : 126;
	if (!is_numeric($schematicID)) { $schematicID = 126; }

	$updated = time() + 60.0 * 60.0;

	function createtypeidtable($quantity, $typeID) {
		$typeName = mysql_result(mysql_query("SELECT typeName FROM invTypes WHERE typeID=$typeID"), 0, 'typeName');
		$sum = 0;
		echo '<div class="table" style="border: 2px solid gray;">'."\n";

		$innerquery = "SELECT * FROM planetSchematicsTypeMap WHERE typeID=$typeID AND isInput=0";
		$result = mysql_query($innerquery);
		if (mysql_num_rows($result)) {
			$schematicID = mysql_result($result, 0, 'schematicID');
			$factor = $quantity / mysql_result($result, 0, 'quantity');
			$cycleTime = mysql_result(mysql_query("SELECT cycleTime FROM planetSchematics WHERE schematicID=$schematicID"), 0, 'cycleTime');
			$result = mysql_query("SELECT * FROM planetSchematicsTypeMap, ($innerquery) innerquery WHERE planetSchematicsTypeMap.schematicID=innerquery.schematicID AND planetSchematicsTypeMap.isInput=1");
			$num = mysql_numrows($result);
			if ($num > 0) {
				echo '<div class="cell">'."\n";
				echo '<div class="table">'."\n";
				for ($i = 0; $i < $num; $i++) {
					echo '<div class="row">'."\n";
//					echo '<div class="cell">'."\n";
					$sum += createtypeidtable($factor * mysql_result($result, $i, 'quantity'), mysql_result($result, $i, 'typeID'));
//					echo "</div>\n";
					echo "</div>\n";
				}
				if ($num > 1) {
					echo '<div class="row" style="text-align: right;">'."\n";
					echo "Sum: ".formatprice($sum)." ISK";
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "</div>\n";
			}
		}
		echo '<div class="cell" style="position: relative; padding: 1em 0px;">'."\n";
			echo '<div class="cell">'."\n";
				echo '<img src="//image.eveonline.com/Type/'.$typeID.'_64.png">';
			echo "</div>\n";
			echo '<div class="cell" style="width: 250px;">'."\n";
				echo $quantity."x ";
				if (isigb())
					echo '<div class="igbinfo" onclick="CCPEVE.showInfo('.$typeID.')">';
				echo $typeName;
				if (isigb())
					echo "</div>";
				echo "<br>\n";
				$singleprice = getprice($typeID, $GLOBALS['systemid'], $GLOBALS['pricetype']);
				$stackprice = $quantity * $singleprice;
				if (isigb())
					echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$typeID.')">';
				echo formatprice($stackprice)." ISK";
				if (isigb())
					echo "</div>";
			echo "</div>\n";
			$profit = $stackprice - $sum;
			if (!empty($cycleTime) && $cycleTime != 0) {
				echo '<div style="position: absolute; top: 0px; left: 0; width: 100%">'."\n";
					echo "cycle time: ".($factor != 1 ? $factor."x " : "").($cycleTime/ 60)." min";
				echo "</div>\n";
			}
			echo '<div style="position: absolute; bottom: 0px; right: 2px;">'."\n";
				echo "Profit: ".formatprice($profit)." ISK";
			echo "</div>\n";
			echo "</div>\n";
		echo "</div>\n";
		return $stackprice;
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s e", $updated + 60.0 * 30.0); ?>">
		<style type="text/css">
			div.table {
				display: table;
				text-align: center;
			}
			div.row {
				display: table-row;
				vertical-align: middle;
			}
			div.cell {
				display: table-cell;
				vertical-align: middle;
			}
		</style>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection($title, '//image.eveonline.com/Type/2398_64.png'); ?>
		<div id="content">
<?php
			if (!empty($_SERVER['HTTP_EVE_CHARNAME']) && strpos($_SERVER['HTTP_EVE_SHIPNAME'], $_SERVER['HTTP_EVE_CHARNAME']) !== FALSE)
			{
				echo '			<font style="color:red;">Your Shipname contains your InGame Name! You are obtainable with the Directional Scanner!</font><br>
			If you already changed your Shipname ignore this until you switched it.\n';
			}

			$host = 'localhost';
			$user = 'eto';
			$password = 'eto';
			$database = 'evedump';

			$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
			mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

			$query = "SELECT planetSchematics.schematicID, schematicName, cycleTime, planetSchematicsTypeMap.quantity, planetSchematicsTypeMap.typeID, invGroups.groupID, invGroups.groupName,
				CASE
					WHEN invGroups.groupID=1042 THEN 1
					WHEN invGroups.groupID=1034 THEN 2
					WHEN invGroups.groupID=1040 THEN 3
					WHEN invGroups.groupID=1041 THEN 4
					ELSE -1
				END as tier
			FROM planetSchematics
			JOIN planetSchematicsTypeMap ON planetSchematics.schematicID=planetSchematicsTypeMap.schematicID AND planetSchematicsTypeMap.isInput=0
			JOIN invTypes ON planetSchematicsTypeMap.typeID=invTypes.typeID
			JOIN invGroups ON invTypes.groupID=invGroups.groupID
			ORDER BY tier, schematicName";

			$schematicsresult = mysql_query($query);
			$schematicsnum = mysql_numrows($schematicsresult);
//			printmysqlselectquerytable($schematicsresult);


			echo '			<form action="'.$_SERVER['PHP_SELF'].'" name="args" method="get">';
			echo "			Market Hub:\n";
			echo '			<select name="system" onchange="document.args.submit();">'."\n";
			foreach($systems as $key => $val) {
				echo '				<option value="'.strtolower($key).'"'.($val == $systemid ? " selected" : "").'>'.$key."</option>\n";
			}
			echo '			</select>'."\n";
			echo '			<select name="pricetype" onchange="document.args.submit();">'."\n";
			echo '				<option value="buy"'.("buy" == $pricetype ? " selected" : "").'>Accept Buy Order</option>'."\n";
			echo '				<option value="sell"'.("sell" == $pricetype ? " selected" : "").'>Place Sell Order</option>'."\n";
			echo '				<option value="bestcase"'.("bestcase" == $pricetype ? " selected" : "").'>Best Case</option>'."\n";
			echo '			</select><br>'."\n";
			echo "			Schematic:\n";
			echo '			<select name="schematic" onchange="document.args.submit();">'."\n";
			for ($i = 0; $i < $schematicsnum; $i++) {
				$id = mysql_result($schematicsresult, $i, 'schematicID');
				$name = mysql_result($schematicsresult, $i, 'schematicName');
				$tier = mysql_result($schematicsresult, $i, 'tier');
				echo '				<option value="'.$id.'"'.($id == $schematicID ? " selected" : "").'>'."$name (Tier $tier)</option>\n";
			}
			echo '			</select><br>'."\n";

			echo "</form><br>\n";


			$query = "SELECT planetSchematics.schematicID, schematicName, cycleTime, planetSchematicsTypeMap.quantity, planetSchematicsTypeMap.typeID
			FROM planetSchematics
			JOIN planetSchematicsTypeMap ON planetSchematics.schematicID=planetSchematicsTypeMap.schematicID AND planetSchematicsTypeMap.isInput=0
			WHERE planetSchematics.schematicID=$schematicID";

			$schematicsresult = mysql_query($query);

			$schematicsnum = mysql_numrows($schematicsresult);
//			printmysqlselectquerytable($schematicsresult);

			if ($schematicsnum > 0) {
				createtypeidtable(mysql_result($schematicsresult, 0, 'quantity'), mysql_result($schematicsresult, 0, 'typeID'))."<br><br>\n";
			}


			mysql_close();
?>

<?php
			echo "			<br>\n";
			echo '			price data provided by <a target="_blank" class="external" href="//eve-central.com">EVE Central</a>, ';

			echo 'updated: '.gmdate('d.m.Y H:i:s e', $updated)."<br><br>\n";
?>
			<strong>See also other pi tools</strong><br>
			<a target="_blank" class="external" href="http://picommodityrelations.comyr.com/pi.html">picommodityrelations.comyr.com</a><br>
			<a target="_blank" class="external" href="http://eveplanets.com/">eveplanets.com</a><br>
			<br><br>
			Fly safe<?php if (!empty($_SERVER['HTTP_EVE_SHIPNAME'])) {echo " <b>".$_SERVER['HTTP_EVE_SHIPNAME']."</b>";} ?> o/
<?php echo getFooter(); ?>
		</div>
	</body>
</html>

