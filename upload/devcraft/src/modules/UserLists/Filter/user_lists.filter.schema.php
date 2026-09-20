<?php

declare(strict_types=1);

/**
 * Фильтр пользовательских списков.
 *
 * @return array{
 *     sort: array{default: string, columns: array<string, string>},
 *     sections: list<array{title: string, fields: list<array{id: string, type: string, label: string, metro?: array<string, mixed>}>}>,
 * }
 */
return [
	'sort'     => [
		'default' => 'id',
		'columns' => [
			'id'         => '#',
			'name'       => __('Название'),
			'owner_id'   => __('Владелец'),
			'visibility' => __('Видимость'),
		],
	],
	'sections' => [
		[
			'title'  => __('Фильтр'),
			'fields' => [
				[
					'id'    => 'name',
					'type'  => 'text',
					'label' => __('Название'),
					'metro' => ['db_column' => 'name'],
				],
				[
					'id'    => 'owner_id',
					'type'  => 'text',
					'label' => __('ID владельца'),
					'metro' => ['db_column' => 'owner_id'],
				],
				[
					'id'    => 'visibility',
					'type'  => 'text',
					'label' => __('Видимость (private/public)'),
					'metro' => ['db_column' => 'visibility'],
				],
			],
		],
	],
];
