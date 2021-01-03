<?php

class StaticWriter extends Base {

    const OUTPUT_EXTENSION = ".html";
    const BACKUP_EXTENSION = ".bak";
    private $now = "";
    private $public_path = "";
    private $config;

    public function __construct(Config $config)  {
        $this->config = $config;
        $this->now = date("Y-m-d");
        $this->public_path = $config->getParam("public_path", "public_html");
    }

    public function write($render_profile_name, $content) {
        $this->installStatics();

        $this->backupAndRotate($render_profile_name);
        return file_put_contents(
            $this->getPathAndFilename($render_profile_name, self::OUTPUT_EXTENSION),
            $content
        );
    }

    public function backupAndRotate($render_profile_name) {
        if (file_exists($this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION))) {
            // Make an even second temporary copy
            copy(
                $this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION),
                $this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION . self::BACKUP_EXTENSION)
            );
            // Delete the backup
            unlink($this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION));
        }
        if (file_exists($this->getPathAndFilename($render_profile_name, self::OUTPUT_EXTENSION))) {
            // Copy the current one as a backup
            copy(
                $this->getPathAndFilename($render_profile_name, self::OUTPUT_EXTENSION),
                $this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION)
            );
            // Delete the current one.
            unlink($this->getPathAndFilename($render_profile_name, self::OUTPUT_EXTENSION));
        }
        if (file_exists($this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION . self::BACKUP_EXTENSION))) {
            // Delete the secondary temporary copy
            unlink($this->getPathAndFilename($render_profile_name, self::BACKUP_EXTENSION . self::BACKUP_EXTENSION));
        }
    }

    private function installStatics() {
        $statics = ["css", "js", "img", "vendors"];
        foreach ($statics as $static) {
            $this->installStatic($static);
        }
    }

    private function installStatic($static) {
        $public_path = $this->pathMe() . $this->public_path . DIRECTORY_SEPARATOR;
        $static_path = $this->pathMe() . "static" . DIRECTORY_SEPARATOR;
        if (file_exists($public_path . $static)) {
            $this->_recurseRmdir($public_path . $static);
        }
        $this->_recurse_copy(
            $static_path . $static,
            $public_path . $static
        );
    }

    private function getPathAndFilename($render_profile_name, $extension = OUTPUT_EXTENSION) {
        return $this->pathMe() . $this->public_path . DIRECTORY_SEPARATOR . 
            $this->config->getRenderProfile($render_profile_name)["slug"] . $extension;
        
    }

    // https://www.php.net/manual/en/function.copy.php#91010
    private function _recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) {
                    $this->_recurse_copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
                }
                else {
                    copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }

    // https://www.php.net/manual/en/function.rmdir.php#98622
    private function _recurseRmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    $this->_recurseRmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }
}