<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DevCraft\Core\Abstracts\AbstractPage;
use DevCraft\Core\Interfaces\SettingsPageInterface;

/**
 * Настройки UserLists.
 */
final class SettingsPage extends AbstractPage implements SettingsPageInterface {

	public function handle(): array {
		$this->addBreadcrumb(__('Настройки'));

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
