<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function give_Shurjo_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=Shurjo' ),
			esc_html__( 'Settings', 'give-Shurjo' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_Shurjo_BASENAME, 'give_Shurjo_plugin_action_links' );


function give_Shurjo_plugin_row_meta( $plugin_meta, $plugin_file ) {

	if ( $plugin_file != GIVE_Shurjo_BASENAME ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'http://docs.givewp.com/addon-Shurjo' )
			),
			esc_html__( 'Documentation', 'give-Shurjo' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/addons/' )
			),
			esc_html__( 'Add-ons', 'give-Shurjo' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'give_Shurjo_plugin_row_meta', 10, 2 );
