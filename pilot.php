<?php
  require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
  require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
  require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Pilot.php';

  $title = "Pilot Analyzer";
  ?>
  <!DOCTYPE HTML>
  <html>
    <head>
  <?php echo getHead($title); ?>
      <style type="text/css">
        .iteminfo {
          background-position: 5px 2px;
        }
        .alliance {
          padding: 1px;
          padding-right: 5px;
          padding-left: 2px;
        }
        .alliancespacer {
          margin: 0px;
        }
        .alliance:nth-of-type(even), .corporation:nth-of-type(even) {
          background-color: rgba( 70, 70, 70, 0.3 );
        }
      </style>
    </head>
    <body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
  <?php echo getPageselection($title, '//image.eveonline.com/Type/22177_64.png'); ?>
      <div id="content">
<?php

      $pilotsText = !empty($_POST['pilots']) ? $_POST['pilots'] :
"Rell Silfani
Karnis Delvari
";
      $pilotsText = trim( $pilotsText, " \t\n\r" );
      $pilotsText = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $pilotsText );

          $time = microtime();
      $pilotIDs = Pilot::getIDsOfIngameCopyPaste( $pilotsText );
          $timeNameToPlayer = microtime() - $time; $time = microtime();
      $pilots = Pilot::getPilotsOfIDs( $pilotIDs );
          $timePlayerInfo = microtime() - $time; $time = microtime();

      $alliances = array();
      $corps = array();

      foreach ( $pilots as $pilot ) {
        $pilot->getKillboardCharacterStats();

        if ( empty( $corps[ $pilot->corporationID ] ) ) {
          $corps[ $pilot->corporationID ] = array();
          $corps[ $pilot->corporationID ][ 'count' ] = 0;
          $corps[ $pilot->corporationID ][ 'iskDestroyed' ] = 0;
          $corps[ $pilot->corporationID ][ 'iskLost' ] = 0;
          $corps[ $pilot->corporationID ][ 'iskDestroyedBest' ] = 0;
          $corps[ $pilot->corporationID ][ 'iskLostBest' ] = 0;
        }
        if ( empty( $alliances[ $pilot->allianceID ] ) && $pilot->allianceID != 0 ) {
          $alliances[ $pilot->allianceID ] = array();
          $alliances[ $pilot->allianceID ][ 'count' ] = 0;
          $alliances[ $pilot->allianceID ][ 'iskDestroyed' ] = 0;
          $alliances[ $pilot->allianceID ][ 'iskLost' ] = 0;
          $alliances[ $pilot->allianceID ][ 'iskDestroyedBest' ] = 0;
          $alliances[ $pilot->allianceID ][ 'iskLostBest' ] = 0;
        }

        $corps[ $pilot->corporationID ][ 'count' ] += 1;
        $corps[ $pilot->corporationID ][ 'iskDestroyed' ] += $pilot->zKillboardCharacterStats->iskDestroyed;
        $corps[ $pilot->corporationID ][ 'iskLost' ] += $pilot->zKillboardCharacterStats->iskLost;
        $corps[ $pilot->corporationID ][ 'iskDestroyedBest' ] = max( $corps[ $pilot->corporationID ][ 'iskDestroyedBest' ], $pilot->zKillboardCharacterStats->iskDestroyed);
        $corps[ $pilot->corporationID ][ 'iskLostBest' ] = max( $corps[ $pilot->corporationID ][ 'iskLostBest' ], $pilot->zKillboardCharacterStats->iskLost);
        if ( $pilot->allianceID != 0 ) {
          $alliances[ $pilot->allianceID ][ 'count' ] += 1;
          $alliances[ $pilot->allianceID ][ 'iskDestroyed' ] += $pilot->zKillboardCharacterStats->iskDestroyed;
          $alliances[ $pilot->allianceID ][ 'iskLost' ] += $pilot->zKillboardCharacterStats->iskLost;
          $alliances[ $pilot->allianceID ][ 'iskDestroyedBest' ] = max( $alliances[ $pilot->allianceID ][ 'iskDestroyedBest' ], $pilot->zKillboardCharacterStats->iskDestroyed);
          $alliances[ $pilot->allianceID ][ 'iskLostBest' ] = max( $alliances[ $pilot->allianceID ][ 'iskLostBest' ], $pilot->zKillboardCharacterStats->iskLost);
        }
      }
          $timeKillboard = microtime() - $time; $time = microtime();

      function cmp( $a, $b ) {
        global $alliances;
        global $corps;
        $value = 0;

        // Best destroyer of alli/ corp
        if ( $value == 0 ) {
          $aValue = $a->allianceID != 0 ? $alliances[ $a->allianceID ][ 'iskDestroyedBest' ] : $corps[ $a->corporationID ][ 'iskDestroyedBest' ];
          $bValue = $b->allianceID != 0 ? $alliances[ $b->allianceID ][ 'iskDestroyedBest' ] : $corps[ $b->corporationID ][ 'iskDestroyedBest' ];
          $tmp = $bValue - $aValue;
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }
        // Best destroyer of corp
        if ( $value == 0 ) {
          $aValue = $corps[ $a->corporationID ][ 'iskDestroyedBest' ];
          $bValue = $corps[ $b->corporationID ][ 'iskDestroyedBest' ];
          $tmp = $bValue - $aValue;
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }

        // Alliance Member Count
        if ( $value == 0 && $a->allianceID != 0 && $b->allianceID != 0) {
          $tmp = $alliances[ $b->allianceID ][ 'count' ] - $alliances[ $a->allianceID ][ 'count' ];
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }
        // Corp Member Count
        if ( $value == 0 ) {
          $tmp = $corps[ $b->corporationID ][ 'count' ] - $corps[ $a->corporationID ][ 'count' ];
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }
        // Player ISK destroyed
        if ( $value == 0 ) {
          $tmp = $b->zKillboardCharacterStats->iskDestroyed - $a->zKillboardCharacterStats->iskDestroyed;
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }
        // Player ISK Lost
        if ( $value == 0) {
          $tmp = $a->zKillboardCharacterStats->iskLost - $b->zKillboardCharacterStats->iskLost;
          $value = $tmp > 0 ? 1 : ( $tmp < 0 ? -1 : 0 );
        }
        if ( $value == 0) {
          $value = strcasecmp( $a->allianceName, $b->allianceName );
        }
        if ( $value == 0 ) {
          $value = strcasecmp( $a->corporationName, $b->corporationName );
        }
        if ( $value == 0 ) {
          $value = strcasecmp( $a->characterName, $b->characterName );
        }
        return $value;
      }
      usort( $pilots, "cmp" );

      echo "\t\t\t" . '<div class="table">' . "\n";

      echo "\t\t\t\t" . '<div class="cell smallonsmall">' . "\n";
      echo "\t\t\t\t\t" . '<div class="table">' . "\n";

      $lastAlli = -1;
      $lastCorp = -1;
      foreach ( $pilots as $pilot ) {
        if ($lastAlli == 0 && $lastCorp != $pilot->corporationID ) {
          $lastAlli = -2;
        }

        if ($lastAlli != $pilot->allianceID) {
          if ( $lastCorp != -1 ) {
            echo "\t\t\t\t\t\t\t\t\t" . "</div>\n";
            echo "\t\t\t\t\t\t\t\t" . "</div>\n";
          }
          if ( $lastAlli != -1 ) {
            echo "\t\t\t\t\t\t\t" . "</div>\n";
            echo "\t\t\t\t\t\t" . "</div>\n";
            echo '<hr class="alliancespacer">' . "\n";
          }
          echo "\t\t\t\t\t\t" . '<div class="alliance">' . "\n";
          if ( $pilot->allianceID == 0) {
            echo "\t\t\t\t\t\t\t" . '<div class="iteminfo">' . "\n";
          } else {
            echo "\t\t\t\t\t\t\t" . "<strong>";
            echo $pilot->allianceName;
            echo "</strong>";
            echo ' (' . $alliances[ $pilot->allianceID ][ 'count' ] . ')';
            echo "<br>\n";
            echo "\t\t\t\t\t\t\t" . '<div class="iteminfo" style="background-image: url(//image.eveonline.com/Alliance/' . $pilot->allianceID . '_64.png);)">' . "\n";
          }
          $lastAlli = $pilot->allianceID;
          $lastCorp = -1;
        }

        if ($lastCorp != $pilot->corporationID) {
          if ( $lastCorp != -1 ) {
            echo "\t\t\t\t\t\t\t\t\t" . "</div>\n";
            echo "\t\t\t\t\t\t\t\t" . "</div>\n";
          }
          echo "\t\t\t\t\t\t\t\t" . '<div class="corporation">' . "\n";
          echo "\t\t\t\t\t\t\t\t\t" . "<strong>";
          echo $pilot->corporationName;
          echo "</strong>";
          echo ' (' . $corps[ $pilot->corporationID ][ 'count' ] . ')';
          echo "<br>\n";
          echo "\t\t\t\t\t\t\t\t\t" . '<div class="iteminfo" style="background-image: url(//image.eveonline.com/Corporation/' . $pilot->corporationID . '_64.png);)">' . "\n";
          $lastCorp = $pilot->corporationID;
        }

        echo "\t\t\t\t\t\t\t\t\t\t" . '<div class="character iteminfo" style="background-image: url(//image.eveonline.com/Character/' . $pilot->characterID . '_64.jpg);)">' . "\n";
        echo "\t\t\t\t\t\t\t\t\t\t\t" . "<strong>" . $pilot->characterName . "</strong>" . "\n";
        echo "\t\t\t\t\t\t\t\t\t\t\t" . '<a href="https://zkillboard.com/character/' . $pilot->characterID . '/" target="_blank" class="external">zK</a>' . "<br>\n";
//        echo "\t\t\t\t\t\t\t\t\t" . $pilot->corporationName . "<br>\n";
        if ( $pilot->allianceID != 0 ) {
//          echo "\t\t\t\t\t\t\t\t\t" . $pilot->allianceName . "<br>\n";
        }

        echo "\t\t\t\t\t\t\t\t\t\t\t" . '<span style="color: green;">';
        echo formatpriceshort( $pilot->zKillboardCharacterStats->iskDestroyed ) . "&nbsp;ISK";
        echo '&nbsp;(' . formatpieces( $pilot->zKillboardCharacterStats->shipsDestroyed ) . '&nbsp;ships)';
        echo '&nbsp;destroyed</span>' . "<br>\n";
        echo "\t\t\t\t\t\t\t\t\t\t\t" . '<span style="color: red;">';
        echo formatpriceshort( $pilot->zKillboardCharacterStats->iskLost ) . "&nbsp;ISK";
        echo '&nbsp;(' . formatpieces( $pilot->zKillboardCharacterStats->shipsLost ) . '&nbsp;ships)';
        echo '&nbsp;lost</span>' . "<br>\n";

        echo "\t\t\t\t\t\t\t\t\t\t" . "</div>\n";
      }
      if ( $lastCorp != -1 ) {
        echo "\t\t\t\t\t\t\t\t\t" . "</div>\n";
        echo "\t\t\t\t\t\t\t\t" . "</div>\n";
      }
      if ( $lastAlli != -1 ) {
        echo "\t\t\t\t\t\t\t" . "</div>\n";
        echo "\t\t\t\t\t\t" . "</div>\n";
      }

      echo "\t\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t" . '<div class="cell">' . "\n";

      $lines = substr_count( $pilotsText, "\n" ) + 1;
      $pilotCount = count( $pilots );
      if ( $lines - $pilotCount > 0 ) {
        echo "\t\t\t\t\t" . '<span style="color: red;">Your List contains more lines than Pilots loaded. Some lines are invalid or a big amout of new pilots was added.</span>' . "<br><br>\n";
      }

