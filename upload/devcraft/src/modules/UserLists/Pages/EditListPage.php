<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DLEPlugins;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Support\DleDataService;
use DevCraft\Modules\UserLists\Services\ListService;

/**
 * Редактирование списка в админке.
 */
final class EditListPage extends AbstractPage {

	public function handle(): array {
		global $config, $dle_login_hash;

		$id   = (int) ($_REQUEST['id'] ?? 0);
		$list = (new ListService())->listsRepo()->findOneById($id);

		$this->addBreadcrumb(__('Списки'), '?mod=user_lists&action=' . ($list?->isAdmin() ? 'admin_lists' : 'user_lists'));
		$this->addBreadcrumb(__('Редактирование'));

		if($list === null) {
			return [
				'view' => 'userlists/edit.twig',
				'data' => [
					'page_title' => __('Список не найден'),
					'list'       => null,
					'users'      => [],
				],
			];
		}

		$suggestors = [];
		$users      = [];

		if(!$list->isAdmin()) {
			$suggestors = (new ListService())->suggestorsRepo()->userIdsForList($list->id());

			foreach(DleDataService::users() as $row) {
				$uid  = (int) ($row['user_id'] ?? 0);
				$name = trim((string) ($row['name'] ?? ''));

				if($uid <= 0 || $name === '') {
					continue;
				}

				$users[] = [
					'id'   => $uid,
					'name' => $name,
				];
			}
		}

		$dleHome = rtrim((string) ($config['http_home_url'] ?? '/'), '/') . '/';

		return [
			'view' => 'userlists/edit.twig',
			'data' => [
				'page_title'       => __('Редактирование списка'),
				'dle_home'         => $dleHome,
				'dle_skin'         => (string) ($config['skin'] ?? 'Default'),
				'dle_login_hash'   => (string) ($dle_login_hash ?? ''),
				'pm_wysiwyg'       => !empty($config['allow_pm_wysiwyg']),
				'pm_editor_script' => !$list->isAdmin() ? $this->buildPmEditorScript() : '',
				'users'            => $users,
				'list'             => [
					'id'                => $list->id(),
					'name'              => $list->name,
					'type'              => $list->type,
					'visibility'        => $list->visibility,
					'description'       => (string) ($list->description ?? ''),
					'allow_suggestions' => $list->allow_suggestions,
					'suggestion_policy' => $list->suggestion_policy,
					'owner_id'          => $list->owner_id,
					'suggestor_ids'     => array_values(array_map('intval', $suggestors)),
				],
			],
		];
	}

	/**
	 * Скрипт DLE PM/TinyMCE для textarea.ajaxwysiwygeditor.
	 */
	private function buildPmEditorScript(): string {
		global $config, $lang, $member_id, $user_group, $dle_login_hash, $is_logged, $db, $tpl;

		if(!isset($lang) || !is_array($lang)) {
			$lang = [];
		}

		if(!isset($lang['language_code'])) {
			$lang['language_code'] = $lang['language_code'] ?? 'ru';
			$lang['direction']     = $lang['direction'] ?? 'ltr';
		}

		if(!isset($tpl) || !is_object($tpl)) {
			if(!class_exists('dle_template', false)) {
				require_once DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
			}
			$tpl             = new \dle_template();
			$tpl->smartphone = false;
			$tpl->tablet     = false;
		}

		$is_pm_ajax_mode        = true;
		$comments_mobile_editor = false;

		/** @noinspection PhpIncludeInspection */
		include DLEPlugins::Check(ENGINE_DIR . '/editor/pm.php');

		return isset($editor_scrips) ? (string) $editor_scrips : '';
	}

}
