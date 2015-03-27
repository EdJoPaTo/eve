<?php

class Util {

	/**
	 * zKillboard: https://github.com/3zLabs/zKillboard/blob/master/classes/Util.php
	 * @param string $url
	 * @param array
	 * @param array
	 * @return array $result
	 */
	public static function postData($url, $postData = array(), $headers = array())
	{
		$hostname = empty($_SERVER['SERVER_NAME']) ? gethostname() : $_SERVER['SERVER_NAME'];
		$userAgent = $hostname." - edjopato@gmail.com";
		if(!isset($headers))
			$headers = array("Connection: keep-alive", "Keep-Alive: timeout=10, max=1000");
		$curl = curl_init();
		$postLine = "";
		if(!empty($postData))
			foreach($postData as $key => $value)
				$postLine .= $key . "=" . $value . "&";
		rtrim($postLine, "&");
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if(!empty($postData))
		{
			curl_setopt($curl, CURLOPT_POST, count($postData));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postLine);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}
?>