<?php

require_once __DIR__."/../helper/requirements.php";

class Category{
    private $table = "category";
    private $database;
    protected $di;

    public function __construct($di)
    {
        $this->di = $di;
        $this->database = $this->di->get('database');
    }

    private function validateData($data)
    {
        $validator = $this->di->get('validator');
        return $validator->check($data, [
            'name' => [
                'required' => true,
                'minlength' => 2,
                'maxlength' => 255,
                'unique' => $this->table
            ]
        ]);
    }
    /**
     * This function is responsible to accept the data from the Routing and add it to the Database.
     */
    public function addCategory($data)
    {
        $validation = $this->validateData($data);
        if(!$validation->fails())
        {
            //Validation was successful
            try
            {
                //Begin Transaction
                $this->database->beginTransaction();
                $data_to_be_inserted = ['name' => $data['name']];
                $category_id = $this->database->insert($this->table, $data_to_be_inserted);
                $this->database->commit();
                return ADD_SUCCESS;
            }
            catch(Exception $e)
            {
                $this->database->rollback();
                return ADD_ERROR;
            }
        }
        else
        {
            //Validation Failed!
            return VALIDATION_ERROR;
        }
    }
}
