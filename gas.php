<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Prices.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Reprocess.php';

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

	$title = $system." Gas Chart";

	$pricetype = !empty($_GET['pricetype']) ? strtolower(htmlspecialchars($_GET['pricetype'])) : "bestcase";
	if ($pricetype != "buy" && $pricetype != "sell") { $pricetype = "bestcase"; }

	$m3percycle = !empty($_GET['cyclem3']) ? (float)htmlspecialchars($_GET['cyclem3']) : 0.0;
	if (!is_numeric($m3percycle) || $m3percycle == 0) {$m3percycle = 20;}


	$updated = time() + 60.0 * 60.0;


	$gasinfo = array(
			25268 => array('use' => 'Blue Pill (Shield boosting)', 'primary' => 'Caldari, The Forge (Mivora)', 'secondary' => 'Vale of the Silent (E-8CSQ)'),
			28694 => array('use' => 'Blue Pill (Shield boosting)', 'primary' => 'Caldari, The Forge (Mivora)', 'secondary' => 'Vale of the Silent (E-8CSQ)'),
			25273 => array('use' => 'Crash (Missile explosion radius)', 'primary' => 'Caldari, Lonetrek (Umamon)', 'secondary' => 'Tenal (09-4XW)'),
			28697 => array('use' => 'Crash (Missile explosion radius)', 'primary' => 'Caldari, Lonetrek (Umamon)', 'secondary' => 'Tenal (09-4XW)'),
			25274 => array('use' => 'Drop (Tracking speed)', 'primary' => 'Gallente, Placid (Amevync)', 'secondary' => 'Cloud Ring (Assilot)'),
			28701 => array('use' => 'Drop (Tracking speed)', 'primary' => 'Gallente, Placid (Amevync)', 'secondary' => 'Cloud Ring (Assilot)'),
			25275 => array('use' => 'Exile (Armor repair)', 'primary' => 'Gallente, Solitude (Elerelle)', 'secondary' => 'Fountain (Pegasus)'),
			28696 => array('use' => 'Exile (Armor repair)', 'primary' => 'Gallente, Solitude (Elerelle)', 'secondary' => 'Fountain (Pegasus)'),

			25277 => array('use' => 'Frentix (Optimal range)', 'primary' => 'Amarr, Derelik (Joas)', 'secondary' => 'Catch (9HXQ-G)'),
			28698 => array('use' => 'Frentix (Optimal range)', 'primary' => 'Amarr, Derelik (Joas)', 'secondary' => 'Catch (9HXQ-G)'),
			25276 => array('use' => 'Mindflood (Capacitor capacity)', 'primary' => 'Amarr, Aridia (Fabai)', 'secondary' => 'Delve (OK-FEM)'),
			28699 => array('use' => 'Mindflood (Capacitor capacity)', 'primary' => 'Amarr, Aridia (Fabai)', 'secondary' => 'Delve (OK-FEM)'),
			25279 => array('use' => 'Soothsayer (Falloff range)', 'primary' => 'Minmatar, Molden Heath (Tartatven)', 'secondary' => 'Wicked Creek (760-9C)'),
			28695 => array('use' => 'Soothsayer (Falloff range)', 'primary' => 'Minmatar, Molden Heath (Tartatven)', 'secondary' => 'Wicked Creek (760-9C)'),
			25278 => array('use' => 'X-Instinct (Signature radius)', 'primary' => 'Minmatar, Heimatar (Hed)', 'secondary' => 'Feythabolis (I-3ODK)'),
			28700 => array('use' => 'X-Instinct (Signature radius)', 'primary' => 'Minmatar, Heimatar (Hed)', 'secondary' => 'Feythabolis (I-3ODK)'),

			30370 => array('use' => 'T3 Production', 'primary' => 'WH, Barren Perimeter Reservoir (3000)', 'secondary' => 'WH, Sizable Perimeter Reservoir (1500)'),
			30371 => array('use' => 'T3 Production', 'primary' => 'WH, Token Perimeter Reservoir (3000)', 'secondary' => 'WH, Barren Perimeter Reservoir (1500)'),
			30372 => array('use' => 'T3 Production', 'primary' => 'WH, Minor Perimeter Reservoir (3000)', 'secondary' => 'WH, Token Perimeter Reservoir (1500)'),
			30373 => array('use' => 'T3 Production', 'primary' => 'WH, Ordinary Perimeter Reservoir (3000)', 'secondary' => 'WH, Minor Perimeter Reservoir (1500)'),
			30374 => array('use' => 'T3 Production', 'primary' => 'WH, Sizable Perimeter Reservoir (3000)', 'secondary' => 'WH, Ordinary Perimeter Reservoir (1500)'),
			30375 => array('use' => 'T3 Production', 'primary' => 'WH, Bountiful Frontier Reservoir (5000)', 'secondary' => 'WH, Vast Frontier Reservoir (1000)'),
			30376 => array('use' => 'T3 Production', 'primary' => 'WH, Vast Frontier Reservoir (5000)', 'secondary' => 'WH, Bountiful Frontier Reservoir (1000)'),
			30377 => array('use' => 'T3 Production', 'primary' => 'C5, C6, Instrumental Core Reservoir (6000)', 'secondary' => 'C5, C6, Vital Core Reservoir (500)'),
			30378 => array('use' => 'T3 Production', 'primary' => 'C5, C6, Vital Core Reservoir (6000)', 'secondary' => 'C5, C6, Instrumental Core Reservoir (500)')
		);

	//refinered: SELECT * FROM invTypeMaterials WHERE typeID=$id;
	//volume: SELECT volume FROM invTypes WHERE typeID=$id;
	//name: SELECT typeName FROM invTypes WHERE typeID=$id;

	$gasquery = "SELECT typeID, typeName, volume FROM evedump.invTypes
	WHERE groupID=711 AND typeName NOT LIKE 'compressed%' AND marketGroupID IS NOT NULL AND published=1
	ORDER BY typeName";
