<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/myfunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/evefunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Prices.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemStack.php';

	$title = "Item Prices";
?>
<!DOCTYPE HTML>
<html>
	<head>
<?php echo getHead($title); ?>
		<style type="text/css">
			.table .cell {
				padding: 10px;
			}
		</style>
	</head>
	<body onload="CCPEVE.requestTrust('<?php echo "http://".$_SERVER['HTTP_HOST']; ?>')">
<?php echo getPageselection($title, '//image.eveonline.com/Type/23_64.png'); ?>
		<div id="content">
<?php

			$items = !empty($_POST['items']) ? $_POST['items'] :
"3.000  Veldspar
2  Logic Circuit
XR-3200 Heavy Missile Bay
Fried Interface Circuit  30  Salvaged Materials  0,30 m3
Power Circuit  2  Salvaged Materials  0,02 m3
Sisters Core Scanner Probe  8  Scanner Probe  0,80 m3
";

			$itemStack = ItemStack::fromIngameCopyPaste($items);

			echo "\t\t\t".'<div class="table">'."\n";

			echo "\t\t\t\t".'<div class="cell">'."\n";
			echo "\t\t\t\t\t"."<strong>input items</strong>";
			echo $itemStack->toHtml(30000142, "\t\t\t\t\t");
			echo "\t\t\t\t".'</div>'."\n";
			echo "\t\t\t\t".'<div class="cell">'."\n";
			echo "\t\t\t\t\t"."<strong>reprocess reprocessable items... (69,6%)</strong>";
			echo $itemStack->getReprocessedStack(0.69575)->toHtml(30000142, "\t\t\t\t\t");
			echo "\t\t\t\t".'</div>'."\n";
			echo "\t\t\t\t".'<div class="cell">'."\n";
			echo "\t\t\t\t\t".'<form action="'.$_SERVER['REQUEST_URI'].'" name="args" method="post">'."\n";
			echo "\t\t\t\t\t\tYou can copy items from ingame <b>Details</b> or <b>List</b> views by selecting wanted items and using <code>Ctrl + C</code> key combination.<br>Icons view does not have copy feature ingame.<br>\n";
			echo "\t\t\t\t\t\t".'<textarea name="items" cols="80" rows="15">'.$items.'</textarea>'."<br>\n";
			echo "\t\t\t\t\t\t".'<input type="submit" value="Submit" />'."\n";
			echo "\t\t\t\t\t".'</form>'."\n";
			echo "\t\t\t\t".'</div>'."\n";

			echo "\t\t\t".'</div>'."\n";

			$mysqli->close();
?>

<?php echo getFooter(); ?>
		</div>
	</body>
</html>
