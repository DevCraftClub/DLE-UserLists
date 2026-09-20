<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Controller;

use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\UserLists\Models\UserList;
use DevCraft\Modules\UserLists\Models\UserListItem;
use DevCraft\Modules\UserLists\Services\ListService;
use DevCraft\Modules\UserLists\Services\PermissionService;
use DevCraft\Modules\UserLists\Services\SeedService;
use DevCraft\Modules\UserLists\Support\PublicSkin;
use DevCraft\Modules\UserLists\UserListsIdentity;

/**
 * Публичные страницы списков (мои, каталог, просмотр, предложения).
 */
final class PageController {

	/**
	 * @param   string  $focus  mine|catalog|view|proposals
	 */
	public function render(string $focus, int $listId, int $page): string {
		global $tpl, $is_logged, $member_id, $config, $db;

		$focus = $focus !== '' ? $focus : 'mine';
		$page  = max(1, $page);

		$logged = !empty($is_logged) && !empty($member_id['user_id']);
		$userId = $logged ? (int) $member_id['user_id'] : 0;
		$cfg    = DataManager::getConfig(UserListsIdentity::code());
		$guestOk = !empty($cfg['guest_can_view_public']);
		$perPage = max(1, (int) ($cfg['count_web'] ?? 20));
		$skin    = PublicSkin::resolve(is_array($config) ? $config : []);

		if(!isset($tpl) || !is_object($tpl)) {
			if(!class_exists('dle_template', false)) {
				require_once \DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
			}

			$tpl      = new \dle_template();
			$tpl->dir = ROOT_DIR . '/templates/' . $skin;
		}

		$restoreDir = $tpl->dir;
		$tpl->dir   = ROOT_DIR . '/templates/' . $skin;

		$service = new ListService();
		(new SeedService())->ensureDefaults($service);
		$perms = new PermissionService();

		$html = match ($focus) {
			'catalog'   => $this->catalog($service, $logged, $guestOk, $page, $perPage, $tpl),
			'view'      => $this->view($service, $logged, $guestOk, $userId, $listId, $page, $perPage, $tpl, $db),
			'proposals' => $this->proposals($service, $perms, $logged, $userId, $tpl),
			default     => $this->mine($service, $perms, $logged, $userId, $tpl),
		};

		$tpl->dir = $restoreDir;

		return $html;
	}

	private function catalog(ListService $service, bool $logged, bool $guestOk, int $page, int $perPage, object $tpl): string {
		if(!$logged && !$guestOk) {
			return $this->error(__('Каталог доступен только авторизованным'));
		}

		$total = $service->listsRepo()->countPublic();
		$rows  = [];

		foreach($service->listsRepo()->findPublic($page, $perPage) as $list) {
			$rows[] = [
				'name' => $list->name,
				'url'  => '?ul_focus=view&list_id=' . $list->id(),
			];
		}

		return $this->listPage($tpl, $rows, __('Каталог публичных списков') . " ({$total})");
	}

	private function mine(ListService $service, PermissionService $perms, bool $logged, int $userId, object $tpl): string {
		if(!$logged || !$perms->isEnabled($userId)) {
			return $this->error(__('Требуется авторизация'));
		}

		$rows = [];

		foreach($service->listsRepo()->findAdminLists() as $list) {
			$rows[] = [
				'name' => $list->name . ' (' . __('админ') . ')',
				'url'  => '?ul_focus=view&list_id=' . $list->id(),
			];
		}

		foreach($service->listsRepo()->findOwnedBy($userId) as $list) {
			$rows[] = [
				'name' => $list->name . ($list->isPublic() ? ' [' . __('публичный') . ']' : ''),
				'url'  => '?ul_focus=view&list_id=' . $list->id(),
			];
		}

		return $this->listPage($tpl, $rows, __('Мои списки'));
	}

