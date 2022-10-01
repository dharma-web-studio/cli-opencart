<?php
namespace Opencart\Admin\Controller\Cli;
class Extension extends \Opencart\System\Engine\Controller {

    public const ALLOWED_METHODS = [
        'add',
        'disable',
        'enable',
        'install',
        'list',
        'remove',
        'status',
        'uninstall'
    ];

    public const ALLOWED_EXTENSION_TYPES = [
        'analytics',
        'captcha',
        'currency',
        'dashboard',
        'feed',
        'fraud',
        'language',
        'module',
        'other',
        'payment',
        'report',
        'shipping',
        'theme',
        'total'
    ];

    public function index() {
        cli_output_array(self::ALLOWED_METHODS, 'Available methods', null, true);
    }

    public function list($extension_type = null) {

        if($extension_type === null || !in_array($extension_type, self::ALLOWED_EXTENSION_TYPES)) {
            $this->return_available_extension_types();
        }

        $this->load->model('setting/extension');
        $results = $this->model_setting_extension->getPaths('%/admin/controller/' . $extension_type .'/%.php');
        foreach ($results as $result) {
            $available[] = basename($result['path'], '.php');
        }
        $installed = [];
        $extensions = $this->model_setting_extension->getExtensionsByType($extension_type);
        foreach ($extensions as $extension) {
            if (in_array($extension['code'], $available)) {
                $installed[] = $extension['code'];
            }
        }
        $data = [];
        if ($results) {
            foreach ($results as $result) {
                $extension = substr($result['path'], 0, strpos($result['path'], '/'));
                $code = basename($result['path'], '.php');
                $this->load->language('extension/' . $extension . '/' . $extension_type . '/' . $code, $code);
                $status = $this->getExtensionStatus($extension_type, $code) ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
                $data[] = $code  . ' (' . $status . ')';
            }
        }

        cli_output_array($data, 'Extensions (' . $extension_type . ')');
    }

    public function status($extension_type, $code) {
        if($this->getExtensionStatus($extension_type, $code)) {
            cli_output('Extension is enabled', true);
        }
        cli_output('Extension is disabled', true);
    }


    public function add(string $extension_type, string $code) {

    }

    public function remove(string $extension_type, string $code) {

    }


