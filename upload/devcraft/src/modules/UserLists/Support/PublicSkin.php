<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Support;

/**
 * Скин темы для публичных шаблонов UserLists.
 */
final class PublicSkin {

	/**
	 * Каталог темы с `devcraft/user_lists/` (текущий скин или Default).
	 *
	 * @param   array<string, mixed>  $config
	 */
	public static function resolve(array $config): string {
		$skin = totranslit((string) ($config['skin'] ?? 'Default'), false, false);

		if(!is_dir(ROOT_DIR . '/templates/' . $skin . '/devcraft/user_lists')) {
			$skin = 'Default';
		}

		return $skin;
	}

}
