<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Controller;

use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\UserLists\Support\PublicSkin;
use DevCraft\Modules\UserLists\UserListsIdentity;

/**
 * Кнопка и окно выбора списков на новости.
 *
 * Стили и скрипты сайта — `siteAssets` манифеста (`{devcraft-header}` / `{devcraft-scripts}`).
 * `focus=css` и `focus=js` оставлены пустыми, чтобы старые вставки не дублировали файлы.
 */
final class WidgetController {

	/**
	 * @param   string  $focus  button|modal|css|js
	 */
	public function render(string $focus, int $newsId): string {
		global $tpl, $is_logged, $member_id, $config, $dle_login_hash;

		$focus = $focus !== '' ? $focus : 'button';

		if(in_array($focus, ['css', 'js'], true)) {
			return '';
		}

		if($newsId <= 0) {
			return '';
		}

		$logged = !empty($is_logged) && !empty($member_id['user_id']);

		if(!$logged) {
			return '';
		}

		$cfg = DataManager::getConfig(UserListsIdentity::code());
		$buttonLabel = trim((string) ($cfg['button_label'] ?? '')) ?: __('В списки');
		$skin        = PublicSkin::resolve(is_array($config) ? $config : []);

		if(!isset($tpl) || !is_object($tpl)) {
			if(!class_exists('dle_template', false)) {
				require_once \DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php');
			}

			$tpl      = new \dle_template();
			$tpl->dir = ROOT_DIR . '/templates/' . $skin;
		}

		$restoreDir = $tpl->dir;
		$tpl->dir   = ROOT_DIR . '/templates/' . $skin;

		$tpl->set('{news-id}', (string) $newsId);
		$tpl->set('{button-label}', htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'));
		$tpl->set('{user-hash}', htmlspecialchars((string) ($dle_login_hash ?? ''), ENT_QUOTES, 'UTF-8'));
		$tpl->set('{create-label}', htmlspecialchars(__('Новый список'), ENT_QUOTES, 'UTF-8'));
		$tpl->set('{modal-title}', htmlspecialchars(__('Мои списки'), ENT_QUOTES, 'UTF-8'));

		$file = $focus === 'modal' ? 'devcraft/user_lists/modal.tpl' : 'devcraft/user_lists/button.tpl';
		$tpl->load_template($file);
		$tpl->compile('content');
		$html = (string) ($tpl->result['content'] ?? '');
		$tpl->dir = $restoreDir;

		return $html;
	}

}
