<?php

declare(strict_types=1);

namespace lloc\FromAdminOverloadToAIAssistant;

use lloc\FromAdminOverloadToAIAssistant\Abilities\SiteHealthAbility;
use WP\MCP\Core\McpAdapter;

class Plugin {

	public static function init(): self {
		McpAdapter::instance();

		add_action( 'wp_abilities_api_init', array( SiteHealthAbility::class, 'register' ) );

		return new self();
	}
}
