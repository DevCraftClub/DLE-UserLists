<?php

declare(strict_types=1);

/**
 * Фильтр админ-списков.
 *
 * @return array{
 *     sort: array{default: string, columns: array<string, string>},
 *     sections: list<array{title: string, fields: list<array{id: string, type: string, label: string, metro?: array<string, mixed>}>}>,
 * }
 */
return [
	'sort'     => [
		'default' => 'position',
		'columns' => [
			'id'       => '#',
			'name'     => __('Название'),
			'position' => __('Порядок'),
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
			],
		],
	],
];
