<?php

class Prices {
	var $typeID;
	var $systemId;
	var $buy;
	var $sell;
	var $maxprice;
	var $updated;
	var $buyunits;
	var $sellunits;
	var $units;

	function __construct($typeID, $systemID, $buy, $sell, $updated = 0, $buyunits = 0, $sellunits = 0) {
		$this->typeID = $typeID;
		$this->systemID = $systemID;
		$this->buy = $buy;
		$this->sell = $sell;
		$this->maxprice = max($buy, $sell);
		$this->updated = $updated;
		$this->buyunits = $buyunits;
		$this->sellunits = $sellunits;
		$this->units = $buyunits + $sellunits;
	}

	public function getPriceByType($pricetype = 'bestcase') {
		$price = 0;
		$units = 0;

		if ($pricetype == 'buy') {
			$price = $this->buy;
			$units = $this->buyunits;
		} elseif ($pricetype == 'sell') {
			$price = $this->sell;
			$units = $this->sellunits;
		} else {
			$price = $this->maxprice;
			$units = $this->units;
		}

		return array('price' => $price, 'units' => $units);
	}

	public function getMouseoverField($amount = 1, $rowprefix = "") {
		$query = "SELECT typeName, volume
		FROM evedump.invTypes
		WHERE typeID=$this->typeID";
		$result = mysql_query($query);
		$typeName = mysql_result($result, 0, 'typeName');
		$volume = $amount * mysql_result($result, 0, 'volume');

		$query = "SELECT solarSystemName
		FROM evedump.mapSolarSystems
		WHERE solarSystemID=$this->systemID";
		$result = mysql_query($query);
		$systemName = mysql_result($result, 0, 'solarSystemName');

		$source = '';
		$source .= $rowprefix.'<div class="hoverpricecontainer">'."\n";
		$source .= $rowprefix."\t".'<div class="iteminfo" style="background-image: url(//image.eveonline.com/Type/'.$this->typeID.'_64.png)">'."\n";
		$source .= $rowprefix."\t\t";
		if ($amount != 1)
			$source .= round($amount, 2)."x&nbsp;";
		$source .= $typeName.'<br>'."\n";
		$source .= $rowprefix."\t\t".formatvolume($volume).'&nbsp;m&sup3;<br>'."\n";
		$source .= $rowprefix."\t"."</div>\n";

		$source .= $rowprefix."\t"."<strong>Accept Buyorder ($systemName)</strong><br>\n";
		if ($this->buyunits == 0)
			$source .= $rowprefix."\t".'<div class="worstvalue">'."\n";
		$source .= $rowprefix."\t".'Price: '.formatprice($amount * $this->buy).'&nbsp;ISK<br>'."\n";
		$source .= $rowprefix."\t".'Units: '.formatamount($this->buyunits).'&nbsp;Units<br>'."\n";
		if ($this->buyunits == 0)
			$source .= $rowprefix."\t"."</div>"."\n";

		$source .= $rowprefix."\t"."<strong>Place Sellorder ($systemName)</strong><br>"."\n";
		if ($this->sellunits == 0)
			$source .= $rowprefix."\t".'<div class="worstvalue">'."\n";
		$source .= $rowprefix."\t".'Price: '.formatprice($amount * $this->sell).'&nbsp;ISK<br>'."\n";
		$source .= $rowprefix."\t".'Units: '.formatamount($this->sellunits).'&nbsp;Units<br>'."\n";
		if ($this->sellunits == 0)
			$source .= $rowprefix."\t"."</div>"."\n";

		$source .= $rowprefix."\t"."<strong>Price Data Update ($systemName)</strong><br>"."\n";
		$source .= $rowprefix."\t";
		if ($this->updated == 0)
			$source .= '<div class="worstvalue">';
		$source .= gmdate('d.m.Y H:i:s e', $this->updated);
		if ($this->updated == 0)
			$source .= "</div>";
		$source .= '<br>'."\n";
		$source .= $rowprefix."</div>\n";

		return $source;
	}

	public static function getFromID($typeID, $systemID) {
		$query="SELECT * FROM eve.prices WHERE id=$typeID and systemid=$systemID";
		$result=mysql_query($query);
		$num=mysql_numrows($result);

		$buy = 0;
		$sell = 0;
		$buyunits = 0;
		$sellunits = 0;
		$updated = 0;

		if ($num == 0)
		{
			$query = "INSERT INTO eve.prices VALUES ($typeID,$systemID,0,0,0,0,0)";
			mysql_query($query);
		}
		elseif ($num == 1)
		{
			$buy = mysql_result($result, 0, 'buy');
			$sell = mysql_result($result, 0, 'sell');
			$buyunits = mysql_result($result, 0, 'buyunits');
			$sellunits = mysql_result($result, 0, 'sellunits');
			$updated = mysql_result($result, 0, 'stamp');
		}
		else
		{
			die('Error: Multiple Prices - Except only one');
		}

		return new Prices($typeID, $systemID, $buy, $sell, $updated, $buyunits, $sellunits);
	}

	public static function updatePricesOfIDs($systemID, $ids) {
		require_once 'Util.php';

		if (is_numeric($ids))
			$ids = array($ids);

		$url = 'https://eve-central.com/api/marketstat?usesystem='.$systemID.'&typeid='.implode(',',$ids);
		try {
			$source = Util::postData($url);
			$xml = simplexml_load_string($source);

			foreach($ids as $id) {
				$buy = (float) $xml->xpath('/evec_api/marketstat/type[@id='.$id.']/buy/percentile')[0];
				$sell = (float) $xml->xpath('/evec_api/marketstat/type[@id='.$id.']/sell/percentile')[0];
				$buyunits = (int) $xml->xpath('/evec_api/marketstat/type[@id='.$id.']/buy/volume')[0];
				$sellunits = (int) $xml->xpath('/evec_api/marketstat/type[@id='.$id.']/sell/volume')[0];

				echo "  item ".$id."\tbuy: ".$buy."\tsell: ".$sell."\tbuyunits: ".$buyunits."\tsellunits: ".$sellunits."\n";

				$stamp = time();
				$query = "INSERT INTO eve.prices (ID, systemID, buy, sell, buyunits, sellunits, stamp)
				VALUES ('$id', '$systemID', '$buy', '$sell', '$buyunits', '$sellunits', '$stamp')
				ON DUPLICATE KEY UPDATE buy='$buy',sell='$sell',buyunits='$buyunits',sellunits='$sellunits',stamp='$stamp'";
				mysql_query($query);
			}
		} catch (Exception $e) {
			echo "Error updateallprices: ".$e->getMessage()."\n";
		}
	}
}

?>
