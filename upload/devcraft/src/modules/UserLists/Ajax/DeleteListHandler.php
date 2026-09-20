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
 * Удаление списка.
 */
final class DeleteListHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$actorId = (int) ($member_id['user_id'] ?? 0);
		$asAdmin = !empty($request->data['_admin']) || ($request->controller ?? '') === 'admin';
		$service = new ListService();
		$id      = (int) ($request->data['id'] ?? 0);
		$list    = $service->listsRepo()->findOneById($id);

		if($list === null) {
			return JsonResponse::fail(__('Ошибка'), __('Список не найден'), 'not_found', 404);
		}

		try {
			$service->deleteList($list, $actorId, $asAdmin);

			return JsonResponse::toast(__('Удалено'), ['id' => $id]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