//      echo "\t\t\t\t" . "query names to player IDs: " . round( $timeNameToPlayer * 1000, 2 ) . " ms<br>\n";
//      echo "\t\t\t\t" . "query pilot corporations: " . round( $timePlayerInfo * 1000, 2 ) . " ms<br>\n";
//      echo "\t\t\t\t" . "query pilot zKillboards: " . round( $timeKillboard * 1000, 2 ) . " ms<br>\n";

      echo "\t\t\t\t\t" . "You can copy pilots from chat member lists (like the local) by selecting them and using <code>Ctrl + C</code> key combination.<br><br>\n";
      echo "\t\t\t\t\t" . '<div class="cell" style="padding-left: 10px;">' . "\n";

      echo "\t\t\t\t\t\t" . '<form action="' . $_SERVER['REQUEST_URI'] . '" name="args" method="post">' . "\n";
      echo "\t\t\t\t\t\t\t" . '<input type="submit" value="Submit" /><br>' . "\n";
      echo "\t\t\t\t\t\t\t" . '<textarea name="pilots" cols="30" rows="' . ( $pilotCount + 10 ) . '">' . $pilotsText . '</textarea>' . "<br>\n";
      echo "\t\t\t\t\t\t\t" . '<input type="submit" value="Submit" />' . "\n";
      echo "\t\t\t\t\t\t\t<br><br>\n";
      echo "\t\t\t\t\t\t" . '</form>' . "\n";

      echo "\t\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t\t" . '<div class="cell" style="padding-left: 10px;">' . "\n";

      $scannedChars = $mysqli->query( "SELECT * FROM eve.characters" )->num_rows;
      $scannedCorps = $mysqli->query( "SELECT * FROM eve.characters GROUP BY corporationID" )->num_rows;
      $scannedAllis = $mysqli->query( "SELECT * FROM eve.characters WHERE allianceID != 0 GROUP BY allianceID" )->num_rows;

      echo "\t\t\t\t\t\t" . "<strong>Current Scan Stats</strong><br>\n";
      echo "\t\t\t\t\t\t" . $pilotCount . " pilots";
      if ( $lines > $pilotCount ) {
        echo " of " . $lines . " Input Lines";
      }
      echo "<br>\n";
      echo "\t\t\t\t\t\t" . count( $corps ) . " corporations" . "<br>\n";
      echo "\t\t\t\t\t\t" . count ( $alliances ) . " alliances" . "<br>\n";


      echo "\t\t\t\t\t\t" . "<br>\n";
      echo "\t\t\t\t\t\t" . "<strong>All Time Stats</strong><br>\n";
      echo "\t\t\t\t\t\t" . $scannedChars . " pilots" . "<br>\n";
      echo "\t\t\t\t\t\t" . $scannedCorps . " corporations" . "<br>\n";
      echo "\t\t\t\t\t\t" . $scannedAllis . " alliances" . "<br>\n";

      echo "\t\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t" . '</div>' . "\n";

      echo "\t\t\t" . '</div>' . "\n";

      $mysqli->close();
?>

<?php echo getFooter(); ?>
    </div>
  </body>
</html>
