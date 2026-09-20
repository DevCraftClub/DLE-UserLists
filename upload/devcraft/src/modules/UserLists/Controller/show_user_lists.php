<?php

declare(strict_types=1);

/**
 * Публичный include кнопки и окна списков (канон Controller/).
 *
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists.php?news_id={news-id}&focus=button"}
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists.php?news_id={news-id}&focus=modal"}
 *
 * focus=css и focus=js ничего не выводят: стили и скрипты — siteAssets и теги {devcraft-header} / {devcraft-scripts}.
 */

if(!defined('DATALIFEENGINE')) {
	header('HTTP/1.1 403 Forbidden');

	exit('Hacking attempt!');
}

if(!defined('DEVCRAFT_BOOTSTRAPPED')) {
	require_once DLEPlugins::Check(ROOT_DIR . '/devcraft/init.php');
}

if(!defined('DEVCRAFT_BOOTSTRAPPED')) {
	return;
}

$focus = isset($focus) ? (string) $focus : 'button';
$newsId = 0;

if(isset($news_id)) {
	$newsId = (int) $news_id;
} elseif(isset($newsid)) {
	$newsId = (int) $newsid;
}

echo (new DevCraft\Modules\UserLists\Controller\WidgetController())->render($focus, $newsId);
