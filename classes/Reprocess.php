<?php

require_once 'ItemStack.php';

class Reprocess {
	var $itemID;
	var $reprocessPercentage;
	var $quantity;

	var $batchSize;
	var $mineralStack;

	function __construct($itemID, $reprocessPercentage, $quantity = 100) {
		$this->itemID = $itemID;
		$this->reprocessPercentage = $reprocessPercentage;
		$this->quantity = $quantity;

		$this->mineralStack = new ItemStack();

		$batchSize = mysql_result(mysql_query("SELECT * FROM evedump.invTypes WHERE typeID=$itemID"), 0, 'portionSize');
		echo mysql_error();

		$result = mysql_query("SELECT * FROM evedump.invTypeMaterials WHERE typeID=$itemID");
		echo mysql_error();
		$num = mysql_num_rows($result);
		for ($i = 0; $i < $num; $i++) {
			$materialTypeID = mysql_result($result, $i, 'materialTypeID');
			$materialQuantity = mysql_result($result, $i, 'quantity');

			$this->mineralStack->addItem($materialTypeID, ($quantity / $batchSize) * floor($materialQuantity * $reprocessPercentage));
		}
	}

	public function getMouseoverField($systemID, $rowprefix = "", $pricetype = 'bestcase') {
		$source = "";

		$source .= $rowprefix.'<div class="hoverpricecontainer">'."\n";
		$source .= $this->toHtml($systemID, $rowprefix."\t", $pricetype);
		$source .= $rowprefix.'</div>'."\n";

		return $source;
	}

	public function toHtml($systemID, $rowprefix = "", $pricetype = 'bestcase') {
		$source = "";

		$query = "SELECT typeName, volume
		FROM evedump.invTypes
		WHERE typeID=$this->itemID";
		$result = mysql_query($query);
		$typeName = mysql_result($result, 0, 'typeName');
		$volume = $this->quantity * mysql_result($result, 0, 'volume');

		$prices = Prices::getFromID($this->itemID, $systemID);
		$price = $this->quantity * $prices->maxprice;


		$source .= $rowprefix.'<div class="iteminfo" style="background-image: url(//image.eveonline.com/Type/'.$this->itemID.'_64.png)">'."\n";
		$source .= $rowprefix."\t";
		$source .= round($this->quantity, 2)."x&nbsp;";
		$source .= "<strong>".$typeName."</strong><br>\n";
		$source .= $rowprefix."\t".formatvolume($volume).'&nbsp;m&sup3;<br>'."\n";
		$source .= $rowprefix."\t".formatprice($price).'&nbsp;ISK<br>'."\n";
		$source .= $rowprefix."</div>\n";

		$source .= $rowprefix.'<br>'."\n";
		$source .= $rowprefix.'<strong>Reprocessed&nbsp;('.round($this->reprocessPercentage* 100,1).'%)</strong>'."\n";
		$source .= $this->mineralStack->toHtml($systemID, $rowprefix, $pricetype);

		return $source;

	}
}

?>
