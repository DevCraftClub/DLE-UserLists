<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Repositories;

use DevCraft\Core\Abstracts\AbstractRepository;
use DevCraft\Modules\UserLists\Models\GroupPermission;

/**
 * Репозиторий прав групп UserLists.
 */
final class GroupPermissionRepository extends AbstractRepository {

	public function findByGroupId(int $groupId): ?GroupPermission {
		/** @var GroupPermission|null $entity */
		$entity = $this->select()->where('group_id', $groupId)->fetchOne();

		return $entity;
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function upsert(int $groupId, array $values): GroupPermission {
		$entity = $this->findByGroupId($groupId);

		if($entity === null) {
			$entity           = new GroupPermission();
			$entity->group_id = $groupId;
		}

		$entity->setValues($values);
		$this->saveEntity($entity);

		return $entity;
	}

}
