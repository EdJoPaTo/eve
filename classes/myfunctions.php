<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	require_once 'Util.php';
	require_once 'OAuth.php';
	require_once 'ssoDetails.php';
	require_once 'mysqlDetails.php';

	session_start();
	if(isset($_SESSION['views']))
	    $_SESSION['views'] = $_SESSION['views'] + 1;
	else
	    $_SESSION['views'] = 1;

	function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}
	function endsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	function toBool($var)
	{
		if (!is_string($var)) return (bool) $var;
		switch (strtolower($var)) {
			case '1':
			case 'true':
			case 'on':
			case 'yes':
			case 'y':
				return true;
			default:
				return false;
		}
	}
	function runsAtiOS()
	{
		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"iphone") ||
			strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"ipad")) {
			if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"safari")) {
				return 'browser'; //Running in browser on iPhone
			} else {
				return 'webapp'; //Running as stand alone WebApp on iPhone
			}
		} else {
			return false; //Running on device other than iPhone
		}
	}
	function build_sorter($key, $desc = false) {
		if ($desc) {
			return function ($a, $b) use ($key) {
				return strnatcmp($a[$key], $b[$key]) * -1;
			};
		} else {
			return function ($a, $b) use ($key) {
				return strnatcmp($a[$key], $b[$key]);
			};
		}
	}
	function printmysqlselectquerytable($result)
	{
		print('<div class="table bordered hoverrow">'."\n");
		print('<div class="headrow">'."\n");
		while ($finfo = mysqli_fetch_field($result)) {
			print('<div class="cell">'.$finfo->name."</div>\n");
		}
		print("</div>\n");
		// print the body of the table
		while ($row = $result->fetch_array()) {
			print('<div class="row">'."\n");
			foreach ($row as $key => $value) {
				if (is_numeric($key))
					print('<div class="cell">'.$value."</div>\n");
			}
			print("</div>\n");
		}
		print("</div><br>\n");
	}
	function getHead($pageTitle)
	{
		$returnString = '';

		$returnString .= "\t\t<title>";
		$returnString .= $pageTitle;
		if ($pageTitle != $_SERVER['SERVER_NAME'])
		{
			$returnString .= ' - '.$_SERVER['SERVER_NAME'];
		}
		$returnString .= '</title>

		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
		<link rel="stylesheet" type="text/css" href="/main.css">
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<meta name="mobile-web-app-capable" content="yes">
		<meta name="format-detection" content="telephone=no">
		<meta name="theme-color" content="#FFB000">'."\n";
		if (runsAtiOS()) {
			$returnString .= '
		<meta name="apple-mobile-web-app-title" content="';
		$returnString .= $pageTitle;
		$returnString .= '">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<!-- ICONS -->

		<!-- iPad retina icon -->
		<link href="/res/ETO_Sk1M_152.png"
		      sizes="152x152"
		      rel="apple-touch-icon-precomposed">

		<!-- iPad retina icon (iOS < 7) -->
		<link href="/res/ETO_Sk1M_144.png"
		      sizes="144x144"
		      rel="apple-touch-icon-precomposed">

		<!-- iPad non-retina icon -->
		<link href="/res/ETO_Sk1M_76.png"
		      sizes="76x76"
		      rel="apple-touch-icon-precomposed">

		<!-- iPad non-retina icon (iOS < 7) -->
		<link href="/res/ETO_Sk1M_72.png"
		      sizes="72x72"
		      rel="apple-touch-icon-precomposed">

		<!-- iPhone 6 Plus icon -->
		<link href="/res/ETO_Sk1M_180.png"
		      sizes="120x120"
		      rel="apple-touch-icon-precomposed">

		<!-- iPhone retina icon (iOS < 7) -->
		<link href="/res/ETO_Sk1M_114.png"
		      sizes="114x114"
		      rel="apple-touch-icon-precomposed">

		<!-- iPhone non-retina icon (iOS < 7) -->
		<link href="/res/ETO_Sk1M_57.png"
		      sizes="57x57"
		      rel="apple-touch-icon-precomposed">

		<!-- STARTUP IMAGES -->

		<!-- iPad retina portrait startup image -->
		<link href="/res/ETO_Sk1M_1536x2008.png"
		      media="(device-width: 768px) and (device-height: 1024px)
		             and (-webkit-device-pixel-ratio: 2)
		             and (orientation: portrait)"
		      rel="apple-touch-startup-image">

		<!-- iPad retina landscape startup image -->
		<link href="/res/ETO_Sk1M_2048x1496.png"
		      media="(device-width: 768px) and (device-height: 1024px)
		             and (-webkit-device-pixel-ratio: 2)
		             and (orientation: landscape)"
		      rel="apple-touch-startup-image">

		<!-- iPad non-retina portrait startup image -->
		<link href="/res/ETO_Sk1M_768x1004.png"
		      media="(device-width: 768px) and (device-height: 1024px)
		             and (-webkit-device-pixel-ratio: 1)
		             and (orientation: portrait)"
		      rel="apple-touch-startup-image">

		<!-- iPad non-retina landscape startup image -->
		<link href="/res/ETO_Sk1M_1024x748.png"
		      media="(device-width: 768px) and (device-height: 1024px)
		             and (-webkit-device-pixel-ratio: 1)
		             and (orientation: landscape)"
		      rel="apple-touch-startup-image">

		<!-- iPhone 6 Plus portrait startup image -->
		<link href="/res/ETO_Sk1M_1242x2148.png"
		      media="(device-width: 414px) and (device-height: 736px)
		             and (-webkit-device-pixel-ratio: 3)
		             and (orientation: portrait)"
		      rel="apple-touch-startup-image">

		<!-- iPhone 6 Plus landscape startup image -->
		<link href="/res/ETO_Sk1M_2208x1182.png"
		      media="(device-width: 414px) and (device-height: 736px)
		             and (-webkit-device-pixel-ratio: 3)
		             and (orientation: landscape)"
		      rel="apple-touch-startup-image">

		<!-- iPhone 6 startup image -->
		<link href="/res/ETO_Sk1M_750x1294.png"
		      media="(device-width: 375px) and (device-height: 667px)
		             and (-webkit-device-pixel-ratio: 2)"
		      rel="apple-touch-startup-image">

		<!-- iPhone 5 startup image -->
		<link href="/res/ETO_Sk1M_640x1096.png"
		      media="(device-width: 320px) and (device-height: 568px)
		             and (-webkit-device-pixel-ratio: 2)"
		      rel="apple-touch-startup-image">

		<!-- iPhone < 5 retina startup image -->
		<link href="/res/ETO_Sk1M_640x920.png"
		      media="(device-width: 320px) and (device-height: 480px)
		             and (-webkit-device-pixel-ratio: 2)"
		      rel="apple-touch-startup-image">

		<!-- iPhone < 5 non-retina startup image -->
		<link href="/res/ETO_Sk1M_320x460.png"
		      media="(device-width: 320px) and (device-height: 480px)
		             and (-webkit-device-pixel-ratio: 1)"
		      rel="apple-touch-startup-image">
		<script type="text/javascript">
			var noddy, remotes = false;

			document.addEventListener(\'click\', function(event) {

			noddy = event.target;

			while(noddy.nodeName !== "A" && noddy.nodeName !== "HTML") {
			noddy = noddy.parentNode;
			}

			if(\'href\' in noddy && noddy.href.indexOf(\'http\') !== -1 && (noddy.href.indexOf(document.location.host) !== -1 || remotes))
			{
			event.preventDefault();
			document.location.href = noddy.href;
			}

			},false);
		</script>'."\n";
		}
		$update = 60;
		if (!empty($_GET["update"])) {
			$tmp = htmlspecialchars($_GET["update"]);
			if (is_numeric($tmp)) {$update = $tmp;}
			unset($tmp);
			$returnString .= '		<META HTTP-EQUIV="refresh" CONTENT="'.$update.'">'."\n";
		}
		if (!empty($_SESSION['characterID'])) {
			$returnString .= "\t\t".'<style type="text/css">
			.pilot64 { background-image: url(//image.eveonline.com/Character/'.$_SESSION['characterID'].'_64.jpg); }
			@media (-webkit-min-device-pixel-ratio: 1.5) {
				.pilot64 { background-image: url(//image.eveonline.com/Character/'.$_SESSION['characterID'].'_128.jpg); }
			}
			@media (-webkit-min-device-pixel-ratio: 3) {
				.pilot64 { background-image: url(//image.eveonline.com/Character/'.$_SESSION['characterID'].'_256.jpg); }
			}
		</style>'."\n";
		}

		return $returnString;
	}
	function getPageselection($title = '', $bgimage = '') {
		$returnString = "";
		$returnString .= "\t\t\t".'<div id="pageselection">'."\n";
		if ($_SERVER["PHP_SELF"] != "/index.php")
			$returnString .= "\t\t\t\t" . '<a href="/" class="wideonly">Overview</a>' . "\n";
//		if ($_SERVER["PHP_SELF"] != "/ore.php")
//			$returnString .= "\t\t\t\t".'<a class="wideonly img" style="background-image: url(//image.eveonline.com/Type/34_32.png);" href="/ore.php">Ore</a>'."\n";
//		if ($_SERVER["PHP_SELF"] != "/ice.php")
//			$returnString .= "\t\t\t\t".'<a class="wideonly img" style="background-image: url(//image.eveonline.com/Type/16265_32.png);" href="/ice.php">Ice</a>'."\n";
//		if ($_SERVER["PHP_SELF"] != "/item.php")
//			$returnString .= "\t\t\t\t".'<a class="wideonly img" style="background-image: url(//image.eveonline.com/Type/23_32.png);" href="/item.php">Item Prices</a>'."\n";
//		if ($_SERVER["PHP_SELF"] != "/planet.php")
//			$returnString .= "\t\t\t\t".'<a class="wideonly img" style="background-image: url(//image.eveonline.com/Type/2398_32.png);" href="/planet.php">PI Commodity</a>'."\n";
//		if (!empty($_SESSION['characterID'])) {
//			if ($_SERVER["PHP_SELF"] != "/api/planet.php")
//				$returnString .= "\t\t\t\t".'<a class="wideonly img" style="background-image: url(//image.eveonline.com/Type/2014_32.png);" href="/api/planet.php">PI Overview</a>'."\n";
//		}
		if (!empty($title)) {
			$returnString .= "\t\t\t\t" . '<div class="wideonly doublespacer"></div>' . "\n";
			$returnString .= "\t\t\t\t" . '<div class="wideonly title';
			if (!empty($bgimage))
				$returnString .= ' img" style="background-image: url('.$bgimage.'); padding-left: 75px;" ';
			$returnString .= '">'.$title.'</div>'."\n";
			$returnString .= "\t\t\t\t" . '<a href="/" class="smallonly title';
			if ( !empty( $bgimage ) )
				$returnString .= ' img" style="background-image: url('.$bgimage.'); padding-left: 75px;" ';
			$returnString .= '">' . $title . '</a>' . "\n";
			$returnString .= "\t\t\t\t" . '<div class="doublespacer"></div>' . "\n";
		} else {
			$returnString .= "\t\t\t\t".'<div class="singlespacer"></div>'."\n";
		}
/*		See you later :)
*/
		if (empty($_SESSION['characterID'])) {
			global $ssoServer, $ssoResponseType, $ssoRedirectURI, $ssoClientID, $ssoScope, $ssoState;
			$ssoState = "stuff";
			$returnString .= "\t\t\t\t".'<a style="background-image: url(//images.contentful.com/idjq7aai9ylm/4fSjj56uD6CYwYyus4KmES/4f6385c91e6de56274d99496e6adebab/EVE_SSO_Login_Buttons_Large_Black.png?w=270&amp;h=45); background-repeat: no-repeat; background-position: center center; min-width: 270px; width: 270px; height: 45px; padding: 0px 10px;" href="'.OAuth::eveSSOLoginURL().'"></a>'."\n";
		} else {
			$returnString .= "\t\t\t\t".'<div class="img pilot64" style="padding-left: 75px; background-size: 64px 64px;">'.$_SESSION['characterName']."</div>\n";
			$returnString .= "\t\t\t\t".'<a href="/eveauth.php">Settings</a>'."\n";
		}
/**/
		$returnString .= "\t\t\t".'</div>'."\n";
//		if (!empty($title)) {
//			$returnString .= "\t\t".'<h1 class="smallonly" style="margin: 0px 15px; margin-top: 75px;">';
//			if (!empty($bgimage))
//				$returnString .= '<img src="'.$bgimage.'">';
//			$returnString .= $title.'</h1>'."\n";
//		} else {
			$returnString .= "\t\t" . '<div class="smallonly" style="margin-top: 60px;"></div>';
//		}
		return $returnString;
	}
	function getFooter() {
		$returnString = "";
		$returnString .= "\t\t\t".'<div id="footer">'."\n";
		$returnString .= "\t\t\t\t<hr>\n";
		$returnString .= "\t\t\t\t".'<div id="footerright">'."\n";
		$returnString .= "\t\t\t\t\t".'<a href="/information/about.php">About</a><br>'."\n";
		$returnString .= "\t\t\t\t\t".'<a href="https://github.com/EdJoPaTo/eve" class="external" target="_blank">GitHub</a><br>'."\n";
		$timeneeded = (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000;
		$returnString .= "\t\t\t\t\tgenerated in ".round($timeneeded)." ms<br>\n";
		$returnString .= "\t\t\t\t".'</div>'."\n";
		$returnString .= "\t\t\t\t".'<div id="footerleft">'."\n";
		$returnString .= "\t\t\t\t\t".'All <a href="/information/legal.php">EVE related materials</a> are property of <a href="http://www.ccpgames.com" class="external">CCP Games</a><br>'."\n";
		$returnString .= "\t\t\t\t\t".'<a href="/information/legal.php">CCP Copyright Notice</a><br>'."\n";
		$returnString .= "\t\t\t\t\t".'german page, german "<a href="/information/impressum.php" class="footerLink">Impressum</a>"<br>'."\n";
		$returnString .= "\t\t\t\t".'</div>'."\n";
		if (!empty($_SERVER['HTTP_EVE_TRUSTED']))
		{
			$returnString .= '<iframe src="/igbtracker.php" height="0" width="0" frameborder="0"></iframe>';
		}
		$returnString .= "\t\t\t".'</div>'."\n";
		return $returnString;
	}
	$igbsave = array('HTTP_EVE_CHARID','HTTP_EVE_CHARNAME',
								'HTTP_EVE_CORPID','HTTP_EVE_CORPNAME',
								'HTTP_EVE_CORPTICKER',
								'HTTP_EVE_ALLIANCEID','HTTP_EVE_ALLIANCENAME',
								'HTTP_EVE_REGIONID','HTTP_EVE_REGIONNAME',
								'HTTP_EVE_CONSTELLATIONID','HTTP_EVE_CONSTELLATIONNAME',
								'HTTP_EVE_SOLARSYSTEMID','HTTP_EVE_SOLARSYSTEMNAME',
								'HTTP_EVE_SYSTEMSECURITY',
								'HTTP_EVE_STATIONID','HTTP_EVE_STATIONNAME',
								'HTTP_EVE_SHIPID','HTTP_EVE_SHIPNAME',
								'HTTP_EVE_SHIPTYPEID','HTTP_EVE_SHIPTYPENAME',
								'HTTP_EVE_CORPROLE',
								'HTTP_EVE_WARFACTIONID',
								'HTTP_EVE_MILITIAID','HTTP_EVE_MILITIANAME',
								'REMOTE_ADDR','REQUEST_TIME');
?>
