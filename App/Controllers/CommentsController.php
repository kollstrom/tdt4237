<?php
namespace App\Controllers;

use App\Models\CommentsModel;
use \App\Controllers\Controller;
use \DateTime;
use App\System\App;
use App\System\CSRF;

class CommentsController extends Controller {
    
    protected $table = "comments";

    public function add() {
        $csrf = new CSRF();
        if(!empty($_POST)){
            if($csrf->validateToken('comment-add', $_POST['token'])){
                $text  = isset($_POST['comment']) ? $_POST['comment'] : '';
                $model = new CommentsModel;
                $model->create([
                    'created_at' => date('Y-m-d H:i:s'),
                    'user'       => $_COOKIE['user'],
                    'text'       => htmlspecialchars($text, ENT_QUOTES)
                ]);

                App::redirect('dashboard');

            }else{
                App::errorCSRF();
            }

            }

       }
    }