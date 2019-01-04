<?php
/*
Plugin Name: Designit
Plugin URI: https://www.designbold.com/collection/create-new
Description: Desingbold designit build plugin allow designning image online
Version: 1.0.2
Author: DesignBold
Author URI: https://www.designbold.com/
License: GPLv2 or later
*/

/*
{Designit} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Designit} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Designit}. If not, see {Plugin URI}.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if(!class_exists('Designit')) {
	class Designit {
		function __construct() {
			global $post;
			include_once('api.php');
		}

		public function init(){
			add_action( 'admin_enqueue_scripts', array( $this, 'dbsdk_namespace_scripts_styles' ) );
			add_action( 'media_buttons', array( $this, 'dbsdk_createButton' ));
		}

		public function dbsdk_namespace_scripts_styles(){
			wp_enqueue_style( 'style', plugin_dir_url(__FILE__) . 'main.css' );
			wp_enqueue_script( 'designTool', plugin_dir_url(__FILE__) . 'button.js');
			wp_localize_script('designTool', 'WPURLS', array( 'siteurl' => get_option('siteurl') )); 
		}

		public function dbsdk_createButton(){
			global $post;
			$output = '';
			$icon = plugin_dir_url(__FILE__) . 'assets/icon.svg';

			$img = '<span class="wp-media-buttons-icon" style="background-image: url(' . $icon . '); width: 16px; height: 16px; margin-top: 1px;"></span>';

			$output = '<button type="button" class="button maxbutton_media_button" onclick="DBSDK.startOverlay('. $post->ID .')" title="Image design" style="padding-left: .4em;">' . $img . ' Image design</button>';

			echo $output;
		}
	}
}
$dbii = new Designit();
$dbii->init();