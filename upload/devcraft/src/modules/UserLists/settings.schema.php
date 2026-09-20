<?php

declare(strict_types=1);

use DevCraft\Modules\UserLists\UserListsIdentity;
use DevCraft\Core\Enums\FormLayout;
use DevCraft\Form\FormSchemaBuilder;

/**
 * Схема настроек UserLists.
 */
return FormSchemaBuilder::create(UserListsIdentity::code())
	->layout(FormLayout::TABS)
	->section(__('Основные'))
		->checkbox('guest_can_view_public', __('Гости могут смотреть публичные списки'))
			->description(__('Если выключено, каталог и публичные списки доступны только авторизованным.'))
			->default(false)
		->text('button_label', __('Текст кнопки на новости'))
			->description(__('Надпись на кнопке «добавить в список».'))
			->default(__('В списки'))
		->number('count_web', __('Записей на странице (сайт)'))
			->description(__('Пагинация новостей внутри списка на сайте.'))
			->default(20)
		->number('count_admin', __('Записей на странице (админка)'))
			->description(__('Пагинация таблиц в админке модуля.'))
			->default(50)
	->section(__('Ограничения'))
		->text('bad_words', __('Запрещённые слова в названии'))
			->description(__('Теги через Enter или запятую. Совпадение блокирует создание/переименование.'))
			->metro([
				'data-role'          => 'tag-input',
				'data-tag-separator' => ',',
			])
			->default('')
		->number('name_min', __('Мин. длина названия'))
			->default(2)
		->number('name_max', __('Макс. длина названия'))
			->default(100)
	->build();
