<?php

declare(strict_types=1);

namespace lloc\FromAdminOverloadToAIAssistant\Abilities;

use WP_Site_Health;

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
		$site_health = WP_Site_Health::get_instance();
		if ( is_null( $site_health ) ) {
			return array(
				'summary' => array(
					'good'        => 0,
					'recommended' => 0,
					'critical'    => 0,
				),
				'results' => array(),
			);
		}

		$tests = WP_Site_Health::get_tests();
		/** @var array<string, array{test: string|callable}> $direct_tests */
		$direct_tests = is_array( $tests['direct'] ?? null ) ? $tests['direct'] : array();

		$category = $params['category'] ?? '';
		$summary  = array(
			'good'        => 0,
			'recommended' => 0,
			'critical'    => 0,
		);
		$output   = array();

		foreach ( $direct_tests as $test ) {
			$callback = $test['test'];
			if ( is_string( $callback ) ) {
				$method = 'get_test_' . $callback;
				if ( ! method_exists( $site_health, $method ) ) {
					continue;
				}
				/** @var callable $callback */
				$callback = array( $site_health, $method );
			}

			$result = call_user_func( $callback );
			if ( ! is_array( $result ) ) {
				continue;
			}
			if ( '' !== $category && ( $result['category'] ?? '' ) !== $category ) {
				continue;
			}

			$status = is_string( $result['status'] ?? null ) ? $result['status'] : 'good';
			if ( isset( $summary[ $status ] ) ) {
				++$summary[ $status ];
			}
			$output[] = array(
				'test'     => is_string( $result['test'] ?? null ) ? $result['test'] : '',
				'label'    => is_string( $result['label'] ?? null ) ? $result['label'] : '',
				'status'   => $status,
				'category' => is_string( $result['category'] ?? null ) ? $result['category'] : '',
			);
		}

		return array(
			'summary' => $summary,
			'results' => $output,
		);
	}
}
