<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Repositories;

use DevCraft\Core\Abstracts\AbstractRepository;
use DevCraft\Modules\UserLists\Models\UserListItem;

/**
 * Репозиторий элементов списков.
 */
final class UserListItemRepository extends AbstractRepository {

	public function findOneById(int $id): ?UserListItem {
		/** @var UserListItem|null $entity */
		$entity = $this->select()->where('id', $id)->fetchOne();

		return $entity;
	}

	public function findMembership(int $listId, int $userId, int $newsId): ?UserListItem {
		/** @var UserListItem|null $entity */
		$entity = $this->select()
			->where('list_id', $listId)
			->where('user_id', $userId)
			->where('news_id', $newsId)
			->fetchOne();

		return $entity;
	}

	/**
	 * @return list<UserListItem>
	 */
	public function findApprovedForUserList(int $listId, int $userId): array {
		/** @var list<UserListItem> $items */
		$items = $this->select()
			->where('list_id', $listId)
			->where('user_id', $userId)
			->where('status', UserListItem::STATUS_APPROVED)
			->orderBy('id', 'DESC')
			->fetchAll();

		return $items;
	}

	/**
	 * Одобренные элементы публичного списка владельца (user_id владельца или любой approved на list).
	 *
	 * @return list<UserListItem>
	 */
	public function findApprovedOnList(int $listId, int $page, int $perPage): array {
		/** @var list<UserListItem> $items */
		$items = $this->select()
			->where('list_id', $listId)
			->where('status', UserListItem::STATUS_APPROVED)
			->orderBy('id', 'DESC')
			->limit($perPage)
			->offset(max(0, ($page - 1) * $perPage))
			->fetchAll();

		return $items;
	}

	/**
	 * @return list<UserListItem>
	 */
	public function findPendingForOwnerLists(array $listIds): array {
		if($listIds === []) {
			return [];
		}

		/** @var list<UserListItem> $items */
		$items = $this->select()
			->where('list_id', 'in', array_values($listIds))
			->where('status', UserListItem::STATUS_PENDING)
			->orderBy('id', 'DESC')
			->fetchAll();

		return $items;
	}

	/**
	 * @return list<int>
	 */
	public function newsIdsInListsForUser(int $userId, int $newsId): array {
		/** @var list<UserListItem> $items */
		$items = $this->select()
			->where('user_id', $userId)
			->where('news_id', $newsId)
			->where('status', UserListItem::STATUS_APPROVED)
			->fetchAll();

		$ids = [];

		foreach($items as $item) {
			$ids[] = $item->list_id;
		}

		return $ids;
	}

}
