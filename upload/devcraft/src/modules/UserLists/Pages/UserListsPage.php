<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DLEPlugins;
use DevCraft\Core\Application;
use DevCraft\Types\FilterSchema;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Admin\FilterFormService;
use DevCraft\Core\Support\DleDataService;
use DevCraft\Modules\UserLists\Models\UserList;
use DevCraft\Modules\UserLists\Repositories\UserListRepository;
use DevCraft\Modules\UserLists\UserListsIdentity;

/**
 * CRUD пользовательских списков с фильтрами.
 */
final class UserListsPage extends AbstractPage {

	public function handle(): array {
		$this->addBreadcrumb(__('Списки пользователей'));

		$filterService = new FilterFormService();
		$query         = $filterService->parseRequestQuery();
		$schema        = $this->loadFilterSchema();
		$order         = FilterFormService::normalizeOrder(
			(string) ($query['order'] ?? $schema->defaultOrder),
			$schema,
		);
		$sort          = strtoupper((string) ($query['sort'] ?? 'DESC'));
		$perPage       = FilterFormService::resolveListCount();
		$page          = max(1, (int) ($query['page'] ?? 1));
		$rules         = $filterService->parseRules($query);
		$criteria      = $filterService->rulesToCriteria($rules, $schema);
		$criteria[]    = ['column' => 'type', 'op' => 'in', 'value' => [UserList::TYPE_USER]];

		/** @var UserListRepository $repository */
		$repository = Application::instance()->database()->repository(UserList::class);
		$result     = $repository->findFiltered(
			$criteria,
			$page,
			$perPage,
			$order,
			$sort,
			$schema->sortColumnKeys(),
			$schema->defaultOrder,
		);

		$userIds = [];

		foreach($result['items'] as $item) {
			/** @var UserList $item */
			if($item->owner_id !== null && $item->owner_id > 0) {
				$userIds[$item->owner_id] = $item->owner_id;
			}
		}

		$userNames = [];

		foreach($userIds as $uid) {
			$row               = DleDataService::user(id: $uid);
			$name              = trim((string) ($row['name'] ?? ''));
			$userNames[$uid] = $name !== '' ? $name : ('#' . $uid);
		}

		$rows = [];

		foreach($result['items'] as $item) {
			/** @var UserList $item */
			$oid    = (int) ($item->owner_id ?? 0);
			$rows[] = [
				'id'         => $item->id(),
				'name'       => $item->name,
				'owner_id'   => $oid,
				'owner_name' => $userNames[$oid] ?? ('#' . $oid),
				'visibility' => $item->visibility,
			];
		}

		$total      = (int) $result['total'];
		$totalPages = max(1, (int) ceil($total / max(1, $perPage)));

		$ownerOptions = [];

		foreach(DleDataService::users() as $userRow) {
			$uid = (int) ($userRow['user_id'] ?? 0);

			if($uid <= 0) {
				continue;
			}

			$uname = trim((string) ($userRow['name'] ?? ''));
			$ownerOptions[] = [
				'id'   => $uid,
				'name' => $uname !== '' ? $uname : ('#' . $uid),
			];
		}

		return [
			'view' => 'userlists/user_lists.twig',
			'data' => [
				'page_title'     => __('Списки пользователей'),
				'items'          => $rows,
				'total'          => $total,
				'per_page'       => $perPage,
				'current_page'   => min($page, $totalPages),
				'page_urls'      => $this->buildPageUrls($query, $totalPages),
				'order'          => $order,
				'sort'           => $sort,
				'filter_rules'   => $rules,
				'filter_chips'   => $filterService->buildChipViewModel($rules, $schema),
				'filter_catalog' => $filterService->buildCatalogViewModel($schema, $repository),
				'query'          => $query,
				'owner_options'  => $ownerOptions,
			],
		];
	}

	/**
	 * @param   array<string, mixed>  $query
	 *
	 * @return array<int, string>
	 */
	private function buildPageUrls(array $query, int $totalPages): array {
		$urls = [];

		for($page = 1; $page <= $totalPages; $page++) {
			$params      = array_merge($query, [
				'mod'    => UserListsIdentity::mod(),
				'action' => 'user_lists',
				'page'   => $page,
			]);
			$urls[$page] = http_build_query($params);
		}

		return $urls;
	}

	private function loadFilterSchema(): FilterSchema {
		/** @var array<string, mixed> $raw */
		$raw = require DLEPlugins::Check(__DIR__ . '/../Filter/user_lists.filter.schema.php');

		return FilterSchema::fromArray($raw);
	}

}
