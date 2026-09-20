<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Repositories;

use DevCraft\Core\Abstracts\AbstractRepository;
use DevCraft\Modules\UserLists\Models\UserList;

/**
 * Репозиторий списков.
 */
final class UserListRepository extends AbstractRepository {

	public function findOneById(int $id): ?UserList {
		/** @var UserList|null $entity */
		$entity = $this->select()->where('id', $id)->fetchOne();

		return $entity;
	}

	/**
	 * @return list<UserList>
	 */
	public function findAdminLists(): array {
		/** @var list<UserList> $items */
		$items = $this->select()
			->where('type', UserList::TYPE_ADMIN)
			->orderBy('position', 'ASC')
			->orderBy('id', 'ASC')
			->fetchAll();

		return $items;
	}

	/**
	 * @return list<UserList>
	 */
	public function findOwnedBy(int $ownerId): array {
		/** @var list<UserList> $items */
		$items = $this->select()
			->where('type', UserList::TYPE_USER)
			->where('owner_id', $ownerId)
			->orderBy('position', 'ASC')
			->orderBy('id', 'ASC')
			->fetchAll();

		return $items;
	}

	public function countOwnedBy(int $ownerId): int {
		return (int) $this->select()
			->where('type', UserList::TYPE_USER)
			->where('owner_id', $ownerId)
			->count();
	}

	public function findAdminByName(string $name): ?UserList {
		/** @var UserList|null $entity */
		$entity = $this->select()
			->where('type', UserList::TYPE_ADMIN)
			->where('name', $name)
			->fetchOne();

		return $entity;
	}

	/**
	 * @return list<UserList>
	 */
	public function findPublic(int $page, int $perPage): array {
		/** @var list<UserList> $items */
		$items = $this->select()
			->where('type', UserList::TYPE_USER)
			->where('visibility', UserList::VIS_PUBLIC)
			->orderBy('id', 'DESC')
			->limit($perPage)
			->offset(max(0, ($page - 1) * $perPage))
			->fetchAll();

		return $items;
	}

	public function countPublic(): int {
		return (int) $this->select()
			->where('type', UserList::TYPE_USER)
			->where('visibility', UserList::VIS_PUBLIC)
			->count();
	}

}
