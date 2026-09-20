<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Pages;

use DevCraft\Core\Abstracts\AbstractPage;

/**
 * Инструкции по подключению в шаблоны.
 */
final class TemplatesPage extends AbstractPage {

	public function handle(): array {
		$pageName = __('Подключение в шаблоны');
		$this->addBreadcrumb($pageName);

		return [
			'view' => 'userlists/templates.twig',
			'data' => [
				'page_title' => $pageName,
			],
		];
	}

}
