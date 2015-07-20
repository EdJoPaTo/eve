<?php
	require 'evefunctions.php';

	function updateall()
	{
		date_default_timezone_set("UTC");
		generatemissingtable();
		updateprices();
		updateapikeyinfo();
		updateaccountstatus();
		updateallbalance();
		updateallplanets();
		updateallplanetinfos();
		echo mysql_error();
	}
	function generatemissingtable()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$query = 'CREATE TABLE IF NOT EXISTS prices (
		id bigint(15) NOT NULL,
		systemID bigint(15) NOT NULL,
		buy numeric(15, 2) NOT NULL,
		sell numeric(15, 2) NOT NULL,
		buyunits bigint(15) NOT NULL,
		sellunits bigint(15) NOT NULL,
		stamp bigint(20) NOT NULL,
		PRIMARY KEY (id, systemid),
		UNIQUE (id, systemid)
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS characters (
		characterID bigint(15) NOT NULL,
		characterName varchar(128) NOT NULL,
		corporationID bigint(15),
		corporationName varchar(128),
		allianceID bigint(15),
		allianceName varchar(128),
		factionID bigint(15),
		factionName varchar(128),
		cachedUntil bigint(15),
		PRIMARY KEY (characterID),
		UNIQUE (characterID)
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS killboardCharacterStats (
		characterID bigint(15) NOT NULL,
		shipsDestroyed bigint(15), shipsLost bigint(15),
		capsuleDestroyed bigint(15), capsuleLost bigint(15),
		structureDestroyed bigint(15), structureLost bigint(15),
		shipsIskDestroyed bigint(15), shipsIskLost bigint(15),
		capsuleIskDestroyed bigint(15), capsuleIskLost bigint(15),
		structureIskDestroyed bigint(15), structureIskLost bigint(15),
		pointsDestroyed bigint(15), pointsLost bigint(15),
		top1DestroyedGroupID bigint(15), top1DestroyedCount bigint(15),
		top2DestroyedGroupID bigint(15), top2DestroyedCount bigint(15),
		top3DestroyedGroupID bigint(15), top3DestroyedCount bigint(15),
		cachedUntil bigint(15),
		PRIMARY KEY (characterID),
		UNIQUE (characterID)
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS api (
		characterID bigint(15) NOT NULL,
		keyID bigint(15) NOT NULL,
		vCode varchar(64) NOT NULL,
		accessMask bigint(15),
		expires bigint(15),
		cachedUntil bigint(15),
		planetsCachedUntil bigint(15),
		PRIMARY KEY (characterID),
		UNIQUE (characterID)
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS accountstatus (
		keyID bigint(15) NOT NULL,
		paidUntil bigint(15),
		createDate bigint(15),
		logonCount int(9),
		logonMinutes bigint(15),
		cachedUntil bigint(15),
		PRIMARY KEY (keyID),
		UNIQUE(keyID)
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS balance (
		characterID bigint(15) NOT NULL,
		balance numeric(15, 2) NOT NULL,
		cachedUntil bigint(15),
		PRIMARY KEY (characterID),
		UNIQUE (characterID),
		FOREIGN KEY (characterID) REFERENCES api (characterID) ON DELETE CASCADE ON UPDATE CASCADE
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS planets (
		ownerID bigint(15) NOT NULL,
		planetID bigint(15) NOT NULL,
		solarSystemID bigint(15),
		solarSystemName varchar(64),
		planetName varchar(64),
		planetTypeID bigint(15),
		planetTypeName varchar(64),
		ownerName varchar(64),
		lastUpdate bigint(15),
		upgradeLevel int(2),
		numberOfPins int(3),
		apiupdated bigint(15),
		pinsCachedUntil bigint(15),
		linksCachedUntil bigint(15),
		routesCachedUntil bigint(15),
		PRIMARY KEY (planetID, ownerID),
		UNIQUE (planetID, ownerID),
		FOREIGN KEY (ownerID) REFERENCES api (characterID) ON DELETE CASCADE ON UPDATE CASCADE
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS planetpins (
		ownerID bigint(15) NOT NULL,
		planetID bigint(15) NOT NULL,
		pinID bigint(15) NOT NULL,
		typeID bigint(15) NOT NULL,
		typeName varchar(64) NOT NULL,
		schematicID bigint(15) NOT NULL,
		lastLaunchTime bigint(15) NOT NULL,
		cycleTime bigint(15) NOT NULL,
		quantityPerCycle bigint(15) NOT NULL,
		installTime bigint(15) NOT NULL,
		expiryTime bigint(15) NOT NULL,
		contentTypeID bigint(15) NOT NULL,
		contentTypeName varchar(64) NOT NULL,
		contentQuantity bigint(15) NOT NULL,
		longitude NUMERIC(15,10) NOT NULL,
		latitude NUMERIC(15,10) NOT NULL,
		PRIMARY KEY (planetID, ownerID, pinID, contentTypeID),
		FOREIGN KEY (planetID, ownerID) REFERENCES planets (planetID, ownerID) ON DELETE CASCADE ON UPDATE CASCADE
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS planetlinks (
		ownerID bigint(15) NOT NULL,
		planetID bigint(15) NOT NULL,
		sourcePinID bigint(15) NOT NULL,
		destinationPinID bigint(15) NOT NULL,
		linkLevel bigint(15) NOT NULL,
		PRIMARY KEY (planetID, ownerID, sourcePinID, destinationPinID),
		UNIQUE (planetID, ownerID, sourcePinID, destinationPinID),
		FOREIGN KEY (planetID, ownerID) REFERENCES planets (planetID, ownerID) ON DELETE CASCADE ON UPDATE CASCADE
		)';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE TABLE IF NOT EXISTS planetroutes (
		ownerID bigint(15) NOT NULL,
		planetID bigint(15) NOT NULL,
		routeID bigint(15) NOT NULL,
		sourcePinID bigint(15) NOT NULL,
		destinationPinID bigint(15) NOT NULL,
		contentTypeID bigint(15) NOT NULL,
		contentTypeName varchar(64) NOT NULL,
		quantity bigint(15) NOT NULL,
		waypoint1 bigint(15) NOT NULL,
		waypoint2 bigint(15) NOT NULL,
		waypoint3 bigint(15) NOT NULL,
		waypoint4 bigint(15) NOT NULL,
		waypoint5 bigint(15) NOT NULL,
		PRIMARY KEY (planetID, ownerID, routeID),
		UNIQUE (planetID, ownerID, routeID),
		FOREIGN KEY (planetID, ownerID) REFERENCES planets (planetID, ownerID) ON DELETE CASCADE ON UPDATE CASCADE
		)';
		mysql_query($query);
		echo mysql_error();

		$query = "CREATE OR REPLACE VIEW marketgrouplastupdate AS
		SELECT invTypes.typeID, invTypes.typeName, invTypes.marketGroupID, min(CASE WHEN prices.stamp IS NULL THEN 0 ELSE prices.stamp END) lastupdate,
			CASE
				WHEN invTypes.marketGroupID = 1857 THEN 30
				WHEN invTypes.marketGroupID = 1033 THEN 30

				WHEN invTypes.marketGroupID = 1855 THEN 60

				WHEN invTypes.marketGroupID = 512 THEN 60
				WHEN invTypes.marketGroupID = 514 THEN 60
				WHEN invTypes.marketGroupID = 515 THEN 60
				WHEN invTypes.marketGroupID = 516 THEN 60
				WHEN invTypes.marketGroupID = 517 THEN 60
				WHEN invTypes.marketGroupID = 518 THEN 60
				WHEN invTypes.marketGroupID = 519 THEN 60
				WHEN invTypes.marketGroupID = 521 THEN 60
				WHEN invTypes.marketGroupID = 522 THEN 60
				WHEN invTypes.marketGroupID = 523 THEN 60
				WHEN invTypes.marketGroupID = 525 THEN 60
				WHEN invTypes.marketGroupID = 526 THEN 60
				WHEN invTypes.marketGroupID = 527 THEN 60
				WHEN invTypes.marketGroupID = 528 THEN 60
				WHEN invTypes.marketGroupID = 529 THEN 60
				WHEN invTypes.marketGroupID = 530 THEN 60

				WHEN invTypes.marketGroupID = 1333 THEN 120
				WHEN invTypes.marketGroupID = 1334 THEN 120
				WHEN invTypes.marketGroupID = 1335 THEN 120
				WHEN invTypes.marketGroupID = 1336 THEN 120
				WHEN invTypes.marketGroupID = 1337 THEN 120

				ELSE 24 * 60
			END updateintervall
		FROM evedump.invTypes
		LEFT JOIN eve.prices ON prices.id = invTypes.typeID
		WHERE invTypes.published=1 AND invTypes.marketGroupID IS NOT NULL AND invTypes.typeID < 300000 AND invTypes.typeName NOT LIKE '%Blueprint'
		GROUP BY invTypes.typeID
		ORDER BY updateintervall, lastupdate, invTypes.marketGroupID, invTypes.typeID
		";
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE OR REPLACE VIEW planetstorage AS
		SELECT ownerID, planetID, contentTypeID as typeID, contentTypeName as typeName, SUM(contentQuantity) as quantity
		FROM planetpins
		WHERE contentTypeID != 0
		GROUP BY ownerID, planetID, contentTypeID
		';
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE OR REPLACE VIEW planetstoragepins AS
		SELECT planetpins.ownerID, planetpins.planetID, planetpins.pinID, planetpins.typeID, planetpins.typeName, pintype.capacity, ROUND(SUM(planetpins.contentQuantity * contenttype.volume),2) AS contentVolume
		FROM planetpins, evedump.invTypes pintype, evedump.invTypes contenttype
		WHERE pintype.capacity!=0
		AND planetpins.typeID=pintype.typeID AND planetpins.contentTypeID=contenttype.typeID
		GROUP BY ownerID, planetID, pinID
		';
		mysql_query($query);
		echo mysql_error();

		$query = "CREATE OR REPLACE VIEW planetindustrypins AS
		SELECT ownerID, planetID, pinID, typeID, typeName, schematicID, lastLaunchTime, cycleTime, quantityPerCycle, installTime, expiryTime
		FROM planetpins
		WHERE (typeName LIKE '%Extractor%' OR typeName LIKE '%Industry Facility' OR typeName LIKE '%High-Tech Production Plant')
		GROUP BY ownerID, planetID, pinID
		";
		mysql_query($query);
		echo mysql_error();

		$query = 'CREATE OR REPLACE VIEW planetroutesbypins AS
		SELECT planetpins.ownerID, planetpins.planetID, planetpins.pinID, planetpins.typeID, planetpins.typeName, planetpins.schematicID,
		CASE WHEN sourcepin.cycleTime!=0 THEN sourcepin.cycleTime ELSE destinationpin.cycleTime END as cycleTime,
		planetpins.quantityPerCycle, planetroutes.routeID, planetroutes.contentTypeID, planetroutes.contentTypeName,
		CASE WHEN planetpins.pinID=planetroutes.destinationPinID THEN planetroutes.quantity ELSE (0 - planetroutes.quantity) END as quantity,
		ROUND((CASE WHEN planetpins.pinID=planetroutes.destinationPinID THEN planetroutes.quantity ELSE (0 - planetroutes.quantity) END) / (CASE WHEN sourcepin.cycleTime!=0 THEN sourcepin.cycleTime ELSE destinationpin.cycleTime END) *60) as quantityPerHour,
		ROUND((CASE WHEN planetpins.pinID=planetroutes.destinationPinID THEN planetroutes.quantity ELSE (0 - planetroutes.quantity) END) *contenttype.volume/ (CASE WHEN sourcepin.cycleTime!=0 THEN sourcepin.cycleTime ELSE destinationpin.cycleTime END) *60 ,2) as volumePerHour
		FROM planetpins
		LEFT JOIN planetroutes ON planetpins.ownerID=planetroutes.ownerID AND planetpins.planetID=planetroutes.planetID AND (planetpins.pinID=planetroutes.sourcePinID OR planetpins.pinID=planetroutes.destinationPinID)
		JOIN evedump.invTypes AS contenttype ON contenttype.typeID=planetroutes.contentTypeID
		JOIN planetpins AS sourcepin ON sourcepin.ownerID=planetpins.ownerID AND sourcepin.planetID=planetpins.planetID AND sourcepin.pinID=planetroutes.sourcePinID
		JOIN planetpins AS destinationpin ON destinationpin.ownerID=planetpins.ownerID AND destinationpin.planetID=planetpins.planetID AND destinationpin.pinID=planetroutes.destinationPinID
		GROUP BY planetpins.ownerID, planetpins.planetID, planetpins.pinID, planetroutes.routeID
		ORDER BY planetpins.ownerID, planetpins.planetID, planetpins.typeName, planetroutes.contentTypeName
		';
		mysql_query($query);
		echo mysql_error();

		$query = "CREATE OR REPLACE VIEW planetproductions AS
		SELECT ownerID, planetID, contentTypeID as typeID, contentTypeName as typeName, SUM(0-quantityPerHour) as productionPerHour
		FROM planetroutesbypins
		WHERE (typeName LIKE '%Extractor%' OR typeName LIKE '%Industry Facility' OR typeName LIKE '%High-Tech Production Plant')
		GROUP BY ownerID, planetID, contentTypeID
		ORDER BY ownerID, planetID, contentTypeName
		";
		mysql_query($query);
		echo mysql_error();

		mysql_close();
	}
	function updateallprices()
	{
		require_once 'Prices.php';
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';
		$table = "prices";

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$oldenoughquerypart = "stamp<".(time() - 60*30); //older than 25 Min

		$query="SELECT systemid FROM ".$table;
		$query.=" WHERE ".$oldenoughquerypart;
		$query.=" group by systemid";
		$result=mysql_query($query);
		$num=mysql_numrows($result);

		echo $num." systems with itemprices to update\n";

		if ($num == 0) { return; }

		$systemids = array();

		for ($i = 0; $i < $num; $i++) {
			$systemids[] = mysql_result($result, $i,'systemid');
		}

		foreach ($systemids as $systemid) {
			echo "system ".$systemid.": ";

			$query="SELECT id FROM ".$table;
			$query.=" WHERE ".$oldenoughquerypart." and systemid=".$systemid;
			$result=mysql_query($query);
			$num=mysql_numrows($result);

			echo $num." itemprices to update\n";

			$ids = array();

			for ($i = 0; $i < $num; $i++) {
				$ids[] = mysql_result($result, $i,'id');
			}

			Prices::updatePricesOfIDs($systemid, $ids);
		}
		echo mysql_error();
		mysql_close();
	}
	function updateprices() {
		global $mysqli;
		require_once 'mysqlDetails.php';
		require_once 'Prices.php';

		$systems = array(
			'Jita' => 30000142,
			'Hek' => 30002053,
			'Amarr' => 30002187,
			'Rens' => 30002510,
			'Dodixie' => 30002659
		);

		$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall <= 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 300";
		$result = $mysqli->query($query);

		$ids = array();
		while ($row = $result->fetch_object()) {
			$ids[] = $row->typeID;
		}
		$result->close();
		sort($ids);

		if (count($ids) > 0) {
			foreach ($systems as $systemname => $systemid) {
				echo "system ".$systemid." (".$systemname.")\n";
				Prices::updatePricesOfIDs($systemid, $ids);
			}
		}


		//Generellen Daten
		$systemname = 'Jita';
		$systemid = $systems[$systemname];

		echo "system ".$systemid." (".$systemname.")\n";
		$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 300";
		$result = $mysqli->query($query);
		while ($result->num_rows > 0) {
			$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 300";
			try {
				for ($i = 0; $i< 6; $i++)
					updatepricesofquery($query, $systemid);
			}	catch (Exception $e) {
				echo $e->getMessage()."... try 50\n";
				$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 50";
				try {
					for ($i = 0; $i< 5; $i++)
						updatepricesofquery($query, $systemid);
				}	catch (Exception $e) {
					echo $e->getMessage()."... try 10\n";
					$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 10";
					try {
						for ($i = 0; $i< 5; $i++)
							updatepricesofquery($query, $systemid);
					}	catch (Exception $e) {
						echo $e->getMessage()."... try 1\n";
						$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 1";
						try {
							for ($i = 0; $i< 10; $i++)
								updatepricesofquery($query, $systemid);
						}	catch (Exception $e) {
							$result = $mysqli->query($query);
							$id = $result->fetch_object()->typeID;
							$result->close();

							$buy = (float) -1;
							$sell = (float) -1;
							$buyunits = (int) -1;
							$sellunits = (int) -1;
							$stamp = time() + 24*60*60;
							$query = "INSERT INTO eve.prices (ID, systemID, buy, sell, buyunits, sellunits, stamp)
							VALUES ('$id', '$systemid', '$buy', '$sell', '$buyunits', '$sellunits', '$stamp')
							ON DUPLICATE KEY UPDATE buy='$buy',sell='$sell',buyunits='$buyunits',sellunits='$sellunits',stamp='$stamp'";
							$mysqli->query($query);
							echo "Item $id: ".$e->getMessage()."\n";
						}
					}
				}
			}
			$query = "SELECT typeID FROM eve.marketgrouplastupdate WHERE updateIntervall > 120 AND lastupdate < ".time()." - updateintervall * 60 LIMIT 300";
			$result = $mysqli->query($query);
		}
	}
	function updatepricesofquery($query, $systemid) {
		global $mysqli;
		require_once 'mysqlDetails.php';
		require_once 'Prices.php';

		$result = $mysqli->query($query);

		$ids = array();
		while ($row = $result->fetch_object()) {
			$ids[] = $row->typeID;
		}
		$result->close();
		sort($ids);

		if (count($ids) > 0) {
			Prices::updatePricesOfIDs($systemid, $ids);
		}
	}
	function updateapikeyinfo()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$query="SELECT keyID, vCode FROM api";
		$query.=" WHERE cachedUntil<'".time()."' OR cachedUntil IS NULL";
		$result = mysql_query($query);
		$num=mysql_numrows($result);

		echo "apikeyinfo of ".$num." keys to update\n";

		if ($num == 0) { return; }

		for($i = 0; $i < $num; $i++) {
			$keyID = mysql_result($result, $i, 'keyID');
			$vCode = mysql_result($result, $i, 'vCode');
			echo "  keyID ".$keyID.": ";

			try {
				$xml = callAPI("account/APIKeyInfo", array('keyID' => $keyID, 'vCode' => $vCode) );
				$cachedUntil = strtotime($xml->xpath('/eveapi/cachedUntil')[0]);
				$accessMask = (float) $xml->xpath('/eveapi/result/key')[0]['accessMask'];
				$expires = (int) strtotime($xml->xpath('/eveapi/result/key')[0]['expires']);

				$query = "UPDATE api SET accessMask=$accessMask,expires='$expires',cachedUntil=$cachedUntil WHERE keyID=$keyID";
				mysql_query($query);
				echo mysql_error();

				echo "updated\n";
			} catch (Exception $e) {
				echo "Error (File: ".$e->getFile().", line ".$e->getLine()."): ".$e->getMessage()."\n";
			}
		}

		echo mysql_error();
		mysql_close();
	}
	function updateaccountstatus()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$query="SELECT api.keyID, api.vCode, api.accessMask FROM api LEFT JOIN accountstatus ON api.keyID=accountstatus.keyID";
		$query.=" WHERE accountstatus.cachedUntil<'".time()."' OR accountstatus.cachedUntil IS NULL";
		$result = mysql_query($query);
		$num=mysql_numrows($result);

		echo "accountstatus of ".$num." accounts to update\n";

		if ($num == 0) { return; }

		for($i = 0; $i < $num; $i++) {
			$keyID = mysql_result($result, $i, 'keyID');
			$vCode = mysql_result($result, $i, 'vCode');
			$accessMask = (int) mysql_result($result, $i, 'accessMask');
			echo "  keyID ".$keyID.": ";

			if (($accessMask & 33554432) != 33554432) {
				echo "not enough api access\n";
				continue;
			}

			try {
				$xml = callAPI("account/AccountStatus", array('keyID' => $keyID, 'vCode' => $vCode) );
				$cachedUntil = strtotime($xml->xpath('/eveapi/cachedUntil')[0]);
				$paidUntil = (int) strtotime($xml->xpath('/eveapi/result/paidUntil')[0]);
				$createDate = (int) strtotime($xml->xpath('/eveapi/result/createDate')[0]);
				$logonCount = (int) $xml->xpath('/eveapi/result/logonCount')[0];
				$logonMinutes = (int) $xml->xpath('/eveapi/result/logonMinutes')[0];

				$query = "INSERT INTO accountstatus (keyID) VALUES ('$keyID')";
				mysql_query($query);
//				echo mysql_error();

				$query = "UPDATE accountstatus SET paidUntil=$paidUntil, createDate=$createDate, logonCount=$logonCount, logonMinutes=$logonMinutes, cachedUntil=$cachedUntil WHERE keyID=$keyID";
				mysql_query($query);
				echo mysql_error();

				echo "updated\n";
			} catch (Exception $e) {
				echo "Error (File: ".$e->getFile().", line ".$e->getLine()."): ".$e->getMessage()."\n";
			}
		}

		echo mysql_error();
		mysql_close();
	}
	function updateallbalance()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$query="SELECT api.characterID, keyID, vCode, accessMask FROM api LEFT JOIN balance ON api.characterID=balance.characterID";
		$query.=" WHERE balance.cachedUntil<'".time()."' OR balance.cachedUntil IS NULL";
		$result = mysql_query($query);
		$num=mysql_numrows($result);

		echo "balance of ".$num." characters to update\n";

		if ($num == 0) { return; }

		for($i = 0; $i < $num; $i++) {
			$characterID = mysql_result($result, $i, 'characterID');
			$keyID = mysql_result($result, $i, 'keyID');
			$vCode = mysql_result($result, $i, 'vCode');
			$accessMask = (int) mysql_result($result, $i, 'accessMask');
			echo "  character ".$characterID.": ";

			if (($accessMask & 1) != 1) {
				echo "not enough api access\n";
				continue;
			}

			try {
				$xml = callAPI("char/AccountBalance", array('keyID' => $keyID, 'vCode' => $vCode, 'characterID' => $characterID) );
				$cachedUntil = strtotime($xml->xpath('/eveapi/cachedUntil')[0]);
				$balance = (float) $xml->xpath('/eveapi/result/rowset/row')[0]->attributes()->balance;

				$query = "DELETE FROM balance WHERE characterID=$characterID";
				mysql_query($query);

				$query = "INSERT INTO balance VALUES ($characterID, $balance, $cachedUntil)";
				mysql_query($query);
				echo mysql_error();

				echo "updated\n";
			} catch (Exception $e) {
				echo "Error (File: ".$e->getFile().", line ".$e->getLine()."): ".$e->getMessage()."\n";
			}
		}

		echo mysql_error();
		mysql_close();
	}
	function updateallplanets()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		$query="SELECT characterID FROM api ";
		$query.=" WHERE planetsCachedUntil<".time()." OR planetsCachedUntil IS NULL";
		$result = mysql_query($query);
		$num=mysql_numrows($result);

		echo "planets of ".$num." characters to update\n";

		if ($num == 0) { return; }

		$characterIDs = array();

		$i=0;
		while ($i < $num)
		{
			$characterIDs[] = mysql_result($result, $i, 'characterID');
			$i++;
		}

		foreach ($characterIDs as $characterID) {
			echo "  character ".$characterID.": ";

			$query = "SELECT * FROM api WHERE characterID='".$characterID."'";
			$result= mysql_query($query);
			if (mysql_numrows($result) != 1)
			{
				echo "no api available\n";
				continue;
			}

			$keyID = mysql_result($result, 0, 'keyID');
			$vCode = mysql_result($result, 0, 'vCode');
			$accessMask = (int) mysql_result($result, 0, 'accessMask');

			if (($accessMask & 2) != 2) {
				echo "not enough api access\n";
				continue;
			}

			try {
				$xml = callAPI("char/PlanetaryColonies", array('keyID' => $keyID, 'vCode' => $vCode, 'characterID' => $characterID) );
				$cachedUntil = strtotime($xml->xpath('/eveapi/cachedUntil')[0]);

				foreach ($xml->xpath('/eveapi/result/rowset/row') as $row) {
					$query = "INSERT INTO planets (planetID,ownerID) VALUES (".$row->attributes()->planetID.",".$characterID.")";
					mysql_query($query);

					$query = "UPDATE planets SET ";
					$i = 0;
					foreach ($row->attributes() as $key => $value) {
						if ($key == 'lastUpdate') { $value = strtotime($value); }
						if ($i++ > 0) { $query .= ","; }
						$query .= $key."=";
						$query .= "'";
						$query .= $value;
						$query .= "'";
					}
					$query .= ",apiupdated='".time()."'";
					$query .= " WHERE planetID=".$row->attributes()->planetID." and ownerID=".$characterID;
					mysql_query($query);
					echo mysql_error();
				}

				$query = "DELETE FROM planets WHERE ownerID=".$characterID." AND updated<".time();
				mysql_query($query);

				$query = "UPDATE api SET planetsCachedUntil=".$cachedUntil." WHERE characterID=".$characterID;
				mysql_query($query);

				echo "updated\n";
			} catch (Exception $e) {
					$query = "UPDATE api SET planetsCachedUntil=".(time() + 60 * 60)." WHERE characterID=".$characterID;
					mysql_query($query);

					echo "Error (File: ".$e->getFile().", line ".$e->getLine()."): ".$e->getMessage()."\n";
			}
		}
		echo mysql_error();
		mysql_close();
	}
	function updateallplanetinfos()
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'eve';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		updateplanetinfos('char/PlanetaryPins', 'planetpins', 'pinsCachedUntil');
		updateplanetinfos('char/PlanetaryLinks', 'planetlinks', 'linksCachedUntil');
		updateplanetinfos('char/PlanetaryRoutes', 'planetroutes', 'routesCachedUntil');

		mysql_query("UPDATE planetpins SET cycleTime=30 WHERE typeName LIKE '%Basic Industry Facility'");
		mysql_query("UPDATE planetpins SET cycleTime=60 WHERE typeName LIKE '%Advanced Industry Facility'");
		mysql_query("UPDATE planetpins SET cycleTime=60 WHERE typeName LIKE '%High-Tech Production Plant'");

		mysql_close();
	}
	function updateplanetinfos($api, $table, $cachedUntilColumnName)
	{
		$query = "SELECT ownerID FROM planets";
		$query .= " WHERE $cachedUntilColumnName<".time()." OR $cachedUntilColumnName is NULL";
		$query .= " GROUP BY ownerID";
		$result = mysql_query($query);
		$num=mysql_numrows($result);

		echo "$table of ".$num." characters to update\n";

		if ($num == 0) { return; }

		$players = array();
		for ($i = 0; $i < $num; $i++) { $players[] = mysql_result($result, $i, 'ownerID'); }

		foreach ($players as $ownerID) {
			$query = "SELECT * FROM api WHERE characterID='".$ownerID."'";
			$result= mysql_query($query);
			if (mysql_numrows($result) != 1)
			{
				echo $ownerID." - no api available\n";
				continue;
			}

			$keyID = mysql_result($result, 0, 'keyID');
			$vCode = mysql_result($result, 0, 'vCode');

			echo "  character ".$ownerID."\n";

			$query = "SELECT * FROM planets WHERE ";
			$query .= "($cachedUntilColumnName<".time()." OR $cachedUntilColumnName is NULL) and ";
			$query .= "ownerID=".$ownerID;
			$result = mysql_query($query);
			$num=mysql_numrows($result);

			echo "    $table of ".$num." planets to update\n";

			for ($i = 0; $i < $num; $i++) {
				$planetID = mysql_result($result, $i, 'planetID');
				echo "      planetID ".$planetID."\n";
				$xml = callAPI($api, array('keyID' => $keyID, 'vCode' => $vCode, 'characterID' => $ownerID, 'planetID' => $planetID));
				$cachedUntil = strtotime($xml->xpath('/eveapi/cachedUntil')[0]);

				$query = "DELETE FROM $table WHERE ownerID=".$ownerID." AND planetID=".$planetID;
				mysql_query($query);

				foreach ($xml->xpath('/eveapi/result/rowset/row') as $row) {
					$query = "INSERT INTO $table VALUES (";
					$query .= "'".$ownerID."',";
					$query .= "'".$planetID."',";
					$j = 0;
					foreach ($row->attributes() as $key => $value) {
						if ($key == 'lastLaunchTime' ||
							$key == 'installTime' ||
							$key == 'expiryTime') {	$value = strtotime($value); }
						if ($j++ > 0) { $query .= ","; }
						$query .= "'";
						$query .= $value;
						$query .= "'";
					}
					$query .= ")";
					mysql_query($query);
					echo mysql_error();
				}

				$query = "UPDATE planets SET $cachedUntilColumnName=".$cachedUntil." WHERE ownerID=".$ownerID." AND planetID=".$planetID;
				mysql_query($query);
			}
		}
	}
?>
