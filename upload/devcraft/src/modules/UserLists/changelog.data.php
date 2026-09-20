<?php

declare(strict_types=1);

use DevCraft\Builders\ChangelogBuilder;

/**
 * Журнал изменений UserLists.
 *
 * @return list<\DevCraft\Types\Changelog>
 */
return [
	ChangelogBuilder::create('200.1.0')
		->date('2026-09-10')
		->added([
			__('Каркас UserLists для DevCraft Admin и DLE 21.0.'),
			__('Публичные вставки: Controller/show_user_lists.php и show_user_lists_page.php.'),
			__('Установка через install.xml (needplugin = DevCraft Admin).'),
			__('Админ-списки с сидами «В планах», «Просмотрено», «Избрано»; содержимое per-user.'),
			__('Пользовательские списки: приват/публик, WYSIWYG-описание, лимиты по группам.'),
			__('Предложения новостей в публичные списки с одобрением владельца (whitelist или все).'),
			__('Кнопка в short/full/custom, окно выбора, публичный AJAX через devcraft/ajax.php.'),
			__('CRUD с фильтрами в админке, права групп, логи LogGenerator info.'),
		])
		->changed([
			__('Стили и скрипты сайта — Public/ + siteAssets, теги {devcraft-header} / {devcraft-scripts}.'),
			__('Форма редактирования: стандартный отступ кнопок, мультиселект предлагающих, TinyMCE для описания.'),
			__('Страница шаблонов: кнопка «Копировать» у каждого include.'),
		])
		->fixed([
			__('Права групп: одна кнопка сохраняет все группы на странице одним AJAX-запросом.'),
			__('Права групп: корректный сбор чекбоксов/лимитов, без ложного reload.'),
			__('Порядок списков: один SQL UPDATE вместо N persist; быстрее DnD.'),
			__('Медленное сохранение при создании: переход на edit без полного reload списка.'),
		])
		->removed([
			__('Legacy FavAll engine/ajax.'),
			__('Файлы engine/modules/devcraft/user_lists.php и user_lists_page.php.'),
			__('CSS и JS модуля в templates/*/devcraft/user_lists/.'),
			__('Регистрация через register_plugin.sql.'),
		])
		->build(),
];
