<?php
/*
Plugin Name: PHP Annotations
Plugin URI: https://marcuskober.de
Description: Example plugin that uses php annotation for registering hooks
Version: 1.0.0
Requires at least: 6.0
Requires PHP: 8.1
Author: Marcus Kober
Author URI: https://marcuskober.de
Text Domain: phpannotations
*/

use PhpAnnotations\Main\App;

if (! defined('ABSPATH')) {
    die('Go away');
}

define('PHPAN_DIR', plugin_dir_path( __FILE__ ));
define('PHPAN_URL', plugin_dir_url( __FILE__ ));
define('PHPAN_BASENAME', plugin_basename( __FILE__ ));
define('PHPAN_VERSION', '1.0.0');

require PHPAN_DIR . 'vendor/autoload.php';

App::init();
