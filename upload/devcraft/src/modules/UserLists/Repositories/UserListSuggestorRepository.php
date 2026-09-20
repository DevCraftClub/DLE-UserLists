<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Repositories;

use DevCraft\Core\Abstracts\AbstractRepository;
use DevCraft\Modules\UserLists\Models\UserListSuggestor;

/**
 * Репозиторий белого списка предлагающих.
 */
final class UserListSuggestorRepository extends AbstractRepository {

	public function isAllowed(int $listId, int $userId): bool {
		$entity = $this->select()
			->where('list_id', $listId)
			->where('user_id', $userId)
			->fetchOne();

		return $entity !== null;
	}

	/**
	 * @return list<int>
	 */
	public function userIdsForList(int $listId): array {
		/** @var list<UserListSuggestor> $items */
		$items = $this->select()->where('list_id', $listId)->fetchAll();
		$ids   = [];

		foreach($items as $item) {
			$ids[] = $item->user_id;
		}

		return $ids;
	}

	public function replaceForList(int $listId, array $userIds): void {
		/** @var list<UserListSuggestor> $existing */
		$existing = $this->select()->where('list_id', $listId)->fetchAll();

		foreach($existing as $row) {
			$this->deleteEntity($row);
		}

		foreach(array_unique(array_map('intval', $userIds)) as $uid) {
			if($uid <= 0) {
				continue;
			}

			$entity          = new UserListSuggestor();
			$entity->list_id = $listId;
			$entity->user_id = $uid;
			$this->saveEntity($entity);
		}
	}

}
