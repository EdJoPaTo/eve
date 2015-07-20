<?php
	function isigb() {
		if (!empty($_SERVER['HTTP_EVE_TRUSTED']) && $_SERVER['HTTP_EVE_TRUSTED'] == "Yes")
			return 'trusted';
		else if (substr($_SERVER['HTTP_USER_AGENT'], -strlen("EVE-IGB")) === "EVE-IGB")
			return 'igb';
		else
			return false;
	}
	function formatpercent( $percent ) {
		return number_format( $percent * 100, 1, ",", "." ) . "%";
	}
	function formatprice($price)
	{
		return number_format($price, 2, ",", ".");
	}
	function formatpriceshort( $price ) {
		$exponent = 0;
		while ( $price > 1000 ) {
			$price /= 1000;
			$exponent += 3;
		}

		switch ($exponent) {
			case 15: $letter = 'p'; break; //Just in case...
			case 12: $letter = 't'; break;
			case  9: $letter = 'b'; break;
			case  6: $letter = 'm'; break;
			case  3: $letter = 'k'; break;
			case  0:
			default: $letter = ''; break;
		}

		return number_format($price, 2, ",", ".") . $letter;
	}
	function formatpieces($pieces)
	{
		return number_format((int)$pieces, 0, ",", ".");
	}
	function formatvolume($volume)
	{
		return number_format($volume, 2, ",", ".");
	}
	function formatamount($amount)
	{
		return number_format($amount, 0, ",", ".");
	}
	function gettypeidbyname($name) {
		global $mysqli;

		$prepare = $mysqli->prepare("SELECT typeID FROM evedump.invTypes WHERE typeName LIKE ?");
		$prepare->bind_param("s", $name);
		$prepare->execute();
		$prepare->bind_result($typeID);

		$prepare->fetch();

		return $typeID;
	}
	function getbestknowprice($id, $pricetype)
	{
		global $mysqli;

		if ($pricetype == 'bestcase') {
			$buyprice = getbestknowprice($id, 'buy');
			$sellprice = getbestknowprice($id, 'sell');
			$price = max($buyprice, $sellprice);
		} else {
			$query = "SELECT max(".$pricetype.") as price FROM eve.prices WHERE id='".$id."'";
			$result = $mysqli->query($query);
			$price = $result->fetch_object()->price;
			$result->close();
		}

		return $price;
	}
	function getcompressedid($id) {
		global $mysqli;
		$price = 0;
		$query = "SELECT * FROM evedump.invTypes WHERE typeID=$id";
		$result = $mysqli->query($query);
		if ($result->num_rows != 1)
			throw new Exception ( "Item not found", 0, NULL );

		$row = $result->fetch_object();
		$typeName = $row->typeName;
		$portionSize = $row->portionSize;
		$result->close();

		$query = "SELECT * FROM evedump.invTypes WHERE typeName LIKE 'compressed ".$typeName."'";
		$result = $mysqli->query($query);
		if ($result->num_rows != 1)
			throw new Exception ( "no compressed form found", 0, NULL );
		$compressedID = $result->fetch_object()->typeID;
		$result->close();

		return $compressedID;
	}
	function callAPI($api, array $data = array()) {
		$url = "https://api.eveonline.com/".$api.".xml.aspx";
		// Certain aspects of the API key require a keyID and vCode.
		// First we validate that such section has been called,
		// then make sure that the keyID and vCode have been provided before populating the query.
		if (preg_match ( '/(\/account\/)|(\/char\/)|(\/corp\/)/', $url )) {
			if (empty ( $data )) {
				throw new Exception ( "No API keyID or verification code have been provided", 0, NULL );
			} else if (empty ( $data ['keyID'] )) {
				throw new Exception ( "No API keyID has been provided", 0, NULL );
			} else if (empty ( $data ['vCode'] )) {
				throw new Exception ( "No API verification code has been provided", 0, NULL );
			}
		}
		// Build the URL query string.
		$url = sprintf ( "%s?%s", $url, http_build_query ( $data ) );

		$cu = curl_init($url);
		curl_setopt ( $cu, CURLOPT_URL, $url );
		curl_setopt ( $cu, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec($cu);
		//$response = file_get_contents($url);

		$errormsg = curl_error($cu);
//		echo "$url $errormsg\n";
		if (curl_errno($cu)) {
			curl_close($cu);
			throw new Exception($errormsg, 0, NULL);
		}
		curl_close($cu);

//		echo $response;

		$xml = new SimpleXMLElement($response);

		$error = $xml->xpath('/eveapi/error');
		if (count($error) > 0) {

			$errno = (int) $error[0]['code'];
			if ($errno != 200) {
				throw new Exception($error[0], 0, NULL);
			}
		}

		return $xml;
	}
	function callKillboardCharacterStats( $characterID ) {
		$url = "https://zkillboard.com/api/stats/characterID/".$characterID."/";

		$cu = curl_init($url);
		curl_setopt ( $cu, CURLOPT_URL, $url );
		curl_setopt ( $cu, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec($cu);
		//$response = file_get_contents($url);

		$errormsg = curl_error($cu);
//		echo "$url $errormsg\n";
		if (curl_errno($cu)) {
			curl_close($cu);
			throw new Exception($errormsg, 0, NULL);
		}
		curl_close($cu);

		$json = json_decode( $response );

		return $json;
	}
?>
