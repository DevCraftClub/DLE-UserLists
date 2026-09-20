<?php

declare(strict_types=1);

/**
 * Определения прав групп UserLists.
 *
 * @return list<array{id: string, title: string, description: string, level: string, type?: string, default?: mixed}>
 */
return [
	[
		'id'          => 'enabled',
		'title'       => __('Доступ к модулю'),
		'description' => __('Группа может пользоваться пользовательскими списками на сайте.'),
		'level'       => 'user',
		'type'        => 'bool',
		'default'     => true,
	],
	[
		'id'          => 'max_lists',
		'title'       => __('Лимит своих списков'),
		'description' => __('Сколько собственных списков может создать пользователь группы. Админ-списки не считаются.'),
		'level'       => 'user',
		'type'        => 'int',
		'default'     => 5,
	],
	[
		'id'          => 'can_public',
		'title'       => __('Публичные списки'),
		'description' => __('Разрешить делать свои списки общедоступными.'),
		'level'       => 'user',
		'type'        => 'bool',
		'default'     => true,
	],
	[
		'id'          => 'can_suggest',
		'title'       => __('Предлагать в чужие списки'),
		'description' => __('Разрешить предлагать новости в публичные списки других пользователей.'),
		'level'       => 'user',
		'type'        => 'bool',
		'default'     => true,
	],
];
