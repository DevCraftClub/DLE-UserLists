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
 * Создание / обновление списка (админ или владелец).
 */
final class SaveListHandler implements AjaxHandlerInterface {

	public function handle(AjaxRequest $request): ResponseInterface {
		global $member_id;

		$actorId = (int) ($member_id['user_id'] ?? 0);
		$asAdmin = !empty($request->data['_admin']) || ($request->controller ?? '') === 'admin';
		$service = new ListService();

		try {
			$id = (int) ($request->data['id'] ?? 0);

			if($id <= 0) {
				$type = (string) ($request->data['type'] ?? UserList::TYPE_USER);

				if($type === UserList::TYPE_ADMIN) {
					if(!$asAdmin) {
						return JsonResponse::fail(__('Ошибка'), __('Недостаточно прав'), 'auth', 403);
					}

					$list = $service->createAdminList((string) ($request->data['name'] ?? ''), $actorId);
				} else {
					$ownerId = $actorId;

					if($asAdmin) {
						$ownerId = (int) ($request->data['owner_id'] ?? 0);

						if($ownerId <= 0) {
							return JsonResponse::fail(
								__('Ошибка'),
								__('Укажите владельца списка'),
								'validation',
								422,
							);
						}
					}

					$list = $service->createUserList(
						$ownerId,
						(string) ($request->data['name'] ?? ''),
						$asAdmin
							? UserList::VIS_PRIVATE
							: (string) ($request->data['visibility'] ?? UserList::VIS_PRIVATE),
						(string) ($request->data['description'] ?? ''),
						$asAdmin,
					);
				}
			} else {
				$list = $service->listsRepo()->findOneById($id);

				if($list === null) {
					return JsonResponse::fail(__('Ошибка'), __('Список не найден'), 'not_found', 404);
				}

				$list = $service->updateList($list, $request->data, $actorId, $asAdmin);

				if(isset($request->data['suggestor_ids']) && is_array($request->data['suggestor_ids'])) {
					$service->suggestorsRepo()->replaceForList($list->id(), $request->data['suggestor_ids']);
				}
			}

			return JsonResponse::toast(__('Сохранено'), [
				'id'   => $list->id(),
				'name' => $list->name,
			]);
		} catch(Throwable $e) {
			return JsonResponse::fail(__('Ошибка'), $e->getMessage(), 'error', 400);
		}
	}

}
