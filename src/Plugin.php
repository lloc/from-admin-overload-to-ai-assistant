<?php

namespace FromAdminOverloadToAIAssistant\src;

use WP\MCP\Core\McpAdapter;
class Plugin {


	public static function init(): self {
		McpAdapter::instance();

		return new self();
	}
}
