<?php
namespace Opencart\Admin\Controller\Cli;
class Welcome extends \Opencart\System\Engine\Controller {

    public const ALLOWED_ENDPOINTS = ['status', 'list', 'install', 'uninstall'];

    public function index() {

        $strpos = $strpos();
        if (!isset($this->request->get['route'])) {

        }

        cli_output('index');
    }

    private function list() {
//        $this->load->model('setting/extension');
//
//        $files = glob(DIR_APPLICATION . 'controller/extension/*.php');
//
//        foreach ($files as $file) {
//            $extension = basename($file, '.php');
//
//            $this->load->language('extension/' . $extension, $extension);
//
//            if ($this->user->hasPermission('access', 'extension/' . $extension)) {
//                $extensions = $this->model_setting_extension->getPaths('%/admin/controller/' . $extension . '/%.php');
//
//                $data['categories'][] = [
//                    'code' => $extension,
//                    'text' => $this->language->get($extension . '_heading_title') . ' (' . count($extensions) . ')',
//                    'href' => $this->url->link('extension/' . $extension, 'user_token=' . $this->session->data['user_token'])
//                ];
//            }
//        }

        cli_output('it works');
    }

}
