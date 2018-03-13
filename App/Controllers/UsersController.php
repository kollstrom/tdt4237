<?php
namespace App\Controllers;

use \App\System\App;
use \App\System\Settings;
use \App\System\FormValidator;
use \App\Controllers\Controller;
use \App\Models\UsersModel;
use \App\System\Mailer;
use App\System\Auth;

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

            if($validator->isValid()) {
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
                $this->render('pages/admin/users_add.twig', [
                    'title'       => 'Add user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ]
                ]);
            }
        }

        else {
            $this->render('pages/admin/users_add.twig', [
                'title'       => 'Add user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users'
            ]);
        }
    }
    
    public function registrationIsValid($validator, $username, $password, $password_verification, $email): bool { 
        
            if ($validator->notEmpty('username',$username, "Your username can't be empty")){
                $validator->validUsername('username2', $username, "Your username is not valid (no spaces, uppercase, special character)");
            }
           
            $validator->availableUsername('username', $username, "Your username is not available");
            
            if ($validator->notEmpty('password',$password, "Your password can't be empty")){
                if ($validator->validPassword('password2', $password, "Choose a different password. ")) {
                    $validator->samePassword('password3', $password, $password_verification, "You didn't write the same password twice");
                }
            }
			
			if ($validator->notEmpty('email', $email, "Please provide an email address")){
                $validator->validEmail('email2', $email, "The provided email address is invalid");
            }
            
            if($validator->isValid()) {
				$payload = App::getTwig()->render('mail_new.twig', [
                   'username'    => $username,
                    'password'    => $password,
                    'title'       => Settings::getConfig()['name'],
                    'description' => Settings::getConfig()['description'],
                    'link'        => Settings::getConfig()['url'] . 'signin'
                ]);
				
				$mailer = new Mailer();
				$mailer->setFrom(Settings::getConfig()['mail']['from'], 'Mailer');
				$mailer->addAddress($email);
				$mailer->Subject = 'Hello ' . $username . '! Registration successful';
                $mailer->msgHTML($content);
                $mailer->send();
                return true;
            }else{
                return false;
            }
    }
    
    public function createNewUser($username, $password, $password_verification, $email){
        $model = new UsersModel();
        
                $model->create([
                    'username'   => $username,
                    'password'   => hash('sha256', Settings::getConfig()['salt'] . $password),
					'email'      => $email,
                    'created_at' => date('Y-m-d H:i:s'),
                    'admin'      => 0
                ]);
    }
    
    
    /* This function is used when a non-administrator registers a new user*/
    public function registrateUser() {
        $validator = New FormValidator;
        if(!empty($_POST)) {
            $username              = isset($_POST['username']) ? $_POST['username'] : '';
			$email                 = isset($_POST['email']) ? $_POST['email'] : '';
            $password              = isset($_POST['password']) ? $_POST['password'] : '';
            $password_verification = isset($_POST['password_verification']) ? $_POST['password_verification'] : '';

            if($this->registrationIsValid($validator, $username, $password, $password_verification, $email)) {
                
                $this->createNewUser($username, $password, $password_verification, $email);
                
                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors(),
                'message'     => ('Registration successful!')
                ]);
            }

            else {
                $this->render('pages/registration.twig', [
                'title'       => 'Registrate',
                'description' => 'Registrate a new user',
                'errors'      => $validator->getErrors()
        ]);
            }
        }

        else {
            $this->render('pages/registration.twig', [
            'title'       => 'Registrate',
            'description' => 'Registrate a new user',
            'errors'      => $validator->getErrors(),
        ]);
        }
    }

    public function edit($id) {
        if(!empty($_POST)) {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $email    = isset($_POST['email']) ? $_POST['email'] : '';

            $validator = new FormValidator();
            $validator->validUsername('username', $username, "Your username is not valid (no spaces, uppercase, special character)");
            $validator->validEmail('email', $email, "Your email is not valid");

            if($validator->isValid()) {
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
                $this->render('pages/admin/users_edit.twig', [
                    'title'       => 'Edit user',
                    'description' => 'Users - Just a simple inventory management system.',
                    'page'        => 'users',
                    'errors'      => $validator->getErrors(),
                    'data'        => [
                        'username' => $username,
                        'email'    => $email
                    ]
                ]);
            }
        }

        else {
            $model = new UsersModel();
            $data = $model->find($id);

            $this->render('pages/admin/users_edit.twig', [
                'title'       => 'Edit user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data
            ]);
        }
    }

    public function delete($id) {
        if(!empty($_POST)) {
            $model = new UsersModel();
            $model->delete($id);

            App::redirect('admin/users');
        }

        else {
            $model = new UsersModel();
            $data = $model->find($id);
            $this->render('pages/admin/users_delete.twig', [
                'title'       => 'Delete user',
                'description' => 'Users - Just a simple inventory management system.',
                'page'        => 'users',
                'data'        => $data
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
