<?php

class Config {

    protected $config;
    public function __construct()
    {
        $this->config = parse_ini_file(__DIR__."/../../config.ini");
    }

    public function get(string $key)
    {
        if(isset($this->config[$key])):
            return $this->config[$key];
        endif;

        die("This config cannot be found : ".$key);
    }
}

?>