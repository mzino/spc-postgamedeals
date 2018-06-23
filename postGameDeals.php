<?php

function sendPost($mess){
	$topic="608672";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.gamesforum.it/board/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIE,'bb_userid=196602; bb_password=cd4532ac32b760ba7f398be245559fc9'); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$data = curl_exec($ch);
	$trova = "var SECURITYTOKEN = \"";
	$pos = strpos($data, $trova) + strlen($trova);
	$data = substr($data, $pos);
	$pos = strpos($data, '"');
	$security_token = substr($data, 0, $pos);
	$stringozza = "message=$mess&wysiwyg=0&signature=1&sbutton=Invia+Risposta+Rapida&fromquickreply=1&s=&securitytoken=$security_token&do=postreply&t=$topic&p=who+cares&specifiedpost=0&parseurl=1&loggedinuser=196602&posthash=&poststarttime=: undefined";
	curl_setopt($ch, CURLOPT_URL, "http://www.gamesforum.it/board/newreply.php?do=postreply&t=$topic");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $stringozza);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$data = curl_exec($ch);
}

if($_GET['key'] != "nbzoprmhasi423relsadan23djas"){
	echo "Non hai i diritti per visualizzare la pagina";
	die;
}

$excludeSite = array("paypal.com/gb/", "redbox.com", "shopto.net", "/accessories/", "harveynorman.com", "jbhifi.com.au", "bestbuy", "saturn.de", "saturn.com", "muve.pl", "flipkart.com", "ebgames.com", "majornelson.com", "frys.com", "ebay.com", "store.ubi.com/ca/", "ebgames.ca", "itch.io", "amazon.com", "walmart", "playstation", "newegg", "target.com", "gamestop");
$drmlist = array("GOG", "Steam", "Uplay", "Origin");
$feed = implode(file('http://yesthereisadeal.com/feed/eu2/'));
$xml = simplexml_load_string($feed, null, LIBXML_NOCDATA);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
$ytiad = "";
foreach($array["channel"]["item"] as $val){
	$title = $val["title"];
	$first = substr($title, 0, strpos($title,"|"));
	$second = substr($title, strpos($title,"|")+2); 
	$link = $val["link"];
	foreach($excludeSite as $exc){
		if (strpos(strtolower($link), $exc) !== false){
			continue 2;
		}
	}
	$desc = $val["description"];
	$drm = "";
	foreach($drmlist as $drms){
		if (strpos(strtolower($desc), strtolower("DRM:</i> ".$drms)) !== false){
			$drm = $drms;
			continue 1;
		}
	}
	$store = substr($desc, 0, strpos($desc, "</span>"));
	$store = strrev($store);
	$store = substr($store, 0, strpos($store, ">"));
	$store = strrev($store);
	if ($drm == "" && $store != "GOG")
		continue 1;
	if($store == "Steam" || $store == "GOG")
		$ytiad.="[URL=".$link."][".$store."] ".$second." - ".$first."[/URL]<br>";
	else
		$ytiad.="[URL=".$link."][$store] $second - $first ($drm)[/URL]<br>";
}
$ytiad.="<br>[I]Powered by YesThereIsADeal.com[/I]";

$feed = implode(file('https://www.reddit.com/r/GameDeals/new/.rss?limit=40'));
$xml = simplexml_load_string($feed);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
$excludeTitle = array("bestbuy", "physical", "harvey norman", "jbhifi", "flipkart", "muve.pl", "best buy", "deals with gold", "eb games", "ebgames", "amazon", "itch.io", "newegg", "shipping", "retail", "psn", "xbox", "playstation", "nintendo", "nsw", "switch", "ps4", "xb1", "3ds", "target", "gamestop", "walmart");

$redd = "";
foreach($array["entry"] as $val){
	$title = $val["title"];
	$content = $val["content"];
	foreach($excludeTitle as $exc){
		if (strpos(strtolower($title), $exc) !== false){
			continue 2;
		}
	}
	$updated = $val["updated"];
	$updated = strtotime(date(DATE_ATOM, strtotime($updated)));
	$now = strtotime(date(DATE_ATOM));
	$diff = abs($now - $updated);
	$years = floor($diff / (365*60*60*24));
	$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	if($days > 0)
		continue 1;
	$pattern = "!<a href=\"([^\"]+)\">\[link\]<!is";
	preg_match($pattern, $content, $link);
	$link = $link[1];
	foreach($excludeSite as $exc){
		if (strpos(strtolower($link), $exc) !== false){
			continue 2;
		}
	}
	/*if (strpos($ytiad, strtolower($link)) !== false){
		continue 1;
	}*/
	$redd.="[URL=".$link."]".$title."[/URL]<br>";
}
$redd.="<br>[I]Powered by Reddit.com - /r/GameDeals[/I]";

$tot='[SIZE=4][COLOR="#0000FF"]Le offerte del giorno[/COLOR][/SIZE]<br><br>';
$tot.=$redd;
$tot.="<br><br>";
$tot.=$ytiad;
$tot = urlencode($tot);
$tot = str_replace("%E2%82%AC", "%80", $tot);
$tot = str_replace("%C2%A3", "%A3", $tot);
sendPost($tot);
?>