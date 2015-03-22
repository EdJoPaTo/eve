<?php
	function isigb() {
		if (!empty($_SERVER['HTTP_EVE_TRUSTED']) && $_SERVER['HTTP_EVE_TRUSTED'] == "Yes")
			return 'trusted';
		else if (substr($_SERVER['HTTP_USER_AGENT'], -strlen("EVE-IGB")) === "EVE-IGB")
			return 'igb';
		else
			return false;
	}
	function formatprice($price)
	{
		return number_format($price, 2, ",", ".");
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
	function getprice($id, $systemid, $pricetype)
	{
		$query="SELECT * FROM eve.prices WHERE id=$id and systemid=$systemid";
		$result=mysql_query($query);
		$num=mysql_numrows($result);

		if ($num == 0)
		{
			$price = 0;
			$query = "INSERT INTO eve.prices VALUES ($id,$systemid,$price,$price,0)";
			mysql_query($query);
			$GLOBALS['updated'] = 0;
		}
		elseif ($num == 1)
		{
			if ($pricetype == 'bestcase') {
				$buyprice = mysql_result($result, 0, 'buy');
				$sellprice = mysql_result($result, 0, 'sell');
				$price = max($buyprice, $sellprice);
			} else {
				$price = mysql_result($result, 0, $pricetype);
			}
			$GLOBALS['updated'] = min($GLOBALS['updated'], mysql_result($result, 0, 'stamp'));
		}
		else
		{
			die('Error: Multiple Prices - Except only one');
		}

		return $price;
	}
	function getbestknowprice($id, $pricetype)
	{
		if ($pricetype == 'bestcase') {
			$buyprice = getbestknowprice($id, 'buy');
			$sellprice = getbestknowprice($id, 'sell');
			$price = max($buyprice, $sellprice);
		} else {
			$query="SELECT max(".$pricetype.") FROM eve.prices WHERE id='".$id."'";
			$result=mysql_query($query);
			$num=mysql_numrows($result);

			$price = mysql_result($result, 0, 'max('.$pricetype.')');
		}

		return $price;
	}
	function getrefinedprice($id, $systemid, $pricetype) {
		$price = 0;
		$query = "SELECT * FROM evedump.invTypeMaterials WHERE typeID=$id";
		$result = mysql_query($query);
		echo mysql_error();
		$num = mysql_num_rows($result);
		for ($i = 0; $i < $num; $i++) {
			$typeID = mysql_result($result, $i, 'materialTypeID');
			$quantity = mysql_result($result, $i, 'quantity');
			$curprice = getprice($typeID, $systemid, $pricetype);
			$price += $curprice * $quantity;
		}
		$portionSize = mysql_result(mysql_query("SELECT * FROM evedump.invTypes WHERE typeID=$id"), 0, 'portionSize');
		echo mysql_error();
		return $price / $portionSize;
	}
	function getcompressedid($id) {
		$price = 0;
		$query = "SELECT * FROM evedump.invTypes WHERE typeID=$id";
		$result = mysql_query($query);
		echo mysql_error();
		$num = mysql_num_rows($result);
		if ($num != 1)
			throw new Exception ( "Item not found", 0, NULL );

		$typeName = mysql_result($result, 0, 'typeName');
		$portionSize = mysql_result($result, 0, 'portionSize');

		$query = "SELECT * FROM evedump.invTypes WHERE typeName LIKE 'compressed ".$typeName."'";
		$result = mysql_query($query);
		echo mysql_error();
		$num = mysql_num_rows($result);
		if ($num != 1)
			throw new Exception ( "no compressed form found", 0, NULL );
		$compressedID = mysql_result($result, 0, 'typeID');

		return $compressedID;
	}
	function getcompressedprice($id, $systemid, $pricetype) {
		$compressedID = getcompressedid($id);
		$price = getprice($compressedID, $systemid, $pricetype);
		$portionSize = mysql_result(mysql_query("SELECT * FROM evedump.invTypes WHERE typeID=$id"), 0, 'portionSize');
		return $price / $portionSize;
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

			// Build the URL query string.
			$url = sprintf ( "%s?%s", $url, http_build_query ( $data ) );
		}

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
	function loginwithapi() {
		$characterID = !empty($_GET['characterID']) ? (int)htmlspecialchars($_GET['characterID']) : 0;
		$keyID = !empty($_GET['keyID']) ? (int)htmlspecialchars($_GET['keyID']) : (int)$keyID;
		$vCode = !empty($_GET['vCode']) ? htmlspecialchars($_GET['vCode']) : $vCode;

		$requiredAPIAccessMask = 33554435;

		if (!is_numeric($keyID) || $keyID == 0 || $vCode == "") {
			echo "Please insert your api key.<br><br>\n\n";

			echo '<form action="'.$_SERVER['PHP_SELF'].'" name="args" method="get">';
			echo '<table class="invis">'."\n";
			echo '<tr><td>Key ID:</td><td><input name="keyID" type="textbox" value="'.$keyID.'" size="7" /> from <a href="https://api.eveonline.com/" class="external" target="_blank">api.eveonline.com</a></td></tr>'."\n";
			echo '<tr><td>Verification Code:</td><td><input name="vCode" type="textbox" value="'.$vCode.'" size="80" /></td></tr>'."\n";
			echo '<tr><td></td><td><input type="submit" value="Get Chars"/></td></tr>'."\n";
			echo "</table>\n";
			echo '</form><br>'."\n";
			echo 'Or create an API first: ';
			echo '<a target="_blank" class="external" href="https://support.eveonline.com/api/Key/CreatePredefined/'.$requiredAPIAccessMask.'">Basic API</a> ';
			echo '<a target="_blank" class="external" href="https://support.eveonline.com/api/Key/CreatePredefined/268435455">Everything API</a>&nbsp;<br>';
		} else {

			$host = 'localhost';
			$user = 'eto';
			$password = 'eto';
			$database = 'eve';

			$conn = mysql_connect($host,$user,$password) or die('Error: Could not connect to database - '.mysql_error());
			mysql_select_db($database,$conn) or die('Error in selecting the database: '.mysql_error());

			$query = "SELECT accessMask, cachedUntil FROM api WHERE keyID=$keyID";
			$result = mysql_query($query);
			$accessMask = mysql_result($result, 0, 'accessMask');
			$cachedUntil = mysql_result($result, 0, 'cachedUntil');

			if ($cachedUntil != 0 && ($accessMask & $requiredAPIAccessMask) != $requiredAPIAccessMask) {
				echo '<span style="color:red;">This API has not enough access to your account in order to function properly with this service.<br>
				You should generate and use a new one with this service: ';
				echo '<a target="_blank" class="external" href="https://support.eveonline.com/api/Key/CreatePredefined/'.$requiredAPIAccessMask.'">Basic API</a>';
				echo '</span><br><br>'."\n\n";
			}

			if (!is_numeric($characterID) || $characterID == 0) {
				$xml = callAPI("account/Characters", array('keyID' => $keyID, 'vCode' => $vCode));
				echo "Choose your Character:<br>\n";
				echo '<table class="invis" style="width:100%;text-align:center;"><tr>'."\n";
				foreach ($xml->xpath('/eveapi/result/rowset/row') as $row) {
					$characterID = $row->attributes()->characterID;
					$name = $row->attributes()->name;
					$corp = $row->attributes()->corporationName;
					$alli = $row->attributes()->allianceName;

					echo '<td>';
					echo '<a href="'."?keyID=$keyID&amp;vCode=$vCode&amp;characterID=$characterID".'">';
					echo '<img src="//image.eveonline.com/Character/'.$characterID.'_256.jpg"><br>';
					echo "$name<br>$corp<br>$alli</a></td>\n";
				}
				echo '</tr></table>';
			} else {
				$query = "SELECT * FROM api WHERE characterID=$characterID";
				$result= mysql_query($query);
				echo mysql_error();
				$num = mysql_numrows($result);
				if ($num > 0) {
					if ($keyID != mysql_result($result, 0, 'keyID') || $vCode != mysql_result($result, 0, 'vCode')) {
						$query = "DELETE FROM api WHERE characterID=$characterID";
						mysql_query($query);
						$num = 0;
					}
				}
				if ($num == 0) {
					$query = "INSERT INTO api (characterID, keyID, vCode) VALUES (".$characterID.",".$keyID.",'".mysql_real_escape_string($vCode)."')";
					mysql_query($query);
				}
			}
			mysql_close();
		}
		return;
	}
?>