//			printmysqlselectquerytable(mysql_query($orequery));


	$gastable = array();
	$result = $mysqli->query($gasquery);
	while ($row = $result->fetch_object()) {
		$a = array();
		$id = $row->typeID;
		$name = $row->typeName;
		$volume = $row->volume;

		$a['id'] = $id;
		$a['name'] = $name;
		$a['volume'] = $volume;

		$prices = Prices::getFromID($id, $systemid);
		$price = $prices->getPriceByType($pricetype)['price'];

		$a['prices'] = $prices;

		$a['1price'] = $price;

		$minedpercycle = $m3percycle / $volume;
		$a['cycleamount'] = $minedpercycle;
		$a['cycleprice'] = $price * $minedpercycle;

		$oretable[] = $a;
	}
	$result->close();
	usort($oretable, build_sorter('cycleprice', true));

	function createlinktarget($system, $m3percycle)
	{
		$system = strtolower($system);

		$link = '?';
		if ($system != 'jita') {
			$link .= 'system='.$system.'&amp;';
		}
		if ($GLOBALS['pricetype'] != 'buy') {
			$link .= 'pricetype='.$GLOBALS['pricetype'].'&amp;';
		}
		$link .= 'cyclem3='.$m3percycle;
//		$link .= '&amp;';

		return $link;
	}
	function createlinktargetsystem($system)
	{
		return createlinktarget($system, $GLOBALS['m3percycle']);
	}
	function createlinktargetcycleamount($m3percycle)
	{
		return createlinktarget($GLOBALS['system'], round( $m3percycle, 0 ));
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s e", $updated + 60.0 * 30.0); ?>">
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection( $title, '//image.eveonline.com/Type/25273_64.png' ) . "\n"; ?>
		<div id="content">
<?php
			if (!empty($_SERVER['HTTP_EVE_CHARNAME']) && strpos($_SERVER['HTTP_EVE_SHIPNAME'], $_SERVER['HTTP_EVE_CHARNAME']) !== FALSE) {
				echo "\t\t\t".'<font style="color:red;">Your Shipname contains your InGame Name! You are obtainable with the Directional Scanner!</font><br>'."\n";
				echo "\t\t\t".'If you already changed your Shipname ignore this until you switched your ship.<br><br>'."\n";
			}

			if ($updated == 0) {
				echo "\t\t\tPrice data update pending... This may take up to a minute.<br><br>\n";
			}
?>
			<div class="table hoverrow border" style="font-size: 90%; text-align: right;">
				<div class="headrow">
					<div class="cell border">Name</div>
					<div class="cell border">Used in</div>
					<div class="cell borderleft">
						Found in<br>
						<div class="table">
							<div class="cell" style="width: 290px;"><br>Empire Region/ Primary Site</div>
							<div class="cell borderleft" style="width: 280px;"><br>Null Region/ Secondary Site</div>
						</div>
					</div>
					<div class="cell border" style="width: 80px;">1 item<br><br>ISK</div>
					<div class="cell borderleft">
						1 cycle | <?php echo formatvolume($m3percycle); ?>m&sup3;<br>
						<div class="table">
							<div class="cell" style="width: 100px; color: #BAA373;">quantity<br>pieces</div>
							<div class="cell borderleft" style="width: 100px;">normal<br>ISK</div>
						</div>
					</div>
				</div>
<?php
			foreach ($oretable as $row) {
				$id = $row['id'];
				echo "\t\t\t\t".'<div class="row border">'."\n";
				echo "\t\t\t\t\t".'<div class="cell border">';
				if (isigb())
					echo '<div class="igbinfo" onclick="CCPEVE.showInfo('.$id.')">';
				echo $row['name'];
				if (isigb())
					echo "</div>";
				echo "</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border" style="text-align: left;">';
				echo $gasinfo[$id]['use'];
				echo "</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t".'<div class="table">'."\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell" style="width: 290px; text-align: left;">';
				echo $gasinfo[$id]['primary'];
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				echo '" style="width: 280px;">';
				echo $gasinfo[$id]['secondary'];
				echo "\t\t\t\t\t\t\t"."</div>\n";
				echo "\t\t\t\t\t\t</div>\n";
				echo "\t\t\t\t\t</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t";
				if (isigb())
					echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$id.')">';
				echo formatprice($row['1price']);
				if (isigb())
					echo "</div>";
				echo "\n";
				echo $row[ 'prices' ]->getMouseoverField( 1, "\t\t\t\t\t\t" );
				echo "\t\t\t\t\t</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t".'<div class="table">'."\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell" style="width: 100px; text-align: right; color: #BAA373;">';
				$cycleamount = $row['cycleamount'];
				echo formatamount($cycleamount);
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				echo '" style="width: 100px; text-align: right;">';
				echo formatprice($row['cycleprice']);
				echo "\n";
				echo $row['prices']->getMouseoverField($cycleamount, "\t\t\t\t\t\t\t\t");
				echo "\t\t\t\t\t\t\t"."</div>\n";
				echo "\t\t\t\t\t\t</div>\n";
				echo "\t\t\t\t\t</div>\n";


				echo "\t\t\t\t</div>\n";
			}

			$mysqli->close();
?>
			</div><br>

<?php
			echo '			<form action="'.$_SERVER['PHP_SELF'].'" name="args" method="get">';
			echo "			<strong>Show</strong><br>\n";
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

			echo "			<br>\n";
			echo "			<strong>Cycleamount</strong><br>\n";
			echo 'Current: ';
			echo '<input name="cyclem3" type="number" value="'.$m3percycle.'" /> m&sup3; <input type="submit" value="Submit" /><br>'."\n";

			echo '			Venture, Prospect (+100% Gas Boni): ';
			echo '<a href="' . createlinktargetcycleamount( 20 ) . '">Gas Cloud Harvester I (20 m&sup3;)</a>, ';
			echo '<a href="' . createlinktargetcycleamount( 40 ) . '">Gas Cloud Harvester II (40 m&sup3;)</a>, ';
			echo "<br>\n";

			echo '			Normal Ship: ';
			echo '<a href="' . createlinktargetcycleamount( 10 ) . '">Gas Cloud Harvester I (10 m&sup3;)</a>, ';
			echo '<a href="' . createlinktargetcycleamount( 20 ) . '">Gas Cloud Harvester II (20 m&sup3;)</a>, ';
			echo "<br>\n";


			echo "</form>\n";
			echo "			<br>\n";
			echo '			price data provided by <a target="_blank" class="external" href="//eve-central.com">EVE Central</a>, ';

			echo 'updated: '.gmdate('d.m.Y H:i:s e', $updated)."<br>\n";
?>
			<a target="_blank" class="external" href="//eve-central.com/home/upload_suggest.html">Be a nice Miner and help EVE Central! :)</a>
			<br><br>
			Fly safe<?php if (!empty($_SERVER['HTTP_EVE_SHIPNAME'])) {echo " <b>".$_SERVER['HTTP_EVE_SHIPNAME']."</b>";} ?> o/
<?php echo getFooter(); ?>
		</div>
	</body>
</html>
