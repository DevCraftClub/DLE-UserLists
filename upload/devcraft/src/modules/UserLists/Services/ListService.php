<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Services;

use DevCraft\Core\Application;
use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\UserLists\Models\UserList;
use DevCraft\Modules\UserLists\Models\UserListItem;
use DevCraft\Modules\UserLists\Repositories\UserListItemRepository;
use DevCraft\Modules\UserLists\Repositories\UserListRepository;
use DevCraft\Modules\UserLists\Repositories\UserListSuggestorRepository;
use DevCraft\Modules\UserLists\UserListsIdentity;
use RuntimeException;

/**
 * Доменная логика списков и элементов.
 */
final class ListService {

	public function __construct(
		private readonly PermissionService $permissions = new PermissionService(),
		private readonly ConfigNormalizer $configNormalizer = new ConfigNormalizer(),
	) {}

	/**
	 * @return array<string, mixed>
	 */
	public function config(): array {
		return $this->configNormalizer->normalize(
			DataManager::getConfig(UserListsIdentity::code()),
		);
	}

	public function listsRepo(): UserListRepository {
		/** @var UserListRepository $repo */
		$repo = Application::instance()->database()->repository(UserList::class);

		return $repo;
	}

	public function itemsRepo(): UserListItemRepository {
		/** @var UserListItemRepository $repo */
		$repo = Application::instance()->database()->repository(UserListItem::class);

		return $repo;
	}

	public function suggestorsRepo(): UserListSuggestorRepository {
		/** @var UserListSuggestorRepository $repo */
		$repo = Application::instance()->database()->repository(
			\DevCraft\Modules\UserLists\Models\UserListSuggestor::class,
		);

		return $repo;
	}

	public function validateName(string $name): string {
		$cfg  = $this->config();
		$name = trim($name);
		$min  = (int) $cfg['name_min'];
		$max  = (int) $cfg['name_max'];
		$len  = mb_strlen($name);

		if($len < $min || $len > $max) {
			throw new RuntimeException(
				__('Название должно быть от {min} до {max} символов', [
					'{min}' => (string) $min,
					'{max}' => (string) $max,
				]),
			);
		}

		$bad = array_filter(array_map('trim', explode(',', (string) $cfg['bad_words'])));

		foreach($bad as $word) {
			if($word !== '' && mb_stripos($name, $word) !== false) {
				throw new RuntimeException(__('В названии есть запрещённое слово'));
			}
		}

		return $name;
	}

	public function createAdminList(string $name, int $actorId): UserList {
		$name = $this->validateName($name);

		if($this->listsRepo()->findAdminByName($name) !== null) {
			throw new RuntimeException(__('Админ-список с таким именем уже есть'));
		}

		$list              = new UserList();
		$list->name        = $name;
		$list->type        = UserList::TYPE_ADMIN;
		$list->owner_id    = null;
		$list->visibility  = UserList::VIS_PRIVATE;
		$list->position    = $this->nextAdminPosition();
		$this->listsRepo()->saveEntity($list);

		AuditLogger::info(__('Создан админ-список'), [
			'list_id' => $list->id(),
			'name'    => $name,
			'actor'   => $actorId,
		]);

		return $list;
	}

	public function createUserList(
		int $ownerId,
		string $name,
		string $visibility = UserList::VIS_PRIVATE,
		string $description = '',
		bool $asAdmin = false,
	): UserList {
		if(!$this->permissions->isEnabled($ownerId)) {
			throw new RuntimeException(__('Модуль недоступен для вашей группы'));
		}

		$max = $this->permissions->maxLists($ownerId);
		$cnt = $this->listsRepo()->countOwnedBy($ownerId);

		if(!$asAdmin && $cnt >= $max) {
			throw new RuntimeException(__('Достигнут лимит списков для вашей группы ({max})', [
				'{max}' => (string) $max,
			]));
		}

		$name = $this->validateName($name);

		if($visibility === UserList::VIS_PUBLIC && !$this->permissions->canPublic($ownerId)) {
			$visibility = UserList::VIS_PRIVATE;
		}

		$list                   = new UserList();
		$list->name             = $name;
		$list->type             = UserList::TYPE_USER;
		$list->owner_id         = $ownerId;
		$list->visibility       = $visibility === UserList::VIS_PUBLIC
			? UserList::VIS_PUBLIC
			: UserList::VIS_PRIVATE;
		$list->description      = $description;
		$list->position         = $this->nextUserPosition($ownerId);
		$this->listsRepo()->saveEntity($list);

		AuditLogger::info(__('Создан пользовательский список'), [
			'list_id'     => $list->id(),
			'owner'       => $ownerId,
			'name'        => $name,
			'as_admin'    => $asAdmin,
			'limit_bypass'=> $asAdmin && $cnt >= $max,
		]);

		return $list;
	}