    public function install() {
        if ($this->model_setting_extension->getInstallByCode(basename($filename, '.ocmod.zip'))) {
            $json['error'] = $this->language->get('error_installed');
        }

        // Unzip the files
        $zip = new \ZipArchive();

        if ($zip->open($file, \ZipArchive::RDONLY)) {
            $install_info = json_decode($zip->getFromName('install.json'), true);

            if ($install_info) {
                if (!$install_info['name']) {
                    $json['error'] = $this->language->get('error_name');
                }

                if (!$install_info['version']) {
                    $json['error'] = $this->language->get('error_version');
                }

                if (!$install_info['author']) {
                    $json['error'] = $this->language->get('error_author');
                }

                if (!$install_info['link']) {
                    $json['error'] = $this->language->get('error_link');
                }
            } else {
                $json['error'] = $this->language->get('error_install');
            }

            $zip->close();
        }

        $extension_data = [
            'extension_id'          => 0,
            'extension_download_id' => 0,
            'name'                  => $install_info['name'],
            'code'              	=> basename($filename, '.ocmod.zip'),
            'version'               => $install_info['version'],
            'author'                => $install_info['author'],
            'link'                  => $install_info['link']
        ];

        $this->load->model('setting/extension');

        $this->model_setting_extension->addInstall($extension_data);
        if (isset($this->request->get['extension_install_id'])) {
            $extension_install_id = (int)$this->request->get['extension_install_id'];
        } else {
            $extension_install_id = 0;
        }

        $this->load->model('setting/extension');

        $extension_install_info = $this->model_setting_extension->getInstall($extension_install_id);

        if ($extension_install_info) {
            $file = DIR_STORAGE . 'marketplace/' . $extension_install_info['code'] . '.ocmod.zip';

            if (!is_file($file)) {
                $json['error'] = sprintf($this->language->get('error_file'), $extension_install_info['code'] . '.ocmod.zip');
            }

            if ($page == 1 && is_dir(DIR_EXTENSION . $extension_install_info['code'] . '/')) {
                $json['error'] = sprintf($this->language->get('error_directory_exists'), $extension_install_info['code'] . '/');
            }

            if ($page > 1 && !is_dir(DIR_EXTENSION . $extension_install_info['code'] . '/')) {
                $json['error'] = sprintf($this->language->get('error_directory'), $extension_install_info['code'] . '/');
            }
        } else {
            $json['error'] = $this->language->get('error_extension');
        }



        if (!$json) {
            // Unzip the files
            $zip = new \ZipArchive();

            if ($zip->open($file)) {
                $total = $zip->numFiles;
                $limit = 200;

                $start = ($page - 1) * $limit;
                $end = $start > ($total - $limit) ? $total : ($start + $limit);

                // Check if any of the files already exist.
                for ($i = $start; $i < $end; $i++) {
                    $source = $zip->getNameIndex($i);

                    $destination = str_replace('\\', '/', $source);

                    // Only extract the contents of the upload folder
                    $path = $extension_install_info['code'] . '/' . $destination;
                    $base = DIR_EXTENSION;
                    $prefix = '';

                    // image > image
                    if (substr($destination, 0, 6) == 'image/') {
                        $path = $destination;
                        $base = substr(DIR_IMAGE, 0, -6);
                    }

                    // We need to store the path differently for vendor folders.
                    if (substr($destination, 0, 15) == 'system/storage/') {
                        $path = substr($destination, 15);
                        $base = DIR_STORAGE;
                        $prefix = 'system/storage/';
                    }

                    // Must not have a path before files and directories can be moved
                    $path_new = '';

                    $directories = explode('/', dirname($path));

                    foreach ($directories as $directory) {
                        if (!$path_new) {
                            $path_new = $directory;
                        } else {
                            $path_new = $path_new . '/' . $directory;
                        }

                        // To fix storage location
                        if (!is_dir($base . $path_new . '/') && mkdir($base . $path_new . '/', 0777)) {
                            $this->model_setting_extension->addPath($extension_install_id, $prefix . $path_new);
                        }
                    }

                    // If check if the path is not directory and check there is no existing file
                    if (substr($source, -1) != '/') {
                        if (!is_file($base . $path) && copy('zip://' . $file . '#' . $source, $base . $path)) {
                            $this->model_setting_extension->addPath($extension_install_id, $prefix . $path);
                        }
                    }
                }

                $zip->close();

                $this->model_setting_extension->editStatus($extension_install_id, 1);
            } else {
                $json['error'] = $this->language->get('error_unzip');
            }
        }

        if (!$json) {
            $json['text'] = sprintf($this->language->get('text_progress'), 2, $total);

            $url = '';

            if (isset($this->request->get['extension_install_id'])) {
                $url .= '&extension_install_id=' . $this->request->get['extension_install_id'];
            }

            if (($page * 200) <= $total) {
                $json['next'] = $this->url->link('marketplace/installer|install', 'user_token=' . $this->session->data['user_token'] . $url . '&page=' . ($page + 1), true);
            } else {
                $json['next'] = $this->url->link('marketplace/installer|vendor', 'user_token=' . $this->session->data['user_token'] . $url, true);
            }
        }



        cli_output('Install Method', true);
    }

    public function uninstall() {
        cli_output('Uninstall Method', true);
    }

    public function enable() {
        cli_output('Enable Method', true);
    }

    public function disable() {
        cli_output( var_dump( $this->config->get( 'module_account_status') ), true );
    }

    /**
     * @param string $extension_type
     * @param string $code
     * @return bool
     */
    private function getExtensionStatus(string $extension_type, string $code): bool {
        return $this->config->get( $extension_type . '_' . $code . '_status') ? true : false;
    }

    /**
     * Return the cli output with available extension types
     *
     * @return void
     */
    private function return_available_extension_types(): void {
        cli_output_array(self::ALLOWED_EXTENSION_TYPES, 'Available extension types', null, true);
    }

}
