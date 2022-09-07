<?php
namespace Opencart\Admin\Controller\Cli;
class Router extends \Opencart\System\Engine\Controller {
    public function index() {
        return $this->cli->router($this->registry);
    }
}
