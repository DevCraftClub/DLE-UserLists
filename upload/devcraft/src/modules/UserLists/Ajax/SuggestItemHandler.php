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
 * Предложение новости в чужой публичный список.
 */
final class SuggestItemHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$userId = (int) ($member_id['user_id'] ?? 0);
		$listId = (int) ($request->data['list_id'] ?? 0);
		$newsId = (int) ($request->data['news_id'] ?? 0);

		if($userId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Требуется авторизация'), 'auth', 403);
		}

		try {
			$item = (new ListService())->suggestNews($listId, $userId, $newsId);

			return JsonResponse::toast(__('Предложение отправлено'), [
				'id'     => $item->id(),
				'status' => $item->status,
			]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
