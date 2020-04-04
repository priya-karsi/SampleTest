<?php
/**
 * DependancyInjector is used to set or get dependency required
 * 
 * 
 */

class DependancyInjector {

    private $dependencies = array();

    /**
     * set takes a string $key and a object $value saves in dependencies array
     * eg. $di->set('connection', $conection);  
     */
    public function set(string $key, $value) {
        $this->dependencies[$key] = $value;
    }

    /**
     * get takes a string $key returns corresponding object from dependencies array
     * eg. $di->get('connection');  
     */
    public function get(string $key) {
        
        if(isset($this->dependencies[$key])):
            return $this->dependencies[$key];
        endif;

        die('This dependency Not Found '.$key);
    }
}


?>