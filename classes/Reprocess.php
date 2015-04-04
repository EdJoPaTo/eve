<?php

require_once 'ItemStack.php';

class Reprocess {
	var $itemID;
	var $reprocessPercentage;
	var $quantity;

	var $batchSize;
	var $mineralStack;

	function __construct($itemID, $reprocessPercentage, $quantity = 100) {
		global $mysqli;
		require_once 'mysqlDetails.php';

		$this->itemID = $itemID;
		$this->reprocessPercentage = $reprocessPercentage;
		$this->quantity = $quantity;

		$this->mineralStack = new ItemStack();

		$batchSize = $mysqli->query("SELECT * FROM evedump.invTypes WHERE typeID=$itemID")->fetch_object()->portionSize;

		$result = $mysqli->query("SELECT * FROM evedump.invTypeMaterials WHERE typeID=$itemID");
		while ($row = $result->fetch_object()) {
			$materialTypeID = $row->materialTypeID;
			$materialQuantity = $row->quantity;

			$this->mineralStack->addItem($materialTypeID, floor(($quantity / $batchSize) * ($materialQuantity * $reprocessPercentage)));
		}
		$result->close();
	}

	public function getMouseoverField($systemID, $rowprefix = "", $pricetype = 'bestcase') {
		$source = "";

		$source .= $rowprefix.'<div class="hoverpricecontainer">'."\n";
		$source .= $this->toHtml($systemID, $rowprefix."\t", $pricetype);
		$source .= $rowprefix.'</div>'."\n";

		return $source;
	}

	public function toHtml($systemID, $rowprefix = "", $pricetype = 'bestcase') {
		global $mysqli;
		require_once 'mysqlDetails.php';
		$source = "";

		$query = "SELECT typeName, volume
		FROM evedump.invTypes
		WHERE typeID=$this->itemID";
		$result = $mysqli->query($query);
		$row = $result->fetch_object();
		$typeName = $row->typeName;
		$volume = $this->quantity * $row->volume;
		$result->close();

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
