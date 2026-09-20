(function (window) {
	'use strict';

	if (!window.DevCraft) {
		console.error('[UserLists] Сначала должен быть загружен DevCraft core.');
		return;
	}

	const Ajax = window.DevCraft.Ajax;
	const Metro = window.DevCraft.Metro;

	function t(key) {
		return window.__ ? window.__(key) : key;
	}

	function post(method, data) {
		return Ajax.post(method, data || {}).then(function (payload) {
			if (Ajax.handleNotice) {
				Ajax.handleNotice(payload);
			}
			return payload;
		});
	}

	/**
	 * POST без полноэкранного лоадера (DnD и быстрые действия).
	 */
	function postSilent(method, data) {
		const params = { controller: 'admin', method: method };
		const mod = document.body.dataset.mod;

		if (mod) {
			params.mod = mod;
		}

		const url = Ajax.url(Ajax.baseUrl(), params);
		const body = new URLSearchParams({
			user_hash: Ajax.getUserHash(),
			data: JSON.stringify(data || {}),
		}).toString();

		return fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body,
		}).then(Ajax.parseResponse).then(function (payload) {
			if (Ajax.handleNotice) {
				Ajax.handleNotice(payload);
			}
			return payload;
		});
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function noticeOk(title) {
		if (Metro && typeof Metro.toast === 'function') {
			Metro.toast(title || t('Готово'));
			return;
		}
		if (Metro && typeof Metro.notify === 'function') {
			Metro.notify(title || t('Готово'));
		}
	}

	/**
	 * @returns {Promise<string|null>}
	 */
	function askAdminListName() {
		return new Promise(function (resolve) {
			var pending;
			var settled = false;

			function done(value) {
				if (settled) {
					return;
				}
				settled = true;
				resolve(value);
			}

			if (!Metro || typeof Metro.dialogCreate !== 'function') {
				var fallback = window.prompt(t('Название админ-списка'), '');
				done(fallback === null ? null : String(fallback).trim());
				return;
			}

			Metro.dialogCreate({
				title: t('Создать админ-список'),
				content: '<label class="d-block mb-1">' + escapeHtml(t('Название')) + '</label>'
					+ '<input type="text" id="dc-ul-admin-name" class="metro-input" style="width:100%" />',
				closeButton: true,
				defaultActions: false,
				onClose: function () {
					done(pending === undefined ? null : pending);
				},
				customButtons: [
					{
						text: t('Сохранить'),
						cls: 'primary js-dialog-close',
						onclick: function () {
							var el = document.getElementById('dc-ul-admin-name');
							pending = el ? String(el.value).trim() : '';
							done(pending || null);
						},
					},
					{
						text: t('Отмена'),
						cls: 'js-dialog-close',
						onclick: function () {
							pending = null;
							done(null);
						},
					},
				],
			});
		});
	}

	/**
	 * @param {Array<{id:number,name:string}>} owners
	 * @returns {Promise<{owner_id:number,name:string}|null>}
	 */
	function askUserListCreate(owners) {
		return new Promise(function (resolve) {
			var pending;
			var settled = false;

			function done(value) {
				if (settled) {
					return;
				}
				settled = true;
				resolve(value);
			}

			var optionsHtml = (owners || []).map(function (u) {
				return '<option value="' + escapeHtml(String(u.id)) + '">'
					+ escapeHtml(u.name) + '</option>';
			}).join('');

			if (!Metro || typeof Metro.dialogCreate !== 'function') {
				var ownerId = owners && owners[0] ? owners[0].id : 0;
				var name = window.prompt(t('Название списка'), '');
				done(name === null || !String(name).trim()
					? null
					: { owner_id: ownerId, name: String(name).trim() });
				return;
			}

			Metro.dialogCreate({
				title: t('Создать список пользователя'),
				content: '<label class="d-block mb-1">' + escapeHtml(t('Владелец')) + '</label>'
					+ '<select id="dc-ul-user-owner" class="metro-input" style="width:100%">'
					+ optionsHtml + '</select>'
					+ '<label class="d-block mb-1 mt-2">' + escapeHtml(t('Название')) + '</label>'
					+ '<input type="text" id="dc-ul-user-name" class="metro-input" style="width:100%" />'
					+ '<p class="text-small fg-gray mt-2 mb-0">'
					+ escapeHtml(t('Список создаётся приватным. Лимит группы для админа не действует.'))
					+ '</p>',
				closeButton: true,
				defaultActions: false,
				onClose: function () {
					done(pending === undefined ? null : pending);
				},
				customButtons: [
					{
						text: t('Сохранить'),
						cls: 'primary js-dialog-close',
						onclick: function () {
							var sel = document.getElementById('dc-ul-user-owner');
							var nameEl = document.getElementById('dc-ul-user-name');
							var ownerId = sel ? parseInt(sel.value, 10) || 0 : 0;
							var name = nameEl ? String(nameEl.value).trim() : '';
							pending = ownerId > 0 && name ? { owner_id: ownerId, name: name } : null;
							done(pending);
						},
					},
					{
						text: t('Отмена'),
						cls: 'js-dialog-close',
						onclick: function () {
							pending = null;
							done(null);
						},
					},
				],
			});
		});
	}

	/**
	 * @returns {Promise<boolean>}
	 */
	function confirmDelete() {
		return new Promise(function (resolve) {
			var pending;
			var settled = false;

			function done(value) {
				if (settled) {
					return;
				}
				settled = true;
				resolve(value);
			}

			if (!Metro || typeof Metro.dialogCreate !== 'function') {
				done(window.confirm(t('Удалить список?')));
				return;
			}

			Metro.dialogCreate({
				title: t('Удалить список?'),
				content: '<p>' + escapeHtml(t('Действие необратимо.')) + '</p>',
				closeButton: true,
				defaultActions: false,
				onClose: function () {
					done(pending === true);
				},
				customButtons: [
					{
						text: t('Удалить'),
						cls: 'alert js-dialog-close',
						onclick: function () {
							pending = true;
							done(true);
						},
					},
					{
						text: t('Отмена'),
						cls: 'js-dialog-close',
						onclick: function () {
							pending = false;
							done(false);
						},
					},
				],
			});
		});
	}

	function collectRowIds(tbody) {
		return Array.from(tbody.querySelectorAll('tr[data-id]')).map(function (tr) {
			return parseInt(tr.getAttribute('data-id'), 10);
		}).filter(Boolean);
	}

	function flagKeyFromName(name) {
		var m = String(name || '').match(/^groups\[\d+]\[(.+)]$/);
		if (m) {
			return m[1];
		}
		m = String(name || '').match(/^flags\[(.+)]$/);
		return m ? m[1] : null;
	}

	function collectPermFlags(scope) {
		var flags = {};

		scope.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
			var key = flagKeyFromName(cb.getAttribute('name'));
			if (key) {
				flags[key] = cb.checked ? 1 : 0;
			}
		});

		scope.querySelectorAll('input[type="number"]').forEach(function (inp) {
			var key = flagKeyFromName(inp.getAttribute('name'));
			if (key) {
				var n = parseInt(inp.value, 10);
				flags[key] = Number.isFinite(n) ? n : 0;
			}
		});

		return flags;
	}

	/** Собирает настройки всех вкладок групп. */
	function collectAllGroupPermissions(root) {
		var groups = {};
		var scope = root.closest('.dc-ul-permissions') || root;
		scope.querySelectorAll('.dc-ul-perm-panel[data-group-id]').forEach(function (panel) {
			var groupId = parseInt(panel.getAttribute('data-group-id'), 10) || 0;
			if (groupId > 0) {
				groups[String(groupId)] = collectPermFlags(panel);
			}
		});
		return groups;
	}

	function collectSuggestorIds(form) {
		var sel = form.querySelector('[name="suggestor_ids[]"], #ul-edit-suggestors');
		if (!sel) {
			return [];
		}
		return Array.from(sel.selectedOptions || []).map(function (opt) {
			return parseInt(opt.value, 10);
		}).filter(Boolean);
	}

	function bindTableDnD(table) {
		if (!table || table.dataset.ulDndBound) {
			return;
		}

		table.dataset.ulDndBound = '1';
		var scope = table.getAttribute('data-ul-dnd-scope') || 'user';
		var tbody = table.tBodies[0];
		var dragRow = null;
		var reorderTimer = null;

		if (!tbody) {
			return;
		}

		/** Оптимистичный UI уже обновлён — без полноэкранного лоадера. */
		function scheduleReorderSave() {
			if (reorderTimer) {
				clearTimeout(reorderTimer);
			}
			reorderTimer = setTimeout(function () {
				reorderTimer = null;
				var ids = collectRowIds(tbody);
				var debugOn = window.DevCraft && DevCraft.Debug && DevCraft.Debug.isEnabled();
				var t0 = debugOn ? performance.now() : 0;

				if (debugOn) {
					DevCraft.Debug.log('UserLists', 'reorder_lists → start', { scope: scope, ids: ids.length });
				}

				postSilent('reorder_lists', {
					scope: scope,
					ids: ids,
					_admin: 1,
				}).then(function (payload) {
					if (!debugOn) {
						return;
					}
					var rtt = Math.round((performance.now() - t0) * 100) / 100;
					var data = payload && payload.data ? payload.data : {};
					var timing = data.timing || null;
					DevCraft.Debug.log('UserLists', 'reorder_lists ← done', {
						rtt_ms: rtt,
						handler_ms: timing ? timing.handler_ms : null,
						pipeline_ms: data.pipeline_ms || null,
						scope: timing ? timing.scope : scope,
						ids: timing ? timing.ids : ids.length,
						success: !(payload && payload.success === false),
					});
				}).catch(function (err) {
					if (!debugOn) {
						return;
					}
					var rtt = Math.round((performance.now() - t0) * 100) / 100;
					DevCraft.Debug.log('UserLists', 'reorder_lists ← fail', { rtt_ms: rtt, err: err });
				});
			}, 120);
		}

		tbody.addEventListener('dragstart', function (event) {
			var row = event.target.closest('tr.ul-dnd-row');
			if (!row || !tbody.contains(row)) {
				return;
			}
			dragRow = row;
			row.classList.add('ul-dnd-dragging');
			if (event.dataTransfer) {
				event.dataTransfer.effectAllowed = 'move';
				event.dataTransfer.setData('text/plain', row.getAttribute('data-id') || '');
			}
		});

		tbody.addEventListener('dragend', function () {
			if (dragRow) {
				dragRow.classList.remove('ul-dnd-dragging');
			}
			dragRow = null;
			tbody.querySelectorAll('.ul-dnd-over').forEach(function (el) {
				el.classList.remove('ul-dnd-over');
			});
		});

		tbody.addEventListener('dragover', function (event) {
			event.preventDefault();
			var row = event.target.closest('tr.ul-dnd-row');
			if (!row || row === dragRow) {
				return;
			}
			tbody.querySelectorAll('.ul-dnd-over').forEach(function (el) {
				el.classList.remove('ul-dnd-over');
			});
			row.classList.add('ul-dnd-over');
			if (event.dataTransfer) {
				event.dataTransfer.dropEffect = 'move';
			}
		});

		tbody.addEventListener('drop', function (event) {
			event.preventDefault();
			var target = event.target.closest('tr.ul-dnd-row');
			if (!dragRow || !target || dragRow === target) {
				return;
			}

			var rows = Array.from(tbody.querySelectorAll('tr.ul-dnd-row'));
			var from = rows.indexOf(dragRow);
			var to = rows.indexOf(target);
			if (from < 0 || to < 0) {
				return;
			}

			if (from < to) {
				tbody.insertBefore(dragRow, target.nextSibling);
			} else {
				tbody.insertBefore(dragRow, target);
			}

			scheduleReorderSave();
		});
	}

	function goEdit(id) {
		var mod = document.body.dataset.mod || 'user_lists';
		window.location.href = '?mod=' + encodeURIComponent(mod) + '&action=edit&id=' + encodeURIComponent(String(id));
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('table[data-ul-dnd-scope]').forEach(bindTableDnD);
	});

	document.addEventListener('click', function (event) {
		var copyBtn = event.target.closest('.js-ul-copy-code');
		if (copyBtn) {
			event.preventDefault();
			var wrap = copyBtn.closest('.remark, .d-flex, div');
			var source = wrap ? wrap.querySelector('.js-ul-copy-source') : null;
			var text = source ? String(source.textContent || '').trim() : '';
			if (!text) {
				return;
			}

			var okMsg = t('Успешно скопировано в буфер обмена');

			function copyViaTextarea() {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.setAttribute('readonly', '');
				ta.style.position = 'fixed';
				ta.style.left = '-9999px';
				document.body.appendChild(ta);
				ta.select();
				var ok = false;
				try {
					ok = document.execCommand('copy');
				} catch (e) {
					ok = false;
				}
				document.body.removeChild(ta);
				return ok;
			}

			function doneOk() {
				noticeOk(okMsg);
			}

			if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
				navigator.clipboard.writeText(text).then(doneOk).catch(function () {
					if (copyViaTextarea()) {
						doneOk();
					}
				});
			} else if (copyViaTextarea()) {
				doneOk();
			}
			return;
		}

		var createAdmin = event.target.closest('.js-ul-create-admin');
		if (createAdmin) {
			event.preventDefault();
			askAdminListName().then(function (name) {
				if (!name) {
					return;
				}
				return post('save_list', { type: 'admin', name: name, _admin: 1 }).then(function (payload) {
					var id = payload && payload.data && payload.data.id;
					if (payload && payload.success !== false && id) {
						goEdit(id);
					}
				});
			});
			return;
		}

		var createUser = event.target.closest('.js-ul-create-user');
		if (createUser) {
			event.preventDefault();
			var owners = [];
			try {
				owners = JSON.parse(createUser.getAttribute('data-owners') || '[]');
			} catch (e) {
				owners = [];
			}
			if (!owners.length) {
				window.alert(t('Нет пользователей для выбора'));
				return;
			}
			askUserListCreate(owners).then(function (data) {
				if (!data) {
					return;
				}
				return post('save_list', {
					type: 'user',
					owner_id: data.owner_id,
					name: data.name,
					_admin: 1,
				}).then(function (payload) {
					var id = payload && payload.data && payload.data.id;
					if (payload && payload.success !== false && id) {
						goEdit(id);
					}
				});
			});
			return;
		}

		var delBtn = event.target.closest('.js-ul-delete');
		if (delBtn) {
			event.preventDefault();
			var id = parseInt(delBtn.getAttribute('data-id'), 10) || 0;
			if (!id) {
				return;
			}
			confirmDelete().then(function (ok) {
				if (!ok) {
					return;
				}
				return post('delete_list', { id: id, _admin: 1 }).then(function (payload) {
					if (payload && payload.success === false) {
						return;
					}
					var row = document.querySelector('tr[data-id="' + id + '"]');
					if (row) {
						row.remove();
					}
				});
			});
		}
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest('#ul-edit-form');
		if (form) {
			event.preventDefault();
			if (typeof tinymce !== 'undefined' && tinymce.triggerSave) {
				tinymce.triggerSave();
			}
			var data = {
				id: parseInt(form.querySelector('[name=id]').value, 10) || 0,
				_admin: 1,
				name: form.querySelector('[name=name]').value,
			};
			var vis = form.querySelector('[name=visibility]');
			if (vis) {
				data.visibility = vis.value;
			}
			var desc = form.querySelector('[name=description]');
			if (desc) {
				data.description = desc.value;
			}
			var allow = form.querySelector('[name=allow_suggestions]');
			if (allow) {
				data.allow_suggestions = allow.checked ? 1 : 0;
			}
			var policy = form.querySelector('[name=suggestion_policy]');
			if (policy) {
				data.suggestion_policy = policy.value;
			}
			if (form.querySelector('#ul-edit-suggestors, [name="suggestor_ids[]"]')) {
				data.suggestor_ids = collectSuggestorIds(form);
			}
			post('save_list', data);
			return;
		}

		var permAll = event.target.closest('#dc-ul-perm-form-all, .dc-ul-perm-form-all');
		if (permAll) {
			event.preventDefault();
			var groups = collectAllGroupPermissions(permAll);
			post('permissions', { groups: groups });
		}
	});
})(window);
