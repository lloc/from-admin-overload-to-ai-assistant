<?php

declare(strict_types=1);

/**
 * Fix loopback HTTP requests in wp-env.
 *
 * wp-env maps the site to localhost:8888 externally, but inside the Docker
 * container the web server listens on port 80. Outgoing HTTP requests from
 * PHP that target localhost:8888 therefore fail. This mu-plugin rewrites
 * those requests to localhost:80 before they are dispatched.
 */

/**
 * @param false|array<string,mixed> $preempt
 * @param array<string,mixed>       $args
 * @return false|array<string,mixed>|\WP_Error
 */
function wp_env_fix_loopback_url( false|array $preempt, array $args, string $url ): false|array|\WP_Error {
	if ( false !== $preempt || ! str_contains( $url, 'localhost:8888' ) ) {
		return $preempt;
	}

	$url = str_replace( 'localhost:8888', 'wordpress', $url );

	remove_filter( 'pre_http_request', 'wp_env_fix_loopback_url', 10 );
	$response = wp_remote_request( $url, $args );
	add_filter( 'pre_http_request', 'wp_env_fix_loopback_url', 10, 3 );

	return $response;
}

add_filter( 'pre_http_request', 'wp_env_fix_loopback_url', 10, 3 );