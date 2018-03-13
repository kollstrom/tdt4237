<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Mailer;
use App\System\Auth;
use App\System\CSRF;

class UsersController extends Controller {

    public function all() {
        $model = new UsersModel();
        $data  = $model->all();

        $this->render('pages/admin/users.twig', [
            'title'       => 'Users',
            'description' => 'Users - Just a simple inventory management system.',
            'page'        => 'users',
            'users'    => $data
        ]);
    }


    /*
    This function is used when the administrator adds a user from the administrator dashboard
    */
    public function add() {
        $csrf = new CSRF();
        if(!empty($_POST)) {
            $username              = isset($_POST['username']) ? $_POST['username'] : '';
            $email                 = isset($_POST['email']) ? $_POST['email'] : '';
            $password              = isset($_POST['password']) ? $_POST['password'] : '';
            $password_verification = isset($_POST['password_verification']) ? $_POST['password_verification'] : '';

            $validator = new FormValidator();
            $validator->validUsername('username', $username, "Your username is not valid (no spaces, uppercase, special character)");
            $validator->availableUsername('username', $username, "Your username is not available");
            $validator->validEmail('email', $email, "Your email is not valid");
            $validator->samePassword('password', $password, $password_verification, "You didn't write the same password twice");

            if(!($csrf->validateToken('user-add', $_POST['token']))){
                App::errorCSRF();
            }elseif($validator->isValid()) {
                $model = new UsersModel();
                $model->create([
                    'username'   => $username,
                    'email'      => $email,
                    'password'   => hash('sha256', Settings::getConfig()['salt'] . $password),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                App::redirect('admin/users');
            }
            else {
                $token = $csrf->generateToken('user-add');
                $this->render('pages/admin/users_add.twig', [
                    'title'       => 'Add user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ],
                    'token'       => $token
                ]);
            }
        }

        else {
            $token = $csrf->generateToken('user-add');
            $this->render('pages/admin/users_add.twig', [
                'title'       => 'Add user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'token'       => $token
            ]);
        }
    }
    
    public function registrationIsValid($validator, $username, $password, $password_verification): bool { 
        
            if ($validator->notEmpty('username',$username, "Your username can't be empty")){
                $validator->validUsername('username2', $username, "Your username is not valid (no spaces, uppercase, special character)");
            }
           
            $validator->availableUsername('username', $username, "Your username is not available");
            
            if ($validator->notEmpty('password',$password, "Your password can't be empty")){
                if ($validator->validPassword('password2', $password, "Choose a different password. ")) {
                    $validator->samePassword('password3', $password, $password_verification, "You didn't write the same password twice");
                }
            }
            
            if($validator->isValid()) {
                return true;
            }else{
                return false;
            }
    }
    
    public function createNewUser($username, $password, $password_verification){
        $model = new UsersModel();
        
                $model->create([
                    'username'   => $username,
                    'password'   => hash('sha256', Settings::getConfig()['salt'] . $password),
                    'created_at' => date('Y-m-d H:i:s'),
                    'admin'      => 0
                ]);
    }
    
    
    /* This function is used when a non-administrator registers a new user*/
    public function registrateUser() {
        $csrf = new CSRF();
        $validator = New FormValidator;
        if(!empty($_POST)) {
            $username              = isset($_POST['username']) ? $_POST['username'] : '';
            $password              = isset($_POST['password']) ? $_POST['password'] : '';
            $password_verification = isset($_POST['password_verification']) ? $_POST['password_verification'] : '';

            if(!($csrf->validateToken('register', $_POST['token']))){
                App::errorCSRF();
            }
            elseif($this->registrationIsValid($validator, $username, $password, $password_verification)) {
                $token = $csrf->generateToken('register');
                $this->createNewUser($username, $password, $password_verification);
                
                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors(),
                'message'     => ('Registration successful!'),
                'token'       => $token
                ]);
            }

            else {
                $token = $csrf->generateToken('register');
                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors(),
                'token'       => $token
        ]);
            }
        }

        else {
            $token = $csrf->generateToken('register');
            $this->render('pages/registration.twig', [
            'title'       => 'Registrate',
            'description' => 'Registrate a new user',
            'errors'      => $validator->getErrors(),
            'token'       => $token
        ]);
        }
    }

    public function edit($id) {
        $csrf = new CSRF();
        if(!empty($_POST)) {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $email    = isset($_POST['email']) ? $_POST['email'] : '';

            $validator = new FormValidator();
            $validator->validUsername('username', $username, "Your username is not valid (no spaces, uppercase, special character)");
            $validator->validEmail('email', $email, "Your email is not valid");

            if(!($csrf->validateToken('user-edit', $_POST['token']))){
                App::errorCSRF();
            }
            elseif($validator->isValid()) {
                $model = new UsersModel();
                $model->update($id, [
                    'username' => $username,
                    'email'    => $email
                ]);

                if($_SESSION['id'] == $id) {
                    $this->logout();
                    App::redirect('signin');
                }

                else {
                    App::redirect('admin/users');
                }
            }

            else {
                $token = $csrf->generateToken('user-edit');
                $this->render('pages/admin/users_edit.twig', [
                    'title'       => 'Edit user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ],
                    'token'       => $token
                ]);
            }
        }

        else {
            $token = $csrf->generateToken('user-edit');
            $model = new UsersModel();
            $data = $model->find($id);

            $this->render('pages/admin/users_edit.twig', [
                'title'       => 'Edit user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data,
                'token'       => $token
            ]);
        }
    }

    public function delete($id) {
        $csrf = new CSRF();
        if(!empty($_POST)) {
            if($csrf->validateToken('user-delete', $_POST['token'])){
                $model = new UsersModel();
                $model->delete($id);

                App::redirect('admin/users');
            }else{
                App::errorCSRF();
            }

        }

        else {
            $token = $csrf->generateToken('user-delete');
            $model = new UsersModel();
            $data = $model->find($id);
            $this->render('pages/admin/users_delete.twig', [
                'title'       => 'Delete user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data,
                'token'       => $token
            ]);
        }
    }
    
    public function viewSQL($id) {
        $auth = new Auth();
        if($auth ->isAdmin()){
            echo var_dump($this->userRep->find($id)); die;
        }else{
            App::error403();
        }
    }

}
