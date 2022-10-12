<?php
namespace Opencart\Admin\Controller\Cli;
class Extension extends \Opencart\System\Engine\Controller {

    public const DEBUG_MODE = true;

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
            $this->returnAvailableExtensionTypes();
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
            cli_output('Extension ' . $code . ' is enabled', true);
        }

        cli_output('Extension ' . $code . ' is disabled', true);

    }


    public function add(string $extension_type, string $code) {

    }

    public function remove(string $extension_type, string $code) {

    }


    public function install(string $code) {

//        $files = $this->listAllFiles(DIR_EXTENSION . $code . '/', $code);
//        cli_output_array($files, '','', true);

        cli_output('Installing ' . $code);

        $this->load->language('cli/extension');
        $this->load->model('setting/extension');

        if ($this->model_setting_extension->getInstallByCode($code)) {
            cli_output($this->language->get('error_installed'), true);
        }

        $install_info = json_decode(file_get_contents(DIR_EXTENSION . $code . '/install.json'), true);

        if ($install_info) {
            if (!$install_info['name']) {
                cli_output($this->language->get('error_name'), true);
            }

            if (!$install_info['version']) {
                cli_output($this->language->get('error_version'), true);
            }

            if (!$install_info['author']) {
                cli_output($this->language->get('error_author'), true);
            }

            if (!$install_info['link']) {
                cli_output($this->language->get('error_link'), true);
            }
        }

        $extension_install_info = [
            'extension_id'          => 0,
            'extension_download_id' => 0,
            'name'                  => $install_info['name'],
            'code'              	=> $code,
            'version'               => $install_info['version'],
            'author'                => $install_info['author'],
            'link'                  => $install_info['link']
        ];

        $extension_install_id = $this->model_setting_extension->addInstall($extension_install_info);


        if ($extension_install_id) {
            $files = $this->listAllFiles(DIR_EXTENSION . $code . '/', $code);

            // Check if any of the files already exist.
            foreach ($files as $file) {
                $source = $file;
                $destination = str_replace('\\', '/', $source);

                // Only extract the contents of the upload folder
                $path = $extension_install_info['code'] . DIRECTORY_SEPARATOR . $destination;
                $base = DIR_EXTENSION;
                $prefix = '';

                // image > image
                if (substr($destination, 0, 6) == 'image/') {
                    $path = substr(DIR_IMAGE, 0, -6);
                }

                // We need to store the path differently for vendor folders.
                if (substr($destination, 0, 15) == 'system/storage/') {
                    $path = substr($destination, 15);
                    $prefix = 'system/storage/';
                }

                $this->model_setting_extension->addPath($extension_install_id, $prefix . $path);

            }

            $this->model_setting_extension->editStatus($extension_install_id, 1);

        } else {
            cli_output($this->language->get('error_json'), true);
        }

        cli_output('Installed ' . $code, true);
    }

    public function uninstall(string $type, string $code) {

        $extension_install_info = $this->model_setting_extension->getInstallByCode($code);
        cli_output('Uninstalling ' . $code);
        $this->model_setting_extension->uninstall($type, $code);
        $this->model_setting_extension->deleteInstall($extension_install_info['extension_install_id']);
        cli_output('Uninstalled ' . $code, true);

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
    private function returnAvailableExtensionTypes(): void {
        cli_output_array(self::ALLOWED_EXTENSION_TYPES, 'Available extension types', null, true);
    }


    /**
     * Return the list of files, that belongs to a extension
     *
     * @param string $path
     * @return array
     */
    private function listAllFiles(string $path, string $code): array {
        $files = array();
        $paths = $this->listAllPaths($path);
        foreach ($paths as $route) {
            $files[] = str_replace( DIR_EXTENSION . $code . DIRECTORY_SEPARATOR, '', $route);
        }
        return $files;
    }


    /**
     * Return the list of paths, that belongs to a extension
     *
     * @param string $path
     * @return array
     */
    private function listAllPaths(string $path): array {

        $files = array();
        $paths = array_diff(scandir($path), array( '.', '..'));

        foreach ($paths as &$item) {
            if(substr($item, 0, 1) == '.' || substr($item, -3) == '.md') {
                continue;
            }
            $item = $path . $item;
        }

        unset($item);

        foreach ($paths as $item) {
            if (is_dir($item)) {
                $paths = array_merge($paths, $this->listAllPaths($item . DIRECTORY_SEPARATOR));
            }
        }

        foreach ($paths as $file) {
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

}
