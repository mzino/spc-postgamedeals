<?php

function sendPost($mess){
	$topic="/1250-consigli-per-gli-acquisti-best-sezione-games-2017-anche-spazio-pc-tra-i-curatori-di-steam-seguiteci/";
	$topicId="1250";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.gamesforum.it");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIE,""); // cookie string here
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$data = curl_exec($ch);
	$trova = "https://www.gamesforum.it/logout/?csrfKey=";
	$pos = strpos($data, $trova) + strlen($trova);
	$data = substr($data, $pos);
	$pos = strpos($data, "'");
	$security_token = substr($data, 0, $pos);
	$formData = array(
		"commentform_".$topicId."_submitted" => "1",
		"csrfKey" => $security_token,
		"_contentReply" => "1",
		"MAX_FILE_SIZE" => "535822336",
		"topic_comment_".$topicId => $mess,
		"topic_auto_follow" => "0",
		"hide" => "0"
	);
	$postvars = http_build_query($formData) . "\n";
	curl_setopt($ch, CURLOPT_URL, "https://www.gamesforum.it/topic$topic");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$data = curl_exec($ch);
}

// if($_GET['key'] != "nbzoprmhasi423relsadan23djas"){
// 	echo "Non hai i diritti per visualizzare la pagina";
// 	die;
// }

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
$ytiad.="<br>[I]Powered by YesThereIsADeal.com[/I]<br>";

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

$tot='[COLOR="#0000FF"]Le offerte del giorno[/COLOR]<br><br><br>';
$tot.=$redd;
$tot.="<br><br><br><br>";
$tot.=$ytiad;
$tot = str_replace("%E2%82%AC", "%80", $tot);
$tot = str_replace("%C2%A3", "%A3", $tot);
sendPost($tot);
?>