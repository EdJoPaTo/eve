<?php

class Pilot {
  var $characterID;
  var $characterName;
  var $corporationID;
  var $corporationName;
  var $allianceID;
  var $allianceName;
  var $factionID;
  var $factionName;
  var $zKillboardCharacterStats;

  function __construct( $characterID = 0, $characterName = "", $corporationID = 0, $corporationName = "", $allianceID = 0, $allianceName = "", $factionID = 0, $factionName = "" ) {
    $this->characterID = (int) $characterID;
    $this->characterName = $characterName;
    $this->corporationID = (int) $corporationID;
    $this->corporationName = $corporationName;
    $this->allianceID = (int) $allianceID;
    $this->allianceName = $allianceName;
    $this->factionID = (int) $factionID;
    $this->factionName = $factionName;
  }

  public function getKillboardCharacterStats( ) {
    require_once 'zKillboardCharacterStats.php';

    if ( empty( $this->zKillboardCharacterStats ) ) {
      $this->zKillboardCharacterStats = zKillboardCharacterStats::getKillboardFromID( $this->characterID );
    }

    return $this->zKillboardCharacterStats;
  }

  public static function getIDsOfIngameCopyPaste( $text ) {
    $names = array();

    $splitted = explode("\n", $text);

    foreach ($splitted as $line) {
      $trimmed = trim($line, " \t\n\r");
      if ($trimmed == "")
        continue;

      $names[] = $trimmed;
    }

    return Pilot::getIDsOfNames( $names );
  }

  public static function getIDsOfNames( $names ) {
    global $mysqli;
    require_once 'mysqlDetails.php';
    require_once 'evefunctions.php';

    if ( !is_array( $names ) )
      $names = array( $names );

    $namesleft = array( );
    $returnArray = array();

    $prepare = $mysqli->prepare( "SELECT characterID FROM eve.characters WHERE characterName LIKE ?" );

    // Gucken ob Namen bereits in der Datenbank sind
    foreach ( $names as $currName ) {
      $prepare->bind_param( "s", $currName );
      $prepare->execute( );
      $prepare->bind_result( $currID );

      if ( $prepare->fetch( ) ) {
        $returnArray[] = $currID;
      } else {
        $namesleft[] = $currName;
      }
    }
    $prepare->close();

    // Noch fehlende Namen von der API holen
    if ( count( $namesleft ) > 0 ) {
      $namesleft = array_slice( $namesleft, 0, 100 );
      $xml = callAPI("eve/CharacterID", array( 'names' => implode( ",", $namesleft ) ) );
      $cachedUntil = strtotime( $xml->xpath( '/eveapi/cachedUntil' )[ 0 ] );

      foreach ( $xml->xpath( '/eveapi/result/rowset/row' ) as $row ) {
        $currName = $row->attributes( )->name;
        $currID = (int) $row->attributes( )->characterID;

        if ( $currID == 0 )
          continue;

        $returnArray[] = $currID;

        $prepare = $mysqli->prepare( "INSERT INTO eve.characters (characterID, characterName) VALUES (?,?)" );
        $prepare->bind_param( "is", $currID, $currName );
        $prepare->execute( );
      }
    }

    return $returnArray;
  }

  public static function getPilotsOfIDs( $ids ) {
    global $mysqli;
    require_once 'mysqlDetails.php';
    require_once 'evefunctions.php';

    if ( is_numeric( $ids ) )
      $names = array( $ids );

    $idsleft = array();
    $returnArray = array();

    foreach ( $ids as $id ) {
      if ( !is_numeric( $id ) ) {
        continue;
      }

      $result = $mysqli->query( "SELECT * FROM eve.characters WHERE characterID=$id" );
      if ( ( $row = $result->fetch_object( ) ) && ( (int) $row->cachedUntil > time() ) ) {
        $returnArray[] = new Pilot(
          $row->characterID,
          $row->characterName,
          $row->corporationID,
          $row->corporationName,
          $row->allianceID,
          $row->allianceName,
          $row->factionID,
          $row->factionName
        );
      } else {
        $idsleft[] = $id;
      }
    }

    if ( count( $idsleft ) > 0 ) {
      $idsleft = array_slice( $idsleft, 0, 150 );
      $xml = callAPI("eve/CharacterAffiliation", array( 'ids' => implode( ",", $idsleft ) ) );
      date_default_timezone_set("UTC");
  		$cachedUntil = strtotime( $xml->xpath( '/eveapi/cachedUntil' )[ 0 ] );

      foreach ( $xml->xpath( '/eveapi/result/rowset/row' ) as $row ) {
        $curCharID = $row->attributes( )->characterID;
        $curCharName = $row->attributes( )->characterName;
        $curCorpID = $row->attributes( )->corporationID;
        $curCorpName = $row->attributes( )->corporationName;
        $curAlliID = $row->attributes( )->allianceID;
        $curAlliName = $row->attributes( )->allianceName;
        $curFactionID = $row->attributes( )->factionID;
        $curFactionName = $row->attributes( )->factionName;

        if ( $curCorpID == 0 )
          continue;

        $returnArray[] = new Pilot(
          $curCharID,
          $curCharName,
          $curCorpID,
          $curCorpName,
          $curAlliID,
          $curAlliName,
          $curFactionID,
          $curFactionName
        );

        $query = "INSERT INTO eve.characters (characterID, characterName, corporationID, corporationName, allianceID, allianceName, factionID, factionName, cachedUntil)
        VALUES ('$curCharID', '" . $mysqli->real_escape_string( $curCharName ) . "', '$curCorpID', '" . $mysqli->real_escape_string( $curCorpName ) . "', '$curAlliID', '" . $mysqli->real_escape_string( $curAlliName ) . "', '$curFactionID', '" . $mysqli->real_escape_string( $curFactionName ) . "', '$cachedUntil')
        ON DUPLICATE KEY UPDATE characterName='" . $mysqli->real_escape_string( $curCharName ) . "', corporationID='$curCorpID', corporationName='" . $mysqli->real_escape_string( $curCorpName ) . "', allianceID='$curAlliID', allianceName='" . $mysqli->real_escape_string( $curAlliName ) . "', factionID='$curFactionID', factionName='" . $mysqli->real_escape_string( $curFactionName ) . "', cachedUntil='$cachedUntil'";
        $mysqli->query( $query );
        if ( $mysqli->error )
          echo $mysqli->error . "<br>\n";
      }
    }

    return $returnArray;
  }

  public function toString( ) {
    $returnString = "";

    $returnString .= $this->characterID . " - " . $this->characterName . "\n";
    $returnString .= "\t" . $this->corporationName . "\n";
    if ( $this->allianceID != 0 )
      $returnString .= "\t" . $this->allianceName . "\n";
    if ( $this->factionID != 0 )
      $returnString .= "\t" . $this->factionName . "\n";

    return $returnString;
  }

  public function toIconDiv( $size ) {
    $returnString = "";

    $returnString .= '<div class="charicon" style="background-image: url(//image.eveonline.com/Character/' . $this->characterID . '_' . $size . '.jpg);">';
    $returnString .= '<div class="corpicon" style="background-image: url(//image.eveonline.com/Corporation/' . $this->corporationID . '_' . $size/4 . '.png);"></div>';
    if ( $this-> allianceID != 0 )
      $returnString .= '<div class="allianceicon" style="background-image: url(//image.eveonline.com/Alliance/' . $this->allianceID . '_' . $size/4 . '.png);"></div>';
    $returnString .= '</div>';

    return $returnString;
  }
}

?>
