<?php

class ErrorHandler {

    protected $errors = array();

    public function addError($error, $key) {
        if($key) {
            $this->errors[$key][] = $error;
        }
        else {
            $this->errors[] = $error;
        }
    }

    public function hasErrors($key=null) {
        // print_r (count($this->all($key)));
        // echo(count($this->errors));
        return count($this->all($key)) ? true : false;
    }

    public function has($key) {
        return isset($this->errors[$key]);
    }

    public function all($key=null) {
        return isset($this->errors[$key]) ? $this->errors[$key] : $this->errors;
    }

    public function first($key) {
        return isset($this->errors[$key]) ? $this->all()[$key][0] : false;
    }

}



?>