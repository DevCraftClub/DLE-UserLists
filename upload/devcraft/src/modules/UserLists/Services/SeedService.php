<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Services;

use DevCraft\Modules\UserLists\Models\UserList;

/**
 * Сиды админ-списков по умолчанию.
 */
final class SeedService {

	/** @var list<string> */
	public const DEFAULT_ADMIN_NAMES = [
		'В планах',
		'Просмотрено',
		'Избрано',
	];

	public function ensureDefaults(ListService $lists): void {
		foreach(self::DEFAULT_ADMIN_NAMES as $name) {
			if($lists->listsRepo()->findAdminByName($name) !== null) {
				continue;
			}

			$list             = new UserList();
			$list->name       = $name;
			$list->type       = UserList::TYPE_ADMIN;
			$list->owner_id   = null;
			$list->visibility = UserList::VIS_PRIVATE;
			$list->position   = (int) (array_search($name, self::DEFAULT_ADMIN_NAMES, true) ?: 0);
			$lists->listsRepo()->saveEntity($list);

			AuditLogger::info(__('Системный сид админ-списка'), ['name' => $name]);
		}
	}

}
