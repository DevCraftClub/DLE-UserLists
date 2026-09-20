<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Services;

/**
 * Нормализация конфига UserLists.
 */
final class ConfigNormalizer {

	/**
	 * @param   array<string, mixed>  $config
	 *
	 * @return array<string, mixed>
	 */
	public function normalize(array $config): array {
		$config = array_merge($this->defaults(), $config);

		$config['guest_can_view_public'] = !empty($config['guest_can_view_public']);
		$config['button_label']          = trim((string) ($config['button_label'] ?? __('В списки')));
		$config['count_web']             = max(1, (int) ($config['count_web'] ?? 20));
		$config['count_admin']           = max(1, (int) ($config['count_admin'] ?? 50));
		$config['bad_words']             = trim((string) ($config['bad_words'] ?? ''));
		$config['name_min']              = max(1, (int) ($config['name_min'] ?? 2));
		$config['name_max']              = max($config['name_min'], (int) ($config['name_max'] ?? 100));

		return $config;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function defaults(): array {
		return [
			'guest_can_view_public' => false,
			'button_label'          => __('В списки'),
			'count_web'             => 20,
			'count_admin'           => 50,
			'bad_words'             => '',
			'name_min'              => 2,
			'name_max'              => 100,
		];
	}

}
