<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Ajax;

use DevCraft\Core\Http\AjaxRequest;
use DevCraft\Core\Http\JsonResponse;
use DevCraft\Core\Interfaces\ResponseInterface;
use DevCraft\Core\Interfaces\AjaxHandlerInterface;
use DevCraft\Modules\UserLists\Services\ListService;
use DevCraft\Modules\UserLists\Services\PermissionService;
use DevCraft\Modules\UserLists\Services\SeedService;
use Throwable;

/**
 * Данные модалки списков для новости.
 */
final class ModalListsHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$userId = (int) ($member_id['user_id'] ?? 0);
		$newsId = (int) ($request->data['news_id'] ?? 0);

		if($userId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Требуется авторизация'), 'auth', 403);
		}

		if(!(new PermissionService())->isEnabled($userId)) {
			return JsonResponse::fail(__('Ошибка'), __('Модуль недоступен'), 'auth', 403);
		}

		if($newsId <= 0) {
			return JsonResponse::fail(__('Ошибка'), __('Не указана новость'), 'validation', 422);
		}

		try {
			$service = new ListService();
			(new SeedService())->ensureDefaults($service);

			return JsonResponse::ok([
				'lists'   => $service->modalPayload($userId, $newsId),
				'news_id' => $newsId,
			]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
