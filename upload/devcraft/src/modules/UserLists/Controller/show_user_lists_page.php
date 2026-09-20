<?php

declare(strict_types=1);

/**
 * Публичные страницы списков (канон Controller/).
 *
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=mine"}
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=catalog"}
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=proposals"}
 * {include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=view&list_id=1"}
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

$focus  = isset($focus) ? (string) $focus : 'mine';
$listId = isset($list_id) ? (int) $list_id : (int) ($_REQUEST['list_id'] ?? 0);
$page   = max(1, (int) ($_REQUEST['cstart'] ?? $_REQUEST['page'] ?? 1));

echo (new DevCraft\Modules\UserLists\Controller\PageController())->render($focus, $listId, $page);
