<?php

class zKillboardDestructionSet {
	var $iskDestroyed;
	var $iskLost;
	var $shipsDestroyed;
	var $shipsLost;
	var $pointsDestroyed;
	var $pointsLost;

	function __construct( $iskDestroyed = 0, $iskLost = 0, $shipsDestroyed = 0, $shipsLost = 0, $pointsDestroyed = 0, $pointsLost = 0 ) {
		$this->iskDestroyed = (int) $iskDestroyed;
		$this->iskLost = (int) $iskLost;
		$this->shipsDestroyed = (int) $shipsDestroyed;
		$this->shipsLost = (int) $shipsLost;
		$this->pointsDestroyed = (int) $pointsDestroyed;
		$this->pointsLost = (int) $pointsLost;
	}
}

class zKillboardCharacterStats {
	var $characterID;
	var $allTime;

	function __construct( $characterID = 0, zKillboardDestructionSet $allTime ) {
		$this->characterID = (int) $characterID;
		$this->allTime = $allTime;
	}

	public static function getKillboardFromID( $characterID ) {
		global $mysqli;
		require_once 'mysqlDetails.php';
		require_once 'evefunctions.php';

		$result = $mysqli->query( "SELECT * FROM eve.killboardCharacterStats WHERE characterID=$characterID" );
		if ( ( $row = $result->fetch_object( ) ) && ( (int) $row->cachedUntil > time() ) ) {
			return new zKillboardCharacterStats(
				$row->characterID,
				new zKillboardDestructionSet(
					$row->iskDestroyed,
					$row->iskLost,
					$row->shipsDestroyed,
					$row->shipsLost,
					$row->pointsDestroyed,
					$row->pointsLost
				)
			);
		}

		$json = callKillboardCharacterStats( $characterID );
		$cachedUntil = time() + 60 * 60 * 24 * 5; // 5 Tage
		$iskDestroyed = empty( $json->iskDestroyed ) ? 0 : (int) $json->iskDestroyed;
		$iskLost = empty( $json->iskLost ) ? 0 : (int) $json->iskLost;
		$shipsDestroyed = empty( $json->shipsDestroyed ) ? 0 : (int) $json->shipsDestroyed;
		$shipsLost = empty( $json->shipsLost ) ? 0 : (int) $json->shipsLost;
		$pointsDestroyed = empty( $json->pointsDestroyed ) ? 0 : (int) $json->pointsDestroyed;
		$pointsLost = empty( $json->pointsLost ) ? 0 : (int) $json->pointsLost;

		$query = "INSERT INTO eve.killboardCharacterStats (characterID, iskDestroyed, iskLost, shipsDestroyed, shipsLost, pointsDestroyed, pointsLost, cachedUntil)
		VALUES ('$characterID', '$iskDestroyed', '$iskLost', '$shipsDestroyed', '$shipsLost', '$pointsDestroyed', '$pointsLost', '$cachedUntil')
		ON DUPLICATE KEY UPDATE iskDestroyed='$iskDestroyed', iskLost='$iskLost', shipsDestroyed='$shipsDestroyed', shipsLost='$shipsLost', pointsDestroyed='$pointsDestroyed', pointsLost='$pointsLost', cachedUntil='$cachedUntil'";
		$mysqli->query( $query );
		if ( $mysqli->error ) {
			echo $mysqli->error . "<br>\n";
		}

		return new zKillboardCharacterStats(
			$characterID,
			new zKillboardDestructionSet( $iskDestroyed, $iskLost, $shipsDestroyed, $shipsLost, $pointsDestroyed, $pointsLost )
		);
	}
}

?>
