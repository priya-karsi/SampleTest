<?php

class Validator {

    protected $di;

    protected $rules = ['required', 'minlength', 'maxlength', 'unique', 'email', 'phone'];

    protected $messages = [
        'required' => 'The :field field is required',
        'minlength' => "The :field field must be a minimum of :satisfier characters",
        'maxlength'=> "The :field field must be a maximum of :satisfier characters",
        'email' => 'The :field is not a valid phone number',
        'unique' => 'The :field is already taken',
        'phone' => 'The :field is not a valid phone number'
    ];


    public function __construct(DependancyInjector $di) {
        $this->di = $di;
    }

    public function check($items, $rules) {
        foreach($items as $item => $value) {
            if(in_array($item, array_keys($rules))) {
                $this->validate([
                    'field' => $item,
                    'value' => $value,
                    'rules' => $rules[$item]
                ]);
            }
        }
        return $this;
    }


    public function validate($item) {
        $field = $item['field'];
        foreach($item['rules'] as $rule => $satisfier) {
            if(!call_user_func_array([$this, $rule], [$field,$item['value'],$satisfier])) {
                // error handling
                $this->di->get('errorhandler')->addError(str_replace([':field',':satisfier'], [$field, $satisfier], $this->messages[$rule]),$field);
            }
        }
    }

    public function required($field, $value, $satisfier) {
        if ($satisfier):
            return !empty(trim($value));
        endif;
    }

    public function minlength($field, $value, $satisfier) {
        return mb_strlen($value) >= $satisfier;
    }

    public function maxlength($field, $value, $satisfier) {
        return mb_strlen($value) <= $satisfier;
    }

    public function unique($field, $value, $satisfier) {
        return !$this->di->get('database')->exists($satisfier, [$field=>$value]);
    }

    public function email($field, $value, $satisfier) {
        if ($satisfier):
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        endif;
    }

    public function phone($field, $value, $satisfier) {
        return strlen(preg_replace('/^[0-9]{10}/', '', $value)) == 10;
    }

    public function fails() {
        return $this->di->get('errorhandler')->hasErrors();
    }

    public function errors() {
        return $this->di->get('errorhandler');
    }

}



?>
