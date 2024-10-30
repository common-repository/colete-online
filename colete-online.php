<?php

/**
 * The plugin bootstrap file
 *
 * @link              www.colete-online.ro
 * @since             1.0.0
 * @package           Colete_Online
 *
 * @wordpress-plugin
 * Plugin Name:       Colete-Online
 * Description:       Colete Online plugin for adding shipping methods
 * Version:           1.5.4
 * Author:            Colete Online
 * Author URI:        www.colete-online.ro
 * Requires PHP:      7.4
 * Tested up to:      6.6
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       coleteonline
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Current plugin version.
 */
define('COLETE_ONLINE_VERSION', '1.5.4');
define('COLETE_ONLINE_ROOT', dirname(__FILE__));
define('COLETE_ONLINE_PLUGIN_ROOT', dirname(plugin_basename(__FILE__)));
define('COLETE_ONLINE_SCRIPT_DEBUG', false);
define('COLETE_ONLINE_STYLE_DEBUG', false);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-colete-online-activator.php
 */
function activate_colete_online()
{
  /**
   * Check if WooCommerce plugin is enabled
   */
  require_once plugin_dir_path(__FILE__) .
    'includes/class-colete-online-activator.php';
  Colete_Online_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-colete-online-deactivator.php
 */
function deactivate_colete_online()
{
  require_once plugin_dir_path(__FILE__) .
    'includes/class-colete-online-deactivator.php';
  Colete_Online_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_colete_online');
register_deactivation_hook(__FILE__, 'deactivate_colete_online');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-colete-online.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_colete_online()
{
  $plugin = new Colete_Online();
  $plugin->run();
}
run_colete_online();