	public function updateList(UserList $list, array $data, int $actorId, bool $asAdmin = false): UserList {
		if(!$asAdmin && $list->isAdmin()) {
			throw new RuntimeException(__('Админ-список нельзя изменить с сайта'));
		}

		if(!$asAdmin && (int) $list->owner_id !== $actorId) {
			throw new RuntimeException(__('Нет прав на изменение списка'));
		}

		if(isset($data['name'])) {
			$list->name = $this->validateName((string) $data['name']);
		}

		if(array_key_exists('description', $data)) {
			$list->description = (string) $data['description'];
		}

		if(isset($data['visibility']) && !$list->isAdmin()) {
			$vis = (string) $data['visibility'];

			if($vis === UserList::VIS_PUBLIC) {
				$owner = (int) ($list->owner_id ?? 0);

				if(!$asAdmin && !$this->permissions->canPublic($owner > 0 ? $owner : $actorId)) {
					throw new RuntimeException(__('Публичные списки запрещены для группы'));
				}

				$list->visibility = UserList::VIS_PUBLIC;
			} else {
				$list->visibility = UserList::VIS_PRIVATE;
			}
		}

		if(isset($data['allow_suggestions'])) {
			$list->allow_suggestions = !empty($data['allow_suggestions']);
		}

		if(isset($data['suggestion_policy'])) {
			$policy = (string) $data['suggestion_policy'];
			$list->suggestion_policy = $policy === UserList::SUGGEST_EVERYONE
				? UserList::SUGGEST_EVERYONE
				: UserList::SUGGEST_WHITELIST;
		}

		$this->listsRepo()->saveEntity($list);

		AuditLogger::info(__('Изменён список'), [
			'list_id' => $list->id(),
			'actor'   => $actorId,
			'data'    => array_keys($data),
		]);

		return $list;
	}

	public function deleteList(UserList $list, int $actorId, bool $asAdmin = false): void {
		if($list->isAdmin() && !$asAdmin) {
			throw new RuntimeException(__('Админ-список нельзя удалить'));
		}

		if(!$asAdmin && (int) $list->owner_id !== $actorId) {
			throw new RuntimeException(__('Нет прав на удаление списка'));
		}

		$listId = $list->id();
		$this->listsRepo()->deleteEntity($list);

		AuditLogger::info(__('Удалён список'), [
			'list_id' => $listId,
			'actor'   => $actorId,
			'admin'   => $asAdmin,
		]);
	}

	/**
	 * Порядок админ-списков (owner_id = null) — один UPDATE.
	 *
	 * @param list<int> $orderedIds
	 */
	public function reorderAdminLists(array $orderedIds): void {
		$this->applyPositionOrder($orderedIds, UserList::TYPE_ADMIN, null);
	}

	/**
	 * @param list<int> $orderedIds
	 */
	public function reorderUserLists(int $ownerId, array $orderedIds): void {
		$this->applyPositionOrder($orderedIds, UserList::TYPE_USER, $ownerId);
	}

	/**
	 * Порядок user-списков из админки: position внутри каждой группы owner_id.
	 *
	 * @param list<int> $orderedIds
	 */
	public function reorderListedUserLists(array $orderedIds): void {
		$byOwner = [];

		foreach($orderedIds as $id) {
			$list = $this->listsRepo()->findOneById((int) $id);

			if($list === null || $list->isAdmin() || $list->owner_id === null) {
				continue;
			}

			$byOwner[(int) $list->owner_id][] = $list->id();
		}

		foreach($byOwner as $ownerId => $ids) {
			$this->reorderUserLists((int) $ownerId, $ids);
		}
	}

	/**
	 * @param list<int> $orderedIds
	 */
	private function applyPositionOrder(array $orderedIds, string $type, ?int $ownerId): void {
		$ids = [];
		$cases = [];
		$pos = 0;

		foreach($orderedIds as $rawId) {
			$id = (int) $rawId;

			if($id <= 0) {
				continue;
			}

			$ids[]   = $id;
			$cases[] = 'WHEN ' . $id . ' THEN ' . $pos;
			$pos++;
		}

		if($ids === []) {
			return;
		}

		$table = PREFIX . '_dc_user_lists';
		$sql   = 'UPDATE `' . $table . '` SET `position` = CASE `id` '
			. implode(' ', $cases)
			. ' END WHERE `type` = :type AND `id` IN (' . implode(',', $ids) . ')';

		$params = ['type' => $type];

		if($ownerId === null) {
			$sql .= ' AND `owner_id` IS NULL';
		} else {
			$sql .= ' AND `owner_id` = :owner_id';
			$params['owner_id'] = $ownerId;
		}

		Application::instance()->database()->connection()->execute($sql, $params);
	}

