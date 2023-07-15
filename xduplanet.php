<?php
require_once 'lib/Feed.php';
require_once 'xdurepo.php';
require_once 'lib/utils.php';

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Asia/Shanghai');
}

header('Content-Type: application/json');

if (isset($_GET['feed'])) {
	$feed = $_GET['feed'];
	$rssUrlInt = $repos[$feed]["feed"];
} else {
	$toReturn = new stdClass();
	$toReturn->repos = $repos;
	exit(json_encode($toReturn));
}

if (isset($_GET['p'])) {
	$page = intval($_GET['p']);
} else {
	$page = -1;
}

Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '1 hours';

$rss = Feed::load($rssUrlInt);
$rsstype = Feed::checktype($rssUrlInt);
$rssTitle = $rss->title;
$toReturn = new stdClass();
if ($page < 0) {
	$list = array();
	if ($rsstype == 'Atom') {
		for ($i = 0; $i < sizeof($rss->entry); $i++) {
			$item = $rss->entry[$i];
			$toAdd = new stdClass();
			$toAdd->title = (string)$item->title;
			$toAdd->time = date(DATE_ATOM,(int)$item->timestamp);
			$toAdd->url = (string)$item->url;
			$list[] = $toAdd;
		}
	} else {
		for ($i = 0; $i < sizeof($rss->item); $i++) {
			$item = $rss->item[$i];
			$toAdd = new stdClass();
                        $toAdd->title = (string)$item->title;
                        $toAdd->time = date(DATE_ATOM,(int)$item->timestamp);
			$toAdd->url = (string)$item->url;
                        $list[] = $toAdd;

		}
	}
	$toReturn->list = $list;
	$toReturn->lastUpdateTime = time();
} else {
	$rsstype == 'Atom' ? $item = $rss->entry[$page] : $item = $rss->item[$page];
	$itemDescription = convert_img_to_a($item->description);
	$itemContent = convert_img_to_a($item->content);
	$toReturn->title = (string)$item->title;
	$toReturn->link = (string)$item->url;
	$toReturn->time = (int)$item->timestamp;
	if ($rsstype == 'Atom') {
		if ($item->content['type'] == 'html') {
			$toReturn->content = (string)$itemContent;
		} else {
			$toReturn->content = (string)htmlspecialchars($itemContent);
		}
	} else {
		if (isset($item->{'content:encoded'})) {
			$toReturn->content = (string)convert_img_to_a($item->{'content:encoded'});
		} else {
			$toReturn->content = (string)$itemDescription;
		}
	}
}
exit(json_encode($toReturn));
?>
