<?php
/*
 * Plugin Name: Remove Yoast SEO Comments
 * Plugin URI: https://wordpress.org/plugins/remove-yoast-seo-comments/
 * Description: Removes the Yoast SEO advertisement HTML comments from your front-end source code.
 * Version: 3.0.1
 * Author: Mitch
 * Author URI: https://profiles.wordpress.org/lowest
 * License: GPL-2.0+
 * Text Domain: rysc
 * Domain Path:
 * Network:
 * License: GPL-2.0+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class RYSC {
	private $version = '3.0.1';
	private $debug_marker_removed = false;
	private $head_marker_removed = false;
	
	public function __construct() {
		add_action( 'init', array( $this, 'bundle' ), 1);
	}
	
	public function bundle() {
		if(defined( 'WPSEO_VERSION' )) {
			$debug_marker = ( version_compare( WPSEO_VERSION, '4.4', '>=' ) ) ? 'debug_mark' : 'debug_marker';
			
			if(class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', $debug_marker )) {
				remove_action( 'wpseo_head', array( WPSEO_Frontend::get_instance(), $debug_marker ) , 2);
				$this->debug_marker_removed = true;
			}
			
			if(class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', 'head' )) {
				remove_action( 'wp_head', array( WPSEO_Frontend::get_instance(), 'head' ) , 1);
				add_action( 'wp_head', array($this, 'rewrite'), 1);
				$this->head_marker_removed = true;
			}
			
			add_action( 'wp_dashboard_setup', array( $this, 'dash_widget' ) );
		}
		
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
	}
	
	public function operating_status() {
		if($this->debug_marker_removed && $this->head_marker_removed) {
			return 1;
		} elseif(!$this->debug_marker_removed && $this->head_marker_removed || $this->debug_marker_removed && !$this->head_marker_removed) {
			return 2;
		} else {
			return 3;
		}
	}
	
	public function dash_widget() {
		wp_add_dashboard_widget( 'dashboard_widget', 'Remove Yoast SEO Comments', array( $this, 'dash_widget_content' ) );
	}
	
	public function dash_widget_content() {
		if($this->operating_status() == 1) {
			$status = '<span style="color:#04B404;font-weight:bold">Fully supported</span>';
			$content = '<p>Version ' . WPSEO_VERSION . ' of Yoast SEO is fully supported by RYSC ' . $this->version . '. The HTML comments have been removed from your front-end source code.</p>';
			$show_supported = false;
		} elseif($this->operating_status() == 2) {
			$status = '<span style="color:#FF8000;font-weight:bold">Not properly supported</span>';
			$content = '<p>Version ' . WPSEO_VERSION . ' of Yoast SEO is not properly supported by RYSC ' . $this->version . '. Some functions are not working. Please downgrade or wait for a plugin update.</p>';
			$show_supported = true;
		} elseif($this->operating_status() == 3) {
			$status = '<span style="color:#DF0101;font-weight:bold">Not supported</span>';
			$content = '<p>Version ' . WPSEO_VERSION . ' of Yoast SEO is not supported by RYSC ' . $this->version . '. Please downgrade or wait for a plugin update.</p>';
			$show_supported = true;
		}
		
		echo '<div class="activity-block"><h3><span class="dashicons dashicons-admin-plugins"></span> Yoast SEO ' . WPSEO_VERSION . ' Compatibility Status: ' . $status . '</h3></div>';
		echo $content;
		if($show_supported) {
			echo '<p style="color:#808080"><span class="dashicons dashicons-lightbulb"></span> Want to downgrade? <a href="https://downloads.wordpress.org/plugin/wordpress-seo.4.9.zip" title="Download Yoast SEO 4.9">Yoast SEO 4.9</a> and earlier are tested and supported by RYSC.</p>';
		}
	}
	
	public function rewrite() {
		$rewrite = new ReflectionMethod( 'WPSEO_Frontend', 'head' );
		
		$filename = $rewrite->getFileName();
		$start_line = $rewrite->getStartLine();
		$end_line = $rewrite->getEndLine()-1;

		$length = $end_line - $start_line;
		$source = file( $filename );
		$body = implode( '', array_slice($source, $start_line, $length) );
		$body = preg_replace( '/echo \'\<\!(.*?)\n/', '', $body);

		eval($body);
	}
	
	public function plugin_links( $link ) {
		$plugin_links = array_merge( $link, array('<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2VYPRGME8QELC" target="_blank" rel="noopener noreferrer">' . __('Donate') . '</a>') );
		
		return $plugin_links;
	}
}

$rysc = new RYSC;