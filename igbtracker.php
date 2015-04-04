<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
	$title = "ETO";

	if (isigb() == 'trusted')
	{
		$query = 'CREATE TABLE IF NOT EXISTS everadar.pilots (
		HTTP_EVE_CHARID int(10) NOT NULL,
		HTTP_EVE_CHARNAME varchar(30) NOT NULL,
		HTTP_EVE_CORPID int(10) NOT NULL,
		HTTP_EVE_CORPNAME varchar(30) NOT NULL,
		HTTP_EVE_CORPTICKER varchar(30) NOT NULL,
		HTTP_EVE_ALLIANCEID int(10) NOT NULL,
		HTTP_EVE_ALLIANCENAME varchar(30) NOT NULL,
		HTTP_EVE_REGIONID int(10) NOT NULL,
		HTTP_EVE_REGIONNAME varchar(30) NOT NULL,
		HTTP_EVE_CONSTELLATIONID int(10) NOT NULL,
		HTTP_EVE_CONSTELLATIONNAME varchar(30) NOT NULL,
		HTTP_EVE_SOLARSYSTEMID int(10) NOT NULL,
		HTTP_EVE_SOLARSYSTEMNAME varchar(30) NOT NULL,
		HTTP_EVE_SYSTEMSECURITY varchar(30) NOT NULL,
		HTTP_EVE_STATIONID int(10) NOT NULL,
		HTTP_EVE_STATIONNAME varchar(70) NOT NULL,
		HTTP_EVE_SHIPID bigint(20) NOT NULL,
		HTTP_EVE_SHIPNAME varchar(30) NOT NULL,
		HTTP_EVE_SHIPTYPEID int(10) NOT NULL,
		HTTP_EVE_SHIPTYPENAME varchar(30) NOT NULL,
		HTTP_EVE_CORPROLE bigint(20) NOT NULL,
		HTTP_EVE_WARFACTIONID int(10) NOT NULL,
		HTTP_EVE_MILITIAID int(10) NOT NULL,
		HTTP_EVE_MILITIANAME varchar(30) NOT NULL,
		REMOTE_ADDR varchar(30) NOT NULL,
		REQUEST_TIME bigint(20) NOT NULL,
		PRIMARY KEY (HTTP_EVE_CHARID),
		UNIQUE HTTP_EVE_CHARID (HTTP_EVE_CHARID)
		)';
		$mysqli->query($query);

		$query = "DELETE FROM everadar.pilots WHERE HTTP_EVE_CHARID='".$_SERVER['HTTP_EVE_CHARID']."'";
		$mysqli->query($query);

		$query = "INSERT INTO everadar.pilots VALUES (";
		$i = 0;
		foreach ($igbsave as $val)
		{
			if ($i++ > 0) { $query .= ","; }
			$query .= "'";
			if (!empty($_SERVER[$val])) {
				$query .= $mysqli->real_escape_string ($_SERVER[$val]);
			}
			$query .= "'";
		}
		$query .= ");";
		$mysqli->query($query);

		$mysqli->close();
		unset($host, $user, $password, $database, $table, $conn, $query, $i);
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<META HTTP-EQUIV="refresh" CONTENT="5">
	</head>
	<body>
		I'm a dolphin!
	</body>
</html>
