<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Models;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DevCraft\Core\Abstracts\AbstractEntity;
use DevCraft\Modules\UserLists\Repositories\UserListItemRepository;

/**
 * Новость в списке (одобренная или предложение).
 */
#[Entity(role: 'dc_user_list_item', repository: UserListItemRepository::class, table: 'dc_user_list_items')]
#[Index(columns: ['list_id', 'user_id', 'news_id'], unique: true, name: 'idx_dc_uli_unique')]
#[Index(columns: ['list_id', 'status'], name: 'idx_dc_uli_list_status')]
#[Index(columns: ['news_id'], name: 'idx_dc_uli_news')]
class UserListItem extends AbstractEntity {

	public const STATUS_APPROVED = 'approved';

	public const STATUS_PENDING = 'pending';

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $list_id = 0;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $user_id = 0;

	#[Column(type: 'integer', unsigned: true, default: 0)]
	public int $news_id = 0;

	#[Column(type: 'string', size: 16, default: 'approved')]
	public string $status = self::STATUS_APPROVED;

	public function __construct() {
		$this->createdAt = new \DateTimeImmutable();
	}

}