	public function toggleNews(int $listId, int $userId, int $newsId): array {
		if(!$this->permissions->isEnabled($userId)) {
			throw new RuntimeException(__('Модуль недоступен для вашей группы'));
		}

		$list = $this->listsRepo()->findOneById($listId);

		if($list === null) {
			throw new RuntimeException(__('Список не найден'));
		}

		if($list->isAdmin()) {
			$ownerKey = $userId;
		} elseif((int) $list->owner_id === $userId) {
			$ownerKey = $userId;
		} else {
			throw new RuntimeException(__('Нельзя менять чужой список напрямую'));
		}

		$existing = $this->itemsRepo()->findMembership($listId, $ownerKey, $newsId);

		if($existing !== null && $existing->status === UserListItem::STATUS_APPROVED) {
			$this->itemsRepo()->deleteEntity($existing);

			return ['in_list' => false, 'list_id' => $listId];
		}

		if($existing !== null) {
			$existing->status = UserListItem::STATUS_APPROVED;
			$this->itemsRepo()->saveEntity($existing);
		} else {
			$item          = new UserListItem();
			$item->list_id = $listId;
			$item->user_id = $ownerKey;
			$item->news_id = $newsId;
			$item->status  = UserListItem::STATUS_APPROVED;
			$this->itemsRepo()->saveEntity($item);
		}

		return ['in_list' => true, 'list_id' => $listId];
	}

	public function suggestNews(int $listId, int $fromUserId, int $newsId): UserListItem {
		if(!$this->permissions->isEnabled($fromUserId) || !$this->permissions->canSuggest($fromUserId)) {
			throw new RuntimeException(__('Предложения недоступны для вашей группы'));
		}

		$list = $this->listsRepo()->findOneById($listId);

		if($list === null || $list->isAdmin() || !$list->isPublic()) {
			throw new RuntimeException(__('В этот список нельзя предлагать'));
		}

		if(!$list->allow_suggestions) {
			throw new RuntimeException(__('Владелец запретил предложения'));
		}

		if((int) $list->owner_id === $fromUserId) {
			throw new RuntimeException(__('Добавляйте новости в свой список через обычное меню'));
		}

		if($list->suggestion_policy === UserList::SUGGEST_WHITELIST
			&& !$this->suggestorsRepo()->isAllowed($listId, $fromUserId)) {
			throw new RuntimeException(__('Вы не в белом списке предлагающих'));
		}

		$ownerId  = (int) $list->owner_id;
		$existing = $this->itemsRepo()->findMembership($listId, $fromUserId, $newsId);

		if($existing !== null) {
			return $existing;
		}

		// Уникальный ключ list+user+news — предложение от fromUserId
		$item          = new UserListItem();
		$item->list_id = $listId;
		$item->user_id = $fromUserId;
		$item->news_id = $newsId;
		$item->status  = UserListItem::STATUS_PENDING;
		$this->itemsRepo()->saveEntity($item);

		AuditLogger::info(__('Предложена новость в список'), [
			'list_id' => $listId,
			'news_id' => $newsId,
			'from'    => $fromUserId,
			'owner'   => $ownerId,
		]);

		return $item;
	}

	public function moderateSuggestion(int $itemId, int $ownerId, bool $approve): void {
		$item = $this->itemsRepo()->findOneById($itemId);

		if($item === null || $item->status !== UserListItem::STATUS_PENDING) {
			throw new RuntimeException(__('Предложение не найдено'));
		}

		$list = $this->listsRepo()->findOneById($item->list_id);

		if($list === null || (int) $list->owner_id !== $ownerId) {
			throw new RuntimeException(__('Нет прав на модерацию'));
		}

		if(!$approve) {
			$this->itemsRepo()->deleteEntity($item);
			AuditLogger::info(__('Предложение отклонено'), [
				'item_id' => $itemId,
				'owner'   => $ownerId,
			]);

			return;
		}

		// Одобрение: элемент остаётся с user_id предлагающего, status approved —
		// в публичном просмотре показываем все approved на list_id.
		$item->status = UserListItem::STATUS_APPROVED;
		$this->itemsRepo()->saveEntity($item);

		AuditLogger::info(__('Предложение одобрено'), [
			'item_id' => $itemId,
			'owner'   => $ownerId,
		]);
	}

	/**
	 * Списки для модалки новости: админ + свои, с флагом наличия новости.
	 *
	 * @return list<array<string, mixed>>
	 */
	public function modalPayload(int $userId, int $newsId): array {
		$checked = array_fill_keys(
			$this->itemsRepo()->newsIdsInListsForUser($userId, $newsId),
			true,
		);
		$rows = [];

		foreach($this->listsRepo()->findAdminLists() as $list) {
			$rows[] = [
				'id'       => $list->id(),
				'name'     => $list->name,
				'type'     => $list->type,
				'checked'  => isset($checked[$list->id()]),
				'deletable'=> false,
			];
		}

		foreach($this->listsRepo()->findOwnedBy($userId) as $list) {
			$rows[] = [
				'id'       => $list->id(),
				'name'     => $list->name,
				'type'     => $list->type,
				'checked'  => isset($checked[$list->id()]),
				'deletable'=> true,
			];
		}

		return $rows;
	}

	private function nextAdminPosition(): int {
		$max = 0;

		foreach($this->listsRepo()->findAdminLists() as $list) {
			$max = max($max, $list->position);
		}

		return $max + 1;
	}

	private function nextUserPosition(int $ownerId): int {
		$max = 0;

		foreach($this->listsRepo()->findOwnedBy($ownerId) as $list) {
			$max = max($max, $list->position);
		}

		return $max + 1;
	}

}
