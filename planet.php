<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Prices.php';

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
		global $mysqli, $systemid, $pricetype;
		$typeName = $mysqli->query("SELECT typeName FROM evedump.invTypes WHERE typeID=$typeID")->fetch_object()->typeName;
		$sum = 0;
		$profit = 0;
		echo '<div class="table" style="border: 2px solid gray;">'."\n";

		$innerquery = "SELECT * FROM evedump.planetSchematicsTypeMap WHERE typeID=$typeID AND isInput=0";
		$result = $mysqli->query($innerquery);
		//printmysqlselectquerytable($result);
		if ($result->num_rows) {
			$row = $result->fetch_object();
			$schematicID = $row->schematicID;
			$factor = $quantity / $row->quantity;
			$cycleTime = $mysqli->query("SELECT cycleTime FROM evedump.planetSchematics WHERE schematicID=$schematicID")->fetch_object()->cycleTime;
			$result = $mysqli->query("SELECT planetSchematicsTypeMap.schematicID, planetSchematicsTypeMap.typeID, planetSchematicsTypeMap.quantity FROM evedump.planetSchematicsTypeMap, ($innerquery) innerquery WHERE planetSchematicsTypeMap.schematicID=innerquery.schematicID AND planetSchematicsTypeMap.isInput=1");
			//printmysqlselectquerytable($result);
			if ($result->num_rows > 0) {
				echo '<div class="cell">'."\n";
				echo '<div class="table">'."\n";
				while ($row = $result->fetch_object()) {
					echo '<div class="row">'."\n";
//					echo '<div class="cell">'."\n";
					$childtypeidresult = createtypeidtable($factor * $row->quantity, $row->typeID);
					$sum += $childtypeidresult['price'];
					$profit += $childtypeidresult['profit'];
//					echo "</div>\n";
					echo "</div>\n";
				}
				if ($result->num_rows > 1) {
					echo '<div class="row" style="text-align: right;">'."\n";
					echo "Sum:&nbsp;".formatprice($sum)."&nbsp;ISK";
					echo "</div>\n";
				}
				echo "</div>\n";
				echo "</div>\n";
			}
		}
		echo '<div class="cell" style="height: 100%">'."\n";
			echo '<div class="table" style="height: 100%">'."\n";
				if (!empty($cycleTime) && $cycleTime != 0) {
					echo '<div class="row">'."\n";
						echo "cycle time:&nbsp;".($factor != 1 ? $factor."x&nbsp;" : "").($cycleTime/ 60)."&nbsp;min";
					echo "</div>\n";
					echo '<div class="row" style="height: 100%"></div>'."\n";
				}
				echo '<div class="row">'."\n";
					echo '<div class="table">'."\n";
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
							$volume = $mysqli->query("SELECT volume FROM evedump.invTypes WHERE typeID=$typeID")->fetch_object()->volume;
							echo formatvolume($volume * $quantity)."&nbsp;m&sup3;";
							echo "<br>\n";
							$prices = Prices::getFromID($typeID, $systemid);
//							$updated = min($updated, $prices->updated);
							$singleprice = $prices->getPriceByType($pricetype)['price'];
							$stackprice = $quantity * $singleprice;
							if (isigb())
								echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$typeID.')">';
							echo formatprice($stackprice)."&nbsp;ISK";
							if (isigb())
								echo "</div>";
							echo $prices->getMouseoverField($quantity);
						echo "</div>\n";
					echo "</div>\n";
				echo "</div>\n";
				if (!empty($cycleTime) && $cycleTime != 0) {
					$profit += $stackprice - $sum;
					echo '<div class="row" style="text-align: right;';
					if ($profit < 0)
						echo ' color: red;';
					elseif ($profit > 0)
						echo ' color: green;';
					echo '">'."\n";
						echo "Profit:&nbsp;".formatprice($profit)."&nbsp;ISK";
					echo "</div>\n";
				}
				echo "</div>\n";
			echo "</div>\n";
		echo "</div>\n";
		$returnvalue = array(
				'price' => $stackprice,
				'profit' => $profit
			);
		return $returnvalue;
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

			$schematicsQuery = "SELECT planetSchematics.schematicID, schematicName, cycleTime, planetSchematicsTypeMap.quantity, planetSchematicsTypeMap.typeID, invGroups.groupID, invGroups.groupName,
				CASE
					WHEN invGroups.groupID=1042 THEN 1
					WHEN invGroups.groupID=1034 THEN 2
					WHEN invGroups.groupID=1040 THEN 3
					WHEN invGroups.groupID=1041 THEN 4
					ELSE -1
				END as tier
			FROM evedump.planetSchematics
			JOIN evedump.planetSchematicsTypeMap ON planetSchematics.schematicID=planetSchematicsTypeMap.schematicID AND planetSchematicsTypeMap.isInput=0
			JOIN evedump.invTypes ON planetSchematicsTypeMap.typeID=invTypes.typeID
			JOIN evedump.invGroups ON invTypes.groupID=invGroups.groupID
			ORDER BY tier, schematicName";

			$schematicsresult = $mysqli->query($schematicsQuery);
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
			$lastTier = 0;
			while ($row = $schematicsresult->fetch_object()) {
				$id = $row->schematicID;
				$name = $row->schematicName;
				$tier = $row->tier;
				if ($lastTier != $tier) {
					if ($lastTier != 0) {
						echo "\t\t\t\t</optgroup>\n";
					}
					echo "\t\t\t\t".'<optgroup label="Tier '.$tier.'">'."\n";
					echo "\t\t\t\t\t".'<option value="'.$tier.'"'.($tier == $schematicID ? " selected" : "").'>'."Overview Tier $tier</option>\n";
					$lastTier = $tier;
				}
				echo "\t\t\t\t\t".'<option value="'.$id.'"'.($id == $schematicID ? " selected" : "").'>'."$name (Tier $tier)</option>\n";
			}
			echo "\t\t\t\t</optgroup>\n";
			echo '			</select><br>'."\n";

			echo "</form><br>\n";


			if ($schematicID >= 65 && $schematicID <= 135) {
				$query = "SELECT planetSchematics.schematicID, schematicName, cycleTime, planetSchematicsTypeMap.quantity, planetSchematicsTypeMap.typeID
				FROM evedump.planetSchematics
				JOIN evedump.planetSchematicsTypeMap ON planetSchematics.schematicID=planetSchematicsTypeMap.schematicID AND planetSchematicsTypeMap.isInput=0
				WHERE planetSchematics.schematicID=$schematicID";
			} elseif ($schematicID >= 1 && $schematicID <= 4) {
				$query = "SELECT *
				FROM ($schematicsQuery) schem
				WHERE tier=$schematicID";
			} else {
				throw new Exception ( "Not a schematic", 0, NULL );
			}

			$schematicsresult = $mysqli->query($query);
//			printmysqlselectquerytable($schematicsresult);

			echo "\t\t\t".'<div style="font-size: 90%">'."\n";
			while ($row = $schematicsresult->fetch_object()) {
				$quantity = $row->quantity;
				$typeID = $row->typeID;
				createtypeidtable($quantity, $typeID);
				echo "\t\t\t\t<br>\n";
			}
			echo "\t\t\t</div>\n";


			$mysqli->close();
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
