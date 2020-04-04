<?php

class TokenHandler {

    private static $REMEMBER_EXPIRY_TIME = "30 minutes";
    private static $FORGET_PWD_EXPIRY_TIME = "15 minutes";
        
    private $table = 'tokens';

    private $database;

    public function __construct(DependancyInjector $di)
    {
        $this->database = $di->get('database');
    
    }

    public static function getCurrentTimeInMilliSec() {
        return round(microtime(true) * 1000);
    }

    public function build() {
        return $this->database->query(
            "CREATE TABLE IF NOT EXISTS {this->table} (id INT PRIMARY KEY AUTO_INCREMENT, user_id INT, token VARCHAR(255) UNIQUE, expires_at DATETIME NOT NULL, is_remember TINYINT DEFAULT 0)"
            );
    }

    public function getValidExixtingToken(int $user_id, int $isRemember) {
        $query = "SELECT * FROM {$this->table} WHERE USER_ID = {$user_id} and expires_at >= Now() and is_remember = {$isRemember}";
        $retVal = $this->database->rawQueryExecutor($query);

        return $retVal[0]['token'] ?? null;
    }

    public function createRememberMeToken(int $user_id) {
        return $this->createToken($user_id, 1);
    }

    public function createForgetPasswordToken(int $user_id) {
        return $this->createToken($user_id, 0);
    }

    private function createToken(int $user_id, int $isRemember) {
        $validToken = $this->getValidExixtingToken($user_id, $isRemember);
        if($validToken) {
            return $validToken;
        }

        $current = date("Y-m-d H:i:s");
        $timeToBeAdded = $isRemember ? TokenHandler::$REMEMBER_EXPIRY_TIME : TokenHandler::$FORGET_PWD_EXPIRY_TIME;

        $data = [
            'user_id' => $user_id,
            'token' => Hash::generateRandomToken($user_id),
            'expires_at' => date("Y-m-d H:i:s", strtotime($current.'+'.$timeToBeAdded)),
            'is_remember' => $isRemember
        ];

        return $this->database->table($this->table)->insert($data) ? $data['token'] : null;
    }

    public function isValid(string $token, int $isRemember) {

        return !empty($this->database->rawQueryExecutor("SELECT * FROM {$this->table} WHERE token = '$token' and expires_at >= Now() and is_remember = $isRemember"));
    }

    public function getUserFromValidToken(string $token) {
        return $this->database->table($this->table)->where('token', '=', $token)->first();
    }

    public function deleteToken(string $token) {
        $sql = "DELETE FROM {$this->table} WHERE TOKEN = '{$token}'";
        return $this->database->query($sql);
    }

}


?>