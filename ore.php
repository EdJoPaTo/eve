<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';

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

	$title = $system." Ore Chart";

	$security = !empty($_GET['security']) ? strtolower(htmlspecialchars($_GET['security'])) : "high";
	if ($security != 'null' && $security != 'low') { $security = "high"; }

	$bonus = !empty($_GET['bonus']) ? toBool(htmlspecialchars($_GET['bonus'])) : false;

	$pricetype = !empty($_GET['pricetype']) ? strtolower(htmlspecialchars($_GET['pricetype'])) : "bestcase";
	if ($pricetype != "buy" && $pricetype != "sell") { $pricetype = "bestcase"; }

	$m3percycle = !empty($_GET['cyclem3']) ? (float)htmlspecialchars($_GET['cyclem3']) : 0.0;
	if (!is_numeric($m3percycle) || $m3percycle == 0) {$m3percycle = 1000;}

	$refine = !empty($_GET['refine']) ? (float)htmlspecialchars($_GET['refine']) : 0.0;
	if (!is_float($refine) || $refine == 0) {$refine = 69.6;}
	$refinepercent = $refine * 0.01;



	$updated = time() + 60.0 * 60.0;

	$mineral = array(
		34 => 'Tritanium',
		35 => 'Pyerite',
		36 => 'Mexallon',
		37 => 'Isogen',
		38 => 'Nocxium',
		39 => 'Zydrine',
		40 => 'Megacyte'
	);

	$ore = array(
		1230	=> array('id' => 1230, 'name' => 'Veldspar', 'bonus' => 0, 'found' => 'HS', 'volume' => 0.1, 'refined' => array(34 => 415)),
		17470	=> array('id' => 17470, 'name' => 'Concentrated Veldspar', 'bonus' => 5, 'found' => 'HS', 'volume' => 0.1, 'refined' => array(34 => 436)),
		17471	=> array('id' => 17471, 'name' => 'Dense Veldspar', 'bonus' => 10, 'found' => 'HS', 'volume' => 0.1, 'refined' => array(34 => 457)),

		1228	=> array('id' => 1228, 'name' => 'Scordite', 'bonus' => 0, 'found' => 'HS', 'volume' => 0.15, 'refined' => array(34 => 346, 35 => 173)),
		17463	=> array('id' => 17463, 'name' => 'Condensated Scordite', 'bonus' => 5, 'found' => 'HS', 'volume' => 0.15, 'refined' => array(34 => 363, 35 => 183)),
		17464	=> array('id' => 17464, 'name' => 'Massive Scordite', 'bonus' => 10, 'found' => 'HS', 'volume' => 0.15, 'refined' => array(34 => 380, 35 => 190)),

		1224	=> array('id' => 1224, 'name' => 'Pyroxeres', 'bonus' => 0, 'found' => 'HS', 'volume' => 0.3, 'refined' => array(34 => 351, 35 => 25, 36 => 50, 38 => 5)),
		17459	=> array('id' => 17459, 'name' => 'Solid Pyroxeres', 'bonus' => 5, 'found' => 'HS', 'volume' => 0.3, 'refined' => array(34 => 368, 35 => 26, 36 => 53, 38 => 5)),
		17460	=> array('id' => 17460, 'name' => 'Viscous Pyroxeres', 'bonus' => 10, 'found' => 'HS', 'volume' => 0.3, 'refined' => array(34 => 385, 35 => 27, 36 => 55, 38 => 5)),

		18		=> array('id' => 18, 'name' => 'Plagioclase', 'bonus' => 0, 'found' => 'HS', 'volume' => 0.35, 'refined' => array(34 => 107, 35 => 213, 36 => 107)),
		17455	=> array('id' => 17455, 'name' => 'Azure Plagioclase', 'bonus' => 5, 'found' => 'HS', 'volume' => 0.35, 'refined' => array(34 => 112, 35 => 224, 36 => 112)),
		17456	=> array('id' => 17456, 'name' => 'Rich Plagioclase', 'bonus' => 10, 'found' => 'HS', 'volume' => 0.35, 'refined' => array(34 => 117, 35 => 234, 36 => 117)),

		1227	=> array('id' => 1227, 'name' => 'Omber', 'bonus' => 0, 'found' => 'HS', 'volume' => 0.6, 'refined' => array(34 => 85, 35 => 34, 37 => 85)),
		17867	=> array('id' => 17867, 'name' => 'Silvery Omber', 'bonus' => 5, 'found' => 'HS', 'volume' => 0.6, 'refined' => array(34 => 89, 35 => 36, 37 => 89)),
		17868	=> array('id' => 17868, 'name' => 'Golden Omber', 'bonus' => 10, 'found' => 'HS', 'volume' => 0.6, 'refined' => array(34 => 94, 35 => 38, 37 => 94)),

		20		=> array('id' => 20, 'name' => 'Kernite', 'bonus' => 0, 'found' => 'HS', 'volume' => 1.2, 'refined' => array(34 => 134, 36 => 267, 37 => 134)),
		17452	=> array('id' => 17452, 'name' => 'Luminous Kernite', 'bonus' => 5, 'found' => 'HS', 'volume' => 1.2, 'refined' => array(34 => 140, 36 => 281, 37 => 140)),
		17453	=> array('id' => 17453, 'name' => 'Fiery Kernite', 'bonus' => 10, 'found' => 'HS', 'volume' => 1.2, 'refined' => array(34 => 147, 36 => 294, 37 => 147)),

		1226	=> array('id' => 1226, 'name' => 'Jaspet', 'bonus' => 0, 'found' => 'LS', 'volume' => 2, 'refined' => array(34 => 72, 35 => 121, 36 => 144, 38 => 72, 39 => 3)),
		17448	=> array('id' => 17448, 'name' => 'Pure Jaspet', 'bonus' => 5, 'found' => 'LS', 'volume' => 2, 'refined' => array(34 => 76, 35 => 127, 36 => 151, 38 => 76, 39 => 3)),
		17449	=> array('id' => 17449, 'name' => 'Pristine Jaspet', 'bonus' => 10, 'found' => 'LS', 'volume' => 2, 'refined' => array(34 => 79, 35 => 133, 36 => 158, 38 => 79, 39 => 3)),

		1231	=> array('id' => 1231, 'name' => 'Hemorphite', 'bonus' => 0, 'found' => 'LS', 'volume' => 3, 'refined' => array(34 => 180, 35 => 72, 36 => 17, 37 => 59, 38 => 118, 39 => 8)),
		17444	=> array('id' => 17444, 'name' => 'Vivid Hemorphite', 'bonus' => 5, 'found' => 'LS', 'volume' => 3, 'refined' => array(34 => 189, 35 => 76, 36 => 18, 37 => 62, 38 => 123, 39 => 9)),
		17445	=> array('id' => 17445, 'name' => 'Radiant Hemorphite', 'bonus' => 10, 'found' => 'LS', 'volume' => 3, 'refined' => array(34 => 198, 35 => 79, 36 => 19, 37 => 65, 38 => 129, 39 => 9)),

		21		=> array('id' => 21, 'name' => 'Hedbergite', 'bonus' => 0, 'found' => 'LS', 'volume' => 3, 'refined' => array(35 => 81, 37 => 196, 38 => 98, 39 => 9)),
		17440	=> array('id' => 17440, 'name' => 'Vitric Hedbergite', 'bonus' => 5, 'found' => 'LS', 'volume' => 3, 'refined' => array(35 => 85, 37 => 206, 38 => 103, 39 => 10)),
		17441	=> array('id' => 17441, 'name' => 'Glazed Hedbergite', 'bonus' => 10, 'found' => 'LS', 'volume' => 3, 'refined' => array(35 => 89, 37 => 216, 38 => 108, 39 => 10)),

		1229	=> array('found' => 'NS', 'bonus' => 0),
		17865	=> array('found' => 'NS', 'bonus' => 5),
		17866	=> array('found' => 'NS', 'bonus' => 10),

		1232	=> array('found' => 'NS', 'bonus' => 0),
		17436	=> array('found' => 'NS', 'bonus' => 5),
		17437	=> array('found' => 'NS', 'bonus' => 10),

		19		=> array('found' => 'NS', 'bonus' => 0),
		17466	=> array('found' => 'NS', 'bonus' => 5),
		17467	=> array('found' => 'NS', 'bonus' => 10),

		1225	=> array('found' => 'NS', 'bonus' => 0),
		17432	=> array('found' => 'NS', 'bonus' => 5),
		17433	=> array('found' => 'NS', 'bonus' => 10),

		1223	=> array('found' => 'NS', 'bonus' => 0),
		17428	=> array('found' => 'NS', 'bonus' => 5),
		17429	=> array('found' => 'NS', 'bonus' => 10),

		22		=> array('found' => 'NS', 'bonus' => 0),
		17425	=> array('found' => 'NS', 'bonus' => 5),
		17426	=> array('found' => 'NS', 'bonus' => 10),

		11396	=> array('found' => 'NS', 'bonus' => 0),
		17869	=> array('found' => 'NS', 'bonus' => 5),
		17870	=> array('found' => 'NS', 'bonus' => 10)
	);

	$host = 'localhost';
	$user = 'eto';
	$password = 'eto';
	$database = 'eve';

	$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
	mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());


	//refinered: SELECT * FROM invTypeMaterials WHERE typeID=$id;
	//volume: SELECT volume FROM invTypes WHERE typeID=$id;
	//name: SELECT typeName FROM invTypes WHERE typeID=$id;

	$orequery = "SELECT typeID, typeName, volume, portionSize FROM evedump.invGroups
	LEFT JOIN evedump.invTypes ON invGroups.groupID=invTypes.groupID
	WHERE categoryID=25 AND typeName NOT LIKE 'compressed%' AND invGroups.groupID!=465 AND marketGroupID IS NOT NULL AND invTypes.published=1
	ORDER BY volume, basePrice";
