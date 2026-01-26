<?php

declare(strict_types=1);

namespace lloc\FromAdminOverloadToAIAssistant\Abilities;

class SiteHealthAbility {

	public static function register(): void {
		wp_register_ability(
			'site-health/check',
			array(
				'label'               => __( 'Check Site Health', 'from-admin-overload-to-ai-assistant' ),
				'description'         => __( 'Run WordPress Site Health checks and return the results grouped by status.', 'from-admin-overload-to-ai-assistant' ),
				'category'            => 'site',
				'permission_callback' => static fn() => current_user_can( 'manage_options' ),
				'execute_callback'    => array( self::class, 'execute' ),
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'category' => array(
							'type'        => 'string',
							'description' => __( 'Filter by category: "security", "performance", or leave empty for all.', 'from-admin-overload-to-ai-assistant' ),
							'enum'        => array( '', 'security', 'performance' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'summary' => array(
							'type'       => 'object',
							'properties' => array(
								'good'        => array( 'type' => 'integer' ),
								'recommended' => array( 'type' => 'integer' ),
								'critical'    => array( 'type' => 'integer' ),
							),
						),
						'results' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'test'     => array( 'type' => 'string' ),
									'label'    => array( 'type' => 'string' ),
									'status'   => array( 'type' => 'string' ),
									'category' => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
				'meta'                => array(
					'mcp' => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			)
		);
	}

	/**
	 * @param array{category?: string} $params
	 * @return array{summary: array{good: int, recommended: int, critical: int}, results: array<int, array{test: string, label: string, status: string, category: string}>}
	 */
	public static function execute( array $params ): array {
		// Simple test first
		return array(
			'summary' => array(
				'good'        => 1,
				'recommended' => 0,
				'critical'    => 0,
			),
			'results' => array(
				array(
					'test'     => 'test',
					'label'    => 'Test passed',
					'status'   => 'good',
					'category' => 'performance',
				),
			),
		);
	}
}
