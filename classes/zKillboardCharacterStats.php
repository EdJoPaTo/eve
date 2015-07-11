<?php

class zKillboardCharacterStats {
	var $characterID;
	var $iskDestroyed;
	var $iskLost;
	var $shipsDestroyed;
	var $shipsLost;

	function __construct( $characterID = 0, $iskDestroyed = 0, $iskLost = 0, $shipsDestroyed = 0, $shipsLost = 0 ) {
		$this->characterID = (int) $characterID;
		$this->iskDestroyed = (int) $iskDestroyed;
		$this->iskLost = (int) $iskLost;
		$this->shipsDestroyed = (int) $shipsDestroyed;
		$this->shipsLost = (int) $shipsLost;
	}

	public static function getKillboardFromID( $characterID ) {
		global $mysqli;
		require_once 'mysqlDetails.php';
		require_once 'evefunctions.php';

		$result = $mysqli->query( "SELECT * FROM eve.killboardCharacterStats WHERE characterID=$characterID" );
		if ( ( $row = $result->fetch_object( ) ) && ( (int) $row->cachedUntil > time() ) ) {
			return new zKillboardCharacterStats(
				$row->characterID,
				$row->iskDestroyed,
				$row->iskLost,
				$row->shipsDestroyed,
				$row->shipsLost
			);
		}

		$json = callKillboardCharacterStats( $characterID );
		$cachedUntil = time() + 60 * 60 * 24 * 5; // 5 Tage
		$iskDestroyed = empty( $json->iskDestroyed ) ? 0 : (int) $json->iskDestroyed;
		$iskLost = empty( $json->iskLost ) ? 0 : (int) $json->iskLost;
		$shipsDestroyed = empty( $json->shipsDestroyed ) ? 0 : (int) $json->shipsDestroyed;
		$shipsLost = empty( $json->shipsLost ) ? 0 : (int) $json->shipsLost;

		$query = "INSERT INTO eve.killboardCharacterStats (characterID, iskDestroyed, iskLost, shipsDestroyed, shipsLost, cachedUntil)
		VALUES ('$characterID', '$iskDestroyed', '$iskLost', '$shipsDestroyed', '$shipsLost', '$cachedUntil')
		ON DUPLICATE KEY UPDATE iskDestroyed='$iskDestroyed', iskLost='$iskLost', shipsDestroyed='$shipsDestroyed', shipsLost='$shipsLost', cachedUntil='$cachedUntil'";
		$mysqli->query( $query );
		if ( $mysqli->error ) {
			echo $mysqli->error . "<br>\n";
		}

		return new zKillboardCharacterStats( $characterID, $iskDestroyed, $iskLost, $shipsDestroyed, $shipsLost );
	}
}

?>
