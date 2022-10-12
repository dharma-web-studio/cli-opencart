<?php
namespace Opencart\Admin\Controller\Cli;
class NotFound extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->cli->echo_not_found();
    }
}
