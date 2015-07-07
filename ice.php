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

	$title = $system." Ice Chart";

	$pricetype = !empty($_GET['pricetype']) ? strtolower(htmlspecialchars($_GET['pricetype'])) : "bestcase";
	if ($pricetype != "buy" && $pricetype != "sell") { $pricetype = "bestcase"; }

	$compress = !empty($_GET['compress']) ? toBool(htmlspecialchars($_GET['compress'])) : false;

	$refine = !empty($_GET['refine']) ? (float)htmlspecialchars($_GET['refine']) : 0.0;
	if (!is_float($refine) || $refine == 0) {$refine = 69.575;}
	$refinepercent = $refine * 0.01;

	$updated = time() + 60.0 * 60.0;

	$iceinfo = array(
			16262 => array('faction' => 'Amarr', 'type' => 'Faction', 'found' => 'HS'),
			17978 => array('faction' => 'Amarr', 'type' => 'Enriched', 'found' => 'NS'),
			16265 => array('faction' => 'Caldari', 'type' => 'Faction', 'found' => 'HS'),
			17976 => array('faction' => 'Caldari', 'type' => 'Enriched', 'found' => 'NS'),
			16264 => array('faction' => 'Gallente', 'type' => 'Faction', 'found' => 'HS'),
			17975 => array('faction' => 'Gallente', 'type' => 'Enriched', 'found' => 'NS'),
			16263 => array('faction' => 'Minmatar', 'type' => 'Faction', 'found' => 'HS'),
			17977 => array('faction' => 'Minmatar', 'type' => 'Enriched', 'found' => 'NS'),
			16266 => array('type' => 'Standard', 'found' => 'LS'),
			16267 => array('type' => 'Standard', 'found' => 'LS'),
			16268 => array('type' => 'Standard', 'found' => 'LS'),
			16269 => array('type' => 'Standard', 'found' => 'LS')
		);

	$orequery = "SELECT typeID, typeName, volume, portionSize FROM evedump.invGroups
	LEFT JOIN evedump.invTypes ON invGroups.groupID=invTypes.groupID
	WHERE categoryID=25 AND typeName NOT LIKE 'compressed%' AND invGroups.groupID!=465 AND marketGroupID IS NOT NULL AND invTypes.published=1
	ORDER BY volume, basePrice";

	$mineralquery = "SELECT typeID, typeName, volume
	FROM evedump.invTypes
	WHERE published=1 AND groupID=18";

	$icequery = "SELECT typeID, typeName, volume, portionSize FROM evedump.invTypes
	WHERE groupID=465 AND typeName NOT LIKE 'compressed%' AND marketGroupID IS NOT NULL AND published=1
	ORDER BY typeName";
