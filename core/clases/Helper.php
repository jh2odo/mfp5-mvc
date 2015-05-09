<?php

class Helper
{

    private $loaded = array();

    public function __get($helper)
    {
        if (!isset($this->loaded[$helper])) {
            $helper_core = BASE_PATH . "core" . DIRSEP . "helpers" . DIRSEP . "{$helper}.php";
            $helper_app = BASE_PATH_APP . "app" . DIRSEP . "helpers" . DIRSEP . "{$helper}.php";
            if (file_exists($helper_core)) {
                require_once($helper_core);
                $this->loaded[$helper] = new $helper();
            } else if (file_exists($helper_app)) {
                require_once($helper_app);
                $this->loaded[$helper] = new $helper();
            } else {
                throw new Exception("Helper {$helper} no encontrado.");
            }
        }
        return $this->loaded[$helper];
    }
}

?>