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
 * Одобрение / отклонение предложения.
 */
final class ModerateSuggestionHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$userId  = (int) ($member_id['user_id'] ?? 0);
		$itemId  = (int) ($request->data['item_id'] ?? 0);
		$approve = !empty($request->data['approve']);

		if($userId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Требуется авторизация'), 'auth', 403);
		}

		try {
			(new ListService())->moderateSuggestion($itemId, $userId, $approve);

			return JsonResponse::toast($approve ? __('Одобрено') : __('Отклонено'));
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
