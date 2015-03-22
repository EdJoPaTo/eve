<?php
	include $_SERVER['DOCUMENT_ROOT'].'/../myfunctions.php';
	$title = "ETO";
	
	if ($_SERVER['HTTP_EVE_TRUSTED'] == 'Yes')
	{
		$host = 'localhost';
		$user = 'eto';
		$password = 'eto';
		$database = 'everadar';
		$table = 'pilots';

		$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
		mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

		if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$table."'"))!=1) /* Table erstellen wenn diese nicht existiert */
		{
			$query = 'create table pilots (
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
			mysql_query($query);
		}

		$query = "DELETE FROM ".$table." WHERE HTTP_EVE_CHARID='".$_SERVER['HTTP_EVE_CHARID']."'";
		mysql_query($query);

		$query = "INSERT INTO ".$table." VALUES (";
		$i = 0;
		foreach ($igbsave as $val)
		{
			if ($i++ > 0) { $query .= ","; }
			$query .= "'";
			if (!empty($_SERVER[$val])) {
				$query .= mysql_real_escape_string ($_SERVER[$val]);
			}
			$query .= "'";
		}
		$query .= ");";
		mysql_query($query);

		mysql_close();
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
