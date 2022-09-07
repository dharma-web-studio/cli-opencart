<?php
namespace Opencart\Admin\Controller\Cli;
class Startup extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->attach_libraries();
    }

    private function attach_libraries() {
        $this->registry->set('cli', new \Opencart\System\Library\CLI($this->registry));
    }
}
