<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Models;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DevCraft\Core\Abstracts\AbstractEntity;
use DevCraft\Modules\UserLists\Repositories\GroupPermissionRepository;

/**
 * Права группы на UserLists (`{prefix}_dc_user_list_group_permissions`).
 */
#[Entity(role: 'dc_user_list_group_permission', repository: GroupPermissionRepository::class, table: 'dc_user_list_group_permissions')]
#[Index(columns: ['group_id'], unique: true, name: 'idx_dc_ul_perm_group')]
class GroupPermission extends AbstractEntity {

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $group_id = 0;

	/** JSON-карта настроек группы. */
	#[Column(type: 'text')]
	public string $settings = '{}';

	public function __construct() {
		$this->createdAt = new \DateTimeImmutable();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function values(): array {
		$decoded = json_decode($this->settings, true);

		return is_array($decoded) ? $decoded : [];
	}

	/**
	 * @param array<string, mixed> $values
	 */
	public function setValues(array $values): void {
		$this->settings = json_encode($values, JSON_UNESCAPED_UNICODE) ?: '{}';
	}

}
