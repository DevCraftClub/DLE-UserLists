<?php

declare(strict_types=1);

use DevCraft\Types\AdminLink;
use DevCraft\Types\ModuleManifest;
use DevCraft\Builders\ModuleManifestBuilder;
use DevCraft\Builders\ModuleAjaxConfigBuilder;
use DevCraft\Builders\ModuleAssetsBuilder;
use DevCraft\Builders\ModuleSiteAssetsBuilder;
use DevCraft\Modules\UserLists\UserListsIdentity;
use DevCraft\Modules\UserLists\Pages\DashboardPage;
use DevCraft\Modules\UserLists\Pages\AdminListsPage;
use DevCraft\Modules\UserLists\Pages\UserListsPage;
use DevCraft\Modules\UserLists\Pages\EditListPage;
use DevCraft\Modules\UserLists\Pages\PermissionsPage;
use DevCraft\Modules\UserLists\Pages\SettingsPage;
use DevCraft\Modules\UserLists\Pages\TemplatesPage;
use DevCraft\Modules\UserLists\Pages\ChangelogPage;
use DevCraft\Modules\UserLists\Ajax\SettingsHandler;
use DevCraft\Modules\UserLists\Ajax\PermissionsHandler;
use DevCraft\Modules\UserLists\Ajax\SaveListHandler;
use DevCraft\Modules\UserLists\Ajax\DeleteListHandler;
use DevCraft\Modules\UserLists\Ajax\ReorderListsHandler;
use DevCraft\Modules\UserLists\Ajax\ModalListsHandler;
use DevCraft\Modules\UserLists\Ajax\ToggleItemHandler;
use DevCraft\Modules\UserLists\Ajax\CreateListHandler;
use DevCraft\Modules\UserLists\Ajax\SuggestItemHandler;
use DevCraft\Modules\UserLists\Ajax\ModerateSuggestionHandler;

/**
 * Манифест модуля UserLists.
 *
 * @return ModuleManifest
 */
return ModuleManifestBuilder::create()
	->mod(UserListsIdentity::mod())
	->code(UserListsIdentity::code())
	->name('UserLists')
	->version('200.1.0')
	->description(__('Пользовательские и административные списки новостей'))
	->icon('mif-list2')
	->docsLink('https://readme.devcraft.club/dev/dle/user_lists/')
	->siteLink('https://devcraft.club/')
	->menu([
		AdminLink::page(__('Главная'), 'dashboard', DashboardPage::class, 'mif-home', UserListsIdentity::mod()),
		AdminLink::page(__('Админ-списки'), 'admin_lists', AdminListsPage::class, 'mif-folder', UserListsIdentity::mod()),
		AdminLink::page(__('Списки пользователей'), 'user_lists', UserListsPage::class, 'mif-users', UserListsIdentity::mod()),
		AdminLink::hidden('edit', EditListPage::class),
		AdminLink::page(__('Права групп'), 'permissions', PermissionsPage::class, 'mif-security', UserListsIdentity::mod()),
		AdminLink::page(__('Подключение в шаблоны'), 'templates', TemplatesPage::class, 'mif-files-empty', UserListsIdentity::mod()),
		AdminLink::page(__('Настройки'), 'settings', SettingsPage::class, 'mif-cog', UserListsIdentity::mod()),
		AdminLink::page(__('История изменений'), 'changelog', ChangelogPage::class, 'mif-library', UserListsIdentity::mod()),
	])
	->ajax(
		ModuleAjaxConfigBuilder::create('admin')
			->methods([
				'settings'             => SettingsHandler::class,
				'permissions'          => PermissionsHandler::class,
				'save_list'            => SaveListHandler::class,
				'delete_list'          => DeleteListHandler::class,
				'reorder_lists'        => ReorderListsHandler::class,
				'modal_lists'          => ModalListsHandler::class,
				'toggle_item'          => ToggleItemHandler::class,
				'create_list'          => CreateListHandler::class,
				'suggest_item'         => SuggestItemHandler::class,
				'moderate_suggestion'  => ModerateSuggestionHandler::class,
			])
			->publicMethod('modal_lists', ModalListsHandler::class, true)
			->publicMethod('toggle_item', ToggleItemHandler::class, true)
			->publicMethod('create_list', CreateListHandler::class, true)
			->publicMethod('suggest_item', SuggestItemHandler::class, true)
			->publicMethod('moderate_suggestion', ModerateSuggestionHandler::class, true)
			->publicMethod('reorder_lists', ReorderListsHandler::class, true)
			->publicMethod('save_list', SaveListHandler::class, true)
			->publicMethod('delete_list', DeleteListHandler::class, true)
	)
	->changelog(require DLEPlugins::Check(__DIR__ . '/changelog.data.php'))
	->assets(ModuleAssetsBuilder::create()->js('user_lists.js'))
	->siteAssets(
		ModuleSiteAssetsBuilder::create()
			->css('user_lists.css')
			->js('user_lists_public.js')
	)
	->build(__DIR__);
