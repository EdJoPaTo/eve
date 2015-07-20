<?php

class zKillboardDestructionSet {
	var $iskDestroyed;
	var $iskLost;
	var $iskPercentage;
	var $shipsDestroyed;
	var $shipsLost;
	var $shipsPercentage;
	var $pointsDestroyed;
	var $pointsLost;
	var $pointsPercentage;

	function __construct( $iskDestroyed = 0, $iskLost = 0, $shipsDestroyed = 0, $shipsLost = 0, $pointsDestroyed = 0, $pointsLost = 0 ) {
		$this->iskDestroyed = (int) $iskDestroyed;
		$this->iskLost = (int) $iskLost;
		$tmp = $this->iskDestroyed + $this->iskLost;
		$this->iskPercentage = $tmp == 0 ? 0 : $this->iskDestroyed / $tmp;

		$this->shipsDestroyed = (int) $shipsDestroyed;
		$this->shipsLost = (int) $shipsLost;
		$tmp = $this->shipsDestroyed + $this->shipsLost;
		$this->shipsPercentage = $tmp == 0 ? 0 : $this->shipsDestroyed / $tmp;

		$this->pointsDestroyed = (int) $pointsDestroyed;
		$this->pointsLost = (int) $pointsLost;
		$tmp = $this->pointsDestroyed + $this->pointsLost;
		$this->pointsPercentage = $tmp == 0 ? 0 : $this->pointsDestroyed / $tmp;
	}
}

class zKillboardTopDestroyed {
	var $groupID;
	var $shipsDestroyed;

	function __construct( $groupID = 0, $shipsDestroyed = 0 ) {
		$this->groupID = (int) $groupID;
		$this->shipsDestroyed = (int) $shipsDestroyed;
	}
}

class zKillboardCharacterStats {
	var $characterID;
	var $allTime;
	var $topDestroyed;

	function __construct( $characterID = 0, zKillboardDestructionSet $allTime, zKillboardTopDestroyed $top1, zKillboardTopDestroyed $top2, zKillboardTopDestroyed $top3 ) {
		$this->characterID = (int) $characterID;
		$this->allTime = $allTime;
		$this->topDestroyed = array();
		$this->topDestroyed[] = $top1;
		$this->topDestroyed[] = $top2;
		$this->topDestroyed[] = $top3;
	}

	static function cmpzKillboardTopDestroyed( $a, $b ) {
		$value = 0;

		if ( $value == 0 ) {
			$aValue = $a[ 'shipsDestroyed' ];
			$bValue = $b[ 'shipsDestroyed' ];
			$tmp = $bValue - $aValue;
			$value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
		}
		if ( $value == 0 ) {
			$aValue = $a[ 'iskDestroyed' ];
			$bValue = $b[ 'iskDestroyed' ];
			$tmp = $bValue - $aValue;
			$value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
		}

		return $value;
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
				),
				new zKillboardTopDestroyed(
					$row->top1DestroyedGroupID,
					$row->top1DestroyedCount
				),
				new zKillboardTopDestroyed(
					$row->top2DestroyedGroupID,
					$row->top2DestroyedCount
				),
				new zKillboardTopDestroyed(
					$row->top3DestroyedGroupID,
					$row->top3DestroyedCount
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

		$topDestroyed = array();
		if ( !empty( $json->groups ) ) {
			foreach ( $json->groups as $key => $value ) {
				$tmp = array();
				$tmp[ 'groupID' ] = (int) $key;
				$tmp[ 'shipsDestroyed' ] = empty( $value->shipsDestroyed ) ? 0 : (int) $value->shipsDestroyed;
				$tmp[ 'iskDestroyed' ] = empty( $value->iskDestroyed ) ? 0 : (int) $value->iskDestroyed;

				if ( $tmp[ 'shipsDestroyed' ] > 0 ) {
					$topDestroyed[] = $tmp;
				}
			}
		}

		usort( $topDestroyed, "zKillboardCharacterStats::cmpzKillboardTopDestroyed" );
		for ( $i=0; $i < 3; $i++ ) {
			if ( empty ( $topDestroyed[ $i ] ) ) {
				$topDestroyed[ $i ] = array();
				$topDestroyed[ $i ][ 'groupID' ] = 0;
				$topDestroyed[ $i ][ 'shipsDestroyed' ] = 0;
			}
		}

		$query = "INSERT INTO eve.killboardCharacterStats (
			characterID,
			iskDestroyed, iskLost,
			shipsDestroyed, shipsLost,
			pointsDestroyed, pointsLost,
			top1DestroyedGroupID, top1DestroyedCount,
			top2DestroyedGroupID, top2DestroyedCount,
			top3DestroyedGroupID, top3DestroyedCount,
			cachedUntil)
		VALUES (
			'$characterID',
			'$iskDestroyed', '$iskLost',
			'$shipsDestroyed', '$shipsLost',
			'$pointsDestroyed', '$pointsLost',
			'" . $topDestroyed[ 0 ][ 'groupID' ] . "', '" . $topDestroyed[ 0 ][ 'shipsDestroyed' ] . "',
			'" . $topDestroyed[ 1 ][ 'groupID' ] . "', '" . $topDestroyed[ 1 ][ 'shipsDestroyed' ] . "',
			'" . $topDestroyed[ 2 ][ 'groupID' ] . "', '" . $topDestroyed[ 2 ][ 'shipsDestroyed' ] . "',
			'$cachedUntil')
		ON DUPLICATE KEY
		UPDATE
			iskDestroyed='$iskDestroyed', iskLost='$iskLost',
			shipsDestroyed='$shipsDestroyed', shipsLost='$shipsLost',
			pointsDestroyed='$pointsDestroyed', pointsLost='$pointsLost',
			top1DestroyedGroupID='" . $topDestroyed[ 0 ][ 'groupID' ] . "', top1DestroyedCount='" . $topDestroyed[ 0 ][ 'shipsDestroyed' ] . "',
			top2DestroyedGroupID='" . $topDestroyed[ 1 ][ 'groupID' ] . "', top2DestroyedCount='" . $topDestroyed[ 1 ][ 'shipsDestroyed' ] . "',
			top3DestroyedGroupID='" . $topDestroyed[ 2 ][ 'groupID' ] . "', top3DestroyedCount='" . $topDestroyed[ 2 ][ 'shipsDestroyed' ] . "',
			cachedUntil='$cachedUntil'";
		$mysqli->query( $query );
		if ( $mysqli->error ) {
			echo $mysqli->error . "<br>\n";
		}

		return new zKillboardCharacterStats(
			$characterID,
			new zKillboardDestructionSet( $iskDestroyed, $iskLost, $shipsDestroyed, $shipsLost, $pointsDestroyed, $pointsLost ),
			new zKillboardTopDestroyed( $topDestroyed[ 0 ][ 'groupID' ], $topDestroyed[ 0 ][ 'shipsDestroyed' ] ),
			new zKillboardTopDestroyed( $topDestroyed[ 1 ][ 'groupID' ], $topDestroyed[ 1 ][ 'shipsDestroyed' ] ),
			new zKillboardTopDestroyed( $topDestroyed[ 2 ][ 'groupID' ], $topDestroyed[ 2 ][ 'shipsDestroyed' ] )
		);
	}
}

?>
