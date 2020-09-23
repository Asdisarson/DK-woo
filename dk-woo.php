<?php

/**
 * Plugin Name:       My Basics Plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            John Smith
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */

function dkp_dir($append = false)
{

    return plugin_dir_path(__FILE__) . $append;

}

function dkp_url($append = false)
{

    return plugin_dir_url(__FILE__) . $append;

}

include_once 'includes.php';

