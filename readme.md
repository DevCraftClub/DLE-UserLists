# Пользовательские списки (UserLists)

Сателлит [DevCraft Admin](https://readme.devcraft.club/dev/dle/devcraft_admin/getting_started) для DataLife Engine: люди собирают новости в **свои списки**, администратор задаёт **общие имена** («В планах», «Просмотрено»). Штатное «Избранное» DLE модуль не трогает.

**Версия:** 200.1.0

## Документация на сайте

| Страница | Адрес |
| -------- | ----- |
| Начало работы | https://readme.devcraft.club/dev/dle/user_lists/200.1.0/getting_started |
| Установка | https://readme.devcraft.club/dev/dle/user_lists/200.1.0/install |
| Подключение в теме | https://readme.devcraft.club/dev/dle/user_lists/200.1.0/guides/theme |
| Общая установка плагинов | https://readme.devcraft.club/instructions/install_instructions |
| Публичные стили и скрипты | https://readme.devcraft.club/dev/dle/devcraft_admin/200.4.1/guides/public_assets |

## Требования

| Компонент | Минимум |
| --------- | ------- |
| DataLife Engine | **≥ 21.0** |
| PHP | **≥ 8.3** |
| DevCraft Admin | **≥ 200.4.1** |

Сначала установите и включите DevCraft Admin, затем **UserLists**.

## Установка

Как собрать архив или поставить zip — [Установка плагинов](https://readme.devcraft.club/instructions/install_instructions). В комплекте: `install_archive.sh` / `install_archive.bat`, затем **Панель управления → Плагины**. Нужен `install.xml` в корне архива (`needplugin` = DevCraft Admin). Отдельный SQL-файл регистрации не используется.

После включения откройте `?mod=user_lists`. Таблицы `dc_user_lists*` появятся при первом заходе. Затем права групп и вставки в тему.

Библиотеки PHP обычно ставятся сами (скрипт установки или раздел Composer в админке). Вручную — только если автоматом не вышло: [Composer](https://readme.devcraft.club/instructions/composer).

## Вставки в тему

Стили и скрипты оболочки — не через include. В `main.tpl`: `{devcraft-header}` в `<head>` и `{devcraft-scripts}` перед `</body>`.

Кнопка и окно на новости:

```smarty
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists.php?news_id={news-id}&focus=button"}
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists.php?news_id={news-id}&focus=modal"}
```

Страницы списков:

```smarty
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=mine"}
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=catalog"}
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=proposals"}
{include file="devcraft/src/modules/UserLists/Controller/show_user_lists_page.php?focus=view&list_id=1"}
```

Пути `engine/modules/devcraft/user_lists*.php` больше не входят в пакет.

Разметка: `templates/{skin}/devcraft/user_lists/` (`button.tpl`, `modal.tpl`, `page.tpl`, `view.tpl`, `proposals.tpl`). Нет каталога в своей теме — скопируйте из `Default`.

Запросы: `devcraft/ajax.php` (`mod=user_lists`). Отдельный `engine/ajax/user_lists.php` не создаём.

Код модуля: `UserLists`. Код плагина в DLE: `user_lists`. Точка входа админки: `engine/inc/user_lists.php`.

## Лицензия

MIT — см. [LICENSE](LICENSE).
