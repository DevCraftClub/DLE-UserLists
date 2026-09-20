<?php

declare(strict_types=1);

namespace DevCraft\Modules\UserLists\Services;

use DevCraft\Core\Logging\LogGenerator;

/**
 * Аудит действий UserLists через LogGenerator (info).
 */
final class AuditLogger {

	public static function info(string $message, array $context = []): void {
		$suffix = $context === []
			? ''
			: ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);

		LogGenerator::for('UserLists')->log($message . $suffix, 'info');
	}

}
