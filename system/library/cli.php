<?php
namespace Opencart\System\Library;
class CLI {
    protected $registry;

    public function __construct($registry) {
        if (!$this->isActive()) {
            $this->echo_invalid_request();
        }

        $this->registry = $registry;
    }

    public function isActive() {
        return defined('OPENCART_CLI_MODE') && OPENCART_CLI_MODE === TRUE;
    }

    public function router() {
        global $argv;

        if (empty($argv[2]) || $argv[2] == 'cli/router') {
            $this->echo_not_found();
            $route = $this->registry->get('config')->get('action_default');
        } else {
            $route = str_replace('../', '', (string)$argv[2]);
            $route = str_replace(':', '|', (string)$route);
        }

        // We dont want to use the loader class as it would make any controller callable.
        $action = new \Opencart\System\Engine\Action($route);

        // Any other output needs to be another Action object.
        $params = array_slice($argv, 3);

        $output = $action->execute($this->registry, $params);

        return $output;
    }

    public function echo_invalid_request() {
        cli_output("Invalid request!", true);
    }

    public function echo_not_found() {
        cli_output("Route not found!", true);
    }

    public function echo_welcome_message() {
        cli_output("Welcome to OpenCart in CLI mode.");
    }
}
