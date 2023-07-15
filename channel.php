<?php
require_once 'lib/Feed.php';
require_once 'repos.php';
require_once 'lib/utils.php';

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Asia/Shanghai');
}
ini_set('mbstring.substitute_character', "none");

header('Content-Type: text/html; charset=gbk');

if (isset($_GET['p'])) {
	$page = intval($_GET['p']);
} else {
	$page = -1;
}

if (isset($_GET['feed'])) {
	$feed = $_GET['feed'];
	$rssUrlInt = $repos[$feed];
} else {
	$feed = "nothing";
	$rssUrlInt = 'https://rsshub.app/eastday/sh';
}

Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '2 hours';

$rss = Feed::load($rssUrlInt);
$rsstype = Feed::checktype($rssUrlInt);
$rssTitle = mb_convert_encoding($rss->title, 'gbk', 'UTF-8');
?>
<html>
	<head>
		<title><?php echo $rssTitle ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312"> 
	</head>
	<body>
	<?php if ($page < 0) : ?>
		<h4><?php echo $rssTitle ?></h4>
		<?php if ($rsstype == 'Atom') : ?>
			<?php for ($i = 0; $i < sizeof($rss->entry); $i++) : ?>
				<p><a href="<?php echo htmlspecialchars('channel.php?feed=' . $feed . '&p=' . $i) ?>">
						<?php echo mb_convert_encoding($rss->entry[$i]->title, 'gbk', 'UTF-8') ?></a>
					<?php echo date('Y-m-d H:i', (int) $rss->entry[$i]->timestamp) ?>
				</p>
			<?php endfor ?>
		<?php else : ?>
			<?php for ($i = 0; $i < sizeof($rss->item); $i++) : ?>
				<p><a href="<?php echo htmlspecialchars('channel.php?feed=' . $feed . '&p=' . $i) ?>">
						<?php echo mb_convert_encoding($rss->item[$i]->title, 'gbk', 'UTF-8') ?></a>
					<?php echo date('Y-m-d H:i', (int) $rss->item[$i]->timestamp) ?>
				</p>
			<?php endfor ?>
		<?php endif ?>
		<a href="/">Back</a>
	<?php else : ?>
		<?php
			$rsstype == 'Atom' ? $item = $rss->entry[$page] : $item = $rss->item[$page];
			$itemTitle = mb_convert_encoding($item->title, 'gbk', 'UTF-8');
			$itemDescription = mb_convert_encoding(convert_img_to_a($item->description), 'gbk', 'UTF-8');
			$itemContent = mb_convert_encoding(convert_img_to_a($item->content), 'gbk', 'UTF-8');
			$itemAuthor = mb_convert_encoding($rss->item[$i]->title, 'gbk', 'UTF-8');
		?>
		<h4><?php echo $itemTitle ?></h4>
		<i>From: <a href="<?php echo $item->url?>"><?php echo $rssTitle ?></a></i>
		<?php if ($rsstype == 'Atom') :?>
			<?php if ($item->content['type'] == 'html'): ?>
				<div><?php echo $itemContent ?></div>
			<?php else: ?>
				<p><?php echo htmlspecialchars($itemContent) ?></p>
			<?php endif ?>
		<?php else : ?>
            		<?php if (isset($item->{'content:encoded'})): ?>
				<div><?php echo mb_convert_encoding(convert_img_to_a($item->{'content:encoded'}), 'gbk', 'UTF-8'); ?></div>
			<?php else: ?>
				<p><?php echo $itemDescription ?></p>
			<?php endif ?>
		<?php endif ?>
		<a href="<?php echo htmlspecialchars('channel.php?feed=' . $feed) ?>">Back</a>
	<?php endif ?>
</body>
</html>
