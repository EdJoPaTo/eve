<?php
/* zKillboard
 * Copyright (C) 2012-2013 EVE-KILL Team and EVSCO.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class OAuth
{
	public static function eveSSOLoginURL()
	{
		global $ssoServer, $ssoResponseType, $ssoRedirectURI, $ssoClientID, $ssoScope, $ssoState;
		return "{$ssoServer}/oauth/authorize?response_type={$ssoResponseType}&amp;redirect_uri={$ssoRedirectURI}&amp;client_id={$ssoClientID}&amp;scope={$ssoScope}&amp;state={$ssoState}";
	}
	public static function eveSSOLoginToken($code, $state)
	{
		global $ssoServer, $ssoSecret, $ssoClientID;
		$tokenURL = $ssoServer . "/oauth/token";
		$b64 = $ssoClientID . ":" . $ssoSecret;
		$base64 = base64_encode($b64);
		$header = array();
		$header[] = "Authorization: Basic {$base64}";
		$fields = array(
			"grant_type" => "authorization_code",
			"code" => $code
		);
		$data = Util::postData($tokenURL, $fields, $header);
		$data = json_decode($data);
		$accessToken = $data->access_token;
		self::eveSSOLoginVerify($accessToken);
	}
	public static function eveSSOLoginVerify($accessToken)
	{
		global $ssoServer;
		$verifyURL = $ssoServer . "/oauth/verify";
		$header = array();
		$header[] = "Authorization: Bearer {$accessToken}";
		$data = Util::postData($verifyURL, NULL, $header);
		self::eveSSOLogin($data);
	}
	public static function eveSSOLogin($data = NULL)
	{
		$data = json_decode($data);
		$_SESSION["characterName"] = $data->CharacterName;
		$_SESSION["characterID"] = (int) $data->CharacterID;
	}
}
