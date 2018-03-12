<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Auth;

class SessionsController extends Controller {

    public function login() {
        if(!empty($_POST)) {
            
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            //$password = isset($_POST['password']) ? hash('sha1', Settings::getConfig()['salt'] . $_POST['password']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if ($this->userRep->getLocked($username) == true) {
                $errors = [
                    "This account is locked. Contact an admin to open it."
                ];
            }
            
            else if($this->auth->checkCredentials($username, $password)) {
                $this->userRep->resetLoginFails($username);
                setcookie("user", $username);
                setcookie("password",  $_POST['password']);
                if ($this->userRep->getAdmin($username)){
                    setcookie("admin", 'yes');
                }else{
                    setcookie("admin", 'no');
                }
                $_SESSION['auth']       = $username;
                $_SESSION['id']         = $this->userRep->getId($username);
                $_SESSION['email']      = $this->userRep->getEmail($username);
                $_SESSION['password']   = $password;

                App::redirect('dashboard');
            }

            else {
                // Authentication failed
                $lastLoginAttempt = $this->userRep->getLastLoginAttempt($username);
                if(is_null($lastLoginAttempt)) {
                    $this->userRep->incrementLoginAttempts($username);
                    $errors = [
                        "Your username and your password don't match."
                    ];
                }
                else {
                    $dateTime = new \DateTime($lastLoginAttempt, new \DateTimeZone('Europe/Oslo'));
                    $interval = $dateTime->diff(new \DateTime('now'));
                    $minutes = $interval->days * 24 * 60;
                    $minutes += $interval->h * 60;
                    $minutes += $interval->i;

                    if ($minutes < 10) {
                        if ($this->userRep->getLoginFails($username) > 5) {
                            $this->userRep->lockAccount($username);
                        }
                    }
                    else {
                        $this->userRep->resetLoginFails($username);
                    }
                    $this->userRep->incrementLoginAttempts($username);
                    $errors = [
                        "Your username and your password don't match."
                    ];
                }
                $this->userRep->setLastLoginAttempt($username);
            }
        }

        $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : ''
        ]);
    }

    public function logout() {
        App::redirect();
    }

}
