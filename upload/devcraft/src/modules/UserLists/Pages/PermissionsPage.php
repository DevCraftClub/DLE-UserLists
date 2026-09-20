<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Modules\UserLists\Services\PermissionService;
use DevCraft\Core\Support\DleDataService;

/**
 * Права групп UserLists.
 */
final class PermissionsPage extends AbstractPage {

	public function handle(): array {
		$this->addBreadcrumb(__('Права групп'));

		$groups = DleDataService::groupsFull();
		$perms  = new PermissionService();
		$defs   = PermissionService::defs();
		$tabs   = [];

		foreach($groups as $group) {
			$gid = (int) ($group['id'] ?? 0);

			if($gid <= 0) {
				continue;
			}

			$settings = $perms->settingsForGroup($gid);
			$items    = [];

			foreach($defs as $def) {
				$id    = $def['id'];
				$type  = $def['type'] ?? 'bool';
				$value = $settings[$id] ?? ($def['default'] ?? ($type === 'int' ? 5 : false));

				$items[$id] = [
					'id'          => $id,
					'title'       => $def['title'],
					'description' => $def['description'],
					'type'        => $type,
					'checked'     => $type !== 'int' && !empty($value),
					'value'       => $type === 'int' ? (int) $value : (int) !empty($value),
				];
			}

			$tabs[] = [
				'id'    => $gid,
				'name'  => (string) ($group['group_name'] ?? ('#' . $gid)),
				'flags' => $items,
			];
		}

		return [
			'view' => 'userlists/permissions.twig',
			'data' => [
				'page_title' => __('Права групп'),
				'tabs'       => $tabs,
				'defs'       => $defs,
			],
		];
	}

}
