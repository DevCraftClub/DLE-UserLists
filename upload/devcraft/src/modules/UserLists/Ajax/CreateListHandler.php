<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\UserLists\Models\UserList;
use DevCraft\Modules\UserLists\Services\ListService;
use Throwable;

/**
 * Быстрое создание своего списка (из модалки).
 */
final class CreateListHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$userId = (int) ($member_id['user_id'] ?? 0);

		if($userId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Требуется авторизация'), 'auth', 403);
		}

		try {
			$list = (new ListService())->createUserList(
				$userId,
				(string) ($request->data['name'] ?? ''),
				(string) ($request->data['visibility'] ?? UserList::VIS_PRIVATE),
				(string) ($request->data['description'] ?? ''),
			);

			return JsonResponse::toast(__('Список создан'), [
				'id'   => $list->id(),
				'name' => $list->name,
			]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
