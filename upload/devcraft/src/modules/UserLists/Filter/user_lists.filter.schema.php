<?php

declare(strict_types=1);

use DevCraft\Builders\FilterSchemaBuilder;
use DevCraft\Types\FormSection;

/**
 * Фильтр пользовательских списков.
 */
return FilterSchemaBuilder::create()
	->defaultOrder('id')
	->sortColumns([
		'id'         => '#',
		'name'       => __('Название'),
		'owner_id'   => __('Владелец'),
		'visibility' => __('Видимость'),
	])
	->addSection(FormSection::fromArray([
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
	]))
	->build();
