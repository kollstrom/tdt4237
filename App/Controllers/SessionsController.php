<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Auth;
use \App\System\CSRF;

class SessionsController extends Controller {

    public function login() {
        $csrf = new CSRF();
        if(!empty($_POST)) {


            if($csrf->validateToken('log-in', $_POST['token'])){

                $username = isset($_POST['username']) ? $_POST['username'] : '';
                $password = isset($_POST['password']) ? hash('sha256', Settings::getConfig()['salt'] . $_POST['password']) : '';


                if ($this->userRep->getLocked($username) == true) {
                    $errors = [
                        "This account is locked. Contact an admin to open it."
                    ];
                }
                else if($this->auth->checkCredentials($username, $password)) {
                    $this->userRep->resetLoginFails($username);
                    setcookie("user", $username,0,NULL, NULL, FALSE,TRUE );
                    setcookie("password",  $password,0,NULL, NULL, FALSE,TRUE);
                    $_SESSION['auth']       = $username;
                    $_SESSION['id']         = $this->userRep->getId($username);
                    $_SESSION['email']      = $this->userRep->getEmail($username);
                    $_SESSION['password']   = $password;

                    App::redirect('dashboard');
                    session_regenerate_id(true);
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
            else{
                App::errorCSRF();
            }
        }
        $token = $csrf->generateToken('log-in');
        $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : '',
            'token'       => $token
        ]);
    }

    public function logout() {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id(true);
        App::redirect();
    }

}
