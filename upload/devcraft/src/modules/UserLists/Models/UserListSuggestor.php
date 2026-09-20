<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Models;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DevCraft\Core\Abstracts\AbstractEntity;
use DevCraft\Modules\UserLists\Repositories\UserListSuggestorRepository;

/**
 * Белый список пользователей, которым разрешено предлагать новости в список.
 */
#[Entity(role: 'dc_user_list_suggestor', repository: UserListSuggestorRepository::class, table: 'dc_user_list_suggestors')]
#[Index(columns: ['list_id', 'user_id'], unique: true, name: 'idx_dc_uls_unique')]
class UserListSuggestor extends AbstractEntity {

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $list_id = 0;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $user_id = 0;

	public function __construct() {
		$this->createdAt = new \DateTimeImmutable();
	}

}
