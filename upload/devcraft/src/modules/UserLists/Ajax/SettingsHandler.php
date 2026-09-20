<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Ajax;

use DevCraft\Core\Http\JsonResponse;
use DevCraft\Types\FormSchema;
use DevCraft\Core\Abstracts\AbstractSettingsHandler;
use DevCraft\Modules\UserLists\Services\ConfigNormalizer;

/**
 * Сохранение настроек UserLists.
 */
final class SettingsHandler extends AbstractSettingsHandler {

	protected function configName(): ?string {
		return null;
	}

	/**
	 * @param   array<string, mixed>  $existing
	 * @param   array<string, mixed>  $valid
	 *
	 * @return array<string, mixed>
	 */
	protected function prepareConfig(array $existing, array $valid, FormSchema $schema): array|JsonResponse {
		$normalizer = new ConfigNormalizer();

		return $normalizer->normalize(array_merge($normalizer->normalize($existing), $valid));
	}

}
