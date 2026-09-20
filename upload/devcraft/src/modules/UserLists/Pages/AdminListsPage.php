<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DLEPlugins;
use DevCraft\Core\Application;
use DevCraft\Types\FilterSchema;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Admin\FilterFormService;
use DevCraft\Modules\UserLists\Models\UserList;
use DevCraft\Modules\UserLists\Repositories\UserListRepository;
use DevCraft\Modules\UserLists\Services\ListService;
use DevCraft\Modules\UserLists\Services\SeedService;
use DevCraft\Modules\UserLists\UserListsIdentity;

/**
 * CRUD админ-списков с фильтрами.
 */
final class AdminListsPage extends AbstractPage {

	public function handle(): array {
		$this->addBreadcrumb(__('Админ-списки'));

		$service = new ListService();
		(new SeedService())->ensureDefaults($service);

		$filterService = new FilterFormService();
		$query         = $filterService->parseRequestQuery();
		$schema        = $this->loadFilterSchema();
		$order         = FilterFormService::normalizeOrder(
			(string) ($query['order'] ?? $schema->defaultOrder),
			$schema,
		);
		$sort          = strtoupper((string) ($query['sort'] ?? 'ASC'));
		$perPage       = FilterFormService::resolveListCount();
		$page          = max(1, (int) ($query['page'] ?? 1));
		$rules         = $filterService->parseRules($query);
		$criteria      = $filterService->rulesToCriteria($rules, $schema);
		$criteria[]    = ['column' => 'type', 'op' => 'in', 'value' => [UserList::TYPE_ADMIN]];

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

		$rows = [];

		foreach($result['items'] as $item) {
			/** @var UserList $item */
			$rows[] = [
				'id'       => $item->id(),
				'name'     => $item->name,
				'position' => $item->position,
			];
		}

		$total      = (int) $result['total'];
		$totalPages = max(1, (int) ceil($total / max(1, $perPage)));

		return [
			'view' => 'userlists/admin_lists.twig',
			'data' => [
				'page_title'     => __('Админ-списки'),
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
				'action' => 'admin_lists',
				'page'   => $page,
			]);
			$urls[$page] = http_build_query($params);
		}

		return $urls;
	}

	private function loadFilterSchema(): FilterSchema {
		/** @var array<string, mixed> $raw */
		$raw = require DLEPlugins::Check(__DIR__ . '/../Filter/admin_lists.filter.schema.php');

		return FilterSchema::fromArray($raw);
	}

}