	private function proposals(ListService $service, PermissionService $perms, bool $logged, int $userId, object $tpl): string {
		if(!$logged || !$perms->isEnabled($userId)) {
			return $this->error(__('Требуется авторизация'));
		}

		$owned   = $service->listsRepo()->findOwnedBy($userId);
		$listIds = array_map(static fn(UserList $list): int => $list->id(), $owned);
		$html    = '';

		foreach($service->itemsRepo()->findPendingForOwnerLists($listIds) as $item) {
			$html .= '<li data-item-id="' . $item->id() . '">#' . $item->news_id
				. ' <button type="button" class="ul-approve" data-id="' . $item->id() . '">' . __('Одобрить') . '</button>'
				. ' <button type="button" class="ul-reject" data-id="' . $item->id() . '">' . __('Отклонить') . '</button>'
				. '</li>';
		}

		$tpl->set('{ul-title}', htmlspecialchars(__('Входящие предложения'), ENT_QUOTES, 'UTF-8'));
		$tpl->set('{ul-items}', $html !== '' ? '<ul class="ul-proposals">' . $html . '</ul>' : '<p>' . __('Нет предложений') . '</p>');
		$tpl->load_template('devcraft/user_lists/proposals.tpl');
		$tpl->compile('content');

		return (string) ($tpl->result['content'] ?? '');
	}

	private function view(
		ListService $service,
		bool $logged,
		bool $guestOk,
		int $userId,
		int $listId,
		int $page,
		int $perPage,
		object $tpl,
		object $db,
	): string {
		$list = $service->listsRepo()->findOneById($listId);

		if($list === null) {
			return $this->error(__('Список не найден'));
		}

		$desc = '';

		if($list->isAdmin()) {
			if(!$logged) {
				return $this->error(__('Требуется авторизация'));
			}

			$items = $service->itemsRepo()->findApprovedForUserList($listId, $userId);
			$title = $list->name;
		} elseif($list->isPublic()) {
			if(!$logged && !$guestOk) {
				return $this->error(__('Просмотр запрещён'));
			}

			$items = $service->itemsRepo()->findApprovedOnList($listId, $page, $perPage);
			$title = $list->name;
			$desc  = (string) ($list->description ?? '');
		} else {
			if(!$logged || (int) $list->owner_id !== $userId) {
				return $this->error(__('Список не найден'));
			}

			$items = $service->itemsRepo()->findApprovedOnList($listId, $page, $perPage);
			$title = $list->name;
			$desc  = (string) ($list->description ?? '');
		}

		$newsHtml = '';

		foreach($items as $item) {
			/** @var UserListItem $item */
			$row = $db->super_query('SELECT id, title FROM ' . PREFIX . '_post WHERE id=' . (int) $item->news_id . ' LIMIT 1');

			if(!is_array($row) || $row === []) {
				continue;
			}

			$newsHtml .= '<li>#' . (int) $row['id'] . ' '
				. htmlspecialchars(stripslashes((string) $row['title']), ENT_QUOTES, 'UTF-8')
				. '</li>';
		}

		$tpl->set('{ul-title}', htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));
		$tpl->set('{ul-description}', $desc);
		$tpl->set('{ul-items}', $newsHtml !== '' ? '<ul class="ul-news">' . $newsHtml . '</ul>' : '<p>' . __('Нет новостей') . '</p>');
		$tpl->load_template('devcraft/user_lists/view.tpl');
		$tpl->compile('content');

		return (string) ($tpl->result['content'] ?? '');
	}

	/**
	 * @param   list<array{name: string, url: string}>  $rows
	 */
	private function listPage(object $tpl, array $rows, string $title): string {
		$html = '';

		foreach($rows as $row) {
			$html .= '<li><a href="' . htmlspecialchars((string) $row['url'], ENT_QUOTES, 'UTF-8') . '">'
				. htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') . '</a></li>';
		}

		$tpl->set('{ul-title}', htmlspecialchars($title, ENT_QUOTES, 'UTF-8'));
		$tpl->set('{ul-items}', $html !== '' ? '<ul class="ul-list">' . $html . '</ul>' : '<p>' . __('Пусто') . '</p>');
		$tpl->load_template('devcraft/user_lists/page.tpl');
		$tpl->compile('content');

		return (string) ($tpl->result['content'] ?? '');
	}

	private function error(string $message): string {
		if(function_exists('msgbox')) {
			msgbox(__('Ошибка'), $message);
		}

		return '';
	}

}