//			printmysqlselectquerytable(mysql_query($icequery));

	$icereprocessed = "SELECT typeID, typeName, volume
	FROM evedump.invTypes
	WHERE published=1 AND groupID=423
	ORDER BY volume";

	$icetable = array();
	$result = $mysqli->query($icequery);
	while ($row = $result->fetch_object()) {
		$a = array();
		$id = $row->typeID;
		$a['id'] = $id;
		$a['name'] = $row->typeName;
		$a['volume'] = $row->volume;

		$prices = Prices::getFromID($id, $systemid);
		$price = $prices->getPriceByType($pricetype)['price'];
		$compressedprices = Prices::getFromID(getcompressedid($id), $systemid);
		$compressedprice = $compressedprices->getPriceByType($pricetype)['price'];
		$reprocess = new Reprocess($id, $refinepercent, 1);
		$reprocessedprice = $reprocess->mineralStack->getPrice($systemid, $pricetype);

		$a['prices'] = $prices;
		$a['compressedprices'] = $compressedprices;
		$a['reprocessed'] = $reprocess;

		$a['1price'] = $price;
		$a['1compressedprice'] = $compressedprice;
		$a['1reprocessedprice'] = $reprocessedprice;
		$a['1bestprice'] = max($a['1price'], $a['1reprocessedprice']);
		$a['1worstprice'] = min($a['1price'], $a['1reprocessedprice']);
		if ($compress) {
			$a['1bestprice'] = max($a['1bestprice'], $a['1compressedprice']);
			$a['1worstprice'] = min($a['1worstprice'], $a['1compressedprice']);
		}

		$icetable[] = $a;
	}
	$result->close();
	usort($icetable, build_sorter('1bestprice', true));

	function createlinktarget($system, $refine)
	{
		$system = strtolower($system);

		$link = '?';
		if ($system != 'jita') {
			$link .= 'system='.$system.'&amp;';
		}
		if ($GLOBALS['pricetype'] != 'buy') {
			$link .= 'pricetype='.$GLOBALS['pricetype'].'&amp;';
		}
		if ($GLOBALS['compress']) {
			$link .= 'compress=true';
			$link .= '&amp;';
		}
		$link .= 'refine='.$refine;

		return $link;
	}
	function createlinktargetsystem($system)
	{
		return createlinktarget($system, $GLOBALS['refine']);
	}
	function createlinktargetrefine($refine)
	{
		return createlinktarget($GLOBALS['system'], $refine);
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s e", $updated + 60.0 * 30.0); ?>">
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection( $title, '//image.eveonline.com/Type/16265_64.png' ) . "\n"; ?>
		<div id="content">
<?php
			if (!empty($_SERVER['HTTP_EVE_CHARNAME']) && strpos($_SERVER['HTTP_EVE_SHIPNAME'], $_SERVER['HTTP_EVE_CHARNAME']) !== FALSE)
			{
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
					<div class="cell border">Type</div>
					<div class="cell border">Faction</div>
					<div class="cell border">Found&nbsp;in<br><br>Security</div>
					<div class="cell borderleft">
						1 item<br>
						<div class="table">
							<div class="cell" style="width: 100px;">normal<br>ISK</div>
							<div class="cell borderleft" style="width: 100px;">reprocessed<br>ISK</div>
<?php if ($compress) echo "\t\t\t\t\t\t\t" . '<div class="cell borderleft" style="width: 100px;">compressed<br>ISK</div>' . "\n"; ?>
						</div>
					</div>
				</div>
<?php
			foreach ($icetable as $row) {
				$id = $row['id'];
				echo "\t\t\t\t".'<div class="row border">'."\n";
				echo "\t\t\t\t\t".'<div class="cell border">';
				if (isigb())
					echo '<div class="igbinfo" onclick="CCPEVE.showInfo('.$id.')">';
				echo $row['name'];
				if (isigb())
					echo "</div>";
				echo "</div>\n";
				echo "\t\t\t\t\t".'<div class="cell border">';
				echo $iceinfo[$id]['type'];
				echo "</div>\n";
				echo "\t\t\t\t\t".'<div class="cell border">';
				if (!empty($iceinfo[$id]['faction']))
					echo $iceinfo[$id]['faction'];
				echo "</div>\n";
				echo "\t\t\t\t\t".'<div class="cell border" style="';
				switch ($iceinfo[$id]['found']) {
					case 'HS':
						echo 'color: cyan;';
						echo '">';
						echo 'High&nbsp;Sec';
						break;
					case 'LS':
						echo 'color: orange;';
						echo '">';
						echo 'Low&nbsp;Sec';
						break;
					default:
						echo 'color: red;';
						echo '">';
						echo 'Null&nbsp;Sec';
						break;
				}
				echo "</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t".'<div class="table">'."\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['1price'] == $row['1bestprice'])
					echo " bestvalue";
				if ($row['1price'] == $row['1worstprice'])
					echo " worstvalue";
				echo '" style="width: 100px; text-align: right;">';
				if (isigb())
					echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$id.')">';
				echo formatprice( $row[ '1price' ] ) . "\n";
				echo $row['prices']->getMouseoverField(1, "\t\t\t\t\t\t\t\t");
				if (isigb())
					echo "</div>";
				echo "\t\t\t\t\t\t\t"."</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['1reprocessedprice'] == $row['1bestprice'])
					echo " bestvalue";
				if ($row['1reprocessedprice'] == $row['1worstprice'])
					echo " worstvalue";
				echo '" style="width: 100px; text-align: right;">';
				echo formatprice( $row[ '1reprocessedprice' ] ) . "\n";
				echo $row['reprocessed']->getMouseoverField($systemid, "\t\t\t\t\t\t\t\t", $pricetype);
				echo "</div>\n";
				if ($compress) {
					echo "\t\t\t\t\t\t\t".'<div class="cell';
					if ($row['1compressedprice'] == $row['1bestprice'])
						echo " bestvalue";
					if ($row['1compressedprice'] == $row['1worstprice'])
						echo " worstvalue";
					echo '" style="width: 100px; text-align: right;">';
					if (isigb())
						echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.getcompressedid($id).')">';
					echo formatprice( $row[ '1compressedprice' ] ) . "\n";
					echo $row['compressedprices']->getMouseoverField(1, "\t\t\t\t\t\t\t\t");
					if (isigb())
						echo "</div>";
					echo "\t\t\t\t\t\t\t"."</div>\n";
				}
				echo "\t\t\t\t\t\t</div>\n";
				echo "\t\t\t\t\t</div>\n";


				echo "\t\t\t\t</div>\n";
			}
?>
			</div><br>

			<div class="table bordered hoverrow" style="font-size: 90%; text-align: right;">
				<div class="headrow">
					<div class="cell">System</div>
<?php
					$result = $mysqli->query($icereprocessed);
					while ($row = $result->fetch_object()) {
						$id = $row->typeID;
						$name = $row->typeName;
						echo "\t\t\t\t\t".'<div class="cell">';
						if (isigb())
							echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$id.')">';
						echo $name;
						if (isigb())
							echo "</div>";
						echo "</div>\n";
					}
					$result->close();
?>
				</div>
<?php
				foreach ($systems as $cursystemname => $cursystemid) {
					echo '					<div class="row';
					if ($cursystemid == $systemid) {
						echo ' highlight';
					}
					echo '">'."\n";
					echo '						<div class="cell"><a href="'.createlinktargetsystem($cursystemname).'">'.$cursystemname.'</a></div>'."\n";

					$result = $mysqli->query($icereprocessed);
					while ($row = $result->fetch_object()) {
						$id = $row->typeID;
						$prices = Prices::getFromID($id, $cursystemid);
						$curprice = $prices->getPriceByType($pricetype)['price'];
						echo "\t\t\t\t\t\t".'<div class="cell';
						if ($curprice == getbestknowprice($id, $pricetype)) {
							echo ' bestvalue';
						}
						echo '">';
						echo formatprice($curprice)."\n";
						echo $prices->getMouseoverField(1, "\t\t\t\t\t\t\t");
						echo "\t\t\t\t\t\t</div>\n";
					}
					$result->close();
					echo '					</div>'."\n";
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

			echo '			<input type="checkbox" name="compress" value="true" onclick="document.args.submit();"'.($compress ? " checked" : "").'> Compressed<br>'."\n";
			echo "			<br>\n";
			echo "			<strong>Refining</strong><br>\n";
			echo '			<input name="refine" type="number" min="30" max="100" step="0.0001" value="'.$refine.'" /> % <input type="submit" value="Submit" /><br>'."\n";
			echo "			All with Max Skills and Standing<br>\n";
			echo '			Station: ';
			echo '<a href="'.createlinktargetrefine(50 * 1.15 * 1.1 * 1.1 * 1.00).'">50%</a>, ';
			echo '<a href="'.createlinktargetrefine(50 * 1.15 * 1.1 * 1.1 * 1.04).'">50% + 4% Impl</a>';
			echo "<br>\n";
			echo '			POS: ';
			echo '<a href="'.createlinktargetrefine(52 * 1.15 * 1.1 * 1.1 * 1.00).'">52%</a>, ';
			echo '<a href="'.createlinktargetrefine(52 * 1.15 * 1.1 * 1.1 * 1.04).'">52% + 4% Impl</a>';
			echo "<br>\n";
			echo '			Low Sec POS: ';
			echo '<a href="'.createlinktargetrefine(54 * 1.15 * 1.1 * 1.1 * 1.00).'">54%</a>, ';
			echo '<a href="'.createlinktargetrefine(54 * 1.15 * 1.1 * 1.1 * 1.04).'">54% + 4% Impl</a>';
			echo "<br>\n";
			echo '			Max Minmatar outpost: ';
			echo '<a href="'.createlinktargetrefine(60 * 1.15 * 1.1 * 1.1 * 1.00).'">60%</a>, ';
			echo '<a href="'.createlinktargetrefine(60 * 1.15 * 1.1 * 1.1 * 1.04).'">60% + 4% Impl</a>';
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
