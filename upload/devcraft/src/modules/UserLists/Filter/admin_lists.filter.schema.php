<?php

declare(strict_types=1);

use DevCraft\Builders\FilterSchemaBuilder;
use DevCraft\Types\FormSection;

/**
 * Фильтр админ-списков.
 */
return FilterSchemaBuilder::create()
	->defaultOrder('position')
	->sortColumns([
		'id'       => '#',
		'name'     => __('Название'),
		'position' => __('Порядок'),
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
		],
	]))
	->build();
