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
            
            if($this->auth->checkCredentials($username, $password)) {
                setcookie("user", $username,0,NULL, NULL, FALSE,TRUE );
                setcookie("password",  $_POST['password'],0,NULL, NULL, FALSE,TRUE);
                if ($this->userRep->getAdmin($username)){
                    setcookie("admin", 'yes',0,NULL, NULL, FALSE,TRUE);
                }else{
                    setcookie("admin", 'no',0,NULL, NULL, FALSE,TRUE);
                }
                $_SESSION['auth']       = $username;
                $_SESSION['id']         = $this->userRep->getId($username);
                $_SESSION['email']      = $this->userRep->getEmail($username);
                $_SESSION['password']   = $password;

                App::redirect('dashboard');
            }

            else {
                $errors = [
                    "Your username and your password don't match."
                ];
            }
        }

        $this->render('pages/signin.twig', [
            'title'       => 'Sign in',
            'description' => 'Sign in to the dashboard',
            'errors'      => isset($errors) ? $errors : ''
        ]);
    }

    public function logout() {
        session_destroy();
        App::redirect();
    }

}
