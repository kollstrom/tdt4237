<?php
namespace App\Models;

use \App\System\App;
use \App\Models\Model;
use \App\System\Auth;

class UsersModel extends Model {

    protected $table = "users";

    public function login($username, $passwordHash) {
        $userRow = $this->getUserRow($username);
        if($userRow) {
            if($userRow->password === $passwordHash) {
                $_SESSION['auth'] = $userRow->id;
                return true;
            }
        }
        return false;
    }

    public static function logged(){
        if(!isset($_SESSION['auth'])) {
            App::redirect('signin');
            exit;
        }
    }
    
    public function getUserRow($username){
        return App::getDb()->prepare('SELECT * FROM users WHERE username = "?"', array($username), (true);
    }
    
    public function getPasswordHash($username){
        $userRow = $this->getUserRow($username);
        return $userRow->password;
    }
    
    public function getId($username){
        $userRow = $this->getUserRow($username);
        return $userRow->id;
    }
    
    public function getEmail($username){
        $userRow = $this->getUserRow($username);
        return $userRow->email;
    }
    
    public function getAdmin($username){
        $userRow = $this->getUserRow($username);
        return $userRow->admin;
    }

    public function getLocked($username) {
        return $this->getUserRow($username)->locked;
    }

    public function getLastLoginAttempt($username) {
        return $this->getUserRow($username)->last_login_attempt;
    }

    public function getLoginFails($username) {
        return $this->getUserRow($username)->login_fails;
    }

    public function incrementLoginAttempts($username) {
        $userRow = $this->getUserRow($username);
        $loginFails = $userRow->login_fails;
        if (is_null($loginFails)) {
            $this->resetLoginFails($username);
        }
        else {
            App::getDb()->execute('UPDATE users SET login_fails = login_fails + 1 WHERE username = "'
            . $username. '"');
        }
    }

    public function resetLoginFails($username) {
        App::getDb()->execute('UPDATE users SET login_fails = 0 WHERE username = "' .$username. '"');
    }

    public function setLastLoginAttempt($username) {
        $lastLoginAttempt = new \DateTime("now", new \DateTimeZone('Europe/Oslo'));
        $attempt = $lastLoginAttempt->format('Y-m-d H:i:s');
        App::getDb()->execute('UPDATE users SET last_login_attempt = "'. $attempt . '" WHERE username = "'
            . $username. '"');
    }

    public function lockAccount($username) {
        App::getDb()->execute('UPDATE users SET locked = 1 WHERE username = "'.$username .'"');
    }

}
