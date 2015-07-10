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
      </style>
    </head>
    <body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
  <?php echo getPageselection($title, '//image.eveonline.com/Type/23_64.png'); ?>
      <div id="content">
<?php

      $pilotsText = !empty($_POST['pilots']) ? $_POST['pilots'] :
"Rell Silfani
Karnis Delvari
Jatsu Enaka
drdready
Larissa Liao
Sternengecko
Lorianne Calmar
Shell Seeker
Evesham
Piir8
Penguin68
Tyr Dolorem
Quasi Vader
Serenety Steel
";

      $pilotIDs = Pilot::getIDsOfIngameCopyPaste( $pilotsText );
      $pilots = Pilot::getPilotsOfIDs( $pilotIDs );

      function cmp( $a, $b ) {
        $value = strcasecmp( $a->allianceName, $b->allianceName );
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

      echo "\t\t\t\t" . '<div class="cell" style="padding: 10px;">' . "\n";
      echo "\t\t\t\t\t" . '<div class="table">' . "\n";

      $lastAlli = -1;
      $lastCorp = -1;
      //TODO: Display Pilots
      foreach ( $pilots as $pilot ) {
        if ($lastAlli != $pilot->allianceID) {
          if ( $lastCorp != -1 ) {
            echo "\t\t\t\t\t\t\t" . "</div>\n";
          }
          if ( $lastAlli != -1 ) {
            echo "\t\t\t\t\t\t" . "</div>\n";
          }
          echo "\t\t\t\t\t\t" . "<strong>";
          if ( $pilot->allianceID == 0) {
            echo "Without Alliance";
          } else {
            echo $pilot->allianceName;
          }
          echo "</strong><br>\n";
          echo "\t\t\t\t\t\t" . '<div class="iteminfo" style="background-image: url(//image.eveonline.com/Alliance/' . $pilot->allianceID . '_128.png);)">' . "\n";
          $lastAlli = $pilot->allianceID;
          $lastCorp = -1;
        }

        if ($lastCorp != $pilot->corporationID) {
          if ( $lastCorp != -1 ) {
            echo "\t\t\t\t\t\t\t" . "</div>\n";
          }
          echo "\t\t\t\t\t\t\t" . "<strong>";
          echo $pilot->corporationName;
          echo "</strong><br>\n";
          echo "\t\t\t\t\t\t\t" . '<div class="iteminfo" style="background-image: url(//image.eveonline.com/Corporation/' . $pilot->corporationID . '_128.png);)">' . "\n";
          $lastCorp = $pilot->corporationID;
        }

        echo "\t\t\t\t\t\t\t\t" . '<div class="iteminfo" style="background-image: url(//image.eveonline.com/Character/' . $pilot->characterID . '_128.jpg);)">' . "\n";
        echo "\t\t\t\t\t\t\t\t\t" . $pilot->characterName . "<br>\n";
//        echo "\t\t\t\t\t\t\t\t\t" . $pilot->corporationName . "<br>\n";
        if ( $pilot->allianceID != 0 ) {
//          echo "\t\t\t\t\t\t\t\t\t" . $pilot->allianceName . "<br>\n";
        }
        echo "\t\t\t\t\t\t\t\t" . "</div>\n";
      }
      if ( $lastCorp != -1 ) {
        echo "\t\t\t\t\t\t\t" . "</div>\n";
      }
      if ( $lastAlli != -1 ) {
        echo "\t\t\t\t\t\t" . "</div>\n";
      }

      echo "\t\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t" . '</div>' . "\n";
      echo "\t\t\t\t" . '<div class="cell" style="padding: 10px;">' . "\n";
      echo "\t\t\t\t\t" . '<form action="' . $_SERVER['REQUEST_URI'] . '" name="args" method="post">' . "\n";
      echo "\t\t\t\t\t\tYou can copy pilots from chat member lists by selecting them and using <code>Ctrl + C</code> key combination.<br>\n";
      echo "\t\t\t\t\t\t" . '<input type="submit" value="Submit" /><br>' . "\n";
      echo "\t\t\t\t\t\t" . '<textarea name="pilots" cols="80" rows="40">' . $pilotsText . '</textarea>' . "<br>\n";
      echo "\t\t\t\t\t\t" . '<input type="submit" value="Submit" />' . "\n";
      echo "\t\t\t\t\t\t<br><br>\n";
      echo "\t\t\t\t\t" . '</form>' . "\n";

      echo "\t\t\t\t" . '</div>' . "\n";

      echo "\t\t\t" . '</div>' . "\n";

      $mysqli->close();
?>

<?php echo getFooter(); ?>
    </div>
  </body>
</html>
