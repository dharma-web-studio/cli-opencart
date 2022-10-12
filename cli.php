<?php
if (!function_exists('cli_output')) {
    function cli_output($message, $exit_status = null) {
        echo $message . PHP_EOL;

        if ($exit_status !== null) {
            exit($exit_status);
        }
    }
}

if (!function_exists('cli_output_array')) {
    function cli_output_array($array, $before = null, $after = null, $exit_status = null) {
        if(!is_array($array)) {
            cli_output('This output method only accept arrays', true);
        }

        $message = '';
        foreach ($array as $item) {
            $message .= $item . PHP_EOL;
        }

        if($before !== null) {
            echo PHP_EOL;
            echo '====================================' . PHP_EOL;
            echo  $before . PHP_EOL;
            echo '====================================' . PHP_EOL;
        }

        echo $message . PHP_EOL;

        if($after !== null) {
            echo $after;
        }

        if ($exit_status !== null) {
            exit($exit_status);
        }
    }
}

if (!function_exists('cli_find_version')) {
    function cli_find_version() {
        $current_dir = dirname(__FILE__);

        $index_contents = file_get_contents($current_dir . '/index.php');

        $matches = array();

        preg_match('~define\s*\(\s*\'VERSION\'\s*,\s*\'(.*?)\'\s*\)\s*;~', $index_contents, $matches);

        if (!empty($matches[1])) {
            return $matches[1];
        } else {
            cli_output("Cannot find OpenCart version.", 1);
        }
    }
}

// The action starts... Let's check for the CLI mode
if (php_sapi_name() != 'cli') {
    header("Location:/");
    cli_output("Get out.", 1);
}

// Version
if (!defined('VERSION')) define('VERSION', cli_find_version());

// Status constant. Should be set to TRUE.
if (!defined('OPENCART_CLI_MODE')) define('OPENCART_CLI_MODE', TRUE);

// Change directory to allow the script to be called from anywhere.
chdir(dirname(__FILE__));

// Determine the admin folder
if (empty($argv[1]) || !is_dir($argv[1])) {
    cli_output("Invalid request. Expecting: <admin-folder-name>", 1);
} else {
    $config_root = './' . $argv[1] . '/';
}

// Set SERVER_PORT
if (!isset($_SERVER['SERVER_PORT'])) {
    $_SERVER['SERVER_PORT'] = 80;
}

// Load Configuration
if (is_file($config_root . 'config.php')) {
    require_once($config_root . 'config.php');
}

// Check if OpenCart is installed
if (!defined('DIR_APPLICATION')) {
    cli_output("OpenCart not installed.", 1);
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

$application_config = 'cli';

// Application
require_once(DIR_SYSTEM . 'cli-framework.php');