//			printmysqlselectquerytable(mysql_query($orequery));

	$mineralquery = "SELECT typeID, typeName, volume FROM evedump.invTypes WHERE published=1 AND groupID=18";

	$oretable = array();
	$result = mysql_query($orequery);
	$num = mysql_num_rows($result);
	for ($i=0; $i < $num; $i++) {
		$a = array();
		$id = mysql_result($result, $i, 'typeID');
		$name = mysql_result($result, $i, 'typeName');
		$volume = mysql_result($result, $i, 'volume');

		if  ($security == 'high'                        && $ore[$id]['found'] == 'LS') { continue; }
		if (($security == 'high' || $security == 'low') && $ore[$id]['found'] == 'NS') { continue; }

		if (!$bonus && $ore[$id]['bonus'] > 0) { continue; }

		$a['id'] = $id;
		$a['name'] = $name;
		$a['volume'] = $volume;

		$price = getprice($id, $systemid, $pricetype);
		$compressedprice = getcompressedprice($id, $systemid, $pricetype);
		$refinedprice = getrefinedprice($id, $systemid, $pricetype) * $refinepercent;

		$a['1price'] = $price;
		$a['1compressedprice'] = $compressedprice;
		$a['1refinedprice'] = $refinedprice;
		$a['1bestprice'] = max($a['1price'], max($a['1compressedprice'], $a['1refinedprice']));
		$a['1worstprice'] = min($a['1price'], min($a['1compressedprice'], $a['1refinedprice']));

		$minedpercycle = $m3percycle / $volume;
		$a['cycleamount'] = $minedpercycle;
		$a['cycleprice'] = $price * $minedpercycle;
		$a['cyclecompressedprice'] = getcompressedprice($id, $systemid, $pricetype) * $minedpercycle;
		$a['cyclerefinedprice'] = getrefinedprice($id, $systemid, $pricetype) * $refinepercent * $minedpercycle;
		$a['cyclebestprice'] = max($a['cycleprice'], max($a['cyclecompressedprice'], $a['cyclerefinedprice']));
		$a['cycleworstprice'] = min($a['cycleprice'], min($a['cyclecompressedprice'], $a['cyclerefinedprice']));

		$oretable[] = $a;
	}
	usort($oretable, build_sorter('cyclebestprice', true));

	function createlinktarget($system, $m3percycle, $refine)
	{
		$system = strtolower($system);

		$link = '?';
		if ($system != 'jita') {
			$link .= 'system='.$system.'&amp;';
		}
		if ($GLOBALS['pricetype'] != 'buy') {
			$link .= 'pricetype='.$GLOBALS['pricetype'].'&amp;';
		}
		if ($GLOBALS['security'] != 'high') {
			$link .= 'security='.$GLOBALS['security'];
			$link .= '&amp;';
		}
		if ($GLOBALS['bonus']) {
			$link .= 'bonus='.$GLOBALS['bonus'];
			$link .= '&amp;';
		}
		$link .= 'cyclem3='.$m3percycle;
		$link .= '&amp;';
		$link .= 'refine='.$refine;

		return $link;
	}
	function createlinktargetsystem($system)
	{
		return createlinktarget($system, $GLOBALS['m3percycle'], $GLOBALS['refine']);
	}
	function createlinktargetcycleamount($m3percycle)
	{
		return createlinktarget($GLOBALS['system'], $m3percycle, $GLOBALS['refine']);
	}
	function createlinktargetrefine($refine)
	{
		return createlinktarget($GLOBALS['system'], $GLOBALS['m3percycle'], $refine);
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s e", $updated + 60.0 * 30.0); ?>">
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection($title, '//image.eveonline.com/Type/34_64.png'); ?>
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
					<div class="cell border">Found&nbsp;in<br><br>Security</div>
					<div class="cell border">Volume<br><br>m&sup3;</div>
					<div class="cell borderleft">
						1 item<br>
						<div class="table" style="width: 300px;">
							<div class="cell" style="width: 33%;">normal<br>ISK</div>
							<div class="cell borderleft" style="width: 33%;">compressed<br>ISK</div>
							<div class="cell borderleft" style="width: 33%;">reprocessed<br>ISK</div>
						</div>
					</div>
					<div class="cell borderleft">
						1 cycle | <?php echo formatvolume($m3percycle); ?>m&sup3;<br>
						<div class="table" style="width: 400px;">
							<div class="cell" style="width: 25%; color: #BAA373;">amount<br>pieces</div>
							<div class="cell borderleft" style="width: 25%;">normal<br>ISK</div>
							<div class="cell borderleft" style="width: 25%;">compressed<br>ISK</div>
							<div class="cell borderleft" style="width: 25%;">reprocessed<br>ISK</div>
						</div>
					</div>
				</div>
<?php
			foreach ($oretable as $row) {
				$id = $row['id'];
				echo "\t\t\t\t".'<div class="row border">'."\n";
				echo "\t\t\t\t\t".'<div class="cell border">';
				if ($bonus) {
					echo "\t\t\t\t\t\t".'<div class="table" style="width: 100%;">'."\n";
					echo "\t\t\t\t\t\t\t".'<div class="cell" style="width: 100%;">';
				}
				if (isigb())
					echo '<div class="igbinfo" onclick="CCPEVE.showInfo('.$id.')">';
				echo $row['name'];
				if (isigb())
					echo "</div>";
				if ($bonus)
				{
					echo "</div>\n";
					echo "\t\t\t\t\t\t\t".'<div class="cell" style="min-width:50px">';
					if ($ore[$id]['bonus'] > 0)
						echo "+" . $ore[$id]['bonus'] . "%";
					echo "</div>\n";
					echo "\t\t\t\t\t\t</div>\n";
				}
				echo "</div>\n";
				echo "\t\t\t\t\t".'<div class="cell border" style="';
				switch ($ore[$id]['found']) {
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
				echo "\t\t\t\t\t".'<div class="cell border">';
				echo formatvolume($row['volume']);
				echo "</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t".'<div class="table" style="width: 100%;">'."\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['1price'] == $row['1bestprice'])
					echo " bestvalue";
				if ($row['1price'] == $row['1worstprice'])
					echo " worstvalue";
				echo '" style="width: 33%; text-align: right;">';
				if (isigb())
					echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$id.')">';
				echo formatprice($row['1price']);
				if (isigb())
					echo "</div>";
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['1compressedprice'] == $row['1bestprice'])
					echo " bestvalue";
				if ($row['1compressedprice'] == $row['1worstprice'])
					echo " worstvalue";
				echo '" style="width: 33%; text-align: right;">';
				if (isigb())
					echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.getcompressedid($id).')">';
				echo formatprice($row['1compressedprice']);
				if (isigb())
					echo "</div>";
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['1refinedprice'] == $row['1bestprice'])
					echo " bestvalue";
				if ($row['1refinedprice'] == $row['1worstprice'])
					echo " worstvalue";
				echo '" style="width: 33%; text-align: right;">';
				echo formatprice($row['1refinedprice']);
				echo "</div>\n";
				echo "\t\t\t\t\t\t</div>\n";
				echo "\t\t\t\t\t</div>\n";

				echo "\t\t\t\t\t".'<div class="cell border">'."\n";
				echo "\t\t\t\t\t\t".'<div class="table" style="width: 100%;">'."\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell" style="width: 25%; text-align: right; color: #BAA373;">';
				echo formatamount($row['cycleamount']);
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['cycleprice'] == $row['cyclebestprice'])
					echo " bestvalue";
				if ($row['cycleprice'] == $row['cycleworstprice'])
					echo " worstvalue";
				echo '" style="width: 25%; text-align: right;">';
				echo formatprice($row['cycleprice']);
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['cyclecompressedprice'] == $row['cyclebestprice'])
					echo " bestvalue";
				if ($row['cyclecompressedprice'] == $row['cycleworstprice'])
					echo " worstvalue";
				echo '" style="width: 25%; text-align: right;">';
				echo formatprice($row['cyclecompressedprice']);
				echo "</div>\n";
				echo "\t\t\t\t\t\t\t".'<div class="cell';
				if ($row['cyclerefinedprice'] == $row['cyclebestprice'])
					echo " bestvalue";
				if ($row['cyclerefinedprice'] == $row['cycleworstprice'])
					echo " worstvalue";
				echo '" style="width: 25%; text-align: right;">';
				echo formatprice($row['cyclerefinedprice']);
				echo "</div>\n";
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
					$result = mysql_query($mineralquery);
					$num = mysql_num_rows($result);
					for ($i=0; $i < $num; $i++) {
						$id = mysql_result($result, $i, 'typeID');
						$name = mysql_result($result, $i, 'typeName');
						echo "\t\t\t\t\t".'<div class="cell">';
						if (isigb())
							echo '<div class="igbmore" onclick="CCPEVE.showMarketDetails('.$id.')">';
						echo $name;
						if (isigb())
							echo "</div>";
						echo "</div>\n";
					}
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

					for ($i = 0; $i < $num; $i++) {
						$id = mysql_result($result, $i, 'typeID');
						$curprice = getprice($id, $cursystemid, $pricetype);
						echo '						<div class="cell';
						if ($curprice == getbestknowprice($id, $pricetype)) {
							echo ' bestvalue';
						}
						echo '">';
						echo $curprice;
						echo "</div>\n";
					}
					echo '					</div>'."\n";
				}


				mysql_close();
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

			echo '			Security: <select name="security" onchange="document.args.submit();">'."\n";
			echo '				<option value="high"'.("high" == $security ? " selected" : "").'>High Sec</option>'."\n";
			echo '				<option value="low"'.("low" == $security ? " selected" : "").'>Low Sec</option>'."\n";
			echo '				<option value="null"'.("null" == $security ? " selected" : "").'>Null Sec</option>'."\n";
			echo '			</select><br>'."\n";

			echo '			<input type="checkbox" name="bonus" value="true" onclick="document.args.submit();"'.($bonus ? " checked" : "").'> +5% / +10% Ore<br>'."\n";
			echo "			<br>\n";
			echo "			<strong>Cycleamount</strong><br>\n";
			echo 'Current: ';
			echo '<input name="cyclem3" type="number" value="'.$m3percycle.'" /> m&sup3; <input type="submit" value="Update!" /><br>'."\n";

			echo '			Venture: ';
			echo '<a href="'.createlinktargetcycleamount(300).'">T1 (~300 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(500).'">T2 (~500 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(860).'">T2+Orca (~860 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(700).'">Prospect (~700 m&sup3;)</a>';
			echo "<br>\n";
			echo '			Retriever: ';
			echo '<a href="'.createlinktargetcycleamount(900).'">T1 (~900 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(1180).'">T2 (~1180 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(2000).'">T2+Orca (~2000 m&sup3;)</a>';
			echo "<br>\n";
			echo '			Hulk: ';
			echo '<a href="'.createlinktargetcycleamount(1700).'">T2 (~1700 m&sup3;)</a>, ';
			echo '<a href="'.createlinktargetcycleamount(2900).'">T2+Orca (~2900 m&sup3;)</a>';
			echo "<br>\n";
			echo "			<br>\n";
			echo "			<strong>Refining</strong><br>\n";
			echo '			<input name="refine" type="number" min="0.1" max="100" step="0.1" value="'.$refine.'" /> % <input type="submit" value="Update!" /><br>'."\n";
			echo "			All with Max Skills and Standing<br>\n";
			echo '			Station: ';
			echo '<a href="'.createlinktargetrefine(69.6).'">50%</a>, ';
			echo '<a href="'.createlinktargetrefine(72.4).'">50% + 4% Impl</a>';
			echo "<br>\n";
			echo '			POS: ';
			echo '<a href="'.createlinktargetrefine(72.4).'">52%</a>, ';
			echo '<a href="'.createlinktargetrefine(75.3).'">52% + 4% Impl</a>';
			echo "<br>\n";
			echo '			Low Sec POS: ';
			echo '<a href="'.createlinktargetrefine(75.1).'">54%</a>, ';
			echo '<a href="'.createlinktargetrefine(78.1).'">54% + 4% Impl</a>';
			echo "<br>\n";
			echo '			Max Minmatar outpost: ';
			echo '<a href="'.createlinktargetrefine(83.5).'">60%</a>, ';
			echo '<a href="'.createlinktargetrefine(86.8).'">60% + 4% Impl</a>';
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

