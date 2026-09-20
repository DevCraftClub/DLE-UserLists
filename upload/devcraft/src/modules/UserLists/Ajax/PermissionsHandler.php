<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Ajax;

use DevCraft\Core\Application;
use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\UserLists\Models\GroupPermission;
use DevCraft\Modules\UserLists\Repositories\GroupPermissionRepository;
use DevCraft\Modules\UserLists\Services\PermissionService;

/**
 * Сохранение прав групп UserLists (все группы одним запросом).
 */
final class PermissionsHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		/** @var GroupPermissionRepository $repo */
		$repo = Application::instance()->database()->repository(GroupPermission::class);

		$batch = $request->data['groups'] ?? null;

		if(is_array($batch) && $batch !== []) {
			$saved = 0;

			foreach($batch as $groupId => $raw) {
				$gid = (int) $groupId;

				if($gid <= 0 || !is_array($raw)) {
					continue;
				}

				$repo->upsert($gid, $this->normalizeFlags($raw));
				$saved++;
			}

			if($saved === 0) {
				return JsonResponse::fail(__('Ошибка'), __('Нет групп для сохранения'), 'validation', 422);
			}

			return JsonResponse::toast(__('Сохранено для {n} групп', ['{n}' => (string) $saved]), [
				'saved' => $saved,
			]);
		}

		$groupId = (int) ($request->data['group_id'] ?? 0);

		if($groupId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Не указана группа'), 'validation', 422);
		}

		$raw = $request->data['flags'] ?? [];

		if(!is_array($raw)) {
			$raw = [];
		}

		$repo->upsert($groupId, $this->normalizeFlags($raw));

		return JsonResponse::toast(__('Сохранено'), ['group_id' => $groupId, 'saved' => 1]);
	}

	/**
	 * @param array<string, mixed> $raw
	 *
	 * @return array<string, mixed>
	 */
	private function normalizeFlags(array $raw): array {
		$values = [];

		foreach(PermissionService::defs() as $def) {
			$id   = $def['id'];
			$type = $def['type'] ?? 'bool';

			if($type === 'int') {
				$values[$id] = max(0, (int) ($raw[$id] ?? ($def['default'] ?? 0)));
			} else {
				$values[$id] = !empty($raw[$id]);
			}
		}

		return $values;
	}

}
