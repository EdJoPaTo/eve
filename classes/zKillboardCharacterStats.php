<?php

class zKillboardDestructionSet {
	var $iskDestroyed;
	var $iskLost;
	var $iskPercentage;

	var $shipsDestroyed;
	var $shipsLost;
	var $capsuleDestroyed;
	var $capsuleLost;
	var $structureDestroyed;
	var $structureLost;
	var $allTypesDestroyed;
	var $allTypesLost;

	var $shipsIskDestroyed;
	var $shipsIskLost;
	var $capsuleIskDestroyed;
	var $capsuleIskLost;
	var $structureIskDestroyed;
	var $structureIskLost;

	var $pointsDestroyed;
	var $pointsLost;
	var $pointsPercentage;

	function __construct(
					$shipsDestroyed = 0, $shipsLost = 0,
					$capsuleDestroyed = 0, $capsuleLost = 0,
					$structureDestroyed = 0, $structureLost = 0,
					$shipsIskDestroyed = 0, $shipsIskLost = 0,
					$capsuleIskDestroyed = 0, $capsuleIskLost = 0,
					$structureIskDestroyed = 0, $structureIskLost = 0,
					$pointsDestroyed = 0, $pointsLost = 0
	) {
		$this->shipsDestroyed = (int) $shipsDestroyed;
		$this->shipsLost = (int) $shipsLost;
		$this->capsuleDestroyed = (int) $capsuleDestroyed;
		$this->capsuleLost = (int) $capsuleLost;
		$this->structureDestroyed = (int) $structureDestroyed;
		$this->structureLost = (int) $structureLost;

		$this->allTypesDestroyed = $this->shipsDestroyed + $this->capsuleDestroyed + $this->structureDestroyed;
		$this->allTypesLost = $this->shipsLost + $this->capsuleLost + $this->structureLost;


		$this->shipsIskDestroyed = (int) $shipsIskDestroyed;
		$this->shipsIskLost = (int) $shipsIskLost;
		$this->capsuleIskDestroyed = (int) $capsuleIskDestroyed;
		$this->capsuleIskLost = (int) $capsuleIskLost;
		$this->structureIskDestroyed = (int) $structureIskDestroyed;
		$this->structureIskLost = (int) $structureIskLost;

		$this->iskDestroyed = $this->shipsIskDestroyed + $this->capsuleIskDestroyed + $this->structureIskDestroyed;
		$this->iskLost = $this->shipsIskLost + $this->capsuleIskLost + $this->structureIskLost;

		$tmp = $this->iskDestroyed + $this->iskLost;
		$this->iskPercentage = $tmp == 0 ? 0 : $this->iskDestroyed / $tmp;


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
					$row->shipsDestroyed, $row->shipsLost,
					$row->capsuleDestroyed, $row->capsuleLost,
					$row->structureDestroyed, $row->structureLost,
					$row->shipsIskDestroyed, $row->shipsIskLost,
					$row->capsuleIskDestroyed, $row->capsuleIskLost,
					$row->structureIskDestroyed, $row->structureIskLost,
					$row->pointsDestroyed, $row->pointsLost
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
		$pointsDestroyed = empty( $json->pointsDestroyed ) ? 0 : (int) $json->pointsDestroyed;
		$pointsLost = empty( $json->pointsLost ) ? 0 : (int) $json->pointsLost;

		$zKiskDestroyed = empty( $json->iskDestroyed ) ? 0 : (int) $json->iskDestroyed;
		$zKiskLost = empty( $json->iskLost ) ? 0 : (int) $json->iskLost;
		$zKshipsDestroyed = empty( $json->shipsDestroyed ) ? 0 : (int) $json->shipsDestroyed;
		$zKshipsLost = empty( $json->shipsLost ) ? 0 : (int) $json->shipsLost;

		$shipsDestroyed = 0;
		$shipsLost = 0;
		$capsuleDestroyed = 0;
		$capsuleLost = 0;
		$structureDestroyed = 0;
		$structureLost = 0;

		$shipsIskDestroyed = 0;
		$shipsIskLost = 0;
		$capsuleIskDestroyed = 0;
		$capsuleIskLost = 0;
		$structureIskDestroyed = 0;
		$structureIskLost = 0;

		$topDestroyed = array();
		if ( !empty( $json->groups ) ) {
			foreach ( $json->groups as $key => $value ) {
				$groupID = (int) $key;
				$categoryID = $mysqli->query( "SELECT categoryID FROM evedump.invGroups WHERE groupID=$groupID" )->fetch_object()->categoryID;

				if ( $categoryID == 23 || // Starbase
							$categoryID == 40 || // Sovereignty Structures
							$categoryID == 22 ) { // Deployable (z.B. MTU)
					$structureDestroyed += empty( $value->shipsDestroyed ) ? 0 : $value->shipsDestroyed;
					$structureLost += empty( $value->shipsLost ) ? 0 : $value->shipsLost;
					$structureIskDestroyed += empty( $value->iskDestroyed ) ? 0 : $value->iskDestroyed;
					$structureIskLost += empty( $value->iskLost ) ? 0 : $value->iskLost;
					continue;
				} elseif ( $groupID == 29 ) {
					$capsuleDestroyed += empty( $value->shipsDestroyed ) ? 0 : $value->shipsDestroyed;
					$capsuleLost += empty( $value->shipsLost ) ? 0 : $value->shipsLost;
					$capsuleIskDestroyed += empty( $value->iskDestroyed ) ? 0 : $value->iskDestroyed;
					$capsuleIskLost += empty( $value->iskLost ) ? 0 : $value->iskLost;
					continue;
				} else {
					$shipsDestroyed += empty( $value->shipsDestroyed ) ? 0 : $value->shipsDestroyed;
					$shipsLost += empty( $value->shipsLost ) ? 0 : $value->shipsLost;
					$shipsIskDestroyed += empty( $value->iskDestroyed ) ? 0 : $value->iskDestroyed;
					$shipsIskLost += empty( $value->iskLost ) ? 0 : $value->iskLost;
				}

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
			shipsDestroyed, shipsLost,
			capsuleDestroyed, capsuleLost,
			structureDestroyed, structureLost,
			shipsIskDestroyed, shipsIskLost,
			capsuleIskDestroyed, capsuleIskLost,
			structureIskDestroyed, structureIskLost,
			pointsDestroyed, pointsLost,
			top1DestroyedGroupID, top1DestroyedCount,
			top2DestroyedGroupID, top2DestroyedCount,
			top3DestroyedGroupID, top3DestroyedCount,
			cachedUntil)
		VALUES (
			'$characterID',
			'$shipsDestroyed', '$shipsLost',
			'$capsuleDestroyed', '$capsuleLost',
			'$structureDestroyed', '$structureLost',
			'$shipsIskDestroyed', '$shipsIskLost',
			'$capsuleIskDestroyed', '$capsuleIskLost',
			'$structureIskDestroyed', '$structureIskLost',
			'$pointsDestroyed', '$pointsLost',
			'" . $topDestroyed[ 0 ][ 'groupID' ] . "', '" . $topDestroyed[ 0 ][ 'shipsDestroyed' ] . "',
			'" . $topDestroyed[ 1 ][ 'groupID' ] . "', '" . $topDestroyed[ 1 ][ 'shipsDestroyed' ] . "',
			'" . $topDestroyed[ 2 ][ 'groupID' ] . "', '" . $topDestroyed[ 2 ][ 'shipsDestroyed' ] . "',
			'$cachedUntil')
		ON DUPLICATE KEY
		UPDATE
			shipsDestroyed='$shipsDestroyed', shipsLost='$shipsLost',
			capsuleDestroyed='$capsuleDestroyed', capsuleLost='$capsuleLost',
			structureDestroyed='$structureDestroyed', structureLost='$structureLost',
			shipsIskDestroyed='$shipsIskDestroyed', shipsIskLost='$shipsIskLost',
			capsuleIskDestroyed='$capsuleIskDestroyed', capsuleIskLost='$capsuleIskLost',
			structureIskDestroyed='$structureIskDestroyed', structureIskLost='$structureIskLost',
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
			new zKillboardDestructionSet(
				$shipsDestroyed, $shipsLost,
				$capsuleDestroyed, $capsuleLost,
				$structureDestroyed, $structureLost,
				$shipsIskDestroyed, $shipsIskLost,
				$capsuleIskDestroyed, $capsuleIskLost,
				$structureIskDestroyed, $structureIskLost,
				$pointsDestroyed, $pointsLost
			),
			new zKillboardTopDestroyed( $topDestroyed[ 0 ][ 'groupID' ], $topDestroyed[ 0 ][ 'shipsDestroyed' ] ),
			new zKillboardTopDestroyed( $topDestroyed[ 1 ][ 'groupID' ], $topDestroyed[ 1 ][ 'shipsDestroyed' ] ),
			new zKillboardTopDestroyed( $topDestroyed[ 2 ][ 'groupID' ], $topDestroyed[ 2 ][ 'shipsDestroyed' ] )
		);
	}
}

?>
