<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DevCraft\Modules\UserLists\UserListsIdentity;
use DevCraft\Core\Config\Paths;
use DevCraft\Core\Support\DataManager;
use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Interfaces\SettingsPageInterface;
use DevCraft\Modules\UserLists\Services\ConfigNormalizer;

/**
 * Настройки UserLists.
 */
final class SettingsPage extends AbstractPage implements SettingsPageInterface {

	public function handle(): array {
		$this->addBreadcrumb(__('Настройки'));

		$normalizer = new ConfigNormalizer();
		$configFile = Paths::config() . '/user_lists.json';

		if(!is_file($configFile)) {
			DataManager::saveConfig(
				UserListsIdentity::code(),
				$normalizer->normalize([]),
			);
		}

		return [
			'view' => 'userlists/settings.twig',
			'data' => [
				'page_title' => __('Настройки'),
			],
		];
	}

	public function supplementFormData(): array {
		return [];
	}

}
