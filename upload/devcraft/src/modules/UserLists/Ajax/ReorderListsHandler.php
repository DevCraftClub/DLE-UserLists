<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\UserLists\Services\ListService;
use Throwable;

/**
 * Перестроение порядка списков (сайт или админка).
 */
final class ReorderListsHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$actorId = (int) ($member_id['user_id'] ?? 0);

		if($actorId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Требуется авторизация'), 'auth', 403);
		}

		$asAdmin = !empty($request->data['_admin']) || ($request->controller ?? '') === 'admin';
		$scope   = (string) ($request->data['scope'] ?? 'user');
		$ids     = $request->data['ids'] ?? [];

		if(!is_array($ids)) {
			$ids = [];
		}

		$ids = array_values(array_filter(array_map('intval', $ids)));

		try {
			$t0      = hrtime(true);
			$service = new ListService();

			if($scope === 'admin') {
				if(!$asAdmin) {
					return JsonResponse::fail(__('Ошибка'), __('Недостаточно прав'), 'auth', 403);
				}

				$service->reorderAdminLists($ids);
			} elseif($asAdmin) {
				$service->reorderListedUserLists($ids);
			} else {
				$service->reorderUserLists($actorId, $ids);
			}

			$handlerMs = (hrtime(true) - $t0) / 1e6;

			return JsonResponse::toast(__('Порядок сохранён'), [
				'timing' => [
					'handler_ms' => round($handlerMs, 2),
					'scope'      => $scope,
					'ids'        => count($ids),
				],
			]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
