(function (window) {
	'use strict';

	function t(phrase) {
		return typeof window.__ === 'function' ? window.__(phrase) : phrase;
	}

	function boot($) {
		if (!$ || typeof $.fn.dialog !== 'function') {
			console.error('[UserLists] Нужны jQuery и окно jQuery UI.');
			return;
		}

		function notifyError(message) {
			if (window.DevCraftPublic && DevCraftPublic.Ajax && typeof DevCraftPublic.Ajax.notify === 'function') {
				DevCraftPublic.Ajax.notify(t('Ошибка'), message, 'error');
				return;
			}
			console.error('[UserLists]', message);
		}

		function notifyOk(message) {
			if (window.DevCraftPublic && DevCraftPublic.Ajax && typeof DevCraftPublic.Ajax.notify === 'function') {
				DevCraftPublic.Ajax.notify(t('Готово'), message, 'success');
				return;
			}
		}

		function publicPost(method, data) {
			if (!window.DevCraftPublic || !DevCraftPublic.Ajax) {
				return Promise.reject(new Error(t('Клиент отправки не загружен')));
			}
			return DevCraftPublic.Ajax.post('user_lists', method, data || {});
		}

		function getModal(newsId) {
			return $('#user-lists-modal-' + newsId);
		}

		function renderLists(newsId, lists) {
			var $body = getModal(newsId).find('.ul-lists-body');
			var html = '';
			(lists || []).forEach(function (list) {
				html += '<label><input type="checkbox" class="ul-list-toggle" data-list-id="' + list.id
					+ '" data-news-id="' + newsId + '"' + (list.checked ? ' checked' : '') + '> '
					+ $('<div>').text(list.name).html()
					+ (list.type === 'admin' ? ' <small>(' + t('админ') + ')</small>' : '')
					+ '</label>';
			});
			$body.html(html || '<p>' + t('Нет списков') + '</p>');
		}

		function loadLists(newsId) {
			return publicPost('modal_lists', { news_id: newsId }).then(function (res) {
				var data = (res && res.data) ? res.data : res;
				renderLists(newsId, (data && data.lists) ? data.lists : []);
			}).catch(function (err) {
				notifyError((err && err.message) ? err.message : t('Не удалось загрузить списки'));
			});
		}

		$(document).on('click', '.user-lists-open', function (e) {
			e.preventDefault();
			var newsId = parseInt($(this).attr('data-news-id'), 10) || 0;
			if (!newsId) {
				return;
			}
			var $modal = getModal(newsId);
			if (!$modal.length) {
				notifyError(t('Окно списков не найдено'));
				return;
			}
			loadLists(newsId).then(function () {
				if (!$modal.data('ui-dialog')) {
					$modal.dialog({
						autoOpen: false,
						width: 420,
						modal: true,
					});
				}
				$modal.dialog('open');
			});
		});

		$(document).on('change', '.ul-list-toggle', function () {
			var $el = $(this);
			var listId = parseInt($el.attr('data-list-id'), 10) || 0;
			var newsId = parseInt($el.attr('data-news-id'), 10) || 0;
			publicPost('toggle_item', { list_id: listId, news_id: newsId }).then(function (res) {
				var data = (res && res.data) ? res.data : res;
				$el.prop('checked', !!(data && data.in_list));
			}).catch(function (err) {
				$el.prop('checked', !$el.prop('checked'));
				notifyError((err && err.message) ? err.message : t('Ошибка'));
			});
		});

		$(document).on('click', '.ul-create-btn', function (e) {
			e.preventDefault();
			var newsId = parseInt($(this).attr('data-news-id'), 10) || 0;
			var name = String(getModal(newsId).find('.ul-new-name').val() || '').trim();
			if (!name) {
				notifyError(t('Укажите название'));
				return;
			}
			publicPost('create_list', { name: name }).then(function () {
				getModal(newsId).find('.ul-new-name').val('');
				notifyOk(t('Список создан'));
				return loadLists(newsId);
			}).catch(function (err) {
				notifyError((err && err.message) ? err.message : t('Ошибка'));
			});
		});

		$(document).on('click', '.ul-approve, .ul-reject', function (e) {
			e.preventDefault();
			var id = parseInt($(this).attr('data-id'), 10) || 0;
			var approve = $(this).hasClass('ul-approve') ? 1 : 0;
			publicPost('moderate_suggestion', { item_id: id, approve: approve }).then(function () {
				$('li[data-item-id="' + id + '"]').remove();
				notifyOk(approve ? t('Одобрено') : t('Отклонено'));
			}).catch(function (err) {
				notifyError((err && err.message) ? err.message : t('Ошибка'));
			});
		});
	}

	if (window.jQuery) {
		window.jQuery(boot);
	} else {
		document.addEventListener('DOMContentLoaded', function () {
			if (window.jQuery) {
				boot(window.jQuery);
			}
		});
	}
})(window);
