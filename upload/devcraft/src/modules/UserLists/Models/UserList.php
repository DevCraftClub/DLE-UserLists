<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Models;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DevCraft\Core\Abstracts\AbstractEntity;
use DevCraft\Modules\UserLists\Repositories\UserListRepository;

/**
 * Список новостей (админский или пользовательский).
 */
#[Entity(role: 'dc_user_list', repository: UserListRepository::class, table: 'dc_user_lists')]
#[Index(columns: ['type', 'name'], name: 'idx_dc_ul_type_name')]
#[Index(columns: ['owner_id', 'name'], name: 'idx_dc_ul_owner_name')]
#[Index(columns: ['visibility'], name: 'idx_dc_ul_visibility')]
class UserList extends AbstractEntity {

	public const TYPE_ADMIN = 'admin';

	public const TYPE_USER = 'user';

	public const VIS_PRIVATE = 'private';

	public const VIS_PUBLIC = 'public';

	public const SUGGEST_WHITELIST = 'whitelist';

	public const SUGGEST_EVERYONE = 'everyone';

	#[Column(type: 'string', size: 100)]
	public string $name = '';

	#[Column(type: 'string', size: 16, default: 'user')]
	public string $type = self::TYPE_USER;

	#[Column(type: 'integer', unsigned: true, nullable: true, default: null)]
	public ?int $owner_id = null;

	#[Column(type: 'string', size: 16, default: 'private')]
	public string $visibility = self::VIS_PRIVATE;

	#[Column(type: 'text', nullable: true, default: null)]
	public ?string $description = null;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $position = 0;

	#[Column(type: 'boolean', default: false)]
	public bool $allow_suggestions = false;

	#[Column(type: 'string', size: 16, default: 'whitelist')]
	public string $suggestion_policy = self::SUGGEST_WHITELIST;

	public function __construct() {
		$this->createdAt = new \DateTimeImmutable();
	}

	public function isAdmin(): bool {
		return $this->type === self::TYPE_ADMIN;
	}

	public function isPublic(): bool {
		return $this->visibility === self::VIS_PUBLIC;
	}

}
