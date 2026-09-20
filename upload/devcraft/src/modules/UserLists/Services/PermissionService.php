<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Services;

use DLEPlugins;
use DevCraft\Core\Application;
use DevCraft\Core\Support\DleDataService;
use DevCraft\Modules\UserLists\Models\GroupPermission;
use DevCraft\Modules\UserLists\Repositories\GroupPermissionRepository;

/**
 * Права групп UserLists.
 */
final class PermissionService {

	/** @var array<int, array<string, mixed>> */
	private array $cache = [];

	/**
	 * @return list<array{id: string, title: string, description: string, level: string, type?: string, default?: mixed}>
	 */
	public static function defs(): array {
		/** @var list<array{id: string, title: string, description: string, level: string, type?: string, default?: mixed}> $defs */
		$defs = require DLEPlugins::Check(
			dirname(__DIR__) . '/permissions.defs.php',
		);

		return $defs;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function settingsForGroup(int $groupId): array {
		if(isset($this->cache[$groupId])) {
			return $this->cache[$groupId];
		}

		$entity = $this->repo()->findByGroupId($groupId);
		$stored = $entity?->values() ?? [];
		$out    = [];

		foreach(self::defs() as $def) {
			$id      = $def['id'];
			$type    = $def['type'] ?? 'bool';
			$default = $def['default'] ?? ($type === 'int' ? 5 : true);

			if(array_key_exists($id, $stored)) {
				$out[$id] = $type === 'int' ? (int) $stored[$id] : !empty($stored[$id]);
			} else {
				$out[$id] = $type === 'int' ? (int) $default : !empty($default);
			}
		}

		return $this->cache[$groupId] = $out;
	}

	public function isEnabled(int $userId): bool {
		return !empty($this->settingsForUser($userId)['enabled']);
	}

	public function maxLists(int $userId): int {
		return max(0, (int) ($this->settingsForUser($userId)['max_lists'] ?? 0));
	}

	public function canPublic(int $userId): bool {
		return !empty($this->settingsForUser($userId)['can_public']);
	}

	public function canSuggest(int $userId): bool {
		return !empty($this->settingsForUser($userId)['can_suggest']);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function settingsForUser(int $userId): array {
		$groupId = $this->groupIdForUser($userId);

		if($groupId <= 0) {
			return $this->defaultsMap();
		}

		return $this->settingsForGroup($groupId);
	}

	public function groupIdForUser(int $userId): int {
		global $member_id;

		if(!empty($member_id['user_id']) && (int) $member_id['user_id'] === $userId) {
			return (int) ($member_id['user_group'] ?? 0);
		}

		$row = DleDataService::user(id: $userId);

		return (int) ($row['user_group'] ?? 0);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function defaultsMap(): array {
		$out = [];

		foreach(self::defs() as $def) {
			$type         = $def['type'] ?? 'bool';
			$default      = $def['default'] ?? ($type === 'int' ? 5 : false);
			$out[$def['id']] = $type === 'int' ? (int) $default : !empty($default);
		}

		return $out;
	}

	private function repo(): GroupPermissionRepository {
		/** @var GroupPermissionRepository $repo */
		$repo = Application::instance()->database()->repository(GroupPermission::class);

		return $repo;
	}

}
