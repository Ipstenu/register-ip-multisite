<?php
/*
Plugin Name: Register IPs
Version: 1.9.1
Description: Logs the IP of the user when they register a new account.
Author: Mika Epstein, Johnny White
Author URI: http://halfelf.org
Plugin URI: http://halfelf.org/plugins/register-ip-ms
Text Domain: register-ip-multisite

Copyright 2005 Johnny White
Copyright 2010-23 Mika Epstein (ipstenu@halfelf.org)

This file is part of Register IPs, a plugin for WordPress.

Register IPs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

Register IPs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress.  If not, see <http://www.gnu.org/licenses/>.

*/

class Register_IP_Multisite {

	/**
	 * Let's get this party started.
	 *
	 * @since 1.7
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * All init functions.
	 *
	 * @since 1.7
	 * @access public
	 */

	public function init() {
		add_action( 'user_register', array( $this, 'log_ip' ) );
		add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ), 10, 1 );
		add_action( 'show_user_profile', array( $this, 'edit_user_profile' ), 10, 1 );
		add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 2 );
		add_action( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );
		add_filter( 'pre_get_users', array( $this, 'columns_sortability' ), 10, 2 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'manage_users_sortable_columns' ) );

		if ( is_multisite() ) {
			add_filter( 'wpmu_users_columns', array( $this, 'column_header_signup_ip' ) );
		} else {
			add_filter( 'manage_users_columns', array( $this, 'column_header_signup_ip' ) );
		}

	}

	/**
	 * Log the IP address
	 *
	 * @since 1.0
	 * @access public
	 */
	public function log_ip( $user_id ) {

		// Default
		$is_xfh = false;

		//Get the IP of the person registering.
		$pure_ip = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';

		// If there's forwarding going on, check if we support it before going on.
		if ( ! defined( 'REGISTER_UP_NO_FORWARD' ) || false === REGISTER_UP_NO_FORWARD ) {
			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$headers = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
				if ( isset( $headers[0] ) ) {
					$pure_ip = sanitize_text_field( $headers[0] );
					$is_xfh  = ( ! empty( $pure_ip ) ) ? true : false;
				}
			}
		}

		// If this is localhost, we should let a person know.
		$localhost = array( 'localhost', '::1', '127.0.0.1' );
		if ( in_array( $pure_ip, $localhost, true ) ) {
			$ip = 'localhost';
		} else {
			$ip = $pure_ip;
		}

		// If this was set by XFH, we put an asterisk:
		if ( $is_xfh ) {
			$ip .= '*';
		}

		// Add user metadata to the usermeta table.
		update_user_meta( $user_id, 'signup_ip', $ip );
	}

	/**
	 * Show the IP on a profile to admins only
	 *
	 * @since 1.0
	 * @access public
	 */
	public function edit_user_profile( $profileuser ) {
		if ( current_user_can( 'manage_options' ) ) {
			$user_id = $profileuser->ID;
			?>
			<h3><?php esc_html_e( 'Signup IP Address', 'register-ip-multisite' ); ?></h3>
			<p style="text-indent:15px;">
				<?php
				$ip = get_user_meta( $user_id, 'signup_ip', true );

				$is_xfh = false;

				// Remove asterisk if found to not break filter.
				if ( substr( $ip, -1 ) === '*' ) {
					$ip     = substr( $ip, 0, -1 );
					$is_xfh = true;
				}

				if ( isset( $ip ) && '' !== $ip && 'none' !== $ip ) {
					$value = $ip;
					if ( has_filter( 'ripm_show_ip' ) ) {
						$value = apply_filters( 'ripm_show_ip', $value );
					}
				} else {
					update_user_meta( $user_id, 'signup_ip', 'none' );
				}

				// Restore asterisk if it was set above.
				if ( true === $is_xfh ) {
					$value .= ' *';
				}

				echo esc_html( $value );
				?>
			</p>
			<?php
		}
	}

	/**
	 * Column Header
	 *
	 * @since 1.0
	 * @access public
	 */
	public function column_header_signup_ip( $column_headers ) {
		$column_headers['signup_ip'] = __( 'IP Address', 'register-ip-multisite' );
		return $column_headers;
	}

	/*
	 * Make Custom Columns Sortable
	 *
	 * @since 1.8.0
	 * @access public
	 */
	public function manage_users_sortable_columns( $columns ) {
		$columns['signup_ip'] = 'signup_ip';
		return $columns;
	}

	/*
	 * Create columns sortability for IP
	 *
	 * @since 1.8.0
	 * @access public
	 */
	public function columns_sortability( $query ) {
		if ( 'signup_ip' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'signup_ip' );
		}
	}

	/**
	 * Column Output
	 *
	 * @since 1.0
	 * @access public
	 */
	public function manage_users_custom_column( $value, $column_name, $user_id ) {
		if ( 'signup_ip' === $column_name ) {
			$ip     = get_user_meta( $user_id, 'signup_ip', true );
			$value  = '<em>' . __( 'None Recorded', 'register-ip-multisite' ) . '</em>';
			$is_xfh = false;

			// Remove asterisk if found to not break filter.
			if ( substr( $ip, -1 ) === '*' ) {
				$ip     = substr( $ip, 0, -1 );
				$is_xfh = true;
			}

			if ( isset( $ip ) && '' !== $ip && 'none' !== $ip ) {
				$value = $ip;
				if ( has_filter( 'ripm_show_ip' ) ) {
					$value = apply_filters( 'ripm_show_ip', $value );
				}
			} else {
				update_user_meta( $user_id, 'signup_ip', 'none' );
			}

			// Restore asterisk if it was set above.
			if ( true === $is_xfh ) {
				$value .= ' *';
			}
		}
		return $value;
	}

	/**
	 * Slap a donate link back into the plugin links. Show some love
	 *
	 * @since 1.0
	 * @access public
	 */
	public function donate_link( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$donate_link = '<a href="https://ko-fi.com/A236CEN/">Donate</a>';
			$links[]     = $donate_link;
		}
		return $links;
	}

}

new Register_IP_Multisite();
