<?php
namespace Opencart\Admin\Controller\Cli;
class Welcome extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->cli->echo_welcome_message();
    }
}
