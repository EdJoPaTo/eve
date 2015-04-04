<?php

class ItemStack {
	var $items;

	function __construct() {
		$this->items = array();
	}

	function addItem($typeID, $quantity = 1) {
		if (!isset($this->items[$typeID]))
			$this->items[$typeID] = 0;

		$this->items[$typeID] += $quantity;
	}

	function getVolume() {
		global $mysqli;
		require_once 'mysqlDetails.php';
		$sumVolume = 0;

		foreach ($this->items as $typeID => $quantity) {
			$query = "SELECT volume
			FROM evedump.invTypes
			WHERE typeID=$typeID";
			$volume = $mysqli->query($query)->fetch_object()->volume;
			$stackvolume = $quantity * $volume;

			$sumVolume += $stackvolume;
		}
		return $sumVolume;
	}

	function getPrice($systemID, $pricetype = 'bestcase') {
		require_once 'Prices.php';
		$sumPrice = 0;
		$updated = time();

		foreach ($this->items as $typeID => $quantity) {
			$prices = Prices::getFromID($typeID, $systemID);
			$price = $quantity * $prices->getPriceByType($pricetype)['price'];
			$updated = min($updated, $prices->updated);

			$sumPrice += $price;
		}

		return $sumPrice;
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
		require_once 'Prices.php';
		require_once 'mysqlDetails.php';
		$source = "";
		$sumVolume = 0;
		$sumPrice = 0;
		$updated = time();

		foreach ($this->items as $typeID => $quantity) {
			$query = "SELECT typeName, volume
			FROM evedump.invTypes
			WHERE typeID=$typeID";
			$result = $mysqli->query($query);
			$row = $result->fetch_object();
			$typeName = $row->typeName;
			$volume = $quantity * $row->volume;
			$result->close();

			$prices = Prices::getFromID($typeID, $systemID);
			$price = $quantity * $prices->maxprice;
			$updated = min($updated, $prices->updated);

			$sumVolume += $volume;
			$sumPrice += $price;

			$source .= $rowprefix.'<div class="iteminfo" style="background-image: url(//image.eveonline.com/Type/'.$typeID.'_64.png)">'."\n";
			$source .= $rowprefix."\t";
			$source .= round($quantity, 2)."x&nbsp;";
			$source .= "<strong>".$typeName."</strong><br>\n";
			$source .= $rowprefix."\t".formatvolume($volume).'&nbsp;m&sup3;<br>'."\n";
			$source .= $rowprefix."\t".formatprice($price).'&nbsp;ISK<br>'."\n";
			$source .= $rowprefix."</div>\n";
		}

		if (count($this->items) > 1) {
			$source .= $rowprefix."<hr>\n";
			$source .= $rowprefix.'<div class="iteminfo" style="background-image: url(//image.eveonline.com/Type/23_64.png)">'."\n";
			$source .= $rowprefix."\t<strong>Sum</strong><br>\n";
			$source .= $rowprefix."\t".formatvolume($sumVolume).'&nbsp;m&sup3;<br>'."\n";
			$source .= $rowprefix."\t".formatprice($sumPrice).'&nbsp;ISK<br>'."\n";
			$source .= $rowprefix."</div>\n";
		}

		$query = "SELECT solarSystemName
		FROM evedump.mapSolarSystems
		WHERE solarSystemID=$systemID";
		$result = $mysqli->query($query);
		$systemName = $result->fetch_object()->solarSystemName;
		$result->close();

		$source .= $rowprefix."<br>\n";
		$source .= $rowprefix."All prices from $systemName"."<br>\n";
		if ($updated == 0)
			$source .= '<div class="worstvalue">';
		$source .= 'updated: '.gmdate('d.m.Y H:i:s e', $updated)."<br>\n";
		if ($updated == 0)
			$source .= "</div>";

		return $source;
	}
}

?>
